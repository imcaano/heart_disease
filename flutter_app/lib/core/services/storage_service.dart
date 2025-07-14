import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/user.dart';

class StorageService {
  static SharedPreferences? _prefs;

  // Keys for storage
  static const String _userKey = 'user';
  static const String _tokenKey = 'token';
  static const String _isLoggedInKey = 'is_logged_in';
  static const String _walletAddressKey = 'wallet_address';
  static const String _themeKey = 'theme_mode';
  static const String _languageKey = 'language';

  static Future<void> init() async {
    _prefs = await SharedPreferences.getInstance();
  }

  // User management
  static Future<void> saveUser(User user) async {
    if (_prefs != null) {
      await _prefs!.setString(_userKey, jsonEncode(user.toJson()));
      await _prefs!.setBool(_isLoggedInKey, true);
    }
  }

  static User? getUser() {
    if (_prefs != null) {
      final userJson = _prefs!.getString(_userKey);
      if (userJson != null) {
        try {
          return User.fromJson(jsonDecode(userJson));
        } catch (e) {
          print('Error parsing user: $e');
        }
      }
    }
    return null;
  }

  static Future<void> clearUser() async {
    if (_prefs != null) {
      await _prefs!.remove(_userKey);
      await _prefs!.setBool(_isLoggedInKey, false);
    }
  }

  static bool get isLoggedIn {
    return _prefs?.getBool(_isLoggedInKey) ?? false;
  }

  // Token management
  static Future<void> saveToken(String token) async {
    if (_prefs != null) {
      await _prefs!.setString(_tokenKey, token);
    }
  }

  static String? getToken() {
    return _prefs?.getString(_tokenKey);
  }

  static Future<void> clearToken() async {
    if (_prefs != null) {
      await _prefs!.remove(_tokenKey);
    }
  }

  // Wallet management
  static Future<void> saveWalletAddress(String address) async {
    if (_prefs != null) {
      await _prefs!.setString(_walletAddressKey, address);
    }
  }

  static String? getWalletAddress() {
    return _prefs?.getString(_walletAddressKey);
  }

  static Future<void> clearWalletAddress() async {
    if (_prefs != null) {
      await _prefs!.remove(_walletAddressKey);
    }
  }

  // Theme management
  static Future<void> saveThemeMode(String themeMode) async {
    if (_prefs != null) {
      await _prefs!.setString(_themeKey, themeMode);
    }
  }

  static String getThemeMode() {
    return _prefs?.getString(_themeKey) ?? 'system';
  }

  // Language management
  static Future<void> saveLanguage(String language) async {
    if (_prefs != null) {
      await _prefs!.setString(_languageKey, language);
    }
  }

  static String getLanguage() {
    return _prefs?.getString(_languageKey) ?? 'en';
  }

  // Generic storage methods
  static Future<void> saveString(String key, String value) async {
    if (_prefs != null) {
      await _prefs!.setString(key, value);
    }
  }

  static String? getString(String key) {
    return _prefs?.getString(key);
  }

  static Future<void> saveBool(String key, bool value) async {
    if (_prefs != null) {
      await _prefs!.setBool(key, value);
    }
  }

  static bool getBool(String key, {bool defaultValue = false}) {
    return _prefs?.getBool(key) ?? defaultValue;
  }

  static Future<void> saveInt(String key, int value) async {
    if (_prefs != null) {
      await _prefs!.setInt(key, value);
    }
  }

  static int getInt(String key, {int defaultValue = 0}) {
    return _prefs?.getInt(key) ?? defaultValue;
  }

  static Future<void> saveDouble(String key, double value) async {
    if (_prefs != null) {
      await _prefs!.setDouble(key, value);
    }
  }

  static double getDouble(String key, {double defaultValue = 0.0}) {
    return _prefs?.getDouble(key) ?? defaultValue;
  }

  static Future<void> saveList(String key, List<String> value) async {
    if (_prefs != null) {
      await _prefs!.setStringList(key, value);
    }
  }

  static List<String> getList(String key) {
    return _prefs?.getStringList(key) ?? [];
  }

  // Clear all data
  static Future<void> clearAll() async {
    if (_prefs != null) {
      await _prefs!.clear();
    }
  }

  // Remove specific key
  static Future<void> remove(String key) async {
    if (_prefs != null) {
      await _prefs!.remove(key);
    }
  }

  // Check if key exists
  static bool containsKey(String key) {
    return _prefs?.containsKey(key) ?? false;
  }
}
