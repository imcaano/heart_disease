import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:fl_chart/fl_chart.dart';
import '../../../../core/providers/auth_provider.dart';
import '../../../../core/providers/prediction_provider.dart';
import '../../../../core/services/api_service.dart';
import '../../../../core/theme/app_theme.dart';
import '../../../../core/models/prediction.dart';
import '../../../../core/widgets/app_drawer.dart';

class ReportsPage extends StatefulWidget {
  const ReportsPage({super.key});

  @override
  State<ReportsPage> createState() => _ReportsPageState();
}

class _ReportsPageState extends State<ReportsPage> {
  bool _isLoading = false;
  List<Prediction> _predictions = [];
  Map<String, dynamic>? _statistics;
  String _selectedFilter = 'all';
  DateTime? _startDate;
  DateTime? _endDate;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);
    try {
      final authProvider = context.read<AuthProvider>();
      final predictionProvider = context.read<PredictionProvider>();

      // Load user predictions
      await predictionProvider
          .loadUserPredictions(authProvider.currentUser!.id);
      _predictions = predictionProvider.predictions;

      // Load statistics
      await _loadStatistics();
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error loading reports: $e')),
      );
    } finally {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _loadStatistics() async {
    try {
      final authProvider = context.read<AuthProvider>();
      final apiService = ApiService();
      final response =
          await apiService.getDashboardStats(authProvider.currentUser!.id);
      if (response.success) {
        setState(() {
          _statistics = response.data;
        });
      }
    } catch (e) {
      // Statistics loading failed, continue without them
    }
  }

  List<Prediction> get _filteredPredictions {
    List<Prediction> filtered = _predictions;

    // Apply result filter
    if (_selectedFilter == 'positive') {
      filtered = filtered.where((p) => p.prediction == 1).toList();
    } else if (_selectedFilter == 'negative') {
      filtered = filtered.where((p) => p.prediction == 0).toList();
    }

    // Apply date filter
    if (_startDate != null) {
      filtered = filtered
          .where(
              (p) => p.createdAt != null && p.createdAt!.isAfter(_startDate!))
          .toList();
    }
    if (_endDate != null) {
      filtered = filtered
          .where((p) =>
              p.createdAt != null &&
              p.createdAt!.isBefore(_endDate!.add(const Duration(days: 1))))
          .toList();
    }

    return filtered;
  }

  Future<void> _downloadReport() async {
    try {
      final apiService = ApiService();
      // Note: This endpoint might not exist in the current API, so we'll show a placeholder
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Report download feature coming soon!'),
        ),
      );
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error generating report: $e')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<AuthProvider>(
      builder: (context, authProvider, child) {
        final user = authProvider.currentUser;
        if (authProvider.isLoading) {
          return const Center(child: CircularProgressIndicator());
        }
        if (user == null) {
          return Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(Icons.error, color: Colors.red, size: 40),
                SizedBox(height: 16),
                Text('User not logged in', style: TextStyle(color: Colors.red)),
                SizedBox(height: 16),
                ElevatedButton(
                  onPressed: () => Navigator.pushNamed(context, '/login'),
                  style: ElevatedButton.styleFrom(
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                  child: Text('Go to Login'),
                ),
              ],
            ),
          );
        }
        return Scaffold(
          appBar: AppBar(
            title: const Text('Reports'),
            backgroundColor: AppTheme.primaryColor,
            foregroundColor: Colors.white,
            elevation: 0,
            actions: [
              IconButton(
                icon: const Icon(Icons.download),
                onPressed: _downloadReport,
                tooltip: 'Download Report',
              ),
            ],
          ),
          drawer: const AppDrawer(),
          body: _isLoading
              ? const Center(child: CircularProgressIndicator())
              : RefreshIndicator(
                  onRefresh: _loadData,
                  child: SingleChildScrollView(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Statistics Cards
                        if (_statistics != null) ...[
                          _buildStatisticsCards(),
                          const SizedBox(height: 24),
                        ],

                        // Charts
                        _buildCharts(),
                        const SizedBox(height: 24),

                        // Filters
                        _buildFilters(),
                        const SizedBox(height: 24),

                        // Predictions Table
                        _buildPredictionsTable(),
                      ],
                    ),
                  ),
                ),
        );
      },
    );
  }

  Widget _buildStatisticsCards() {
    final stats = _statistics!;
    return GridView.count(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      crossAxisCount: 2,
      crossAxisSpacing: 16,
      mainAxisSpacing: 16,
      childAspectRatio: 1.5,
      children: [
        _buildStatCard(
          'Total Predictions',
          '${stats['total_predictions'] ?? 0}',
          Icons.analytics,
          AppTheme.primaryColor,
        ),
        _buildStatCard(
          'Accuracy Rate',
          '${stats['system_accuracy'] ?? 0}%',
          Icons.trending_up,
          AppTheme.successColor,
        ),
        _buildStatCard(
          'High Risk Cases',
          '${stats['high_risk_cases'] ?? 0}',
          Icons.warning,
          AppTheme.dangerColor,
        ),
        _buildStatCard(
          'Low Risk Cases',
          '${stats['low_risk_cases'] ?? 0}',
          Icons.check_circle,
          AppTheme.successColor,
        ),
      ],
    );
  }

  Widget _buildStatCard(
      String title, String value, IconData icon, Color color) {
    return Card(
      elevation: 2,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, color: color, size: 32),
            const SizedBox(height: 8),
            Text(
              value,
              style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                    color: color,
                    fontWeight: FontWeight.bold,
                  ),
            ),
            const SizedBox(height: 4),
            Text(
              title,
              style: Theme.of(context).textTheme.bodySmall?.copyWith(
                    color: AppTheme.gray600,
                  ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCharts() {
    if (_predictions.isEmpty) {
      return Card(
        child: Padding(
          padding: const EdgeInsets.all(40),
          child: Column(
            children: [
              const Icon(Icons.bar_chart, size: 48, color: AppTheme.gray400),
              const SizedBox(height: 16),
              Text(
                'No data for charts',
                style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      color: AppTheme.gray600,
                    ),
              ),
            ],
          ),
        ),
      );
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Analytics',
          style: Theme.of(context).textTheme.titleLarge?.copyWith(
                fontWeight: FontWeight.bold,
              ),
        ),
        const SizedBox(height: 16),

        // Risk Distribution Chart
        Card(
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Risk Distribution',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.w600,
                      ),
                ),
                const SizedBox(height: 16),
                SizedBox(
                  height: 200,
                  child: PieChart(
                    PieChartData(
                      sections: [
                        PieChartSectionData(
                          value: _predictions
                              .where((p) => p.prediction == 1)
                              .length
                              .toDouble(),
                          title: 'High Risk',
                          color: AppTheme.dangerColor,
                          radius: 60,
                        ),
                        PieChartSectionData(
                          value: _predictions
                              .where((p) => p.prediction == 0)
                              .length
                              .toDouble(),
                          title: 'Low Risk',
                          color: AppTheme.successColor,
                          radius: 60,
                        ),
                      ],
                      centerSpaceRadius: 40,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 16),

        // Confidence Distribution Chart
        Card(
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Confidence Distribution',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.w600,
                      ),
                ),
                const SizedBox(height: 16),
                SizedBox(
                  height: 200,
                  child: BarChart(
                    BarChartData(
                      alignment: BarChartAlignment.spaceAround,
                      maxY: 100,
                      barTouchData: BarTouchData(enabled: false),
                      titlesData: FlTitlesData(
                        show: true,
                        bottomTitles: AxisTitles(
                          sideTitles: SideTitles(
                            showTitles: true,
                            getTitlesWidget: (value, meta) {
                              const labels = [
                                '0-20',
                                '21-40',
                                '41-60',
                                '61-80',
                                '81-100'
                              ];
                              if (value.toInt() < labels.length) {
                                return Text(labels[value.toInt()]);
                              }
                              return const Text('');
                            },
                          ),
                        ),
                        leftTitles: const AxisTitles(
                          sideTitles: SideTitles(
                            showTitles: true,
                            reservedSize: 40,
                          ),
                        ),
                        topTitles: const AxisTitles(
                            sideTitles: SideTitles(showTitles: false)),
                        rightTitles: const AxisTitles(
                            sideTitles: SideTitles(showTitles: false)),
                      ),
                      borderData: FlBorderData(show: false),
                      barGroups: _buildConfidenceBarGroups(),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }

  List<BarChartGroupData> _buildConfidenceBarGroups() {
    final confidenceRanges = [
      0,
      0,
      0,
      0,
      0
    ]; // 0-20, 21-40, 41-60, 61-80, 81-100

    for (final prediction in _predictions) {
      final confidence = ((prediction.probability ?? 0.0) * 100).round();
      if (confidence <= 20) {
        confidenceRanges[0]++;
      } else if (confidence <= 40)
        confidenceRanges[1]++;
      else if (confidence <= 60)
        confidenceRanges[2]++;
      else if (confidence <= 80)
        confidenceRanges[3]++;
      else
        confidenceRanges[4]++;
    }

    return List.generate(
        5,
        (index) => BarChartGroupData(
              x: index,
              barRods: [
                BarChartRodData(
                  toY: confidenceRanges[index].toDouble(),
                  color: AppTheme.primaryColor,
                  width: 20,
                ),
              ],
            ));
  }

  Widget _buildFilters() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Filters',
              style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.w600,
                  ),
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                Expanded(
                  child: DropdownButtonFormField<String>(
                    value: _selectedFilter,
                    decoration: const InputDecoration(
                      labelText: 'Result Filter',
                      border: OutlineInputBorder(),
                    ),
                    items: const [
                      DropdownMenuItem(
                          value: 'all', child: Text('All Results')),
                      DropdownMenuItem(
                          value: 'positive', child: Text('High Risk')),
                      DropdownMenuItem(
                          value: 'negative', child: Text('Low Risk')),
                    ],
                    onChanged: (value) {
                      setState(() => _selectedFilter = value!);
                    },
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: TextFormField(
                    decoration: const InputDecoration(
                      labelText: 'Start Date',
                      border: OutlineInputBorder(),
                    ),
                    readOnly: true,
                    onTap: () async {
                      final date = await showDatePicker(
                        context: context,
                        initialDate: _startDate ??
                            DateTime.now().subtract(const Duration(days: 30)),
                        firstDate: DateTime(2020),
                        lastDate: DateTime.now(),
                      );
                      if (date != null) {
                        setState(() => _startDate = date);
                      }
                    },
                    controller: TextEditingController(
                      text: _startDate?.toString().split(' ')[0] ?? '',
                    ),
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: TextFormField(
                    decoration: const InputDecoration(
                      labelText: 'End Date',
                      border: OutlineInputBorder(),
                    ),
                    readOnly: true,
                    onTap: () async {
                      final date = await showDatePicker(
                        context: context,
                        initialDate: _endDate ?? DateTime.now(),
                        firstDate: DateTime(2020),
                        lastDate: DateTime.now(),
                      );
                      if (date != null) {
                        setState(() => _endDate = date);
                      }
                    },
                    controller: TextEditingController(
                      text: _endDate?.toString().split(' ')[0] ?? '',
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

  Widget _buildPredictionsTable() {
    final filteredPredictions = _filteredPredictions;

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Prediction History',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.w600,
                      ),
                ),
                Text(
                  '${filteredPredictions.length} predictions',
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                        color: AppTheme.gray600,
                      ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            if (filteredPredictions.isEmpty)
              Center(
                child: Padding(
                  padding: const EdgeInsets.all(40),
                  child: Column(
                    children: [
                      const Icon(Icons.analytics_outlined,
                          size: 48, color: AppTheme.gray400),
                      const SizedBox(height: 16),
                      Text(
                        'No predictions found',
                        style:
                            Theme.of(context).textTheme.titleMedium?.copyWith(
                                  color: AppTheme.gray600,
                                ),
                      ),
                    ],
                  ),
                ),
              )
            else
              SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: DataTable(
                  columns: const [
                    DataColumn(label: Text('Date')),
                    DataColumn(label: Text('Result')),
                    DataColumn(label: Text('Confidence')),
                    DataColumn(label: Text('Risk Level')),
                  ],
                  rows: filteredPredictions.map((prediction) {
                    final isHighRisk = prediction.prediction == 1;
                    return DataRow(
                      cells: [
                        DataCell(Text(_formatDate(
                            prediction.createdAt ?? DateTime.now()))),
                        DataCell(Text(isHighRisk ? 'High Risk' : 'Low Risk')),
                        DataCell(Text(
                            '${((prediction.probability ?? 0.0) * 100).toStringAsFixed(1)}%')),
                        DataCell(
                          Container(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 8, vertical: 4),
                            decoration: BoxDecoration(
                              color: isHighRisk
                                  ? AppTheme.dangerColor.withOpacity(0.1)
                                  : AppTheme.successColor.withOpacity(0.1),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Text(
                              isHighRisk ? 'High' : 'Low',
                              style: TextStyle(
                                color: isHighRisk
                                    ? AppTheme.dangerColor
                                    : AppTheme.successColor,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ),
                        ),
                      ],
                    );
                  }).toList(),
                ),
              ),
          ],
        ),
      ),
    );
  }

  String _formatDate(DateTime date) {
    return '${date.day}/${date.month}/${date.year}';
  }
}
