# VYBE 다국어(i18n) 가이드

> 시스템 UI를 한국어/영어/일본어/중국어로 전환하는 다국어 기능 안내.

---

## 핵심 원칙: 단일 설정 소스

모든 언어 목록은 **`config/locales.php`** 한 곳에서 관리합니다.
header 드롭다운, 미들웨어 지원 목록, 기본 언어 처리가 전부 이 설정을 바라봅니다.

---

## 지원 언어

`config/locales.php`의 `available` 배열에 정의:

| 코드 | label | native | 기본 여부 |
|------|-------|--------|----------|
| ko | KOR | 한국어 | 기본 + fallback |
| en | ENG | English | - |
| ja | JPN | 日本語 | - |
| zh | CHN | 中文 | - |

---

## 파일 구조

```
config/locales.php               ← ★ 단일 설정 소스 (언어 목록/표시명/활성 여부)

lang/
├── ko.json                      ← 한국어 (~240개 키)
├── en.json                      ← 영어
├── ja.json                      ← 일본어
└── zh.json                      ← 중국어

app/Http/Middleware/SetLocale.php ← 언어 전환 미들웨어 (config 참조)
app/Services/AutoTranslator.php  ← 사용자 콘텐츠 자동번역 (배치/캐시/API)
app/Helpers/TranslationHelper.php ← trans_auto() 헬퍼 함수
bootstrap/app.php                ← nite_locale 쿠키 암호화 제외
config/app.php                   ← locale='ko', fallback_locale='ko'
```

---

## 새 언어 추가 방법 (2단계)

### 1단계: `config/locales.php`에 항목 추가

```php
'th' => [
    'label'  => 'THA',
    'native' => 'ไทย',
    'enabled' => true,
    'order'   => 5,
],
```

### 2단계: `lang/th.json` 번역 파일 생성

```bash
cp lang/en.json lang/th.json
# th.json 값을 태국어로 번역
```

**끝.** 미들웨어, header 드롭다운, 지원 목록이 자동으로 반영됩니다.

> `enabled: false`로 설정하면 번역 파일이 있어도 사용자에게 노출되지 않습니다.

---

## 동작 방식

### 언어 전환 흐름

```
[사용자] → 헤더 지구본 클릭 → ?lang=en
    ↓
[SetLocale 미들웨어]
  1. ?lang 파라미터 감지
  2. config/locales.php의 enabled 목록에 있는지 확인
  3. 세션 + 쿠키(nite_locale, 1년)에 저장
  4. ?lang 파라미터 제거 후 리다이렉트
    ↓
[다음 요청부터]
  1. 세션에서 locale 확인
  2. 없으면 쿠키에서 확인
  3. 없으면 config/locales.php의 default 값 사용
  4. App::setLocale() 적용
```

### 우선순위

1. `?lang=` URL 파라미터 (전환 시)
2. 세션 (`locale`)
3. 쿠키 (`nite_locale`, 1년 TTL)
4. 기본값 (`config/locales.php` → `default`)

### Header UI

- 지구본 아이콘 + 현재 언어 코드 표시
- 드롭다운: `SetLocale::enabledLocales()`에서 자동 생성
- 현재 선택된 언어에 ✓ 표시

### 번역 로딩 오버레이

언어 전환 클릭 시 전체 화면 오버레이가 표시됩니다:
- 지구본 스피너 애니메이션
- 선택한 언어로 "번역 중..." 메시지 표시 (한/영/일/중)
- 페이지 로드 완료 시 자동 사라짐
- 뒤로가기 시에도 `pageshow` 이벤트로 오버레이 자동 숨김

메시지 목록:
- ko: 번역 중... / 잠시만 기다려주세요
- en: Translating... / Please wait a moment
- ja: 翻訳中... / しばらくお待ちください
- zh: 翻译中... / 请稍候

구현: `layouts/app.blade.php`의 `#lang-overlay` + `lang-switch` 이벤트

---

## 자동번역 (사용자 콘텐츠)

### 구조

