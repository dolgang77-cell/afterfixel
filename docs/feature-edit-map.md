# VYBE 기능별 수정 위치 맵

> "이거 수정하려면 어디 파일을 봐야 해?" — 이 문서에서 찾으세요.

---

## 홈 상단 Hero / 메인 CTA

| 항목 | 내용 |
|------|------|
| 보이는 위치 | 홈 최상단 — "오늘 밤, 어디로 갈까?" 영역 |
| 핵심 파일 | `resources/views/home.blade.php` (8~49행 Hero 섹션) |
| 데이터 소스 | `HomeController.php` → `TonightService::getStatusSummary()` |
| CTA 버튼 문구 | `TonightService::getCurrentTimeSlot()`의 `cta` 키 |
| 같이 봐야 하는 파일 | `app/Services/TonightService.php` |
| 수정 난이도 | 하 (문구만) / 중 (CTA 로직) |

---

## 오늘밤 추천 섹션 (홈 + 전용 페이지)

| 항목 | 내용 |
|------|------|
| 보이는 위치 | 홈의 "오늘밤 추천" 캐러셀 + `/tonight` 전용 페이지 |
| 핵심 파일 | `app/Services/TonightService.php` |
| 시간대 분류 수정 | `getCurrentTimeSlot()` — 시간 범위/라벨/CTA 문구 |
| 파티 점수 수정 | `scoreTonightParty()` — 각 항목의 가중치(score += N) |
| 클럽 점수 수정 | `scoreTonightClub()` — 영업 상태, 평점, 장르 매칭 점수 |
| 섹션 구성 수정 | `buildTonightSections()` — 시간대별 어떤 섹션을 보여줄지 |
| 추천 이유 문구 | `generateTonightReason()` |
| 카드 UI | `resources/views/components/tonight-card.blade.php` |
| 전용 페이지 | `resources/views/tonight/index.blade.php` |
| 컨트롤러 | `app/Http/Controllers/TonightController.php` |
| 관련 DB | parties, clubs, favorites, recent_views, user_preferences |
| 테스트 | `/tonight`, `/tonight?area=홍대`, `/tonight/status` (JSON) |
| 자주 하는 실수 | 점수 계산 수정 후 섹션이 빈 배열이 되어 화면이 비는 경우 |
| 수정 난이도 | 상 |

---

## 파티 리스트

| 항목 | 내용 |
|------|------|
| 보이는 위치 | `/parties` — 하단 탭바 "파티" 탭 |
| 핵심 파일 | `resources/views/parties/index.blade.php` |
| 컨트롤러 | `app/Http/Controllers/PartyController.php` → `index()` |
| 필터 로직 | 컨트롤러의 `->onDate()`, `->inArea()`, `->inGenre()` 스코프 |
| 카드 UI | `resources/views/components/party-card.blade.php` |
| 관련 DB | parties (+ clubs via eager loading) |
| 테스트 | `/parties`, `/parties?date=2026-04-15&area=홍대` |
| 수정 난이도 | 하 |

---

## 파티 상세

| 항목 | 내용 |
|------|------|
| 보이는 위치 | `/parties/{id}` |
| 핵심 파일 | `resources/views/parties/show.blade.php` |
| 컨트롤러 | `PartyController@show` |
| 조회수 기록 | `$party->recordView()` + `RecentView::record()` |
| 찜 상태 | `Favorite::isFavorited()` |
| OG 태그 | 뷰 상단의 `@section('og_image')` |
| Phase 2 추가 | intro_title, short_description, full_description(접기/펼치기), guide_text, 승인된 이미지 갤러리, 후기 CRUD 섹션, 문의 폼 |
| 이미지 갤러리 | `x-image-gallery` 컴포넌트 사용 (full 모드, 라이트박스 포함) |
| 관련 DB | parties, clubs, favorites, recent_views, media, reviews, inquiries |
| 수정 난이도 | 하 |

---

## 클럽 리스트 / 상세

| 항목 | 내용 |
|------|------|
| 리스트 | `resources/views/clubs/index.blade.php`, `ClubController@index` |
| 상세 | `resources/views/clubs/show.blade.php`, `ClubController@show` |
| 카드 UI | `resources/views/components/club-card.blade.php` |
| 리뷰 | `$club->reviews()` — Review 모델에서 가져옴 (Phase 2), `$club->recentReviews()` — CommunityPost에서도 가져옴 |
| Phase 2 추가 | intro_title, short_description, full_description(접기/펼치기), guide_text, highlight_tags, 승인된 이미지 갤러리(`$club->approvedMedia()`), 후기 CRUD 섹션, 문의 폼 |
| 이미지 갤러리 | `x-image-gallery` 컴포넌트 사용 (full 모드, 라이트박스 포함) |
| 성능 최적화 | `ClubController@index`에서 `AutoTranslator::preloadBatch()`로 area/genre/subgenre/vibe 값을 일괄 프리로드 (3.5초→0.35초, 10배 개선). 개별 `trans_auto()` 호출이 캐시 히트하여 API 호출 0건 |
| 관련 DB | clubs, parties(upcomingParties), community_posts, media, reviews, inquiries, translation_cache |
| 수정 난이도 | 하 |

