import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../../core/routes/app_router.dart';
import '../../../../core/providers/auth_provider.dart';
import '../../../../core/providers/prediction_provider.dart';
import '../../../../core/theme/app_theme.dart';
import '../../../../core/models/prediction.dart';
import '../../../../core/models/user.dart';
import '../../../../core/widgets/app_drawer.dart';

class DashboardPage extends StatefulWidget {
  const DashboardPage({super.key});

  @override
  State<DashboardPage> createState() => _DashboardPageState();
}

class _DashboardPageState extends State<DashboardPage> {
  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    final authProvider = context.read<AuthProvider>();
    final predictionProvider = context.read<PredictionProvider>();

    if (authProvider.currentUser != null) {
      await predictionProvider.loadUserPredictions(
        authProvider.currentUser!.id,
      );
      await predictionProvider
          .fetchDashboardStats(authProvider.currentUser!.id);
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = Provider.of<AuthProvider>(context).currentUser;
    if (Provider.of<AuthProvider>(context).isLoading) {
      return const Center(child: CircularProgressIndicator());
    }
    return Scaffold(
      appBar: AppBar(
        title: const Text('Dashboard'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadData,
            tooltip: 'Refresh',
          ),
        ],
      ),
      drawer: const AppDrawer(),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (user != null && user.role == 'user')
              Padding(
                padding: const EdgeInsets.all(16.0),
                child: Container(
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: [
                        AppTheme.primaryColor,
                        AppTheme.primaryColor.withOpacity(0.7)
                      ],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(20),
                    boxShadow: [
                      BoxShadow(
                        color: AppTheme.primaryColor.withOpacity(0.2),
                        blurRadius: 10,
                        offset: Offset(0, 4),
                      ),
                    ],
                  ),
                  padding:
                      const EdgeInsets.symmetric(vertical: 32, horizontal: 24),
                  child: Row(
                    children: [
                      CircleAvatar(
                        radius: 32,
                        backgroundColor: Colors.white,
                        child: Icon(Icons.person,
                            size: 40, color: AppTheme.primaryColor),
                      ),
                      SizedBox(width: 24),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Welcome, ${user.username}!',
                              style: TextStyle(
                                color: Colors.white,
                                fontSize: 24,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            SizedBox(height: 8),
                            Text(
                              'Here you can manage your health, view predictions, and book consultations.',
                              style: TextStyle(
                                color: Colors.white.withOpacity(0.9),
                                fontSize: 16,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            SizedBox(height: 24),

            // Health Tip Banner
            _buildHealthTipBanner(),

            SizedBox(height: 24),

            // Simple welcome message
            Center(
              child: Card(
                elevation: 2,
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(16)),
                child: Padding(
                  padding: const EdgeInsets.all(24),
                  child: Column(
                    children: [
                      Icon(
                        Icons.favorite,
                        size: 48,
                        color: AppTheme.primaryColor,
                      ),
                      SizedBox(height: 16),
                      Text(
                        'Welcome to Heart Disease Prediction',
                        style: TextStyle(
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                          color: AppTheme.primaryColor,
                        ),
                        textAlign: TextAlign.center,
                      ),
                      SizedBox(height: 8),
                      Text(
                        'Use the navigation menu to explore features and manage your health.',
                        style: TextStyle(
                          fontSize: 16,
                          color: AppTheme.gray600,
                        ),
                        textAlign: TextAlign.center,
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildHealthTipBanner() {
    // You can randomize or rotate tips if you want
    const tip =
        '💡 Tip: Regular exercise and a healthy diet can reduce your risk of heart disease!';
    return Card(
      color: AppTheme.successColor.withOpacity(0.1),
      elevation: 0,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          children: [
            const Icon(Icons.health_and_safety, color: AppTheme.successColor),
            const SizedBox(width: 12),
            Expanded(
              child: Text(
                tip,
                style: const TextStyle(
                    color: AppTheme.successColor, fontWeight: FontWeight.w600),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDashboardSummary(PredictionProvider predictionProvider) {
    final stats = predictionProvider.dashboardStats;
    final totalPredictions = stats?['total_predictions'] ??
        stats?['totalPredictions'] ??
        predictionProvider.totalPredictions;
    final highRisk = stats?['high_risk_cases'] ??
        stats?['highRisk'] ??
        predictionProvider.highRiskPredictions;
    final lowRisk = stats?['low_risk_cases'] ??
        stats?['lowRisk'] ??
        predictionProvider.lowRiskPredictions;
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceEvenly,
          children: [
            _buildSummaryStat('Total', totalPredictions, AppTheme.primaryColor,
                Icons.analytics),
            _buildSummaryStat('Positive', highRisk, Colors.red, Icons.warning),
            _buildSummaryStat(
                'Negative', lowRisk, Colors.green, Icons.check_circle),
          ],
        ),
      ),
    );
  }

  Widget _buildSummaryStat(
      String label, int value, Color color, IconData icon) {
    return Column(
      children: [
        Icon(icon, color: color, size: 32),
        const SizedBox(height: 4),
        Text('$value',
            style: TextStyle(
                fontSize: 28, fontWeight: FontWeight.bold, color: color)),
        const SizedBox(height: 4),
        Text(label, style: TextStyle(color: color)),
      ],
    );
  }

  Widget _buildRiskDistributionChart(PredictionProvider predictionProvider) {
    final stats = predictionProvider.dashboardStats;
    final highRisk = stats?['high_risk_cases'] ?? stats?['highRisk'] ?? 0;
    final lowRisk = stats?['low_risk_cases'] ?? stats?['lowRisk'] ?? 0;
    final total = highRisk + lowRisk;
    if (total == 0) return const SizedBox();
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Risk Distribution',
                style: Theme.of(context)
                    .textTheme
                    .titleMedium
                    ?.copyWith(fontWeight: FontWeight.bold)),
            const SizedBox(height: 16),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: [
                _buildRiskPie(highRisk, lowRisk),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildRiskPie(int highRisk, int lowRisk) {
    final total = highRisk + lowRisk;
    final highPercent = total > 0 ? (highRisk / total) * 100 : 0;
    final lowPercent = total > 0 ? (lowRisk / total) * 100 : 0;
    return SizedBox(
      width: 100,
      height: 100,
      child: Stack(
        children: [
          CircularProgressIndicator(
            value: highPercent / 100,
            backgroundColor: Colors.green.withOpacity(0.2),
            valueColor: const AlwaysStoppedAnimation<Color>(Colors.red),
            strokeWidth: 10,
          ),
          Center(
            child: Text(
              '${highPercent.toStringAsFixed(0)}%\nHigh',
              textAlign: TextAlign.center,
              style: const TextStyle(
                  fontWeight: FontWeight.bold, color: Colors.red),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildRecentPredictions(PredictionProvider predictionProvider) {
    final recentPredictions = predictionProvider.predictions.take(5).toList();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              'Recent Predictions',
              style: Theme.of(
                context,
              ).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold),
            ),
            TextButton(
              onPressed: () {
                AppRouter.navigateTo(AppRouter.reports);
              },
              child: const Text('View All'),
            ),
          ],
        ),
        const SizedBox(height: 16),
        if (recentPredictions.isEmpty)
          Card(
            child: Padding(
              padding: const EdgeInsets.all(40),
              child: Column(
                children: [
                  const Icon(
                    Icons.analytics_outlined,
                    size: 48,
                    color: AppTheme.gray400,
                  ),
                  const SizedBox(height: 16),
                  Text(
                    'No predictions yet',
                    style: Theme.of(
                      context,
                    ).textTheme.titleMedium?.copyWith(color: AppTheme.gray600),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Start by making your first prediction',
                    style: Theme.of(
                      context,
                    ).textTheme.bodyMedium?.copyWith(color: AppTheme.gray500),
                  ),
                ],
              ),
            ),
          )
        else
          ...recentPredictions.map(
            (prediction) => _buildPredictionTile(prediction),
          ),
      ],
    );
  }

  Widget _buildPredictionTile(Prediction prediction) {
    final isHighRisk = prediction.prediction == 1;
    final riskColor = isHighRisk ? AppTheme.dangerColor : AppTheme.successColor;
    final riskText = isHighRisk ? 'Positive' : 'Negative';

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: ListTile(
        leading: Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: riskColor.withOpacity(0.1),
            borderRadius: BorderRadius.circular(8),
          ),
          child: Icon(
            isHighRisk ? Icons.warning : Icons.check_circle,
            color: riskColor,
            size: 20,
          ),
        ),
        title: Text(
          riskText,
          style: Theme.of(context).textTheme.titleMedium?.copyWith(
                fontWeight: FontWeight.w600,
                color: riskColor,
              ),
        ),
        subtitle: Text(
          'Confidence: ${((prediction.probability ?? 0.0) * 100).toStringAsFixed(1)}%',
          style: Theme.of(
            context,
          ).textTheme.bodyMedium?.copyWith(color: AppTheme.gray600),
        ),
        trailing: Text(
          _formatDate(prediction.createdAt ?? DateTime.now()),
          style: Theme.of(
            context,
          ).textTheme.bodySmall?.copyWith(color: AppTheme.gray500),
        ),
        onTap: () {
          // TODO: Navigate to prediction details
        },
      ),
    );
  }

  String _formatDate(DateTime date) {
    return '${date.day}/${date.month}/${date.year}';
  }
}
