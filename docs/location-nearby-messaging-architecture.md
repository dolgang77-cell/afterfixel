# 위치 기반 주변 사용자 + 30분 만료 메시지 설계안

기준 코드베이스: `/var/www/nightlife`  
작성일: 2026-04-20  
대상 서비스: 모바일 중심 클럽/파티 플랫폼 `VYBE`

이 문서는 현재 프로젝트의 `NearbyController`, `NiteNotification`, `Report`, `ModerationService`, `/admin/*`, `/md-dashboard/*` 구조를 유지하면서, 위치 기반 주변 사용자 노출과 30분 만료 1:1 메시지 기능을 실제 운영 가능한 수준으로 확장하기 위한 설계 문서다.

---

## [1. 기능 정의]

### A. 위치 기반 주변 사용자 표시 기능

- 기능 목적
  - 사용자가 클럽/파티 상세 또는 `Near Me` 흐름에서 “지금 이 근처에 누가 있는지”를 안전하게 확인하게 한다.
- 활성 조건
  - 로그인 사용자만 사용 가능
  - 위치 권한 허용 + 위치 공유 동의 + 주변 노출 ON 상태일 때만 활성화
- 노출 기준
  - `same_venue`: 같은 클럽 체크인 또는 같은 파티 입장 상태
  - `within_100m`: 내 근처 100m 이내
  - `within_300m`: 내 근처 300m 이내
  - `same_club_active`: 같은 클럽 입장 중
- 좌표 노출 정책
  - 위도/경도 원본값은 사용자 화면에 절대 노출하지 않는다.
  - 화면에는 아래 범주형 문구만 노출한다.
  - `같은 장소에 있어요`
  - `내 근처 100m 이내`
  - `내 근처 300m 이내`
  - `같은 클럽 입장 중`
- 사용자 제어
  - 위치 공유 `ON/OFF`
  - 주변 사용자에 나를 `표시/숨김`
  - 특정 클럽/파티에서만 일시 공개
  - 자동 만료: 마지막 위치 업데이트 후 10분 지나면 주변 목록에서 자동 제외
- 필터 구조
  - 성별: `male/female/mixed/hidden`
  - 연령대: `20-24`, `25-29`, `30+` 등 범주형
  - 관심사: `EDM`, `HipHop`, `Tour`, `Networking`, `Drinks`
  - 목적: `같이 놀 사람`, `정보 교환`, `투어 동행`, `대화만`
  - 언어: `ko/en/ja/zh`
  - 외국인 모드와 연계 가능
- 차단 정책
  - `user_blocks` 기준으로 상호 비노출
  - 신고 누적 임계 초과 사용자도 주변 사용자 목록에서 자동 숨김

### B. 인스턴트 메시지 기능

- 기능 목적
  - 주변 사용자 카드 또는 간단 프로필에서 1:1 대화를 빠르게 시작하게 한다.
- 시작 조건
  - 서로 차단 상태가 아니어야 함
  - 위치 공유 기반 노출로 발견했거나, 프로필 공개 범위 안에서 접근했을 때만 시작 가능
- 메시지 전송 방식
  - 기본: WebSocket
  - 대체: SSE fallback
  - 최후: 짧은 polling fallback
- 메시지 보존 정책
  - 메시지 생성 시 `expires_at = created_at + 30분`
  - 30분 경과 후 사용자 화면, 대화 목록, DB 조회, 캐시, 알림 표시에서 제외
  - 운영 로그/감사 로그는 본문 원문 저장 없이 최소 메타만 별도 보관
- 필수 기능
  - 읽음 여부
  - 신고
  - 차단
  - 대화 나가기
  - 대화 시작 전 안내 모달
- 최초 메시지 전 안내
  - `메시지는 30분 뒤 자동 삭제됩니다`
  - `정확한 위치는 상대에게 공개되지 않습니다`
  - `불쾌한 메시지는 신고/차단할 수 있습니다`
  - `계속 진행` / `취소`

---

## [2. 사용자 UX 흐름]

