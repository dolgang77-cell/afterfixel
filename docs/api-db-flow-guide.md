# VYBE API / DB / 화면 연결 흐름 가이드

---

## 1. DB 테이블 관계도

```
users ──────┬── favorites (session_id/user_id 기반)
            ├── recent_views (session_id/user_id 기반)
            ├── user_preferences (session_id/user_id 기반)
            ├── notification_settings (session_id/user_id 기반)
            ├── nite_notifications (session_id/user_id 기반)
            ├── admin_logs (user_id, 관리자만)
            ├── md_profiles (user_id FK, 1:1)
            ├── access_logs (user_id, nullable)
            ├── user_devices (user_id, nullable)
            └── device_tokens (user_id FK)

push_campaigns ┬── push_delivery_logs (push_campaign_id FK + user_id FK)
               └── push_inflow_logs (push_campaign_id FK + user_id FK)

search_logs (독립, user_id nullable)

md_profiles ┬── md_club (md_profile_id + club_id, M:N 피벗)
            └── md_party (md_profile_id + party_id, M:N 피벗)

clubs ──────┬── parties (club_id FK)
            ├── community_posts (club_id FK, nullable)
            ├── click_logs (target_type='club')
            ├── md_club (club_id FK → md_profiles)
            ├── media (owner_type='club', owner_id)
            ├── reviews (target_type='club', target_id)
            └── inquiries (target_type='club', target_id)

parties ────┬── favorites (target_type='party')
            ├── recent_views (target_type='party')
            ├── click_logs (target_type='party')
            ├── md_party (party_id FK → md_profiles)
            ├── media (owner_type='party', owner_id)
            ├── reviews (target_type='party', target_id)
            └── inquiries (target_type='party', target_id)

media ──────── uploaded_by → users, approved_by → users
reviews ────── user_id → users
inquiries ──┬── user_id → users, assigned_md_id → md_profiles
            └── inquiry_replies (inquiry_id FK, author_type/author_id)

tour_recommendations ── favorites (target_type='tour')

reports ────── reporter_id → users, target_type (community_post/review/media), target_id
forbidden_words (독립, 캐시 5분)
user_moderation_actions ── user_id → users, actioned_by → users
moderation_policies (독립, 캐시 5분)
moderation_logs ── user_id → users (nullable)

banners (독립)
access_logs ── user_devices (device_id 기반, 자동 upsert)
```

### 핵심 관계

- **Club ↔ Party**: 1:N. 하나의 클럽에 여러 파티가 열림. `Party::club()`, `Club::parties()`
- **Favorites/RecentViews**: 다형성(polymorphic). `target_type`으로 club/party/tour 구분
- **세션 + 회원 기반**: 비로그인 시 `session_id` 기반, 로그인 시 `user_id` 기반. 로그인 시 세션 데이터 병합
- **User ↔ MdProfile**: 1:1. MD 역할 사용자는 MdProfile 보유
- **MdProfile ↔ Club/Party**: M:N. `md_club`, `md_party` 피벗 테이블 (visible, priority, note 포함)
- **Media**: 다형성. `owner_type`(md_profile/club/party/review/community/push)으로 대상 구분. `uploaded_by`로 업로더 추적. 승인 플로우 포함. 업로드 시 `ImageOptimizer`로 자동 최적화 (EXIF 보정, 리사이즈, 압축, 썸네일 생성)
- **Review ↔ Club/Party**: 다형성. `target_type`(club/party)으로 구분
- **Inquiry → MdProfile**: `assigned_md_id`로 자동 배정된 MD 연결
- **InquiryReply → Inquiry**: 1:N. `author_type`(user/md/admin)으로 작성자 유형 구분
- **AccessLog**: 사용자 접속 기록. user_id nullable (비로그인 포함). device_id로 기기 식별, guest_id로 익명 사용자 추적
- **UserDevice**: 기기 레지스트리. AccessLog에 device_id가 있으면 자동 upsert
- **PushCampaign → PushDeliveryLog**: 1:N. 캠페인별 전달 기록
- **PushCampaign → PushInflowLog**: 1:N. 캠페인별 유입 기록
- **User → DeviceToken**: 1:N. 사용자별 기기 토큰 (android/ios/web)
- **SearchLog**: 검색 기록. user_id nullable (비로그인 포함)
- **Report**: 신고 기록. reporter_id + target_type + target_id unique 제약. status: pending/reviewed/dismissed
- **ForbiddenWord**: 금칙어. match_type(exact/contains/regex), action_type(block/mask/review/warn). 5분 캐시
- **UserModerationAction**: 사용자 제재. action_type(warning/restrict_write/restrict_upload/suspend/ban). 기간 또는 영구
- **ModerationPolicy**: 운영 정책. key/value 쌍. 5분 캐시
- **ModerationLog**: 자동 숨김 등 운영 로그

---

## 2. 주요 DB 테이블 상세

