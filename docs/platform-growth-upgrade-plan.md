# Platform Growth Upgrade Plan

## 1. 현재 구조 진단

### 이미 구축된 핵심 자산
- 사용자 인증: `AuthController`, `users`, `favorites`, `recent_views`, `user_preferences`
- 탐색/추천: `HomeController`, `TonightService`, `NearbyService`, `TourRecommendationService`
- 예약 문의: `InquiryController`, `Inquiry`, `InquiryReply`, `MdDashboardController`, `Admin\InquiryController`
- 공급자 운영: `MdDashboardController`, `MdAccessService`, MD 매핑 테이블 기반 운영
- 콘텐츠: `CommunityPost`, `Review`, `Media`, `ProfileImage`, `RichContentService`
- 알림: `NiteNotification`, `NotificationSetting`, `PushCampaign`, `PushService`, `NotificationService`
- 안전/운영: `Report`, `ModerationController`, `ModerationService`, `ForbiddenWordFilter`, `AdminLog`, `AccessLog`

### 구조적으로 강한 점
- 이미 `추천`, `문의`, `MD 운영`, `푸시`, `신고`가 분리된 서비스/모델로 나뉘어 있어 확장성이 높음
- `UserPreference`, `Favorite`, `RecentView`, `AccessLog`가 있어 개인화 추천 고도화가 쉬움
- `Inquiry`와 `InquiryReply`가 있어 예약 전환 퍼널을 “문의 기반 CRM”으로 확장 가능
- `PushCampaign`, `PushDeliveryLog`, `PushInflowLog`가 있어 CRM/광고/리텐션 실험 기반이 이미 있음
- `Media`, `ProfileImage` 승인 구조가 있어 UGC/MD 콘텐츠를 역할별로 차등 운영 가능

### 현재의 명확한 한계
- 문의는 있으나 `견적`, `확정`, `방문완료`, `정산` 데이터 모델이 없음
- 추천은 존재하지만 실시간성/전환 최적화가 약하고, 설명 가능한 개인화가 제한적임
- MD는 응답/콘텐츠 관리자는 가능하지만 “매출 책임자”로 운영할 수 있는 기능이 부족함
- 커뮤니티는 게시판형에 가까워 습관화 장치와 미션 구조가 없음
- 푸시는 발송 기능은 있으나 행동 기반 자동화와 세그먼트 CRM이 약함
- 광고/부스팅은 배너 위주이며 성과형 상품 구조가 부족함

---

## 2. 사용자 기능

### 2-1. 기능명
예약 퍼널 고도화: 빠른 문의 / 견적 받기 / 예약 요청 분리

### 설명
현재 `Inquiry` 기반 문의 흐름을 유지하되, 단일 문의를 `문의`, `견적`, `예약 요청`의 3단계로 분기한다.

### 왜 필요한지
- 지금은 예약 전환 퍼널이 “메시지 보내기” 수준이라 이탈률이 높다.
- 사용자는 답변 속도와 예상 조건을 모르고, MD는 리드 우선순위를 파악하기 어렵다.

### 핵심 기능 상세
- `inquiries`에 `intent_type` 추가: `question`, `quote_request`, `reservation_request`
- `inquiries`에 `budget_min`, `budget_max`, `visit_time_slot`, `gender_mix`, `special_request` 추가
- 클럽/파티 상세 CTA를 3개로 분리
- 문의 폼에 `MD 평균 응답시간`, `최근 확정률`, `예상 패키지 가격대` 노출
- MD 화면에서 intent별 우선순위 큐 제공

### 구현 난이도
중

### 수익화 가능 여부
있음

### 구현 포인트
- 대상 파일
  - `app/Models/Inquiry.php`
  - `app/Http/Controllers/InquiryController.php`
  - `resources/views/clubs/show.blade.php`
  - `resources/views/parties/show.blade.php`
  - `app/Http/Controllers/MdDashboardController.php`
- 신규 마이그레이션
  - `add_conversion_fields_to_inquiries`

---

### 2-2. 기능명
실시간 가능성 배지: 답변 가능 / 혼잡도 / 추천 시간대

