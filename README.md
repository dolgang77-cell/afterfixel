# VYBE — 서울 나이트라이프 추천 서비스

서울의 클럽, 파티, 투어 루트를 시간/위치/취향 기반으로 추천하는 모바일 웹앱.

## 빠른 시작

```bash
composer install
cp .env.example .env
php artisan key:generate
# .env 열어 DB_DATABASE, DB_USERNAME, DB_PASSWORD 수정
php artisan migrate
php artisan db:seed
php artisan storage:link
php artisan serve
```

- 서비스: http://127.0.0.1:8000
- 관리자: http://127.0.0.1:8000/admin (admin@nite.kr)
- MD 대시보드: http://127.0.0.1:8000/md-dashboard (MD 역할 계정)

## 기술 스택

| 계층 | 기술 |
|------|------|
| 백엔드 | Laravel 13 (PHP 8.3) |
| 프론트 | Blade + Tailwind CSS CDN + Alpine.js CDN |
| DB | MySQL 8 |
| 웹서버 | Nginx + PHP-FPM |
| 운영 | 신고/제재/금칙어 필터/자동 숨김/운영 정책 |
| 앱 | Android TWA (636KB APK) |
| PWA | Service Worker + manifest.json |

## 주요 기능

- 오늘밤 추천: 현재 시간 기반 자동 추천
- Near Me: GPS/지역 기반 가까운 곳 추천
- AI 투어: 9차원 스코어링 + 3종 루트 생성
- 개인화: 찜, 최근 본, 관심 지역/장르/예산
- 회원 인증: 회원가입/로그인, 닉네임 필수(변경 불가), 세션 데이터 병합, bcrypt 비밀번호
- 역할 기반 접근 제어: user/md/admin/super_admin 역할, active/suspended/withdrawn 상태
- MD 프로필: MD 상세 페이지, 클럽/파티별 MD 노출
- MD 대시보드: MD 전용 모바일 관리 화면 (프로필, 배정 클럽/파티 콘텐츠, 이미지 업로드/삭제/정렬, 문의 답변, 후기 확인)
- 이미지 최적화: 업로드 시 자동 처리 (EXIF 보정, 리사이즈, 압축, 썸네일 생성)
- 미디어 시스템: 이미지 업로드 + 자동 최적화 + 역할별 승인 플로우 (admin=자동승인, MD=담당 MD 워크스페이스 대상만 자동승인, user=pending)
- 후기 시스템: 클럽/파티 별점(1-5) + 태그 + 이미지 (이미지는 승인 필요)
- 문의 시스템: 클럽/파티 문의 → MD 자동 배정 → 답변 관리 → 7단계 상태 + 알림
- 통합 검색: 클럽/파티/MD 통합 검색, 인기 키워드, 검색 로그
- 푸쉬 캠페인: 타겟 푸쉬 발송, 예약 발송, 클릭/유입 추적
- 이미지 갤러리: 재사용 컴포넌트 (compact/full 모드, 라이트박스 뷰어, 터치 스와이프), 커뮤니티·클럽·파티에 적용
- 커뮤니티 이미지: 글쓰기 시 이미지 첨부 (최대 5장), 승인 플로우 적용
- 신고 시스템: 게시글/후기/미디어 신고, 중복 방지, 자동 숨김 처리
- 운영 정책: 금칙어 필터(차단/마스킹), 제재 관리(경고/제한/정지/차단), 자동 숨김 임계값 설정
- 다국어(i18n): 한국어/영어/일본어/중국어 4개 언어 지원 (~240개 번역 키), 사용자 콘텐츠 자동번역(Google Translate + DB 캐시 + 배치 프리로드), 45개 vibe/subgenre 사전 캐시, 클럽 목록 10배 성능 개선(3.5초→0.35초), 언어 전환 로딩 오버레이, config 기반 언어 관리, 모든 사용자 페이지 완전 번역 (관리자 페이지는 한국어 전용)
- 법적 문서: 이용약관(/terms), 개인정보처리방침(/privacy) — 법률 검토 필요 초안
- 관리자: 클럽/파티 CRUD, 게시글 검수, 노출 관리, 미디어 승인, 후기/문의 관리, 푸쉬 관리(이미지 업로드 지원), 회원/MD/MD매핑 관리(닉네임 변경), 접속 로그(기기 식별, KST 표시, 상세 페이지), 운영정책(신고/제재/금칙어/정책 설정), 통계

