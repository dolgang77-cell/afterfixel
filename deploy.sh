#!/usr/bin/env bash
###############################################################################
# NITE 배포 스크립트
# 사용법: bash deploy.sh [--fresh]
#   --fresh : 최초 설치 (서비스 파일 복사, 디렉토리 생성 포함)
###############################################################################
set -euo pipefail

APP_DIR="/var/www/nightlife"
LOG_DIR="/var/log/nightlife"
PHP_FPM="php8.3-fpm"

cd "$APP_DIR"

echo "═══════════════════════════════════════"
echo "  NITE 배포 시작  $(date '+%Y-%m-%d %H:%M:%S')"
echo "═══════════════════════════════════════"

#── 최초 설치 모드 ──
if [[ "${1:-}" == "--fresh" ]]; then
    echo ""
    echo "[1/F] 로그 디렉토리 생성..."
    sudo mkdir -p "$LOG_DIR" /var/log/php-fpm
    sudo chown www-data:www-data "$LOG_DIR"

    echo "[2/F] 서비스 파일 복사..."
    sudo cp deploy/nightlife-worker.service    /etc/systemd/system/
    sudo cp deploy/nightlife-scheduler.service /etc/systemd/system/
    sudo cp deploy/nightlife-scheduler.timer   /etc/systemd/system/
    sudo cp deploy/php-fpm-pool.conf           /etc/php/8.3/fpm/pool.d/nightlife.conf
    sudo cp deploy/nginx.conf                  /etc/nginx/sites-available/nightlife
    sudo ln -sf /etc/nginx/sites-available/nightlife /etc/nginx/sites-enabled/

    echo "[3/F] .env 확인..."
    if [ ! -f .env ]; then
        cp .env.example .env
        php artisan key:generate --force
        echo "  → .env 생성됨. DB 비밀번호 등을 수정하세요!"
        echo "  → 수정 후 다시 deploy.sh 를 실행하세요."
        exit 0
    fi

    echo "[4/F] 스토리지 심볼릭 링크..."
    php artisan storage:link --force 2>/dev/null || true

    echo "[5/F] systemd 데몬 리로드..."
    sudo systemctl daemon-reload
    sudo systemctl enable nightlife-worker nightlife-scheduler.timer

    echo ""
    echo "  최초 설치 완료. 이제 deploy.sh (인수 없이) 실행하세요."
    echo ""
    exit 0
fi

#── 일반 배포 ──
echo ""
echo "[1/7] 유지보수 모드 진입..."
php artisan down --retry=15 --refresh=5 || true

echo "[2/7] 코드 업데이트..."
git pull origin main 2>/dev/null || echo "  (git pull 스킵 - 수동 배포)"

echo "[3/7] Composer 의존성 설치..."
composer install --no-dev --optimize-autoloader --no-interaction --quiet

echo "[4/7] 마이그레이션..."
php artisan migrate --force

echo "[5/7] 캐시 최적화..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "[6/7] 서비스 재시작..."
sudo systemctl restart "$PHP_FPM"
sudo systemctl restart nightlife-worker
sudo systemctl reload nginx

echo "[7/7] 유지보수 모드 해제..."
php artisan up

echo ""
echo "═══════════════════════════════════════"
echo "  배포 완료  $(date '+%Y-%m-%d %H:%M:%S')"
echo "═══════════════════════════════════════"
echo ""
echo "  상태 확인:"
echo "    systemctl status $PHP_FPM"
echo "    systemctl status nginx"
echo "    systemctl status nightlife-worker"
echo "    curl -sI https://nightlife.ive.co.kr"
echo ""