---

## Near Me (내 근처)

| 항목 | 내용 |
|------|------|
| 보이는 위치 | `/nearby`, 홈의 "내 근처" 섹션 |
| 거리 계산 | `app/Services/GeoService.php` → `haversineDistance()` |
| 이동시간 추정 | `GeoService::estimateTravelTime()` (직선거리 × 1.4 보정) |
| 스코어링 | `app/Services/NearbyService.php` → `scoreNearMeClub()`, `scoreNearMeParty()` |
| 반경 설정 | `NearbyService::DEFAULT_RADIUS_KM` (기본 5km) |
| 지역 좌표 | `GeoService::AREA_CENTERS` — 8개 지역 하드코딩 |
| GPS 권한 | `nearby/index.blade.php`의 Alpine.js `requestLocation()`. 헤더 위치 버튼은 `layouts/app.blade.php`의 `locationBtn()` |
| GPS 설정 | `enableHighAccuracy: true`, `timeout: 10000`(10초), `maximumAge: 60000`(1분). 8개 서울 지역구 매칭 + 최근접 폴백 |
| 카드 UI | `resources/views/components/nearby-card.blade.php` |
| 컨트롤러 | `NearbyController.php` |
| 테스트 | `/nearby?lat=37.55&lng=126.92`, `/nearby?area=홍대` |
| 자주 하는 실수 | 새 지역 추가 시 `GeoService::AREA_CENTERS`에 좌표 추가 누락 |
| 수정 난이도 | 중 |

---

## 찜 버튼

| 항목 | 내용 |
|------|------|
| 보이는 위치 | 클럽/파티 상세 페이지, 찜 목록 |
| 핵심 파일 | `app/Http/Controllers/FavoriteController.php` → `toggle()` |
| 모델 | `app/Models/Favorite.php` → `toggle()`, `isFavorited()` |
| 관련 DB | favorites (session_id, target_type, target_id) |
| 테스트 | 상세 페이지에서 하트 클릭 → 새로고침 후 유지 확인 |
| 수정 난이도 | 하 |

---

## 관심 설정

| 항목 | 내용 |
|------|------|
| 보이는 위치 | `/my/preferences` |
| 핵심 파일 | `resources/views/my/preferences.blade.php` |
| 컨트롤러 | `MyPageController@preferences`, `@updatePreferences` |
| 모델 | `app/Models/UserPreference.php` |
| 영향 | 홈 맞춤 추천, 오늘밤 추천, Near Me 스코어링 모두 반영 |
| 관련 DB | user_preferences |
| 수정 난이도 | 하 |

---

## 알림 설정

| 항목 | 내용 |
|------|------|
| 보이는 위치 | `/settings/notifications` |
| 핵심 파일 | `resources/views/notifications/settings.blade.php` |
| 컨트롤러 | `NotificationSettingController@edit`, `@update` |
| 발송 로직 | `app/Services/NotificationService.php` |
| 스케줄 | `SendPartyReminders` (10분마다), `SendTonightRecommendations` (금/토 18시), `SendScheduledPush` (매분, 예약 푸쉬) |
| 관련 DB | notification_settings, nite_notifications |
| 수정 난이도 | 중 |

---

## 이미지 갤러리 컴포넌트

| 항목 | 내용 |
|------|------|
| 보이는 위치 | 커뮤니티 카드(compact), 클럽 상세(full), 파티 상세(full) |
| 핵심 파일 | `resources/views/components/image-gallery.blade.php` |
| Props | `images` (URL 배열), `thumbnails` (썸네일 URL 배열), `compact` (bool) |
| compact 모드 | 1장=풀폭 h-32, 2장 이상=2개 썸네일 + "+N" 오버레이. 클릭 시 라이트박스 |
| full 모드 | 1장=풀폭, 2장=2컬럼, 3+장=3컬럼(첫 이미지 2x2). 최대 6개, 초과 "+N" 오버레이 |
| 라이트박스 | 풀스크린(z-9999), 카운터(1/5), 닫기(버튼+ESC+외부 클릭), 좌우(키보드+버튼+스와이프), 하단 썸네일 스트립 |
| 이미지 소스 | `Media::scopePublic()` (approved + is_visible). 커뮤니티 post-card는 legacy `$post->images` JSON 폴백 |
| 사용하는 뷰 | `components/post-card.blade.php`, `clubs/show.blade.php`, `parties/show.blade.php` |
| 번역 키 | `gallery.close`, `gallery.prev`, `gallery.next`, `gallery.of` (4개 언어) |
| 수정 시 영향 | 커뮤니티 목록, 클럽 상세, 파티 상세 페이지 모두 영향 |
| 수정 난이도 | 중 |

---

## 커뮤니티 글쓰기

