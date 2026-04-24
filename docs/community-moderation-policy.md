# VYBE 커뮤니티 운영 정책

> 신고, 금칙어, 제재, 자동 숨김 등 커뮤니티 운영 시스템을 설명하는 문서입니다.

---

## 1. 신고 시스템

### 신고 대상

| 대상 | target_type | 설명 |
|------|-------------|------|
| 커뮤니티 게시글 | `community_post` | 사용자가 작성한 커뮤니티 글 |
| 후기 | `review` | 클럽/파티에 대한 후기 |
| 미디어 | `media` | 업로드된 이미지 |

### 신고 사유

| 코드 | 의미 |
|------|------|
| `abuse` | 욕설/비하/혐오 |
| `spam` | 스팸/광고 |
| `adult` | 성인/선정적 콘텐츠 |
| `false_info` | 허위 정보 |
| `privacy` | 개인정보 노출 |
| `other` | 기타 (상세 사유 직접 입력) |

### 중복 방지

- 동일 사용자가 동일 콘텐츠에 대해 한 번만 신고 가능
- `reporter_id` + `target_type` + `target_id` unique 제약
- 중복 시도 시 422 에러 반환

### 자동 숨김 임계값

- 기본값: **5건** (`auto_hide_report_threshold`)
- `/admin/moderation/policies`에서 변경 가능
- 임계값을 넘으면 해당 콘텐츠가 자동으로 `is_hidden=true` 처리

---

## 2. 자동 숨김 흐름

```
사용자가 콘텐츠 신고
        │
        ▼
ReportController@store
  → 중복 확인 (reporter + target unique)
  → Report 생성 (status=pending)
        │
        ▼
ModerationService::processReport()
  → 해당 콘텐츠의 총 신고 건수 조회
  → ModerationPolicy에서 auto_hide_report_threshold 조회 (캐시 5분)
        │
        ├── 신고 수 < 임계값 → 종료
        │
        └── 신고 수 >= 임계값
                │
                ▼
        ModerationService::autoHide()
          → 대상 콘텐츠 is_hidden = true
          → moderation_logs에 자동 숨김 기록
```

### 추가 확인 사항

- `ModerationService`는 신고 처리 시 `User::canWrite()`, `User::canUpload()`, 스팸 제한(`checkSpamLimit()`)도 함께 확인합니다.

---

## 3. 금칙어 필터

### 매칭 방식

| match_type | 설명 | 예시 |
|------------|------|------|
| `exact` | 정확히 일치하는 단어만 차단 | "욕설" → "욕설"만 매칭, "욕설이야"는 패스 |
| `contains` | 포함하는 텍스트 차단 | "욕설" → "이 욕설이야"도 매칭 |
| `regex` | 정규식 패턴 매칭 | `욕.*설` → "욕!설", "욕 설" 등 매칭 |

### 동작 유형

| action_type | 설명 | 현재 적용 |
|-------------|------|----------|
| `block` | 저장 자체를 차단 (422 에러) | 적용 중 |
| `mask` | 해당 단어를 `***`로 마스킹 후 저장 | 향후 적용 |
| `review` | 저장 후 검토 대기 상태로 전환 | 향후 적용 |
| `warn` | 경고 메시지 표시 후 저장 허용 | 향후 적용 |

### 적용 위치

- `CommunityController@store` — 커뮤니티 글쓰기
- `ReviewController@store` — 후기 작성

### 정규화 처리

- 입력 텍스트에서 공백/특수문자를 정규화하여 우회 시도를 방지합니다.
- 예: "욕 설", "욕.설", "욕!설" → 정규화 후 "욕설"로 검사

### 캐시

- 활성 금칙어 목록은 **5분** 동안 캐시됩니다.
- 금칙어 추가/삭제/활성화 토글 시 캐시가 즉시 초기화됩니다.

---

## 4. 제재 단계

### 제재 유형 (경중 순서)

