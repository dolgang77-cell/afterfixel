# VYBE 트러블슈팅 가이드

> 문제가 생겼을 때 여기서 찾으세요.

---

## composer install 안 될 때

**증상**: `Your requirements could not be resolved...` 또는 메모리 부족

**가능한 원인**:
- PHP 버전이 8.3 미만
- Composer 버전이 오래됨
- 메모리 부족

**확인**:
```bash
php -v              # 8.3+ 확인
composer --version   # 2.x 확인
```

**해결**:
```bash
# 메모리 부족 시
COMPOSER_MEMORY_LIMIT=-1 composer install

# PHP 확장 누락 시
sudo apt install php8.3-mbstring php8.3-xml php8.3-mysql php8.3-curl
```

---

## .env 문제

**증상**: 모든 페이지에서 500 에러, "No application encryption key"

**확인**:
```bash
ls -la .env           # 파일 존재?
grep APP_KEY .env     # 키 값이 비어있지 않은지?
```

**해결**:
```bash
cp .env.example .env
php artisan key:generate
# .env 열어서 DB_* 값 수정
```

**재발 방지**: `.env` 파일은 git에 포함되지 않으므로, 새 환경에서 항상 수동 생성 필요.

---

## DB 연결 안 될 때

**증상**: `SQLSTATE[HY000] [1045] Access denied` 또는 `Connection refused`

**확인**:
```bash
# MySQL 실행 중?
systemctl status mysql

# .env의 DB 정보가 맞는지?
grep DB_ .env

# 직접 연결 테스트
mysql -u nightlife_user -p nightlife_db -e "SELECT 1"
```

**해결**:
```bash
# MySQL 재시작
sudo systemctl restart mysql

# DB/사용자가 없는 경우
mysql -u root -e "CREATE DATABASE nightlife_db CHARACTER SET utf8mb4"
mysql -u root -e "CREATE USER 'nightlife_user'@'127.0.0.1' IDENTIFIED BY '비밀번호'"
mysql -u root -e "GRANT ALL ON nightlife_db.* TO 'nightlife_user'@'127.0.0.1'"
```

---

## 마이그레이션 꼬였을 때

**증상**: `Table already exists` 또는 `Column not found`

**확인**:
```bash
php artisan migrate:status    # 어디까지 실행됐는지 확인
```

**해결**:
```bash
# 방법 1: 특정 마이그레이션만 롤백
php artisan migrate:rollback --step=1 --force

# 방법 2: 전체 초기화 (데이터 삭제! 운영에서 쓰지 말 것!)
php artisan migrate:fresh --force
php artisan db:seed --force

# 방법 3: 수동으로 DB 테이블 수정
mysql -u nightlife_user -p nightlife_db
> ALTER TABLE ... ;
# 그 다음 migrations 테이블에서 해당 마이그레이션 행을 수동 삽입
```

**재발 방지**: 마이그레이션 파일은 이미 실행된 것을 수정하지 마세요. 항상 새 마이그레이션을 만드세요.

---

## API 에러 날 때

**증상**: API가 500 반환, 또는 의도하지 않은 HTML 반환

**확인**:
```bash
# JSON 응답 확인
curl -s -H "Accept: application/json" http://nightlife.ive.co.kr/api/home | head -5

# 에러 로그 확인
bash ops/logs.sh error
```

**해결**:
- 422 응답: 입력값 검증 실패. `details` 필드에 어떤 값이 잘못됐는지 나옴
- 404 응답: 해당 ID의 리소스가 없음
- 429 응답: Rate limit 초과. 잠시 후 재시도
- 500 응답: 서버 에러. `storage/logs/` 확인

---

## 추천 결과가 비정상일 때

**증상**: 오늘밤 추천이나 Near Me에서 결과가 안 나옴/이상한 결과

**확인**:
```bash
# 1) 오늘 날짜의 파티가 있는지?
php artisan tinker --execute="echo Party::today()->count();"

# 2) 활성 클럽이 있는지?
php artisan tinker --execute="echo Club::active()->count();"

# 3) 현재 시간대 확인
php artisan tinker --execute="print_r(App\Services\TonightService::getCurrentTimeSlot());"
```