### 1) 클럽/파티 페이지 진입
- 화면명: 클럽 상세 / 파티 상세
- 사용자 행동: 페이지 진입 후 `근처 사용자 보기` 또는 `같은 장소 사용자` CTA 탭
- 시스템 동작: 로그인 여부, 위치 권한 상태, 공유 동의 상태 확인
- 예외 상황: 비로그인, 위치 권한 거부, 공유 OFF
- UX 주의사항: CTA는 고정 버튼보다 상세 상단 정보 블록 아래에 보조 액션으로 배치

### 2) 위치 권한 요청
- 화면명: 위치 권한 안내 바텀시트
- 사용자 행동: 권한 허용 / 나중에
- 시스템 동작: 브라우저 위치 권한 요청
- 예외 상황: 브라우저가 위치 권한을 차단함
- UX 주의사항: “정확한 위치는 공개되지 않습니다” 문구를 먼저 보여야 함

### 3) 위치 공유 동의 여부 선택
- 화면명: 위치 공유 설정 모달
- 사용자 행동: `이 장소에서만 공유`, `전체 OFF`, `숨김으로 보기`
- 시스템 동작: `nearby_visibility_settings`, `user_location_status` 갱신
- 예외 상황: GPS 없음, 권한 허용 후 좌표 획득 실패
- UX 주의사항: 기본값은 OFF, 사용자가 스스로 켜야 함

### 4) 주변 사용자 리스트 보기
- 화면명: 주변 사용자 리스트
- 사용자 행동: 리스트 탐색, 필터 적용
- 시스템 동작: 범주형 거리/장소 기준으로 사용자 카드 반환
- 예외 상황: 주변 사용자 없음, 차단으로 인한 빈 목록
- UX 주의사항: 좌표 대신 배지 문구만 표시

### 5) 특정 사용자 프로필 열람
- 화면명: 간단 프로필 바텀시트
- 사용자 행동: 카드 탭
- 시스템 동작: 닉네임, 관심사, 언어, 현재 상태, 공통 클럽/파티 정보 표시
- 예외 상황: 상대방이 방금 숨김 ON
- UX 주의사항: 본문보다 CTA는 `메시지 보내기` 1개만 유지

### 6) 메시지 보내기
- 화면명: 첫 메시지 안내 + 채팅방
- 사용자 행동: `메시지 보내기` 탭
- 시스템 동작: 대화방 생성 또는 기존 재사용
- 예외 상황: 상대방 차단, 스팸 제한 초과
- UX 주의사항: 첫 메시지 전 안전 안내 반드시 노출

### 7) 대화 진행
- 화면명: 채팅방
- 사용자 행동: 메시지 송수신
- 시스템 동작: 실시간 수신, 읽음 처리, 남은 만료 시간 계산
- 예외 상황: 네트워크 끊김, 상대방 숨김 전환, 차단 발생
- UX 주의사항: 남은 시간이 짧은 메시지는 가벼운 경고 배지로 표시 가능

### 8) 30분 후 메시지 자동 삭제
- 화면명: 채팅방 / 대화 목록
- 사용자 행동: 이전 메시지 재열람 시도
- 시스템 동작: 만료 메시지 제외, 마지막 메시지 재계산
- 예외 상황: 배치 지연
- UX 주의사항: UX는 “완전 비노출”이 기본, 첫 진입 시 정책 안내만 남김

### 9) 신고/차단/숨김 처리
- 화면명: 신고/차단 바텀시트
- 사용자 행동: 신고 사유 선택, 차단 확정
- 시스템 동작: `reports`, `user_blocks`, `moderation_logs` 기록
- 예외 상황: 중복 신고, 이미 차단된 사용자
- UX 주의사항: 신고와 차단을 분리하되 같은 액션 시트에서 접근 가능하게 설계

---

## [3. UI 구성안]

