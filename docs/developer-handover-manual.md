# VYBE 개발자 인수인계 매뉴얼

> 이 문서는 이 프로젝트를 처음 받는 개발자가 **혼자 읽고, 이해하고, 수정하고, 배포까지** 할 수 있도록 작성된 인수인계 문서입니다.

---

## A. 프로젝트 한눈에 보기

### 이 서비스가 뭔가요?

**VYBE**는 서울의 클럽/파티/투어 루트를 추천하는 **모바일 웹앱**입니다.

쉽게 말하면:
- 사용자가 "오늘 밤 어디 갈까?" 하면 → 시간, 위치, 취향에 맞는 클럽/파티를 추천해주는 서비스
- 관리자가 클럽/파티 정보를 등록하고 운영하는 어드민 패널이 포함되어 있음
- Android 앱(TWA)으로도 패키징되어 있음

### 사용자에게 제공하는 기능

| 기능 | 설명 |
|------|------|
| 홈 | 오늘밤 추천, 오늘의 파티, 인기 클럽, 내 근처 추천을 한 화면에서 확인 |
| 파티 | 날짜/지역/장르로 파티를 검색하고 상세 정보 확인 |
| 클럽 | 지역/장르로 클럽을 찾고, 리뷰/평점/드레스코드 확인 |
| 투어AI | 지역/시간/예산/장르를 입력하면 AI가 최적 루트(3개)를 추천 |
| 오늘밤 추천 | 현재 시간 기준으로 "지금 갈만한 곳"을 자동 추천 |
| Near Me | GPS 또는 지역 선택 기반으로 가까운 클럽/파티 추천 |
| 찜/최근본 | 마음에 드는 곳을 찜하고, 최근 본 항목을 이어보기 |
| 관심 설정 | 선호 지역/장르/예산/외국인 모드를 설정하면 맞춤 추천 |
| 알림 | 파티 리마인더, 새 파티 매칭, 오늘밤 추천 알림, 문의 상태 변경 알림 |
| 통합 검색 | 클럽/파티/MD를 한 번에 검색. 인기 키워드 표시 |
| 커뮤니티 | 클럽 후기, 팁, 실시간 제보를 작성/신고 (이미지 첨부 가능, 최대 5장) |
| 마이페이지 | 찜, 최근 본, 관심 설정, 알림 설정 관리 |

### 관리자 기능

| 기능 | URL |
|------|-----|
| 대시보드 | `/admin` — 통계, 신고 게시글, 관리 로그 |
| 클럽 CRUD | `/admin/clubs` — 등록/수정/삭제/상태변경 |
| 파티 CRUD | `/admin/parties` — 등록/수정/삭제/일괄상태변경 |
| 게시글 관리 | `/admin/posts` — 신고 검수, 숨김/삭제, 일괄 처리 |
| 노출 관리 | `/admin/exposure` — 클럽 정렬 순서, 파티 고정, 배너 현황 |
| 배너 관리 | `/admin/banners` — 위치별 배너 CRUD |
| 미디어 관리 | `/admin/media` — 이미지 승인/거절/숨김/삭제, 대기 건수 뱃지 |
| 후기 관리 | `/admin/reviews` — 후기 목록/필터, 숨김 토글 |
| 문의 관리 | `/admin/inquiries` — 문의 목록, 상태 변경(7단계), MD 배정, 내부 메모 |
| 푸쉬 관리 | `/admin/push` — 푸쉬 캠페인 CRUD, 발송, 통계 |
| 신고 관리 | `/admin/moderation/reports` — 신고 목록, 상태 변경 (pending/reviewed/dismissed) |
| 제재 관리 | `/admin/moderation/banned-users` — 회원 제재 (경고/제한/정지/차단) |
| 금칙어 관리 | `/admin/moderation/forbidden-words` — 금칙어 등록/삭제/토글 |
| 운영 정책 | `/admin/moderation/policies` — 자동 숨김 임계값, 스팸 제한 등 설정 |
| 회원 관리 | `/admin/users` — 검색, 역할/상태 변경, 활동 요약 |
| MD 관리 | `/admin/md` — MD 프로필 CRUD |
| MD 매핑 | `/admin/md-mappings` — MD ↔ 클럽/파티 매칭 관리 |
| 접속 로그 | `/admin/access-logs` — IP/기기/로그인 상태/device_source/device_id별 조회, 상세 페이지 |
| 통합 검색 | `/search` — 클럽/파티/MD 통합 검색, 인기 키워드, 검색 로그 |
| 이용약관 | `/terms` — 10개 조항 (법률 검토 필요 초안) |
| 개인정보처리방침 | `/privacy` — 9개 섹션 (법률 검토 필요 초안) |
| 로그 | `/admin/logs/*` — 관리 로그, 클릭 로그, 추천 로그, 알림 로그 |

### MD 대시보드 기능

| 기능 | URL |
|------|-----|
| 대시보드 | `/md-dashboard` — MD 전용 관리 화면 |
| 프로필 수정 | `/md-dashboard/profile` — MD 본인 프로필 편집 |
| 클럽 관리 | `/md-dashboard/clubs` — 배정된 클럽 목록/콘텐츠 수정 |
| 파티 관리 | `/md-dashboard/parties` — 배정된 파티 목록/콘텐츠 수정 |
| 후기 확인 | `/md-dashboard/reviews` — 배정 클럽/파티의 후기 열람 |
| 문의 관리 | `/md-dashboard/inquiries` — 배정된 문의 목록/답변, 상태 변경 |
| 미디어 | `/md-dashboard/media` — 본인 업로드 이미지 상태 확인 |

### 전체 구조 (쉬운 말)

```
[사용자 모바일 브라우저/앱]
        ↕
[Nginx 웹서버]
        ↕
[PHP-FPM] → [Laravel 13 애플리케이션]
                    ↕
              [MySQL 데이터베이스]
```

- **프론트엔드**: Laravel Blade 템플릿 + Tailwind CSS(CDN) + Alpine.js(CDN). SPA가 아닌 서버 렌더링.
- **백엔드**: Laravel 13 (PHP 8.3). API와 웹 페이지 모두 같은 서버에서 처리.
- **데이터베이스**: MySQL. 세션/캐시/큐도 DB 사용.
- **앱**: TWA(Trusted Web Activity) 방식으로 웹을 Android 앱으로 래핑.

---

## B. 기술 스택

| 계층 | 기술 | 버전 | 왜 이 기술을 썼나 |
|------|------|------|------------------|
| 백엔드 | Laravel | 13.x | 한국에서 가장 많이 쓰는 PHP 프레임워크. 빠른 MVP 개발 |
| PHP | PHP | 8.3 | Laravel 13 요구사항 |
| 프론트 HTML | Blade | - | Laravel 기본 템플릿 엔진. 서버에서 HTML 생성 |
| 프론트 CSS | Tailwind CSS | 4.x | CDN으로 로드. 별도 빌드 불필요 |
| 프론트 JS | Alpine.js | 3.x | CDN으로 로드. 간단한 인터랙션 처리 |
| DB | MySQL | 8.x | 관계형 DB. 세션/캐시/큐도 DB 테이블 사용 |
| 웹서버 | Nginx | 1.24 | 정적 파일 + PHP-FPM 리버스 프록시 |
| PHP 프로세스 | PHP-FPM | 8.3 | 전용 풀(nightlife.conf)로 운영 |
| 큐 | Laravel Queue | DB driver | 백그라운드 작업 처리 (알림 발송 등) |
| 앱 | TWA | - | 웹앱을 Android 앱으로 래핑. 636KB APK |
| PWA | Service Worker | - | 오프라인 지원, 홈 화면 추가 |

### 신입이 특히 알아야 할 점

