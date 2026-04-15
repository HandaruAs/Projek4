import 'package:flutter_app/models/user_model.dart';
import 'package:flutter_app/services/api_service.dart';
import 'package:flutter_app/services/storage_service.dart';

class AuthService {
  final ApiService _apiService = ApiService();
  final StorageService _storageService = StorageService();

  // LOGIN 
  Future<UserModel> login(String email, String password) async {
    final response = await _apiService.login(email, password);

    if (response['status'] == 'success') {
      final userData = response['data']['user'];
      final token = response['data']['token'];

      final user = UserModel.fromJson({...userData, 'token': token});

      await _storageService.saveToken(token);
      await _storageService.saveUser(user);

      return user;
    }

    
    throw Exception(response['message'] ?? 'Login gagal');
  }
  // REGISTER
  Future<Map<String, dynamic>> register(
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

    // ✅ Selalu kembalikan status & message ke provider
    if (response['status'] == 'success') {
      final userData = response['data']['user'];
      final token = response['data']['token'];

       print('=== REGISTER DEBUG ===');
      print('full response: $response');
      print('userData: $userData');
      print('role dari server: ${userData['role']}');
      

      final user = UserModel.fromJson({...userData, 'token': token});
      print('role di UserModel: ${user.role}');

      await _storageService.saveToken(token);
      await _storageService.saveUser(user);

      return {'success': true, 'user': user};
    }

    // ✅ Gagal — teruskan pesan error dari server (misal: "email sudah terdaftar")
    return {
      'success': false,
      'message': response['message'] ?? 'Registrasi gagal. Silakan coba lagi.',
    };
  }

  // FORGOT PASSWORD — Langkah 1: Kirim OTP
  Future<Map<String, dynamic>> forgotPassword(String email) async {
    final response = await _apiService.forgotPassword(email);
    return {
      'success': response['status'] == 'success',
      'message': response['message'],
    };
  }

  // VERIFY OTP — Langkah 2: Verifikasi kode OTP
  Future<bool> verifyOtp(String email, String otp) async {
    final response = await _apiService.verifyOtp(email, otp);
    return response['status'] == 'success';
  }

  // RESET PASSWORD — Langkah 3: Set password baru
  Future<bool> resetPassword(
    String email,
    String otp,
    String password,
    String passwordConfirmation,
  ) async {
    final response = await _apiService.resetPassword(
      email,
      otp,
      password,
      passwordConfirmation,
    );
    return response['status'] == 'success';
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
    if (token == null || token.isEmpty) return false;
    return true;
  }
}