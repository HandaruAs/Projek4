import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_app/services/storage_service.dart';

class ApiService {
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;
  ApiService._internal();

  final Dio _dio = Dio(BaseOptions(
    baseUrl: 'http://192.168.1.103:8000/api',
    connectTimeout: const Duration(seconds: 30),
    receiveTimeout: const Duration(seconds: 30),
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
  ));

  final StorageService _storageService = StorageService();

  Future<void> _addAuthHeader() async {
    String? token = await _storageService.getToken();

    if (token != null && token.isNotEmpty) {
      _dio.options.headers['Authorization'] = 'Bearer $token';
    }
  }

  // LOGIN
  Future<Map<String, dynamic>> login(String email, String password) async {
    try {
      final response = await _dio.post(
        '/login',
        data: {
          'email': email,
          'password': password,
        },
      );

      print(response.data);
      return Map<String, dynamic>.from(response.data);

    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  // REGISTER
  Future<Map<String, dynamic>> register(
    String name,
    String email,
    String password, {
    String role = "user",
  }) async {

    try {

      final endpoint = role == "admin"
          ? '/register/admin'
          : '/register/user';

      final response = await _dio.post(
        endpoint,
        data: {
          'name': name,
          'email': email,
          'password': password,
          'password_confirmation': password,
        },
      );

      return Map<String, dynamic>.from(response.data);

    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  // FORGOT PASSWORD
  Future<Map<String, dynamic>> forgotPassword(String email) async {
    try {
      final response = await _dio.post(
        '/forgot-password',
        data: {
          'email': email,
        },
      );

      return Map<String, dynamic>.from(response.data);

    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  // GET COMMODITIES
  Future<Map<String, dynamic>> getCommodities({String? date}) async {
    try {
      await _addAuthHeader();

      final response = await _dio.get(
        '/commodities',
        queryParameters: {
          'date': date,
        },
      );

      return Map<String, dynamic>.from(response.data);

    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  // COMMODITY DETAIL
  Future<Map<String, dynamic>> getCommodityDetail(String commodityId) async {
    try {
      await _addAuthHeader();

      final response = await _dio.get('/commodities/$commodityId');

      return Map<String, dynamic>.from(response.data);

    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  // PRICE HISTORY
  Future<Map<String, dynamic>> getPriceHistory(String commodityId, String period) async {
    try {
      await _addAuthHeader();

      final response = await _dio.get(
        '/price-history',
        queryParameters: {
          'commodity_id': commodityId,
          'period': period,
        },
      );

      return Map<String, dynamic>.from(response.data);

    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  // PREDICT PRICE
  Future<Map<String, dynamic>> predictPrice(String commodityId, double quantity) async {
    try {
      await _addAuthHeader();

      final response = await _dio.post(
        '/predict-price',
        data: {
          'commodity_id': commodityId,
          'quantity': quantity,
        },
      );

      return Map<String, dynamic>.from(response.data);

    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  // LOGOUT
  Future<void> logout() async {
    try {
      await _addAuthHeader();
      await _dio.post('/logout');
    } on DioException catch (e) {
      if (kDebugMode) {
        print('Logout error: ${e.message}');
      }
    }
  }

  String _handleError(DioException error) {
    if (error.response != null) {
      return error.response?.data['message'] ?? 'Server error';
    } else {
      return 'Network error: ${error.message}';
    }
  }
}