### clubs
| 컬럼 | 타입 | 설명 |
|------|------|------|
| id, name, slug | - | 기본 정보 |
| area | string | 홍대/이태원/강남/압구정/신촌/건대/성수/종로 |
| genre | string | 힙합/EDM/R&B/테크노/하우스/팝/케이팝/재즈/록 |
| open_time, close_time | time | 영업시간 |
| entry_fee_min, entry_fee_max | int | 입장료 범위 |
| foreigner_allowed | boolean | 외국인 입장 가능 |
| lat, lng | decimal | GPS 좌표 (Near Me에서 사용) |
| rating_avg, rating_count | float, int | 평점 |
| is_active | boolean | 활성 여부 |
| sort_order | int | 홈 노출 순서 (관리자가 설정) |

### parties
| 컬럼 | 타입 | 설명 |
|------|------|------|
| club_id | FK | 소속 클럽 |
| event_date | date | 파티 날짜 |
| start_time, end_time | time | 시작/종료 시간 |
| genre | string | 파티 장르 |
| ticket_price_min/max | int | 티켓 가격 |
| status | enum | upcoming/ongoing/ended/cancelled |
| sort_order | int | 홈 고정 (0이면 미고정) |

### user_preferences
| 컬럼 | 타입 | 설명 |
|------|------|------|
| session_id | string | 세션 식별자 (UK) |
| preferred_areas | JSON | 관심 지역 배열 |
| preferred_genres | JSON | 관심 장르 배열 |
| budget_min/max | int | 예산 범위 |
| foreigner_mode | boolean | 외국인 모드 |

### users (확장 필드)
| 컬럼 | 타입 | 설명 |
|------|------|------|
| nickname | string(30) | 닉네임 (unique, not null, 2~20자, `[a-zA-Z0-9가-힣_]+`). 가입 후 변경 불가 (관리자만 변경 가능) |
| role | enum | user/md/admin/super_admin (기본 user) |
| phone | string | 전화번호 (nullable) |
| status | enum | active/suspended/withdrawn (기본 active) |
| last_login_at | timestamp | 마지막 로그인 시각 |

### md_profiles
| 컬럼 | 타입 | 설명 |
|------|------|------|
| user_id | FK | users 테이블 연결 |
| display_name | string | 표시 이름 |
| profile_image | string | 프로필 이미지 경로 |
| intro | text | 소개글 |
| contact_info | text | 연락처 (관리자만 열람) |
| external_link | string | 외부 링크 (공개) |
| areas | JSON | 활동 지역 |
| genres | JSON | 장르 |
| affiliation | string | 소속 |
| admin_memo | text | 관리자 메모 |
| status | enum | active/inactive |
| visible | boolean | 사용자 페이지 노출 여부 |
| priority | int | 노출 우선순위 |

### md_club (피벗)
| 컬럼 | 타입 | 설명 |
|------|------|------|
| md_profile_id | FK | md_profiles |
| club_id | FK | clubs |
| visible | boolean | 노출 여부 |
| priority | int | 우선순위 |
| note | text | 메모 |

### md_party (피벗)
| 컬럼 | 타입 | 설명 |
|------|------|------|
| md_profile_id | FK | md_profiles |
| party_id | FK | parties |
| visible | boolean | 노출 여부 |
| priority | int | 우선순위 |
| note | text | 메모 |

### clubs (Phase 2 추가 필드)
| 컬럼 | 타입 | 설명 |
|------|------|------|
| short_description | string | 짧은 소개 |
| full_description | text | 상세 소개 (접기/펼치기) |
| intro_title | string | 인트로 타이틀 |
| guide_text | text | 안내 텍스트 |
| highlight_tags | JSON | 하이라이트 태그 |

### parties (Phase 2 추가 필드)
| 컬럼 | 타입 | 설명 |
|------|------|------|
| short_description | string | 짧은 소개 |
| full_description | text | 상세 소개 (접기/펼치기) |
| intro_title | string | 인트로 타이틀 |
| guide_text | text | 안내 텍스트 |
| highlight_tags | JSON | 하이라이트 태그 |

### media
| 컬럼 | 타입 | 설명 |
|------|------|------|
| owner_type | string | 소유 대상 (md_profile/club/party/review/community/push) |
| owner_id | int | 소유 대상 ID |
| uploaded_by | FK | 업로드한 사용자 |
| uploaded_by_role | string | 업로드 시점 역할 (admin/md/user) |
| file_path | string | 파일 저장 경로 |
| file_url | string | 공개 URL |
| original_name | string | 원본 파일명 |
| original_size | int | 원본 파일 크기 (bytes) |
| optimized_size | int | 최적화 후 파일 크기 (bytes) |
| mime_type | string | MIME 타입 |
| width | int | 이미지 너비 (px) |
| height | int | 이미지 높이 (px) |
| thumbnail_path | string | 썸네일 경로 (`{folder}/thumbs/{filename}_thumb.{ext}`) |
| approval_status | enum | pending/approved/rejected/hidden |
| approved_by | FK (nullable) | 승인한 관리자 |
| approved_at | timestamp (nullable) | 승인 시각 |
| rejected_reason | text (nullable) | 거절 사유 |
| is_visible | boolean | 노출 여부 |
| sort_order | int | 정렬 순서 |

