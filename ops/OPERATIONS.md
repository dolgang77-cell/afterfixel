# NITE 운영 매뉴얼

## 시스템 구성

```
[클라이언트] → [Nginx :80] → [PHP-FPM unix sock] → [Laravel]
                                                        ├── MySQL (nightlife_db)
                                                        ├── Queue Worker (systemd)
                                                        └── Scheduler (systemd timer, 1분)
```

| 구성요소 | 버전/경로 |
|---------|----------|
| PHP | 8.3 |
| Laravel | 13.x |
| Nginx | `/etc/nginx/sites-available/nightlife` |
| PHP-FPM Pool | `/etc/php/8.3/fpm/pool.d/nightlife.conf` |
| 앱 경로 | `/var/www/nightlife` |
| 로그 | `/var/log/nightlife/`, `storage/logs/` |
| DB | MySQL `nightlife_db` |

---

## 1. 배포 절차

### 일반 배포 (코드 업데이트)
```bash
cd /var/www/nightlife
sudo bash deploy.sh
```
deploy.sh가 순서대로 실행:
1. 유지보수 모드 진입
2. git pull
3. composer install --no-dev
4. DB 마이그레이션
5. 캐시 최적화 (config/route/view/event)
6. PHP-FPM + Worker 재시작, Nginx 리로드
7. 유지보수 모드 해제

### 최초 설치
```bash
sudo bash deploy.sh --fresh
# .env 수정 후
sudo bash deploy.sh
```

### 수동 배포 (단계별)
```bash
php artisan down --retry=15
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan event:cache
sudo systemctl restart php8.3-fpm
sudo systemctl restart nightlife-worker
sudo systemctl reload nginx
php artisan up
```

---

## 2. 서비스 관리 명령어

### 시작/중지/재시작
```bash
# Nginx
sudo systemctl start|stop|restart|reload nginx

# PHP-FPM
sudo systemctl start|stop|restart php8.3-fpm

# Queue Worker
sudo systemctl start|stop|restart nightlife-worker

# Scheduler Timer
sudo systemctl start|stop nightlife-scheduler.timer
```

### 상태 확인
```bash
# 전체 상태 점검
bash ops/healthcheck.sh

# 개별 상태
systemctl status nginx
systemctl status php8.3-fpm
systemctl status nightlife-worker
systemctl status nightlife-scheduler.timer
systemctl list-timers | grep nightlife
```

### 유지보수 모드
```bash
php artisan down --retry=30 --refresh=15    # 진입
php artisan up                               # 해제
```

---

## 3. 로그 확인

### 스크립트 사용
```bash
bash ops/logs.sh laravel      # 앱 로그
bash ops/logs.sh error        # 에러만
bash ops/logs.sh nginx        # Nginx 로그
bash ops/logs.sh fpm          # PHP-FPM 로그
bash ops/logs.sh worker       # 큐 워커 로그
bash ops/logs.sh scheduler    # 스케줄러 로그
bash ops/logs.sh all          # 전체 요약
```

### 직접 확인
```bash
# Laravel 앱 로그 (일별 로테이션)
tail -f storage/logs/laravel-$(date +%Y-%m-%d).log

# Nginx
tail -f /var/log/nginx/nightlife-access.log
tail -f /var/log/nginx/nightlife-error.log

# PHP-FPM 슬로우 로그
cat /var/log/php-fpm/nightlife-slow.log

# 큐 워커
journalctl -u nightlife-worker -f
```

---

## 4. DB 관리

### 마이그레이션
```bash
php artisan migrate --force              # 새 마이그레이션 적용
php artisan migrate:status               # 적용 상태 확인
php artisan migrate:rollback --force     # 마지막 배치 롤백
```

### 시드 (테스트 데이터)
```bash
php artisan db:seed --force              # 전체 시드
php artisan db:seed --class=ClubSeeder --force   # 특정 시더만
```

### 백업
```bash
mysqldump -u nightlife_user -p nightlife_db > backup_$(date +%Y%m%d_%H%M%S).sql
```

---

## 5. 캐시 관리

```bash
# 전체 캐시 재생성 (배포 시 자동 실행)
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 캐시 초기화 (디버깅 시)
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# OPcache 초기화 (코드 변경 후 반영 안 될 때)
sudo systemctl restart php8.3-fpm
```

---

## 6. 장애 대응

