import 'package:flutter/material.dart';
import '../../../../core/services/api_service.dart';
import '../../../../core/theme/app_theme.dart';
import '../../../../core/providers/auth_provider.dart';
import 'package:provider/provider.dart';

class AdminReportsPage extends StatefulWidget {
  const AdminReportsPage({super.key});

  @override
  State<AdminReportsPage> createState() => _AdminReportsPageState();
}

class _AdminReportsPageState extends State<AdminReportsPage> {
  bool _isLoading = false;
  List<Map<String, dynamic>> _predictions = [];
  String _searchQuery = '';
  String _predictionFilter = 'all';

  @override
  void initState() {
    super.initState();
    _loadPredictions();
  }

  Future<void> _loadPredictions() async {
    setState(() => _isLoading = true);
    try {
      final apiService = ApiService();
      final response = await apiService.getAllPredictions();
      if (response.isSuccess && response.data != null) {
        setState(() {
          _predictions = response.data != null
              ? List<Map<String, dynamic>>.from(response.data!)
              : [];
        });
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: ${response.error}')),
        );
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error loading predictions: $e')),
      );
    } finally {
      setState(() => _isLoading = false);
    }
  }

  List<Map<String, dynamic>> get _filteredPredictions {
    return _predictions.where((prediction) {
      final username = prediction['username']?.toString() ?? '';
      final email = prediction['email']?.toString() ?? '';
      final predictionResult = prediction['prediction']?.toString() ?? '';

      final matchesSearch = _searchQuery.isEmpty ||
          username.toLowerCase().contains(_searchQuery.toLowerCase()) ||
          email.toLowerCase().contains(_searchQuery.toLowerCase());

      final matchesPrediction = _predictionFilter == 'all' ||
          (_predictionFilter == 'high' && predictionResult == '1') ||
          (_predictionFilter == 'low' && predictionResult == '0');

      return matchesSearch && matchesPrediction;
    }).toList();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Admin Reports'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadPredictions,
            tooltip: 'Refresh',
          ),
          IconButton(
            icon: const Icon(Icons.logout),
            tooltip: 'Logout',
            onPressed: () async {
              await Provider.of<AuthProvider>(context, listen: false).logout();
              if (mounted) {
                Navigator.of(context)
                    .pushNamedAndRemoveUntil('/login', (route) => false);
              }
            },
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : Column(
              children: [
                // Search and Filter Section
                _buildSearchAndFilter(),

                // Predictions List
                Expanded(
                  child: RefreshIndicator(
                    onRefresh: _loadPredictions,
                    child: _filteredPredictions.isEmpty
                        ? _buildEmptyState()
                        : _buildPredictionsList(),
                  ),
                ),
              ],
            ),
    );
  }

  Widget _buildSearchAndFilter() {
    return Card(
      margin: const EdgeInsets.all(16),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            // Search Bar
            TextField(
              decoration: InputDecoration(
                hintText: 'Search by username or email...',
                prefixIcon: const Icon(Icons.search),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                contentPadding:
                    const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              ),
              onChanged: (value) {
                setState(() {
                  _searchQuery = value;
                });
              },
            ),
            const SizedBox(height: 12),

            // Prediction Filter
            Row(
              children: [
                const Text('Filter by result: '),
                const SizedBox(width: 8),
                Expanded(
                  child: DropdownButtonFormField<String>(
                    value: _predictionFilter,
                    decoration: InputDecoration(
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                      contentPadding: const EdgeInsets.symmetric(
                          horizontal: 12, vertical: 8),
                    ),
                    items: const [
                      DropdownMenuItem(
                          value: 'all', child: Text('All Results')),
                      DropdownMenuItem(value: 'high', child: Text('High Risk')),
                      DropdownMenuItem(value: 'low', child: Text('Low Risk')),
                    ],
                    onChanged: (value) {
                      setState(() {
                        _predictionFilter = value ?? 'all';
                      });
                    },
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(Icons.analytics_outlined,
              size: 64, color: AppTheme.gray400),
          const SizedBox(height: 16),
          Text(
            _searchQuery.isNotEmpty || _predictionFilter != 'all'
                ? 'No predictions found matching your criteria'
                : 'No predictions found',
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                  color: AppTheme.gray600,
                ),
          ),
          const SizedBox(height: 8),
          Text(
            'Try adjusting your search or filters',
            style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                  color: AppTheme.gray500,
                ),
          ),
        ],
      ),
    );
  }

  Widget _buildPredictionsList() {
    return ListView.builder(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      itemCount: _filteredPredictions.length,
      itemBuilder: (context, index) {
        final prediction = _filteredPredictions[index];
        return _buildPredictionCard(prediction);
      },
    );
  }

  Widget _buildPredictionCard(Map<String, dynamic> prediction) {
    final isHighRisk = prediction['prediction'] == 1;
    final riskColor = isHighRisk ? AppTheme.dangerColor : AppTheme.successColor;
    final riskText = isHighRisk ? 'High Risk' : 'Low Risk';
    final confidence = prediction['confidence_score'] ?? 0.0;

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: riskColor.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Icon(
                    isHighRisk ? Icons.warning : Icons.check_circle,
                    color: riskColor,
                    size: 24,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        prediction['username'] ?? 'Unknown User',
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      Text(
                        prediction['email'] ?? 'No email',
                        style: TextStyle(
                          fontSize: 14,
                          color: Colors.grey[600],
                        ),
                      ),
                    ],
                  ),
                ),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: riskColor.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(
                        riskText,
                        style: TextStyle(
                          color: riskColor,
                          fontWeight: FontWeight.w600,
                          fontSize: 12,
                        ),
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      '${(confidence * 100).toStringAsFixed(1)}%',
                      style: TextStyle(
                        color: riskColor,
                        fontWeight: FontWeight.w600,
                        fontSize: 12,
                      ),
                    ),
                  ],
                ),
              ],
            ),

            const SizedBox(height: 16),

            // Medical Data
            Text(
              'Medical Parameters',
              style: Theme.of(context).textTheme.titleSmall?.copyWith(
                    fontWeight: FontWeight.w600,
                  ),
            ),
            const SizedBox(height: 8),

            // Key parameters in a grid
            Row(
              children: [
                Expanded(
                  child: _buildParameterItem(
                      'Age', '${prediction['age'] ?? 'N/A'}'),
                ),
                Expanded(
                  child: _buildParameterItem(
                      'Sex', prediction['sex'] == 1 ? 'Male' : 'Female'),
                ),
                Expanded(
                  child: _buildParameterItem(
                      'CP', _getChestPainType(prediction['cp'])),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Row(
              children: [
                Expanded(
                  child: _buildParameterItem(
                      'Trestbps', '${prediction['trestbps'] ?? 'N/A'} mmHg'),
                ),
                Expanded(
                  child: _buildParameterItem(
                      'Chol', '${prediction['chol'] ?? 'N/A'} mg/dl'),
                ),
                Expanded(
                  child: _buildParameterItem(
                      'FBS', prediction['fbs'] == 1 ? '>120' : '≤120'),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Row(
              children: [
                Expanded(
                  child: _buildParameterItem(
                      'RestECG', _getEcgResults(prediction['restecg'])),
                ),
                Expanded(
                  child: _buildParameterItem(
                      'Thalach', '${prediction['thalach'] ?? 'N/A'}'),
                ),
                Expanded(
                  child: _buildParameterItem(
                      'Exang', prediction['exang'] == 1 ? 'Yes' : 'No'),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Row(
              children: [
                Expanded(
                  child: _buildParameterItem(
                      'Oldpeak', '${prediction['oldpeak'] ?? 'N/A'}'),
                ),
                Expanded(
                  child: _buildParameterItem(
                      'Slope', _getSlopeType(prediction['slope'])),
                ),
                Expanded(
                  child:
                      _buildParameterItem('CA', '${prediction['ca'] ?? 'N/A'}'),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Row(
              children: [
                Expanded(
                  child: _buildParameterItem(
                      'Thal', _getThalassemiaType(prediction['thal'])),
                ),
                const Expanded(child: SizedBox()),
                const Expanded(child: SizedBox()),
              ],
            ),

            const SizedBox(height: 16),

            // Additional Info
            Row(
              children: [
                Icon(Icons.calendar_today, size: 16, color: Colors.grey[500]),
                const SizedBox(width: 8),
                Text(
                  'Created: ${_formatDate(prediction['created_at'])}',
                  style: TextStyle(
                    fontSize: 12,
                    color: Colors.grey[500],
                  ),
                ),
                const Spacer(),
                if (prediction['verified_by_expert'] == true)
                  Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: AppTheme.successColor.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.verified,
                            size: 12, color: AppTheme.successColor),
                        SizedBox(width: 4),
                        Text(
                          'Verified',
                          style: TextStyle(
                            color: AppTheme.successColor,
                            fontWeight: FontWeight.w600,
                            fontSize: 12,
                          ),
                        ),
                      ],
                    ),
                  ),
              ],
            ),

            const SizedBox(height: 12),

            // Action Buttons
            Row(
              children: [
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () => _viewPredictionDetails(prediction),
                    icon: const Icon(Icons.visibility, size: 16),
                    label: const Text('View Details'),
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 8),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () => _exportPrediction(prediction),
                    icon: const Icon(Icons.download, size: 16),
                    label: const Text('Export'),
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 8),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildParameterItem(String label, String value) {
    return Container(
      padding: const EdgeInsets.all(8),
      decoration: BoxDecoration(
        color: Colors.grey[50],
        borderRadius: BorderRadius.circular(8),
      ),
      child: Column(
        children: [
          Text(
            label,
            style: TextStyle(
              fontSize: 12,
              color: Colors.grey[600],
              fontWeight: FontWeight.w500,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            value,
            style: const TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.bold,
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  String _getChestPainType(dynamic cp) {
    switch (cp) {
      case 0:
        return 'Typical angina';
      case 1:
        return 'Atypical angina';
      case 2:
        return 'Non-anginal pain';
      case 3:
        return 'Asymptomatic';
      default:
        return 'Unknown';
    }
  }

  String _getEcgResults(dynamic restecg) {
    switch (restecg) {
      case 0:
        return 'Normal';
      case 1:
        return 'ST-T wave abnormality';
      case 2:
        return 'Left ventricular hypertrophy';
      default:
        return 'Unknown';
    }
  }

  String _getSlopeType(dynamic slope) {
    switch (slope) {
      case 0:
        return 'Upsloping';
      case 1:
        return 'Flat';
      case 2:
        return 'Downsloping';
      default:
        return 'Unknown';
    }
  }

  String _getThalassemiaType(dynamic thal) {
    switch (thal) {
      case 0:
        return 'Normal';
      case 1:
        return 'Fixed defect';
      case 2:
        return 'Reversible defect';
      case 3:
        return 'Not applicable';
      default:
        return 'Unknown';
    }
  }

  String _formatDate(dynamic date) {
    if (date == null) return 'Unknown';
    try {
      final dateTime = DateTime.parse(date.toString());
      return '${dateTime.day}/${dateTime.month}/${dateTime.year} ${dateTime.hour}:${dateTime.minute}';
    } catch (e) {
      return 'Invalid date';
    }
  }

  void _viewPredictionDetails(Map<String, dynamic> prediction) {
    // TODO: Implement view prediction details functionality
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
          content: Text('View details for prediction ID: ${prediction['id']}')),
    );
  }

  void _exportPrediction(Map<String, dynamic> prediction) {
    // TODO: Implement export prediction functionality
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('Export prediction ID: ${prediction['id']}')),
    );
  }
}
