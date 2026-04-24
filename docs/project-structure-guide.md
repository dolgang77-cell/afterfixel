# VYBE 프로젝트 구조 가이드

> 각 폴더가 무슨 역할인지, 언제 이 폴더를 보게 되는지 설명합니다.

---

## 루트 디렉토리

```
nightlife/
├── app/                    ← PHP 애플리케이션 코드 (수정 가장 많이 함)
├── bootstrap/              ← 프레임워크 부팅 (거의 건드리지 않음)
├── config/                 ← 설정 파일 (DB, 캐시, 메일 등)
├── database/               ← 마이그레이션, 시더, 팩토리
├── deploy/                 ← 서버 배포 설정 파일 (Nginx, PHP-FPM, systemd)
├── docs/                   ← 이 문서들
├── ops/                    ← 운영 스크립트 (상태점검, 로그)
├── android/                ← Android TWA 앱 프로젝트
├── public/                 ← 웹 루트 (Nginx가 서빙하는 디렉토리)
├── resources/              ← Blade 뷰, CSS, JS 소스
├── routes/                 ← URL 라우팅 정의
├── storage/                ← 로그, 캐시, 업로드 파일
├── tests/                  ← 테스트 코드
├── vendor/                 ← Composer 패키지 (건드리지 않음)
├── .env                    ← 환경변수 (git에 포함 안 됨!)
├── .env.example            ← 환경변수 템플릿
├── composer.json           ← PHP 의존성
├── deploy.sh               ← 배포 스크립트
└── package.json            ← Node.js 의존성 (Vite 빌드용, 현재 미사용)
```

---

## app/ — 애플리케이션 코드

**언제 보나**: 기능 수정할 때 거의 항상.

### app/Http/Controllers/ — 컨트롤러

"URL을 입력하면 어떤 코드가 실행되는지"를 결정하는 파일들.

```
Controllers/
├── HomeController.php         ← GET / (홈 페이지)
├── ClubController.php         ← GET /clubs, /clubs/{id}
├── PartyController.php        ← GET /parties, /parties/{id}
├── TourController.php         ← GET /tour, POST /tour/recommend
├── TonightController.php      ← GET /tonight, /tonight/quick, /tonight/status
├── NearbyController.php       ← GET /nearby, /nearby/recommend, /nearby/summary
├── CommunityController.php    ← GET /community, POST /community
├── FavoriteController.php     ← POST /favorites/{type}/{id}
├── MyPageController.php       ← GET /my, /my/recent, /my/preferences
├── NotificationController.php ← GET /notifications
├── NotificationSettingController.php ← GET/POST /settings/notifications
│
├── Admin/                     ← 관리자 전용 (URL: /admin/*)
│   ├── AuthController.php         ← 로그인/로그아웃
│   ├── DashboardController.php    ← 대시보드 통계
│   ├── ClubController.php         ← 클럽 CRUD
│   ├── PartyController.php        ← 파티 CRUD + 일괄 상태 변경
│   ├── CommunityPostController.php ← 게시글 검수/숨김/삭제
│   ├── BannerController.php       ← 배너 CRUD
│   ├── ExposureController.php     ← 홈 노출 순서 관리
│   └── LogController.php          ← 로그 조회 (관리/클릭/추천/알림)
│
└── Api/                       ← JSON API (URL: /api/*)
    ├── HomeApiController.php
    ├── ClubApiController.php
    ├── PartyApiController.php
    ├── TourApiController.php
    └── CommunityApiController.php
```

**규칙**: 하나의 URL 그룹 = 하나의 컨트롤러.

### app/Models/ — 데이터 모델

"DB 테이블 하나 = 모델 하나" 관계.

```
Models/
├── User.php              ← users 테이블. isAdmin() 메서드 있음
├── Club.php              ← clubs 테이블. $areas, $genres 상수 정의
├── Party.php             ← parties 테이블. belongsTo(Club)
├── CommunityPost.php     ← community_posts. $types 상수, report() 메서드
├── TourRecommendation.php ← tour_recommendations. AI 추천 결과 저장
├── Favorite.php          ← favorites. 다형성(club/party/tour)
├── RecentView.php        ← recent_views. 다형성. record() 메서드
├── UserPreference.php    ← user_preferences. 관심 지역/장르/예산
├── NiteNotification.php  ← nite_notifications. 인앱 알림
├── NotificationSetting.php ← notification_settings. 알림 설정
├── AdminLog.php          ← admin_logs. 관리자 행동 기록
├── Banner.php            ← banners. 홈/파티/클럽 배너
├── ClickLog.php          ← click_logs. 클릭 통계
├── FavoriteParty.php     ← favorite_parties. (구 버전, favorites로 대체됨)
│
└── Traits/               ← 여러 모델이 공유하는 기능
    ├── HasPriceRange.php     ← price_text 속성 (최소~최대 가격 텍스트)
    ├── HasThumbnail.php      ← thumbnail_url 속성 (없으면 기본 이미지)
    └── HasViewCount.php      ← recordView() 메서드 (조회수 증가)
```

