# VYBE 브랜드 가이드

## 브랜드명

**VYBE** (바이브)

### 선정 이유
- "Vibe" = Z세대/밀레니얼이 가장 많이 쓰는 감성 단어
- 4글자, 짧고 기억하기 쉬움
- 클럽/파티/나이트라이프의 "분위기" 그 자체
- 기존 서비스와 겹치지 않는 고유함
- "V" 심볼이 아이콘으로 깔끔

---

## 로고 시스템

### 심볼 (V 마크)
- 날카로운 V 형태 + 하단 도트
- Electric Violet → Fuchsia Pink 그라디언트
- 다크 배경 (Deep Space #0C0C18)

### 워드마크
- "VYBE" 텍스트, 굵은 볼드, 그라디언트 적용

### 조합형
- 심볼 + 워드마크 (헤더에서 사용)

---

## 컬러 시스템

| 용도 | 컬러 | HEX |
|------|------|-----|
| **메인 (Electric Violet)** | 보라 | `#8B5CF6` |
| **서브 (Fuchsia Pink)** | 핑크 | `#EC4899` |
| **배경 (Deep Space)** | 다크 | `#08080F` |
| **카드 배경** | 다크 그레이 | `#111119` |
| **포인트 라이트** | 연보라 | `#A78BFA` |
| **포인트 다크** | 진보라 | `#7C3AED` |
| **그라디언트** | 보라→핑크 | `#8B5CF6 → #EC4899` |

### 그라디언트 CSS
```css
background: linear-gradient(135deg, #8B5CF6, #EC4899);
```

---

## 파일 위치

| 자산 | 경로 |
|------|------|
| SVG 심볼 로고 | `public/icons/icon.svg` |
| favicon | `public/favicon.ico` |
| PWA 아이콘 72~512px | `public/icons/icon-{size}.png` |
| Apple Touch | `public/icons/apple-touch-icon.png` |
| manifest | `public/manifest.json` |
| 헤더 로고 | `resources/views/components/layout/header.blade.php` |
| 사이트 title/meta | `resources/views/layouts/app.blade.php` |
| 관리자 로고 | `resources/views/admin/layouts/app.blade.php` |
| MD 대시보드 로고 | `resources/views/md-dashboard/layout.blade.php` |
| Android 앱 이름 | `android/app/src/main/res/values/strings.xml` |
| SW 캐시 이름 | `public/sw.js` (`vybe-v1`) |

---

## 브랜드 변경 시 수정 위치

1. `public/icons/` — 아이콘 세트 교체
2. `public/favicon.ico` — 파비콘 교체
3. `public/manifest.json` — name/short_name
4. `resources/views/components/layout/header.blade.php` — 헤더 로고
5. `resources/views/layouts/app.blade.php` — title/meta/OG/Twitter
6. `resources/views/admin/layouts/app.blade.php` — 관리자 제목
7. `resources/views/md-dashboard/layout.blade.php` — MD 제목
8. `android/app/src/main/res/values/strings.xml` — 앱 이름
9. `public/sw.js` — 캐시 이름
10. `lang/*.json` — PWA/My 관련 텍스트
11. `docs/*.md`, `README.md` — 문서

---

## 피해야 할 것

- 네이버/라인/카카오를 연상시키는 초록/노랑 단색 사용
- 복잡한 그라디언트 남발
- 앱 아이콘으로 축소 시 뭉개지는 복잡한 형태
- 저렴한 유흥업소 광고 느낌의 폰트/컬러
