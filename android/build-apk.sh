#!/bin/bash
###############################################################################
# NITE Android - APK / AAB 빌드 스크립트
# 사용법:
#   bash build-apk.sh debug       # 디버그 APK
#   bash build-apk.sh release     # 릴리스 APK (서명)
#   bash build-apk.sh bundle      # 릴리스 AAB (Play Store 제출용)
#   bash build-apk.sh all         # 전체 빌드
###############################################################################
set -euo pipefail

export JAVA_HOME=${JAVA_HOME:-/usr/lib/jvm/java-21-openjdk-amd64}
export ANDROID_HOME=${ANDROID_HOME:-/opt/android-sdk}

cd "$(dirname "$0")"

MODE="${1:-debug}"

case "$MODE" in
    debug)
        echo "=== Debug APK 빌드 ==="
        GRADLE_OPTS="-Xmx512m" ./gradlew assembleDebug --no-daemon
        APK=$(find app/build/outputs/apk/debug -name "*.apk" | head -1)
        echo ""
        echo "  APK: $APK ($(du -h "$APK" | cut -f1))"
        echo "  설치: adb install $APK"
        ;;
    release)
        echo "=== Release APK 빌드 (서명) ==="
        [ -f app/nite-release.keystore ] || { echo "ERROR: app/nite-release.keystore 없음"; exit 1; }
        GRADLE_OPTS="-Xmx512m" ./gradlew assembleRelease --no-daemon
        APK=$(find app/build/outputs/apk/release -name "*.apk" | head -1)
        echo ""
        echo "  APK: $APK ($(du -h "$APK" | cut -f1))"
        echo "  설치: adb install $APK"
        ;;
    bundle|aab)
        echo "=== Release AAB 빌드 (Play Store 제출용) ==="
        [ -f app/nite-release.keystore ] || { echo "ERROR: app/nite-release.keystore 없음"; exit 1; }
        GRADLE_OPTS="-Xmx512m" ./gradlew bundleRelease --no-daemon
        AAB=$(find app/build/outputs/bundle/release -name "*.aab" | head -1)
        echo ""
        echo "  AAB: $AAB ($(du -h "$AAB" | cut -f1))"
        echo "  Play Console에 업로드하세요."
        ;;
    all)
        bash "$0" debug
        echo ""
        bash "$0" release
        echo ""
        bash "$0" bundle
        ;;
    *)
        echo "사용법: bash build-apk.sh [debug|release|bundle|all]"
        exit 1
        ;;
esac