### 사이트 접속 불가
```bash
# 1. Nginx 확인
systemctl status nginx
nginx -t                    # 설정 문법 확인

# 2. PHP-FPM 확인
systemctl status php8.3-fpm
ls -la /run/php/php8.3-fpm-nightlife.sock   # 소켓 존재?

# 3. 앱 에러 확인
tail -20 storage/logs/laravel-$(date +%Y-%m-%d).log

# 4. DB 연결 확인
php artisan tinker --execute="DB::connection()->getPdo(); echo 'OK';"
```

### 500 에러
```bash
# 1. 디버그 모드 임시 활성화 (주의: 끝나면 반드시 끄기)
# .env에서 APP_DEBUG=true 후
php artisan config:clear
# 에러 확인 후
# APP_DEBUG=false 복원 + php artisan config:cache

# 2. 로그 확인
bash ops/logs.sh error

# 3. 권한 문제 확인
ls -la storage/logs/
chown -R www-data:www-data storage bootstrap/cache
```

### 큐 워커 중단
```bash
systemctl status nightlife-worker      # 상태 확인
journalctl -u nightlife-worker -n 50   # 에러 로그
sudo systemctl restart nightlife-worker
```

### OOM (메모리 부족)
```bash
free -h                    # 메모리 현황
# PHP-FPM pool 설정 조정
sudo vi /etc/php/8.3/fpm/pool.d/nightlife.conf
# pm.max_children 줄이기
sudo systemctl restart php8.3-fpm
```

---

## 7. 보안 점검 항목

| 항목 | 확인 방법 | 기대값 |
|-----|----------|-------|
| APP_DEBUG | `grep APP_DEBUG .env` | `false` |
| APP_ENV | `grep APP_ENV .env` | `production` |
| .env 노출 | `curl -s http://DOMAIN/.env` | 404 또는 403 |
| 디렉토리 리스팅 | `curl -s http://DOMAIN/storage/` | 404 또는 403 |
| 관리자 접근 | `/admin` | 로그인 필요 |
| HTTPS | `curl -sI https://DOMAIN` | 200 + SSL |
| DB 비밀번호 | `.env` 외 노출 없음 | - |
| 파일 권한 | `stat -c %a .env` | 640 이하 |

### HTTPS 설정 (Let's Encrypt)
```bash
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d nightlife.ive.co.kr
# 자동 갱신 확인
sudo certbot renew --dry-run
```

### .env 권한 강화
```bash
chmod 640 .env
chown root:www-data .env
```

---

## 8. 스케줄 작업

| 스케줄 | 명령어 | 설명 |
|-------|--------|-----|
| 매 10분 | `nite:send-party-reminders` | 파티 시작 리마인더 발송 |
| 금 18:00 | `nite:send-tonight` | 금요일 오늘밤 추천 알림 |
| 토 18:00 | `nite:send-tonight` | 토요일 오늘밤 추천 알림 |

```bash
# 스케줄 목록 확인
php artisan schedule:list

# 수동 실행
php artisan schedule:run
```

---

## 9. 주요 URL

| 용도 | URL |
|-----|-----|
| 사용자 홈 | `http://nightlife.ive.co.kr` |
| 관리자 | `http://nightlife.ive.co.kr/admin` |
| 관리자 로그인 | `http://nightlife.ive.co.kr/admin/login` |
| API 상태 | `http://nightlife.ive.co.kr/tonight/status` |

---

## 10. 파일 구조 요약

```
/var/www/nightlife/
├── app/                    # 애플리케이션 코드
├── bootstrap/cache/        # 프레임워크 캐시 (쓰기 필요)
├── config/                 # 설정 파일
├── database/migrations/    # DB 스키마
├── deploy/                 # 배포 설정 파일
│   ├── nginx.conf
│   ├── php-fpm-pool.conf
│   ├── nightlife-worker.service
│   ├── nightlife-scheduler.service
│   └── nightlife-scheduler.timer
├── ops/                    # 운영 스크립트
│   ├── healthcheck.sh      # 상태 점검
│   ├── logs.sh             # 로그 확인
│   └── OPERATIONS.md       # 이 문서
├── public/                 # 웹 루트 (Nginx root)
│   ├── index.php
│   ├── storage -> ../storage/app/public
│   ├── sw.js               # Service Worker
│   └── manifest.json       # PWA manifest
├── storage/                # 파일 저장소 (쓰기 필요)
│   ├── app/public/         # 업로드 파일
│   ├── framework/          # 세션, 캐시, 뷰 컴파일
│   └── logs/               # 앱 로그
├── .env                    # 환경변수 (git 미포함)
├── .env.example            # 환경변수 템플릿
├── deploy.sh               # 배포 스크립트
└── composer.json
```