### 주변 사용자 리스트 화면
- 상단 영역: 현재 클럽/파티명, 위치 공유 배지, 필터 버튼, 숨김 토글
- 본문 영역: 사용자 카드 리스트
- CTA 버튼: `내 위치 공유 설정`, `새로고침`
- 상태 표시: `같은 장소`, `100m`, `300m`, `방금 활동`
- 빈 화면 처리: `주변에 공개 중인 사용자가 없습니다`
- 에러 처리: `위치 정보를 가져오지 못했습니다`
- 로딩 처리: skeleton card 6개
- 모바일 하단 고정 액션: 불필요

### 지도형 또는 리스트형 사용자 표시 화면
- 권장안: 리스트형 기본, 지도형은 2차
- 지도형에서도 핀은 좌표 대신 범주 버블만 표시
- 정확한 핀 드래그/개별 위치 추정은 금지

### 사용자 간단 프로필 카드
- 상단 영역: 아바타, 닉네임, 연령대, 언어
- 본문 영역: 관심사 태그, 현재 상태 배지, 공통 장소
- CTA 버튼: `메시지 보내기`, `숨기기`
- 상태 표시: `같은 장소`, `응답 빠름`, `외국인 모드`
- 빈 화면 처리: 없음
- 에러 처리: 비노출 전환 시 `프로필을 불러올 수 없습니다`
- 로딩 처리: shimmer avatar + text block
- 모바일 하단 고정 액션: 없음

### 채팅방 화면
- 상단 영역: 상대 닉네임, 공통 장소 라벨, 신고/차단 메뉴
- 본문 영역: 메시지 타임라인
- CTA 버튼: 입력창, 전송, 신고/차단
- 상태 표시: 읽음, 만료 예정, 전송 실패
- 빈 화면 처리: `첫 메시지를 보내면 30분 뒤 자동 삭제됩니다`
- 에러 처리: `메시지 전송 실패`
- 로딩 처리: 최근 메시지 skeleton
- 모바일 하단 고정 액션: 입력창은 하단 고정

### 메시지 만료 안내 UI
- 채팅방 상단 notice banner
- `이 대화의 메시지는 발송 후 30분 뒤 자동 삭제됩니다`
- 남은 시간 카운트다운은 메시지별이 아니라 최근 메시지 hover/tap 시만 노출

### 위치 공유 설정 화면
- 상단 영역: 위치 공유 토글
- 본문 영역: 공개 범위, 표시 모드, 자동 종료 시간
- CTA 버튼: `이 장소에서만 공유`, `전체 OFF`
- 상태 표시: `현재 공유 중`, `숨김 모드`
- 빈 화면 처리: 없음
- 에러 처리: GPS 불가 안내
- 로딩 처리: 토글 skeleton
- 모바일 하단 고정 액션: 저장 버튼 필요 없음, 즉시 저장 권장

### 신고/차단 바텀시트
- 상단 영역: 상대 닉네임
- 본문 영역: 신고 사유 라디오, 상세 입력
- CTA 버튼: `신고`, `차단`, `취소`
- 상태 표시: 중복 신고 안내
- 빈 화면 처리: 없음
- 에러 처리: 신고 실패 toast
- 로딩 처리: submit loading
- 모바일 하단 고정 액션: 예

### 관리자 검토 화면
- 상단 영역: 신고 상태 필터, 기간 필터, 위험도 필터
- 본문 영역: 신고 리스트, 신고된 메시지 메타, 사용자 제재 이력
- CTA 버튼: `경고`, `메시지 차단`, `위치 노출 정지`, `계정 정지`
- 상태 표시: 대기/검토중/완료
- 빈 화면 처리: `신규 신고 없음`
- 에러 처리: `검토 저장 실패`
- 로딩 처리: 표 skeleton
- 모바일 하단 고정 액션: 불필요, 관리자 PC 우선

---

## [4. 필수 정책 및 안전장치]

- 위치 공유 기본값은 `OFF`
- 위치 공유는 명시 동의 후에만 활성화
- 정확한 GPS 좌표 직접 노출 금지
- 위치는 `same_venue`, `within_100m`, `within_300m` 범주로만 표시
- 차단 사용자 상호 비노출
- 신고 누적 시 자동 숨김 또는 검토 대기
- 욕설/성희롱/위협 메시지는 `ForbiddenWordFilter` 확장 + ML moderation hook 제안
- 미성년자 보호
  - 서비스 자체가 성인 대상이면 성인 인증 또는 `19+` 게이트 필요
  - 미인증 사용자는 주변 사용자/메시지 기능 비활성 권장