### reviews
| 컬럼 | 타입 | 설명 |
|------|------|------|
| user_id | FK | 작성자 |
| target_type | string | club 또는 party |
| target_id | int | 대상 ID |
| content | text | 후기 내용 |
| rating | int | 별점 (1-5) |
| tags | JSON | 선택한 태그 |
| like_count | int | 좋아요 수 |
| report_count | int | 신고 수 |
| is_hidden | boolean | 숨김 여부 |

### inquiries
| 컬럼 | 타입 | 설명 |
|------|------|------|
| user_id | FK | 문의 작성자 |
| target_type | string | club 또는 party |
| target_id | int | 대상 ID |
| assigned_md_id | FK (nullable) | 배정된 MD |
| status | enum | pending/in_progress/answered/reservation_confirmed/consultation_completed/closed/hidden |
| subject | string | 제목 |
| message | text | 문의 내용 |
| preferred_contact | string | 선호 연락 방법 |
| visit_date | date (nullable) | 방문 예정일 |
| party_size | int (nullable) | 인원수 |

### inquiry_replies
| 컬럼 | 타입 | 설명 |
|------|------|------|
| inquiry_id | FK | 문의 ID |
| author_type | string | user/md/admin |
| author_id | FK | 작성자 ID |
| message | text | 답변 내용 |
| is_internal | boolean | 내부 메모 여부 (관리자만 열람) |

### access_logs
| 컬럼 | 타입 | 설명 |
|------|------|------|
| user_id | FK (nullable) | 로그인 사용자 |
| ip_address | string | IP 주소 |
| url | string | 요청 URL |
| method | string | HTTP 메서드 |
| user_agent | text | User Agent 원문 |
| device_type | string | desktop/mobile/tablet (자동 파싱) |
| os | string | 운영체제 (자동 파싱) |
| browser | string | 브라우저 (자동 파싱) |
| is_mobile | boolean | 모바일 여부 |
| device_id | string(100) | 기기 식별자 (app: `and_` prefix, web: `web_` prefix) |
| guest_id | string(100) | 브라우저 익명 ID (`g_` prefix, localStorage/cookie) |
| device_source | string(20) | web / app / pwa |
| os_version | string(30) | OS 버전 (User-Agent 파싱) |
| device_model | string(100) | 기기 모델 (Android User-Agent 파싱) |
| app_version | string(30) | 앱 버전 (X-App-Version 헤더) |
| build_version | string(30) | 빌드 버전 (X-Build-Version 헤더) |
| client_timezone | string(50) | 클라이언트 타임존 (X-Client-Timezone 헤더) |
| client_timezone_offset | smallint | 타임존 오프셋 분 (X-Client-Timezone-Offset 헤더) |
| language | string(10) | 언어 (Accept-Language 헤더) |

### user_devices
| 컬럼 | 타입 | 설명 |
|------|------|------|
| device_id | string (unique) | 기기 식별자 |
| user_id | FK (nullable) | 연결된 사용자 |
| platform | string | web / android / ios |
| device_model | string | 기기 모델 |
| os | string | 운영체제 |
| os_version | string | OS 버전 |
| browser | string | 브라우저 |
| app_version | string | 앱 버전 |
| build_version | string | 빌드 버전 |
| last_ip | string | 마지막 접속 IP |
| push_token_linked | boolean | 푸쉬 토큰 연결 여부 |
| first_seen_at | timestamp | 최초 접속 |
| last_seen_at | timestamp | 마지막 접속 |

### device_tokens
| 컬럼 | 타입 | 설명 |
|------|------|------|
| user_id | FK | 사용자 |
| token | string | 기기 토큰 |
| platform | enum | android/ios/web |

### push_campaigns
| 컬럼 | 타입 | 설명 |
|------|------|------|
| title | string | 캠페인 제목 |
| body | text | 본문 |
| type | enum | notice/event/party/system/marketing |
| status | enum | draft/scheduled/sending/sent/failed/cancelled |
| target_type | enum | all/logged_in/area/genre/custom |
| target_config | JSON | 타겟 조건 (지역, 장르 등) |
| exclude_staff | boolean | 스태프(MD/admin) 제외 여부 |
| link | string (nullable) | 클릭 시 이동 URL |
| scheduled_at | timestamp (nullable) | 예약 발송 시각 |
| sent_at | timestamp (nullable) | 실제 발송 시각 |
| target_count | int | 타겟 사용자 수 |
| sent_count | int | 발송 성공 수 |
| failed_count | int | 발송 실패 수 |
| clicked_count | int | 클릭 수 |
| inflow_count | int | 유입 수 |

### push_delivery_logs
| 컬럼 | 타입 | 설명 |
|------|------|------|
| push_campaign_id | FK | 캠페인 |
| user_id | FK | 수신 사용자 |
| status | enum | sent/failed/clicked |
| clicked_at | timestamp (nullable) | 클릭 시각 |

