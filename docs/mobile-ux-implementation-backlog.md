# VYBE 모바일 UX 고도화 구현 백로그

> 목적: [`mobile-ux-upgrade-prd.md`](mobile-ux-upgrade-prd.md)를 개발팀이 바로 착수할 수 있는 단위로 분해한 실행 백로그.
>
> 기준 코드베이스: `/var/www/nightlife`

---

## 1. 문서 사용 방법

- 이 문서는 "무엇을 만들지"가 아니라 "어디부터 어떻게 손댈지"를 정리한 개발 착수 문서다.
- 각 티켓은 화면, API, 상태값, 이벤트, 수용 기준을 함께 가진다.
- 1차는 전환율과 모바일 체감 품질을 올리는 작업, 2차는 비교/개인화/탐색 강화, 3차는 운영 자동화와 SLA 체계화에 집중한다.
- 모든 티켓은 모바일 웹 기준으로 정의한다. PC 대응은 반응형 보완 수준으로 제한한다.

---

## 2. 공통 개발 원칙

### 화면 원칙

- 화면당 Primary CTA는 1개만 유지한다.
- 하단 엄지 영역에서 핵심 액션을 다시 실행할 수 있어야 한다.
- 긴 설명보다 상태, 가격, 응답속도, 가능 여부를 먼저 보여준다.
- 버튼을 늘리는 대신 상태 요약과 다음 행동을 분명히 보여준다.

### 데이터 원칙

- 리스트 카드와 상세 상단에서 같은 상태를 다른 기준으로 계산하지 않는다.
- 문의 상태, 응답시간, 예약 가능 여부는 공통 계산 로직으로 제공한다.
- 이벤트 이름은 PRD 기준(`home_primary_cta_click`, `list_filter_apply`, `detail_sticky_cta_click`, `inquiry_submit`)을 따른다.

### QA 원칙

- Blade 파싱 오류, null target 오류, 이미지 누락은 회귀 항목에 반드시 포함한다.
- 클럽과 파티 화면은 항상 짝으로 검증한다.
- 로그인 전/후, 한국어/비한국어, 데이터 있음/없음 케이스를 최소 1회씩 확인한다.

---

## 3. 선행 참고 코드

| 영역 | 우선 확인 파일 |
|---|---|
| 홈 | `app/Http/Controllers/HomeController.php`, `resources/views/home.blade.php`, `resources/views/components/layout/bottom-nav.blade.php` |
| 클럽 리스트/상세 | `app/Http/Controllers/ClubController.php`, `resources/views/clubs/index.blade.php`, `resources/views/clubs/show.blade.php`, `resources/views/components/club-card.blade.php` |
| 파티 리스트/상세 | `app/Http/Controllers/PartyController.php`, `resources/views/parties/index.blade.php`, `resources/views/parties/show.blade.php`, `resources/views/components/party-card.blade.php` |
| 검색 | `app/Http/Controllers/SearchController.php`, `resources/views/search/index.blade.php` |
| 문의 | `app/Http/Controllers/InquiryController.php`, `resources/views/my/inquiries.blade.php`, `resources/views/my/inquiry-show.blade.php`, `app/Services/InquiryConversionService.php`, `app/Services/AvailabilitySignalService.php` |
| 마이페이지 | `app/Http/Controllers/MyPageController.php`, `resources/views/my/index.blade.php`, `resources/views/my/recent.blade.php`, `resources/views/my/favorites.blade.php` |
| 알림 | `app/Http/Controllers/NotificationController.php`, `resources/views/notifications/index.blade.php`, `app/Services/NotificationService.php` |
| 관리자 문의 운영 | `app/Http/Controllers/Admin/InquiryController.php`, `resources/views/admin/inquiries/index.blade.php`, `resources/views/admin/inquiries/show.blade.php`, `resources/views/admin/dashboard.blade.php` |
| MD 문의 운영 | `app/Http/Controllers/MdDashboardController.php`, `resources/views/md-dashboard/inquiries.blade.php`, `resources/views/md-dashboard/inquiry-show.blade.php` |

---

## 4. 단계별 추진 요약

