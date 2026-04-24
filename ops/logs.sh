#!/bin/bash
###############################################################################
# NITE - 로그 확인 스크립트
# 사용법: bash ops/logs.sh [laravel|nginx|fpm|worker|scheduler|error]
###############################################################################

APP_DIR="/var/www/nightlife"
LINES=${2:-50}

case "${1:-laravel}" in
    laravel|app)
        echo "=== Laravel 애플리케이션 로그 (최근 ${LINES}줄) ==="
        tail -n "$LINES" "$APP_DIR/storage/logs/laravel-$(date +%Y-%m-%d).log" 2>/dev/null || \
        tail -n "$LINES" "$APP_DIR/storage/logs/laravel.log" 2>/dev/null || \
        echo "로그 파일 없음"
        ;;
    error|errors)
        echo "=== 최근 에러 로그 ==="
        grep -i "error\|exception\|fatal" "$APP_DIR/storage/logs/laravel-$(date +%Y-%m-%d).log" 2>/dev/null | tail -n "$LINES" || \
        echo "에러 없음"
        ;;
    nginx)
        echo "=== Nginx Access 로그 (최근 ${LINES}줄) ==="
        tail -n "$LINES" /var/log/nginx/nightlife-access.log 2>/dev/null || echo "파일 없음"
        echo ""
        echo "=== Nginx Error 로그 ==="
        tail -n 20 /var/log/nginx/nightlife-error.log 2>/dev/null || echo "파일 없음"
        ;;
    fpm|php)
        echo "=== PHP-FPM 에러 로그 (최근 ${LINES}줄) ==="
        tail -n "$LINES" /var/log/php-fpm/nightlife-error.log 2>/dev/null || echo "파일 없음"
        echo ""
        echo "=== PHP-FPM 슬로우 쿼리 ==="
        tail -n 20 /var/log/php-fpm/nightlife-slow.log 2>/dev/null || echo "파일 없음"
        ;;
    worker|queue)
        echo "=== Queue Worker 로그 (최근 ${LINES}줄) ==="
        tail -n "$LINES" /var/log/nightlife/worker.log 2>/dev/null || \
        journalctl -u nightlife-worker --no-pager -n "$LINES" 2>/dev/null || echo "파일 없음"
        ;;
    scheduler|cron)
        echo "=== Scheduler 로그 (최근 ${LINES}줄) ==="
        tail -n "$LINES" /var/log/nightlife/scheduler.log 2>/dev/null || \
        journalctl -u nightlife-scheduler --no-pager -n "$LINES" 2>/dev/null || echo "파일 없음"
        ;;
    all)
        for t in laravel nginx fpm worker scheduler; do
            bash "$0" "$t" 20
            echo ""
        done
        ;;
    *)
        echo "사용법: bash ops/logs.sh [laravel|error|nginx|fpm|worker|scheduler|all] [줄수]"
        ;;
esac
