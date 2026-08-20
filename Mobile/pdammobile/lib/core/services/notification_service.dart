import 'package:laravel_echo/laravel_echo.dart';
import 'package:pusher_client/pusher_client.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:dio/dio.dart';
import 'dart:convert';
import 'package:flutter/material.dart';
import '../../main.dart';
import '../../features/riwayat/presentation/riwayat_page.dart';

import 'api_client.dart';

class NotificationService {
  static final NotificationService _instance = NotificationService._internal();
  factory NotificationService() => _instance;
  NotificationService._internal();

  late Echo echo;
  final FlutterLocalNotificationsPlugin _localNotifications = FlutterLocalNotificationsPlugin();
  bool _isInitialized = false;

  Future<void> init() async {
    if (_isInitialized) return;

    // Inisialisasi plugin notifikasi lokal
    const AndroidInitializationSettings initializationSettingsAndroid =
        AndroidInitializationSettings('@mipmap/ic_launcher');
    const DarwinInitializationSettings initializationSettingsDarwin =
        DarwinInitializationSettings(
      requestAlertPermission: true,
      requestBadgePermission: true,
      requestSoundPermission: true,
    );
    const InitializationSettings initializationSettings = InitializationSettings(
      android: initializationSettingsAndroid,
      iOS: initializationSettingsDarwin,
    );

    await _localNotifications.initialize(
      settings: initializationSettings,
      onDidReceiveNotificationResponse: (details) {
        if (details.payload != null && details.payload != 'pdam_data_update') {
          try {
            final data = jsonDecode(details.payload!) as Map<String, dynamic>;
            _navigateToHistory(data);
          } catch (e) {
            _navigateToHistory();
          }
        } else {
          _navigateToHistory();
        }
      },
    );

    // Buat Notification Channel untuk Android (Wajib untuk notif berbunyi di Android 8.0+)
    const AndroidNotificationChannel channel = AndroidNotificationChannel(
      'pdam_notifications', 
      'Sistem Notifikasi PDAM',
      description: 'Notifikasi real-time update data PDAM',
      importance: Importance.max,
      playSound: true,
      enableVibration: true,
    );

    await _localNotifications
        .resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>()
        ?.createNotificationChannel(channel);

    // Request izin notifikasi untuk Android 13+
    await _localNotifications
        .resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>()
        ?.requestNotificationsPermission();
        
    // Inisialisasi Firebase Messaging untuk FCM
    FirebaseMessaging messaging = FirebaseMessaging.instance;
    await messaging.requestPermission(
      alert: true,
      badge: true,
      sound: true,
    );

    // Ambil token FCM perangkat secara asinkron agar tidak memblokir loading screen
    sendFcmTokenToServer();

    // Handle klik notifikasi FCM saat aplikasi di Foreground
    FirebaseMessaging.onMessage.listen((RemoteMessage message) {
      debugPrint('🔔 [FCM Foreground]: ${message.notification?.title}');
      if (message.notification != null) {
        _showLocalNotification(
          message.notification!.body ?? 'Ada pembaruan data',
          payload: jsonEncode(message.data),
        );
      }
    });

    // Handle klik notifikasi dari state BACKGROUND
    FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
      _navigateToHistory(message.data);
    });

    // Handle klik notifikasi dari state TERMINATED
    messaging.getInitialMessage().then((initialMessage) {
      if (initialMessage != null) {
        WidgetsBinding.instance.addPostFrameCallback((_) {
          Future.delayed(const Duration(milliseconds: 500), () => _navigateToHistory(initialMessage.data));
        });
      }
    });

    try {
      PusherOptions options = PusherOptions(
        host: '192.168.0.117',
        wsPort: 8080,
        encrypted: false,
      );

      PusherClient pusher = PusherClient(
        '53zobiecpnse0yxedlks', // VITE_REVERB_APP_KEY
        options,
        autoConnect: false,
        enableLogging: true,
      );

      echo = Echo(
        broadcaster: EchoBroadcasterType.Pusher,
        client: pusher,
      );

      pusher.connect();

      // Listen ke public channel
      echo.channel('public-notifications').listen('.DataUpdatedEvent', (dynamic e) {
        debugPrint('🔔 [Pusher Event Received]: $e');
        try {
          String? messageText;
          
          if (e is Map) {
            messageText = e['message'];
          } else if (e != null) {
             // Coba parse jika e adalah String (JSON) atau object yang bisa di-toString
             final dataMap = json.decode(e.toString()) as Map;
             messageText = dataMap['message'];
          }

          if (messageText != null) {
            _showLocalNotification(messageText, payload: e is String ? e : jsonEncode(e));
          }
        } catch (err) {
          debugPrint('Error parsing notification data: $err');
        }
      });

      _isInitialized = true;
    } catch (e) {
      debugPrint('Error initializing Echo: $e');
    }
  }

  Future<void> _showLocalNotification(String message, {String? payload}) async {
    const AndroidNotificationDetails androidPlatformChannelSpecifics =
        AndroidNotificationDetails(
      'pdam_notifications', 
      'Sistem Notifikasi PDAM',
      channelDescription: 'Notifikasi real-time update data PDAM',
      importance: Importance.max,
      priority: Priority.high,
      showWhen: true,
      icon: '@mipmap/ic_launcher',
    );
    
    const NotificationDetails platformChannelSpecifics = 
        NotificationDetails(android: androidPlatformChannelSpecifics);
        
    await _localNotifications.show(
      id: DateTime.now().millisecond, // id random
      title: 'Tirta Flow - Update Data',
      body: message,
      notificationDetails: platformChannelSpecifics,
      payload: payload ?? 'pdam_data_update',
    );
  }

  Future<void> sendFcmTokenToServer([String? token]) async {
    try {
      final fcmToken = token ?? await FirebaseMessaging.instance.getToken();
      if (fcmToken == null) return;

      final prefs = await SharedPreferences.getInstance();
      final authToken = prefs.getString('auth_token');
      if (authToken != null && authToken.isNotEmpty) {
        final dio = Dio();
        await dio.post(
          '${ApiClient.baseUrl}/auth/fcm-token',
          data: {'fcm_token': fcmToken},
          options: Options(
            headers: {
              'Authorization': 'Bearer $authToken',
              'Accept': 'application/json',
            },
          ),
        );
        debugPrint('✅ [FCM Token] Berhasil dikirim ke backend');
      }
    } catch (e) {
      debugPrint('❌ [FCM Token] Gagal mengirim ke backend: $e');
    }
  }

  void _navigateToHistory([Map<String, dynamic>? payloadData]) {
    final context = navigatorKey.currentContext;
    if (context != null) {
      String? kategori;
      String? lokasiName;
      String? asetName;
      String? noSr;
      
      if (payloadData != null) {
        kategori = payloadData['kategori'];
        lokasiName = payloadData['nama_lokasi'];
        asetName = payloadData['nama_aset'];
        noSr = payloadData['no_sr'];
      }
      
      Navigator.of(context).push(
        MaterialPageRoute(builder: (_) => RiwayatPage(
          filterKategori: kategori,
          filterLokasiName: (lokasiName != null && lokasiName.isNotEmpty) ? lokasiName : null,
          filterAsetName: (asetName != null && asetName.isNotEmpty) ? asetName : null,
          filterNoSr: (noSr != null && noSr.isNotEmpty) ? noSr : null,
        )),
      );
    }
  }
}