| 단계 | 목표 | 대표 산출물 | 추천 기간 |
|---|---|---|---|
| 1차 | 전환 구조 정리, 문의 흐름 강화, 홈/상세/마이 핵심 개선 | 홈 개편, 리스트 필터, 상세 CTA, 문의 상태, 운영 인박스 1차 | 2주 |
| 2차 | 비교/개인화/탐색 강화 | 비교함, 최근 본 강화, 리뷰/FAQ, 저장 필터, 지도 토글 | 2~3주 |
| 3차 | 운영 자동화, SLA 가시화, 재방문 유도 | 응답 템플릿, 자동 알림, 운영 큐 우선순위, 캠페인 연동 | 2주 |

## 4-1. 구현 현황 (2026-04-20)

| 티켓 | 상태 | 반영 내용 | 검증 메모 |
|---|---|---|---|
| `MOB-01` | 완료 | 홈 첫 화면을 히어로 → 이어보기 → 빠른 탐색 → 오늘밤 추천 순서로 재배치하고 Primary CTA를 1개로 정리 | 홈 HTML에서 새 섹션 마커 확인 |
| `MOB-02` | 완료 | 홈 상단에 날짜/지역/장르/외국인 기준의 빠른 탐색 칩을 추가 | 홈에서 리스트/검색 1탭 진입 구조 확인 |
| `MOB-03` | 완료 | 클럽/파티 리스트에 sticky `필터 / 정렬 / 지도 / 결과` 툴바와 바텀시트 필터·정렬 패널 추가, 정렬 파라미터 지원 | `/clubs`, `/parties` HTML 마커와 `view:cache` 확인 |
| `MOB-04` | 완료 | 리스트 카드에 상태 배지, 가격대, 응답 속도, 방문 추천 정보 추가 | `/clubs`, `/parties` 카드 마커와 평균 응답 텍스트 확인 |
| `MOB-05` | 완료 | 클럽/파티 상세 상단을 요약 카드 구조로 재배치하고 `문의 전 빠른 확인` 블록을 상단으로 이동 | `/clubs/43`, `/parties/44` 렌더 확인 |
| `MOB-06` | 완료 | 상세 하단 CTA를 `찜 / 공유 / 문의하기` 중심의 sticky footer로 단순화 | 문의 앵커 `#detail-inquiry` 연결 확인 |
| `MOB-07` | 완료 | 공통 문의 폼 partial 도입, 제목 제거, 선택 입력 토글 구성 | `php artisan view:cache` 통과 |
| `MOB-08` | 완료 | 내 문의 리스트/상세에 상태 타임라인, 최근 답변, 첫 응답 정보 추가 | `php -l app/Models/Inquiry.php` 통과 |
| `MOB-09` | 완료 | 마이페이지 상단에 진행중 문의, 읽지 않은 알림, 이어보기 카드 추가 | `php -l app/Http/Controllers/MyPageController.php` 통과 |
| `OPS-01` | 완료 | 관리자 대시보드와 문의 목록에 우선 처리 큐 카드 및 `queue` 필터 추가 | 관련 컨트롤러 문법 검사 통과 |
| `OPS-02` | 완료 | MD 문의 목록에 지연/후속 조치 큐, 최근 대화, 상세 고정 답변 CTA 추가 | `md-dashboard/inquiries` 렌더/문법 확인 |
| `DATA-01` | 완료 (2026-04-22) | 공통 `ux_events` 수집 엔드포인트, 레이아웃 추적 스크립트, 홈/리스트/상세/문의/마이/관리자 인박스 핵심 이벤트 속성 반영 | `php artisan view:cache`, `route:list --name=ux-events.store` 확인. 마이그레이션 적용 필요 |
| `MOB-10` | 완료 (2026-04-22) | 세션 기반 `compare_items` 저장, 리스트 `비교 추가/제거`, 상단 비교 버튼, 하단 비교 트레이, 클럽/파티 비교 화면 반영 | `route:list --name=compare.index`, `php artisan view:cache` 확인. 마이그레이션 적용 필요 |
| `MOB-11` | 완료 (2026-04-22) | `RevisitHubService`와 공통 partial로 홈/마이의 최근 본, 최근 찜, 읽지 않은 알림, 열린 문의를 같은 기준으로 노출 | `php -l app/Services/RevisitHubService.php`, `php artisan view:cache` 확인 |
| `MOB-12` | 완료 (2026-04-22) | 공통 `ReviewSummaryService`로 클럽/파티 상세에 평균 평점, 최근 후기 3개, 문의 이력 기반 검증 후기 배지, 후기 신고 진입을 통합 반영 | `php -l app/Services/ReviewSummaryService.php`, `php artisan view:cache`, `/clubs/43`, `/parties/44` `200` 확인 |
| `MOB-13` | 완료 (2026-04-22) | 공통 `VenueFaqService`와 바텀시트 partial로 클럽/파티 상세에 운영 FAQ 5개 이상을 노출하고, 관리자 폼에서 `guide_text`를 직접 관리 가능하게 반영 | `php -l app/Services/VenueFaqService.php`, `php artisan view:cache`, `/clubs/43`, `/parties/44` `200` 확인 |
| `MOB-14` | 완료 (2026-04-22) | 공통 `ListMapService`와 지도 패널 partial로 클럽/파티 리스트에 `view=map` 기반 지도/리스트 토글을 추가하고, 필터·정렬 상태를 유지한 채 위치 탐색이 가능하도록 반영 | `php -l app/Services/ListMapService.php`, `php artisan view:cache`, `/clubs?view=map`, `/parties?view=map` `200` 확인 |
| `MOB-15` | 완료 (2026-04-22) | 세션 기반 `saved_filters` 모델/마이그레이션, 리스트 저장/해제 액션, 알림 설정 화면 관리, 신규 클럽/파티 등록 시 저장 필터 매칭 알림 연결을 반영 | `php -l app/Models/SavedFilter.php`, `php artisan view:cache`, `/clubs`, `/parties`, `/settings/notifications` `200` 확인. 마이그레이션 적용 전에는 UI가 `준비중`으로 안전 fallback |
| `OPS-03` | 완료 (2026-04-22) | `ReplyTemplateService` 기본/DB 템플릿과 문의 상세 템플릿 삽입 UI, 관리자/MD 공통 내부 메모 분리 저장을 반영 | `php -l app/Services/ReplyTemplateService.php`, `php artisan view:cache`, `admin_show_ok`, `md_show_ok` 확인. 마이그레이션 적용 전에는 기본 템플릿 fallback |
| 다음 우선 작업 | 진행 예정 | `-` | 운영 문서/추가 검증 |