| 항목 | 내용 |
|------|------|
| 보이는 위치 | `/community/create` |
| 핵심 파일 | `resources/views/community/create.blade.php` |
| 검증 | `app/Http/Requests/StoreCommunityPostRequest.php` (로그인 필수) |
| 컨트롤러 | `CommunityController@store` |
| 닉네임 | 로그인 사용자의 닉네임 자동 사용 (별도 입력 없음) |
| 이미지 업로드 | 최대 5장, `POST /media/upload` (owner_type=community)로 업로드, `uploaded_image_ids`로 게시글에 연결 |
| 이미지 제한 | 최대 10MB, jpeg/png/webp (서버에서 자동 최적화) |
| 이미지 승인 | 텍스트는 즉시 노출, 이미지는 승인 대기 (pending → 관리자 승인 후 노출) |
| 신고 | `CommunityController@report` → `CommunityPost::report()` (5회 이상 자동 숨김) |
| Rate Limit | 글쓰기 5회/분, 신고 10회/분 |
| 관련 DB | community_posts, media (owner_type=community) |
| 수정 난이도 | 하 |

---

## 관리자: 클럽/파티 등록

| 항목 | 내용 |
|------|------|
| 클럽 등록 | `/admin/clubs/create` → `Admin\ClubController@store` |
| 파티 등록 | `/admin/parties/create` → `Admin\PartyController@store` |
| 등록 폼 | `admin/clubs/form.blade.php`, `admin/parties/form.blade.php` |
| 검증 | 컨트롤러 내 `validateClub()`, `validateParty()` private 메서드 |
| 파티 등록 시 알림 | `NotificationService::sendNewPartyAlerts()` 자동 호출 |
| 로그 기록 | `AdminLog::record()` 자동 호출 |
| 수정 난이도 | 하 |

---

## 관리자: 노출순서 변경

| 항목 | 내용 |
|------|------|
| 보이는 위치 | `/admin/exposure` |
| 핵심 파일 | `resources/views/admin/exposure/index.blade.php` |
| 컨트롤러 | `Admin\ExposureController` |
| 클럽 순서 | `sort_order` 컬럼 숫자로 관리 (큰 수 = 상단) |
| 파티 고정 | `sort_order > 0`이면 홈에 고정 |
| 배너 | 위치별(home_top 등) 그룹 표시. 수정은 `/admin/banners`에서 |
| 수정 난이도 | 하 |

---

## 공통 하단 탭바

| 항목 | 내용 |
|------|------|
| 핵심 파일 | `resources/views/components/layout/bottom-nav.blade.php` |
| 탭 구성 | 홈, 파티, 클럽, 투어AI, MY (5개 고정) |
| 활성 탭 판단 | `Request::segment(1)` 기준 |
| 수정 시 영향 | 모든 사용자 페이지 |
| 수정 난이도 | 하 (단, 탭 추가/제거 시 전체 레이아웃 영향 주의) |

---

## 공통 카드 UI

| 카드 | 파일 | 사용처 |
|------|------|--------|
| party-card | `components/party-card.blade.php` | 홈, 파티 목록, 맞춤 추천 |
| club-card | `components/club-card.blade.php` | 홈, 클럽 목록 |
| tonight-card | `components/tonight-card.blade.php` | 홈 오늘밤 추천, /tonight |
| nearby-card | `components/nearby-card.blade.php` | /nearby |
| post-card | `components/post-card.blade.php` | 홈 실시간, 커뮤니티 목록 |
| image-gallery | `components/image-gallery.blade.php` | post-card(compact), clubs/show(full), parties/show(full) |

**주의**: 카드 수정 전 `grep -rn "x-카드이름" resources/views/`로 사용처 반드시 확인.

---

## 공통 레이아웃

| 파일 | 역할 | 수정 시 영향 |
|------|------|------------|
| `layouts/app.blade.php` | HTML head, 메타태그, CDN, PWA, 기기 식별 JS (nite_device_id/nite_guest_id 생성 및 cookie 저장) | 모든 사용자 페이지 |
| `layouts/partials/styles.blade.php` | 공통 CSS (card, btn, glass 등) | 모든 사용자 페이지 |
| `components/layout/header.blade.php` | 상단 헤더 (언어 선택, 위치 버튼, 알림, 검색) | 모든 사용자 페이지 |
| `components/layout/bottom-nav.blade.php` | 하단 탭바 | 모든 사용자 페이지 |
| `admin/layouts/app.blade.php` | 관리자 레이아웃 | 모든 관리자 페이지 |

---

## 회원 인증 (회원가입/로그인/로그아웃)

| 항목 | 내용 |
|------|------|
| 보이는 위치 | `/register`, `/login`, 마이페이지 로그인/로그아웃 버튼 |
| 핵심 파일 | `app/Http/Controllers/AuthController.php` |
| 회원가입 뷰 | `resources/views/auth/register.blade.php` |
| 로그인 뷰 | `resources/views/auth/login.blade.php` |
| 라우트 | `GET/POST /register`, `GET/POST /login`, `POST /logout`, `GET /auth/check-nickname` |
| 닉네임 | 가입 시 필수 (2~20자, `[a-zA-Z0-9가-힣_]+`), unique, 가입 후 변경 불가 (관리자만 변경 가능). 실시간 중복 확인 API 제공 |
| 세션 병합 | 로그인 시 favorites, recent_views, user_preferences, notification_settings 병합 |
| 관련 DB | users (phone, nickname, status, last_login_at 추가) |
| 수정 난이도 | 중 |