1. **SPA가 아닙니다.** React/Vue 같은 프론트엔드 프레임워크를 쓰지 않습니다. 모든 HTML은 서버에서 Blade로 생성합니다. 페이지 이동은 전통적인 `<a href>` 방식입니다.

2. **Tailwind CSS는 CDN입니다.** `npm run build`로 CSS를 빌드하지 않습니다. `layouts/app.blade.php`의 `<script src="https://cdn.tailwindcss.com">` 태그가 모든 CSS를 처리합니다.

3. **Alpine.js도 CDN입니다.** `x-data`, `x-show`, `@click` 같은 속성이 보이면 그것이 Alpine.js입니다. 별도 설치 없이 HTML 속성만으로 동작합니다.

4. **세션 + 회원 기반입니다.** 로그인 없이도 사용 가능하며, 개인화 데이터(찜, 최근 본, 관심 설정)는 `session_id`로 연결됩니다. 회원가입/로그인 시 세션 데이터가 회원 계정으로 병합됩니다.

5. **닉네임 정책.** 회원가입 시 닉네임 필수 (2~20자, 영문/한글/숫자/언더스코어). 가입 후 변경 불가. 커뮤니티 글/후기 작성 시 닉네임이 자동으로 사용됩니다. 닉네임 중복 확인 API: `GET /auth/check-nickname?nickname=`.

6. **역할 기반 접근 제어.** `user`, `md`, `admin`, `super_admin` 4개 역할이 있습니다. `AdminMiddleware`는 `isAdmin()` + `isActive()` 모두 확인합니다. `MdMiddleware`는 `isMd()` + `isActive()` + mdProfile 보유 여부를 확인합니다.

9. **사용자 제재 시스템.** User 모델에 `canWrite()`, `canUpload()` 메서드가 추가되었습니다. 관리자가 제재(경고/글쓰기 제한/업로드 제한/정지/차단)를 적용하면, 커뮤니티 글쓰기와 후기 작성 시 자동 차단됩니다.

10. **금칙어 필터.** `ForbiddenWordFilter` 서비스가 커뮤니티 글쓰기(`CommunityController@store`)와 후기 작성(`ReviewController@store`)에 적용됩니다. exact/contains/regex 매칭을 지원하며 5분 캐시를 사용합니다.

11. **신고 및 자동 숨김.** 게시글/후기/미디어 신고 시 `ModerationService::processReport()`가 호출되어 신고 누적 건수가 임계값(기본 5)을 넘으면 자동 숨김 처리됩니다. 임계값은 `moderation_policies` 테이블에서 설정합니다.

12. **법적 문서.** `/terms`(이용약관)와 `/privacy`(개인정보처리방침)는 초안 상태이며, 프로덕션 출시 전 법률 검토가 필요합니다. 회원가입 페이지에 링크가 추가되어 있습니다.

13. **다국어(i18n).** 한국어(기본), 영어, 일본어, 중국어 4개 언어를 지원합니다. `SetLocale` 미들웨어가 `?lang=` 파라미터, 세션, 쿠키 순으로 언어를 감지합니다. 시스템 UI 번역(~240개 키)은 `lang/{locale}.json` + `{{ __('key') }}`로, 사용자 콘텐츠 번역은 `AutoTranslator` 서비스 + `{{ trans_auto($text) }}`로 처리됩니다. 모든 사용자 페이지(13개 뷰 + 5개 컴포넌트 + 인증 뷰)가 완전 번역되었습니다. 관리자 페이지는 한국어 전용입니다. `AutoTranslator::preloadBatch()`로 `ClubController@index/show`, `PartyController@show`, `HomeController@index`에서 배치 프리로드를 적용합니다(클럽 목록 페이지 3.5초→0.35초, 10배 개선). 45개 vibe/subgenre 용어(대형, 캐주얼, 언더그라운드 등)가 `translation_cache` 테이블에 사전 등록되어 API 호출 없이 즉시 반환됩니다. 언어 전환 시 로딩 오버레이가 표시됩니다. `nite_locale` 쿠키는 `bootstrap/app.php`에서 암호화 제외 처리되었습니다. 상세 가이드: `docs/i18n-guide.md`

14. **헤더 위치 버튼.** 헤더 중앙에 Alpine.js `locationBtn()` 컴포넌트로 현재 지역명(홍대, 강남 등)을 표시합니다. GPS 좌표를 8개 서울 지역구에 매칭하며, `enableHighAccuracy: true`, `maximumAge: 60000`(1분), `timeout: 10000`(10초)으로 정확한 위치를 획득합니다. localStorage에 마지막 지역을 저장하여 재방문 시 즉시 표시합니다.

7. **이미지 최적화 파이프라인.** 모든 이미지 업로드 시 `ImageOptimizer::process()`가 자동 적용됩니다 (EXIF 방향 보정 → 최대 1920px 리사이즈 → JPEG 82% 압축 → 400px 썸네일 생성). PNG 투명 이미지는 PNG로 유지됩니다.

8. **이미지 승인 정책.** 관리자 업로드 이미지는 자동 승인입니다. MD 업로드는 `md_profile`, 본인 담당 `club`, 본인 담당 `party` 대상일 때만 자동 승인되어 즉시 노출됩니다. 일반 사용자 업로드와 MD 계정의 커뮤니티/후기 업로드는 기존대로 `pending` 상태로 시작합니다. `Media::scopePublic()`은 계속 `approved` + `is_visible` 조건만 노출합니다.

9. **MD 모바일 업무 화면.** MD는 `/md-dashboard/*` 웹 화면과 `/api/md/*` JSON API를 통해 앱/모바일에서 직접 담당 클럽·파티 소개, 이미지, 문의, 후기를 관리합니다. 핵심 검증은 `MdAccessService`가 담당하고, `MediaUploadController`가 업로드/삭제/정렬 시 서버 단에서 다시 한 번 권한을 막습니다.

---

## C. 처음 실행하는 방법

### 1단계: 프로젝트 받기
```bash
git clone <repository-url> /var/www/nightlife
cd /var/www/nightlife
```

### 2단계: PHP 의존성 설치
```bash
composer install
```

### 3단계: 환경변수 설정
```bash
cp .env.example .env
php artisan key:generate
```
`.env` 파일을 열어 DB 정보를 수정합니다:
```
DB_DATABASE=nightlife_db
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

### 4단계: DB 준비
```bash
# DB 생성 (MySQL에서)
mysql -u root -e "CREATE DATABASE nightlife_db CHARACTER SET utf8mb4"

# 테이블 생성
php artisan migrate

# 테스트 데이터 투입
php artisan db:seed
```

### 5단계: 스토리지 링크
```bash
php artisan storage:link
```

### 6단계: 개발 서버 실행
```bash
php artisan serve
# http://127.0.0.1:8000 에서 확인
```

### 7단계: 관리자 확인
- `http://127.0.0.1:8000/admin/login`
- 이메일: `admin@nite.kr`
- 비밀번호: 시더에서 설정한 값 (기본: `admin1234!`)

### 자주 막히는 포인트

| 증상 | 원인 | 해결 |
|------|------|------|
| `500 Server Error` | `.env` 없거나 `APP_KEY` 미생성 | `cp .env.example .env && php artisan key:generate` |
| DB 연결 오류 | DB 미생성 또는 비밀번호 틀림 | `.env`의 `DB_*` 값 확인 |
| 관리자 로그인 실패 | 시더 미실행 | `php artisan db:seed --class=AdminSeeder` |
| CSS 깨짐 | 네트워크 문제 (CDN 접근 불가) | 인터넷 연결 확인 |
| `/clubs/{id}` 또는 `/parties/{id}` 500 | Blade 속성 안 `@json([...])`에 PHP 정적 프로퍼티/복잡한 배열식을 직접 넣어 파싱 오류 발생 | `@php $intentFormData = [...] @endphp`로 분리 후 `x-data='@js($intentFormData)'` 사용 |
| 문의 작성은 되는데 잘못된 대상 ID도 저장됨 | `InquiryController@store`가 타입만 검사하고 실제 대상 존재 여부를 확인하지 않음 | `Club::whereKey()` / `Party::whereKey()` 존재 확인 후 없으면 `404` 반환 |