---

## 5. 1차 구현 백로그

### MOB-01. 홈 히어로 재구성

| 항목 | 내용 |
|---|---|
| 상태 | 완료 (2026-04-20) |
| 목표 | 첫 화면에서 "오늘 갈 곳 찾기"와 "이어서 보기"를 동시에 노출해 탐색 시작률을 높인다. |
| 우선순위 | 상 |
| 주요 작업 | 홈 섹션 순서를 히어로 → 이어보기 → 오늘 영업/오늘 파티 → 빠른 필터 → 후기로 재정렬 |
| 프론트 작업 | `resources/views/home.blade.php`의 섹션 재배치, Primary CTA 1개 유지, 보조 CTA 1개만 노출 |
| 백엔드 작업 | `HomeController@index`에서 `continueViewing`, `tonightSummary`, `tonightStatus`를 상단 전용 데이터로 명확히 분리 |
| 이벤트 | `home_primary_cta_click`, `home_recent_continue_click` |
| 수용 기준 | 첫 스크린에 Primary CTA 1개, 최근 본 데이터 존재 시 히어로 바로 아래 노출, 검색/오늘 추천 진입이 1탭 |
| 관련 파일 | `app/Http/Controllers/HomeController.php`, `resources/views/home.blade.php` |

### MOB-02. 홈 빠른 필터 및 상태 칩 추가