---

## 역할 기반 접근 제어 (RBAC)

| 항목 | 내용 |
|------|------|
| 역할 종류 | `user`, `md`, `admin`, `super_admin` |
| 상태 종류 | `active`, `suspended`, `withdrawn` |
| 핵심 파일 | `app/Models/User.php` — `isAdmin()`, `isActive()`, `isMd()` |
| 미들웨어 (관리자) | `app/Http/Middleware/AdminMiddleware.php` — `isAdmin()` + `isActive()` 확인 |
| 미들웨어 (MD) | `app/Http/Middleware/MdMiddleware.php` — `isMd()` + `isActive()` + mdProfile 보유 확인. 별칭: `'md'` (`bootstrap/app.php`에 등록) |
| 수정 난이도 | 중 |

---

## 관리자: 회원 관리

| 항목 | 내용 |
|------|------|
| 보이는 위치 | `/admin/users` |
| 핵심 파일 | `app/Http/Controllers/Admin/UserController.php` |
| 목록 뷰 | `resources/views/admin/users/index.blade.php` |
| 상세 뷰 | `resources/views/admin/users/show.blade.php` |
| 라우트 | `GET /admin/users`, `GET /admin/users/{user}`, `PATCH /admin/users/{user}/role`, `PATCH /admin/users/{user}/status` |
| 기능 | 이름/이메일/전화번호 검색, 역할/상태 필터, 역할 변경, 상태 변경, 닉네임 변경, 활동 요약 |
| 수정 난이도 | 하 |

---

## 관리자: MD 프로필 관리

| 항목 | 내용 |
|------|------|
| 보이는 위치 | `/admin/md` |
| 핵심 파일 | `app/Http/Controllers/Admin/MdProfileController.php` |
| 모델 | `app/Models/MdProfile.php` |
| 뷰 폴더 | `resources/views/admin/md/` |
| 주요 필드 | display_name, profile_image, intro, contact_info, external_link, areas, genres, affiliation, admin_memo, status, visible, priority |
| 라우트 | full CRUD at `/admin/md` |
| 수정 난이도 | 하 |

---

## 관리자: MD ↔ 클럽/파티 매핑

| 항목 | 내용 |
|------|------|
| 보이는 위치 | `/admin/md-mappings` |
| 핵심 파일 | `app/Http/Controllers/Admin/MdMappingController.php` |
| 뷰 | `resources/views/admin/md-mappings/index.blade.php` |
| 라우트 | `GET/POST /admin/md-mappings`, `DELETE /admin/md-mappings/{type}/{id}` |
| 관련 DB | `md_club`, `md_party` (many-to-many, visible/priority/note 포함) |
| 모델 관계 | `Club::mdProfiles()`, `Club::activeMds()`, `Party::mdProfiles()`, `Party::activeMds()` |
| 수정 난이도 | 중 |

---

## MD 프로필 (사용자 측)

| 항목 | 내용 |
|------|------|
| 보이는 위치 | `/md/{md}`, 클럽/파티 상세 내 MD 섹션 |
| 핵심 파일 | `app/Http/Controllers/MdController.php` |
| 상세 뷰 | `resources/views/md/show.blade.php` |
| 카드 UI | `resources/views/components/md-card.blade.php` |
| 클럽 상세 연동 | `resources/views/clubs/show.blade.php` — MD 섹션 |
| 파티 상세 연동 | `resources/views/parties/show.blade.php` — MD 섹션 |
| 필터 | active & visible MD만 노출 |
| 수정 난이도 | 하 |

---

## 관리자: 접속 로그

| 항목 | 내용 |
|------|------|
| 보이는 위치 | `/admin/access-logs` (목록), `/admin/access-logs/{accessLog}` (상세) |
| 모델 | `app/Models/AccessLog.php` — User Agent 자동 파싱 (device_type, os, browser, is_mobile). KST 접근자: `created_at_kst`, `created_at_kst_short` |
| 헬퍼 | `app/Helpers/DateHelper.php` — `toKst()`, `toKstShort()`, `toKstFull()`, `kstToUtc()`. 서버는 UTC 저장, 관리자 화면은 KST 표시 |
| 미들웨어 | `app/Http/Middleware/LogAccessMiddleware.php` — GET 요청만 기록, API/AJAX/정적 파일 제외. 헤더(`X-Device-Id`, `X-Device-Source`, `X-App-Version`, `X-Build-Version`, `X-Client-Timezone`, `X-Client-Timezone-Offset`) 및 쿠키(`nite_device_id`, `nite_guest_id`), `Accept-Language` 수집 |
| 뷰 | `resources/views/admin/access-logs/index.blade.php` (목록), `resources/views/admin/access-logs/show.blade.php` (상세) |
| 라우트 | `GET /admin/access-logs`, `GET /admin/access-logs/{accessLog}` |
| 필터 | IP, user_id, 기기 유형, 로그인 상태, 날짜 범위(KST 입력→UTC 변환), device_source, 브라우저, OS, device_id 검색, guest_id 검색 |
| 기기 식별 | `device_id` (web: `web_` prefix, app: `and_` prefix), `guest_id` (`g_` prefix). 웹은 localStorage+cookie에 저장, 앱은 ANDROID_ID 사용 |
| 관련 DB | access_logs, user_devices |
| 관련 모델 | `app/Models/UserDevice.php` — `upsert()` 메서드로 기기 정보 자동 등록/갱신 |
| 수정 난이도 | 중 |

