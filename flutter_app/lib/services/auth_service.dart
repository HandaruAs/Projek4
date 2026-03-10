import 'package:flutter_app/models/user_model.dart';
import 'package:flutter_app/services/api_service.dart';
import 'package:flutter_app/services/storage_service.dart';

class AuthService {
  final ApiService _apiService = ApiService();
  final StorageService _storageService = StorageService();

  Future<UserModel?> login(String email, String password) async {
    try {
      final response = await _apiService.login(email, password);
      
      if (response['status'] == 'success' && response['data'] != null) {
        final userData = response['data']['user'];
        final token = response['data']['token'];
        
        final user = UserModel.fromJson(userData);
        
        await _storageService.saveToken(token);
        await _storageService.saveUser(user);
        
        return user;
      }
      return null;
    } catch (e) {
      rethrow;
    }
  }

  Future<UserModel?> register(String name, String email, String password) async {
    try {
      final response = await _apiService.register(name, email, password);
      
      if (response['status'] == 'success' && response['data'] != null) {
        final userData = response['data']['user'];
        final token = response['data']['token'];
        
        final user = UserModel.fromJson(userData);
        
        await _storageService.saveToken(token);
        await _storageService.saveUser(user);
        
        return user;
      }
      return null;
    } catch (e) {
      rethrow;
    }
  }

  Future<bool> forgotPassword(String email) async {
    try {
      final response = await _apiService.forgotPassword(email);
      return response['status'] == 'success';
    } catch (e) {
      rethrow;
    }
  }

  Future<void> logout() async {
    try {
      await _apiService.logout();
    } catch (e) {
      // Log error but continue with local logout
      print('Logout API error: $e');
    } finally {
      // Always clear local storage even if API call fails
      await _storageService.clearAll();
    }
  }

  Future<UserModel?> getCurrentUser() async {
    return await _storageService.getUser();
  }

  Future<bool> isLoggedIn() async {
    final token = await _storageService.getToken();
    return token != null && token.isNotEmpty;
  }
}