- 과도한 메시지 발송 제한
  - 신규 대화 시작: 10분당 3회
  - 동일 사용자 연속 메시지: 30초당 5개 제한
  - 차단/신고 누적 사용자: 자동 rate tighten
- 스크린샷 안내
  - 완전 차단 불가
  - 채팅방 상단에 `캡처/공유에 주의하세요` 문구 고지
- 메시지 만료 정책 고지
  - 첫 대화 시작 전
  - 채팅방 상단 notice
  - 설정/약관 페이지 명시
- 위치정보/메시지 약관
  - 위치 기반 만남 기능 별도 동의
  - 메시지 자동 삭제 정책
  - 신고/차단 및 운영 검토 정책

---

## [5. 데이터 구조 설계]

### `user_location_status`
- 컬럼
  - `id`, `user_id`, `session_id`, `is_location_enabled`, `is_visible_nearby`, `visibility_mode`, `last_lat`, `last_lng`, `last_geohash`, `last_accuracy_m`, `last_venue_type`, `last_venue_id`, `last_seen_at`, `expires_at`
- 타입
  - bool, string, decimal, datetime
- 용도
  - 사용자 최신 위치 상태 저장
- 인덱스 추천
  - `(user_id)` unique
  - `(last_geohash, expires_at)`
  - `(last_venue_type, last_venue_id, expires_at)`
- 만료 처리
  - `expires_at <= now()` 시 주변 노출 제외

### `venue_checkins`
- 컬럼
  - `id`, `user_id`, `venue_type`, `venue_id`, `checked_in_at`, `checked_out_at`, `source`
- 용도
  - 같은 클럽/파티 입장 상태 추적
- 인덱스
  - `(venue_type, venue_id, checked_out_at)`
  - `(user_id, checked_out_at)`

### `nearby_visibility_settings`
- 컬럼
  - `id`, `user_id`, `share_mode`, `show_age_band`, `show_gender`, `show_language`, `allow_message_requests`, `allow_same_venue_only`, `auto_hide_after_minutes`
- 용도
  - 노출/메시지 허용 설정
- 인덱스
  - `(user_id)` unique

### `user_blocks`
- 컬럼
  - `id`, `user_id`, `blocked_user_id`, `reason`, `created_at`
- 용도
  - 상호 비노출 및 메시지 차단
- 인덱스
  - unique `(user_id, blocked_user_id)`
  - `(blocked_user_id, user_id)`

### `conversations`
- 컬럼
  - `id`, `type`, `user_one_id`, `user_two_id`, `created_by`, `last_message_id`, `last_message_at`, `last_message_expires_at`, `status`
- 용도
  - 1:1 대화방 메타
- 인덱스
  - unique `(user_one_id, user_two_id, type)`
  - `(last_message_at desc)`

### `messages`
- 컬럼
  - `id`, `conversation_id`, `sender_id`, `body`, `body_redacted`, `status`, `read_at`, `created_at`, `expires_at`, `deleted_at`
- 용도
  - 30분 만료 메시지
- 인덱스
  - `(conversation_id, created_at desc)`
  - `(expires_at, deleted_at)`
  - `(sender_id, created_at)`
- 자동 삭제 방식
  - soft delete vs hard delete 비교
  - soft delete 장점: 장애 시 복구 가능, 운영 감사 용이
  - soft delete 단점: 개인정보 최소 보관 원칙에 불리
  - 권장안: 본문은 hard delete, 운영용 메타 로그만 별도 `message_moderation_events`에 저장

### `message_reports`
- 컬럼
  - `id`, `message_id`, `conversation_id`, `reporter_id`, `reported_user_id`, `reason`, `detail`, `status`, `reviewed_by`, `reviewed_at`
- 인덱스
  - `(status, created_at)`
  - `(reported_user_id, status)`