---

## 마이페이지

| 항목 | 내용 |
|------|------|
| 보이는 위치 | `/my` |
| 로그인 시 | 사용자 닉네임/이메일 표시, 로그아웃 버튼 |
| 비로그인 시 | 로그인/회원가입 버튼 표시 |
| 수정 난이도 | 하 |

---

## Android 앱 수정

| 수정 대상 | 파일 |
|-----------|------|
| 앱 이름 | `android/app/src/main/res/values/strings.xml` |
| 앱 아이콘 | `android/app/src/main/res/mipmap-*/ic_launcher*.png` |
| 스플래시 | `android/app/src/main/res/drawable/splash.xml` |
| 상태바 색상 | `android/app/src/main/AndroidManifest.xml` (STATUS_BAR_COLOR) |
| 패키지명 | `android/app/build.gradle.kts` (applicationId) — 출시 후 변경 불가! |
| 버전 | `android/app/build.gradle.kts` (versionCode, versionName) |
| 대상 URL | `android/app/build.gradle.kts` (manifestPlaceholders) |
| TWA 인증 | `public/.well-known/assetlinks.json` |
| 빌드 | `bash android/build-apk.sh debug\|release\|bundle` |
| JS 인터페이스 | `android/app/src/main/java/.../MainActivity.java` — `NiteApp` JavascriptInterface (getDeviceId, getDeviceModel, getManufacturer, getOsVersion, getAppVersion, getBuildVersion). ANDROID_ID 기반 `and_` prefixed deviceId. User-Agent에 `NiteApp/{version}` 추가 |

---

## MD 대시보드

| 항목 | 내용 |
|------|------|
| 보이는 위치 | `/md-dashboard/*` |
| 핵심 파일 | `app/Http/Controllers/MdDashboardController.php` |
| 레이아웃 | `resources/views/md-dashboard/layout.blade.php` |
| 뷰 폴더 | `resources/views/md-dashboard/` (index, profile, clubs, club-content, parties, party-content, reviews, inquiries, inquiry-show, media) |
| 미들웨어 | `app/Http/Middleware/MdMiddleware.php` (별칭 `'md'`) |
| 라우트 | `routes/md.php` (prefix: `/md-dashboard`) |
| 기능 | 프로필 편집, 배정 클럽/파티 콘텐츠 수정, 이미지 업로드/삭제/정렬, 후기 열람, 문의 답변, 문의 상태 변경(in_progress/answered/reservation_confirmed/consultation_completed), 모바일 친화 작업 화면 |
| 모바일/API | `app/Http/Controllers/Api/MdApiController.php`, `routes/api.php` (`/api/md/me`, `/api/md/me/clubs`, `/api/md/me/parties`, `/api/md/me/inquiries`, `/api/md/me/reviews`) |
| 권한 검증 | `app/Services/MdAccessService.php` — 담당 클럽/파티, 본인 MD 프로필, 담당 문의 검증 |
| 수정 난이도 | 중 |

---

## 미디어 관리 (이미지 승인)

| 항목 | 내용 |
|------|------|
| 보이는 위치 | `/admin/media` (관리자), `/md-dashboard/media` (MD) |
| 모델 | `app/Models/Media.php` — `scopePublic()` (approved + is_visible만 노출) |
| 업로드 컨트롤러 | `app/Http/Controllers/MediaUploadController.php` |
| 관리자 컨트롤러 | `app/Http/Controllers/Admin/MediaController.php` (승인/거절/숨김/삭제/일괄승인) |
| 이미지 최적화 | `app/Services/ImageOptimizer.php` — EXIF 보정 → max 1920px 리사이즈 → JPEG 82% 압축 → 400px 썸네일. 모든 업로드 경로에 적용 |
| 뷰 | `resources/views/admin/media/index.blade.php` |
| 승인 정책 | admin 업로드 → auto-approved. MD 업로드 → `md_profile`/담당 `club`/담당 `party`만 auto-approved. user 업로드와 MD의 커뮤니티/후기 업로드 → pending |
| 정책 수정 위치 | `app/Models/Media.php` (`shouldAutoApprove`, `approvalAttributesFor`) + `app/Http/Controllers/MediaUploadController.php` + `app/Services/MdAccessService.php` |
| 업로드 경로 | `/storage/md/`, `/storage/clubs/`, `/storage/parties/`, `/storage/reviews/`, `/storage/community/`, `/storage/push/` |
| 썸네일 경로 | `{folder}/thumbs/{filename}_thumb.{ext}` |
| 업로드 제한 | 최대 10MB (서버에서 자동 최적화) |
| owner_type | md_profile, club, party, review, community, push |
| 관련 DB | media (owner_type, owner_id, uploaded_by, uploaded_by_role, approval_status, is_visible, sort_order, original_name, original_size, optimized_size, mime_type, width, height, thumbnail_path) |
| 수정 난이도 | 중 |

