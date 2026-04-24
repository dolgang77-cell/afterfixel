# VYBE 관리자 패널 사용 가이드

> 관리자 패널의 모든 기능을 설명하는 문서입니다.

---

## 접속 및 권한

- **URL**: `/admin` (로그인: `/admin/login`)
- **기본 계정**: `admin@nite.kr` / 시더에서 설정한 비밀번호 (기본 `admin1234!`)
- **접근 가능 역할**: `admin`, `super_admin` (status가 `active`인 경우만)
- **미들웨어**: `AdminMiddleware` — `isAdmin()` + `isActive()` 동시 확인

---

## 대시보드 (`/admin`)

관리자 홈 화면. 서비스 현황을 한눈에 파악합니다.

- 주요 통계: 총 클럽/파티/게시글/회원 수
- 신고된 게시글 목록 (빠른 처리용)
- 최근 관리 로그

---

## 클럽 관리 (`/admin/clubs`)

| 기능 | 설명 |
|------|------|
| 목록 | 전체 클럽 목록. 지역/장르/상태 필터 |
| 등록 | `/admin/clubs/create` — 이름, 지역, 장르, 영업시간, 입장료, GPS 좌표, 이미지 등 |
| 수정 | `/admin/clubs/{id}/edit` — 등록과 동일한 폼 |
| 삭제 | 삭제 시 연결된 파티 확인 필요 |
| 상태 변경 | `is_active` 토글 — 비활성 시 사용자 화면에서 즉시 제외 |
| 노출 순서 | `sort_order` 값으로 홈 노출 순서 결정 (높을수록 상단) |

---

## 파티 관리 (`/admin/parties`)

| 기능 | 설명 |
|------|------|
| 목록 | 전체 파티 목록. 날짜/클럽/상태 필터 |
| 등록 | `/admin/parties/create` — 클럽 선택, 날짜, 시간, 장르, 티켓 가격, 이미지 등 |
| 수정 | `/admin/parties/{id}/edit` |
| 상태 변경 | upcoming/ongoing/ended/cancelled. 일괄 상태 변경 지원 |
| 고정 | `sort_order > 0` 설정 시 홈에 고정 노출 |
| 알림 | 파티 등록 시 `NotificationService::sendNewPartyAlerts()` 자동 호출 |

### 파티 카드 구분을 반드시 확인하세요

현재 파티 카드는 단순히 "파티" 1종이 아닙니다.

| 구분 | 의미 | 운영자가 봐야 할 점 |
|------|------|---------------------|
| `실이벤트` | 외부 공지/공식 일정/RA 등으로 실제 확인된 이벤트 | 날짜, 라인업, 설명을 구체적으로 써도 됨 |
| `운영형 카드` | 공식 운영시간과 최근 패턴으로 만든 대표 세션 카드 | 확정 행사처럼 과장하면 안 됨 |
| `일반 이벤트` | 일반 등록형 파티 | 등록 출처와 설명 정확성 확인 |

관리자 목록의 `구분` 컬럼과 사용자 화면의 배지가 서로 연결되어 있으므로, 운영형 카드를 실이벤트처럼 소개하면 홈/검색/알림까지 같이 오해를 만듭니다.

### 전국 콘텐츠 운영 시 실무 원칙

- 클럽/파티 원본은 관리자 화면만이 아니라 `app/Support/CuratedNightlifeData.php`에도 있습니다.
- 전국 단위 정리, 폐업 반영, 운영형 카드 삭제는 코드 원본 수정 후 동기화까지 해야 안전합니다.
- 오래된 자동 파티를 지울 때는 기존 slug prefix를 `legacyPartyPrefixes()`에 추가해야 DB 잔재가 같이 정리됩니다.
- 실제 특집 이벤트를 추가할 때는 운영형 카드와 날짜가 겹쳐도 실이벤트가 우선 노출되도록 설계되어 있으나, `club_slug`와 `event_date`를 정확히 맞춰야 합니다.

자세한 절차는 [nightlife-data-operations-guide.md](nightlife-data-operations-guide.md)를 따르세요.

---

