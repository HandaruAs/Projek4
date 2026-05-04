import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_app/services/storage_service.dart';

class ApiService {
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;
  ApiService._internal();

  final Dio _dio = Dio(BaseOptions(
    baseUrl: 'http://192.168.1.6:8000/api',
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
    if (token != null && token.isNotEmpty) {
      _dio.options.headers['Authorization'] = 'Bearer $token';
    }
  }

  // ══════════════════════════════════════════════════════════
  // AUTH
  // ══════════════════════════════════════════════════════════

  Future<Map<String, dynamic>> login(String email, String password) async {
    try {
      final response = await _dio.post(
        '/login',
        data: {'email': email, 'password': password},
      );
      return Map<String, dynamic>.from(response.data);
    } on DioException catch (e) {
      if (e.response != null)
        return Map<String, dynamic>.from(e.response!.data);
      throw _handleError(e);
    }
  }

  Future<Map<String, dynamic>> register(
    String name,
    String email,
    String password, {
    String role = 'user',
  }) async {
    try {
      final endpoint = role == 'admin' ? '/register/admin' : '/register/user';
      final response = await _dio.post(endpoint, data: {
        'name': name,
        'email': email,
        'password': password,
        'password_confirmation': password,
      });
      return Map<String, dynamic>.from(response.data);
    } on DioException catch (e) {
      if (e.response != null)
        return Map<String, dynamic>.from(e.response!.data);
      throw _handleError(e);
    }
  }

  Future<Map<String, dynamic>> forgotPassword(String email) async {
    try {
      final response = await _dio.post(
        '/forgot-password',
        data: {'email': email},
      );
      return Map<String, dynamic>.from(response.data);
    } on DioException catch (e) {
      if (e.response != null)
        return Map<String, dynamic>.from(e.response!.data);
      throw _handleError(e);
    }
  }

  Future<Map<String, dynamic>> verifyOtp(String email, String otp) async {
    try {
      final response = await _dio.post(
        '/verify-otp',
        data: {'email': email, 'otp': otp},
      );
      return Map<String, dynamic>.from(response.data);
    } on DioException catch (e) {
      if (e.response != null)
        return Map<String, dynamic>.from(e.response!.data);
      throw _handleError(e);
    }
  }

  Future<Map<String, dynamic>> resetPassword(
    String email,
    String otp,
    String password,
    String passwordConfirmation,
  ) async {
    try {
      final response = await _dio.post('/reset-password', data: {
        'email': email,
        'otp': otp,
        'password': password,
        'password_confirmation': passwordConfirmation,
      });
      return Map<String, dynamic>.from(response.data);
    } on DioException catch (e) {
      if (e.response != null)
        return Map<String, dynamic>.from(e.response!.data);
      throw _handleError(e);
    }
  }

  Future<void> logout() async {
    try {
      await _addAuthHeader();
      await _dio.post('/logout');
    } on DioException catch (e) {
      if (kDebugMode) print('Logout error: ${e.message}');
    }
  }

  // ══════════════════════════════════════════════════════════
  // PROFILE
  // ══════════════════════════════════════════════════════════

  Future<Map<String, dynamic>> getProfile() async {
    try {
      await _addAuthHeader();
      final response = await _dio.get('/profile');
      return Map<String, dynamic>.from(response.data);
    } on DioException catch (e) {
      if (e.response != null)
        return Map<String, dynamic>.from(e.response!.data);
      throw _handleError(e);
    }
  }

  Future<Map<String, dynamic>> updateProfile({
    String? name,
    String? email,
    String? phone,
    String? address,
  }) async {
    try {
      await _addAuthHeader();
      final response = await _dio.put('/profile', data: {
        if (name != null) 'name': name,
        if (email != null) 'email': email,
        if (phone != null) 'phone': phone,
        if (address != null) 'address': address,
      });
      return Map<String, dynamic>.from(response.data);
    } on DioException catch (e) {
      if (e.response != null)
        return Map<String, dynamic>.from(e.response!.data);
      throw _handleError(e);
    }
  }

  Future<Map<String, dynamic>> changePassword({
    required String oldPassword,
    required String newPassword,
  }) async {
    try {
      await _addAuthHeader();
      final response = await _dio.post('/change-password', data: {
        'old_password': oldPassword,
        'password': newPassword,
        'password_confirmation': newPassword,
      });
      return Map<String, dynamic>.from(response.data);
    } on DioException catch (e) {
      if (e.response != null)
        return Map<String, dynamic>.from(e.response!.data);
      throw _handleError(e);
    }
  }

  // ══════════════════════════════════════════════════════════
  // COMMODITIES
  // ══════════════════════════════════════════════════════════

  Future<Map<String, dynamic>> getCommodities() async {
    try {
      final response = await _dio.get('/commodities');
      final data = Map<String, dynamic>.from(response.data);

      if (kDebugMode && data['data'] != null) {
        final list = data['data'] as List;
        debugPrint('=== TOTAL COMMODITIES: ${list.length}');
        list.take(3).forEach((item) {
          debugPrint(
              '=== item: name=${item['name']}, category=${item['category']}');
        });
      }

      return data;
    } on DioException catch (e) {
      if (e.response != null)
        return Map<String, dynamic>.from(e.response!.data);
      throw _handleError(e);
    }
  }

  Future<Map<String, dynamic>> getCommodityDetail(String commodityId) async {
    try {
      final response = await _dio.get('/commodities/$commodityId');
      return Map<String, dynamic>.from(response.data);
    } on DioException catch (e) {
      if (e.response != null)
        return Map<String, dynamic>.from(e.response!.data);
      throw _handleError(e);
    }
  }

  // ══════════════════════════════════════════════════════════
  // PRICE HISTORIES
  // ══════════════════════════════════════════════════════════

  Future<Map<String, dynamic>> getPriceHistory(
    String commodityId,
    String period,
  ) async {
    try {
      final now = DateTime.now();
      final endDate = now.toIso8601String().substring(0, 10);
      late String startDate;

      switch (period) {
        case '7days':
          startDate = now
              .subtract(const Duration(days: 7))
              .toIso8601String()
              .substring(0, 10);
          break;
        case '30days':
          startDate = now
              .subtract(const Duration(days: 30))
              .toIso8601String()
              .substring(0, 10);
          break;
        case '3months':
          startDate = now
              .subtract(const Duration(days: 90))
              .toIso8601String()
              .substring(0, 10);
          break;
        default:
          startDate = now
              .subtract(const Duration(days: 7))
              .toIso8601String()
              .substring(0, 10);
      }

      final response = await _dio.get(
        '/price-histories',
        queryParameters: {
          'commodity_id': commodityId,
          'start_date': startDate,
          'end_date': endDate,
          'per_page': '200',
        },
      );
      return Map<String, dynamic>.from(response.data);
    } on DioException catch (e) {
      if (e.response != null)
        return Map<String, dynamic>.from(e.response!.data);
      throw _handleError(e);
    }
  }

  /// GET /api/prices/latest
  /// Harga terbaru semua komoditas.
  /// [category] opsional untuk filter kategori.
  /// [search] opsional untuk filter nama komoditas.
  Future<Map<String, dynamic>> getLatestPrices({
    String? category,
    String? search,
  }) async {
    try {
      final params = <String, String>{};
      if (category != null && category != 'Semua')
        params['category'] = category;
      if (search != null && search.isNotEmpty) params['search'] = search;

      final response = await _dio.get(
        '/prices/latest',
        queryParameters: params.isNotEmpty ? params : null,
      );
      return Map<String, dynamic>.from(response.data);
    } on DioException catch (e) {
      if (e.response != null)
        return Map<String, dynamic>.from(e.response!.data);
      throw _handleError(e);
    }
  }

  /// GET /api/prices/top?limit=3
  /// Top N komoditas dengan harga tertinggi.
  Future<Map<String, dynamic>> getTopPrices({int limit = 3}) async {
    try {
      final response = await _dio.get(
        '/prices/top',
        queryParameters: {'limit': limit.toString()},
      );
      return Map<String, dynamic>.from(response.data);
    } on DioException catch (e) {
      if (e.response != null)
        return Map<String, dynamic>.from(e.response!.data);
      throw _handleError(e);
    }
  }

  /// GET /api/prices/categories
  /// Daftar kategori unik dari seluruh komoditas.
  Future<Map<String, dynamic>> getPriceCategories() async {
    try {
      final response = await _dio.get('/prices/categories');
      return Map<String, dynamic>.from(response.data);
    } on DioException catch (e) {
      if (e.response != null)
        return Map<String, dynamic>.from(e.response!.data);
      throw _handleError(e);
    }
  }

  // ══════════════════════════════════════════════════════════
  // PREDICTIONS
  // ══════════════════════════════════════════════════════════

  Future<Map<String, dynamic>> predictPrice(
    String commodityId,
    double quantity,
  ) async {
    try {
      await _addAuthHeader();
      final response = await _dio.post(
        '/predictions/generate',
        data: {'commodity_id': commodityId, 'quantity': quantity},
      );
      return Map<String, dynamic>.from(response.data);
    } on DioException catch (e) {
      if (e.response != null)
        return Map<String, dynamic>.from(e.response!.data);
      throw _handleError(e);
    }
  }

  // ══════════════════════════════════════════════════════════
  // STATISTICS
  // ══════════════════════════════════════════════════════════

  Future<Map<String, dynamic>> getStatistics() async {
    try {
      final response = await _dio.get('/statistics');
      return Map<String, dynamic>.from(response.data);
    } on DioException catch (e) {
      if (e.response != null)
        return Map<String, dynamic>.from(e.response!.data);
      throw _handleError(e);
    }
  }

  // ══════════════════════════════════════════════════════════
  // HELPER
  // ══════════════════════════════════════════════════════════

  String _handleError(DioException error) {
    if (error.type == DioExceptionType.connectionTimeout ||
        error.type == DioExceptionType.receiveTimeout) {
      return 'Koneksi timeout. Periksa jaringan Anda.';
    }
    if (error.type == DioExceptionType.connectionError) {
      return 'Tidak dapat terhubung ke server.';
    }
    if (error.response != null) {
      return error.response?.data['message'] ?? 'Server error';
    }
    return 'Network error: ${error.message}';
  }
}
