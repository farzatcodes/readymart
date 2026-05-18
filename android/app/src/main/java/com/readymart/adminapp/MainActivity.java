package com.readymart.adminapp;

import android.annotation.SuppressLint;
import android.content.Intent;
import android.net.Uri;
import android.os.Bundle;
import android.webkit.WebChromeClient;
import android.webkit.WebResourceRequest;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import androidx.appcompat.app.AppCompatActivity;

public class MainActivity extends AppCompatActivity {

    // ─── TODO: change this to your actual admin URL ───────────────────────
    static final String ADMIN_URL = "https://YOUR_DOMAIN.com/admin/";
    // ─────────────────────────────────────────────────────────────────────

    private WebView webView;

    @SuppressLint("SetJavaScriptEnabled")
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        webView = findViewById(R.id.webview);

        WebSettings ws = webView.getSettings();
        ws.setJavaScriptEnabled(true);
        ws.setDomStorageEnabled(true);
        ws.setLoadWithOverviewMode(true);
        ws.setUseWideViewPort(true);
        ws.setBuiltInZoomControls(false);
        ws.setSupportZoom(false);
        ws.setMixedContentMode(WebSettings.MIXED_CONTENT_ALWAYS_ALLOW);

        webView.setWebChromeClient(new WebChromeClient());
        webView.setWebViewClient(new WebViewClient() {
            @Override
            public boolean shouldOverrideUrlLoading(WebView view, WebResourceRequest request) {
                Uri uri = request.getUrl();
                String scheme = uri.getScheme();
                if (scheme == null) return false;

                switch (scheme) {
                    case "tel":
                    case "mailto":
                    case "whatsapp":
                    case "sms":
                    case "intent":
                        try {
                            Intent intent = new Intent(Intent.ACTION_VIEW, uri);
                            if (intent.resolveActivity(getPackageManager()) != null) {
                                startActivity(intent);
                            }
                        } catch (Exception ignored) {}
                        return true;   // consumed — don't load in WebView

                    default:
                        return false;  // let WebView handle http/https
                }
            }
        });

        // If launched from a notification, deep-link to the order
        String deepLink = getIntent().getStringExtra("url");
        webView.loadUrl(deepLink != null ? "https://YOUR_DOMAIN.com" + deepLink : ADMIN_URL);
    }

    @Override
    public void onBackPressed() {
        if (webView.canGoBack()) {
            webView.goBack();
        } else {
            super.onBackPressed();
        }
    }
}