## 게시글 관리 (`/admin/posts`)

| 기능 | 설명 |
|------|------|
| 목록 | 전체 게시글. 신고 상태 필터 |
| 신고 검수 | 신고 5회 이상 자동 숨김. 관리자가 확인 후 복원/삭제 결정 |
| 숨김/삭제 | 개별 또는 일괄 처리 |
| 일괄 처리 | 체크박스로 여러 게시글 한 번에 숨김/삭제 |

---

## 노출 관리 (`/admin/exposure`)

| 기능 | 설명 |
|------|------|
| 클럽 순서 | `sort_order` 숫자로 홈 노출 순서 관리 |
| 파티 고정 | `sort_order > 0` 파티를 홈에 고정 |
| 배너 현황 | 위치별 활성 배너 확인 (수정은 배너 관리에서) |

---

## 배너 관리 (`/admin/banners`)

| 기능 | 설명 |
|------|------|
| 위치별 관리 | home_top, home_middle 등 위치별 배너 등록/수정/삭제 |
| 이미지 | 배너 이미지 업로드 |
| 링크 | 클릭 시 이동 URL 설정 |
| 활성/비활성 | 노출 여부 토글 |
| 기간 설정 | 시작/종료일 설정 가능 |

---

## 회원 관리 (`/admin/users`)

### 회원 목록

- **검색**: 이름, 닉네임, 이메일, 전화번호로 검색
- **필터**: 역할별 (user/md/admin/super_admin), 상태별 (active/suspended/withdrawn)
- **표시 정보**: 이름, 닉네임, 이메일, 역할, 상태, 가입일, 마지막 로그인

### 회원 상세 (`/admin/users/{user}`)

- 기본 정보: 이름, 닉네임, 이메일, 전화번호, 역할, 상태
- **닉네임 변경**: 관리자만 회원의 닉네임을 변경할 수 있음 (사용자는 가입 후 변경 불가)
- 활동 요약: 찜 수, 최근 본 항목, 게시글 수 등
- 마지막 로그인 시각

### 역할 변경 (`PATCH /admin/users/{user}/role`)

| 역할 | 설명 |
|------|------|
| `user` | 일반 사용자 |
| `md` | MD (클럽/파티 관련 프로필 보유) |
| `admin` | 관리자 (관리 패널 접근 가능) |
| `super_admin` | 최고 관리자 |

### 상태 변경 (`PATCH /admin/users/{user}/status`)

| 상태 | 설명 |
|------|------|
| `active` | 정상 활동 |
| `suspended` | 일시 정지 (로그인 불가) |
| `withdrawn` | 탈퇴 |

---

## MD 관리 (`/admin/md`)

### MD 프로필 CRUD

- **목록**: 전체 MD 프로필. 상태/노출 필터
- **등록**: `/admin/md/create` — 사용자 연결, 표시명, 프로필 이미지, 소개, 연락처, 외부 링크, 활동 지역, 장르, 소속
- **수정**: `/admin/md/{id}/edit`
- **삭제**: `/admin/md/{id}`

### 주요 필드

| 필드 | 설명 | 공개 범위 |
|------|------|-----------|
| display_name | 표시 이름 | 공개 |
| profile_image | 프로필 이미지 | 공개 |
| intro | 소개글 | 공개 |
| contact_info | 연락처 | 관리자만 |
| external_link | 외부 링크 (SNS 등) | 공개 |
| areas | 활동 지역 (JSON) | 공개 |
| genres | 장르 (JSON) | 공개 |
| affiliation | 소속 | 공개 |
| admin_memo | 관리자 메모 | 관리자만 |
| status | active/inactive | 관리용 |
| visible | 사용자 페이지 노출 여부 | 관리용 |
| priority | 노출 우선순위 | 관리용 |

---

## MD 매핑 (`/admin/md-mappings`)

MD와 클럽/파티의 연결을 관리합니다.

### 매핑 추가 (`POST /admin/md-mappings`)

- MD 프로필 선택
- 대상 유형 선택 (클럽 또는 파티)
- 대상 항목 선택
- visible, priority, note 설정

