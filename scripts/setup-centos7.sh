#!/bin/bash
# VYBE 서버 셋업 스크립트 (CentOS 7 + Apache + PHP 8.3 + MariaDB)
# 사용법: sudo bash scripts/setup-centos7.sh

set -e
echo "=========================================="
echo "  VYBE Server Setup (CentOS 7)"
echo "=========================================="

# 색상
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

log() { echo -e "${GREEN}[✓]${NC} $1"; }
warn() { echo -e "${YELLOW}[!]${NC} $1"; }
fail() { echo -e "${RED}[✗]${NC} $1"; exit 1; }

# Root 확인
[ "$EUID" -ne 0 ] && fail "root 권한으로 실행하세요 (sudo)"

# ── 1. 레포 설치 ──
log "EPEL + Remi 레포 설치..."
yum install -y epel-release
yum install -y https://rpms.remirepo.net/enterprise/remi-release-7.rpm
yum install -y yum-utils

# ── 2. PHP 8.3 설치 ──
log "PHP 8.3 설치..."
yum-config-manager --disable 'remi-php*'
yum-config-manager --enable remi-php83
yum install -y php php-fpm php-mysqlnd php-pdo php-gd php-mbstring \
    php-xml php-curl php-zip php-intl php-exif php-opcache php-bcmath \
    php-json php-fileinfo php-process

PHP_VER=$(php -v | head -1 | grep -oP '\d+\.\d+')
echo "PHP version: $PHP_VER"
[[ "$PHP_VER" < "8.3" ]] && fail "PHP 8.3+ 필요. 현재: $PHP_VER"
log "PHP $PHP_VER 설치 완료"

# ── 3. Apache 설정 ──
log "Apache 설정..."
yum install -y httpd mod_ssl

cat > /etc/httpd/conf.d/nightlife.conf << 'APACHECONF'
<VirtualHost *:80>
    ServerName nightlife.ive.co.kr
    DocumentRoot /var/www/nightlife/public

    <Directory /var/www/nightlife/public>
        AllowOverride All
        Require all granted
        Options -Indexes +FollowSymLinks
    </Directory>

    LimitRequestBody 20971520
    ErrorLog /var/log/httpd/nightlife-error.log
    CustomLog /var/log/httpd/nightlife-access.log combined
</VirtualHost>
APACHECONF

# mod_rewrite 활성화 확인
httpd -M 2>/dev/null | grep -q rewrite_module || warn "mod_rewrite 미활성화 - 확인 필요"

# ── 4. PHP 설정 ──
log "PHP 설정 최적화..."
PHP_INI="/etc/php.ini"
sed -i 's/^upload_max_filesize.*/upload_max_filesize = 10M/' $PHP_INI
sed -i 's/^post_max_size.*/post_max_size = 20M/' $PHP_INI
sed -i 's/^memory_limit.*/memory_limit = 256M/' $PHP_INI
sed -i 's/^max_execution_time.*/max_execution_time = 60/' $PHP_INI

# OPcache + JIT
cat >> /etc/php.d/10-opcache.ini << 'OPCACHE'
opcache.jit=1255
opcache.jit_buffer_size=64M
OPCACHE

# ── 5. Composer 설치 ──
if ! command -v composer &> /dev/null; then
    log "Composer 설치..."
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
fi
log "Composer $(composer --version 2>/dev/null | grep -oP '\d+\.\d+\.\d+')"

# ── 6. 프로젝트 설정 ──
PROJECT_DIR="/var/www/nightlife"
if [ -d "$PROJECT_DIR" ]; then
    log "프로젝트 디렉토리 발견: $PROJECT_DIR"

    cd $PROJECT_DIR
    composer install --no-dev --optimize-autoloader 2>&1 | tail -3

    # 권한
    chown -R apache:apache storage bootstrap/cache
    chmod -R 775 storage bootstrap/cache

    # storage 심링크
    php artisan storage:link 2>/dev/null || true

    # 업로드 디렉토리
    for dir in uploads clubs parties md reviews community push; do
        mkdir -p storage/app/public/$dir/thumbs
    done
    chown -R apache:apache storage/app/public
    chmod -R 775 storage/app/public

    # 캐시
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    log "프로젝트 설정 완료"
else
    warn "프로젝트가 $PROJECT_DIR에 없습니다. 먼저 파일을 복사하세요."
fi

# ── 7. 크론 설정 ──
log "크론 설정..."
CRON_LINE="* * * * * cd /var/www/nightlife && php artisan schedule:run >> /dev/null 2>&1"
(crontab -u apache -l 2>/dev/null; echo "$CRON_LINE") | sort -u | crontab -u apache -

# ── 8. 서비스 시작 ──
log "서비스 시작..."
systemctl enable httpd
systemctl restart httpd
systemctl enable php-fpm
systemctl restart php-fpm

# ── 9. 방화벽 ──
if systemctl is-active --quiet firewalld; then
    firewall-cmd --permanent --add-service=http
    firewall-cmd --permanent --add-service=https
    firewall-cmd --reload
    log "방화벽 HTTP/HTTPS 허용"
fi

# ── 10. SELinux ──
if getenforce 2>/dev/null | grep -q "Enforcing"; then
    warn "SELinux Enforcing 모드. 웹 서버 접근 권한 설정 필요:"
    echo "  setsebool -P httpd_can_network_connect 1"
    echo "  chcon -R -t httpd_sys_rw_content_t /var/www/nightlife/storage"
    echo "  chcon -R -t httpd_sys_rw_content_t /var/www/nightlife/bootstrap/cache"
fi

echo ""
echo "=========================================="
echo "  셋업 완료!"
echo "=========================================="
echo ""
echo "다음 단계:"
echo "  1. .env 파일 설정 (DB 정보, APP_URL 등)"
echo "  2. php artisan migrate --force"
echo "  3. SSL 인증서 설치 (certbot 등)"
echo "  4. https://nightlife.ive.co.kr/ 접속 확인"
echo ""
warn "CentOS 7은 EOL입니다. 장기 운영 시 OS 업그레이드를 권장합니다."
