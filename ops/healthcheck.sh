#!/bin/bash
###############################################################################
# NITE - 서비스 상태 점검 스크립트
# 사용법: bash ops/healthcheck.sh
###############################################################################
set -uo pipefail

APP_DIR="/var/www/nightlife"
DOMAIN="nightlife.ive.co.kr"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

pass() { echo -e "  ${GREEN}[OK]${NC}  $1"; }
fail() { echo -e "  ${RED}[FAIL]${NC} $1"; ERRORS=$((ERRORS+1)); }
warn() { echo -e "  ${YELLOW}[WARN]${NC} $1"; }

ERRORS=0

echo ""
echo "═══════════════════════════════════════"
echo "  NITE 서비스 상태 점검"
echo "  $(date '+%Y-%m-%d %H:%M:%S')"
echo "═══════════════════════════════════════"
echo ""

# ── 1. 서비스 프로세스 ──
echo "[ 서비스 프로세스 ]"
systemctl is-active --quiet nginx && pass "Nginx 실행 중" || fail "Nginx 중지됨"
systemctl is-active --quiet php8.3-fpm && pass "PHP-FPM 실행 중" || fail "PHP-FPM 중지됨"
systemctl is-active --quiet nightlife-worker && pass "Queue Worker 실행 중" || fail "Queue Worker 중지됨"
systemctl is-active --quiet nightlife-scheduler.timer && pass "Scheduler Timer 활성" || fail "Scheduler Timer 비활성"
echo ""

# ── 2. HTTP 응답 ──
echo "[ HTTP 응답 ]"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "http://$DOMAIN" --max-time 5 2>/dev/null || echo "000")
if [ "$HTTP_CODE" = "200" ]; then
    pass "홈페이지 200 OK ($DOMAIN)"
elif [ "$HTTP_CODE" = "503" ]; then
    warn "유지보수 모드 (503)"
else
    fail "홈페이지 응답 $HTTP_CODE"
fi

ADMIN_CODE=$(curl -s -o /dev/null -w "%{http_code}" "http://$DOMAIN/admin/login" --max-time 5 2>/dev/null || echo "000")
[ "$ADMIN_CODE" = "200" ] && pass "관리자 로그인 페이지 200" || fail "관리자 페이지 응답 $ADMIN_CODE"
echo ""

# ── 3. DB 연결 ──
echo "[ 데이터베이스 ]"
cd "$APP_DIR"
php artisan tinker --execute="try { DB::connection()->getPdo(); echo 'connected'; } catch(Exception \$e) { echo 'error: '.\$e->getMessage(); }" 2>/dev/null | grep -q "connected" \
    && pass "DB 연결 정상" || fail "DB 연결 실패"
echo ""

# ── 4. 디스크/로그 ──
echo "[ 디스크 / 로그 ]"
DISK_USAGE=$(df -h "$APP_DIR" | tail -1 | awk '{print $5}' | tr -d '%')
if [ "$DISK_USAGE" -lt 80 ]; then
    pass "디스크 사용량: ${DISK_USAGE}%"
elif [ "$DISK_USAGE" -lt 90 ]; then
    warn "디스크 사용량 높음: ${DISK_USAGE}%"
else
    fail "디스크 거의 가득 참: ${DISK_USAGE}%"
fi

LOG_SIZE=$(du -sh "$APP_DIR/storage/logs/" 2>/dev/null | awk '{print $1}')
pass "Laravel 로그 크기: ${LOG_SIZE:-0}"

# 최근 에러 확인
RECENT_ERRORS=$(tail -100 "$APP_DIR/storage/logs/laravel-$(date +%Y-%m-%d).log" 2>/dev/null | grep -c "ERROR" || echo "0")
if [ "$RECENT_ERRORS" -gt 0 ]; then
    warn "오늘 에러 로그 ${RECENT_ERRORS}건 (storage/logs/ 확인)"
else
    pass "오늘 에러 로그 없음"
fi
echo ""

# ── 5. 파일 권한 ──
echo "[ 파일 권한 ]"
STORAGE_OWNER=$(stat -c "%U" "$APP_DIR/storage" 2>/dev/null)
[ "$STORAGE_OWNER" = "www-data" ] && pass "storage/ 소유자 www-data" || fail "storage/ 소유자 $STORAGE_OWNER (www-data 아님)"

[ -w "$APP_DIR/storage/logs" ] && pass "storage/logs/ 쓰기 가능" || fail "storage/logs/ 쓰기 불가"
[ -L "$APP_DIR/public/storage" ] && pass "public/storage 심볼릭 링크 존재" || warn "public/storage 심볼릭 링크 없음"
echo ""

# ── 6. 환경설정 ──
echo "[ 환경설정 ]"
APP_DEBUG=$(grep "^APP_DEBUG=" "$APP_DIR/.env" | cut -d= -f2)
[ "$APP_DEBUG" = "false" ] && pass "APP_DEBUG=false" || fail "APP_DEBUG=$APP_DEBUG (운영에서는 false 필수)"

APP_ENV=$(grep "^APP_ENV=" "$APP_DIR/.env" | cut -d= -f2)
[ "$APP_ENV" = "production" ] && pass "APP_ENV=production" || warn "APP_ENV=$APP_ENV"

[ -f "$APP_DIR/bootstrap/cache/config.php" ] && pass "Config 캐시 존재" || warn "Config 캐시 없음 (php artisan config:cache)"
[ -f "$APP_DIR/bootstrap/cache/routes-v7.php" ] && pass "Route 캐시 존재" || warn "Route 캐시 없음 (php artisan route:cache)"
echo ""

# ── 7. SSL ──
echo "[ SSL/보안 ]"
HTTPS_CODE=$(curl -s -o /dev/null -w "%{http_code}" "https://$DOMAIN" --max-time 5 2>/dev/null || echo "000")
if [ "$HTTPS_CODE" = "200" ]; then
    pass "HTTPS 정상"
elif [ "$HTTPS_CODE" = "000" ]; then
    warn "HTTPS 미설정 (certbot --nginx -d $DOMAIN)"
else
    warn "HTTPS 응답 $HTTPS_CODE"
fi
echo ""

# ── 결과 ──
echo "═══════════════════════════════════════"
if [ "$ERRORS" -gt 0 ]; then
    echo -e "  ${RED}점검 실패: ${ERRORS}건의 문제 발견${NC}"
else
    echo -e "  ${GREEN}점검 통과: 모든 항목 정상${NC}"
fi
echo "═══════════════════════════════════════"
echo ""

exit $ERRORS
