# NITE 배포 가이드

## 서버 요구사항

| 항목 | 최소 | 권장 |
|------|------|------|
| OS | Ubuntu 22.04+ | Ubuntu 24.04 LTS |
| PHP | 8.2 | 8.3 |
| MySQL | 8.0 | 8.0+ |
| Nginx | 1.18+ | 1.24+ |
| RAM | 1GB | 2GB+ |
| 디스크 | 10GB | 20GB+ |

PHP 확장: `mbstring`, `xml`, `curl`, `mysql`, `gd`, `zip`, `opcache`, `bcmath`

---

## 디렉토리 구조

```
/var/www/nightlife/
├── public/              ← Nginx document root
│   ├── index.php        ← 엔트리포인트
│   ├── icons/           ← PWA 아이콘
│   ├── manifest.json    ← PWA 매니페스트
│   ├── sw.js            ← Service Worker
│   └── storage/         ← 심볼릭 링크 → storage/app/public
├── storage/
│   ├── app/public/      ← 업로드 파일 (이미지 등)
│   ├── framework/cache/ ← 캐시
│   ├── framework/views/ ← 컴파일된 뷰
│   └── logs/            ← 애플리케이션 로그
├── deploy/              ← 배포 설정 파일
│   ├── nginx.conf
│   ├── php-fpm-pool.conf
│   ├── nightlife-worker.service
│   ├── nightlife-scheduler.service
│   └── nightlife-scheduler.timer
├── deploy.sh            ← 배포 스크립트
└── .env                 ← 환경변수 (git 제외)
```

---

## 최초 설치

```bash
# 1. 코드 배치
cd /var/www
git clone <repo-url> nightlife
cd nightlife

# 2. Composer 설치
composer install --no-dev --optimize-autoloader

# 3. 최초 설치 실행 (디렉토리, 서비스 파일, .env 생성)
bash deploy.sh --fresh

# 4. .env 편집 (DB 비밀번호 등)
nano .env

# 5. DB 테이블 생성 + 관리자 계정
php artisan migrate --force
php artisan db:seed --class=AdminUserSeeder --force

# 6. 스토리지 링크 + 권한
php artisan storage:link
sudo chown -R www-data:www-data storage bootstrap/cache

# 7. 배포 (캐시 최적화, 서비스 시작)
bash deploy.sh

# 8. SSL 인증서 (HTTPS)
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d nightlife.ive.co.kr

# 9. 서비스 시작
sudo systemctl start nightlife-worker
sudo systemctl start nightlife-scheduler.timer
```

---

## 일상 배포

```bash
cd /var/www/nightlife
bash deploy.sh
```

deploy.sh가 자동 수행하는 작업:
1. 유지보수 모드 진입
2. git pull
3. composer install --no-dev
4. migrate
5. config/route/view/event 캐시
6. PHP-FPM + 큐 워커 재시작, Nginx 리로드
7. 유지보수 모드 해제

---

## 서비스 관리 명령어

```bash
# ── 상태 확인 ──
systemctl status php8.3-fpm
systemctl status nginx
systemctl status nightlife-worker
systemctl status nightlife-scheduler.timer

# ── 재시작 ──
sudo systemctl restart php8.3-fpm
sudo systemctl restart nginx
sudo systemctl restart nightlife-worker

# ── 유지보수 모드 ──
php artisan down --retry=15      # 진입
php artisan up                   # 해제

# ── 큐 ──
php artisan queue:monitor         # 큐 상태 확인
php artisan queue:retry all       # 실패 작업 재시도

# ── 캐시 초기화 ──
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## 로그 확인

| 로그 | 경로 |
|------|------|
| 앱 로그 | `storage/logs/laravel-*.log` |
| Nginx 접근 | `/var/log/nginx/nightlife-access.log` |
| Nginx 에러 | `/var/log/nginx/nightlife-error.log` |
| PHP-FPM 에러 | `/var/log/php-fpm/nightlife-error.log` |
| PHP-FPM 슬로우 | `/var/log/php-fpm/nightlife-slow.log` |
| 큐 워커 | `/var/log/nightlife/worker.log` |
| 스케줄러 | `/var/log/nightlife/scheduler.log` |

```bash
# 실시간 앱 로그
tail -f storage/logs/laravel-$(date +%Y-%m-%d).log