| 항목 | 내용 |
|---|---|
| 상태 | 완료 (2026-04-20) |
| 목표 | 사용자가 홈에서 검색 페이지를 거치지 않고 바로 리스트 진입하게 만든다. |
| 우선순위 | 상 |
| 주요 작업 | 지역, 장르, 오늘, 외국인 가능, 영업중 칩을 고정 노출 |
| 프론트 작업 | 칩 탭 UI, 선택 시 `clubs.index` 또는 `parties.index`로 즉시 이동 |
| 백엔드 작업 | 기존 리스트 파라미터 체계를 재사용하되 홈 링크 생성 규칙 통일 |
| 이벤트 | `home_quick_filter_click` |
| 수용 기준 | 홈에서 칩 1탭으로 결과 리스트 이동, 선택 상태가 쿼리스트링으로 유지 |
| 관련 파일 | `resources/views/home.blade.php`, `routes/web.php` |

### MOB-03. 클럽/파티 리스트 상단 고정 필터 바

| 항목 | 내용 |
|---|---|
| 상태 | 완료 (2026-04-20) |
| 목표 | 사용자가 10초 안에 후보를 압축할 수 있게 한다. |
| 우선순위 | 상 |
| 주요 작업 | 상단 고정 액션 바 `필터 / 정렬 / 지도 / 비교` 추가 |
| 프론트 작업 | `resources/views/clubs/index.blade.php`, `resources/views/parties/index.blade.php`에 sticky bar와 바텀시트 UI 추가 |
| 백엔드 작업 | 정렬 파라미터 지원. 최소 `추천순`, `인기순`, `가격낮은순`, `응답빠른순` 구현 |
| 이벤트 | `list_filter_apply`, `list_sort_change` |
| 수용 기준 | 필터 적용 후 결과 수 즉시 노출, 정렬 상태 URL 유지, 모바일 하단 가림 없이 동작 |
| 관련 파일 | `app/Http/Controllers/ClubController.php`, `app/Http/Controllers/PartyController.php`, `resources/views/clubs/index.blade.php`, `resources/views/parties/index.blade.php` |

### MOB-04. 리스트 카드 상태 요약 강화

| 항목 | 내용 |
|---|---|
| 상태 | 완료 (2026-04-20) |
| 목표 | 리스트만 보고도 문의 가능성과 적합도를 판단하게 만든다. |
| 우선순위 | 상 |
| 주요 작업 | 카드에 상태 배지, 가격대, 응답속도, 외국인 가능 여부 추가 |
| 프론트 작업 | `club-card`, `party-card` 컴포넌트 재설계 |
| 백엔드 작업 | 카드용 상태 계산값을 컨트롤러 또는 전용 서비스에서 주입 |
| 이벤트 | `list_card_click`, `list_compare_add` |
| 수용 기준 | 모든 카드에 최소 1개 상태 배지 노출, 가격/지역/장르/응답 정보 중 3개 이상 표시 |
| 관련 파일 | `resources/views/components/club-card.blade.php`, `resources/views/components/party-card.blade.php`, `app/Services/AvailabilitySignalService.php`, `app/Services/InquiryConversionService.php` |

### MOB-05. 상세 상단 요약 블록 재구성

| 항목 | 내용 |
|---|---|
| 상태 | 완료 (2026-04-20) |
| 목표 | 상세 첫 화면에서 문의 여부를 판단하게 만든다. |
| 우선순위 | 상 |
| 주요 작업 | 이름, 지역, 장르, 가격, 상태, 응답시간, 외국인 가능, 문의 가능 여부를 첫 스크린에 재배치 |
| 프론트 작업 | `clubs.show`, `parties.show` 상단 정보를 요약 카드 구조로 재배치 |
| 백엔드 작업 | `InquiryConversionService` 결과를 상단 표시용 구조로 정리 |
| 이벤트 | `detail_summary_view`, `detail_sticky_cta_click` |
| 수용 기준 | 첫 스크린에 가격/상태/문의 가능 여부가 모두 보이고, CTA 이전에 핵심 판단 정보가 완성됨 |
| 관련 파일 | `resources/views/clubs/show.blade.php`, `resources/views/parties/show.blade.php`, `app/Services/InquiryConversionService.php` |

### MOB-06. 상세 하단 고정 CTA 정리