---

## 후기 (Review)

| 항목 | 내용 |
|------|------|
| 보이는 위치 | 클럽/파티 상세 페이지 내 후기 섹션 |
| 핵심 파일 | `app/Http/Controllers/ReviewController.php` |
| 모델 | `app/Models/Review.php` |
| 라우트 | `POST /reviews/{type}/{id}`, `PATCH /reviews/{review}`, `DELETE /reviews/{review}` |
| 작성자 표시 | `user->nickname` (닉네임) — clubs/show, parties/show에서 표시 |
| 관리자 | `app/Http/Controllers/Admin/ReviewController.php` — 목록/필터, 숨김 토글 |
| 관련 DB | reviews (user_id, target_type, target_id, content, rating, tags, like_count, report_count, is_hidden) |
| 태그 | 분위기 좋음, 음악 최고, 입장 쉬움 등 |
| 이미지 정책 | 후기 텍스트 즉시 노출, 이미지는 미디어 승인 플로우 (pending → admin 승인) |
| 수정 난이도 | 하 |

---

## 문의 (Inquiry)

| 항목 | 내용 |
|------|------|
| 보이는 위치 | 클럽/파티 상세 내 문의 폼, `/my/inquiries` |
| 핵심 파일 | `app/Http/Controllers/InquiryController.php` |
| 모델 | `app/Models/Inquiry.php`, `app/Models/InquiryReply.php` |
| 알림 서비스 | `app/Services/InquiryNotificationService.php` — 문의 생성/답변/상태변경 시 알림 발송 |
| 사용자 라우트 | `POST /inquiries/{type}/{id}`, `GET /my/inquiries`, `GET /my/inquiries/{inquiry}`, `POST /my/inquiries/{inquiry}/message` |
| MD 처리 | `MdDashboardController` — inquiries, showInquiry, replyInquiry, 상태 변경(`PATCH /md-dashboard/inquiries/{inquiry}/status`, in_progress/answered/reservation_confirmed/consultation_completed만 가능) |
| 관리자 | `app/Http/Controllers/Admin/InquiryController.php` — 목록/상세, 상태 변경(모든 상태), MD 재배정, 내부 메모 |
| MD 자동 배정 | `Inquiry::assignMd()` — 대상에 매핑된 최고 우선순위 active MD 자동 배정 |
| 상태 흐름 | pending → in_progress → answered → reservation_confirmed / consultation_completed → closed (→ hidden) |
| 알림 트리거 | 문의 생성(사용자+MD), MD 답변(사용자), 관리자 답변(사용자), 상태 변경(사용자). NiteNotification type=`inquiry_update`, 링크: `/my/inquiries/{id}` |
| 관련 DB | inquiries (status: pending/in_progress/answered/reservation_confirmed/consultation_completed/closed/hidden), inquiry_replies (author_type: user/md/admin, is_internal) |
| 수정 난이도 | 중 |

---

## 관리자: 미디어 관리

| 항목 | 내용 |
|------|------|
| 보이는 위치 | `/admin/media` (사이드바 "운영" 섹션, 대기 건수 뱃지) |
| 핵심 파일 | `app/Http/Controllers/Admin/MediaController.php` |
| 뷰 | `resources/views/admin/media/index.blade.php` |
| 기능 | 승인(approve), 거절(reject + 사유), 숨김(hide), 삭제(delete), 일괄 승인(bulk-approve) |
| 수정 난이도 | 하 |

---

## 관리자: 후기 관리

| 항목 | 내용 |
|------|------|
| 보이는 위치 | `/admin/reviews` (사이드바 "운영" 섹션) |
| 핵심 파일 | `app/Http/Controllers/Admin/ReviewController.php` |
| 기능 | 후기 목록 (필터), 숨김 토글 (is_hidden) |
| 수정 난이도 | 하 |

---

## 관리자: 문의 관리

| 항목 | 내용 |
|------|------|
| 보이는 위치 | `/admin/inquiries` (사이드바 "운영" 섹션, 대기 건수 뱃지) |
| 핵심 파일 | `app/Http/Controllers/Admin/InquiryController.php` |
| 기능 | 문의 목록/상세, 상태 변경(7단계: pending/in_progress/answered/reservation_confirmed/consultation_completed/closed/hidden — 관리자는 모든 상태 변경 가능), MD 배정 변경, 답변 작성, 내부 메모(is_internal) |
| 알림 | 상태 변경 시 사용자에게 자동 알림 (`InquiryNotificationService`) |
| 수정 난이도 | 하 |

---

## 푸쉬 캠페인 관리