### 설명
클럽/파티 상세와 리스트에 “지금 가도 되는지”를 바로 판단할 수 있는 상태 배지를 붙인다.

### 왜 필요한지
- 나이트라이프는 정보 지연이 전환 손실로 직결된다.
- 사용자는 가격보다 “지금 답이 오나”, “오늘 사람이 많나”를 먼저 본다.

### 핵심 기능 상세
- `오늘 문의량`, `최근 30분 응답수`, `오늘 확정수` 기반 상태 배지 계산
- `TonightService`, `NearbyService` 결과에 `availability_score`, `crowd_score`, `best_visit_window` 추가
- 상태 예시
  - `지금 문의 빠름`
  - `답변 지연 중`
  - `오늘 피크 예상`
  - `23:00 이전 입장 추천`

### 구현 난이도
상

### 수익화 가능 여부
있음

### 구현 포인트
- 대상 파일
  - `app/Services/TonightService.php`
  - `app/Services/NearbyService.php`
  - `app/Models/Inquiry.php`
  - `resources/views/components/club-card.blade.php`
  - `resources/views/components/party-card.blade.php`
  - `resources/views/clubs/show.blade.php`
  - `resources/views/parties/show.blade.php`
- 신규 테이블
  - `area_live_stats`
  - `entity_operational_snapshots`

---

### 2-3. 기능명
개인화 추천 2.0: 위치 + 취향 + 행동 기반 랭킹

### 설명
현재 `UserPreference`, `Favorite`, `RecentView`, `AccessLog`를 결합해 추천 점수 체계를 만든다.

### 왜 필요한지
- 현재 홈 개인화는 조건 필터 중심이라 탐색 만족도와 전환율이 제한적이다.
- 이미 행동 데이터가 있는데 점수화가 부족하다.

### 핵심 기능 상세
- 추천 점수 입력값
  - 선호 장르/지역
  - 최근 조회/찜/문의
  - 체류 시간
  - 현재 위치
  - 시간대
  - 예산 적합성
- 홈에 신규 섹션 추가
  - `지금 나한테 맞는 곳`
  - `최근 본 흐름 이어서 추천`
  - `비슷한 취향 유저가 많이 간 곳`
- 추천 이유 문구 출력
  - `최근 홍대 EDM 파티를 많이 본 사용자에게 추천`

### 구현 난이도
상

### 수익화 가능 여부
있음

### 구현 포인트
- 대상 파일
  - `app/Http/Controllers/HomeController.php`
  - `app/Services/TonightService.php`
  - `app/Services/NearbyService.php`
  - `app/Models/AccessLog.php`
  - `app/Models/Favorite.php`
  - `app/Models/RecentView.php`
- 신규 서비스
  - `app/Services/Recommendation/PersonalizationService.php`
  - `app/Services/Recommendation/ScoreExplainer.php`

---

### 2-4. 기능명
커뮤니티 미션과 체류 보상 시스템

### 설명
커뮤니티/후기를 단순 작성형이 아니라 미션, 출석, 주간 랭킹 구조로 바꾼다.

### 왜 필요한지
- 초기 서비스에서 체류시간과 UGC 양산은 자연 발생하지 않는다.
- 현재 커뮤니티는 “쓸 사람만 쓰는 구조”라 습관화가 약하다.

### 핵심 기능 상세
- 일일/주간 미션
  - 후기 1건 작성
  - 사진 포함 후기 작성
  - 좋아요 3개 이상 받기
  - 파티 비교 글 작성
- 보상
  - 포인트
  - 등급 경험치
  - 배지
  - 특정 파티 혜택 쿠폰

### 구현 난이도
중

### 수익화 가능 여부
있음

### 구현 포인트
- 대상 파일
  - `app/Http/Controllers/CommunityController.php`
  - `app/Http/Controllers/ReviewController.php`
  - `resources/views/community/index.blade.php`
  - `resources/views/clubs/show.blade.php`
  - `resources/views/parties/show.blade.php`
- 신규 테이블
  - `missions`
  - `user_mission_progress`
  - `reward_wallets`

---

