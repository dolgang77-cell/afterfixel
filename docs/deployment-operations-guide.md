# VYBE 배포 & 운영 가이드

---

## 1. 로컬 개발 환경

### 필수 소프트웨어
- PHP 8.3+
- Composer
- MySQL 8.x
- (선택) Node.js 18+ — 현재 Vite 빌드 미사용, CDN으로 대체

### 처음 설정
```bash
cd /var/www/nightlife
composer install
cp .env.example .env
php artisan key:generate

# .env 수정: DB_DATABASE, DB_USERNAME, DB_PASSWORD

php artisan migrate
php artisan db:seed
php artisan storage:link
php artisan serve
# → http://127.0.0.1:8000
```

### 개발 서버 실행
```bash
php artisan serve                    # 기본 포트 8000
php artisan serve --port=8001        # 다른 포트
php artisan serve --host=0.0.0.0     # 외부 접근 허용
```

---

## 2. 환경변수 (.env) 설명

| 변수 | 운영값 | 설명 |
|------|--------|------|
| `APP_ENV` | `production` | `local`이면 디버그 정보 노출 |
| `APP_DEBUG` | `false` | `true`이면 에러 상세 노출. 운영에서 절대 true 금지! |
| `APP_URL` | `https://nightlife.ive.co.kr` | 전체 URL 생성의 기준 |
| `DB_CONNECTION` | `mysql` | |
| `DB_DATABASE` | `nightlife_db` | |
| `SESSION_DRIVER` | `database` | 세션을 DB에 저장 |
| `CACHE_STORE` | `database` | 캐시를 DB에 저장 |
| `QUEUE_CONNECTION` | `database` | 큐를 DB에 저장 |
| `LOG_STACK` | `daily` | 일별 로그 파일 로테이션 |
| `LOG_LEVEL` | `warning` | warning 이상만 기록 |

---

## 3. DB 마이그레이션 / 시드

```bash
# 마이그레이션 상태 확인
php artisan migrate:status

# 새 마이그레이션 적용
php artisan migrate --force          # 운영에서는 --force 필요

# 시드 실행 (테스트 데이터)
php artisan db:seed --force

# 특정 시더만
php artisan db:seed --class=ClubSeeder --force

# 마이그레이션 생성 (새 테이블/컬럼 추가 시)
php artisan make:migration add_new_field_to_clubs_table --table=clubs
```

**주의**: 마이그레이션 파일은 한번 실행되면 다시 실행되지 않습니다. 스키마 수정은 새 마이그레이션으로.

---

## 4. Production 배포 절차

### 자동 배포 (추천)
```bash
sudo bash deploy.sh
```

이 스크립트가 수행하는 작업:
1. `php artisan down` (유지보수 모드)
2. `git pull origin main`
3. `composer install --no-dev --optimize-autoloader`
4. `php artisan migrate --force`
5. `php artisan config:cache && route:cache && view:cache && event:cache`
6. `systemctl restart php8.3-fpm nightlife-worker`
7. `systemctl reload nginx`
8. `php artisan up`

### 수동 배포 (단계별)
```bash
php artisan down --retry=15
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo systemctl restart php8.3-fpm
sudo systemctl restart nightlife-worker
sudo systemctl reload nginx
php artisan up
```

### 배포 후 확인
```bash
# 자동 점검
bash ops/healthcheck.sh

# 수동 확인
curl -sI http://nightlife.ive.co.kr        # 200 확인
curl -sI http://nightlife.ive.co.kr/admin/login  # 200 확인
curl -s http://nightlife.ive.co.kr/api/home | head -1  # JSON 확인
```

---

## 5. 서비스 구성 (systemd)

| 서비스 | 파일 | 역할 |
|--------|------|------|
| `nginx` | `/etc/nginx/sites-available/nightlife` | 웹서버 |
| `php8.3-fpm` | `/etc/php/8.3/fpm/pool.d/nightlife.conf` | PHP 프로세스 |
| `nightlife-worker` | `/etc/systemd/system/nightlife-worker.service` | 큐 워커 (알림 발송 등) |
| `nightlife-scheduler.timer` | `/etc/systemd/system/nightlife-scheduler.timer` | 1분마다 스케줄 실행 |

