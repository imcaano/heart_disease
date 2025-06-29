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
    final authProvider = context.watch<AuthProvider>();
    final predictionProvider = context.watch<PredictionProvider>();
    final user = authProvider.currentUser;

    if (user == null) {
      // Redirect to login if not logged in
      Future.microtask(() => AppRouter.navigateToAndClear(AppRouter.login));
      return const Scaffold();
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('Dashboard'),
        actions: [
          IconButton(
            icon: const Icon(Icons.notifications),
            onPressed: () {
              // TODO: Implement notifications
            },
          ),
          IconButton(
            icon: const Icon(Icons.person),
            onPressed: () {
              AppRouter.navigateTo(AppRouter.profile);
            },
          ),
        ],
      ),
      drawer: const AppDrawer(),
      body: RefreshIndicator(
        onRefresh: _loadData,
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Welcome Section
              _buildWelcomeCard(user),
              const SizedBox(height: 20),

              // Quick Stats
              _buildStatsGrid(predictionProvider),
              const SizedBox(height: 20),

              // Quick Actions
              _buildQuickActions(),
              const SizedBox(height: 20),

              // Recent Predictions
              _buildRecentPredictions(predictionProvider),
            ],
          ),
        ),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () {
          AppRouter.navigateTo(AppRouter.prediction);
        },
        icon: const Icon(Icons.add),
        label: const Text('New Prediction'),
        backgroundColor: AppTheme.primaryColor,
      ),
    );
  }

  Widget _buildWelcomeCard(User user) {
    return Card(
      elevation: 4,
      child: Container(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(12),
          gradient: LinearGradient(
            colors: [AppTheme.primaryColor, AppTheme.primaryDark],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
        ),
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  CircleAvatar(
                    radius: 30,
                    backgroundColor: Colors.white.withOpacity(0.2),
                    child: Icon(Icons.person, size: 30, color: Colors.white),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Welcome back,',
                          style: Theme.of(context)
                              .textTheme
                              .bodyLarge
                              ?.copyWith(color: Colors.white.withOpacity(0.8)),
                        ),
                        Text(
                          user.username,
                          style: Theme.of(
                            context,
                          ).textTheme.headlineSmall?.copyWith(
                                color: Colors.white,
                                fontWeight: FontWeight.bold,
                              ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              Text(
                'Monitor your heart health and get AI-powered insights',
                style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                      color: Colors.white.withOpacity(0.9),
                    ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildStatsGrid(PredictionProvider predictionProvider) {
    final stats = predictionProvider.dashboardStats;
    final totalPredictions =
        stats?['total_predictions'] ?? stats?['totalPredictions'] ?? 0;
    final highRiskPredictions = stats?['high_risk'] ??
        stats?['highRiskCount'] ??
        stats?['highRisk'] ??
        0;
    final lowRiskPredictions =
        stats?['low_risk'] ?? stats?['lowRiskCount'] ?? stats?['lowRisk'] ?? 0;
    final successRate = totalPredictions > 0
        ? ((lowRiskPredictions / totalPredictions) * 100).toStringAsFixed(1)
        : '0';

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
          totalPredictions.toString(),
          Icons.analytics,
          AppTheme.primaryColor,
        ),
        _buildStatCard(
          'Positive',
          highRiskPredictions.toString(),
          Icons.warning,
          AppTheme.dangerColor,
        ),
        _buildStatCard(
          'Negative',
          lowRiskPredictions.toString(),
          Icons.check_circle,
          AppTheme.successColor,
        ),
        _buildStatCard(
          'Success Rate',
          '$successRate%',
          Icons.trending_up,
          AppTheme.warningColor,
        ),
      ],
    );
  }

  Widget _buildStatCard(
    String title,
    String value,
    IconData icon,
    Color color,
  ) {
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
              style: Theme.of(
                context,
              ).textTheme.bodySmall?.copyWith(color: AppTheme.gray600),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildQuickActions() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Quick Actions',
          style: Theme.of(
            context,
          ).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 16),
        Row(
          children: [
            Expanded(
              child: _buildActionCard(
                'New Prediction',
                Icons.add_circle,
                AppTheme.primaryColor,
                () => AppRouter.navigateTo(AppRouter.prediction),
              ),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: _buildActionCard(
                'View Reports',
                Icons.assessment,
                AppTheme.successColor,
                () => AppRouter.navigateTo(AppRouter.reports),
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildActionCard(
    String title,
    IconData icon,
    Color color,
    VoidCallback onTap,
  ) {
    return Card(
      elevation: 2,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            children: [
              Icon(icon, color: color, size: 32),
              const SizedBox(height: 12),
              Text(
                title,
                style: Theme.of(
                  context,
                ).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w600),
                textAlign: TextAlign.center,
              ),
            ],
          ),
        ),
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
                  Icon(
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