**해결**:
- 파티가 없으면: 관리자에서 오늘 날짜 파티 등록, 또는 시더 실행
- 시간대가 `daytime`이면: 18시 이후에 테스트하거나, `getCurrentTimeSlot('22:00')`으로 시간 오버라이드
- 점수가 0이면: `TonightService`의 `scoreTonightParty()`에서 조건 확인

추가 확인:
```bash
# 전국 큐레이션 동기화가 필요한지 확인
php artisan nightlife:sync-curated-data

# 지난 파티가 남아 있는지 확인
php artisan tinker --execute="echo App\\Models\\Party::whereDate('event_date', '<', today())->count();"
```

운영 기준:
- `실이벤트`와 `운영형 카드`는 구분해서 봐야 합니다.
- 운영형 카드를 실이벤트처럼 과장해 등록하면 추천 품질이 떨어집니다.
- 전국 데이터 정리 기준은 `docs/nightlife-data-operations-guide.md`를 참고하세요.

---

## Near Me 결과가 안 나올 때

**증상**: `/nearby?lat=...&lng=...`에서 "근처에 추천할 곳이 없습니다"

**확인**:
```bash
# 클럽에 좌표가 있는지?
php artisan tinker --execute="echo Club::active()->whereNotNull('lat')->count();"

# 거리 계산이 정상인지?
php artisan tinker --execute="
echo App\Services\GeoService::haversineDistance(37.55, 126.92, 37.5540, 126.9223);
"
```

**해결**:
- 좌표 없음: 관리자에서 클럽의 lat/lng 입력
- 반경 밖: `NearbyService::DEFAULT_RADIUS_KM` 값 확인 (기본 5km)
- 폴백 제안: 반경 확장 링크가 나와야 함

---

## 알림이 안 갈 때

**증상**: 파티 등록했는데 알림이 안 옴

**확인**:
```bash
# 큐 워커 실행 중?
systemctl status nightlife-worker

# 스케줄러 실행 중?
systemctl status nightlife-scheduler.timer

# 알림 DB에 기록됐는지?
php artisan tinker --execute="echo App\Models\NiteNotification::latest()->first()?->toJson();"

# 사용자의 알림 설정 확인
php artisan tinker --execute="
\$pref = App\Models\NotificationSetting::first();
echo json_encode(\$pref?->toArray(), JSON_PRETTY_PRINT);
"
```

**해결**:
```bash
# 큐 워커 재시작
sudo systemctl restart nightlife-worker

# 수동으로 스케줄 실행
php artisan schedule:run

# 수동으로 알림 명령 실행
php artisan nite:send-party-reminders
php artisan nite:send-tonight
```

---

## 홈만 500 뜨고 다른 페이지는 정상일 때

**증상**: `/clubs`, `/parties`는 열리는데 홈(`/`)만 500처럼 보임

이 프로젝트에서는 실제 PHP 에러가 아니라 브라우저 캐시/PWA 서비스워커 때문에 홈만 오래된 응답을 붙잡는 경우가 있습니다.

**확인**:
```bash
curl -I http://127.0.0.1/
curl -I http://183.111.6.101/
curl -I http://127.0.0.1/parties
```

**판단 기준**:
- 로컬과 외부 `curl` 모두 200이면, 서버 장애보다 브라우저 캐시 가능성이 큽니다.
- `storage/logs/laravel.log`에 같은 시각의 새 에러가 없으면, 실제 서버 500이 아닐 수 있습니다.
- 홈만 문제고 다른 문서형 페이지가 정상이면 서비스워커와 HTML 캐시를 먼저 의심하세요.

**해결**:
- 브라우저 강력 새로고침
- 서비스워커 업데이트 이후 재접속
- 배포 직후에는 홈, 파티, 검색을 같이 열어 최신 리소스가 반영됐는지 확인

---

## 관리자 로그인 안 될 때

**증상**: "이메일 또는 비밀번호가 일치하지 않습니다" 또는 "관리자 권한이 없습니다"

**확인**:
```bash
# 관리자 계정이 있는지?
php artisan tinker --execute="
echo App\Models\User::whereIn('role', ['admin','super_admin'])->get(['id','email','role'])->toJson();
"
```