- 서비스: `app/Services/AutoTranslator.php`
- 헬퍼: `trans_auto($text)` — Blade에서 `{{ trans_auto($content) }}` 사용
- 캐시 테이블: `translation_cache` (hash+locale 기준 중복 방지)
- 번역 엔진: Google Translate 무료 API

### 동작 방식

1. 현재 locale이 기본 언어(ko)면 **원문 그대로 반환** (API 호출 없음)
2. 다른 언어면 `translation_cache` 테이블에서 캐시 확인
3. 캐시 히트 → 즉시 반환 (**0ms**)
4. 캐시 미스 → Google Translate API 호출 → 결과 캐시 저장 → 반환
5. 한번 번역된 텍스트는 DB에 영구 저장되어 다시 API 호출하지 않음

### 배치 번역 (속도 최적화)

컨트롤러에서 `AutoTranslator::preloadBatch($texts)` 호출 시:
- 여러 텍스트를 `§§§` 구분자로 합쳐 **1번의 API 호출**로 처리
- 5건 기준: 배치 ~800ms / 개별 호출 ~2500ms (3배 빠름)
- 캐시 히트 시: **0ms**

적용 위치:
- `ClubController@index` — area/genre/subgenre/vibe 값을 일괄 프리로드 (3.5초→0.35초, 10배 개선)
- `ClubController@show` — 클럽 설명, 가이드, 후기 일괄 프리로드
- `PartyController@show` — 파티 설명, 가이드, 라인업 일괄 프리로드
- `HomeController@index` — 홈 페이지 클럽/파티 데이터 일괄 프리로드

> **성능 주의**: 목록 페이지에서 `trans_auto()`를 개별 호출하면 클럽 20개 × 3회 = 60회 API 호출(~30초)이 발생합니다. 반드시 컨트롤러에서 `preloadBatch()`를 먼저 호출하세요.

### 적용 범위

| 대상 | 함수 | 설명 |
|------|------|------|
| 클럽/파티 설명 | `trans_auto()` | description, short/full_description, guide_text |
| 후기 내용 | `trans_auto()` | review.content |
| 커뮤니티 글 | `trans_auto()` | post.title, post.content |
| MD 소개 | `trans_auto()` | md.intro |

### 번역 사전 (Pre-cached Dictionary)

45개 vibe/subgenre 용어가 `translation_cache` 테이블에 EN/JA/ZH 3개 언어로 사전 등록되어 있습니다. 이 용어들은 API 호출 없이 즉시 반환됩니다.

주요 용어 예시:
- 대형→Large, 중형→Medium, 소형→Small
- 캐주얼→Casual, 프리미엄→Premium, 럭셔리→Luxury
- 에너지틱→Energetic, 언더그라운드→Underground, 트렌디→Trendy
- 힙합→Hip-Hop, 테크노→Techno, 하우스→House, EDM→EDM

사전에 없는 용어는 `preloadBatch()` 호출 시 Google Translate API로 번역 후 자동 캐시됩니다.

새 vibe/subgenre 용어 추가 시: `translation_cache` 테이블에 직접 INSERT하거나, 시더를 통해 추가하세요.

### 관련 파일

| 파일 | 역할 |
|------|------|
| `app/Services/AutoTranslator.php` | 번역 서비스 (배치/캐시/API) |
| `app/Helpers/TranslationHelper.php` | `trans_auto()` 헬퍼 함수 |
| `database/migrations/..._create_translation_cache_table.php` | 캐시 테이블 |

---

## config/locales.php 설정 항목

| 필드 | 설명 | 예시 |
|------|------|------|
| `default` | 기본 언어 코드 | `'ko'` |
| `available.{code}.label` | 짧은 라벨 (UI 표시용) | `'KOR'` |
| `available.{code}.native` | 원어 표시명 (드롭다운) | `'한국어'` |
| `available.{code}.enabled` | 사용자에게 노출 여부 | `true` |
| `available.{code}.order` | 드롭다운 정렬 순서 | `1` |

---

## 번역 범위

### 시스템 UI — `__('key')` (~240개 키)