| 항목 | 내용 |
|---|---|
| 상태 | 완료 (2026-04-20) |
| 목표 | 스크롤 위치와 무관하게 문의 액션을 유지한다. |
| 우선순위 | 상 |
| 주요 작업 | 하단 CTA를 `찜 / 공유 / 문의하기` 중심으로 단순화하고, 예약은 문의 흐름 안으로 통합 |
| 프론트 작업 | sticky footer CTA 컴포넌트 추가, 중복 버튼 제거 |
| 백엔드 작업 | 별도 신규 없음. 단, 문의 진입 시 대상 타입과 ID 전달 규칙 통일 |
| 이벤트 | `detail_sticky_cta_click`, `favorite_toggle`, `share_click` |
| 수용 기준 | 상세 하단 버튼이 항상 보이고, 문의하기 버튼 라벨과 실제 이동 동작이 일치 |
| 관련 파일 | `resources/views/clubs/show.blade.php`, `resources/views/parties/show.blade.php` |

### MOB-07. 문의 입력 UX 1차 고도화

| 항목 | 내용 |
|---|---|
| 상태 | 완료 (2026-04-20) |
| 목표 | 문의를 단순 폼 제출이 아니라 상담 시작으로 느끼게 만든다. |
| 우선순위 | 상 |
| 주요 작업 | 메시지, 방문일, 인원, 예산, 연락 희망수단을 짧은 입력 블록으로 재구성 |
| 프론트 작업 | 상세 페이지 문의 폼과 `my/inquiry-show` 메시지 입력영역 UI 정리 |
| 백엔드 작업 | `InquiryController@store`, `addMessage` 유효성 확장. 필요한 경우 nullable 필드용 마이그레이션 추가 |
| 이벤트 | `inquiry_submit`, `inquiry_message_add` |
| 수용 기준 | 제목 입력 없이 문의 가능, 필수값은 메시지 하나만 유지, 나머지는 상담 품질 향상용 선택 필드로 제공 |
| 관련 파일 | `app/Http/Controllers/InquiryController.php`, `resources/views/clubs/show.blade.php`, `resources/views/parties/show.blade.php`, `resources/views/my/inquiry-show.blade.php` |

### MOB-08. 내 문의 상태 타임라인 도입

| 항목 | 내용 |
|---|---|
| 상태 | 완료 (2026-04-20) |
| 목표 | 사용자가 문의 후 현재 어디까지 진행됐는지 직관적으로 이해하게 만든다. |
| 우선순위 | 상 |
| 주요 작업 | `접수됨 → 담당자 배정 → 상담중 → 견적 제안 → 예약 확인 대기 → 예약 확정 → 종료` 타임라인 도입 |
| 프론트 작업 | `my/inquiries`, `my/inquiry-show`에 상태 배지와 타임라인 추가 |
| 백엔드 작업 | 상태 표준화, 상태 변경 로그 저장 구조 설계 또는 기존 로그 활용 |
| 이벤트 | `inquiry_timeline_view`, `inquiry_reminder_click` |
| 수용 기준 | 사용자는 자신의 문의 상세에서 현재 상태, 마지막 답변 시간, 담당자 정보를 볼 수 있어야 함 |
| 관련 파일 | `app/Http/Controllers/InquiryController.php`, `app/Models/Inquiry.php`, `resources/views/my/inquiries.blade.php`, `resources/views/my/inquiry-show.blade.php` |

### MOB-09. 마이페이지 액션 대시보드화

| 항목 | 내용 |
|---|---|
| 상태 | 완료 (2026-04-20) |
| 목표 | 마이페이지를 설정 화면이 아니라 재방문 액션 허브로 전환한다. |
| 우선순위 | 상 |
| 주요 작업 | 진행중 문의, 읽지 않은 알림, 최근 본 이어보기, 찜한 곳을 상단 카드로 노출 |
| 프론트 작업 | `my.index` 구조 개편, 섹션 우선순위 재배치 |
| 백엔드 작업 | `MyPageController@index`에서 진행중 문의 수와 상태별 요약 제공 |
| 이벤트 | `my_action_card_click`, `my_recent_continue_click` |
| 수용 기준 | 로그인 사용자는 진입 즉시 해야 할 행동 카드 1개 이상 확인 가능 |
| 관련 파일 | `app/Http/Controllers/MyPageController.php`, `resources/views/my/index.blade.php` |