### `moderation_logs`
- 기존 테이블 재사용
- 메시지/위치 노출 관련 action 추가
  - `message_hidden`
  - `location_visibility_suspended`
  - `message_rate_limited`

### `notification_queue`
- 기존 `nite_notifications` + queue jobs 조합 권장
- 별도 큐 테이블이 필요하면
  - `id`, `user_id`, `type`, `payload`, `scheduled_at`, `dispatched_at`, `status`

---

## [6. 백엔드 구현 요구사항]

### API 설계

`POST /api/me/location-status`
- 요청값: `lat`, `lng`, `accuracy_m`, `venue_type?`, `venue_id?`, `share_mode`, `is_visible_nearby`
- 응답값: `visibility_state`, `bucket_label`, `expires_at`
- 권한 체크: 로그인 + active user
- 예외 처리: 권한 OFF, GPS 없음, validation fail
- 보안: 원본 좌표는 응답에 포함 금지

`PATCH /api/me/location-visibility`
- 요청값: `is_location_enabled`, `is_visible_nearby`, `share_mode`
- 응답값: 현재 상태

`GET /api/venues/{type}/{id}/nearby-users`
- 요청값: `distance_bucket?`, `gender?`, `age_band?`, `interest?`, `purpose?`
- 응답값: 가공된 주변 사용자 카드 목록
- 권한 체크: 로그인 + 위치 동의 + venue 접근 허용

`POST /api/conversations`
- 요청값: `target_user_id`, `source_context_type`, `source_context_id`
- 응답값: `conversation_id`
- 권한 체크: 로그인, active, block check

`GET /api/conversations`
- 응답값: 대화 목록, 마지막 메시지 미리보기, unread count
- 권한 체크: 참여자 only

`GET /api/conversations/{conversation}`
- 응답값: 만료 제외 메시지 목록
- 권한 체크: 참여자 only

`POST /api/conversations/{conversation}/messages`
- 요청값: `body`
- 응답값: `message_id`, `expires_at`
- 권한 체크: 참여자 only, rate limit, block check

`POST /api/conversations/{conversation}/read`
- 요청값: `last_seen_message_id`
- 응답값: success

`POST /api/messages/{message}/report`
- 요청값: `reason`, `detail`
- 응답값: success

`POST /api/users/{user}/block`
- 요청값: `reason`
- 응답값: success

`DELETE /api/users/{user}/block`
- 응답값: success

`PATCH /admin/messages/{message}/moderate`
- 요청값: `action=hide|warn|suspend_visibility|ban`
- 권한 체크: admin

### WebSocket / SSE / Polling 비교

- WebSocket
  - 장점: 실시간 채팅 적합
  - 단점: 인프라 추가 필요
  - 권장: 기본 채택
- SSE
  - 장점: 구현 단순, 서버푸시 적합
  - 단점: 양방향 입력은 별도 POST 필요
  - 권장: fallback
- Polling
  - 장점: 가장 단순
  - 단점: 배터리/트래픽 비효율
  - 권장: 최후 fallback

### 위치정보 갱신 주기
- 앱 foreground + nearby 화면 열림: 30~60초
- 상세페이지 체류 중: 60~90초
- background: 갱신 중지
- venue check-in 상태만 유지하고 raw GPS는 정기 갱신 최소화

### 배터리/트래픽 절약
- 사용자가 nearby 화면을 벗어나면 위치 갱신 중단
- geohash 단위 변화 없으면 서버 업데이트 생략
- `accuracy_m > 150m`면 coarse mode만 유지
- 메시지 read receipt debounce 3초

### 확장 고려사항
- `geohash_prefix` 기반 샤딩 가능 구조
- conversation/message는 별도 DB 또는 파티셔닝 준비
- 만료 메시지 삭제 job은 큐 worker 수평 확장 가능해야 함

---

## [7. 메시지 30분 자동 삭제 설계]

- 메시지 생성 시
  - `expires_at = now()->addMinutes(30)`
- 조회 시
  - 모든 message query 기본 조건: `whereNull(deleted_at)->where('expires_at', '>', now())`