### 운영 메모 (2026-04-20)

- 상세 페이지 문의 폼의 Alpine 초기화 값은 인라인 `@json([...])`보다 `@js($preparedArray)` 패턴이 안전합니다. 이번에 `/parties/44`, `/clubs/36` 500의 직접 원인이었고, 둘 다 동일하게 수정했습니다.
- 문의 DB는 `2026_04_17_171000_add_conversion_fields_to_inquiries_table.php` 마이그레이션이 반드시 적용돼 있어야 합니다. 미적용 시 `md-dashboard/inquiries`와 신규 문의 흐름에서 컬럼 오류가 납니다.
- 미디어는 원본 업로드 후 `ImageOptimizer`가 썸네일과 변형 이미지를 생성합니다. 기존 데이터 백필은 `php artisan media:generate-variants`로 다시 돌릴 수 있습니다.
- 스케줄러는 분 단위 크론에서 `php artisan schedule:run`을 호출하고, `media:generate-variants`는 시간 단위로 예약되어 있습니다. 운영 점검 시 아래 순서로 확인하면 됩니다.

```bash
systemctl is-active httpd
systemctl is-active crond
php artisan schedule:list
php artisan migrate:status
curl -I http://127.0.0.1/parties/44
curl -I http://127.0.0.1/clubs/36
curl -I http://127.0.0.1/admin/login
curl -s -o /dev/null -w '%{http_code} %{redirect_url}\n' http://127.0.0.1/md-dashboard/inquiries
```

- 관리자 미디어 품질 점검 화면은 `/admin/media/diagnostics` 입니다. 원본 누락, 썸네일/변형 누락, 내부 IP URL 잔존 여부를 필터링해서 볼 수 있고, 개별/일괄 재생성이 가능합니다.
- 크론 로그에 `pam_limits(crond:session): unknown limit item 'noproc'`가 보이면 `/etc/security/limits.conf`에서 `noproc`를 `nproc`로 수정해야 합니다.

### 모바일 UX/문의 운영 반영 메모 (2026-04-20)

- 홈 1차 개편은 `app/Http/Controllers/HomeController.php`, `resources/views/home.blade.php` 기준으로 반영했습니다. 첫 화면 순서는 히어로 → 이어보기 → 빠른 탐색 → 오늘밤 추천으로 바뀌었고, 히어로는 Primary CTA 1개와 보조 진입 카드 1개만 남겨 탐색 시작점을 좁혔습니다.
- 홈 빠른 탐색은 컨트롤러에서 `quickFilters`, `heroHighlights`, `heroSecondary`를 미리 계산해 뷰에 내려줍니다. 기본값은 `오늘 파티`, 선호 지역/장르, `외국인 OK`, `강남`, `EDM`이고, 실제 리스트 파라미터(`area`, `genre`, `date`, `foreigner`)에 맞춰 링크를 만듭니다.
- 리스트 1차 개편은 `app/Http/Controllers/ClubController.php`, `app/Http/Controllers/PartyController.php`, `resources/views/components/list-toolbar.blade.php`를 중심으로 반영했습니다. 클럽/파티 리스트 모두 상단 sticky 툴바에서 `필터 / 정렬 / 지도 / 결과`를 바로 다루고, 필터와 정렬은 바텀시트에서 처리합니다.
- 리스트 정렬은 공통으로 `추천순`, `인기순`, `가격 낮은순`, `응답 빠른순`을 지원합니다. `응답 빠른순`은 최근 30일 문의 기준 평균 첫 답변 시간 서브쿼리(`inquiries`, `inquiry_replies`)를 사용하므로, 운영 DB에 문의 데이터가 거의 없으면 추천순과 체감 차이가 작을 수 있습니다.
- 리스트 카드 상태 요약은 `resources/views/components/club-card.blade.php`, `resources/views/components/party-card.blade.php`와 각 컨트롤러의 `buildCardSummaries()` 기준으로 맞췄습니다. `AvailabilitySignalService` 배치 계산값을 재사용해서 카드에 상태 배지, 가격대, 평균 응답 속도, 방문 추천 문구를 같이 노출합니다.
- 공통 배지 컴포넌트는 `resources/views/components/badge.blade.php`에 `cyan`, `orange` 변형을 추가했고, Alpine `x-cloak` 초기 숨김은 `resources/views/layouts/partials/styles.blade.php`에서 글로벌로 처리합니다.
- 비교 버튼은 이번 단계에 넣지 않았습니다. 현재 코드베이스에 비교 저장/트레이 기능이 전혀 없어서 죽은 버튼을 만드는 대신, 비교 UX는 `MOB-10` 범위에서 따로 구현하는 쪽으로 유지합니다.
- 사용자 상세 화면은 `resources/views/clubs/show.blade.php`, `resources/views/parties/show.blade.php` 기준으로 1스크린 요약형 구조로 재정리했습니다. 상단에 가격, 응답 속도, 상태 배지, 문의 가능성, `문의 전 빠른 확인` 블록이 먼저 보이고, 하단은 `찜 / 공유 / 문의하기` 고정 CTA만 남깁니다.
- 상세 문의 폼은 `resources/views/partials/inquiry-form.blade.php`로 공통화했습니다. 제목 입력은 제거했고, 기본 입력은 메시지, 방문일, 인원만 먼저 보이며 예산/연락수단/성비/추가 요청은 `상세 정보 추가` 토글 안으로 넣었습니다.
- 사용자 문의 추적은 `app/Models/Inquiry.php`의 타임라인 헬퍼를 기준으로 맞췄습니다. `resources/views/my/inquiries.blade.php`, `resources/views/my/inquiry-show.blade.php`에서 현재 단계, 최근 답변, 첫 응답 정보를 같은 계산식으로 보여줍니다.
- 마이페이지는 `app/Http/Controllers/MyPageController.php`와 `resources/views/my/index.blade.php`에서 액션 허브로 바꿨습니다. 진행중 문의 수, 읽지 않은 알림 수, 최근 본 이어보기를 상단 카드로 노출합니다.
- 위치 기반 근처 사용자/30분 만료 메시지 1차 스캐폴드는 `config/nearby-messaging.php`, `database/migrations/2026_04_20_180000_create_nearby_messaging_tables.php`, `app/Services/NearbyMessagingService.php`, `app/Http/Controllers/Api/NearbyUserApiController.php`, `app/Http/Controllers/Api/ConversationApiController.php` 기준으로 추가했습니다. 기본은 `NEARBY_MESSAGING_ENABLED=false` 이고, 켜야 `/api/nearby-users/*`, `/api/conversations/*` 라우트와 스케줄러가 활성화됩니다.
- 신규 테이블은 `nearby_visibility_settings`, `user_location_statuses`, `venue_checkins`, `user_blocks`, `conversations`, `messages`, `message_reports` 입니다. 현재 단계는 API/배치/모델 골격 + 상세 페이지 모바일 진입 UI + 관리자 메시지 신고 검토 화면까지 완료된 상태입니다.
- 만료 정리는 `nearby:expire-stale-presence`, `nearby:purge-expired-messages` 두 커맨드로 매분 스케줄됩니다. 실제 운영 반영 전에는 `.env`에 기능 플래그를 켠 뒤 `php artisan migrate`, `php artisan schedule:list`, `php artisan route:list --path=api` 순서로 검증해야 합니다.
- 상세 페이지 모바일 진입 UI는 `resources/views/partials/nearby-people-widget.blade.php`를 공통 partial로 만들고 `resources/views/clubs/show.blade.php`, `resources/views/parties/show.blade.php`에 삽입했습니다. 동선은 요약 카드 → 동의/설정 바텀시트 → 주변 사용자 리스트 → 프로필 → 30분 만료 채팅 바텀시트 순서입니다.
- 관리자 검토 화면은 `app/Http/Controllers/Admin/ModerationController.php`, `routes/admin.php`, `resources/views/admin/moderation/message-reports.blade.php`, `resources/views/admin/layouts/app.blade.php` 기준으로 추가했습니다. 기능 플래그가 켜지고 마이그레이션이 적용된 경우에만 관리자 사이드바에 `메시지 신고` 메뉴가 노출됩니다.
- 관리자 문의 인박스는 `app/Http/Controllers/Admin/DashboardController.php`, `app/Http/Controllers/Admin/InquiryController.php` 기준으로 `미응답 / 응답 지연 / 견적 필요 / 확정 대기` 큐를 추가했습니다. `/admin`과 `/admin/inquiries` 둘 다 같은 큐 기준을 사용하며, `queue` 쿼리스트링으로 필터가 유지됩니다.
- MD 문의 화면은 `app/Http/Controllers/MdDashboardController.php`, `resources/views/md-dashboard/inquiries.blade.php`, `resources/views/md-dashboard/inquiry-show.blade.php` 기준으로 정리했습니다. 리스트에서 최근 대화, 지연 여부, 확인 우선 문의를 한 번에 보고, 상세 하단에는 `#reply-form`으로 이동하는 고정 답변 CTA가 있습니다.
- 문의 운영 2차 반영으로 `app/Models/Inquiry.php`에 공통 답변 등록/상태 전이 메서드(`addReply`, `nextUserMessageStatus`)를 추가했습니다. 관리자/MD/API/회원 추가 메시지 경로가 모두 이 메서드를 사용하고, 회원이 답변 후 다시 메시지를 남기면 `answered` 또는 `consultation_completed` 상태가 자동으로 `in_progress`로 재오픈됩니다.
- 관리자 푸시 운영 화면은 `app/Http/Controllers/Admin/PushCampaignController.php`, `app/Services/PushService.php`, `resources/views/admin/push/*.blade.php` 기준으로 리텐션 프리셋 3종을 지원합니다. 프리셋은 `최근 본 후 미문의`, `찜 후 미문의`, `응답 도착 후 미확인`이고, 각 사용자에게 맞는 원래 클럽/파티 상세 또는 `/my/inquiries/{id}` 링크를 개인화해서 보냅니다.
- 리텐션 타게팅은 별도 테이블 추가 없이 `recent_views`, `favorites`, `nite_notifications`, `inquiries`를 조합합니다. `PushService`가 `target_type=custom` 캠페인을 감지하면 사용자별 첫 매칭 항목을 계산해 링크를 생성하며, 관련 테이블이 없는 환경에서는 빈 대상으로 처리해 500 대신 발송 0건으로 마무리합니다.
- 뷰/문서 변경 후 기본 검증 명령은 아래 순서로 반복하면 됩니다. `/my/inquiries/*` 실화면은 인증 세션이 필요하므로 브라우저 로그인 상태에서 추가 확인해야 합니다.