### push_inflow_logs
| 컬럼 | 타입 | 설명 |
|------|------|------|
| push_campaign_id | FK | 캠페인 |
| user_id | FK | 유입 사용자 |
| url | string | 유입 URL |

### search_logs
| 컬럼 | 타입 | 설명 |
|------|------|------|
| user_id | FK (nullable) | 검색 사용자 |
| keyword | string | 검색어 |
| results_count | int | 검색 결과 수 |

---

## 3. API 엔드포인트 상세

### GET /api/home
| 항목 | 내용 |
|------|------|
| 역할 | 홈 화면 데이터 (오늘 파티, 인기 클럽, 최근 게시글) |
| 파라미터 | 없음 |
| 응답 | `{ data: { today_parties: [...], hot_clubs: [...], recent_posts: [...] } }` |
| 구현 | `app/Http/Controllers/Api/HomeApiController.php` |
| 호출 화면 | 외부 앱/클라이언트에서 사용 |
| DB | parties, clubs, community_posts |

### GET /api/clubs
| 항목 | 내용 |
|------|------|
| 역할 | 클럽 목록 (필터 가능) |
| 파라미터 | `?area=홍대&genre=EDM&foreigner=1` |
| 응답 | `{ data: [...clubs] }` |
| 구현 | `Api\ClubApiController@index` |
| DB | clubs (is_active=true만) |
| 주의 | 입력값 검증 추가됨 (area/genre/foreigner) |

### GET /api/clubs/{id}
| 항목 | 내용 |
|------|------|
| 역할 | 클럽 상세 + 조회수 증가 |
| 응답 | `{ data: { ...club, upcoming_parties: [...] } }` |
| 구현 | `Api\ClubApiController@show` |
| DB | clubs, parties |

### GET /api/parties
| 항목 | 내용 |
|------|------|
| 역할 | 파티 목록 (필터 가능) |
| 파라미터 | `?date=2026-04-15&area=강남&genre=EDM` |
| 응답 | `{ data: [...parties with club] }` |
| 구현 | `Api\PartyApiController@index` |
| DB | parties + clubs (eager loading) |

### POST /api/tour/recommend
| 항목 | 내용 |
|------|------|
| 역할 | AI 투어 추천 |
| 파라미터 | `{ area: "홍대", time: "22:00", genre: "EDM", budget: 50000 }` |
| 검증 | `TourRecommendRequest` — area(필수, 8개 중), time(필수, HH:MM), budget(필수, 1만~50만) |
| 응답 | `{ data: { routes: [...], warnings: [...] } }` |
| 구현 | `Api\TourApiController@recommend` → `TourRecommendationService` |
| Rate Limit | 10회/분 |
| DB | clubs, tour_recommendations (결과 저장) |

### GET /tonight/status (웹 API)
| 항목 | 내용 |
|------|------|
| 역할 | 오늘밤 추천 요약 (홈 위젯용 JSON) |
| 응답 | `{ summary: { timeSlot, items, total }, status: { todayPartyCount, openClubCount } }` |
| 구현 | `TonightController@statusSummary` → `TonightService` |

### GET /nearby/summary (웹 API)
| 항목 | 내용 |
|------|------|
| 역할 | 내 근처 요약 (홈 위젯용 JSON) |
| 파라미터 | `?lat=37.55&lng=126.92` |
| 응답 | `{ summary: { nearestArea, items, total }, status: { nearbyClubCount, openClubCount } }` |
| 구현 | `NearbyController@summary` → `NearbyService` |

### POST /me/location (웹 API)
| 항목 | 내용 |
|------|------|
| 역할 | 사용자 위치를 세션에 저장 |
| 파라미터 | `{ lat: 37.55, lng: 126.92 }` |
| 응답 | `{ success: true, area: "홍대" }` |
| 구현 | `NearbyController@saveLocation` |

### 인증 라우트 (웹)

| 메서드 | URL | 설명 |
|--------|-----|------|
| GET | `/register` | 회원가입 폼 (닉네임 입력 + 실시간 중복 확인) |
| POST | `/register` | 회원가입 처리 (닉네임 필수: 2~20자, unique) |
| GET | `/login` | 로그인 폼 |
| POST | `/login` | 로그인 처리 (세션 데이터 병합) |
| POST | `/logout` | 로그아웃 |
| GET | `/auth/check-nickname` | 닉네임 중복 확인 API (`?nickname=`) |

### MD 라우트 (웹, 사용자 측)

| 메서드 | URL | 설명 |
|--------|-----|------|
| GET | `/md/{md}` | MD 프로필 상세 (active & visible만) |

### 관리자: 회원 관리 라우트

| 메서드 | URL | 설명 |
|--------|-----|------|
| GET | `/admin/users` | 회원 목록 (검색, 역할/상태 필터) |
| GET | `/admin/users/{user}` | 회원 상세 + 활동 요약 |
| PATCH | `/admin/users/{user}/role` | 역할 변경 |
| PATCH | `/admin/users/{user}/status` | 상태 변경 |

### 관리자: MD 관리 라우트

