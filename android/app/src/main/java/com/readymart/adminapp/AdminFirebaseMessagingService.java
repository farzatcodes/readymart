package com.readymart.adminapp;

import android.app.NotificationChannel;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.content.Context;
import android.content.Intent;
import android.os.Build;
import android.util.Log;

import androidx.core.app.NotificationCompat;

import com.google.firebase.messaging.FirebaseMessagingService;
import com.google.firebase.messaging.RemoteMessage;

import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URLEncoder;
import java.nio.charset.StandardCharsets;

public class AdminFirebaseMessagingService extends FirebaseMessagingService {

    private static final String TAG        = "ReadyMartFCM";
    private static final String CHANNEL_ID = "readymart_orders";

    // ─── TODO: change this to your actual domain ──────────────────────────
    private static final String REGISTER_URL = "https://YOUR_DOMAIN.com/admin/register_device.php";
    // ─────────────────────────────────────────────────────────────────────

    /** Called when FCM issues a fresh token (first install or token refresh). */
    @Override
    public void onNewToken(String token) {
        super.onNewToken(token);
        Log.d(TAG, "New FCM token: " + token);
        registerWithServer(token);
    }

    /** Called when a notification arrives while the app is in the foreground. */
    @Override
    public void onMessageReceived(RemoteMessage message) {
        String title = "New Order";
        String body  = "A new order has been placed!";
        String url   = null;

        if (message.getNotification() != null) {
            title = message.getNotification().getTitle();
            body  = message.getNotification().getBody();
        }
        if (!message.getData().isEmpty()) {
            if (message.getData().containsKey("title")) title = message.getData().get("title");
            if (message.getData().containsKey("body"))  body  = message.getData().get("body");
            url = message.getData().get("url");
        }

        showNotification(title, body, url);
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    private void showNotification(String title, String body, String deepLinkUrl) {
        NotificationManager nm = (NotificationManager) getSystemService(Context.NOTIFICATION_SERVICE);

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            NotificationChannel ch = new NotificationChannel(
                    CHANNEL_ID,
                    "Order Notifications",
                    NotificationManager.IMPORTANCE_HIGH);
            ch.setDescription("Notifies when a new order is placed");
            nm.createNotificationChannel(ch);
        }

        Intent intent = new Intent(this, MainActivity.class);
        if (deepLinkUrl != null) intent.putExtra("url", deepLinkUrl);
        intent.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP | Intent.FLAG_ACTIVITY_SINGLE_TOP);

        PendingIntent pi = PendingIntent.getActivity(
                this, 0, intent,
                PendingIntent.FLAG_UPDATE_CURRENT | PendingIntent.FLAG_IMMUTABLE);

        NotificationCompat.Builder builder = new NotificationCompat.Builder(this, CHANNEL_ID)
                .setSmallIcon(R.drawable.ic_notification)
                .setContentTitle(title)
                .setContentText(body)
                .setStyle(new NotificationCompat.BigTextStyle().bigText(body))
                .setPriority(NotificationCompat.PRIORITY_HIGH)
                .setAutoCancel(true)
                .setContentIntent(pi);

        nm.notify((int) System.currentTimeMillis(), builder.build());
    }

    private void registerWithServer(final String token) {
        new Thread(() -> {
            try {
                URL endpoint = new URL(REGISTER_URL);
                HttpURLConnection conn = (HttpURLConnection) endpoint.openConnection();
                conn.setRequestMethod("POST");
                conn.setDoOutput(true);
                conn.setConnectTimeout(10_000);
                conn.setReadTimeout(10_000);
                conn.setRequestProperty("Content-Type", "application/x-www-form-urlencoded");

                String body = "token=" + URLEncoder.encode(token, "UTF-8");
                try (OutputStream os = conn.getOutputStream()) {
                    os.write(body.getBytes(StandardCharsets.UTF_8));
                }

                int code = conn.getResponseCode();
                Log.d(TAG, "Token registration HTTP " + code);
                conn.disconnect();
            } catch (Exception e) {
                Log.e(TAG, "Token registration failed: " + e.getMessage());
            }
        }).start();
    }
}