```bash
php -l app/Models/Inquiry.php
php -l app/Http/Controllers/MyPageController.php
php -l app/Http/Controllers/Admin/DashboardController.php
php -l app/Http/Controllers/Admin/InquiryController.php
php -l app/Http/Controllers/MdDashboardController.php
php artisan view:cache
curl -I http://127.0.0.1/clubs/43
curl -I http://127.0.0.1/parties/44
curl -I http://127.0.0.1/admin/login
curl -s -o /dev/null -w '%{http_code} %{redirect_url}\n' http://127.0.0.1/md-dashboard/inquiries
```

---

## D. 프로젝트 읽는 순서

신입 개발자가 코드를 처음 읽을 때 이 순서를 따르세요:

### 1단계: 전체 구조 파악
1. **이 문서** (`docs/developer-handover-manual.md`) — 지금 읽고 있는 것
2. **`.env.example`** — 환경변수가 뭐가 있는지 확인
3. **`composer.json`** — PHP 의존성 확인 (Laravel 버전 등)

### 2단계: 라우팅 (URL → 코드 매핑)
4. **`routes/web.php`** — 사용자가 접근하는 모든 URL
5. **`routes/api.php`** — JSON API 엔드포인트
6. **`routes/admin.php`** — 관리자 URL

### 2.5단계: MD 라우팅
7. **`routes/md.php`** — MD 전용 대시보드 URL (prefix: `/md-dashboard`)

### 3단계: 화면 구조
8. **`resources/views/layouts/app.blade.php`** — 모든 페이지의 공통 레이아웃 (HTML head, 하단탭바)
9. **`resources/views/layouts/partials/styles.blade.php`** — 공통 CSS (카드, 버튼, 글래스모피즘 등)
10. **`resources/views/components/layout/bottom-nav.blade.php`** — 하단 고정 탭바 (5개 탭)
11. **`resources/views/home.blade.php`** — 홈 화면 (가장 복잡한 페이지)

### 4단계: 주요 기능
11. **`app/Http/Controllers/HomeController.php`** — 홈에 어떤 데이터가 내려가는지
12. **`app/Http/Controllers/ClubController.php`** — 클럽 목록/상세
13. **`app/Http/Controllers/PartyController.php`** — 파티 목록/상세
14. **`app/Services/TonightService.php`** — 오늘밤 추천 핵심 로직 (가장 복잡)
15. **`app/Services/NearbyService.php`** — Near Me 위치 기반 추천
16. **`app/Services/TourRecommendationService.php`** — AI 투어 추천

### 5단계: 데이터
17. **`app/Models/Club.php`** — 클럽 모델 (스코프, 상수, 관계)
18. **`app/Models/Party.php`** — 파티 모델
19. **`app/Support/CuratedNightlifeData.php`** — 전국 클럽/파티 큐레이션 원본, 운영형 카드/실이벤트 생성 규칙
20. **`docs/nightlife-data-operations-guide.md`** — 전국 데이터 유지보수 실무 기준서
21. **`database/seeders/ClubSeeder.php`** — 테스트 데이터 구조 이해

### 6단계: 인증 & 회원
22. **`app/Http/Controllers/AuthController.php`** — 회원가입/로그인/로그아웃
23. **`app/Models/User.php`** — 역할(role), 상태(status), isAdmin(), isActive()

### 7단계: 관리자
24. **`resources/views/admin/layouts/app.blade.php`** — 관리자 레이아웃
25. **`app/Http/Controllers/Admin/DashboardController.php`** — 대시보드
26. **`app/Http/Controllers/Admin/UserController.php`** — 회원 관리
27. **`app/Http/Controllers/Admin/MdProfileController.php`** — MD 프로필 관리
28. **`app/Http/Controllers/Admin/MdMappingController.php`** — MD ↔ 클럽/파티 매핑
29. **`app/Http/Controllers/Admin/MediaController.php`** — 미디어 승인/관리
30. **`app/Http/Controllers/Admin/ReviewController.php`** — 후기 관리
31. **`app/Http/Controllers/Admin/InquiryController.php`** — 문의 관리
32. **`app/Models/AccessLog.php`** — 접속 로그 모델 (UA 자동 파싱, KST 접근자, device/guest ID 지원)
30-1. **`app/Helpers/DateHelper.php`** — UTC↔KST 변환 헬퍼 (`toKst()`, `kstToUtc()` 등)
30-2. **`app/Models/UserDevice.php`** — 기기 레지스트리 모델 (`upsert()` 메서드)

