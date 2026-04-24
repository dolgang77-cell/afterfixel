# VYBE 초보 PHP 개발자 유지보수 매뉴얼

> 이 문서는 "Laravel도 잘 모르고, 이 프로젝트도 처음 보는 PHP 개발자"가 실제로 유지보수할 수 있게 만드는 실전 운영 문서입니다.

---

## 1. 이 문서가 필요한 사람

이 문서는 아래 조건에 해당하는 사람을 기준으로 작성했습니다.

- PHP 문법은 겨우 읽을 수 있지만 Laravel은 익숙하지 않다.
- 프로젝트를 처음 받았고, 무엇부터 봐야 할지 모르겠다.
- 운영 서버에서 500 에러가 나면 어디부터 확인해야 할지 모른다.
- "클럽 상세 수정", "문의 답변", "관리자 푸시", "근처 유저 쪽지" 같은 요구가 들어왔을 때 어디를 손대야 하는지 빠르게 찾고 싶다.

이 문서 하나만 읽어도 아래 정도는 할 수 있게 만드는 것이 목표입니다.

- 개발 서버 실행
- DB 마이그레이션 적용
- 특정 기능의 코드 위치 찾기
- 500 에러 1차 진단
- 배포 전 최소 검증
- 운영 중 자주 터지는 포인트 회피

---

## 2. 이 서비스가 한 줄로 뭐냐

VYBE는 서울의 클럽, 파티, 투어, 문의, MD 운영, 위치 기반 근처 유저 기능까지 들어간 **모바일 우선 Laravel 웹앱**입니다.

정리하면 기능 축은 아래 여섯 개입니다.

- 사용자 화면: 홈, 클럽, 파티, 검색, 투어, 찜, 최근 본
- 문의 기능: 사용자 문의, 관리자 문의 인박스, MD 문의 응답
- 관리자 기능: 클럽/파티/미디어/회원/푸시/문의/운영 정책 관리
- MD 기능: 담당 클럽/파티 콘텐츠 관리, 문의 응답
- 알림 기능: 일반 알림, 문의 알림, 푸시 캠페인
- 위치 기능: 근처 사용자, 같은 클럽/같은 파티 1:1 메시지

---

## 3. 가장 먼저 이해해야 하는 구조

### 3-1. 이 프로젝트는 React SPA가 아니다

이 프로젝트는 대부분이 **Laravel Blade 서버 렌더링**입니다.

즉:

- 페이지 HTML은 서버가 만들어서 보낸다.
- 화면 이동은 대부분 `<a href>` 기반이다.
- 프론트 로직은 Alpine.js가 가볍게 붙는다.
- "컴포넌트"라고 해도 React 컴포넌트가 아니라 Blade partial인 경우가 많다.

### 3-2. 실제 코드의 큰 축

아래만 기억하면 된다.

- `routes/web.php`
  웹 페이지 URL 정의
- `routes/api.php`
  JSON API 정의
- `app/Http/Controllers`
  실제 요청 처리
- `app/Models`
  DB 테이블과 연결된 모델
- `app/Services`
  복잡한 비즈니스 로직
- `resources/views`
  Blade 화면 파일
- `database/migrations`
  DB 스키마 변경
- `docs`
  운영/개발 문서

### 3-3. 먼저 보는 순서

기능을 수정할 때는 아래 순서로 보면 된다.

1. URL이 어디서 열리는지 `routes/*.php` 확인
2. 연결된 Controller 확인
3. Controller가 호출하는 Service 확인
4. 데이터가 어떤 Model/Table을 쓰는지 확인
5. 마지막으로 View 확인

초보자가 가장 많이 하는 실수는 Blade만 먼저 열고 고치다가 로직을 놓치는 것이다.

---

## 4. 실제 폴더를 어떻게 읽어야 하나

### 4-1. 사용자 주요 화면

- 홈: `app/Http/Controllers/HomeController.php`
- 클럽 리스트/상세: `app/Http/Controllers/ClubController.php`
- 파티 리스트/상세: `app/Http/Controllers/PartyController.php`
- 검색: `app/Http/Controllers/SearchController.php`
- 투어 추천: `app/Http/Controllers/TourController.php`
- 마이페이지: `app/Http/Controllers/MyPageController.php`
- 문의: `app/Http/Controllers/InquiryController.php`

### 4-2. 관리자 화면

- 관리자 메인: `app/Http/Controllers/Admin/DashboardController.php`
- 문의 관리: `app/Http/Controllers/Admin/InquiryController.php`
- 푸시 관리: `app/Http/Controllers/Admin/PushCampaignController.php`
- 미디어 관리: `app/Http/Controllers/Admin/MediaController.php`
- 회원 관리: `app/Http/Controllers/Admin/UserController.php`
- MD 관리: `app/Http/Controllers/Admin/MdController.php`