- 정기 배치
  - `php artisan messages:purge-expired --chunk=1000`
  - 1분 주기 스케줄 실행
- 삭제 대상
  - DB `messages`
  - Redis conversation cache
  - 대화 목록 last_message cache
  - 브로드캐스트 캐시
  - 미전송 알림 큐 payload
- 대화방 마지막 메시지 만료 시
  - 다음 최신 비만료 메시지로 `conversations.last_message_id`, `last_message_at`, `last_message_expires_at` 재계산
  - 남은 메시지가 없으면 null 처리
- UX 비교
  - `이 메시지는 만료되어 삭제되었습니다`
    - 장점: 삭제 정책 체감 쉬움
    - 단점: 화면이 지저분해짐
  - 완전 비노출
    - 장점: 개인정보 최소 보관 원칙에 유리
    - 단점: 사용자 혼란 가능
  - 권장안: 메시지 본문은 완전 비노출, 채팅방 상단 정책 배너로 설명
- 삭제 시점 오차 최소화
  - 조회 필터에서 즉시 제외
  - 배치는 실제 물리 삭제 담당
  - 즉, 사용자 체감은 30분 즉시, DB 정리는 배치 수 초~수 분 허용
- 장애 대비
  - 조회 필터가 1차 안전망
  - purge job 실패 시 다음 분 재시도
  - 6시간 이상 만료 데이터 잔존 시 admin alert

---

## [8. 프론트엔드 구현 요구사항]

### 컴포넌트 단위
- `NearbyPermissionSheet`
- `LocationShareToggleCard`
- `NearbyUserList`
- `NearbyUserCard`
- `NearbyFiltersSheet`
- `ConversationList`
- `ChatRoom`
- `MessageComposer`
- `MessagePolicyNotice`
- `ReportBlockSheet`
- `LocationVisibilitySettings`

### UX 포인트
- 위치 권한 요청 UX
  - 브라우저 시스템 팝업 전에 사전 안내 모달 필수
- 위치 공유 상태 배지
  - `공유 중`, `숨김`, `이 장소만`
- 근처 사용자 카드
  - 거리 대신 `100m 이내`, `같은 장소`
- 메시지 남은 시간 표시
  - 전체 메시지에 상시 노출 금지
  - 최근 메시지 또는 길게 눌렀을 때만 `18분 후 삭제`
- 자동 삭제 전 안내
  - 채팅방 상단 notice
- 차단/신고 버튼 위치
  - 채팅방 우상단 kebab menu
- 빈 대화방 처리
  - 정책 notice + 첫 메시지 안내
- 만료 후 채팅창 갱신
  - WebSocket event `messages.expired` 또는 reopen fetch
- 스크롤 최적화
  - 최근 50개 우선 로드, 위로 당겨 과거 로딩
- 새 메시지 수신 UI
  - 하단 sticky `새 메시지 2개`
- 네트워크 재연결
  - reconnect backoff: 1s, 3s, 5s, 10s

---

## [9. 관리자 기능]

### 신고 내역 보기
- 목적: 악성 메시지/스토킹 대응
- 화면 구성: 신고 리스트, 신고 사유, 신고자, 피신고자, 시간
- 필터: 상태, 사유, 누적 신고 수, 기간
- 액션 버튼: 검토 시작, 경고, 차단, 위치공유 정지
- 주의사항: 원문 메시지는 만료 정책과 별개로 메타 중심 접근

### 메시지 신고 상세
- 목적: 개별 사건 판단
- 화면 구성: 대화 컨텍스트, 신고 사유, 관련 사용자 이력
- 필터: 메시지 유형, 자동 필터 적중 여부
- 액션 버튼: dismiss, warn, temp-ban
- 주의사항: 관리자 원문 접근은 제한된 역할만 허용

### 사용자 차단/정지
- 목적: 재발 방지
- 화면 구성: 현재 제재 상태, 최근 신고, 위치/메시지 악용 지표
- 필터: active, suspended, banned
- 액션 버튼: 경고, 24시간 위치공유 금지, 7일 메시지 금지, 계정 정지
- 주의사항: 남용 방지를 위한 사유 필수

