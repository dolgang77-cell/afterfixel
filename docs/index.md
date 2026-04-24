# VYBE 개발자 문서 안내

> 이 프로젝트를 처음 받았다면, 아래 순서대로 읽으세요.

## 전체 문서 네비게이션

이 페이지 하나에서 공개 가능한 문서를 전부 이동할 수 있게 정리했습니다.

### 시작 문서

- [zero-to-maintainer-runbook.md](zero-to-maintainer-runbook.md) — 초보 PHP 개발자용 실전 유지보수 매뉴얼
- [developer-handover-manual.md](developer-handover-manual.md) — 메인 인수인계 문서
- [project-structure-guide.md](project-structure-guide.md) — 프로젝트 구조 설명
- [feature-edit-map.md](feature-edit-map.md) — 기능별 수정 시작점 찾기
- [api-db-flow-guide.md](api-db-flow-guide.md) — API/DB/화면 흐름 설명

### 운영/배포 문서

- [admin-manual.md](admin-manual.md) — 관리자 화면 사용 가이드
- [nightlife-data-operations-guide.md](nightlife-data-operations-guide.md) — 전국 클럽/파티 데이터 운영 기준서
- [next-work-checkpoint-20260422.md](next-work-checkpoint-20260422.md) — 다음 세션용 작업 체크포인트
- [deployment-operations-guide.md](deployment-operations-guide.md) — 배포 및 운영 절차
- [server-migration-guide.md](server-migration-guide.md) — 서버 이전 및 환경 이관 가이드
- [troubleshooting-guide.md](troubleshooting-guide.md) — 장애/에러 대응 가이드

### 제품/기획 문서

- [brand-guide.md](brand-guide.md) — 브랜드/톤앤매너 가이드
- [platform-growth-upgrade-plan.md](platform-growth-upgrade-plan.md) — 플랫폼 성장 고도화 계획
- [mobile-ux-upgrade-prd.md](mobile-ux-upgrade-prd.md) — 모바일 UX 고도화 PRD
- [mobile-ux-implementation-backlog.md](mobile-ux-implementation-backlog.md) — 모바일 UX 구현 백로그

### 기능별 심화 문서

- [location-nearby-messaging-architecture.md](location-nearby-messaging-architecture.md) — 위치 기반 근처 사용자/메시지 설계
- [community-moderation-policy.md](community-moderation-policy.md) — 커뮤니티 운영 정책
- [i18n-guide.md](i18n-guide.md) — 다국어 처리 가이드

### 정책/약관 초안

- [privacy-policy-draft.md](privacy-policy-draft.md) — 개인정보처리방침 초안
- [terms-of-service-draft.md](terms-of-service-draft.md) — 이용약관 초안

### 참고

- 이 `/docs` 공개 라우트에서는 `docs/backups/*` 같은 백업 파일은 의도적으로 노출하지 않습니다.
- 저장소 내부 참조 파일인 `.env.example`, `deploy.sh`, `android/build-apk.sh`, `ops/OPERATIONS.md` 역시 본 공개 문서 네비게이션에는 포함하지 않았습니다.

## 추천 읽는 순서

| 순서 | 문서 | 용도 | 읽는 시점 |
|:---:|------|------|----------|
| 0 | [zero-to-maintainer-runbook.md](zero-to-maintainer-runbook.md) | **초보 PHP 개발자용 실전 유지보수 가이드** — 아무것도 몰라도 당장 운영 가능하게 만드는 문서 | 프로젝트 받은 직후 |
| 1 | [developer-handover-manual.md](developer-handover-manual.md) | **메인 인수인계 문서** — 프로젝트 전체를 파악 | 입사 첫날 |
| 2 | [project-structure-guide.md](project-structure-guide.md) | 폴더/파일 구조 상세 설명 | 코드를 열기 전에 |
| 3 | [feature-edit-map.md](feature-edit-map.md) | "이거 수정하려면 어디?" 빠른 찾기 | 수정 작업할 때마다 |
| 4 | [api-db-flow-guide.md](api-db-flow-guide.md) | API, DB, 화면 연결 흐름 | 데이터 흐름 이해할 때 |
| 5 | [nightlife-data-operations-guide.md](nightlife-data-operations-guide.md) | 전국 클럽/파티 데이터 운영 기준, 실이벤트/운영형 카드 분류, 동기화 절차 | 전국 콘텐츠 유지보수 시작 전 |
| 6 | [admin-manual.md](admin-manual.md) | 관리자 패널 사용 가이드 | 관리자 업무할 때 |
| 7 | [community-moderation-policy.md](community-moderation-policy.md) | 커뮤니티 운영 정책 (신고/제재/금칙어) | 운영 정책 이해할 때 |
| 8 | [deployment-operations-guide.md](deployment-operations-guide.md) | 배포, 운영, 장애 대응 | 배포 전/장애 발생 시 |
| 9 | [troubleshooting-guide.md](troubleshooting-guide.md) | 에러별 해결법 | 문제 생겼을 때 |
| 10 | [mobile-ux-upgrade-prd.md](mobile-ux-upgrade-prd.md) | 모바일 UX 1차 고도화 실행안 (와이어프레임+PRD) | 서비스 고도화 작업 시작 전 |
| 11 | [mobile-ux-implementation-backlog.md](mobile-ux-implementation-backlog.md) | 모바일 UX 고도화 개발 백로그 (티켓/수용기준/코드 시작점) | 개발 착수 직전 |
| 12 | [location-nearby-messaging-architecture.md](location-nearby-messaging-architecture.md) | 근처 사용자/30분 만료 메시지 설계 및 구현 시작점 | 위치·메시지 기능 착수 전 |

## 기타 참고 문서

- `/docs` — 외부 공개 문서 진입 경로. `docs/` 아래 Markdown 문서를 브라우저에서 바로 읽을 수 있음
- [privacy-policy-draft.md](privacy-policy-draft.md) — 개인정보처리방침 안내
- [terms-of-service-draft.md](terms-of-service-draft.md) — 이용약관 안내
- [nightlife-data-operations-guide.md](nightlife-data-operations-guide.md) — 전국 클럽/파티 데이터 운영 및 정리 기준
- [next-work-checkpoint-20260422.md](next-work-checkpoint-20260422.md) — 이어서 할 작업 메모
- [i18n-guide.md](i18n-guide.md) — 다국어(i18n) 가이드 (언어 추가/번역 키 추가 방법)
- [mobile-ux-upgrade-prd.md](mobile-ux-upgrade-prd.md) — 모바일 서비스 고도화 실행 문서
- [mobile-ux-implementation-backlog.md](mobile-ux-implementation-backlog.md) — 모바일 UX 고도화 구현 백로그
- [location-nearby-messaging-architecture.md](location-nearby-messaging-architecture.md) — 위치 기반 근처 사용자 + 30분 만료 메시지 설계 문서
- `ops/OPERATIONS.md`, `.env.example`, `deploy.sh`, `android/build-apk.sh` 는 저장소 내부 참고 파일이며, 공개 `/docs` 라우트에서는 직접 노출하지 않음