### OPS-01. 관리자 문의 인박스 1차

| 항목 | 내용 |
|---|---|
| 상태 | 완료 (2026-04-20) |
| 목표 | 운영자가 로그인 후 미응답/긴급 문의를 가장 먼저 보게 만든다. |
| 우선순위 | 상 |
| 주요 작업 | `미응답`, `응답 지연`, `견적 필요`, `확정 대기` 큐를 대시보드 카드로 분리 |
| 프론트 작업 | `admin/dashboard` 또는 `admin/inquiries/index` 상단에 우선 처리 카드 추가 |
| 백엔드 작업 | 문의 우선순위 계산값, 최초 응답 경과 시간, 마지막 업데이트 시각 노출 |
| 이벤트 | `admin_inbox_item_open`, `admin_inbox_item_complete` |
| 수용 기준 | 운영 첫 화면에서 우선 처리 대상 수치 확인 가능, 카드 클릭 시 해당 필터 목록으로 이동 |
| 관련 파일 | `app/Http/Controllers/Admin/DashboardController.php`, `app/Http/Controllers/Admin/InquiryController.php`, `resources/views/admin/dashboard.blade.php`, `resources/views/admin/inquiries/index.blade.php` |

### OPS-02. MD 문의 화면 반응 속도 개선

| 항목 | 내용 |
|---|---|
| 상태 | 완료 (2026-04-20) |
| 목표 | MD가 모바일에서 답변과 상태 변경을 빠르게 처리하게 만든다. |
| 우선순위 | 상 |
| 주요 작업 | `md-dashboard/inquiries` 리스트에서 상태, 최근 메시지, 지연 여부를 한 화면에 요약 |
| 프론트 작업 | MD 문의 카드 구조 단순화, 상세 진입 CTA 고정 |
| 백엔드 작업 | `MdDashboardController@inquiries` 쿼리 최적화, 최신 답변/지연 상태 제공 |
| 이벤트 | `md_inquiry_open`, `md_inquiry_reply_submit` |
| 수용 기준 | 문의 카드에서 대상명, 상태, 최근 메시지 시간, 사용자 메시지 요약 확인 가능 |
| 관련 파일 | `app/Http/Controllers/MdDashboardController.php`, `resources/views/md-dashboard/inquiries.blade.php`, `resources/views/md-dashboard/inquiry-show.blade.php` |

### DATA-01. 핵심 화면 이벤트 심기

| 항목 | 내용 |
|---|---|
| 목표 | 개편 이후 성과를 수치로 판단할 수 있게 만든다. |
| 우선순위 | 상 |
| 주요 작업 | 홈, 리스트, 상세, 문의, 마이페이지, 운영 인박스 이벤트 삽입 |
| 프론트 작업 | 클릭/제출/필터 적용 시 공통 추적 호출 삽입 |
| 백엔드 작업 | 기존 로그 체계와 연결하거나 최소 저장 엔드포인트 마련 |
| 이벤트 | PRD 정의 이벤트 전체 |
| 수용 기준 | 1차 범위의 핵심 CTA와 폼 제출은 모두 이벤트가 남아야 함 |
| 관련 파일 | `resources/views/layouts/app.blade.php`, `resources/views/components/layout/header.blade.php`, 관련 페이지 Blade, `routes/web.php`, `app/Http/Controllers/Admin/LogController.php` 또는 신규 추적 엔드포인트 |

---

## 6. 2차 구현 백로그

### MOB-10. 비교함 및 비교 트레이

| 항목 | 내용 |
|---|---|
| 목표 | 사용자가 후보 2~4개를 저장하고 최종 결정을 빠르게 하게 만든다. |
| 우선순위 | 중 |
| 주요 작업 | 리스트 카드 `비교 추가`, 하단 비교 트레이, 비교 요약 화면 |
| 백엔드 작업 | 세션 또는 로그인 사용자 기준 비교함 저장 구조 추가 |
| 수용 기준 | 클럽/파티 최대 4개 비교 가능, 새로고침 후 유지 |
| 관련 파일 | 리스트/상세 Blade, `FavoriteController` 유사 구조 신규 추가 |

### MOB-11. 최근 본/찜/알림 통합 재정렬