### 8단계: Phase 2 시스템
31. **`app/Services/ImageOptimizer.php`** — 이미지 최적화 서비스 (EXIF 보정, 리사이즈, 압축, 썸네일 생성)
32. **`app/Models/Media.php`** — 미디어 모델 (승인 플로우, `scopePublic()`)
32. **`app/Models/Review.php`** — 후기 모델 (1-5 별점, 태그)
33. **`app/Models/Inquiry.php`** — 문의 모델 (자동 MD 배정, 7단계 상태)
34. **`app/Models/InquiryReply.php`** — 문의 답변 모델 (내부 메모 지원)
35. **`app/Http/Controllers/MdDashboardController.php`** — MD 전용 대시보드
36. **`app/Http/Controllers/MediaUploadController.php`** — 이미지 업로드 (역할별 자동 승인)
37. **`app/Http/Controllers/ReviewController.php`** — 후기 CRUD
38. **`app/Http/Controllers/InquiryController.php`** — 문의 작성/조회
39. **`app/Http/Middleware/MdMiddleware.php`** — MD 전용 미들웨어

### 9단계: Phase 3 시스템 (푸쉬/검색)
40. **`app/Services/InquiryNotificationService.php`** — 문의 알림 서비스 (생성/답변/상태변경 알림)
41. **`app/Services/PushService.php`** — 푸쉬 캠페인 발송 서비스 (타겟 해석, 인앱 알림 생성, 전달 로그)
42. **`app/Models/PushCampaign.php`** — 푸쉬 캠페인 모델 (유형, 상태, 타겟)
43. **`app/Models/PushDeliveryLog.php`** — 푸쉬 전달 로그
44. **`app/Models/PushInflowLog.php`** — 푸쉬 유입 로그
45. **`app/Models/DeviceToken.php`** — 기기 토큰 모델
46. **`app/Http/Controllers/Admin/PushCampaignController.php`** — 푸쉬 관리자 CRUD + 발송
47. **`app/Http/Controllers/SearchController.php`** — 통합 검색 (클럽/파티/MD)
48. **`app/Console/Commands/SendScheduledPush.php`** — 예약 푸쉬 발송 커맨드 (`nite:send-scheduled-push`)

### 10단계: Phase 4 시스템 (운영 정책/제재)
49. **`app/Models/Report.php`** — 신고 모델 (reporter_id, target_type, target_id, reason, status)
50. **`app/Models/ForbiddenWord.php`** — 금칙어 모델 (word, match_type, action_type, category, severity)
51. **`app/Models/UserModerationAction.php`** — 제재 모델 (action_type, starts_at, ends_at, is_permanent)
52. **`app/Models/ModerationPolicy.php`** — 운영 정책 모델 (key/value, 5분 캐시)
53. **`app/Services/ForbiddenWordFilter.php`** — 금칙어 검사/마스킹 서비스
54. **`app/Services/ModerationService.php`** — 신고 처리, 자동 숨김, 스팸 검사
55. **`app/Http/Controllers/ReportController.php`** — 신고 접수 (중복 방지)
56. **`app/Http/Controllers/Admin/ModerationController.php`** — 관리자 운영정책 (신고/제재/금칙어/정책)
57. **`resources/views/components/report-button.blade.php`** — 신고 버튼 UI 컴포넌트
57-1. **`resources/views/components/image-gallery.blade.php`** — 이미지 갤러리 컴포넌트 (compact/full 모드, 라이트박스 뷰어)
58. **`resources/views/legal/terms.blade.php`** — 이용약관
59. **`resources/views/legal/privacy.blade.php`** — 개인정보처리방침

### 11단계: 다국어(i18n)
60. **`lang/ko.json`** — 한국어 번역 파일 (~240개 키)
61. **`lang/en.json`** — 영어 번역 파일
62. **`lang/ja.json`** — 일본어 번역 파일
63. **`lang/zh.json`** — 중국어 번역 파일
64. **`app/Http/Middleware/SetLocale.php`** — 언어 전환 미들웨어 (세션/쿠키/파라미터 감지)
65. **`config/locales.php`** — 단일 설정 소스 (언어 목록/표시명/활성 여부)
66. **`app/Services/AutoTranslator.php`** — 사용자 콘텐츠 자동번역 서비스 (Google Translate + DB 캐시 + 배치 프리로드)
67. **`app/Helpers/TranslationHelper.php`** — `trans_auto()` 헬퍼 함수
68. **`database/migrations/..._create_translation_cache_table.php`** — 번역 캐시 테이블

---

## E. 전체 서비스 동작 흐름

### 사용자 흐름 (홈 → 파티 상세 → 찜)

1. 사용자가 `nightlife.ive.co.kr`에 접속
2. Nginx가 요청을 받아 PHP-FPM으로 전달
3. `HomeController@index`가 실행
4. 컨트롤러가 DB에서 데이터 조회:
   - 오늘 파티 (`Party::today()->upcoming()`)
   - 인기 클럽 (`Club::active()->orderByDesc('rating_avg')`)
   - 오늘밤 추천 (`TonightService::getHomeTonightSummary()`)
   - 개인화 데이터 (세션 기반 관심설정/최근본)
5. Blade 템플릿(`home.blade.php`)으로 HTML 생성
6. 사용자에게 HTML 응답

7. 사용자가 파티 카드 클릭 → `/parties/3`으로 이동
8. `PartyController@show`가 실행
9. 조회수 증가 (`recordView`), 최근 본 기록 저장 (`RecentView::record`)
10. 파티 상세 페이지 렌더링

11. 사용자가 찜 버튼 클릭 → POST `/favorites/party/3`
12. `FavoriteController@toggle` → DB에 찜 저장/해제
13. 이전 페이지로 돌아감

### 관리자 흐름 (파티 등록 → 알림 발송)

1. 관리자가 `/admin/login`에서 로그인
2. `AdminMiddleware`가 `isAdmin()` 확인
3. `/admin/parties/create`에서 파티 정보 입력
4. `Admin\PartyController@store`가 실행
5. DB에 파티 저장
6. `NotificationService::sendNewPartyAlerts()`로 매칭 알림 발송
7. `AdminLog::record()`로 관리 로그 기록

---

## F. 기능별 빠른 이해

### 홈

| 항목 | 내용 |
|------|------|
| 하는 일 | 오늘밤 추천, 파티, 클럽, 이어보기, Near Me를 한 화면에 표시 |
| 사용자 화면 | `/` (루트) |
| 관련 API | `GET /tonight/status` (JSON), `GET /nearby/summary` (JSON) |
| 관련 DB | parties, clubs, recent_views, favorites, user_preferences |
| 주요 파일 | `app/Http/Controllers/HomeController.php`, `resources/views/home.blade.php` |
| 수정 시 먼저 볼 파일 | `home.blade.php` (화면), `HomeController.php` (데이터) |
| 연쇄 확인 | `TonightService.php` (추천 데이터), 공통 컴포넌트 (`tonight-card`, `party-card`, `club-card`) |
| 주의사항 | 홈은 가장 많은 컴포넌트를 사용하므로 공통 컴포넌트 수정 시 홈부터 확인 |

### 오늘밤 추천

| 항목 | 내용 |
|------|------|
| 하는 일 | 현재 시간 기준으로 "지금 갈만한 곳" 자동 추천 |
| 사용자 화면 | `/tonight`, 홈의 "오늘밤 추천" 섹션 |
| 관련 API | `GET /tonight/status`, `GET|POST /tonight/quick` |
| 관련 DB | parties, clubs, favorites, recent_views, user_preferences |
| 주요 파일 | `app/Services/TonightService.php` (핵심 로직) |
| 수정 시 먼저 볼 파일 | `TonightService.php`의 `getCurrentTimeSlot()` (시간대 분류), `scoreTonightParty()` (점수 계산) |
| 연쇄 확인 | `TonightController.php`, `tonight/index.blade.php`, `tonight-card.blade.php` |
| 주의사항 | 시간대별 로직이 복잡. `getCurrentTimeSlot()`의 시간 범위를 수정하면 전체 추천 결과가 바뀜 |