### 매핑 삭제 (`DELETE /admin/md-mappings/{type}/{id}`)

- type: `club` 또는 `party`
- 삭제 시 사용자 화면에서 즉시 제거

### 사용자 화면 반영

- 클럽 상세 (`/clubs/{id}`) — 해당 클럽에 매핑된 active & visible MD 목록 표시
- 파티 상세 (`/parties/{id}`) — 해당 파티에 매핑된 active & visible MD 목록 표시
- MD 상세 (`/md/{id}`) — MD 프로필 + 연결된 클럽/파티 표시

---

## 접속 로그 (`/admin/access-logs`)

사용자 접속 기록을 조회합니다. `LogAccessMiddleware`가 자동으로 기록합니다.

### 기록 조건

- GET 요청만 기록
- API, AJAX, 정적 파일(css/js/이미지) 요청 제외
- User Agent에서 device_type, os, browser, is_mobile 자동 파싱
- 추가 수집: device_id, guest_id, device_source, os_version, device_model, app_version, build_version, client_timezone, language (헤더/쿠키에서 수집)
- device_id가 있으면 `user_devices` 테이블에 기기 정보 자동 등록/갱신

### 시간대 표시

- 서버는 모든 시각을 **UTC**로 저장합니다
- 관리자 화면에서는 **KST (Asia/Seoul)**로 변환하여 표시합니다
- 날짜 필터 입력도 KST 기준이며, 내부적으로 UTC로 변환하여 조회합니다
- 헬퍼: `DateHelper::toKst()`, `DateHelper::kstToUtc()`

### 필터

| 필터 | 설명 |
|------|------|
| IP 주소 | 특정 IP 검색 |
| 사용자 ID | 특정 회원의 접속 기록 |
| 기기 유형 | desktop / mobile / tablet |
| 로그인 상태 | 로그인 사용자 / 비로그인 사용자 |
| 날짜 범위 | 시작일 ~ 종료일 (KST 기준) |
| device_source | web / app / pwa |
| 브라우저 | 브라우저별 필터 |
| OS | 운영체제별 필터 |
| device_id | 기기 ID 검색 |
| guest_id | 게스트 ID 검색 |

### 목록 표시 정보

- 접속 시각 (KST, 툴팁으로 UTC 표시), IP 주소, URL, 사용자(로그인 시), 기기 유형, OS, 브라우저, device_id, device_source (web/app/pwa 뱃지), OS 버전, 기기 모델

### 상세 페이지 (`/admin/access-logs/{accessLog}`)

- 시간 정보: KST + UTC + 클라이언트 타임존
- 사용자 정보: 로그인 사용자, 세션 ID, guest_id, device_id
- 기기 정보: device_source, device_model, os_version, app_version, build_version, raw User-Agent
- 네트워크/경로: IP, URL, referrer

---

## 미디어 관리 (`/admin/media`)

이미지 업로드 승인/거절을 관리합니다. 사이드바 "운영" 섹션에 위치하며, 대기 중인 건수가 뱃지로 표시됩니다.

### 승인 정책 (필수 숙지)

| 업로드 역할 | 초기 상태 | 설명 |
|-------------|-----------|------|
| admin / super_admin | `approved` | 자동 승인, 즉시 노출 |
| md | `approved` 또는 `pending` | MD 프로필/담당 클럽/담당 파티 업로드는 자동 승인. 그 외 일반 사용자 경로 업로드는 pending |
| user | `pending` | 관리자 승인 대기 |

**사용자 화면 노출 조건**: `approval_status = 'approved'` AND `is_visible = true` (이 두 조건 모두 충족해야 노출)

**관리자 확인 포인트**
- MD 업로드는 `/admin/media`에서 `uploaded_by_role=md` 필터로 바로 확인할 수 있습니다.
- 자동 승인된 MD 이미지도 관리자는 숨김/삭제할 수 있습니다.
- MD 계정이 비활성 또는 담당 매핑이 없으면 `/md-dashboard/*`, `/api/md/*`, `/api/upload/md-images` 접근이 차단됩니다.

### 기능