### 위치 공유 악용 모니터링
- 목적: 스토킹/상시 추적 방지
- 화면 구성: 특정 유저 반복 조회 횟수, 반복 대화 시도 횟수
- 필터: 사용자, venue, 시간대
- 액션 버튼: 위치 노출 중지

### 비정상 메시지 발송량 모니터링
- 목적: 스팸 탐지
- 화면 구성: 시간당 전송량, 신규 대화 시작량, 차단률
- 액션 버튼: rate limit 강화, 경고

### 특정 사용자 대화 접근 권한 정책
- 목적: 개인정보 최소 접근
- 원칙: super_admin 또는 지정된 trust & safety 운영자만 제한 접근

### 자동 제재 룰 설정
- 목적: 운영 자동화
- 예시 룰
  - 24시간 내 신고 3건 이상 → 위치 노출 자동 OFF
  - 10분 내 신규 대화 5개 이상 → 메시지 제한
  - 금칙어 2회 적중 → 검토 대기

### 로그 기록 / 통계 대시보드
- 목적: 사고 추적 + 기능 성과 측정
- KPI
  - nearby opt-in rate
  - message start rate
  - 신고율
  - block rate
  - 30분 내 reply rate

---

## [10. 우선순위 개발 로드맵]

| 항목 | 설명 | 우선순위 | 개발 난이도 | 기대 효과 |
|---|---|---:|---|---|
| 위치 공유 ON/OFF | 사용자 동의 기반 노출 제어 | 1차 | 중 | 개인정보 보호 기반 확보 |
| 주변 사용자 노출 | 같은 장소/100m/300m 범주 노출 | 1차 | 중 | 현장 매칭 경험 생성 |
| 1:1 메시지 | conversation + message + 실시간 송수신 | 1차 | 상 | 핵심 상호작용 완성 |
| 30분 만료 삭제 | expires_at + 조회 제외 + purge job | 1차 | 상 | 개인정보 최소 보관 |
| 읽음 처리 | last_seen 기반 읽음 상태 | 2차 | 중 | 메시지 품질 향상 |
| 신고/차단 | `reports`, `user_blocks` 연동 | 2차 | 중 | 안전장치 확보 |
| 필터 | 성별/연령대/관심사/언어 필터 | 2차 | 중 | 매칭 품질 향상 |
| 관리자 신고 관리 | 신고 큐, 메시지 제재, 위치 제재 | 2차 | 중 | 운영 대응 시간 단축 |
| 푸시/알림 | 새 메시지, 메시지 요청, 신고 상태 | 2차 | 중 | 재진입/응답률 개선 |
| 추천 사용자 | 공통 관심사/언어/장르 기반 | 3차 | 중 | 전환율 향상 |
| 목적 기반 매칭 | 투어 동행/대화만/정보교환 | 3차 | 중 | 품질 높은 연결 |
| 정교한 노출 제어 | venue-only, age band-only 등 | 3차 | 중 | 신뢰도 향상 |
| 운영 자동화 | 신고 누적 자동 숨김, 자동 제한 | 3차 | 중 | 운영 비용 절감 |

---

## [11. 실제 구현 지시]

### 필요한 파일 구조 제안

```text
app/Models/
  Conversation.php
  Message.php
  MessageReport.php
  UserBlock.php
  UserLocationStatus.php
  VenueCheckin.php
  NearbyVisibilitySetting.php

app/Http/Controllers/
  NearbyUserController.php
  ConversationController.php
  MessageController.php
  UserBlockController.php
  LocationVisibilityController.php

app/Http/Controllers/Admin/
  MessageModerationController.php
  NearbyModerationController.php

app/Services/
  NearbyUserService.php
  MessageExpiryService.php
  MessageRealtimeService.php
  MessageSafetyService.php

app/Console/Commands/
  PurgeExpiredMessages.php
  ExpireStaleLocationPresence.php

resources/views/
  nearby/users/index.blade.php
  nearby/users/_card.blade.php
  chat/index.blade.php
  chat/show.blade.php
  components/chat/*.blade.php
  admin/messages/*.blade.php
```