| 단계 | action_type | 효과 |
|------|-------------|------|
| 1. 경고 | `warning` | 기록만 남김, 활동 제한 없음 |
| 2. 글쓰기 제한 | `restrict_write` | 커뮤니티/후기 작성 불가 (`canWrite()=false`) |
| 3. 업로드 제한 | `restrict_upload` | 이미지 업로드 불가 (`canUpload()=false`) |
| 4. 정지 | `suspend` | 글쓰기+업로드 불가, `user.status=suspended` |
| 5. 차단 | `ban` | 글쓰기+업로드 불가, `user.status=banned` |

### 기간 설정

- **임시 제재**: `starts_at` ~ `ends_at` 기간 동안만 유효 (일 단위)
- **영구 제재**: `is_permanent=true`, 관리자가 수동으로 해제할 때까지 유효

### 제재 적용/해제 흐름

```
관리자가 제재 적용 (/admin/moderation/banned-users)
        │
        ├── warning → 기록만 남김
        ├── restrict_write → User::canWrite() = false
        ├── restrict_upload → User::canUpload() = false
        ├── suspend → user.status = 'suspended'
        └── ban → user.status = 'banned'

관리자가 제재 해제
        │
        └── suspend/ban 해제 시 → user.status = 'active' 복원
```

### User 모델 메서드

- `canWrite()`: `restrict_write`, `suspend`, `ban` 중 하나라도 활성이면 `false`
- `canUpload()`: `restrict_upload`, `suspend`, `ban` 중 하나라도 활성이면 `false`
- `moderationActions()`: 해당 사용자의 전체 제재 이력 관계

---

## 5. 운영 정책 설정

`moderation_policies` 테이블에서 key/value 쌍으로 관리됩니다. 5분 캐시 적용.

| 정책 키 | 기본값 | 설명 |
|---------|--------|------|
| `auto_hide_report_threshold` | 5 | 신고 N건 이상 시 자동 숨김 |
| `spam_post_limit_per_hour` | 5 | 시간당 게시글 작성 제한 수 |
| `review_limit_per_day` | 10 | 일일 후기 작성 제한 수 |
| `image_upload_limit_per_post` | 5 | 게시글당 이미지 업로드 수 제한 |
| `forbidden_word_default_action` | block | 금칙어 기본 동작 (block/mask/review/warn) |

관리자는 `/admin/moderation/policies`에서 모든 값을 편집할 수 있습니다.

---

## 6. 관련 DB 테이블

| 테이블 | 설명 |
|--------|------|
| `reports` | 신고 기록 (reporter_id, target_type, target_id, reason, detail, status) |
| `forbidden_words` | 금칙어 목록 (word, match_type, action_type, category, severity, is_active) |
| `user_moderation_actions` | 제재 기록 (user_id, action_type, reason, starts_at, ends_at, is_permanent) |
| `moderation_policies` | 운영 정책 (key, value) |
| `moderation_logs` | 자동 숨김 등 운영 관련 로그 |

마이그레이션 파일: `database/migrations/2026_04_15_700000_create_moderation_tables.php`

---

## 7. 관련 파일 목록

| 구분 | 파일 |
|------|------|
| 모델 | `app/Models/Report.php`, `ForbiddenWord.php`, `UserModerationAction.php`, `ModerationPolicy.php` |
| 서비스 | `app/Services/ForbiddenWordFilter.php`, `app/Services/ModerationService.php` |
| 컨트롤러 | `app/Http/Controllers/ReportController.php`, `Admin/ModerationController.php` |
| User 모델 | `app/Models/User.php` — `canWrite()`, `canUpload()`, `moderationActions()` |
| 적용 컨트롤러 | `CommunityController@store` (금칙어+스팸+제재), `ReviewController@store` (금칙어+제재) |
| 뷰 (관리자) | `resources/views/admin/moderation/reports.blade.php`, `banned-users.blade.php`, `forbidden-words.blade.php`, `policies.blade.php` |
| 뷰 (사용자) | `resources/views/components/report-button.blade.php` |
| 라우트 | `routes/web.php` (POST /reports), `routes/admin.php` (moderation/*) |