| 기능 | 설명 |
|------|------|
| 승인 (approve) | pending → approved. 즉시 사용자 화면에 노출 |
| 거절 (reject) | pending → rejected. 거절 사유 입력 필수 |
| 숨김 (hide) | approved/pending → hidden. 일시적으로 노출 차단 |
| 삭제 (delete) | 이미지 파일 + DB 레코드 영구 삭제 |
| 일괄 승인 (bulk-approve) | 체크박스로 여러 이미지 한 번에 승인 |

### 이미지 최적화

모든 이미지 업로드 시 `ImageOptimizer`가 자동으로 적용됩니다:
- EXIF 방향 보정
- 최대 1920px 리사이즈
- JPEG 82% 품질 압축 (PNG 투명 이미지는 PNG 유지)
- 400px 썸네일 자동 생성
- 업로드 제한: 최대 10MB

### 업로드 경로

- MD 프로필: `/storage/md/`
- 클럽: `/storage/clubs/`
- 파티: `/storage/parties/`
- 후기: `/storage/reviews/`
- 커뮤니티: `/storage/community/`
- 푸쉬: `/storage/push/`
- 썸네일: `{각 폴더}/thumbs/`

---

## 후기 관리 (`/admin/reviews`)

사용자/MD가 작성한 클럽/파티 후기를 관리합니다. 후기 작성자는 닉네임으로 표시됩니다.

| 기능 | 설명 |
|------|------|
| 목록 | 전체 후기 목록. 대상 유형, 별점, 숨김 상태 등 필터 |
| 숨김 토글 | `is_hidden` 토글 — 숨김 시 사용자 화면에서 즉시 제외 |

### 후기 이미지 정책

- 후기 텍스트는 작성 즉시 노출
- 후기에 첨부된 이미지는 미디어 승인 플로우를 따름 (pending → 관리자 승인 후 노출)

---

## 문의 관리 (`/admin/inquiries`)

클럽/파티에 대한 사용자 문의를 관리합니다. 사이드바에 대기 중 건수가 뱃지로 표시됩니다.

### 문의 상태 (7단계)

| 상태 | 설명 |
|------|------|
| `pending` | 미답변 (초기 상태) |
| `in_progress` | 처리 중 |
| `answered` | 답변 완료 |
| `reservation_confirmed` | 예약 확정 |
| `consultation_completed` | 상담 완료 |
| `closed` | 종료 |
| `hidden` | 숨김 |

### 상태 흐름

```
pending → in_progress → answered → reservation_confirmed → closed
                                  → consultation_completed → closed
                                                            → hidden
```

### 기능

| 기능 | 설명 |
|------|------|
| 목록 | 전체 문의 목록. 상태/대상/MD 필터 |
| 상세 | 문의 내용 + 답변 이력 (사용자/MD/관리자 답변 구분) |
| 상태 변경 | 관리자는 모든 상태로 변경 가능. 상태 변경 시 사용자에게 자동 알림 |
| MD 배정 | 자동 배정된 MD를 다른 MD로 변경 가능 |
| 답변 | 관리자 답변 작성. 답변 시 사용자에게 자동 알림 |
| 내부 메모 | `is_internal=true`로 작성하면 관리자끼리만 볼 수 있는 메모 |

### MD의 상태 변경 권한

MD는 `/md-dashboard/inquiries/{inquiry}/status`에서 다음 상태만 변경 가능:
- `in_progress` (처리 중)
- `answered` (답변 완료)
- `reservation_confirmed` (예약 확정)
- `consultation_completed` (상담 완료)

### MD 자동 배정 정책

- 사용자가 문의 작성 시, 해당 클럽/파티에 매핑된 active MD 중 가장 높은 priority의 MD가 자동 배정
- 매핑된 MD가 없으면 `assigned_md_id = null` → 관리자가 직접 처리
- 관리자가 언제든 MD를 재배정 가능

---

## 푸쉬 관리 (`/admin/push`)

타겟 사용자에게 인앱 알림을 발송하고 성과를 추적합니다. 사이드바 "마케팅" 섹션에 위치합니다.

### 캠페인 유형