# 에러만 필터
grep -i error storage/logs/laravel-$(date +%Y-%m-%d).log | tail -20

# Nginx 실시간
sudo tail -f /var/log/nginx/nightlife-error.log

# 슬로우 쿼리/요청
sudo tail -f /var/log/php-fpm/nightlife-slow.log
```

---

## 장애 대응 체크리스트

### 사이트 접속 불가 (502/504)

```bash
# 1. PHP-FPM 동작 확인
sudo systemctl status php8.3-fpm
# 죽어있으면 → sudo systemctl restart php8.3-fpm

# 2. 소켓 파일 확인
ls -la /run/php/php8.3-fpm-nightlife.sock
# 없으면 → PHP-FPM 재시작

# 3. Nginx 설정 확인
sudo nginx -t
sudo systemctl reload nginx

# 4. 디스크 공간
df -h
# 로그 용량 확인: du -sh storage/logs/ /var/log/nginx/
```

### 500 에러

```bash
# 1. 앱 로그 확인
tail -50 storage/logs/laravel-$(date +%Y-%m-%d).log

# 2. 권한 확인
ls -la storage/
sudo chown -R www-data:www-data storage bootstrap/cache

# 3. .env 확인
php artisan config:show app.debug   # false여야 함
php artisan config:show database    # DB 연결 확인

# 4. 캐시 꼬임 시
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

### DB 연결 오류

```bash
# 1. MySQL 상태
sudo systemctl status mysql

# 2. 연결 테스트
php artisan db:show

# 3. 마이그레이션 상태
php artisan migrate:status
```

### 큐 작업 안됨

```bash
# 1. 워커 상태
sudo systemctl status nightlife-worker
sudo journalctl -u nightlife-worker --since "1 hour ago"

# 2. 실패 작업 확인
php artisan queue:failed

# 3. 재시도
php artisan queue:retry all
sudo systemctl restart nightlife-worker
```

### 메모리 부족 (OOM)

```bash
# 1. 메모리 확인
free -h

# 2. PHP-FPM 프로세스 확인
ps aux | grep php-fpm | wc -l

# 3. pool 설정에서 max_children 줄이기
# deploy/php-fpm-pool.conf → pm.max_children 값 조정
sudo systemctl restart php8.3-fpm
```

### SSL 인증서 만료

```bash
# 인증서 상태 확인
sudo certbot certificates

# 수동 갱신
sudo certbot renew

# 자동 갱신 확인 (cron 등록 여부)
sudo systemctl status certbot.timer
```

---

## 운영 체크리스트

### 배포 전

- [ ] `.env`에 `APP_DEBUG=false` 확인
- [ ] `.env`에 `APP_ENV=production` 확인
- [ ] `APP_KEY` 생성 확인
- [ ] DB 비밀번호 강력한 값으로 설정
- [ ] `LOG_LEVEL=warning` 설정 (운영)
- [ ] `LOG_STACK=daily` 설정 (로그 로테이션)

### 배포 후

- [ ] `curl -sI https://nightlife.ive.co.kr` → 200 확인
- [ ] `https://nightlife.ive.co.kr/admin/login` 접속 확인
- [ ] 관리자 로그인 정상 동작
- [ ] 클럽/파티 목록 페이지 로드 확인
- [ ] PWA manifest 로드: `/manifest.json`
- [ ] Service Worker 등록: DevTools → Application 탭

### 정기 점검 (주 1회)

- [ ] `df -h` 디스크 공간 확인
- [ ] `free -h` 메모리 확인
- [ ] `php artisan queue:failed` 실패 작업 확인
- [ ] `storage/logs/` 오래된 로그 정리
- [ ] SSL 인증서 만료일 확인
- [ ] `sudo apt update && sudo apt upgrade` 보안 패치