### 4-3. MD 화면

- MD 대시보드: `app/Http/Controllers/MdDashboardController.php`
- MD API: `app/Http/Controllers/Api/MdApiController.php`

### 4-4. 위치/근처 메시지

- 위치/근처 사용자 API: `app/Http/Controllers/Api/NearbyUserApiController.php`
- 대화 API: `app/Http/Controllers/Api/ConversationApiController.php`
- 핵심 로직: `app/Services/NearbyMessagingService.php`

---

## 5. 자주 손대는 기능과 시작 파일

### 5-1. 클럽/파티 상세 화면 수정

보통 아래 파일부터 본다.

- `resources/views/clubs/show.blade.php`
- `resources/views/parties/show.blade.php`
- `resources/views/partials/inquiry-form.blade.php`
- `resources/views/partials/nearby-people-widget.blade.php`

화면 문구만 바꿀 것 같아도, 실제 데이터는 Controller에서 만들어서 내려주는 경우가 많다.

함께 자주 보는 파일:

- `app/Http/Controllers/ClubController.php`
- `app/Http/Controllers/PartyController.php`
- `app/Services/InquiryConversionService.php`
- `app/Services/AvailabilitySignalService.php`

### 5-2. 문의 기능 수정

사용자 문의 생성/추가 메시지:

- `app/Http/Controllers/InquiryController.php`
- `app/Models/Inquiry.php`
- `app/Models/InquiryReply.php`
- `app/Services/InquiryNotificationService.php`

관리자 문의 화면:

- `app/Http/Controllers/Admin/InquiryController.php`
- `resources/views/admin/inquiries/index.blade.php`
- `resources/views/admin/inquiries/show.blade.php`

MD 문의 화면:

- `app/Http/Controllers/MdDashboardController.php`
- `resources/views/md-dashboard/inquiries.blade.php`
- `resources/views/md-dashboard/inquiry-show.blade.php`

### 5-3. 푸시 캠페인 수정

- `app/Http/Controllers/Admin/PushCampaignController.php`
- `app/Services/PushService.php`
- `app/Models/PushCampaign.php`
- `resources/views/admin/push/index.blade.php`
- `resources/views/admin/push/form.blade.php`
- `resources/views/admin/push/show.blade.php`

### 5-4. 근처 유저/같은 클럽 메시지 수정

- `config/nearby-messaging.php`
- `app/Services/NearbyMessagingService.php`
- `app/Http/Controllers/Api/NearbyUserApiController.php`
- `app/Http/Controllers/Api/ConversationApiController.php`
- `resources/views/partials/nearby-people-widget.blade.php`
- `database/migrations/2026_04_20_180000_create_nearby_messaging_tables.php`

---

## 6. 개발 서버 실행법

### 6-1. 가장 기본