### 추가해야 할 DB migration 목록
- `create_user_location_status_table`
- `create_venue_checkins_table`
- `create_nearby_visibility_settings_table`
- `create_user_blocks_table`
- `create_conversations_table`
- `create_messages_table`
- `create_message_reports_table`
- `create_message_moderation_events_table`
- `add_message_policy_columns_to_users_or_settings_table`

### API route 목록
- `POST /api/me/location-status`
- `PATCH /api/me/location-visibility`
- `GET /api/venues/{type}/{id}/nearby-users`
- `POST /api/conversations`
- `GET /api/conversations`
- `GET /api/conversations/{conversation}`
- `POST /api/conversations/{conversation}/messages`
- `POST /api/conversations/{conversation}/read`
- `POST /api/messages/{message}/report`
- `POST /api/users/{user}/block`
- `DELETE /api/users/{user}/block`

### 프론트 화면/컴포넌트 목록
- 주변 사용자 목록
- 위치 공유 권한 시트
- 위치 공유 설정 모달
- 간단 프로필 카드
- 대화 목록
- 채팅방
- 신고/차단 시트
- 만료 정책 notice

### 관리자 페이지 추가 목록
- `/admin/messages/reports`
- `/admin/messages/conversations`
- `/admin/messages/users`
- `/admin/nearby/abuse`
- `/admin/nearby/policies`

### 환경변수 추가 항목
- `NEARBY_ENABLED=true`
- `NEARBY_LOCATION_TTL_MINUTES=10`
- `MESSAGE_TTL_MINUTES=30`
- `MESSAGE_WEBSOCKET_DRIVER=reverb`
- `MESSAGE_RATE_LIMIT_PER_10_MIN=20`
- `MESSAGE_NEW_CONVERSATION_LIMIT_PER_10_MIN=3`
- `NEARBY_MAX_DISTANCE_METERS=300`
- `MESSAGE_CONTENT_MODERATION_ENABLED=true`

### 스케줄러 / cron / queue worker 설정
- `* * * * * php artisan schedule:run`
- schedule
  - `messages:purge-expired` 매분
  - `nearby:expire-stale-presence` 매분
- queue worker
  - realtime broadcast
  - notification dispatch
  - moderation async review

### 보안 체크리스트
- 위치 공유 기본 OFF
- 원본 좌표 응답 금지
- 차단 사용자 상호 비노출
- 메시지 만료 조회 필터 기본 적용
- 메시지 본문 hard delete 정책
- 관리자 원문 접근 제한
- rate limit 전 API 적용
- 신고/차단 로깅 필수

### 테스트 체크리스트
- 위치 OFF 상태에서 nearby 접근 차단
- 차단 사용자 상호 비노출
- 30분 경과 메시지 조회 제외
- purge job 후 DB 물리 삭제
- 대화방 마지막 메시지 만료 재계산
- 신고 누적 시 자동 제재
- 정렬/필터/채팅 동시 사용 시 모바일 UI 깨짐 없음
- 네트워크 재연결 후 메시지 중복 수신 없음

---

## 기존 서비스와 충돌 가능한 부분

- 현재 `routes/api.php`는 공개 API와 MD API 중심이다. 메시지 API 추가 시 auth middleware 체계 정리가 필요하다.
- 현재 `Report`는 범용 신고 모델이므로 `target_type = message` 확장만으로 재사용 가능하지만, 관리자 화면 필터가 메시지 타입을 처리하도록 수정해야 한다.
- 현재 `NiteNotification`은 웹 알림 중심이다. 메시지 알림이 급증하면 별도 채널 분리 또는 unread counter 최적화가 필요하다.
- 현재 `NearbyController`는 장소 추천용이다. 주변 사용자 기능은 별도 `NearbyUserController`로 분리하는 편이 안전하다.
- 현재 `ModerationService`는 커뮤니티/리뷰 중심 자동숨김 로직만 있다. 메시지/위치 악용 룰은 별도 메서드 확장이 필요하다.
