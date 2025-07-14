import 'package:flutter/foundation.dart';
import '../models/user.dart';
import '../services/api_service.dart';
import '../services/storage_service.dart';
import 'package:shared_preferences/shared_preferences.dart';

class AuthProvider extends ChangeNotifier {
  User? _currentUser;
  bool _isLoading = false;
  String? _error;
  final ApiService _apiService = ApiService();

  User? get currentUser => _currentUser;
  bool get isLoading => _isLoading;
  String? get error => _error;
  bool get isLoggedIn => _currentUser != null;
  bool get isAdmin => _currentUser?.isAdmin ?? false;

  AuthProvider() {
    _loadUserFromStorage();
  }

  Future<void> _loadUserFromStorage() async {
    _currentUser = StorageService.getUser();
    notifyListeners();
  }

  Future<bool> login(
    String username,
    String password,
    String walletAddress,
  ) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _apiService.login(
        username,
        password,
        walletAddress,
      );

      if (response.isSuccess && response.data != null) {
        _currentUser = response.data;
        await StorageService.saveUser(_currentUser!);
        await StorageService.saveWalletAddress(walletAddress);

        // Token is already stored by ApiService.login()
        _error = null;
        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _error = response.error ?? 'Login failed';
        _isLoading = false;
        notifyListeners();
        return false;
      }
    } catch (e) {
      _error = 'Network error: $e';
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> signup(
    String username,
    String email,
    String password,
    String walletAddress,
  ) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _apiService.signup(
        username,
        email,
        password,
        walletAddress,
      );

      if (response.isSuccess && response.data != null) {
        _currentUser = response.data;
        await StorageService.saveUser(_currentUser!);
        await StorageService.saveWalletAddress(walletAddress);
        _error = null;
        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _error = response.error ?? 'Signup failed';
        _isLoading = false;
        notifyListeners();
        return false;
      }
    } catch (e) {
      _error = 'Network error: $e';
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<void> logout() async {
    _currentUser = null;
    await StorageService.clearUser();
    await StorageService.clearToken();
    await StorageService.clearWalletAddress();
    _error = null;
    notifyListeners();
  }

  Future<void> updateUser(User updatedUser) async {
    _currentUser = updatedUser;
    await StorageService.saveUser(_currentUser!);
    notifyListeners();
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }

  void setLoading(bool loading) {
    _isLoading = loading;
    notifyListeners();
  }
}