### 2-5. 기능명
VIP / 등급 시스템 + 혜택 지갑

### 설명
문의, 방문, 후기 품질, 친구 초대 기준으로 사용자 등급을 부여한다.

### 왜 필요한지
- 재방문 이유를 만들어야 한다.
- 경쟁 서비스와 차별점은 “단순 정보”가 아니라 “내 계정 가치”다.

### 핵심 기능 상세
- 등급: Bronze / Silver / Gold / VIP
- 산정 기준
  - 예약 확정 수
  - 방문완료 수
  - 후기 품질 점수
  - 커뮤니티 기여도
  - 친구 초대 전환 수
- 혜택
  - 빠른 문의 우선 응답
  - 전용 패키지 접근
  - 웰컴드링크 혜택
  - 이벤트 초대

### 구현 난이도
중

### 수익화 가능 여부
있음

### 구현 포인트
- 신규 테이블
  - `loyalty_tiers`
  - `user_loyalty_accounts`
  - `reward_wallet_transactions`

---

## 3. MD 기능

### 3-1. 기능명
MD 리드 인박스 + SLA 대시보드

### 설명
현재 MD 문의함을 매출 리드 처리 화면으로 고도화한다.

### 왜 필요한지
- 현재 MD 대시보드는 답변 관리에는 충분하지만 “어떤 문의부터 처리해야 돈이 되는지”가 드러나지 않는다.

### 핵심 기능 상세
- 문의 리스트 정렬 기준 추가
  - 응답 지연
  - 예상 객단가
  - 방문일 임박
  - 재문의 고객
- 각 문의 카드에 요약 표시
  - 인원
  - 예산
  - intent
  - 최근 답변 시점
  - 예상 전환 등급
- KPI
  - 평균 첫 응답시간
  - 확정률
  - 취소율
  - 객단가

### 구현 난이도
중

### 수익화 가능 여부
있음

### 구현 포인트
- 대상 파일
  - `app/Http/Controllers/MdDashboardController.php`
  - `resources/views/md-dashboard/inquiries.blade.php`
  - `resources/views/md-dashboard/inquiry-show.blade.php`
- 신규 테이블
  - `inquiry_events`
  - `md_performance_snapshots`

---

### 3-2. 기능명
MD 정산/커미션 시스템

### 설명
문의 -> 확정 -> 방문완료를 기준으로 MD 수익을 계산하고 노출한다.

### 왜 필요한지
- MD가 적극적으로 일할 유인은 결국 수익 투명성이다.
- 현재 구조는 운영은 가능하지만 성과 기반 보상이 보이지 않는다.

### 핵심 기능 상세
- 예약 확정 건별 커미션 계산
- 클럽/파티별 수수료율 설정
- 정산 예정 / 확정 / 지급 완료 상태 관리
- 정산 이의제기 기록

### 구현 난이도
상

### 수익화 가능 여부
있음

### 구현 포인트
- 신규 테이블
  - `reservation_orders`
  - `reservation_status_logs`
  - `md_commissions`
  - `md_payouts`
- 대상 파일
  - `app/Http/Controllers/MdDashboardController.php`
  - `resources/views/md-dashboard/index.blade.php`

---

### 3-3. 기능명
패키지/재고/업셀 관리

### 설명
문의 응답 단계에서 MD가 바로 패키지를 제안하고 업셀할 수 있도록 한다.

### 왜 필요한지
- 수익성은 문의 수보다 객단가와 업셀에서 나온다.
- 지금은 자유 텍스트 답변이라 제안 표준화가 어렵다.

### 핵심 기능 상세
- 날짜별 패키지 생성
- 좌석/부스/병 옵션 관리
- 인원별 추천 패키지 자동 선택
- 시간대별 가격 차등
- MD 답변창에서 원클릭 삽입

### 구현 난이도
상

### 수익화 가능 여부
있음

### 구현 포인트
- 신규 테이블
  - `reservation_packages`
  - `package_inventory`
  - `package_proposals`

---

### 3-4. 기능명
콘텐츠 성과 분석 스튜디오

### 설명
MD가 업로드한 이미지/영상/소개가 실제 문의로 이어졌는지 본다.