| 메서드 | URL | 설명 |
|--------|-----|------|
| GET/POST | `/admin/md` | MD 프로필 목록/등록 |
| GET | `/admin/md/create` | MD 프로필 등록 폼 |
| GET | `/admin/md/{md}` | MD 프로필 상세 |
| GET | `/admin/md/{md}/edit` | MD 프로필 수정 폼 |
| PUT/PATCH | `/admin/md/{md}` | MD 프로필 수정 |
| DELETE | `/admin/md/{md}` | MD 프로필 삭제 |

### 관리자: MD 매핑 라우트

| 메서드 | URL | 설명 |
|--------|-----|------|
| GET | `/admin/md-mappings` | MD ↔ 클럽/파티 매핑 목록 |
| POST | `/admin/md-mappings` | 매핑 추가 |
| DELETE | `/admin/md-mappings/{type}/{id}` | 매핑 삭제 |

### 후기 라우트 (웹, 인증 필요)

| 메서드 | URL | 설명 |
|--------|-----|------|
| POST | `/reviews/{type}/{id}` | 후기 작성 (type: club/party) |
| PATCH | `/reviews/{review}` | 후기 수정 (본인만) |
| DELETE | `/reviews/{review}` | 후기 삭제 (본인만) |

### 문의 라우트 (웹, 인증 필요)

| 메서드 | URL | 설명 |
|--------|-----|------|
| POST | `/inquiries/{type}/{id}` | 문의 작성 (type: club/party) |
| GET | `/my/inquiries` | 내 문의 목록 |
| GET | `/my/inquiries/{inquiry}` | 내 문의 상세 |
| POST | `/my/inquiries/{inquiry}/message` | 문의에 메시지 추가 |

### MD 대시보드 라우트 (MdMiddleware)

| 메서드 | URL | 설명 |
|--------|-----|------|
| GET | `/md-dashboard` | MD 대시보드 메인 |
| GET/POST | `/md-dashboard/profile` | MD 프로필 조회/수정 |
| GET | `/md-dashboard/clubs` | 배정된 클럽 목록 |
| GET/POST | `/md-dashboard/clubs/{club}/content` | 클럽 콘텐츠 조회/수정 |
| GET | `/md-dashboard/parties` | 배정된 파티 목록 |
| GET/POST | `/md-dashboard/parties/{party}/content` | 파티 콘텐츠 조회/수정 |
| GET | `/md-dashboard/reviews` | 배정 클럽/파티 후기 |
| GET | `/md-dashboard/inquiries` | 배정된 문의 목록 |
| GET | `/md-dashboard/inquiries/{inquiry}` | 문의 상세 |
| POST | `/md-dashboard/inquiries/{inquiry}/reply` | 문의 답변 |
| PATCH | `/md-dashboard/inquiries/{inquiry}/status` | 문의 상태 변경 (in_progress/answered/reservation_confirmed/consultation_completed만 가능) |
| GET | `/md-dashboard/media` | 본인 업로드 이미지 상태 |

### 푸쉬 추적 라우트 (웹)

| 메서드 | URL | 설명 |
|--------|-----|------|
| POST | `/push/track-click` | 푸쉬 클릭 기록 (PushDeliveryLog 업데이트, clicked_count 증가) |
| POST | `/push/track-inflow` | 푸쉬 유입 기록 (PushInflowLog 생성, inflow_count 증가) |
| POST | `/device-tokens` | 기기 토큰 등록 (platform: android/ios/web) |

### 통합 검색 라우트 (웹)

| 메서드 | URL | 설명 |
|--------|-----|------|
| GET | `/search?q=키워드` | 클럽/파티/MD 통합 검색. JSON 응답 지원 |

### 신고 라우트 (웹, 인증 필요)

| 메서드 | URL | 설명 |
|--------|-----|------|
| POST | `/reports` | 신고 접수 (target_type, target_id, reason, detail). 중복 시 422 |

### 법적 문서 라우트 (웹)

| 메서드 | URL | 설명 |
|--------|-----|------|
| GET | `/terms` | 이용약관 (10개 조항, 초안) |
| GET | `/privacy` | 개인정보처리방침 (9개 섹션, 초안) |

### 관리자: 운영정책 라우트

| 메서드 | URL | 설명 |
|--------|-----|------|
| GET | `/admin/moderation/reports` | 신고 목록 |
| PATCH | `/admin/moderation/reports/{report}` | 신고 상태 변경 (reviewed/dismissed) |
| GET | `/admin/moderation/banned-users` | 제재 회원 목록 |
| POST | `/admin/moderation/banned-users` | 제재 적용 |
| DELETE | `/admin/moderation/banned-users/{action}` | 제재 해제 |
| GET | `/admin/moderation/forbidden-words` | 금칙어 목록 |
| POST | `/admin/moderation/forbidden-words` | 금칙어 등록 |
| DELETE | `/admin/moderation/forbidden-words/{word}` | 금칙어 삭제 |
| PATCH | `/admin/moderation/forbidden-words/{word}/toggle` | 금칙어 활성/비활성 토글 |
| GET | `/admin/moderation/policies` | 운영 정책 목록 |
| PATCH | `/admin/moderation/policies/{policy}` | 운영 정책 값 수정 |