## 문서

## MD 모바일 관리 정책

- MD 웹/앱 진입점: `/md-dashboard/*`, `/api/md/*`
- MD는 본인 `md_profile`, 본인에게 매핑된 `club`, `party`만 수정/업로드/삭제/정렬할 수 있습니다.
- MD 이미지 자동 승인 정책은 `app/Models/Media.php`와 `app/Http/Controllers/MediaUploadController.php`에서 결정합니다.
- MD 담당 대상 검증은 `app/Services/MdAccessService.php`에서 수행하며, 프론트 숨김과 별개로 서버에서 403 차단합니다.
- 일반 사용자 업로드 정책은 유지됩니다. 커뮤니티/후기 이미지는 계속 pending 검토 흐름입니다.
- 정책을 바꾸려면 `Media.php`, `MediaUploadController.php`, `MdAccessService.php`, `MdApiController.php`, `routes/api.php`, `routes/md.php`를 함께 봐야 합니다.

**처음이면 이 순서로 읽으세요:**

1. [docs/developer-handover-manual.md](docs/developer-handover-manual.md) — 메인 인수인계 문서
2. [docs/project-structure-guide.md](docs/project-structure-guide.md) — 폴더/파일 구조
3. [docs/feature-edit-map.md](docs/feature-edit-map.md) — "이거 수정하려면 어디?"
4. [docs/api-db-flow-guide.md](docs/api-db-flow-guide.md) — API/DB/화면 연결 흐름
5. [docs/admin-manual.md](docs/admin-manual.md) — 관리자 패널 사용 가이드
6. [docs/community-moderation-policy.md](docs/community-moderation-policy.md) — 커뮤니티 운영 정책
7. [docs/deployment-operations-guide.md](docs/deployment-operations-guide.md) — 배포/운영
8. [docs/troubleshooting-guide.md](docs/troubleshooting-guide.md) — 에러 해결
9. [docs/privacy-policy-draft.md](docs/privacy-policy-draft.md) — 개인정보처리방침 안내
10. [docs/terms-of-service-draft.md](docs/terms-of-service-draft.md) — 이용약관 안내
11. [docs/i18n-guide.md](docs/i18n-guide.md) — 다국어(i18n) 가이드
12. [docs/mobile-ux-upgrade-prd.md](docs/mobile-ux-upgrade-prd.md) — 모바일 UX 1차 고도화 실행 문서
13. [docs/mobile-ux-implementation-backlog.md](docs/mobile-ux-implementation-backlog.md) — 모바일 UX 고도화 개발 백로그

## 배포

```bash
sudo bash deploy.sh            # 자동 배포
bash ops/healthcheck.sh        # 상태 점검
bash ops/logs.sh error         # 에러 로그
```

## Android 앱 빌드

```bash
cd android
bash build-apk.sh debug       # 테스트 APK
bash build-apk.sh release     # 서명된 APK
bash build-apk.sh bundle      # Play Store AAB
```

## 프로젝트 구조

```
app/Http/Controllers/     ← 컨트롤러 (Web + Admin + API + MD Dashboard)
app/Http/Middleware/      ← AdminMiddleware, MdMiddleware, SetLocale 등
lang/                     ← 다국어 번역 파일 (ko/en/ja/zh.json)
app/Helpers/              ← 헬퍼 클래스 (DateHelper: UTC↔KST 변환)
app/Models/               ← 데이터 모델 (Media, Review, Inquiry, PushCampaign, DeviceToken, UserDevice, AccessLog, Report, ForbiddenWord, UserModerationAction, ModerationPolicy 포함)
app/Services/             ← 추천 로직 (Tonight, Nearby, Tour, Geo, Push, InquiryNotification, ImageOptimizer, ForbiddenWordFilter, ModerationService, AutoTranslator)
resources/views/          ← Blade 뷰 (사용자 + 관리자 + MD 대시보드)
routes/                   ← web.php, api.php, admin.php, md.php
database/migrations/      ← DB 스키마
android/                  ← Android TWA 앱
deploy/                   ← 서버 설정 파일
ops/                      ← 운영 스크립트
docs/                     ← 이 문서들
```
