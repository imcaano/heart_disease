import 'package:flutter/foundation.dart';
import '../models/prediction.dart';
import '../services/api_service.dart';

class PredictionProvider extends ChangeNotifier {
  List<Prediction> _predictions = [];
  Prediction? _currentPrediction;
  bool _isLoading = false;
  String? _error;
  String? _consultation;
  final ApiService _apiService = ApiService();
  Map<String, dynamic>? _dashboardStats;

  List<Prediction> get predictions => _predictions;
  Prediction? get currentPrediction => _currentPrediction;
  bool get isLoading => _isLoading;
  String? get error => _error;
  String? get consultation => _consultation;
  Map<String, dynamic>? get dashboardStats => _dashboardStats;

  // Statistics
  int get totalPredictions => _predictions.length;
  int get highRiskPredictions => _predictions.where((p) => p.isHighRisk).length;
  int get lowRiskPredictions => _predictions.where((p) => p.isLowRisk).length;
  double get averageProbability => _predictions.isEmpty
      ? 0.0
      : _predictions
              .where((p) => p.probability != null)
              .map((p) => p.probability!)
              .reduce((a, b) => a + b) /
          _predictions.where((p) => p.probability != null).length;

  Future<bool> makePrediction(Map<String, dynamic> predictionData,
      {int? userId}) async {
    _isLoading = true;
    _error = null;
    _consultation = null;
    notifyListeners();

    try {
      print('Making prediction with data: $predictionData');
      final response = await _apiService.makePrediction(predictionData);

      if (response.isSuccess && response.data != null) {
        _currentPrediction = response.data;
        if (userId != null) {
          await loadUserPredictions(userId);
        } else {
          _predictions.add(_currentPrediction!);
        }
        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _error = response.error ?? 'Prediction failed';
        _isLoading = false;
        notifyListeners();
        return false;
      }
    } catch (e) {
      print('Prediction error in provider: $e');
      _error = 'Network error: $e';
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<void> _getConsultation(
    Map<String, dynamic> predictionData,
    int predictionResult,
  ) async {
    try {
      final response = await _apiService.getConsultation(
        predictionData,
        predictionResult,
      );
      if (response.isSuccess && response.data != null) {
        _consultation = response.data;
        notifyListeners();
      }
    } catch (e) {
      print('Failed to get consultation: $e');
    }
  }

  Future<bool> loadUserPredictions(int userId) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _apiService.getUserPredictions(userId);

      if (response.isSuccess && response.data != null) {
        _predictions = response.data ?? [];
        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _error = response.error ?? 'Failed to load predictions';
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

  Future<bool> savePrediction(Prediction prediction) async {
    try {
      final response = await _apiService.savePrediction(prediction);
      return response.isSuccess;
    } catch (e) {
      _error = 'Failed to save prediction: $e';
      notifyListeners();
      return false;
    }
  }

  void clearCurrentPrediction() {
    _currentPrediction = null;
    _consultation = null;
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

  // Filter predictions
  List<Prediction> getHighRiskPredictions() {
    return _predictions.where((p) => p.isHighRisk).toList();
  }

  List<Prediction> getLowRiskPredictions() {
    return _predictions.where((p) => p.isLowRisk).toList();
  }

  List<Prediction> getPredictionsByDateRange(DateTime start, DateTime end) {
    return _predictions.where((p) {
      if (p.createdAt == null) return false;
      return p.createdAt!.isAfter(start) && p.createdAt!.isBefore(end);
    }).toList();
  }

  // Get prediction statistics
  Map<String, dynamic> getPredictionStats() {
    if (_predictions.isEmpty) {
      return {
        'total': 0,
        'highRisk': 0,
        'lowRisk': 0,
        'averageProbability': 0.0,
        'highRiskPercentage': 0.0,
        'lowRiskPercentage': 0.0,
      };
    }

    final total = _predictions.length;
    final highRisk = highRiskPredictions;
    final lowRisk = lowRiskPredictions;

    return {
      'total': total,
      'highRisk': highRisk,
      'lowRisk': lowRisk,
      'averageProbability': averageProbability,
      'highRiskPercentage': (highRisk / total) * 100,
      'lowRiskPercentage': (lowRisk / total) * 100,
    };
  }

  Future<void> fetchDashboardStats(int userId) async {
    _isLoading = true;
    notifyListeners();
    try {
      final response = await _apiService.getUserDashboardStats(userId);
      if (response.isSuccess && response.data != null) {
        _dashboardStats = response.data;
      } else {
        _dashboardStats = null;
      }
    } catch (e) {
      _dashboardStats = null;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }
}