| 항목 | 내용 |
|------|------|
| 보이는 위치 | `/admin/push` (사이드바 "마케팅" 섹션) |
| 핵심 파일 | `app/Http/Controllers/Admin/PushCampaignController.php` |
| 서비스 | `app/Services/PushService.php` — 타겟 해석, 인앱 알림 생성, 전달 로그 기록 |
| 모델 | `app/Models/PushCampaign.php`, `PushDeliveryLog`, `PushInflowLog`, `DeviceToken` |
| 뷰 | `resources/views/admin/push/` (index, form, show) — form에 이미지 파일 업로드 지원 (`pushImageUploader()` Alpine 컴포넌트) |
| 라우트 | CRUD + cancel (`POST /admin/push/{campaign}/cancel`) + send-now (`POST /admin/push/{campaign}/send-now`) |
| 캠페인 유형 | notice, event, party, system, marketing |
| 캠페인 상태 | draft, scheduled, sending, sent, failed, cancelled |
| 타겟 유형 | all, logged_in, area (preferred_areas 기준), genre (preferred_genres 기준), custom |
| 통계 카드 (index) | total, sent, scheduled, totalSent, totalClicked |
| 캠페인별 통계 (show) | target_count, sent_count, failed_count, clicked_count, inflow_count, click rate |
| 예약 발송 | `app/Console/Commands/SendScheduledPush.php` (`nite:send-scheduled-push`), 매분 실행 (`routes/console.php`) |
| 추적 라우트 | `POST /push/track-click` (클릭 기록), `POST /push/track-inflow` (유입 기록) |
| 기기 등록 | `POST /device-tokens` — platform: android/ios/web |
| UTM | 캠페인 링크에 `?utm_campaign={id}` 자동 부착 |
| 관련 DB | push_campaigns, push_delivery_logs, push_inflow_logs, device_tokens |
| 수정 난이도 | 중 |

---

## 통합 검색

| 항목 | 내용 |
|------|------|
| 보이는 위치 | `/search` (헤더 검색 버튼 연결) |
| 핵심 파일 | `app/Http/Controllers/SearchController.php` |
| 라우트 | `GET /search?q=키워드` |
| 검색 대상 | 클럽(active, name/area/genre/description/vibe), 파티(non-cancelled, name/genre/description/lineup), MD(public, display_name/intro/affiliation) |
| 데이터 정책 | active 클럽, 취소되지 않은 파티, public MD만 결과에 포함 |
| 인기 키워드 | 최근 7일 search_logs에서 추출, 검색어 없을 때 표시 |
| 빈 상태 | 추천 키워드 표시 |
| API | JSON 응답 지원 (Accept: application/json) |
| 관련 DB | search_logs |
| 수정 난이도 | 하 |

---

## 신고 버튼

| 항목 | 내용 |
|------|------|
| 보이는 위치 | 커뮤니티 게시글, 후기, 미디어 등 각 콘텐츠 옆 |
| 핵심 파일 | `resources/views/components/report-button.blade.php` |
| 컨트롤러 | `app/Http/Controllers/ReportController.php` |
| 라우트 | `POST /reports` |
| 신고 사유 | abuse, spam, adult, false_info, privacy, other |
| 중복 방지 | reporter + target_type + target_id unique 제약 |
| 자동 숨김 | `ModerationService::processReport()` — 임계값 초과 시 자동 숨김 |
| 관련 DB | reports, moderation_logs |
| 수정 난이도 | 하 |

---

## 금칙어 필터

| 항목 | 내용 |
|------|------|
| 적용 위치 | `CommunityController@store`, `ReviewController@store` |
| 핵심 파일 | `app/Services/ForbiddenWordFilter.php` |
| 모델 | `app/Models/ForbiddenWord.php` |
| 매칭 방식 | exact (정확 일치), contains (포함), regex (정규식) |
| 동작 유형 | block (저장 차단), mask (마스킹), review (검토 대기), warn (경고) |
| 정규화 | 공백/특수문자 정규화 후 검사 |
| 캐시 | 5분 캐시, 추가/삭제/토글 시 캐시 자동 초기화 |
| 관리자 | `/admin/moderation/forbidden-words` |
| 관련 DB | forbidden_words |
| 수정 난이도 | 중 |

---

## 사용자 제재

| 항목 | 내용 |
|------|------|
| 보이는 위치 | `/admin/moderation/banned-users` |
| 핵심 파일 | `app/Models/UserModerationAction.php`, `app/Models/User.php` |
| 제재 유형 | warning (경고), restrict_write (글쓰기 제한), restrict_upload (업로드 제한), suspend (정지), ban (차단) |
| User 메서드 | `canWrite()` — restrict_write/suspend/ban 시 false, `canUpload()` — restrict_upload/suspend/ban 시 false |
| 기간 | 임시 (starts_at ~ ends_at) 또는 영구 (is_permanent) |
| 상태 연동 | suspend/ban 시 user.status 변경, 해제 시 active 복원 |
| 관련 DB | user_moderation_actions |
| 수정 난이도 | 중 |

---

## 관리자: 운영정책 (신고/제재/금칙어/정책)

