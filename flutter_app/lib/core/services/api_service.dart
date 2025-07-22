import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../models/user.dart';
import '../models/prediction.dart';
import '../models/api_response.dart';
import 'dart:math';

class ApiService {
  // Update this to match your local XAMPP setup
  static const String baseUrl = 'http://192.168.18.12/heart_disease/api';

  final http.Client _client = http.Client();

  // Headers for API requests
  Map<String, String> get _headers => {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      };

  // Add auth token to headers if available
  Map<String, String> _getAuthHeaders(String? token) {
    final headers = Map<String, String>.from(_headers);
    if (token != null) {
      headers['Authorization'] = 'Bearer $token';
    }
    return headers;
  }

  // Helper to generate a random wallet address
  String generateRandomWalletAddress() {
    final rand = Random.secure();
    final bytes = List<int>.generate(20, (_) => rand.nextInt(256));
    return '0x' + bytes.map((b) => b.toRadixString(16).padLeft(2, '0')).join();
  }

  // Login user
  Future<ApiResponse<User>> login(
    String username,
    String password,
  ) async {
    try {
      final jsonData = jsonEncode({
        'username': username,
        'password': password,
      });

      final response = await _client.post(
        Uri.parse('$baseUrl/login.php'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonData,
      );

      print('Login response status: ${response.statusCode}');
      print('Login response body: ${response.body}');

      final data = jsonDecode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        // Store token for API authentication
        if (data['token'] != null) {
          final prefs = await SharedPreferences.getInstance();
          await prefs.setString('auth_token', data['token']);
        }

        // Create user from response data
        final user = User.fromJson(data['user']);
        return ApiResponse.success(user);
      } else {
        return ApiResponse.error(
            data['message'] ?? data['error'] ?? 'Login failed');
      }
    } catch (e) {
      print('Login error: $e');
      return ApiResponse.error('Network error: $e');
    }
  }

  Future<ApiResponse<User>> signup(
    String username,
    String email,
    String password,
  ) async {
    try {
      final walletAddress = generateRandomWalletAddress();
      final jsonData = jsonEncode({
        'username': username,
        'email': email,
        'password': password,
        'wallet_address': walletAddress,
      });

      final response = await _client.post(
        Uri.parse('$baseUrl/signup.php'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonData,
      );

      final data = jsonDecode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        // The backend may not return a user object, so create a minimal User
        final user = data['user'] != null
            ? User.fromJson(data['user'])
            : User(
                id: 0,
                username: username,
                email: email,
                walletAddress: walletAddress,
                role: 'user',
                createdAt: DateTime.now(),
                totalPredictions: 0,
                predictionAccuracy: 0.0,
                reputationScore: 0,
                lastLogin: null,
                status: 'active',
              );
        return ApiResponse.success(user);
      } else {
        return ApiResponse.error(
            data['message'] ?? data['error'] ?? 'Signup failed');
      }
    } catch (e) {
      return ApiResponse.error('Network error: $e');
    }
  }