### Near Me

| 항목 | 내용 |
|------|------|
| 하는 일 | GPS 또는 지역 선택 기반으로 가까운 곳 추천 |
| 사용자 화면 | `/nearby`, 홈의 "내 근처" 섹션 |
| 관련 DB | clubs (lat/lng 좌표), parties |
| 주요 파일 | `app/Services/NearbyService.php`, `app/Services/GeoService.php` |
| 수정 시 먼저 볼 파일 | `GeoService.php` (거리 계산), `NearbyService.php` (스코어링) |
| 연쇄 확인 | `NearbyController.php`, `nearby/index.blade.php`, `nearby-card.blade.php` |
| 주의사항 | `GeoService::AREA_CENTERS`에 지역별 좌표가 하드코딩되어 있음. 지역 추가 시 여기도 수정 필요 |

### 개인화 (찜/최근본/관심설정)

| 항목 | 내용 |
|------|------|
| 하는 일 | 로그인 없이 세션 기반으로 개인화 데이터 관리 |
| 관련 DB | favorites, recent_views, user_preferences |
| 찜 | `FavoriteController@toggle` → `Favorite::toggle()` |
| 최근 본 | `ClubController@show` / `PartyController@show`에서 `RecentView::record()` 호출 |
| 관심 설정 | `MyPageController@updatePreferences` → `UserPreference` 모델 |
| 주의사항 | 모든 데이터는 `session_id` 기반. 세션 만료 시 데이터 접근 불가 (DB에는 남아있음) |

### 회원 인증 (회원가입/로그인)

| 항목 | 내용 |
|------|------|
| 하는 일 | 회원가입, 로그인, 로그아웃. 로그인 시 세션 데이터(찜, 최근 본, 관심설정, 알림) 병합 |
| 사용자 화면 | `/register`, `/login` |
| 주요 파일 | `app/Http/Controllers/AuthController.php`, `resources/views/auth/register.blade.php`, `resources/views/auth/login.blade.php` |
| 관련 DB | users (phone, nickname, status, last_login_at 필드 추가) |
| 닉네임 | 가입 시 필수 (2~20자, `[a-zA-Z0-9가-힣_]+`). 가입 후 변경 불가 (관리자만 변경 가능). 중복 확인: `GET /auth/check-nickname` |
| 비밀번호 | bcrypt 해시, 최소 8자 |
| 세션 병합 | 로그인 시 기존 세션의 favorites, recent_views, user_preferences, notification_settings가 회원 계정으로 이관 |
| 수정 난이도 | 중 |

### MD 프로필 (사용자 측)

| 항목 | 내용 |
|------|------|
| 하는 일 | MD 상세 프로필 표시, 클럽/파티 상세에서 관련 MD 섹션 노출 |
| 사용자 화면 | `/md/{md}`, 클럽/파티 상세 내 MD 섹션 |
| 주요 파일 | `app/Http/Controllers/MdController.php`, `resources/views/md/show.blade.php`, `resources/views/components/md-card.blade.php` |
| 관련 DB | md_profiles, md_club, md_party |
| 필터 | active & visible MD만 노출 |
| 수정 난이도 | 하 |

### 관리자 페이지

| 항목 | 내용 |
|------|------|
| 하는 일 | 클럽/파티 등록, 게시글 검수, 노출 관리, 회원/MD 관리, 미디어 승인, 후기/문의 관리, 접속 로그, 통계 확인 |
| 사용자 화면 | `/admin/*` |
| 권한 | `AdminMiddleware` → `User::isAdmin()` + `User::isActive()` (role이 admin 또는 super_admin이고 status가 active) |
| 주요 파일 | `app/Http/Controllers/Admin/` 폴더 전체 |
| 레이아웃 | `resources/views/admin/layouts/app.blade.php` (사이드바 + 헤더) |
| 사이드바 섹션 | 콘텐츠(클럽/파티), 커뮤니티(게시글), 운영(미디어 관리[대기 뱃지]/후기 관리/문의 관리[대기 뱃지]), 마케팅(푸쉬 관리), 노출(노출관리/배너), 회원(회원관리/MD관리/MD매칭), 로그(관리로그/접속로그) |
| 주의사항 | 관리자 화면은 사용자 화면과 완전히 분리된 레이아웃을 사용. CSS도 기본 Tailwind (다크 테마 아님) |

### MD 대시보드

| 항목 | 내용 |
|------|------|
| 하는 일 | MD 본인의 프로필 수정, 배정된 클럽/파티 콘텐츠 관리, 후기 확인, 문의 답변, 업로드 이미지 상태 확인 |
| 사용자 화면 | `/md-dashboard/*` |
| 권한 | `MdMiddleware` → `User::isMd()` + `User::isActive()` + mdProfile 보유 |
| 주요 파일 | `app/Http/Controllers/MdDashboardController.php` |
| 레이아웃 | `resources/views/md-dashboard/layout.blade.php` |
| 뷰 폴더 | `resources/views/md-dashboard/` (index, profile, clubs, parties, reviews, inquiries, media 등) |
| 주의사항 | MD는 본인에게 배정된 클럽/파티만 관리 가능. 업로드 이미지는 pending 상태로 시작 |

### 후기 시스템

| 항목 | 내용 |
|------|------|
| 하는 일 | 클럽/파티에 대한 후기 작성/수정/삭제, 별점(1-5), 태그 선택 |
| 사용자 화면 | 클럽/파티 상세 페이지 내 후기 섹션 |
| 라우트 | `POST /reviews/{type}/{id}`, `PATCH /reviews/{review}`, `DELETE /reviews/{review}` |
| 주요 파일 | `app/Http/Controllers/ReviewController.php`, `app/Models/Review.php` |
| 관련 DB | reviews (user_id, target_type, target_id, content, rating, tags, like_count, report_count, is_hidden) |
| 태그 | 분위기 좋음, 음악 최고, 입장 쉬움 등 |
| 이미지 정책 | 후기 텍스트는 즉시 노출. 후기 이미지는 미디어 승인 플로우를 따름 (pending → admin 승인 후 노출) |
| 주의사항 | 로그인 필수. 본인 후기만 수정/삭제 가능 |

### 문의 시스템

| 항목 | 내용 |
|------|------|
| 하는 일 | 클럽/파티에 대한 문의 작성, MD 자동 배정, 답변 주고받기, 상태 변경 알림 |
| 사용자 화면 | 클럽/파티 상세 내 문의 폼, `/my/inquiries` (내 문의 목록) |
| 라우트 | `POST /inquiries/{type}/{id}`, `GET /my/inquiries`, `GET /my/inquiries/{inquiry}`, `POST /my/inquiries/{inquiry}/message` |
| 주요 파일 | `app/Http/Controllers/InquiryController.php`, `app/Models/Inquiry.php`, `app/Models/InquiryReply.php`, `app/Services/InquiryNotificationService.php` |
| 관련 DB | inquiries, inquiry_replies |
| 상태 흐름 | pending → in_progress → answered → reservation_confirmed / consultation_completed → closed (→ hidden) |
| MD 상태 변경 | MD는 `PATCH /md-dashboard/inquiries/{inquiry}/status`로 in_progress, answered, reservation_confirmed, consultation_completed 변경 가능 |
| 알림 | 문의 생성(사용자+MD), MD 답변(사용자), 관리자 답변(사용자), 상태 변경(사용자) — `InquiryNotificationService` |
| MD 자동 배정 | `Inquiry::assignMd()` — 대상 클럽/파티에 매핑된 최고 우선순위 active MD 자동 배정 |
| 주의사항 | 매핑된 MD가 없으면 assigned_md_id=null (관리자가 직접 처리). 관리자는 내부 메모(is_internal) 작성 가능. 관리자는 모든 상태로 변경 가능 |