| 유형 | 설명 |
|------|------|
| `notice` | 공지 |
| `event` | 이벤트 |
| `party` | 파티 알림 |
| `system` | 시스템 알림 |
| `marketing` | 마케팅 |

### 캠페인 상태

| 상태 | 설명 |
|------|------|
| `draft` | 작성 중 |
| `scheduled` | 예약됨 (scheduled_at 시각에 자동 발송) |
| `sending` | 발송 중 |
| `sent` | 발송 완료 |
| `failed` | 발송 실패 |
| `cancelled` | 취소됨 |

### 타겟 설정

| 타겟 유형 | 설명 |
|-----------|------|
| `all` | 전체 사용자 |
| `logged_in` | 로그인 사용자만 |
| `area` | 관심 지역(preferred_areas) 기준 |
| `genre` | 관심 장르(preferred_genres) 기준 |
| `custom` | 직접 지정 |

- **스태프 제외**: `exclude_staff` 옵션으로 MD/admin 제외 가능
- **알림 비활성 제외**: 알림을 끈 사용자에게는 발송하지 않음

### 기능

| 기능 | 설명 |
|------|------|
| 목록 | 캠페인 목록 + 통계 카드 (total, sent, scheduled, totalSent, totalClicked) |
| 생성 | 제목, 본문, 유형, 타겟, 링크, 이미지(파일 업로드 또는 URL 입력), 예약 시각 설정 |
| 상세 | 캠페인 정보 + 전달 통계 (target_count, sent_count, failed_count, clicked_count, inflow_count, click rate) |
| 즉시 발송 | `send-now` — 바로 발송 처리 |
| 예약 발송 | `scheduled_at` 설정 → `nite:send-scheduled-push` 커맨드가 매분 확인 후 발송 |
| 취소 | scheduled 상태 캠페인 취소 |

### 추적 지표

| 지표 | 설명 |
|------|------|
| clicked_count | 사용자가 알림을 클릭한 횟수 |
| inflow_count | 사용자가 푸쉬 링크를 통해 페이지에 진입한 횟수 (별도 추적) |
| click rate | clicked_count / sent_count |

### UTM 추적

캠페인 링크에 `?utm_campaign={campaign_id}`가 자동 부착됩니다.

---

## 운영정책: 신고 관리 (`/admin/moderation/reports`)

신고된 콘텐츠를 검토하고 처리합니다. 사이드바 "운영정책" 섹션에 위치하며, 대기 중(pending) 건수가 뱃지로 표시됩니다.

### 신고 대상

| 대상 | target_type |
|------|-------------|
| 커뮤니티 게시글 | `community_post` |
| 후기 | `review` |
| 미디어 | `media` |

### 신고 사유

| 사유 | 설명 |
|------|------|
| `abuse` | 욕설/비하 |
| `spam` | 스팸/광고 |
| `adult` | 성인 콘텐츠 |
| `false_info` | 허위 정보 |
| `privacy` | 개인정보 노출 |
| `other` | 기타 |

### 기능

| 기능 | 설명 |
|------|------|
| 목록 | 전체 신고 목록. 상태(pending/reviewed/dismissed) 필터 |
| 검토 완료 | pending → reviewed. 관리자가 확인 완료 표시 |
| 기각 | pending → dismissed. 부적절한 신고 기각 |

### 자동 숨김

- 동일 콘텐츠에 대한 신고가 임계값(기본 5)을 넘으면 `ModerationService`가 자동으로 해당 콘텐츠를 숨김 처리합니다.
- 임계값은 운영 정책(`/admin/moderation/policies`)에서 `auto_hide_report_threshold`로 설정합니다.
- 자동 숨김 처리 시 `moderation_logs`에 기록됩니다.

---

## 운영정책: 제재 관리 (`/admin/moderation/banned-users`)

사용자에 대한 제재(경고, 제한, 정지, 차단)를 관리합니다.

### 제재 유형