  // Make prediction
  Future<ApiResponse<Prediction>> makePrediction(
      Map<String, dynamic> predictionData) async {
    try {
      print('Making prediction request with data: $predictionData');

      final response = await _client.post(
        Uri.parse('http://192.168.18.12:5000/predict'), // Direct Python API
        headers: _headers,
        body: jsonEncode(predictionData),
      );

      print('Prediction response status:  [32m${response.statusCode} [0m');
      print('Prediction response body: ${response.body}');

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['prediction'] != null) {
          // Merge input and result for full Prediction object
          final prediction = Prediction.fromJson({...predictionData, ...data});
          return ApiResponse.success(prediction);
        } else {
          return ApiResponse.error(data['message'] ?? 'Prediction failed');
        }
      } else if (response.statusCode == 404) {
        return ApiResponse.error(
            'API endpoint not found. Please check if the Python server is running.');
      } else if (response.statusCode == 500) {
        return ApiResponse.error(
            'Server error. Please check the Python server logs.');
      } else {
        return ApiResponse.error(
            'HTTP ${response.statusCode}: ${response.reasonPhrase}');
      }
    } catch (e) {
      print('Prediction error: $e');
      if (e.toString().contains('SocketException')) {
        return ApiResponse.error(
            'Network error: Unable to connect to Python server.');
      } else if (e.toString().contains('FormatException')) {
        return ApiResponse.error('Invalid response format from Python server.');
      } else {
        return ApiResponse.error('Network error: $e');
      }
    }
  }

  // Get consultation
  Future<ApiResponse<String>> getConsultation(
    Map<String, dynamic> predictionData,
    int predictionResult,
  ) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/chatgpt_consultation.php'),
        headers: _headers,
        body: jsonEncode({
          'predictionData': predictionData,
          'predictionResult': predictionResult,
        }),
      );

      final data = jsonDecode(response.body);

      if (data['success'] == true) {
        return ApiResponse.success(data['consultation']);
      } else {
        return ApiResponse.error(
          data['message'] ?? 'Failed to get consultation',
        );
      }
    } catch (e) {
      return ApiResponse.error('Network error: $e');
    }
  }

  // Dashboard stats
  Future<ApiResponse<Map<String, dynamic>>> getDashboardStats(
      [int? userId]) async {
    try {
      String url = '$baseUrl/dashboard_stats.php';
      if (userId != null) {
        url += '?user_id=$userId';
      }
      final response = await http.get(
        Uri.parse(url),
        headers: _headers,
      );

      final data = jsonDecode(response.body);

      if (response.statusCode == 200) {
        return ApiResponse.success(data);
      } else {
        return ApiResponse.error(
          data['error'] ?? 'Failed to get dashboard stats',
        );
      }
    } catch (e) {
      return ApiResponse.error('Network error: $e');
    }
  }

  // Admin stats
  Future<ApiResponse<Map<String, dynamic>>> getAdminStats() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/admin_stats.php'),
        headers: _headers,
      );

      final data = jsonDecode(response.body);

      if (response.statusCode == 200) {
        return ApiResponse.success(data);
      } else {
        return ApiResponse.error(
          data['error'] ?? 'Failed to get admin stats',
        );
      }
    } catch (e) {
      return ApiResponse.error('Network error: $e');
    }
  }

  // Get user predictions
  Future<ApiResponse<List<Prediction>>> getUserPredictions(int userId) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/reports.php?user_id=$userId'),
        headers: _headers,
      );

      final data = jsonDecode(response.body);

      if (data['success'] == true) {
        final predictions = (data['predictions'] as List)
            .map((json) => Prediction.fromJson(json))
            .toList();
        return ApiResponse.success(predictions);
      } else {
        return ApiResponse.error(
          data['message'] ?? 'Failed to get predictions',
        );
      }
    } catch (e) {
      return ApiResponse.error('Network error: $e');
    }
  }

  // Save prediction
  Future<ApiResponse<bool>> savePrediction(Prediction prediction) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/save_prediction.php'),
        headers: _headers,
        body: jsonEncode(prediction.toJson()),
      );

      final data = jsonDecode(response.body);

      if (data['success'] == true) {
        return ApiResponse.success(true);
      } else {
        return ApiResponse.error(
          data['message'] ?? 'Failed to save prediction',
        );
      }
    } catch (e) {
      return ApiResponse.error('Network error: $e');
    }
  }

  // Get all users (admin)
  Future<ApiResponse<List<Map<String, dynamic>>>> getUsers() async {
    try {
      final response = await _client.get(
        Uri.parse('$baseUrl/get_users.php'),
        headers: _headers,
      );
      final data = jsonDecode(response.body);
      if (data['success'] == true) {
        return ApiResponse.success(
            List<Map<String, dynamic>>.from(data['users']));
      } else {
        return ApiResponse.error(data['message'] ?? 'Failed to get users');
      }
    } catch (e) {
      return ApiResponse.error('Network error: $e');
    }
  }

  Future<ApiResponse<bool>> updateUser(User user) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/update_user.php'),
        headers: _headers,
        body: jsonEncode(user.toJson()),
      );

      final data = jsonDecode(response.body);

      if (data['success'] == true) {
        return ApiResponse.success(true);
      } else {
        return ApiResponse.error(data['message'] ?? 'Failed to update user');
      }
    } catch (e) {
      return ApiResponse.error('Network error: $e');
    }
  }

  Future<ApiResponse<bool>> deleteUser(int userId) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/delete_user.php'),
        headers: _headers,
        body: jsonEncode({'user_id': userId}),
      );

      final data = jsonDecode(response.body);

      if (data['success'] == true) {
        return ApiResponse.success(true);
      } else {
        return ApiResponse.error(data['message'] ?? 'Failed to delete user');
      }
    } catch (e) {
      return ApiResponse.error('Network error: $e');
    }
  }

  // Import dataset
  Future<ApiResponse<Map<String, dynamic>>> importDataset(
    String name,
    String description,
    List<Map<String, dynamic>> data,
  ) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/import_dataset.php'),
        headers: _headers,
        body: jsonEncode({
          'name': name,
          'description': description,
          'data': data,
        }),
      );

      final responseData = jsonDecode(response.body);

      if (responseData['success'] == true) {
        return ApiResponse.success(responseData);
      } else {
        return ApiResponse.error(
          responseData['message'] ?? 'Failed to import dataset',
        );
      }
    } catch (e) {
      return ApiResponse.error('Network error: $e');
    }
  }

  // Get dashboard stats for a user
  Future<ApiResponse<Map<String, dynamic>>> getUserDashboardStats(
      int userId) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/dashboard_stats.php?user_id=$userId'),
        headers: _headers,
      );
      final data = jsonDecode(response.body);
      if (data['success'] == true) {
        return ApiResponse.success(data);
      } else {
        return ApiResponse.error(
            data['message'] ?? 'Failed to get dashboard stats');
      }
    } catch (e) {
      return ApiResponse.error('Network error: $e');
    }
  }

  // Get all predictions (admin)
  Future<ApiResponse<List<Map<String, dynamic>>>> getAllPredictions() async {
    try {
      final response = await _client.get(
        Uri.parse('$baseUrl/get_all_predictions.php'),
        headers: _headers,
      );
      final data = jsonDecode(response.body);
      if (data['success'] == true) {
        return ApiResponse.success(
            List<Map<String, dynamic>>.from(data['predictions']));
      } else {
        return ApiResponse.error(
            data['message'] ?? 'Failed to get predictions');
      }
    } catch (e) {
      return ApiResponse.error('Network error: $e');
    }
  }

  // Update password
  Future<ApiResponse<bool>> updatePassword(
    String currentPassword,
    String newPassword,
    String confirmPassword,
  ) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('auth_token');
      final response = await _client.post(
        Uri.parse('$baseUrl/update_password.php'),
        headers: _getAuthHeaders(token),
        body: jsonEncode({
          'current_password': currentPassword,
          'new_password': newPassword,
          'confirm_password': confirmPassword,
        }),
      );

      final data = jsonDecode(response.body);

      if (data['success'] == true) {
        return ApiResponse.success(true);
      } else {
        return ApiResponse.error(
            data['message'] ?? 'Failed to update password');
      }
    } catch (e) {
      return ApiResponse.error('Network error: $e');
    }
  }
}