**초보 주의**: `Club::$areas`와 `Club::$genres`는 모델 파일에 하드코딩된 배열입니다. 지역이나 장르를 추가하려면 이 배열을 수정해야 합니다.

### app/Services/ — 비즈니스 로직

"복잡한 계산/로직"이 들어있는 곳. 컨트롤러에서 호출.

```
Services/
├── TonightService.php              ← 오늘밤 추천 핵심 (시간대 판단, 스코어링, 섹션 빌드)
├── NearbyService.php               ← Near Me 추천 (거리 기반 스코어링)
├── GeoService.php                  ← 거리 계산 유틸 (haversine, 이동시간 추정)
├── NotificationService.php         ← 알림 발송 로직
├── TourRecommendationService.php   ← AI 투어 추천 (메인 오케스트레이터)
│
└── Tour/                           ← 투어 추천 하위 모듈
    ├── ClubScorer.php                  ← 9차원 클럽 점수 계산
    ├── RouteBuilder.php                ← 최적/예산/최단 루트 생성
    ├── TravelTimeCalculator.php        ← 지역간 이동시간 룩업
    └── ExplanationGenerator.php        ← 추천 이유 자연어 생성
```

**이 폴더를 보게 되는 경우**: 추천 결과가 이상할 때, 점수 기준을 바꾸고 싶을 때.

### app/Http/Middleware/ — 미들웨어

```
Middleware/
└── AdminMiddleware.php   ← /admin/* 접근 시 관리자 권한 확인
```

### app/Http/Requests/ — 폼 검증

```
Requests/
├── StoreCommunityPostRequest.php  ← 커뮤니티 글 작성 시 입력값 검증
└── TourRecommendRequest.php       ← 투어 추천 요청 시 입력값 검증
```

### app/Console/Commands/ — 스케줄 작업

```
Commands/
├── SendTonightRecommendations.php  ← 금/토 18시에 오늘밤 추천 알림 발송
└── SendPartyReminders.php          ← 10분마다 파티 시작 리마인더 발송
```

---

## resources/views/ — 화면 (Blade 템플릿)

**언제 보나**: UI/화면을 수정할 때.

```
views/
├── layouts/
│   ├── app.blade.php              ← ★ 모든 사용자 페이지의 공통 레이아웃
│   │                                 (HTML head, Tailwind 설정, OG 태그, PWA, 하단탭바)
│   └── partials/
│       └── styles.blade.php       ← 공통 CSS (카드, 버튼, 애니메이션)
│
├── components/                    ← 재사용 가능한 UI 조각들
│   ├── layout/
│   │   ├── header.blade.php           ← 상단 헤더 (로고, 알림벨)
│   │   ├── bottom-nav.blade.php       ← ★ 하단 고정 탭바 (5개 탭)
│   │   └── flash-messages.blade.php   ← 알림 메시지
│   ├── party-card.blade.php       ← 파티 카드 (캐러셀/리스트 모드)
│   ├── club-card.blade.php        ← 클럽 카드 (그리드/리스트 모드)
│   ├── tonight-card.blade.php     ← 오늘밤 추천 카드 (컴팩트/풀)
│   ├── nearby-card.blade.php      ← Near Me 카드 (거리/이동시간 표시)
│   ├── post-card.blade.php        ← 커뮤니티 게시글 카드
│   ├── section-header.blade.php   ← 섹션 제목 (펄스 애니메이션, 전체보기 링크)
│   ├── empty-state.blade.php      ← 결과 없음 상태
│   ├── hero-image.blade.php       ← 상세 페이지 히어로 이미지
│   └── ...                        ← badge, filter-chips, rating-stars 등
│
├── home.blade.php                 ← ★ 홈 페이지 (가장 복잡)
├── clubs/
│   ├── index.blade.php            ← 클럽 목록
│   └── show.blade.php             ← 클럽 상세
├── parties/
│   ├── index.blade.php            ← 파티 목록
│   └── show.blade.php             ← 파티 상세
├── tonight/
│   └── index.blade.php            ← 오늘밤 추천 전용 페이지
├── nearby/
│   └── index.blade.php            ← Near Me 페이지
├── tour/
│   ├── index.blade.php            ← 투어 입력 폼
│   └── result.blade.php           ← 투어 추천 결과
├── community/
│   ├── index.blade.php            ← 커뮤니티 목록
│   └── create.blade.php           ← 글쓰기 폼
├── my/
│   ├── index.blade.php            ← 마이페이지
│   ├── recent.blade.php           ← 최근 본 항목
│   ├── preferences.blade.php      ← 관심 설정
│   └── favorites.blade.php        ← 찜 목록
├── notifications/
│   ├── index.blade.php            ← 알림 목록
│   └── settings.blade.php         ← 알림 설정
├── offline.blade.php              ← PWA 오프라인 페이지
│
└── admin/                         ← 관리자 화면 (완전히 분리된 레이아웃)
    ├── layouts/app.blade.php      ← 관리자 레이아웃 (사이드바+헤더, 라이트 테마)
    ├── auth/login.blade.php
    ├── dashboard.blade.php
    ├── clubs/ (index, form)
    ├── parties/ (index, form)
    ├── posts/ (index, show)
    ├── banners/ (index, form)
    ├── exposure/ (index)
    └── logs/ (index, clicks, recommendations, notifications)
```