| 항목 | 내용 |
|---|---|
| 목표 | 재방문 사용자의 다음 행동 유도를 강화한다. |
| 우선순위 | 중 |
| 주요 작업 | 최근 본, 찜, 미응답 문의, 읽지 않은 알림을 하나의 재방문 모듈로 정리 |
| 백엔드 작업 | 최근 본 우선순위, 찜 상태, 알림 카운트 묶음 응답 제공 |
| 수용 기준 | 마이페이지와 홈의 재방문 블록이 동일 기준으로 동작 |
| 관련 파일 | `HomeController.php`, `MyPageController.php`, 관련 Blade |

### MOB-12. 리뷰 요약 및 검증 리뷰 배지

| 항목 | 내용 |
|---|---|
| 목표 | 상세 페이지 신뢰도를 높인다. |
| 우선순위 | 중 |
| 주요 작업 | 별점 평균, 최근 후기 3개, 검증 후기 배지, 신고 진입 개선 |
| 백엔드 작업 | 리뷰 요약 집계값 캐시 또는 계산 최적화 |
| 수용 기준 | 상세 페이지 상단 또는 중단에 리뷰 요약 카드 노출 |
| 관련 파일 | `resources/views/clubs/show.blade.php`, `resources/views/parties/show.blade.php`, `app/Http/Controllers/ReviewController.php` |

### MOB-13. FAQ/운영 정보 바텀시트

| 항목 | 내용 |
|---|---|
| 목표 | 문의 전에 반복 질문을 줄인다. |
| 우선순위 | 중 |
| 주요 작업 | 드레스코드, 외국인 입장, 테이블, 입장시간, 결제 방식 FAQ를 바텀시트로 제공 |
| 백엔드 작업 | 클럽/파티별 FAQ 데이터 구조 필요 시 마이그레이션 추가 |
| 수용 기준 | 상세에서 FAQ 5개 이상 제공, FAQ 확인 후 문의 전환 흐름 유지 |
| 관련 파일 | 상세 Blade, admin club/party form, 관련 모델 |

### MOB-14. 지도/리스트 토글

| 항목 | 내용 |
|---|---|
| 목표 | 위치 기반 탐색을 강화한다. |
| 우선순위 | 중 |
| 주요 작업 | 리스트 상단에서 지도 토글, nearby 데이터 재사용 검토 |
| 백엔드 작업 | 위치 좌표 없는 데이터 예외 처리, NearbyService 재사용 범위 점검 |
| 수용 기준 | 지도 진입/복귀 시 필터 상태 유지 |
| 관련 파일 | `app/Http/Controllers/NearbyController.php`, `app/Services/NearbyService.php`, 리스트 Blade |

### MOB-15. 저장 필터/구독 알림

| 항목 | 내용 |
|---|---|
| 목표 | 재방문을 자동으로 유도한다. |
| 우선순위 | 중 |
| 주요 작업 | "홍대/EDM/오늘" 같은 조건 저장, 조건 일치 시 알림 발송 |
| 백엔드 작업 | 저장 필터 테이블, 매칭 배치, 푸시 연동 |
| 수용 기준 | 사용자는 필터 조건을 저장하고 해제할 수 있어야 함 |
| 관련 파일 | `NotificationService.php`, `NotificationSettingController.php`, 신규 모델/마이그레이션 |

---

## 7. 3차 구현 백로그

### OPS-03. 답변 템플릿 및 내부 메모

| 항목 | 내용 |
|---|---|
| 목표 | 반복 응답 시간을 줄이고 운영 품질을 균일하게 만든다. |
| 우선순위 | 중 |
| 주요 작업 | 관리자/MD 답변 템플릿, 내부 메모, 자주 쓰는 답변 스니펫 |
| 수용 기준 | 운영자는 상세 화면에서 템플릿을 삽입하고 내부 메모를 분리 저장 가능 |
| 관련 파일 | 관리자/MD 문의 상세 Blade, 관련 컨트롤러, 신규 테이블 |

### OPS-04. 운영 SLA 시각화

