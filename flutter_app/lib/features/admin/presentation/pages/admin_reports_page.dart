import 'package:flutter/material.dart';
import '../../../../core/services/api_service.dart';
import '../../../../core/theme/app_theme.dart';
import '../../../../core/providers/auth_provider.dart';
import 'package:provider/provider.dart';
import '../../../../core/widgets/app_drawer.dart';

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
        title: const Text('Admin Reports', style: TextStyle(color: Colors.white)),
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
      drawer: const AppDrawer(),
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
            // Filter Bar
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  const Text('Filter by result: '),
                  const SizedBox(width: 8),
                  SizedBox(
                    width: 180,
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
                        DropdownMenuItem(
                            value: 'high', child: Text('High Risk')),
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
    final address = (prediction['address'] ?? '').toString().isNotEmpty ? prediction['address'] : 'No address (no appointment)';

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Address
            if (address.isNotEmpty) ...[
              Text(
                'Address: $address',
                style: TextStyle(
                  fontSize: 14,
                  color: Colors.grey[700],
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 8),
            ],
            // Prediction fields (showing a summary)
            Text('Prediction Details:',
                style: TextStyle(fontWeight: FontWeight.bold)),
            Text('Age: 	${prediction['age']}'),
            Text('Sex: 	${prediction['sex'] == 1 ? 'Male' : 'Female'}'),
            Text('Chest Pain: 	${prediction['cp']}'),
            Text('BP: 	${prediction['trestbps']}'),
            Text('Chol: 	${prediction['chol']}'),
            Text('FBS: 	${prediction['fbs']}'),
            Text('ECG: 	${prediction['restecg']}'),
            Text('Thalach: 	${prediction['thalach']}'),
            Text('Exang: 	${prediction['exang']}'),
            Text('Oldpeak: 	${prediction['oldpeak']}'),
            Text('Slope: 	${prediction['slope']}'),
            Text('CA: 	${prediction['ca']}'),
            Text('Thal: 	${prediction['thal']}'),
            const SizedBox(height: 8),
            // Prediction result
            Row(
              children: [
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
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
                const SizedBox(width: 8),
                Text('Confidence: ${confidence.toStringAsFixed(2)}'),
              ],
            ),
            const SizedBox(height: 8),
            Text('Created: ${_formatDate(prediction['created_at'])}',
                style: TextStyle(fontSize: 12, color: Colors.grey[500])),
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