**중요**: `components/` 폴더의 파일은 `<x-party-card>`, `<x-tonight-card>` 같은 태그로 다른 뷰에서 호출됩니다. 수정하면 여러 페이지에 영향을 줍니다.

---

## routes/ — URL 라우팅

```
routes/
├── web.php        ← 사용자 웹 페이지 라우트 (~35개)
├── api.php        ← JSON API 라우트 (~8개, throttle 적용)
├── admin.php      ← 관리자 라우트 (~37개, AdminMiddleware 보호)
└── console.php    ← Artisan 명령어 + 스케줄 정의
```

**규칙**: URL을 추가하려면 해당 라우트 파일에 정의하고, 컨트롤러를 만들고, 뷰를 만듭니다.

---

## database/ — DB 관련

```
database/
├── migrations/    ← 테이블 생성/수정 정의 (21개 파일)
├── seeders/       ← 테스트 데이터 (클럽 8개, 파티, 커뮤니티, 관리자)
└── factories/     ← 테스트용 팩토리 (현재 미사용)
```

**초보 주의**: 마이그레이션 파일은 한번 실행되면 다시 실행되지 않습니다. 스키마를 수정하려면 **새 마이그레이션**을 만드세요. 기존 파일을 수정하면 안 됩니다 (이미 실행된 파일이므로).

---

## public/ — 웹 루트

```
public/
├── index.php          ← 모든 요청의 진입점 (수정 금지)
├── .well-known/
│   └── assetlinks.json ← Android TWA 인증 파일
├── icons/             ← PWA/앱 아이콘 (9개 사이즈)
├── manifest.json      ← PWA 매니페스트
├── sw.js              ← Service Worker (오프라인 캐시)
├── robots.txt         ← 검색엔진 크롤러 규칙
├── storage → ../storage/app/public  ← 업로드 파일 심볼릭 링크
└── favicon.ico
```

---

## android/ — Android 앱

```
android/
├── app/
│   ├── build.gradle.kts       ← 앱 빌드 설정 (SDK, 의존성, 서명)
│   ├── nite-release.keystore  ← ★ 서명키 (절대 git에 넣지 말 것!)
│   ├── proguard-rules.pro
│   └── src/main/
│       ├── AndroidManifest.xml ← TWA 설정, 권한, Deep Link
│       └── res/               ← 아이콘, 스플래시, 테마
├── build.gradle.kts
├── settings.gradle.kts
├── gradle.properties
├── gradlew                    ← Gradle 래퍼 (빌드 명령)
└── build-apk.sh               ← 빌드 스크립트
```

---

## deploy/ + ops/ — 배포/운영

```
deploy/
├── nginx.conf                     ← Nginx 사이트 설정
├── php-fpm-pool.conf              ← PHP-FPM 전용 풀 설정
├── nightlife-worker.service       ← 큐 워커 systemd 서비스
├── nightlife-scheduler.service    ← 스케줄러 systemd 서비스
└── nightlife-scheduler.timer      ← 1분마다 실행하는 타이머

ops/
├── OPERATIONS.md      ← 서버 운영 매뉴얼
├── healthcheck.sh     ← 서비스 상태 자동 점검
└── logs.sh            ← 로그 통합 조회 스크립트
```
