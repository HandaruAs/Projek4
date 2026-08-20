import 'package:dio/dio.dart';
import 'package:flutter_app/services/storage_service.dart';
import 'package:flutter_dotenv/flutter_dotenv.dart';

class NotificationApiService {
  static final NotificationApiService _instance =
      NotificationApiService._internal();
  factory NotificationApiService() => _instance;
  NotificationApiService._internal();

  // Pakai baseUrl yang sama dengan api_service.dart
  final Dio _dio = Dio(BaseOptions(
    baseUrl: dotenv.env['BASE_URL'] ?? 'http://localhost:8000/api',
    connectTimeout: const Duration(seconds: 30),
    receiveTimeout: const Duration(seconds: 30),
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
  ));

  final StorageService _storageService = StorageService();

  Future<void> _addAuthHeader() async {
    final token = await _storageService.getToken();

    // Debug — hapus setelah fix terkonfirmasi
    print(
        'TOKEN RETRIEVED: ${token != null ? "${token.substring(0, 20)}..." : "NULL"}');

    if (token != null && token.isNotEmpty) {
      _dio.options.headers['Authorization'] = 'Bearer $token';
    } else {
      // Hapus header lama kalau token tidak ada
      _dio.options.headers.remove('Authorization');
      print('WARNING: Token null atau kosong!');
    }
  }

  // ── GET /api/notifications ──────────────────────────────
  Future<List<Map<String, dynamic>>> fetchNotifications() async {
    try {
      await _addAuthHeader();
      final response = await _dio.get('/notifications');

      // ← tambah baris ini
      print('NOTIF RESPONSE: ${response.data}');

      if (response.data['success'] == true && response.data['data'] != null) {
        return List<Map<String, dynamic>>.from(response.data['data']);
      }
      return [];
    } on DioException catch (e) {
      // ← tambah baris ini juga
      print(
          'NOTIF ERROR: ${e.response?.data} | status: ${e.response?.statusCode}');
      return [];
    }
  }

  // ── POST /api/notifications/{id}/read ──────────────────
  Future<void> markRead(String id) async {
    try {
      await _addAuthHeader();
      await _dio.post('/notifications/$id/read');
    } on DioException catch (_) {}
  }

  // ── POST /api/notifications/read-all ───────────────────
  Future<void> markAllRead() async {
    try {
      await _addAuthHeader();
      await _dio.post('/notifications/read-all');
    } on DioException catch (_) {}
  }
}
