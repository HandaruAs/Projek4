import 'package:flutter/material.dart';
import 'package:flutter_app/models/user_model.dart';
import 'package:flutter_app/services/auth_service.dart';

class AuthProvider extends ChangeNotifier {
  final AuthService _authService = AuthService();

  UserModel? _currentUser;
  bool _isLoading = false;
  String? _errorMessage;

  UserModel? get currentUser => _currentUser;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  bool get isAuthenticated => _currentUser != null;

  // ─────────────────────────────────────────────
  // LOGIN
  // ─────────────────────────────────────────────
  Future<bool> login(String email, String password) async {
    _setLoading(true);
    _clearError();

    try {
      final user = await _authService.login(email, password);
      if (user != null) {
        _currentUser = user;
        _setLoading(false);
        notifyListeners();
        return true;
      } else {
        _errorMessage = 'Login gagal. Periksa email dan password Anda.';
        _setLoading(false);
        return false;
      }
    } catch (e) {
      _errorMessage = e.toString();
      _setLoading(false);
      return false;
    }
  }

  // ─────────────────────────────────────────────
  // REGISTER
  // ─────────────────────────────────────────────
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
        // ✅ Pesan error dari server langsung tampil di snackbar
        // (misal: "The email has already been taken.")
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

  // ─────────────────────────────────────────────
  // FORGOT PASSWORD — Langkah 1: Kirim OTP
  // ─────────────────────────────────────────────
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

  // ─────────────────────────────────────────────
  // VERIFY OTP — Langkah 2: Verifikasi kode OTP
  // ─────────────────────────────────────────────
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

  // ─────────────────────────────────────────────
  // RESET PASSWORD — Langkah 3: Set password baru
  // ─────────────────────────────────────────────
  Future<bool> resetPassword(
    String email,
    String otp,
    String password,
    String passwordConfirmation,
  ) async {
    _setLoading(true);
    _clearError();

    try {
      final success = await _authService.resetPassword(
        email,
        otp,
        password,
        passwordConfirmation,
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
  // LOGOUT
  // ─────────────────────────────────────────────
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

  // ─────────────────────────────────────────────
  // CHECK AUTH STATUS
  // ─────────────────────────────────────────────
  Future<void> checkAuthStatus() async {
    _setLoading(true);
    try {
      final isLoggedIn = await _authService.isLoggedIn();
      if (isLoggedIn) {
        _currentUser = await _authService.getCurrentUser();
      }
    } catch (e) {
      _errorMessage = e.toString();
    } finally {
      _setLoading(false);
      notifyListeners();
    }
  }

  // ─────────────────────────────────────────────
  // Helpers
  // ─────────────────────────────────────────────
  void _setLoading(bool value) {
    _isLoading = value;
    notifyListeners();
  }

  void _clearError() {
    _errorMessage = null;
  }
}