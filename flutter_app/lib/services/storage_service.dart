import 'dart:convert';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:flutter_app/models/user_model.dart';
import 'package:shared_preferences/shared_preferences.dart';

class StorageService {
  final FlutterSecureStorage _secureStorage = const FlutterSecureStorage();
  static const String _tokenKey = 'auth_token';
  static const String _userKey = 'user_data';

  // Token management
  Future<void> saveToken(String token) async {
    await _secureStorage.write(key: _tokenKey, value: token);
  }

  Future<String?> getToken() async {
    return await _secureStorage.read(key: _tokenKey);
  }

  Future<void> deleteToken() async {
    await _secureStorage.delete(key: _tokenKey);
  }

  // User data management
  Future<void> saveUser(UserModel user) async {
    final userJson = jsonEncode(user.toJson());
    await _secureStorage.write(key: _userKey, value: userJson);
  }

  Future<UserModel?> getUser() async {
    final userJson = await _secureStorage.read(key: _userKey);
    if (userJson != null && userJson.isNotEmpty) {
      final userMap = jsonDecode(userJson);
      return UserModel.fromJson(userMap);
    }
    return null;
  }

  Future<void> deleteUser() async {
    await _secureStorage.delete(key: _userKey);
  }

  // Clear all data
  Future<void> clearAll() async {
    await _secureStorage.deleteAll();
    final prefs = await SharedPreferences.getInstance();
    await prefs.clear();
  }

  // Shared Preferences for app settings
  Future<void> saveLastSelectedMarket(String marketId) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('last_market', marketId);
  }

  Future<String?> getLastSelectedMarket() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('last_market');
  }

  Future<void> saveLastSelectedDate(String date) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('last_date', date);
  }

  Future<String?> getLastSelectedDate() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('last_date');
  }
}