### 왜 필요한지
- 지금은 콘텐츠 업로드는 되지만 성과 피드백이 거의 없다.

### 핵심 기능 상세
- 콘텐츠별 조회/찜/문의 전환율
- 클럽별 권장 업로드 빈도
- 성과 좋은 문구/썸네일 추천
- 오늘 업로드 미션 제공

### 구현 난이도
중

### 수익화 가능 여부
있음

### 구현 포인트
- 기존 활용
  - `Media`
  - `AccessLog`
  - `ClickLog`
- 신규 테이블
  - `content_assets`
  - `content_performance_daily`

---

## 4. 관리자 기능

### 4-1. 기능명
신고 / 악성유저 대응 고도화 콘솔

### 설명
현재의 `Report`, `ModerationController`, `ForbiddenWord`, `UserModerationAction`를 통합 운영 콘솔로 고도화한다.

### 왜 필요한지
- 커뮤니티/후기/미디어가 커질수록 운영 리스크가 급증한다.
- 지금도 기반은 있으나 “위험도 기반 운영”은 부족하다.

### 핵심 기능 상세
- 유저 위험 점수
- 누적 신고 이력
- 노쇼 의심 점수
- 광고성 도배 패턴
- 반복 악성 계정 묶음 조회
- 자동 숨김 조건 설정

### 구현 난이도
상

### 수익화 가능 여부
없음

### 구현 포인트
- 대상 파일
  - `app/Http/Controllers/Admin/ModerationController.php`
  - `app/Services/ModerationService.php`
  - `resources/views/admin/moderation/reports.blade.php`
- 신규 테이블
  - `user_risk_scores`
  - `trust_events`

---

### 4-2. 기능명
광고/부스팅 상품 관리자

### 설명
배너 중심 광고를 성과형 노출 상품으로 확장한다.

### 왜 필요한지
- 수수료만으로 초기 성장 비용을 감당하기 어렵다.

### 핵심 기능 상세
- 광고 슬롯 유형
  - 홈 스폰서드 카드
  - 지역 탭 상단
  - 검색 결과 상단
  - MD 프로필 부스팅
- 성과 지표
  - 노출
  - 클릭
  - 문의
  - 확정

### 구현 난이도
중

### 수익화 가능 여부
있음

### 구현 포인트
- 기존 활용
  - `Banner`
  - `PushCampaign`
  - `PushInflowLog`
- 신규 테이블
  - `ad_slots`
  - `sponsored_placements`
  - `ad_performance_stats`

---

### 4-3. 기능명
CRM 자동화 운영툴

### 설명
현재 푸시 캠페인을 행동 기반 자동 트리거까지 확장한다.

### 왜 필요한지
- 현재는 푸시 발송은 가능하나 lifecycle 운영이 어렵다.

### 핵심 기능 상세
- 트리거 예시
  - 문의 작성 후 답변 미도착 30분
  - 상세 조회 후 미문의 2시간
  - 찜 후 미방문 1일
  - 파티 시작 전날
  - VIP 승급 직전
- 발송 채널
  - 인앱
  - 웹푸시
  - 앱푸시

### 구현 난이도
상

### 수익화 가능 여부
있음

### 구현 포인트
- 기존 활용
  - `PushCampaign`
  - `PushDeliveryLog`
  - `NotificationSetting`
  - `NotificationService`
- 신규 테이블
  - `automation_rules`
  - `automation_runs`
  - `campaign_segments`

---

## 5. 공통 핵심 성장 기능

### 5-1. 기능명
그룹 플래너 + 공동 예약

### 설명
친구들과 후보를 모으고 투표하고 최종 문의까지 이어지는 협업 도구다.

### 왜 필요한지
- 실제 나이트라이프 방문은 개인이 아닌 그룹 단위 의사결정이 많다.
- 이 기능은 네트워크 효과를 만들어 락인을 강화한다.

### 핵심 기능 상세
- 후보 저장
- 친구 초대 링크
- 지역/예산/장르 투표
- 리더가 최종 예약 문의
- 결과 히스토리 보관

### 구현 난이도
상

