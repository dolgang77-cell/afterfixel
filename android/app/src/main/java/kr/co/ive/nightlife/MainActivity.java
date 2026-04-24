package kr.co.ive.nightlife;

import android.Manifest;
import android.annotation.SuppressLint;
import androidx.appcompat.app.AppCompatActivity;
import android.content.pm.PackageManager;
import android.content.pm.PackageInfo;
import android.graphics.Color;
import android.net.Uri;
import android.os.Build;
import android.os.Bundle;
import android.provider.Settings;
import android.view.View;
import android.view.Window;
import android.view.WindowManager;
import android.webkit.GeolocationPermissions;
import android.webkit.JavascriptInterface;
import android.webkit.WebChromeClient;
import android.webkit.WebResourceRequest;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.FrameLayout;

import androidx.core.app.ActivityCompat;
import androidx.core.content.ContextCompat;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

public class MainActivity extends AppCompatActivity {

    private static final String BASE_URL = "http://nightlife.ive.co.kr";
    private static final int LOCATION_PERMISSION_REQUEST = 1001;

    private WebView webView;
    private String pendingGeolocationOrigin;
    private GeolocationPermissions.Callback pendingGeolocationCallback;

    @Override
    @SuppressLint("SetJavaScriptEnabled")
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        // 전체화면 + 상태바/네비바 색상
        setupWindow();

        // WebView 생성
        webView = new WebView(this);
        FrameLayout container = new FrameLayout(this);
        container.setBackgroundColor(Color.parseColor("#08080f"));
        container.addView(webView, new FrameLayout.LayoutParams(
                FrameLayout.LayoutParams.MATCH_PARENT,
                FrameLayout.LayoutParams.MATCH_PARENT));
        setContentView(container);

        // 시스템 바 인셋 처리 (상태바/네비바 영역만큼 패딩)
        ViewCompat.setOnApplyWindowInsetsListener(container, (v, windowInsets) -> {
            Insets insets = windowInsets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(insets.left, insets.top, insets.right, insets.bottom);
            return WindowInsetsCompat.CONSUMED;
        });

        // WebView 설정
        WebSettings settings = webView.getSettings();
        settings.setJavaScriptEnabled(true);
        settings.setDomStorageEnabled(true);
        settings.setGeolocationEnabled(true);
        settings.setMediaPlaybackRequiresUserGesture(false);
        settings.setAllowFileAccess(false);
        settings.setDatabaseEnabled(true);
        settings.setCacheMode(WebSettings.LOAD_DEFAULT);
        settings.setMixedContentMode(WebSettings.MIXED_CONTENT_ALWAYS_ALLOW);
        // 앱 버전/빌드 정보
        String appVersion = "1.0";
        int buildVersion = 1;
        try {
            PackageInfo pInfo = getPackageManager().getPackageInfo(getPackageName(), 0);
            appVersion = pInfo.versionName;
            buildVersion = (int) pInfo.getLongVersionCode();
        } catch (Exception ignored) {}

        settings.setUserAgentString(settings.getUserAgentString() + " NiteApp/" + appVersion);

        // 기기 식별자 (앱 범위 Android ID)
        @SuppressLint("HardwareIds")
        String androidId = Settings.Secure.getString(getContentResolver(), Settings.Secure.ANDROID_ID);
        String deviceId = "and_" + (androidId != null ? androidId : "unknown");

        // JavaScript Interface로 기기 정보 주입
        String finalAppVersion = appVersion;
        int finalBuildVersion = buildVersion;
        webView.addJavascriptInterface(new Object() {
            @JavascriptInterface
            public String getDeviceId() { return deviceId; }
            @JavascriptInterface
            public String getDeviceModel() { return Build.MODEL; }
            @JavascriptInterface
            public String getManufacturer() { return Build.MANUFACTURER; }
            @JavascriptInterface
            public String getOsVersion() { return Build.VERSION.RELEASE; }
            @JavascriptInterface
            public String getAppVersion() { return finalAppVersion; }
            @JavascriptInterface
            public int getBuildVersion() { return finalBuildVersion; }
        }, "NiteApp");

