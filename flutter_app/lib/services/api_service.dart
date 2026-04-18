import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_app/services/storage_service.dart';

class ApiService {
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;
  ApiService._internal();

  final Dio _dio = Dio(BaseOptions(
    baseUrl: 'http://10.10.180.166:8000/api',
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

  // ── AUTH ────────────────────────────────────────────────

  Future<Map<String, dynamic>> login(String email, String password) async {
    try {
      final response = await _dio.post(
        '/login',
        data: {'email': email, 'password': password},
      );
      return Map<String, dynamic>.from(response.data);
    } on DioException catch (e) {
      if (e.response != null) return Map<String, dynamic>.from(e.response!.data);
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
      if (e.response != null) return Map<String, dynamic>.from(e.response!.data);
      throw _handleError(e);
    }
  }

  Future<Map<String, dynamic>> forgotPassword(String email) async {
    try {
      final response = await _dio.post('/forgot-password', data: {'email': email});
      return Map<String, dynamic>.from(response.data);
    } on DioException catch (e) {
      if (e.response != null) return Map<String, dynamic>.from(e.response!.data);
      throw _handleError(e);
    }
  }

  Future<Map<String, dynamic>> verifyOtp(String email, String otp) async {
    try {
      final response = await _dio.post('/verify-otp', data: {'email': email, 'otp': otp});
      return Map<String, dynamic>.from(response.data);
    } on DioException catch (e) {
      if (e.response != null) return Map<String, dynamic>.from(e.response!.data);
      throw _handleError(e);
    }
  }

  Future<Map<String, dynamic>> resetPassword(
    String email, String otp, String password, String passwordConfirmation,
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
      if (e.response != null) return Map<String, dynamic>.from(e.response!.data);
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

  // ── COMMODITIES ─────────────────────────────────────────

  Future<Map<String, dynamic>> getCommodities() async {
    try {
      final response = await _dio.get('/commodities');
      
      // DEBUG
      final data = Map<String, dynamic>.from(response.data);
      if (data['data'] != null) {
        final list = data['data'] as List;
        print('=== TOTAL FROM API: ${list.length}');
        list.take(3).forEach((item) {
          print('=== item: name=${item['name']}, category=${item['category']}');
        });
      }
      
      return data;
    } on DioException catch (e) {
      if (e.response != null) return Map<String, dynamic>.from(e.response!.data);
      throw _handleError(e);
    }
  }

  Future<Map<String, dynamic>> getCommodityDetail(String commodityId) async {
    try {
      final response = await _dio.get('/commodities/$commodityId');
      return Map<String, dynamic>.from(response.data);
    } on DioException catch (e) {
      if (e.response != null) return Map<String, dynamic>.from(e.response!.data);
      throw _handleError(e);
    }
  }

  // ── PRICE HISTORIES ─────────────────────────────────────

  Future<Map<String, dynamic>> getPriceHistory(
    String commodityId,
    String period,
  ) async {
    try {
      // Konversi period ke start_date & end_date yang dimengerti API Laravel
      final now      = DateTime.now();
      final endDate  = now.toIso8601String().substring(0, 10);
      late String startDate;

      switch (period) {
        case '7days':
          startDate = now.subtract(const Duration(days: 7)).toIso8601String().substring(0, 10);
          break;
        case '30days':
          startDate = now.subtract(const Duration(days: 30)).toIso8601String().substring(0, 10);
          break;
        case '3months':
          startDate = now.subtract(const Duration(days: 90)).toIso8601String().substring(0, 10);
          break;
        default:
          startDate = now.subtract(const Duration(days: 7)).toIso8601String().substring(0, 10);
      }

      final response = await _dio.get(
        '/price-histories',
        queryParameters: {
          'commodity_id': commodityId,
          'start_date':   startDate,
          'end_date':     endDate,
          'per_page':     '200',
        },
      );
      return Map<String, dynamic>.from(response.data);
    } on DioException catch (e) {
      if (e.response != null) return Map<String, dynamic>.from(e.response!.data);
      throw _handleError(e);
    }
  }

  // ── PREDICTIONS ──────────────────────────────────────────

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
      if (e.response != null) return Map<String, dynamic>.from(e.response!.data);
      throw _handleError(e);
    }
  }

  Future<Map<String, dynamic>> getProfile() async {
  try {
    await _addAuthHeader();
    final response = await _dio.get('/profile');
    return Map<String, dynamic>.from(response.data);
  } on DioException catch (e) {
    if (e.response != null) return Map<String, dynamic>.from(e.response!.data);
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
      if (name    != null) 'name':    name,
      if (email   != null) 'email':   email,
      if (phone   != null) 'phone':   phone,
      if (address != null) 'address': address,
    });
    return Map<String, dynamic>.from(response.data);
  } on DioException catch (e) {
    if (e.response != null) return Map<String, dynamic>.from(e.response!.data);
    throw _handleError(e);
  }
}

Future<Map<String, dynamic>> getStatistics() async {
  try {
    final response = await _dio.get('/statistics');
    return Map<String, dynamic>.from(response.data);
  } on DioException catch (e) {
    if (e.response != null) return Map<String, dynamic>.from(e.response!.data);
    throw _handleError(e);
  }
}

  // ── HELPER ───────────────────────────────────────────────

  String _handleError(DioException error) {
    if (error.response != null) {
      return error.response?.data['message'] ?? 'Server error';
    }
    return 'Network error: ${error.message}';
  }
}