```bash
cd /var/www/nightlife
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

### 6-2. 서버가 안 뜨면

아래부터 본다.

```bash
php -v
composer --version
php artisan about
php artisan migrate:status
php artisan route:list
```

### 6-3. Blade 수정 후 꼭 해볼 것

```bash
php artisan view:clear
php artisan view:cache
```

초보자가 가장 자주 놓치는 부분이다.

Blade 문법 에러는 저장 직후 안 보이다가 실제 접근 시 500으로 터질 수 있다.

---

## 7. 운영 서버에서 500 에러가 났을 때 가장 먼저 할 일

### 7-1. 무조건 로그부터 본다

```bash
tail -n 200 storage/logs/laravel.log
```

또는 날짜별 로그가 있으면:

```bash
ls -lt storage/logs
tail -n 200 storage/logs/laravel-YYYY-MM-DD.log
```

### 7-2. 그 다음 확인 순서

1. `.env` 값이 잘못됐는지
2. 최근 마이그레이션이 빠졌는지
3. Blade 문법 에러인지
4. DB 테이블이 없는데 모델이 접근하는지
5. config/view cache 문제인지

### 7-3. 거의 항상 쓰는 명령

```bash
php artisan migrate:status
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
php artisan view:cache
```

### 7-4. 자주 있는 실제 원인

- 새 기능 코드는 들어갔는데 마이그레이션이 미적용
- Blade 안에서 복잡한 PHP 배열식을 바로 넣다가 파싱 오류
- `Schema::hasTable()` 같은 방어 없이 새 테이블 모델 접근
- config cache 때문에 `.env` 변경이 반영되지 않음

---

## 8. 마이그레이션을 만질 때 주의사항

### 8-1. 절대 함부로 하지 말 것

운영 DB에서 아래 실수는 치명적이다.

- 기존 컬럼 이름 의미 없이 변경
- 데이터 백필 없이 not null 강제
- 대량 테이블에 무거운 인덱스 한 번에 추가
- 운영 테이블 drop

### 8-2. 안전한 기본 흐름

1. 새 컬럼 nullable로 추가
2. 코드에서 새 컬럼이 없어도 안 죽게 방어
3. 운영 반영
4. 데이터 채우기
5. 필요하면 그 다음에 stricter constraint 추가

### 8-3. 확인 명령

```bash
php artisan migrate:status
php artisan migrate --pretend
```

---

## 9. 문의 기능에서 꼭 알아야 하는 룰

문의는 보기보다 로직이 많다.

### 9-1. 핵심 모델

- `Inquiry`
- `InquiryReply`

### 9-2. 상태 전이

최근 반영 기준으로 답변 등록은 `Inquiry::addReply()`로 통일돼 있다.

중요 포인트:

- 관리자/MD가 공개 답변하면 자동 상태 전이 가능
- 내부 메모는 상태를 바꾸지 않음
- 사용자가 다시 메시지를 남기면 `answered`, `consultation_completed` 상태가 `in_progress`로 재오픈될 수 있음

즉, 문의 상태가 이상하면 Controller 여러 군데보다 먼저 `app/Models/Inquiry.php`를 봐야 한다.

### 9-3. 새 문의 관련 500이 나면

아래를 먼저 확인한다.

```bash
php artisan migrate:status
php -l app/Models/Inquiry.php
php -l app/Http/Controllers/InquiryController.php
php -l app/Http/Controllers/Admin/InquiryController.php
php -l app/Http/Controllers/MdDashboardController.php
php artisan view:cache
```

---

## 10. 근처 유저 / 같은 클럽 메시지 기능에서 꼭 알아야 하는 룰

### 10-1. 이 기능은 그룹 채팅이 아니다

이건 오픈채팅방이 아니라 **같은 장소/근처 기반 1:1 메시지**다.

거리 버킷은 대략 아래 순서다.

- `same_club_active`
- `same_venue`
- `within_100m`
- `within_300m`

### 10-2. 기능이 안 보이면 어디를 봐야 하나

1. `.env`의 `NEARBY_MESSAGING_ENABLED=true`
2. 근처 메시지 마이그레이션 적용 여부
3. `php artisan config:clear`
4. `php artisan route:list --path=nearby-users`
5. `php artisan route:list --path=conversations`

### 10-3. 실제 점검 명령

```bash
php artisan migrate:status
php artisan schedule:list
php artisan route:list --path=nearby-users
php artisan route:list --path=conversations
```

### 10-4. 화면에서 안 뜨는 흔한 원인

- 로그인 안 됨
- 위치 권한 거부
- 두 사용자 모두 공개 상태가 아님
- 같은 클럽/같은 파티가 아님
- 차단 상태
- 위치 TTL 만료

### 10-5. 이 기능 관련 스케줄러

- `nearby:expire-stale-presence`
- `nearby:purge-expired-messages`

둘 다 매분 돌아야 한다.

---

## 11. 푸시 캠페인을 수정할 때 꼭 알아야 하는 룰

### 11-1. 주요 파일

- `PushCampaignController`
- `PushService`
- `PushCampaign`

### 11-2. 현재 구조

푸시는 기본적으로 DB 기반 인앱 알림 중심이다.
즉, FCM이 핵심이 아니라 `NiteNotification` 생성이 핵심이다.

### 11-3. 최근 추가된 리텐션 프리셋

현재는 아래 프리셋이 있다.

- 최근 본 후 미문의
- 찜 후 미문의
- 응답 도착 후 미확인

이 프리셋은 사용자마다 링크가 달라질 수 있다.

즉, 단순히 제목/본문만 바꾸는 기능이 아니라 **사용자별 개인화 링크**를 만들 수 있다.

### 11-4. 리텐션 대상이 0명인 게 버그는 아닐 수 있다

대상이 안 잡히는 이유:

- 최근 본 데이터 없음
- 찜 데이터 없음
- 문의 데이터 없음
- 알림 읽음 상태라 대상 제외
- 관련 테이블이 비어 있음

---

## 12. 절대 함부로 건드리면 안 되는 것

초보자가 가장 위험하게 건드리는 부분들이다.

### 12-1. `.env`

운영 서버 `.env`는 작은 오타 하나로도 전체 장애가 날 수 있다.

변경 후 반드시:

```bash
php artisan config:clear
```

### 12-2. 캐시/세션 드라이버

현재 환경에 따라 `file`, `database`가 섞여 있을 수 있다.
드라이버를 바꾸면 로그인 상태나 큐가 전부 영향을 받는다.

### 12-3. 미디어/이미지 경로

이미지는 원본만 있는 게 아니라 썸네일/변형 이미지 경로가 같이 얽혀 있다.
파일 삭제 코드를 함부로 바꾸면 화면 전체 이미지가 깨질 수 있다.

### 12-4. 관리자 권한 관련 미들웨어

관리자/MD 접근 제어는 여러 화면과 API에 걸쳐 있다.
접근 막힌다고 해서 바로 미들웨어를 빼지 말고 원래 역할/상태 조건을 먼저 봐야 한다.

---

## 13. 화면 하나 수정할 때의 추천 작업 순서

예: "클럽 상세에 문구 추가"

1. 라우트 확인
2. Controller 확인
3. View 확인
4. 필요한 데이터가 없으면 Controller에서 내려주기
5. Blade 수정
6. `php -l`과 `php artisan view:cache`
7. 실제 URL 열기

예: "관리자 문의 목록에 새 뱃지 추가"

1. `Admin/InquiryController`
2. `Inquiry` 모델 헬퍼
3. `resources/views/admin/inquiries/index.blade.php`
4. 문법 검사
5. 렌더 체크

예: "근처 사용자 목록 조건 추가"

1. `NearbyMessagingService`
2. `NearbyUserApiController`
3. `nearby-people-widget`
4. 위치/대화 API 응답 확인

---

## 14. 배포 전 최소 검증 체크리스트

코드 수정 후 최소한 아래는 확인한다.

### 14-1. 문법 검사

```bash
php -l app/Http/Controllers/SomeController.php
php -l app/Models/SomeModel.php
php -l app/Services/SomeService.php
```

### 14-2. Blade 캐시

```bash
php artisan view:cache
```

### 14-3. 라우트 확인

```bash
php artisan route:list
```

### 14-4. 마이그레이션 상태

```bash
php artisan migrate:status
```

### 14-5. 실제 URL 확인

최소 아래는 직접 본다.

- `/`
- `/clubs`
- `/parties`
- `/admin/login`
- `/admin/inquiries`
- `/md-dashboard/inquiries`
- `/docs`

---

## 15. 장애가 났을 때 보고 순서

### 15-1. 사용자 화면만 죽을 때

- 해당 URL 라우트
- 연결 Controller
- Blade
- 관련 Service
- 최근 마이그레이션

### 15-2. 관리자 화면만 죽을 때

- 관리자 미들웨어 통과 여부
- 해당 admin controller
- admin blade
- 관련 신규 테이블 존재 여부

### 15-3. API만 죽을 때

- `routes/api.php`
- JSON validation
- 인증 미들웨어
- config feature flag

### 15-4. 근처 메시지만 안 될 때

- 기능 플래그
- 근처 메시지 migration
- nearby routes
- schedule
- 위치 권한과 로그인 상태

---

## 16. 초보자가 자주 하는 실수와 방지법

### 실수 1. 화면만 수정하고 로직은 안 봄

방지:
항상 Controller와 Service를 같이 본다.

### 실수 2. 운영에서 바로 마이그레이션 전체 실행

방지:
필요한 migration만 먼저 확인하고, 영향 범위를 본다.

### 실수 3. `.env` 수정 후 cache 안 비움

방지:
변경 직후 `php artisan config:clear`

### 실수 4. 테스트용 데이터 남김

방지:
운영 서버에서는 롤백 가능한 방식이나 직접 삭제 계획까지 세운다.

### 실수 5. 사용자 변경사항을 덮어씀

방지:
수정 전 파일을 다시 읽고, 기존 변경을 절대 무시하지 않는다.

---

## 17. 진짜 급할 때 쓰는 명령 모음

```bash
php artisan about
php artisan migrate:status
php artisan route:list
php artisan schedule:list
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
php artisan view:cache
tail -n 200 storage/logs/laravel.log
```

근처 메시지 전용:

```bash
php artisan route:list --path=nearby-users
php artisan route:list --path=conversations
php artisan nearby:expire-stale-presence
php artisan nearby:purge-expired-messages
```

---

## 18. 마지막 조언

이 프로젝트는 "파일 하나만 고치면 끝"인 구조가 아니다.
대부분 기능은 아래 4개가 같이 움직인다.

- 라우트
- 컨트롤러
- 서비스
- Blade

문제가 생겼을 때는 절대 감으로 고치지 말고,

1. URL
2. Controller
3. Service
4. Model
5. View

순서로 추적하면 된다.

그리고 운영 반영 전에는 꼭 아래 세 가지를 반복한다.

- 문법 검사
- Blade 캐시 재생성
- 실제 URL 확인

이 세 개만 습관화해도 500 에러의 상당수를 막을 수 있다.