### 서비스 관리 명령어
```bash
# 상태 확인
systemctl status nginx php8.3-fpm nightlife-worker nightlife-scheduler.timer

# 재시작
sudo systemctl restart php8.3-fpm
sudo systemctl restart nightlife-worker
sudo systemctl reload nginx

# 로그 확인
journalctl -u nightlife-worker -f       # 큐 워커 로그
journalctl -u nightlife-scheduler -n 20 # 스케줄러 최근 로그
```

---

## 6. 로그 확인

```bash
bash ops/logs.sh laravel      # 앱 로그 (에러, 경고)
bash ops/logs.sh error        # 에러만 필터링
bash ops/logs.sh nginx        # Nginx 접근/에러 로그
bash ops/logs.sh fpm          # PHP-FPM 에러/슬로우 로그
bash ops/logs.sh worker       # 큐 워커 로그
bash ops/logs.sh all          # 전체 요약
```

### 로그 파일 위치
| 로그 | 경로 |
|------|------|
| Laravel 앱 | `storage/logs/laravel-YYYY-MM-DD.log` |
| Nginx 접근 | `/var/log/nginx/nightlife-access.log` |
| Nginx 에러 | `/var/log/nginx/nightlife-error.log` |
| PHP-FPM 에러 | `/var/log/php-fpm/nightlife-error.log` |
| PHP-FPM 슬로우 | `/var/log/php-fpm/nightlife-slow.log` |
| 큐 워커 | `/var/log/nightlife/worker.log` |

---

## 7. 장애 대응

### 사이트 접속 불가
```bash
# 1) 서비스 상태 확인
systemctl status nginx php8.3-fpm

# 2) Nginx 설정 오류?
sudo nginx -t

# 3) PHP-FPM 소켓 존재?
ls -la /run/php/php8.3-fpm-nightlife.sock

# 4) 앱 에러 확인
tail -20 storage/logs/laravel-$(date +%Y-%m-%d).log

# 5) DB 연결 확인
php artisan tinker --execute="DB::connection()->getPdo(); echo 'OK';"
```

### 500 에러 발생 시
```bash
# 1) 로그 확인
bash ops/logs.sh error

# 2) 캐시 초기화
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
sudo systemctl restart php8.3-fpm

# 3) 디버그 모드 임시 활성화 (원인 파악 후 반드시 끄기)
# .env에서 APP_DEBUG=true → php artisan config:clear → 확인 → APP_DEBUG=false 복원
```

### 롤백
```bash
# git으로 이전 커밋으로 복원
git log --oneline -5              # 이전 커밋 확인
git checkout <이전_커밋_hash>     # 해당 커밋으로 이동
sudo bash deploy.sh               # 재배포
```

---

## 8. Android 앱 빌드

```bash
cd /var/www/nightlife/android

# 환경변수 설정
export JAVA_HOME=/usr/lib/jvm/java-21-openjdk-amd64
export ANDROID_HOME=/opt/android-sdk

# 디버그 APK (테스트용)
bash build-apk.sh debug
# → app/build/outputs/apk/debug/app-debug.apk

# 릴리스 APK (서명됨)
bash build-apk.sh release
# → app/build/outputs/apk/release/app-release.apk

# Play Store 제출용 AAB
bash build-apk.sh bundle
# → app/build/outputs/bundle/release/app-release.aab
```

### 버전 올리기
`android/app/build.gradle.kts`에서:
```kotlin
versionCode = 2          // 정수, 매번 +1
versionName = "1.1.0"    // 사용자 표시 버전
```

---

## 9. 캐시 관리

```bash
# 운영 최적화 (배포 시 자동 실행)
php artisan config:cache     # .env → PHP 배열 캐시
php artisan route:cache      # 라우트 → 직렬화 캐시
php artisan view:cache       # Blade → 컴파일 캐시
php artisan event:cache      # 이벤트 → 캐시

# 개발 중 캐시 초기화
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# OPcache 초기화 (코드 수정이 반영 안 될 때)
sudo systemctl restart php8.3-fpm
```

**핵심**: 운영 서버에서 코드를 수정하면 반드시 `php artisan config:cache && route:cache && view:cache` 후 `systemctl restart php8.3-fpm` 실행.