| 항목 | 내용 |
|---|---|
| 목표 | 응답 지연을 시스템이 먼저 드러내게 만든다. |
| 우선순위 | 중 |
| 주요 작업 | 10분, 30분, 60분 이상 미응답 상태 배지 및 우선순위 점수화 |
| 수용 기준 | 인박스 정렬이 단순 최신순이 아니라 SLA 우선순위를 반영 |
| 관련 파일 | `Inquiry` 모델 정렬 스코프, admin/md inquiries 화면 |
| 진행 상태 | 완료 |

### OPS-05. 문의-예약-종료 자동 상태 전이

| 항목 | 내용 |
|---|---|
| 목표 | 수동 상태 변경 누락을 줄인다. |
| 우선순위 | 하 |
| 주요 작업 | 답변 등록, 담당자 배정, 예약 확정 시 상태 자동 전이 규칙 정리 |
| 수용 기준 | 주요 액션 후 수동 상태 변경 없이도 정상 흐름 유지 |
| 관련 파일 | `app/Models/Inquiry.php`, `InquiryController`, `Admin/InquiryController`, `MdDashboardController` |
| 진행 상태 | 완료 |

### OPS-06. 캠페인 연동 리텐션 시나리오

| 항목 | 내용 |
|---|---|
| 목표 | 이탈 사용자를 다시 상세/문의로 복귀시킨다. |
| 우선순위 | 하 |
| 주요 작업 | 최근 본 미완료, 찜 후 미문의, 응답 도착 후 미확인 사용자 대상 알림 |
| 수용 기준 | 관리자에서 조건별 캠페인 생성 가능, 클릭 후 원래 상세/문의 화면으로 복귀 |
| 관련 파일 | `app/Http/Controllers/Admin/PushCampaignController.php`, `app/Services/NotificationService.php`, 푸시 관련 뷰 |
| 진행 상태 | 완료 |

---

## 8. 공통 선행 과제

| 항목 | 이유 |
|---|---|
| Inquiry 상태 정의 재정리 | 사용자/MD/Admin 화면이 동일 용어를 써야 함 |
| 카드용 상태 계산 공통화 | 리스트와 상세의 상태 배지가 불일치하면 신뢰가 떨어짐 |
| 이벤트 수집 위치 합의 | Blade/Alpine에 분산 삽입 시 누락 가능성이 큼 |
| 바텀시트/배지/CTA 공통 컴포넌트화 | 화면마다 따로 만들면 유지보수 비용이 급증 |
| 이미지 fallback 규칙 통일 | 홈/리스트/상세에서 깨진 이미지 대응 방식이 달라지면 품질이 불안정해짐 |

---

## 9. 권장 착수 순서

1. `DATA-01`부터 진행한다.
홈, 리스트, 상세, 문의, 운영 1차 화면 구조는 반영됐으므로, 이제 핵심 이벤트 계측을 먼저 정리해야 전체 퍼널을 닫을 수 있다.

2. 이어서 `MOB-10`, `MOB-11`, `MOB-12`, `MOB-13`, `MOB-14`, `MOB-15`를 묶어 처리한다.
비교, 최근 본, 리뷰, FAQ, 지도, 저장 필터는 사용자의 재방문과 후보 압축을 강화하는 2차 묶음으로 보는 편이 맞다.

3. 마지막으로 `OPS-03`, `OPS-04`, `OPS-05`, `OPS-06`을 진행한다.
운영 템플릿, SLA, 자동 상태 전이, 리텐션 캠페인은 사용자 퍼널과 이벤트 구조가 안정된 뒤 붙이는 것이 안전하다.

---

## 10. Definition of Done

- 디자인 시안 없이도 개발 가능한 수준의 레이아웃 구조가 문서화되어 있어야 한다.
- 클럽/파티 화면이 동일 패턴으로 구현되어야 한다.
- 로그인 전/후, 데이터 없음, 이미지 없음, 문의 없음 상태가 모두 처리되어야 한다.
- 최소 수동 검증 항목: 홈, 클럽 리스트, 파티 리스트, 클럽 상세, 파티 상세, 내 문의, 관리자 문의 목록, MD 문의 목록.
- 문서 반영 항목이 끝나면 `docs/developer-handover-manual.md` 또는 관련 운영 문서에 변경점을 다시 남긴다.