### 이미지 갤러리

| 항목 | 내용 |
|------|------|
| 하는 일 | 이미지를 그리드+라이트박스로 표시하는 재사용 컴포넌트 |
| 컴포넌트 | `resources/views/components/image-gallery.blade.php` |
| Props | `images` (URL 배열), `thumbnails` (썸네일 URL 배열), `compact` (bool) |
| compact 모드 | 커뮤니티 리스트용. 1장이면 풀폭 썸네일, 2장 이상이면 2개 + "+N" 오버레이. 클릭 시 라이트박스 |
| full 모드 | 상세 페이지용. 1장=풀폭, 2장=2컬럼, 3+장=3컬럼(첫 이미지 2x2). 최대 6개 표시, 초과 시 "+N" 오버레이 |
| 라이트박스 | 풀스크린 오버레이(z-9999), 이미지 카운터(1/5), 닫기(버튼+ESC+영역 클릭), 좌우 네비게이션(키보드+버튼+터치 스와이프), 하단 썸네일 스트립 |
| 적용 위치 | `components/post-card.blade.php` (커뮤니티 카드), `clubs/show.blade.php` (클럽 상세), `parties/show.blade.php` (파티 상세) |
| 이미지 소스 | `Media::scopePublic()` (approved + is_visible). 커뮤니티는 legacy `$post->images` JSON 배열 폴백 |
| 번역 키 | `gallery.close`, `gallery.prev`, `gallery.next`, `gallery.of` (4개 언어) |
| 수정 난이도 | 중 |

### 미디어 시스템

| 항목 | 내용 |
|------|------|
| 하는 일 | 이미지 업로드 + 자동 최적화, 역할별 승인 플로우, 관리자 승인/거절/숨김 |
| 주요 파일 | `app/Http/Controllers/MediaUploadController.php`, `app/Models/Media.php`, `app/Services/ImageOptimizer.php` |
| 관련 DB | media (owner_type, owner_id, uploaded_by, approval_status, is_visible, sort_order, original_name, original_size, optimized_size, mime_type, width, height, thumbnail_path) |
| owner_type | md_profile, club, party, review, community, push |
| 이미지 최적화 | `ImageOptimizer::process()` — EXIF 보정 → max 1920px 리사이즈 → JPEG 82% 압축 → 400px 썸네일 생성. PNG 투명 이미지는 PNG 유지 |
| 업로드 제한 | 최대 10MB (서버에서 자동 최적화) |
| 승인 정책 | admin → auto-approved, MD/user → pending (커뮤니티/후기 이미지 포함) |
| 사용자 노출 | `Media::scopePublic()` — approval_status=approved AND is_visible=true 만 노출 |
| 업로드 경로 | `/storage/md/`, `/storage/clubs/`, `/storage/parties/`, `/storage/reviews/`, `/storage/community/`, `/storage/push/` |
| 썸네일 경로 | `{folder}/thumbs/{filename}_thumb.{ext}` |
| 관리자 기능 | 승인, 거절(사유 입력), 숨김, 삭제, 일괄 승인 |

### 푸쉬 캠페인 시스템

| 항목 | 내용 |
|------|------|
| 하는 일 | 타겟 사용자에게 인앱 알림 발송, 전달/클릭/유입 추적 |
| 관리자 화면 | `/admin/push` — 캠페인 CRUD, 즉시 발송, 예약 발송, 통계 |
| 주요 파일 | `app/Http/Controllers/Admin/PushCampaignController.php`, `app/Services/PushService.php` |
| 모델 | `PushCampaign`, `PushDeliveryLog`, `PushInflowLog`, `DeviceToken` |
| 캠페인 유형 | notice, event, party, system, marketing |
| 캠페인 상태 | draft, scheduled, sending, sent, failed, cancelled |
| 타겟 유형 | all, logged_in, area (preferred_areas 기준), genre (preferred_genres 기준), custom |
| 옵션 | 스태프(MD/admin) 제외 가능, 알림 비활성 사용자 제외 |
| 예약 발송 | `nite:send-scheduled-push` 커맨드 (매분 실행, `routes/console.php` 등록) |
| 추적 | `POST /push/track-click` (클릭), `POST /push/track-inflow` (유입), UTM 파라미터 `?utm_campaign={id}` |
| 기기 등록 | `POST /device-tokens` — platform: android/ios/web |
| 관련 DB | push_campaigns, push_delivery_logs, push_inflow_logs, device_tokens |
| 주의사항 | clickedCount = 알림 클릭, inflowCount = 푸쉬 링크 통한 페이지 진입 (별도 추적) |

### 통합 검색

| 항목 | 내용 |
|------|------|
| 하는 일 | 클럽/파티/MD를 한 번에 검색, 인기 키워드 표시, 검색 로그 저장 |
| 사용자 화면 | `/search?q=키워드` |
| 주요 파일 | `app/Http/Controllers/SearchController.php` |
| 검색 대상 | 클럽(active, name/area/genre/description/vibe), 파티(non-cancelled, name/genre/description/lineup), MD(public, display_name/intro/affiliation) |
| 데이터 정책 | active 클럽, 취소되지 않은 파티, public MD만 검색 결과에 포함 |
| 인기 키워드 | 최근 7일 검색 로그에서 추출, 검색어 없을 때 표시 |
| 빈 상태 | 추천 키워드 표시 |
| API | JSON 응답 지원 (Accept: application/json) |
| 관련 DB | search_logs |
| UI 연동 | 헤더 검색 버튼이 `/search`로 연결 |

### 신고 시스템

| 항목 | 내용 |
|------|------|
| 하는 일 | 게시글/후기/미디어 신고, 중복 방지, 자동 숨김 처리 |
| 사용자 화면 | 각 콘텐츠에 신고 버튼 (`x-report-button` 컴포넌트) |
| 라우트 | `POST /reports` |
| 주요 파일 | `app/Http/Controllers/ReportController.php`, `app/Models/Report.php`, `app/Services/ModerationService.php` |
| 신고 사유 | abuse, spam, adult, false_info, privacy, other |
| 중복 방지 | reporter + target_type + target_id unique 제약 |
| 자동 숨김 | 신고 누적이 임계값(기본 5)을 넘으면 `ModerationService::autoHide()` 실행, moderation_logs에 기록 |
| 관련 DB | reports, moderation_logs, moderation_policies |
| 수정 난이도 | 중 |

### 금칙어 필터

| 항목 | 내용 |
|------|------|
| 하는 일 | 커뮤니티 글쓰기/후기 작성 시 금칙어 검사 및 차단/마스킹 |
| 주요 파일 | `app/Services/ForbiddenWordFilter.php`, `app/Models/ForbiddenWord.php` |
| 매칭 방식 | exact (정확 일치), contains (포함), regex (정규식) |
| 동작 | block (저장 차단), mask (마스킹), review (검토 대기), warn (경고) |
| 적용 위치 | `CommunityController@store`, `ReviewController@store` |
| 캐시 | 5분 캐시, 금칙어 추가/삭제/토글 시 캐시 자동 초기화 |
| 관리자 | `/admin/moderation/forbidden-words` — 등록/삭제/활성화 토글 |
| 수정 난이도 | 중 |

### 사용자 제재 (Moderation Actions)

| 항목 | 내용 |
|------|------|
| 하는 일 | 사용자에게 경고/글쓰기 제한/업로드 제한/정지/차단 제재 적용 |
| 주요 파일 | `app/Models/UserModerationAction.php`, `app/Models/User.php` (canWrite, canUpload) |
| 제재 유형 | warning, restrict_write, restrict_upload, suspend, ban |
| 기간 | 임시 (일 단위) 또는 영구 (is_permanent) |
| User 모델 | `canWrite()` — restrict_write/suspend/ban 제재 시 false, `canUpload()` — restrict_upload/suspend/ban 제재 시 false |
| suspend/ban | user.status를 suspended/banned로 변경. 제재 해제 시 active로 복원 |
| 관리자 | `/admin/moderation/banned-users` — 제재 적용/해제 |
| 수정 난이도 | 중 |

