import 'package:flutter_app/models/user_model.dart';
import 'package:flutter_app/services/api_service.dart';
import 'package:flutter_app/services/storage_service.dart';

class AuthService {

  final ApiService _apiService = ApiService();
  final StorageService _storageService = StorageService();

  // LOGIN
  Future<UserModel?> login(String email, String password) async {

    final response = await _apiService.login(email, password);

    if (response['status'] == 'success') {

      final userData = response['data']['user'];
      final token = response['data']['token'];

      final user = UserModel.fromJson({
        ...userData,
        'token': token
      });

      await _storageService.saveToken(token);
      await _storageService.saveUser(user);

      return user;
    }

    return null;
  }

  // REGISTER
  Future<UserModel?> register(
    String name,
    String email,
    String password, {
    String role = "user",
  }) async {

    final response = await _apiService.register(
      name,
      email,
      password,
      role: role,
    );

    if (response['status'] == 'success') {

      final userData = response['data']['user'];
      final token = response['data']['token'];

      final user = UserModel.fromJson({
        ...userData,
        'token': token
      });

      await _storageService.saveToken(token);
      await _storageService.saveUser(user);

      return user;
    }

    return null;
  }

  // FORGOT PASSWORD
  Future<bool> forgotPassword(String email) async {

    final response = await _apiService.forgotPassword(email);

    if (response['status'] == 'success') {
      return true;
    }

    return false;
  }

  // LOGOUT
  Future<void> logout() async {

    try {
      await _apiService.logout();
    } catch (_) {}

    await _storageService.clearAll();
  }

  Future<UserModel?> getCurrentUser() async {
    return await _storageService.getUser();
  }

  Future<bool> isLoggedIn() async {

    final token = await _storageService.getToken();

    if (token == null || token.isEmpty) {
      return false;
    }

    return true;
  }
}