        // 외부 링크 처리
        webView.setWebViewClient(new WebViewClient() {
            @Override
            public boolean shouldOverrideUrlLoading(WebView view, WebResourceRequest request) {
                Uri uri = request.getUrl();
                String host = uri.getHost();
                // 내부 URL은 WebView에서 로드
                if (host != null && host.contains("nightlife.ive.co.kr")) {
                    return false;
                }
                // 외부 URL은 기본 브라우저로
                try {
                    android.content.Intent intent = new android.content.Intent(
                            android.content.Intent.ACTION_VIEW, uri);
                    startActivity(intent);
                } catch (Exception e) {
                    // 브라우저 없으면 무시
                }
                return true;
            }

            @Override
            public void onReceivedError(WebView view, int errorCode,
                                        String description, String failingUrl) {
                // 네트워크 오류 시 오프라인 페이지
                view.loadUrl(BASE_URL + "/offline");
            }
        });

        // 위치 권한 + 파일 업로드 처리
        webView.setWebChromeClient(new WebChromeClient() {
            @Override
            public void onGeolocationPermissionsShowPrompt(String origin,
                                                           GeolocationPermissions.Callback callback) {
                if (hasLocationPermission()) {
                    callback.invoke(origin, true, false);
                } else {
                    pendingGeolocationOrigin = origin;
                    pendingGeolocationCallback = callback;
                    requestLocationPermission();
                }
            }
        });

        // URL 로드 (딥링크 또는 기본 URL)
        String url = BASE_URL;
        if (getIntent() != null && getIntent().getData() != null) {
            Uri data = getIntent().getData();
            if (data.getHost() != null && data.getHost().contains("nightlife.ive.co.kr")) {
                url = data.toString();
            }
        }
        webView.loadUrl(url);
    }

    private void setupWindow() {
        Window window = getWindow();
        window.addFlags(WindowManager.LayoutParams.FLAG_DRAWS_SYSTEM_BAR_BACKGROUNDS);
        window.setStatusBarColor(Color.parseColor("#08080f"));
        window.setNavigationBarColor(Color.parseColor("#08080f"));

        // 엣지 투 엣지
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.R) {
            window.setDecorFitsSystemWindows(false);
        }

        // 상태바 아이콘 밝은 색 (어두운 배경)
        View decorView = window.getDecorView();
        int flags = decorView.getSystemUiVisibility();
        flags &= ~View.SYSTEM_UI_FLAG_LIGHT_STATUS_BAR;
        flags &= ~View.SYSTEM_UI_FLAG_LIGHT_NAVIGATION_BAR;
        decorView.setSystemUiVisibility(flags);
    }

    private boolean hasLocationPermission() {
        return ContextCompat.checkSelfPermission(this,
                Manifest.permission.ACCESS_FINE_LOCATION) == PackageManager.PERMISSION_GRANTED;
    }

    private void requestLocationPermission() {
        ActivityCompat.requestPermissions(this,
                new String[]{
                        Manifest.permission.ACCESS_FINE_LOCATION,
                        Manifest.permission.ACCESS_COARSE_LOCATION
                },
                LOCATION_PERMISSION_REQUEST);
    }

    @Override
    public void onRequestPermissionsResult(int requestCode, String[] permissions, int[] grantResults) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults);
        if (requestCode == LOCATION_PERMISSION_REQUEST && pendingGeolocationCallback != null) {
            boolean granted = grantResults.length > 0
                    && grantResults[0] == PackageManager.PERMISSION_GRANTED;
            pendingGeolocationCallback.invoke(pendingGeolocationOrigin, granted, false);
            pendingGeolocationCallback = null;
            pendingGeolocationOrigin = null;
        }
    }

    @Override
    public void onBackPressed() {
        if (webView != null && webView.canGoBack()) {
            webView.goBack();
        } else {
            super.onBackPressed();
        }
    }

    @Override
    protected void onResume() {
        super.onResume();
        if (webView != null) webView.onResume();
    }

    @Override
    protected void onPause() {
        if (webView != null) webView.onPause();
        super.onPause();
    }

    @Override
    protected void onDestroy() {
        if (webView != null) {
            webView.destroy();
            webView = null;
        }
        super.onDestroy();
    }
}
