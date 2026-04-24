# VYBE 다음 작업 체크포인트

> 저장 시점: 2026-04-22  
> 목적: 이번 작업 세션 이후 바로 이어서 진행할 수 있도록, 남은 핵심 작업을 우선순위대로 정리한 메모입니다.

---

## 1. 최우선 작업

### 1-1. 전국 `운영형 카드` 추가 정리

현재 `실이벤트` / `운영형 카드` 분류와 배지 노출은 반영되어 있습니다.  
다음 단계는 아직 남아 있는 전국 운영형 카드 중 표현이 과하거나 근거가 약한 카드를 더 줄이는 것입니다.

작업 기준:

- 확정 게스트/라인업처럼 보이는 표현 제거
- 공식 운영시간/예약 가이드/최근 운영 패턴 정도만 남기기
- 실제 특집 이벤트와 같은 날짜에 충돌할 수 있는 반복 카드 추가 정리
- 근거가 약한 반복 템플릿은 삭제하고 `legacyPartyPrefixes()`에 정리 대상 prefix 추가

주요 파일:

- `app/Support/CuratedNightlifeData.php`
- `app/Models/Party.php`

검증:

```bash
php -l app/Support/CuratedNightlifeData.php
php artisan nightlife:sync-curated-data
curl -I http://127.0.0.1/parties
curl -I http://127.0.0.1/search?q=파티
curl -I http://127.0.0.1/tonight
```

---

## 2. 그다음 작업

### 2-1. 전국 클럽 최신성 재검수

전국 확장은 반영했지만, 운영 흔적이 애매한 장소는 계속 주기적으로 다시 확인해야 합니다.

우선 점검할 내용:

- 오래된 폐점 정보와 현재 운영 정보가 섞이는 장소
- 재오픈/이전 이력이 있는 장소
- 제휴 없는 상태에서 과도하게 상세한 파티 템플릿이 붙은 장소
- 지역 확장 후 실제 검색 수요가 있는 도시의 대표 장소 정확도

실무 기준:

- 운영 근거가 약하면 클럽은 설명을 보수적으로 바꾸고
- 파티는 운영형 카드부터 줄입니다.
- 폐업이 확실하면 `is_active`를 내리거나 큐레이션 목록에서 제거합니다.

주요 파일:

- `app/Support/CuratedNightlifeData.php`
- `docs/nightlife-data-operations-guide.md`

---

## 3. UI/메시지 보강

### 3-1. 자동 문구 중 `운영형 카드` 설명이 약한 화면 추가 점검

이미 홈, 파티, 검색, 비교, Near Me, 오늘밤 추천, 알림까지 주요 화면은 반영했습니다.  
다음에는 아래를 다시 눈으로 보면서 문구가 어색하지 않은지 점검합니다.

점검 대상:

- 홈 카드 문구
- 저장 필터 기반 파티 알림 문구
- 푸시/알림함의 파티 문구
- 비교 화면과 지도 배지 요약 문구

목표:

- `운영형 카드`가 사용자를 속이는 느낌 없이 보일 것
- `실이벤트`가 실제 확인 이벤트라는 점이 더 분명할 것

주요 파일:

- `app/Services/NotificationService.php`
- `app/Services/TonightService.php`
- `app/Services/NearbyService.php`
- `resources/views/home.blade.php`
- `resources/views/notifications/index.blade.php`

---

## 4. 장애 대응 후속

### 4-1. 홈 500 재제보 시 즉시 확인 순서 유지

현재 기준으로 홈(`/`)은 로컬과 외부 IP 모두 `HTTP 200`입니다.  
다만 브라우저 캐시/PWA 서비스워커 때문에 사용자 환경에서만 홈 500처럼 보일 가능성은 계속 염두에 둡니다.

재제보 시 바로 할 일:

1. `curl -I http://127.0.0.1/`
2. `curl -I http://183.111.6.101/`
3. `storage/logs/laravel.log`의 같은 시각 새 에러 확인
4. 서비스워커/브라우저 캐시 여부 확인

관련 파일:

- `public/sw.js`
- `resources/views/layouts/app.blade.php`
- `docs/troubleshooting-guide.md`

---

## 5. 이어서 작업할 때 시작 순서

다음 세션에서는 아래 순서로 시작하면 됩니다.

1. `docs/nightlife-data-operations-guide.md`와 이 문서를 읽고 현재 운영 기준 확인
2. `app/Support/CuratedNightlifeData.php`에서 운영형 카드/특집 이벤트 잔여 이슈 확인
3. 보수적 정리 후 `php artisan nightlife:sync-curated-data` 실행
4. 홈/파티/검색/오늘밤/알림 `HTTP 200` 확인
5. 필요하면 새로 정리한 내용을 운영 가이드 문서에 다시 반영

---

## 6. 관련 문서

- [index.md](index.md)
- [nightlife-data-operations-guide.md](nightlife-data-operations-guide.md)
- [developer-handover-manual.md](developer-handover-manual.md)
- [troubleshooting-guide.md](troubleshooting-guide.md)