**해결**:
```bash
# 관리자 시더 실행
php artisan db:seed --class=AdminSeeder --force

# 또는 비밀번호 직접 리셋
php artisan tinker --execute="
\$u = App\Models\User::where('email','admin@nite.kr')->first();
\$u->password = bcrypt('admin1234!');
\$u->save();
echo 'done';
"
```

---

## Android 빌드 실패

**증상**: `./gradlew assembleDebug` 실패

**확인**:
```bash
# Java 설치?
java -version     # 17+ 필요

# Android SDK 설치?
echo $ANDROID_HOME
ls $ANDROID_HOME/platforms/

# 서명키 존재?
ls android/app/nite-release.keystore
```

**해결**:
```bash
# 환경변수 설정
export JAVA_HOME=/usr/lib/jvm/java-21-openjdk-amd64
export ANDROID_HOME=/opt/android-sdk

# Gradle 캐시 초기화
cd android && ./gradlew clean

# 메모리 부족 시
GRADLE_OPTS="-Xmx512m" ./gradlew assembleDebug --no-daemon
```

---

## 배포 후 빈 화면

**증상**: 배포 후 페이지가 빈 화면이거나 CSS 없이 텍스트만 나옴

**확인**:
```bash
# OPcache 문제일 가능성
sudo systemctl restart php8.3-fpm

# 캐시 문제
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Nginx 에러 로그
tail -20 /var/log/nginx/nightlife-error.log
```

**해결**:
```bash
# 전체 캐시 재생성 + 서비스 재시작
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo systemctl restart php8.3-fpm
sudo systemctl reload nginx
```

**재발 방지**: 배포 스크립트(`deploy.sh`)를 사용하면 이 과정이 자동으로 처리됩니다.

---

## 클럽 페이지가 느릴 때 (3초 이상)

**증상**: `/clubs` 페이지 로딩이 3초 이상 걸림. 외국어(EN/JA/ZH) 전환 시 특히 느림.

**원인**: 각 클럽 카드에서 `trans_auto()`가 area/genre/subgenre별로 개별 호출되어, 캐시 미스 시 건당 ~500ms의 Google Translate API 호출 발생. 20개 클럽 × 3회 = 최대 60회 API 호출.

**확인**:
```bash
# translation_cache에 해당 용어가 있는지 확인
php artisan tinker --execute="echo DB::table('translation_cache')->where('target_locale','en')->count();"
```

**해결**:
1. `ClubController@index`에서 `AutoTranslator::preloadBatch()` 호출이 있는지 확인
2. vibe/subgenre 사전 데이터가 `translation_cache`에 있는지 확인
3. 사전 데이터 누락 시 시더 재실행 또는 수동 INSERT

**재발 방지**: 새 목록 페이지에서 `trans_auto()`를 사용할 때는 반드시 컨트롤러에서 `preloadBatch()`를 먼저 호출하세요.

---

## 헤더 위치 버튼이 안 보일 때

**증상**: 헤더에 지역명(홍대, 강남 등) 버튼이 표시되지 않음

**확인**:
- `resources/views/components/layout/header.blade.php`에 `locationBtn()` Alpine.js 컴포넌트가 있는지 확인
- 브라우저 콘솔에서 위치 권한 거부 에러가 있는지 확인

**해결**:
- 위치 권한이 거부된 경우: localStorage의 `nite_last_area` 값이 폴백으로 사용됨. 없으면 "서울" 표시
- `locationBtn()` 컴포넌트가 누락된 경우: header.blade.php에 위치 버튼 복원 필요
- GPS 정확도 문제: `layouts/app.blade.php`에서 `enableHighAccuracy: true`, `timeout: 10000`, `maximumAge: 60000` 설정 확인

---

## 빠른 진단 명령어 모음

```bash
# 서비스 전체 상태 점검
bash ops/healthcheck.sh

# 에러 로그만 빠르게
bash ops/logs.sh error

# DB 연결 테스트
php artisan tinker --execute="DB::connection()->getPdo(); echo 'OK';"

# 라우트 목록
php artisan route:list

# 마이그레이션 상태
php artisan migrate:status

# 캐시 전체 초기화
php artisan optimize:clear
```