### 관리자: 미디어 관리 라우트

| 메서드 | URL | 설명 |
|--------|-----|------|
| GET | `/admin/media` | 미디어 목록 (승인 상태 필터) |
| POST | `/admin/media/{media}/approve` | 승인 |
| POST | `/admin/media/{media}/reject` | 거절 (사유 입력) |
| POST | `/admin/media/{media}/hide` | 숨김 |
| DELETE | `/admin/media/{media}` | 삭제 |
| POST | `/admin/media/bulk-approve` | 일괄 승인 |

### 관리자: 후기 관리 라우트

| 메서드 | URL | 설명 |
|--------|-----|------|
| GET | `/admin/reviews` | 후기 목록 (필터) |
| POST | `/admin/reviews/{review}/toggle-hidden` | 숨김 토글 |

### 관리자: 문의 관리 라우트

| 메서드 | URL | 설명 |
|--------|-----|------|
| GET | `/admin/inquiries` | 문의 목록 |
| GET | `/admin/inquiries/{inquiry}` | 문의 상세 |
| PATCH | `/admin/inquiries/{inquiry}/status` | 상태 변경 (모든 상태 가능) |
| PATCH | `/admin/inquiries/{inquiry}/assign-md` | MD 배정 변경 |
| POST | `/admin/inquiries/{inquiry}/reply` | 답변 (내부 메모 지원) |

### 관리자: 푸쉬 관리 라우트

| 메서드 | URL | 설명 |
|--------|-----|------|
| GET | `/admin/push` | 캠페인 목록 + 통계 카드 |
| GET | `/admin/push/create` | 캠페인 생성 폼 |
| POST | `/admin/push` | 캠페인 등록 |
| GET | `/admin/push/{campaign}` | 캠페인 상세 + 전달 통계 |
| GET | `/admin/push/{campaign}/edit` | 캠페인 수정 폼 |
| PUT/PATCH | `/admin/push/{campaign}` | 캠페인 수정 |
| DELETE | `/admin/push/{campaign}` | 캠페인 삭제 |
| POST | `/admin/push/{campaign}/cancel` | 캠페인 취소 |
| POST | `/admin/push/{campaign}/send-now` | 즉시 발송 |

### 관리자: 접속 로그 라우트

| 메서드 | URL | 설명 |
|--------|-----|------|
| GET | `/admin/access-logs` | 접속 로그 목록 (IP/기기/로그인상태/날짜(KST)/device_source/브라우저/OS/device_id/guest_id 필터) |
| GET | `/admin/access-logs/{accessLog}` | 접속 로그 상세 (시간 정보, 사용자, 기기, 네트워크/경로) |

---

## 4. 추천 로직 데이터 흐름

### 오늘밤 추천 (`TonightService`)

```
[현재 시간] → getCurrentTimeSlot() → 5개 시간대 중 하나 결정
                                        ↓
[DB 조회] → 오늘 파티 + 활성 클럽 + 사용자 찜/최근본/관심설정
                                        ↓
[스코어링] → scoreTonightParty() / scoreTonightClub()
             - 시간 상태 (입장 가능/곧 시작/마감 임박): 최대 30점
             - 장르 매칭: 12점
             - 지역 매칭: 10점
             - 평점 보너스: 10점
             - 예산 적합: 8점
             - 찜 보너스: 8점
             - 이동 시간: 5점
             - 외국인 모드: +5/-20점
             - 주말 보너스: 5점
                                        ↓
[섹션 빌드] → buildTonightSections() → 시간대에 따라 다른 섹션 구성
             - early_evening: 오늘 시작 예정 파티 → 추천 클럽
             - peak: 지금 출발 추천 → 입장 가능 클럽
             - peak_late: 지금 입장 가능 → 영업 중 클럽
             - late_night: 아직 갈 수 있는 곳
                                        ↓
[추천 이유] → generateTonightReason() → "선호 장르 EDM 매칭 · 관심 지역 홍대"
```

### Near Me (`NearbyService` + `GeoService`)

```
[사용자 GPS 좌표 또는 지역 선택]
        ↓
[GeoService::haversineDistance()] → 모든 클럽과의 직선거리 계산
        ↓
[GeoService::estimateTravelTime()] → 거리 × 1.4 보정 → 택시 이동시간 추정
        ↓
[NearbyService::scoreNearMeClub()] → 거리 점수(최대 40) + 영업 상태(25) + 평점 + 장르 + 예산
        ↓
[buildNearMeSections()] → 5가지 섹션 자동 생성
  1. 지금 바로 갈 수 있는 클럽 (영업 중 + 가까운 순)
  2. 내 근처 곧 시작하는 파티
  3. 가장 가까운 순
  4. 관심 장르 기준
  5. 외국인 모드 기준
```

### AI 투어 추천 (`TourRecommendationService`)