| 유형 | 설명 | 효과 |
|------|------|------|
| `warning` | 경고 | 기록만 남김 |
| `restrict_write` | 글쓰기 제한 | 커뮤니티/후기 작성 불가 (`canWrite()=false`) |
| `restrict_upload` | 업로드 제한 | 이미지 업로드 불가 (`canUpload()=false`) |
| `suspend` | 정지 | 글쓰기+업로드 불가, user.status=suspended |
| `ban` | 차단 | 글쓰기+업로드 불가, user.status=banned |

### 기능

| 기능 | 설명 |
|------|------|
| 제재 적용 | 사용자 선택, 제재 유형, 사유, 기간(일) 또는 영구 설정 |
| 제재 해제 | 활성 제재 비활성화, suspend/ban의 경우 user.status를 active로 복원 |
| 목록 | 전체 제재 목록. 유형/상태 필터 |

---

## 운영정책: 금칙어 관리 (`/admin/moderation/forbidden-words`)

커뮤니티 글쓰기 및 후기 작성 시 필터링할 금칙어를 관리합니다.

### 금칙어 필드

| 필드 | 설명 |
|------|------|
| `word` | 금칙어 단어/패턴 |
| `match_type` | exact (정확 일치), contains (포함), regex (정규식) |
| `action_type` | block (차단), mask (마스킹), review (검토 대기), warn (경고) |
| `category` | 분류 (선택) |
| `severity` | 심각도 (선택) |
| `is_active` | 활성 여부 |

### 기능

| 기능 | 설명 |
|------|------|
| 등록 | 금칙어 추가 (단어, 매칭 방식, 동작 유형 설정) |
| 삭제 | 금칙어 제거 |
| 활성/비활성 토글 | 일시적으로 금칙어 비활성화/재활성화 |

**주의**: 금칙어 추가/삭제/토글 시 캐시가 자동 초기화됩니다 (5분 캐시).

---

## 운영정책: 정책 설정 (`/admin/moderation/policies`)

운영 관련 설정값을 관리합니다. 모든 값은 5분 캐시됩니다.

| 정책 키 | 기본값 | 설명 |
|---------|--------|------|
| `auto_hide_report_threshold` | 5 | 자동 숨김 신고 임계값 |
| `spam_post_limit_per_hour` | 5 | 시간당 게시글 작성 제한 |
| `review_limit_per_day` | 10 | 일일 후기 작성 제한 |
| `image_upload_limit_per_post` | 5 | 게시글당 이미지 업로드 제한 |
| `forbidden_word_default_action` | block | 금칙어 기본 동작 |

---

## 관리 로그 (`/admin/logs`)

관리자 작업 기록. `AdminLog::record()`로 자동 기록됩니다.

- 클럽/파티 등록/수정/삭제
- 게시글 숨김/삭제
- 노출 순서 변경
- 회원 역할/상태 변경
- MD 프로필 등록/수정/삭제
- MD 매핑 추가/삭제
- 미디어 승인/거절/숨김/삭제
- 후기 숨김 토글
- 문의 상태 변경, MD 배정, 답변

---

## 사이드바 메뉴 구조

```
대시보드
콘텐츠
  ├── 클럽 관리
  └── 파티 관리
커뮤니티
  └── 게시글 관리
운영
  ├── 미디어 관리 [대기 N건]
  ├── 후기 관리
  └── 문의 관리 [대기 N건]
운영정책
  ├── 신고 관리 [대기 N건]
  ├── 제재 관리
  ├── 금칙어 관리
  └── 정책 설정
마케팅
  └── 푸쉬 관리
노출
  ├── 노출 관리
  └── 배너 관리
회원
  ├── 회원 관리
  ├── MD 관리
  └── MD 매칭
로그
  ├── 관리 로그
  └── 접속 로그 (목록 + 상세)
```

---

## 다국어 관련 참고

관리자 패널(`/admin/*`)은 **한국어 전용**입니다. 다국어 번역은 사용자 페이지에만 적용됩니다.
- 사용자 페이지: 시스템 UI(`__()`) + 사용자 콘텐츠(`trans_auto()`) 모두 번역됨
- 관리자 페이지: 번역 미적용 — 한국어로만 운영
- 언어 관련 설정은 `config/locales.php`에서 관리 (새 언어 추가/비활성화)