| 영역 | 예시 키 |
|------|---------|
| 네비게이션 | `nav.home`, `nav.party`, `nav.club`, `nav.tour`, `nav.my` |
| 인증 | `auth.login_title`, `auth.register_title`, `auth.email`, `auth.password` |
| 홈 | `home.hero_title`, `home.tonight`, `home.hot_clubs`, `home.nearby` |
| 클럽/파티 | `club.info_hours`, `party.info_date`, `club.review_section`, `party.inquiry_title` |
| 커뮤니티 | `community.title`, `community.write`, `community.tab_all` |
| 마이페이지 | `my.favorites`, `my.recent`, `my.preferences` |
| 투어 | `tour.title`, `tour.generate`, `tour.budget` |
| 검색 | `search.placeholder`, `search.popular`, `search.no_results` |
| 상태값 | `status.pending`, `status.answered`, `status.reservation_confirmed` 등 |
| 공통 | `common.login`, `common.save`, `common.cancel`, `common.delete` 등 |
| 알림 | `notification.title`, `notification.mark_all_read`, `notification.empty` |
| 신고 | `report.title`, `report.submit`, `report.cancel` |
| 언어 전환 | `lang.translating`, `lang.please_wait` |

### 사용자 콘텐츠 — `trans_auto($text)` (자동번역)

| 대상 | 설명 |
|------|------|
| 클럽/파티 설명 | description, short/full_description, guide_text |
| 후기 내용 | review.content |
| 커뮤니티 글 | post.title, post.content |
| MD 소개 | md.intro |

### 번역 미적용

- 관리자 페이지 (`/admin/*`) — 한국어 전용
- 클럽/파티 이름 (고유명사)
- 문의 메시지 (비공개 1:1 소통)

---

## Blade에서 사용법

```blade
{{ __('nav.home') }}
{{ __('common.welcome', ['name' => $user->nickname]) }}
```

---

## 번역 적용 뷰 파일 (전체 목록)

**13개 페이지 뷰:**
`home`, `clubs/index`, `clubs/show`, `parties/index`, `parties/show`, `community/index`, `community/create`, `my/index`, `my/recent`, `my/preferences`, `tour/index`, `md/show`, `notifications/index`

**5개 컴포넌트:**
`layout/header`, `layout/bottom-nav`, `md-card`, `post-card`, `report-button`

**인증 뷰:**
`auth/login`, `auth/register`

---

## 관련 파일

| 파일 | 역할 |
|------|------|
| `config/locales.php` | **단일 설정 소스** - 언어 목록/표시명/활성 여부 |
| `lang/*.json` | 번역 키-값 (~240개 키/파일) |
| `app/Http/Middleware/SetLocale.php` | 언어 감지/전환 (config 참조) |
| `app/Services/AutoTranslator.php` | 사용자 콘텐츠 자동번역 (배치/캐시/API) |
| `app/Helpers/TranslationHelper.php` | `trans_auto()` 헬퍼 함수 |
| `database/migrations/..._create_translation_cache_table.php` | 번역 캐시 테이블 |
| `bootstrap/app.php` | `nite_locale` 쿠키 암호화 제외 |
| `resources/views/layouts/app.blade.php` | 번역 로딩 오버레이 (`#lang-overlay`) |
| `resources/views/components/layout/header.blade.php` | 언어 드롭다운 (config 자동 렌더링) |
| `resources/views/components/layout/bottom-nav.blade.php` | 탭 라벨 번역 |

---

## 주의사항

- **언어 추가 시 `config/locales.php` + `lang/{code}.json` 2곳만 수정** — header/미들웨어는 자동 반영
- 4개 JSON 파일의 키가 동일해야 함 (키 누락 시 fallback으로 ko 값 표시)
- `__()` 함수가 키를 찾지 못하면 키 문자열 자체를 그대로 출력
- JSON 편집 시 마지막 항목 뒤 콤마 주의
- `enabled: false`로 설정하면 UI에서 숨겨지지만 번역 파일은 유지 가능