### 운영 정책

| 항목 | 내용 |
|------|------|
| 하는 일 | 신고 임계값, 스팸 제한 등 운영 관련 설정값 관리 |
| 주요 파일 | `app/Models/ModerationPolicy.php` |
| 기본 키 | auto_hide_report_threshold=5, spam_post_limit_per_hour=5, review_limit_per_day=10, image_upload_limit_per_post=5, forbidden_word_default_action=block |
| 캐시 | 5분 캐시 |
| 관리자 | `/admin/moderation/policies` |
| 수정 난이도 | 하 |

### 법적 문서 (이용약관/개인정보처리방침)

| 항목 | 내용 |
|------|------|
| 하는 일 | 서비스 이용약관 및 개인정보처리방침 표시 |
| 사용자 화면 | `/terms`, `/privacy` |
| 주요 파일 | `resources/views/legal/terms.blade.php`, `resources/views/legal/privacy.blade.php` |
| 이용약관 | 10개 조항 (목적, 정의, 서비스, 가입, 금지행위, 제재, 이미지, 문의, 면책, 연락처) |
| 개인정보처리방침 | 9개 섹션 (수집 항목, 방법, 목적, 보유, 제3자, 권리, 파기, 보안, 연락처) |
| 주의사항 | 초안 상태이므로 프로덕션 출시 전 반드시 법률 검토 필요 |
| 회원가입 연동 | `auth/register.blade.php`에 약관/개인정보 링크 추가됨 |
| 수정 난이도 | 하 |

### Android 앱

| 항목 | 내용 |
|------|------|
| 방식 | TWA (Trusted Web Activity) — 웹앱을 Chrome Custom Tab으로 래핑 |
| 디렉토리 | `android/` |
| 빌드 | `bash android/build-apk.sh debug|release|bundle` |
| 서명키 | `android/app/nite-release.keystore` (git에 포함하면 안 됨!) |
| 인증 | `public/.well-known/assetlinks.json`에 SHA-256 핑거프린트 등록 필수 |
| JS 인터페이스 | `MainActivity.java`에 `NiteApp` JavascriptInterface — getDeviceId() (ANDROID_ID, `and_` prefix), getDeviceModel(), getManufacturer(), getOsVersion(), getAppVersion(), getBuildVersion(). User-Agent에 `NiteApp/{version}` 추가 |
| 주의사항 | 앱은 웹을 그대로 보여주므로, 웹 수정 = 앱 수정. 단, 앱 아이콘/스플래시는 별도. deviceId는 하드웨어 시리얼이 아닌 앱 범위 식별자(factory reset 시 리셋) |

---

## G. 신규 개발자가 가장 자주 하게 될 수정 작업

### 홈 문구 수정
1. `resources/views/home.blade.php` 열기
2. Hero 섹션의 `<h1>`, `<p>` 텍스트 수정
3. 브라우저에서 `/` 확인

### 파티 카드 UI 수정
1. `resources/views/components/party-card.blade.php` 열기
2. 카드 레이아웃 수정
3. 이 카드를 사용하는 모든 페이지 확인: 홈, 파티 목록, 오늘밤 추천

### 추천 점수 기준 수정
1. `app/Services/TonightService.php` 열기
2. `scoreTonightParty()` 또는 `scoreTonightClub()` 메서드에서 점수 가중치 수정
3. `/tonight` 페이지에서 결과 확인
4. **주의**: Near Me도 비슷한 구조이므로 `NearbyService.php`도 같이 확인

### 위치 반경 수정
1. `app/Services/NearbyService.php` 열기
2. 상수 `DEFAULT_RADIUS_KM`, `EXPANDED_RADIUS_KM` 수정
3. `/nearby?lat=37.55&lng=126.92`로 테스트

### 관리자 필드 추가
1. 마이그레이션 생성: `php artisan make:migration add_new_field_to_clubs_table`
2. 모델에 `$fillable` 배열에 필드 추가
3. `admin/clubs/form.blade.php`에 입력 필드 추가
4. `Admin\ClubController`의 `validateClub()`에 검증 규칙 추가
5. `php artisan migrate` 실행

### 알림 발송 시간 수정
1. `routes/console.php` 또는 `app/Console/Kernel.php`에서 스케줄 확인
2. `app/Console/Commands/SendTonightRecommendations.php` 수정
3. `php artisan schedule:list`로 확인

### 배너 변경
1. `/admin/banners`에서 직접 등록/수정 가능
2. 코드 수정 불필요 (관리자 UI 사용)

---

## H. 신입이 가장 많이 실수할 지점

### 1. `.env` 파일 누락
- 증상: 모든 페이지에서 500 에러
- 원인: `.env` 파일이 없거나 `APP_KEY`가 비어있음
- 방지: `cp .env.example .env && php artisan key:generate`

### 2. 마이그레이션/시드 누락
- 증상: 특정 테이블 없다는 에러, 관리자 로그인 불가
- 원인: `php artisan migrate`를 안 했거나 시더 안 돌림
- 방지: 코드 풀 받은 후 항상 `php artisan migrate` 확인

### 3. 공통 컴포넌트 수정 시 영향 범위 확인 누락
- 증상: 한 페이지만 수정하려 했는데 여러 페이지가 깨짐
- 원인: `tonight-card.blade.php`, `party-card.blade.php` 등은 여러 페이지에서 사용
- 방지: 컴포넌트 수정 전 `grep -rn "x-tonight-card\|x-party-card" resources/views/`로 사용처 확인

### 4. 캐시 때문에 수정이 반영 안 됨 (운영 서버)
- 증상: 코드를 수정했는데 화면이 안 바뀜
- 원인: OPcache, 라우트 캐시, 뷰 캐시가 이전 코드를 서빙
- 해결: `php artisan config:clear && php artisan route:clear && php artisan view:clear && sudo systemctl restart php8.3-fpm`

### 5. 추천 로직 수정 시 API 응답 구조 깨짐
- 증상: 추천 결과가 빈 배열이거나 에러
- 원인: `TonightService`의 반환값 구조를 바꿨는데 Blade 템플릿은 이전 구조를 참조
- 방지: 서비스 반환값 수정 시 반드시 호출하는 컨트롤러 + Blade를 같이 수정

### 6. Android 앱 서명키 분실
- 증상: Play Store에 업데이트 업로드 불가
- 원인: `nite-release.keystore` 파일 분실
- 방지: 키스토어 파일을 안전한 곳에 백업. **절대 git에 커밋하지 말 것.**

---

## I. 업무 시작 전 체크리스트

매일 작업 시작 전 확인하세요:

- [ ] 코드 최신화: `git pull`
- [ ] `.env` 파일 존재 확인
- [ ] DB 연결 확인: `php artisan tinker --execute="DB::connection()->getPdo(); echo 'OK';"`
- [ ] 마이그레이션 확인: `php artisan migrate:status`
- [ ] 개발 서버 실행: `php artisan serve`
- [ ] 홈 페이지 정상 확인: `http://127.0.0.1:8000/`
- [ ] 관리자 페이지 정상 확인: `http://127.0.0.1:8000/admin/login`
- [ ] API 정상 확인: `curl http://127.0.0.1:8000/api/home`

운영 서버 배포 전 추가 확인:
- [ ] `APP_DEBUG=false` 확인
- [ ] `php artisan config:cache` 실행
- [ ] `php artisan route:cache` 실행
- [ ] `sudo systemctl restart php8.3-fpm`
- [ ] 홈, 파티, 클럽, 관리자 페이지 정상 확인