```
[입력: 지역, 시간, 장르, 예산, 외국인모드]
        ↓
[ClubScorer::scoreAll()] → 9차원 점수 계산 (지역, 시간, 장르, 예산, 평점, 외국인, 드레스코드, 근접성, 인기도)
        ↓
[threshold 필터] → 20점 이상만 후보
        ↓
[조건 완화] → 후보 부족하면 장르 제거 → 외국인 제거 → 임계값 하향
        ↓
[RouteBuilder] → 3개 루트 생성
  - buildOptimal(): 점수 + 이동시간 균형
  - buildBudget(): 최저 비용
  - buildShortest(): 최단 이동
        ↓
[ExplanationGenerator] → 각 정류장/루트에 대한 설명 생성
        ↓
[DB 저장] → tour_recommendations에 결과 기록
```

---

## 5. 관리자 수정 → 사용자 반영 흐름

관리자가 데이터를 수정하면 사용자 화면에 **즉시 반영**됩니다. 별도 캐시 무효화가 필요 없습니다.

예: 관리자가 클럽의 `is_active`를 false로 변경
→ `Club::active()` 스코프가 자동으로 필터링
→ 홈, 클럽 목록, 추천 결과에서 즉시 제외

예: 관리자가 파티를 등록
→ `Party::today()->upcoming()`에 자동 포함
→ `NotificationService::sendNewPartyAlerts()` 호출로 매칭 알림 발송
→ 홈 "오늘의 파티"에 즉시 노출

---

## 6. 이미지 승인 데이터 흐름

```
[이미지 업로드] → MediaUploadController / Admin\ImageUploadController
        ↓
[최적화] → ImageOptimizer::process()
  → EXIF 방향 보정
  → 최대 1920px 리사이즈
  → JPEG 82% 압축 (PNG 투명은 PNG 유지)
  → 400px 썸네일 생성 ({folder}/thumbs/{filename}_thumb.{ext})
  → original_name, original_size, optimized_size, mime_type, width, height, thumbnail_path 기록
        ↓
[역할 확인] → uploaded_by_role 기록
        ↓
  admin → approval_status = 'approved' (즉시 노출)
  md    → md_profile/담당 club/담당 party 업로드일 때만 approval_status = 'approved'
  md    → 커뮤니티/후기 등 일반 사용자 경로 업로드는 approval_status = 'pending'
  user  → approval_status = 'pending' (커뮤니티/후기 이미지 포함)
        ↓
[관리자 검토] → Admin\MediaController
  승인(approve) → approval_status = 'approved', approved_by, approved_at 기록
  거절(reject)  → approval_status = 'rejected', rejected_reason 기록
  숨김(hide)    → approval_status = 'hidden'
        ↓
[사용자 페이지] → Media::scopePublic()
  approval_status = 'approved' AND is_visible = true 만 노출
  pending/rejected/hidden은 절대 노출하지 않음
```

---

## 7. 문의 자동 배정 흐름

```
[사용자 문의 작성] → InquiryController@store
        ↓
[Inquiry::assignMd()] → 대상(club/party)에 매핑된 MD 조회
  → active 상태 + 가장 높은 priority MD 자동 배정
  → 매핑 없으면 assigned_md_id = null (관리자 처리)
        ↓
[MD 답변] → MdDashboardController@replyInquiry
  → inquiry.status = 'answered'
  → InquiryReply 생성 (author_type='md')
        ↓
[관리자 개입] → Admin\InquiryController
  → 상태 변경 (모든 상태 가능), MD 재배정, 내부 메모 작성(is_internal=true, 관리자만 열람)
        ↓
[알림 발송] → InquiryNotificationService

---

## 8. MD 모바일/API 관리 흐름

```
[MD 로그인] → MdMiddleware
        ↓
[MdAccessService]
  → mdProfile 존재 확인
  → assigned club/party 조회
  → 담당 club/party/inquiry/media만 허용
        ↓
