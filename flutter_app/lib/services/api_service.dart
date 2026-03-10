import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_app/services/mock_data.dart';
import 'package:flutter_app/services/storage_service.dart';

class ApiService {
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;
  ApiService._internal();

  // Memanggil data tiruan tanpa backend.
  final bool useMockData = true;

  final Dio _dio = Dio(BaseOptions(
    baseUrl: 'https://your-laravel-backend.com/api', // ganti ama link laravelnya nanti 
    connectTimeout: const Duration(seconds: 30),
    receiveTimeout: const Duration(seconds: 30),
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
  ));

  final StorageService _storageService = StorageService();

  Future<void> _addAuthHeader() async {
    if (useMockData) return; // Skip for mock data
    
    String? token = await _storageService.getToken();
    if (token != null && token.isNotEmpty) {
      _dio.options.headers['Authorization'] = 'Bearer $token';
    }
  }

  // Authentication APIs
  Future<Map<String, dynamic>> login(String email, String password) async {
    if (useMockData) {
      await Future.delayed(const Duration(seconds: 1)); // Simulate network delay
      return {
        'status': 'success',
        'data': {
          'user': MockData.mockUser.toJson(),
          'token': 'mock_token_123',
        }
      };
    }
    
    try {
      final response = await _dio.post('/login', data: {
        'email': email,
        'password': password,
      });
      
      if (response.statusCode == 200) {
        return response.data;
      } else {
        throw Exception('Login failed');
      }
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  Future<Map<String, dynamic>> register(String name, String email, String password) async {
    if (useMockData) {
      await Future.delayed(const Duration(seconds: 1));
      return {
        'status': 'success',
        'data': {
          'user': MockData.mockUser.toJson(),
          'token': 'mock_token_123',
        }
      };
    }
    
    try {
      final response = await _dio.post('/register', data: {
        'name': name,
        'email': email,
        'password': password,
        'password_confirmation': password,
      });
      
      return response.data;
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  Future<Map<String, dynamic>> forgotPassword(String email) async {
    if (useMockData) {
      await Future.delayed(const Duration(seconds: 1));
      return {
        'status': 'success',
        'message': 'Reset link sent to email'
      };
    }
    
    try {
      final response = await _dio.post('/forgot-password', data: {
        'email': email,
      });
      
      return response.data;
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  // Commodity APIs
  Future<Map<String, dynamic>> getCommodities({String? marketId, String? date}) async {
    if (useMockData) {
      await Future.delayed(const Duration(milliseconds: 500));
      return {
        'status': 'success',
        'data': {
          'commodities': MockData.mockCommodities.map((c) => c.toJson()).toList(),
        }
      };
    }
    
    try {
      await _addAuthHeader();
      
      final Map<String, dynamic> queryParams = {};
      if (marketId != null && marketId.isNotEmpty) queryParams['market_id'] = marketId;
      if (date != null && date.isNotEmpty) queryParams['date'] = date;
      
      final response = await _dio.get('/commodities', queryParameters: queryParams);
      return response.data;
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  Future<Map<String, dynamic>> getCommodityDetail(String commodityId) async {
    if (useMockData) {
      await Future.delayed(const Duration(milliseconds: 500));
      final commodity = MockData.mockCommodities.firstWhere(
        (c) => c.id == commodityId,
        orElse: () => MockData.mockCommodities.first,
      );
      return {
        'status': 'success',
        'data': {
          'commodity': commodity.toJson(),
        }
      };
    }
    
    try {
      await _addAuthHeader();
      final response = await _dio.get('/commodities/$commodityId');
      return response.data;
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  // Market APIs
  Future<Map<String, dynamic>> getMarkets({String? search}) async {
    if (useMockData) {
      await Future.delayed(const Duration(milliseconds: 500));
      List<Map<String, dynamic>> markets = MockData.mockMarkets.map((m) => m.toJson()).toList();
      
      if (search != null && search.isNotEmpty) {
        markets = markets.where((m) {
          return m['name'].toLowerCase().contains(search.toLowerCase()) ||
                 m['district'].toLowerCase().contains(search.toLowerCase());
        }).toList();
      }
      
      return {
        'status': 'success',
        'data': {
          'markets': markets,
        }
      };
    }
    
    try {
      await _addAuthHeader();
      
      final Map<String, dynamic> queryParams = {};
      if (search != null && search.isNotEmpty) queryParams['search'] = search;
      
      final response = await _dio.get('/markets', queryParameters: queryParams);
      return response.data;
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  Future<Map<String, dynamic>> getMarketDetail(String marketId) async {
    if (useMockData) {
      await Future.delayed(const Duration(milliseconds: 500));
      final market = MockData.mockMarkets.firstWhere(
        (m) => m.id == marketId,
        orElse: () => MockData.mockMarkets.first,
      );
      return {
        'status': 'success',
        'data': {
          'market': market.toJson(),
          'commodities': MockData.mockCommodities.take(5).map((c) => c.toJson()).toList(),
        }
      };
    }
    
    try {
      await _addAuthHeader();
      final response = await _dio.get('/markets/$marketId');
      return response.data;
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  // Price History APIs
  Future<Map<String, dynamic>> getPriceHistory(String commodityId, String period) async {
    if (useMockData) {
      await Future.delayed(const Duration(milliseconds: 500));
      final history = MockData.getMockPriceHistory(commodityId, period);
      return {
        'status': 'success',
        'data': {
          'history': history.map((h) => h.toJson()).toList(),
        }
      };
    }
    
    try {
      await _addAuthHeader();
      final response = await _dio.get('/price-history', queryParameters: {
        'commodity_id': commodityId,
        'period': period,
      });
      return response.data;
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  // Prediction API
  Future<Map<String, dynamic>> predictPrice(String commodityId, double quantity) async {
    if (useMockData) {
      await Future.delayed(const Duration(seconds: 1));
      return {
        'status': 'success',
        'data': MockData.getMockPredictionResult(commodityId, quantity),
      };
    }
    
    try {
      await _addAuthHeader();
      final response = await _dio.post('/predict-price', data: {
        'commodity_id': commodityId,
        'quantity': quantity,
      });
      return response.data;
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  // Logout
  Future<void> logout() async {
    if (useMockData) {
      return;
    }
    
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
      if (error.response?.data != null && error.response?.data['message'] != null) {
        return error.response?.data['message'];
      }
      return 'Server error: ${error.response?.statusCode}';
    } else if (error.type == DioExceptionType.connectionTimeout) {
      return 'Connection timeout';
    } else if (error.type == DioExceptionType.receiveTimeout) {
      return 'Receive timeout';
    } else if (error.type == DioExceptionType.cancel) {
      return 'Request cancelled';
    } else {
      return 'Network error: ${error.message}';
    }
  }
}