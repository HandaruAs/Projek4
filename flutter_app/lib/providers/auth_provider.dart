import 'package:flutter/material.dart';
import 'package:flutter_app/models/user_model.dart';
import 'package:flutter_app/services/api_service.dart';
import 'package:flutter_app/services/auth_service.dart';
import 'package:flutter_app/services/storage_service.dart';

class AuthProvider extends ChangeNotifier {
  final AuthService    _authService    = AuthService();
  final StorageService _storageService = StorageService();
  final ApiService     _apiService     = ApiService(); // ← tambah

  UserModel? _currentUser;
  bool       _isLoading = false;
  String?    _errorMessage;

  UserModel? get currentUser    => _currentUser;
  bool       get isLoading      => _isLoading;
  String?    get errorMessage   => _errorMessage;
  bool       get isAuthenticated => _currentUser != null;

  Future<bool> login(String email, String password) async {
    _setLoading(true);
    _clearError();
    try {
      final user = await _authService.login(email, password);
      _currentUser = user;
      return true;
    } catch (e) {
      _errorMessage = e.toString().replaceAll("Exception: ", "");
      return false;
    } finally {
      _setLoading(false);
      notifyListeners();
    }
  }

  Future<bool> register(String name, String email, String password) async {
    _setLoading(true);
    _clearError();
    try {
      final result = await _authService.register(name, email, password);
      if (result['success'] == true) {
        _currentUser = result['user'] as UserModel;
        _setLoading(false);
        notifyListeners();
        return true;
      } else {
        _errorMessage = result['message'] ?? 'Registrasi gagal. Silakan coba lagi.';
        _setLoading(false);
        return false;
      }
    } catch (e) {
      _errorMessage = e.toString();
      _setLoading(false);
      return false;
    }
  }

  Future<bool> forgotPassword(String email) async {
    _setLoading(true);
    _clearError();
    try {
      final result = await _authService.forgotPassword(email);
      if (!result['success']) {
        _errorMessage = result['message'] ?? 'Gagal mengirim kode OTP. Coba lagi.';
      }
      _setLoading(false);
      return result['success'];
    } catch (e) {
      _errorMessage = e.toString();
      _setLoading(false);
      return false;
    }
  }

  Future<bool> verifyOtp(String email, String otp) async {
    _setLoading(true);
    _clearError();
    try {
      final success = await _authService.verifyOtp(email, otp);
      if (!success) _errorMessage = 'Kode OTP tidak valid atau sudah kedaluwarsa.';
      _setLoading(false);
      return success;
    } catch (e) {
      _errorMessage = e.toString();
      _setLoading(false);
      return false;
    }
  }

  Future<bool> resetPassword(
    String email, String otp, String password, String passwordConfirmation,
  ) async {
    _setLoading(true);
    _clearError();
    try {
      final success = await _authService.resetPassword(
        email, otp, password, passwordConfirmation,
      );
      if (!success) _errorMessage = 'Gagal mereset password. Silakan ulangi dari awal.';
      _setLoading(false);
      return success;
    } catch (e) {
      _errorMessage = e.toString();
      _setLoading(false);
      return false;
    }
  }

  // ─────────────────────────────────────────────
  // UPDATE CURRENT USER — dipanggil setelah update profile
  // ─────────────────────────────────────────────
  Future<void> updateCurrentUser(UserModel user) async {
    _currentUser = user;
    await _storageService.saveUser(user);
    notifyListeners();
  }

  // ─────────────────────────────────────────────
  // REFRESH PROFILE — fetch ulang dari server (untuk sync foto dari web)
  // ─────────────────────────────────────────────
  Future<void> refreshProfile() async {
  try {
    final response = await _apiService.getProfile();
    print('=== refreshProfile response: $response'); // ← tambah ini
    if (response['status'] == 'success' && response['data'] != null) {
      final updatedUser = UserModel.fromJson(
        Map<String, dynamic>.from(response['data']),
      );
      print('=== phone: ${updatedUser.phone}');     // ← tambah ini
      print('=== address: ${updatedUser.address}'); // ← tambah ini
      await updateCurrentUser(updatedUser);
    }
  } catch (e) {
    print('=== refreshProfile error: $e'); // ← tambah ini
  }
}

  Future<void> logout() async {
    _setLoading(true);
    try {
      await _authService.logout();
      _currentUser = null;
    } catch (e) {
      _errorMessage = e.toString();
    } finally {
      _setLoading(false);
      notifyListeners();
    }
  }

  Future<void> checkAuthStatus() async {
    _setLoading(true);
    try {
      final isLoggedIn = await _authService.isLoggedIn();
      if (isLoggedIn) {
        // Load dari storage dulu (cepat, untuk UI awal)
        _currentUser = await _authService.getCurrentUser();
        notifyListeners();
        // Lalu fetch dari server (untuk sync avatar terbaru dari web)
        await refreshProfile(); // ← tambah
      }
    } catch (e) {
      _errorMessage = e.toString();
    } finally {
      _setLoading(false);
      notifyListeners();
    }
  }

  void _setLoading(bool value) {
    _isLoading = value;
    notifyListeners();
  }

  void _clearError() {
    _errorMessage = null;
  }
}