### 수익화 가능 여부
있음

### 구현 포인트
- 신규 테이블
  - `group_plans`
  - `group_plan_members`
  - `group_plan_candidates`
  - `group_plan_votes`

---

### 5-2. 기능명
라이브 나이트 맵

### 설명
현재 `NearbyService`, `GeoService`, `AccessLog`, `Inquiry` 데이터를 이용해 지역별 실시간 추천 맵을 만든다.

### 왜 필요한지
- 경쟁 서비스가 단순 리스트형일수록 지도 기반 실시간 경험이 차별점이 된다.

### 핵심 기능 상세
- 지역별 인기도 히트맵
- 현재 문의량
- 혼잡도 추정
- 1차/2차 이동 추천
- 도보/택시 예상 시간

### 구현 난이도
상

### 수익화 가능 여부
있음

### 구현 포인트
- 기존 활용
  - `GeoService`
  - `NearbyService`
  - `AccessLog`
  - `Inquiry`
- 신규 테이블
  - `live_area_stats`
  - `movement_recommendations`

---

### 5-3. 기능명
이미지/영상 콘텐츠 극대화

### 설명
지금의 `Media`를 이미지 중심 저장소에서 “예약 전환형 미디어 자산”으로 확장한다.

### 왜 필요한지
- 클럽/파티는 텍스트보다 분위기 전달이 중요하다.
- 경쟁 우위는 결국 고품질 현장 콘텐츠의 누적량이다.

### 핵심 기능 상세
- 영상 업로드 지원
- 세로형 숏폼 피드
- 콘텐츠 자산 태그
  - 장르
  - 지역
  - 드레스코드
  - 시간대
- 콘텐츠별 전환율 집계

### 구현 난이도
상

### 수익화 가능 여부
있음

### 구현 포인트
- 기존 활용
  - `Media`
  - `ImageOptimizer`
- 신규 테이블
  - `media_assets`
  - `media_asset_tags`
  - `media_conversion_stats`

---

### 5-4. 기능명
실험 프레임워크

### 설명
CTA, 추천 알고리즘, 광고 슬롯, 푸시 문구를 빠르게 실험할 수 있게 한다.

### 왜 필요한지
- 초기 성장 단계는 기능보다 실험 속도가 중요하다.

### 핵심 기능 상세
- 실험군 분배
- KPI 연결
- 기능 플래그
- 점진 롤아웃

### 구현 난이도
중

### 수익화 가능 여부
있음

### 구현 포인트
- 신규 테이블
  - `experiments`
  - `experiment_variants`
  - `experiment_assignments`
  - `experiment_metrics`

---

## 6. 경쟁사가 쉽게 따라오기 어려운 락인 기능

1. 나이트 패스포트
- 방문/후기/배지/등급/혜택 히스토리를 계정 자산으로 만든다.

2. 그룹 플래너
- 친구들과의 의사결정 이력이 남아 다른 서비스로 이동할 이유를 줄인다.

3. MD 관계 자산
- 특정 MD와의 예약 기록, 만족도, 선호 패키지를 누적한다.

4. 라이브 나이트 맵
- 지역별 실시간 문의/이동/인기 데이터를 기반으로 한 실시간 추천은 데이터 축적형 자산이다.

5. 개인화 취향 그래프
- 선호 장르, 예산, 지역, 시간대, 반응 콘텐츠를 축적한 추천 프로필은 복제가 어렵다.

6. 혜택 지갑
- 포인트가 아니라 실제 예약 우선권과 오프라인 혜택을 묶어 락인을 만든다.

7. 콘텐츠-전환 연결 그래프
- 어떤 콘텐츠가 문의와 확정으로 이어지는지 내부적으로 학습하는 구조를 만든다.

---

## 7. 운영 리스크 / 악용 가능성 / 대응 방안

### 예약 전환 기능
- 리스크: 허위 재고, 허위 가격
- 악용 가능성: MD가 과장 제안
- 대응 방안: 패키지 변경 로그, 확정 실패율 페널티, 관리자 검수