| 항목 | 내용 |
|------|------|
| 보이는 위치 | `/admin/moderation/*` (사이드바 "운영정책" 섹션) |
| 핵심 파일 | `app/Http/Controllers/Admin/ModerationController.php` |
| 뷰 폴더 | `resources/views/admin/moderation/` (reports, banned-users, forbidden-words, policies) |
| 신고 관리 | 목록 + 상태 변경 (pending → reviewed/dismissed), 사이드바에 대기 건수 뱃지 |
| 제재 관리 | 사용자별 제재 적용/해제 |
| 금칙어 관리 | 등록/삭제/활성화 토글 |
| 정책 설정 | auto_hide_report_threshold, spam_post_limit_per_hour, review_limit_per_day, image_upload_limit_per_post, forbidden_word_default_action |
| 관련 DB | reports, user_moderation_actions, forbidden_words, moderation_policies, moderation_logs |
| 수정 난이도 | 중 |

---

## 다국어(i18n) — 언어 전환

| 항목 | 내용 |
|------|------|
| 보이는 위치 | 전체 페이지 — 헤더 지구본 아이콘 드롭다운 |
| 핵심 파일 | `app/Http/Middleware/SetLocale.php`, `config/locales.php` (단일 설정 소스) |
| 번역 파일 | `lang/ko.json`, `lang/en.json`, `lang/ja.json`, `lang/zh.json` (~240개 키/파일) |
| 자동번역 서비스 | `app/Services/AutoTranslator.php` (Google Translate + DB 캐시 + 배치 프리로드) |
| 자동번역 헬퍼 | `app/Helpers/TranslationHelper.php` → `trans_auto($text)` |
| 번역 캐시 테이블 | `translation_cache` (hash + target_locale, unique constraint) |
| 설정 파일 | `config/locales.php` (언어 목록/활성 여부), `config/app.php` (locale, fallback_locale) |
| 쿠키 암호화 제외 | `bootstrap/app.php` — `nite_locale` 쿠키 암호화 제외 |
| 미들웨어 등록 | `bootstrap/app.php` (web 미들웨어 그룹) |
| 언어 선택 UI | `resources/views/components/layout/header.blade.php` (config 기반 자동 렌더링) |
| 로딩 오버레이 | `resources/views/layouts/app.blade.php` — `#lang-overlay` + `lang-switch` 이벤트 |
| 번역 적용 뷰 (13개) | `home`, `clubs/index`, `clubs/show`, `parties/index`, `parties/show`, `community/index`, `community/create`, `my/index`, `my/recent`, `my/preferences`, `tour/index`, `md/show`, `notifications/index` |
| 번역 적용 컴포넌트 (6개) | `header`, `bottom-nav`, `md-card`, `post-card`, `report-button`, `image-gallery` |
| 번역 적용 인증 뷰 | `auth/login`, `auth/register` |
| 관리자 페이지 | 번역 미적용 (한국어 전용) |
| 배치 프리로드 적용 | `ClubController@index` (area/genre/subgenre/vibe), `ClubController@show`, `PartyController@show`, `HomeController@index` — `AutoTranslator::preloadBatch()` |
| 번역 사전 | 45개 vibe/subgenre 용어가 `translation_cache`에 사전 등록 (EN/JA/ZH). API 호출 없이 즉시 반환 |
| 언어 저장 | 세션 + 쿠키 (`nite_locale`, 1년 TTL, 암호화 제외) |
| 전환 방법 | `?lang={코드}` URL 파라미터 |
| 새 언어 추가 | (1) `config/locales.php`에 항목 추가 (2) `lang/{code}.json` 생성 — header/미들웨어 자동 반영 |
| 새 번역 키 | 4개 JSON 파일에 키 추가 → Blade에서 `{{ __('key') }}` 사용 |
| 사용자 콘텐츠 번역 | Blade에서 `{{ trans_auto($text) }}` 사용 (클럽/파티 설명, 후기, 커뮤니티 글, MD 소개) |
| 상세 가이드 | `docs/i18n-guide.md` |
| 수정 난이도 | 하 (번역 추가) / 중 (새 언어 추가) |

---

## 법적 문서 (이용약관/개인정보처리방침)

| 항목 | 내용 |
|------|------|
| 보이는 위치 | `/terms`, `/privacy` |
| 핵심 파일 | `resources/views/legal/terms.blade.php`, `resources/views/legal/privacy.blade.php` |
| 라우트 | `GET /terms`, `GET /privacy` (web.php) |
| 이용약관 | 10개 조항 (목적, 정의, 서비스, 가입, 금지행위, 제재, 이미지, 문의, 면책, 연락처) |
| 개인정보처리방침 | 9개 섹션 (수집 항목, 방법, 목적, 보유, 제3자, 권리, 파기, 보안, 연락처) |
| 회원가입 연동 | `auth/register.blade.php`에 약관/개인정보 링크 추가 |
| 주의사항 | 초안 상태, 프로덕션 출시 전 법률 검토 필수 |
| 수정 난이도 | 하 |