[웹 화면]  → /md-dashboard/*
[JSON API] → /api/md/me, /api/md/me/clubs, /api/md/me/parties
             /api/md/me/inquiries, /api/md/me/reviews
             PATCH /api/clubs/{club}/content
             PATCH /api/parties/{party}/content
             POST /api/upload/md-images
             DELETE /api/media/{media}
             PATCH /api/media/{media}/order
        ↓
[MediaUploadController]
  → uploaded_by_role, approval_status, approved_by, approved_at, is_visible 기록
  → 담당 대상이 아니면 서버에서 403
        ↓
[사용자 화면]
  → approved + is_visible 이미지만 노출
```
  → 문의 생성: 사용자 + MD에게 알림
  → MD 답변: 사용자에게 알림
  → 관리자 답변: 사용자에게 알림
  → 상태 변경: 사용자에게 알림
  → NiteNotification type='inquiry_update', 링크='/my/inquiries/{id}'
```

### 문의 상태 흐름

```
pending → in_progress → answered → reservation_confirmed → closed
                                  → consultation_completed → closed
                                                            → hidden (관리자만)
```

- **MD 변경 가능 상태**: in_progress, answered, reservation_confirmed, consultation_completed
- **관리자 변경 가능 상태**: 모든 상태

---

## 8. 푸쉬 캠페인 발송 흐름

```
[관리자: 캠페인 생성] → /admin/push/create
        ↓
[즉시 발송] → POST /admin/push/{campaign}/send-now
  → PushService::send()
  → 타겟 사용자 해석 (target_type에 따라 all/logged_in/area/genre/custom)
  → exclude_staff 옵션 시 MD/admin 제외
  → 알림 비활성 사용자 제외
  → NiteNotification 생성 + PushDeliveryLog 기록
  → sent_count, failed_count 업데이트
  → status = 'sent'
        ↓
[예약 발송] → status='scheduled', scheduled_at 설정
  → nite:send-scheduled-push (매분 실행)
  → scheduled_at <= now인 캠페인 자동 발송
        ↓
[추적]
  → POST /push/track-click → PushDeliveryLog.clicked_at 기록, clicked_count 증가
  → POST /push/track-inflow → PushInflowLog 생성, inflow_count 증가
  → 캠페인 링크에 ?utm_campaign={id} 자동 부착
```

---

## 9. 통합 검색 흐름

```
[사용자: /search] → SearchController@index
        ↓
[검색어 없음] → 최근 7일 인기 키워드 표시 (search_logs 집계)
        ↓
[검색어 입력] → q 파라미터로 검색
  → 클럽: is_active=true, name/area/genre/description/vibe LIKE 검색
  → 파티: status != cancelled, name/genre/description/lineup LIKE 검색
  → MD: status=active & visible=true, display_name/intro/affiliation LIKE 검색
        ↓
[검색 로그] → search_logs에 keyword, results_count 저장
        ↓
[응답] → Blade 뷰 (Accept: text/html) 또는 JSON (Accept: application/json)
```

---

## 10. 신고 → 자동 숨김 흐름

```
[사용자 신고] → POST /reports → ReportController@store
        ↓
[중복 확인] → reporter + target_type + target_id unique 체크
        ↓
[Report 생성] → status='pending'
        ↓
[ModerationService::processReport()]
  → 해당 target의 총 신고 수 조회
  → ModerationPolicy::getValue('auto_hide_report_threshold') (기본 5)
  → 신고 수 >= 임계값이면 autoHide() 실행
        ↓
[autoHide()] → 대상 콘텐츠 is_hidden=true (community_post/review/media)
  → moderation_logs에 기록
```

### 금칙어 필터 흐름

```
[글쓰기/후기 작성] → CommunityController@store / ReviewController@store
        ↓
[ForbiddenWordFilter::check()] → 활성 금칙어 목록 조회 (5분 캐시)
  → 입력 텍스트 정규화 (공백/특수문자 제거)
  → exact/contains/regex 매칭 순서로 검사
        ↓
[action_type='block'] → 422 에러, 저장 차단
[action_type='mask']  → 해당 단어 마스킹 (***) 후 저장 (향후 적용)
[action_type='review'] → 저장 후 검토 대기 (향후 적용)
[action_type='warn']  → 경고 메시지 표시 (향후 적용)
```

### 제재 확인 흐름

```
[글쓰기/후기 작성 시도]
        ↓
[User::canWrite()] → active한 restrict_write/suspend/ban 제재 확인
  → 제재 있음 → 403 에러
  → 제재 없음 → 계속 진행
        ↓
[이미지 업로드 시도]
        ↓
[User::canUpload()] → active한 restrict_upload/suspend/ban 제재 확인
  → 제재 있음 → 403 에러
  → 제재 없음 → 계속 진행
```

---

## 11. 다국어(i18n) 언어 전환 흐름

### SetLocale 미들웨어 동작

```
[모든 웹 요청] → SetLocale 미들웨어 (web 그룹에 등록)
        ↓
[?lang= 파라미터 있음?]
  → 있고 SUPPORTED에 포함 → 세션 + 쿠키(nite_locale, 1년)에 저장 → ?lang 제거 후 리다이렉트
  → 없음 → 계속 진행
        ↓
[세션에 nite_locale 있음?] → 있으면 사용
  → 없으면 [쿠키에 nite_locale 있음?] → 있으면 사용
    → 없으면 기본값 'ko' 사용
        ↓
[App::setLocale($locale)] → 이후 모든 __() 호출에 해당 언어 적용
```

### 번역 해석 흐름

```
[Blade 뷰] → {{ __('nav.home') }}
        ↓
[Laravel 번역 엔진] → lang/{현재locale}.json에서 'nav.home' 키 검색
  → 있으면 해당 값 반환 (예: "Home")
  → 없으면 fallback_locale(ko)에서 검색
  → 그래도 없으면 키 문자열 자체 반환 ("nav.home")
```

### 지원 언어

| 코드 | 언어 | 파일 |
|------|------|------|
| ko | 한국어 (기본/fallback) | `lang/ko.json` |
| en | English | `lang/en.json` |
| ja | 日本語 | `lang/ja.json` |
| zh | 中文 | `lang/zh.json` |