### 커뮤니티/미션
- 리스크: 저품질 글 양산
- 악용 가능성: 포인트 파밍, 셀프 좋아요
- 대응 방안: 품질 점수, 신고율 반영, 보상 상한선

### VIP/등급
- 리스크: 혜택 원가 증가
- 악용 가능성: 허위 방문 적립
- 대응 방안: 방문완료 조건 엄격화, 등급 혜택 원가 캡

### 위치 추천
- 리스크: 부정확한 혼잡도
- 악용 가능성: 문의량 조작
- 대응 방안: Inquiry, AccessLog, ClickLog 다중 시그널 결합

### 광고/부스팅
- 리스크: 추천 신뢰 하락
- 악용 가능성: 광고 과다 집행
- 대응 방안: 광고 슬롯 명시, 자연 추천과 분리 운영

### 푸시/CRM
- 리스크: 과발송
- 악용 가능성: 무분별 세그먼트 발송
- 대응 방안: 빈도 캡, 야간 발송 제한, 옵트아웃 강화

---

## 8. 단계별 로드맵

### MVP 이후 (0~3개월)
목표: 문의 전환율과 MD 응답 효율 개선

우선순위
1. 예약 퍼널 고도화
2. MD 리드 인박스 + SLA
3. CRM 자동화 1차
4. 신고/악성유저 대응 고도화 1차
5. 개인화 추천 2.0 1차

실제 개발 순서
1. `inquiries` 확장 마이그레이션
2. MD 문의 리스트 우선순위화
3. 홈 추천 점수 서비스 추가
4. 푸시 자동화 규칙 테이블 추가

### 성장기 (3~9개월)
목표: 체류시간, 재방문, 공급자 수익 확대

우선순위
1. 커뮤니티 미션
2. VIP/등급 시스템
3. 패키지/재고/업셀 관리
4. 콘텐츠 성과 분석 스튜디오
5. 광고/부스팅 MVP

실제 개발 순서
1. `reward_wallets`, `loyalty` 계열 도입
2. `reservation_packages`, `package_inventory` 도입
3. `content_performance_daily` 집계
4. `ad_slots`, `sponsored_placements` 도입

### 확장기 (9~18개월)
목표: 차별화, 락인, 비수수료 매출 확장

우선순위
1. 그룹 플래너
2. 라이브 나이트 맵
3. 영상/숏폼 피드
4. 실험 프레임워크
5. MD 정산/커미션 정식화

실제 개발 순서
1. 그룹 플래너 데이터모델 추가
2. 라이브 스탯 배치/실시간 집계 추가
3. 영상 업로드 파이프라인 추가
4. 실험/플래그 시스템 도입

---

## 9. 개발팀 전달용 최종 우선순위

### 바로 개발 가능한 1차 Epic
- EPIC-01: Inquiry Conversion Upgrade
- EPIC-02: MD Lead Inbox Upgrade
- EPIC-03: Personalized Ranking Layer
- EPIC-04: CRM Automation Layer
- EPIC-05: Trust & Safety Upgrade

### 매출 직결 2차 Epic
- EPIC-06: Reservation Package & Inventory
- EPIC-07: MD Commission & Settlement
- EPIC-08: Sponsored Placement System
- EPIC-09: Loyalty & VIP

### 차별화 3차 Epic
- EPIC-10: Group Planner
- EPIC-11: Live Night Map
- EPIC-12: Shortform Content Feed
- EPIC-13: Experimentation Platform

## 10. 결론

현재 서비스는 “기능 부족” 상태가 아니라 “데이터는 있는데 전환과 락인 구조가 약한 상태”다.

따라서 방향은 다음이 맞다.
- 문의를 예약 퍼널로 바꾼다
- MD를 운영자가 아니라 매출 파트너로 바꾼다
- 추천을 필터가 아니라 행동 기반 랭킹으로 바꾼다
- 커뮤니티를 게시판이 아니라 미션형 참여 구조로 바꾼다
- 푸시를 공지에서 CRM으로 바꾼다
- 광고를 배너에서 성과형 상품으로 바꾼다

이 문서는 현재 코드 구조를 기준으로 바로 구현 가능한 확장 경로만 정리한 것이다.
