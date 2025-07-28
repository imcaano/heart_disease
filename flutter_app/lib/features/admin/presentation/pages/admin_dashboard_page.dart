import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:fl_chart/fl_chart.dart';
import '../../../../core/routes/app_router.dart';
import '../../../../core/providers/auth_provider.dart';
import '../../../../core/services/api_service.dart';
import '../../../../core/theme/app_theme.dart';
import '../../../../core/widgets/app_drawer.dart';

class AdminDashboardPage extends StatefulWidget {
  const AdminDashboardPage({super.key});

  @override
  State<AdminDashboardPage> createState() => _AdminDashboardPageState();
}

class _AdminDashboardPageState extends State<AdminDashboardPage>
    with TickerProviderStateMixin {
  bool _isLoading = false;
  Map<String, dynamic>? _dashboardData;
  List<Map<String, dynamic>> _recentActivities = [];
  List<Map<String, dynamic>> _topUsers = [];

  // Animation controllers for animated numbers
  late AnimationController _totalUsersController;
  late AnimationController _totalPredictionsController;
  late AnimationController _positivePredictionsController;
  late AnimationController _negativePredictionsController;

  // Animation values
  late Animation<double> _totalUsersAnimation;
  late Animation<double> _totalPredictionsAnimation;
  late Animation<double> _positivePredictionsAnimation;
  late Animation<double> _negativePredictionsAnimation;

  @override
  void initState() {
    super.initState();

    // Initialize animation controllers
    _totalUsersController = AnimationController(
      duration: const Duration(milliseconds: 1500),
      vsync: this,
    );
    _totalPredictionsController = AnimationController(
      duration: const Duration(milliseconds: 1500),
      vsync: this,
    );
    _positivePredictionsController = AnimationController(
      duration: const Duration(milliseconds: 1500),
      vsync: this,
    );
    _negativePredictionsController = AnimationController(
      duration: const Duration(milliseconds: 1500),
      vsync: this,
    );

    _loadDashboardData();
  }

  @override
  void dispose() {
    _totalUsersController.dispose();
    _totalPredictionsController.dispose();
    _positivePredictionsController.dispose();
    _negativePredictionsController.dispose();
    super.dispose();
  }

  Future<void> _loadDashboardData() async {
    setState(() => _isLoading = true);
    try {
      final apiService = ApiService();
      // Use getDashboardStats() to match web dashboard
      final response = await apiService.getDashboardStats();
      if (response.isSuccess && response.data != null) {
        setState(() {
          _dashboardData = response.data;
          _recentActivities = List<Map<String, dynamic>>.from(
              response.data?['recent_activities'] ?? []);
          _topUsers = List<Map<String, dynamic>>.from(
              response.data?['top_users'] ?? []);
        });
        _startAnimations();
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error loading dashboard: $e')),
      );
    } finally {
      setState(() => _isLoading = false);
    }
  }

  void _startAnimations() {
    if (_dashboardData == null) return;
    _totalUsersController.reset();
    _totalPredictionsController.reset();
    _positivePredictionsController.reset();
    _negativePredictionsController.reset();
    // Use the correct keys from dashboard_stats.php
    _totalUsersAnimation = Tween<double>(
      begin: 0,
      end: (_dashboardData!['totalUsers'] ?? 0).toDouble(),
    ).animate(CurvedAnimation(
      parent: _totalUsersController,
      curve: Curves.easeOutCubic,
    ));
    _totalPredictionsAnimation = Tween<double>(
      begin: 0,
      end: (_dashboardData!['totalPredictions'] ?? 0).toDouble(),
    ).animate(CurvedAnimation(
      parent: _totalPredictionsController,
      curve: Curves.easeOutCubic,
    ));
    _positivePredictionsAnimation = Tween<double>(
      begin: 0,
      end: (_dashboardData!['positivePredictions'] ?? 0).toDouble(),
    ).animate(CurvedAnimation(
      parent: _positivePredictionsController,
      curve: Curves.easeOutCubic,
    ));
    _negativePredictionsAnimation = Tween<double>(
      begin: 0,
      end: (_dashboardData!['negativePredictions'] ?? 0).toDouble(),
    ).animate(CurvedAnimation(
      parent: _negativePredictionsController,
      curve: Curves.easeOutCubic,
    ));
    _totalUsersController.forward();
    Future.delayed(const Duration(milliseconds: 200), () {
      _totalPredictionsController.forward();
    });
    Future.delayed(const Duration(milliseconds: 400), () {
      _positivePredictionsController.forward();
    });
    Future.delayed(const Duration(milliseconds: 600), () {
      _negativePredictionsController.forward();
    });
  }

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(
      builder: (context, constraints) {
        return Scaffold(
          appBar: AppBar(
            title: const Text('Admin Dashboard'),
            backgroundColor: AppTheme.primaryColor,
            foregroundColor: Colors.white,
            elevation: 0,
            actions: [
              IconButton(
                icon: const Icon(Icons.refresh),
                onPressed: _loadDashboardData,
                tooltip: 'Refresh',
              ),
              IconButton(
                icon: const Icon(Icons.logout),
                tooltip: 'Logout',
                onPressed: () async {
                  await context.read<AuthProvider>().logout();
                  if (mounted) {
                    Navigator.of(context)
                        .pushNamedAndRemoveUntil('/login', (route) => false);
                  }
                },
              ),
            ],
          ),
          drawer: const AppDrawer(),
          body: SingleChildScrollView(
            padding: const EdgeInsets.all(16),
            child: ConstrainedBox(
              constraints: BoxConstraints(
                minHeight: constraints.maxHeight - 32,
              ),
              child: IntrinsicHeight(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    // Welcome Section
                    _buildWelcomeSection(),
                    const SizedBox(height: 24),

                    // Statistics Cards
                    if (_dashboardData != null) ...[
                      _buildStatisticsCards(),
                      const SizedBox(height: 24),
                    ],

                    // Charts
                    _buildCharts(),
                    const SizedBox(height: 24),

                    // Quick Actions
                    _buildQuickActions(),
                    const SizedBox(height: 24),

                    // Recent Activities
                    _buildRecentActivities(),
                    const SizedBox(height: 24),

                    // Top Users
                    _buildTopUsers(),
                  ],
                ),
              ),
            ),
          ),
        );
      },
    );
  }

  Widget _buildWelcomeSection() {
    final authProvider = context.watch<AuthProvider>();
    final user = authProvider.currentUser;

    return Card(
      child: Container(
        width: double.infinity,
        padding: const EdgeInsets.all(24),
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: [
              AppTheme.primaryColor,
              AppTheme.primaryColor.withOpacity(0.8),
            ],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          borderRadius: BorderRadius.circular(12),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                CircleAvatar(
                  radius: 30,
                  backgroundColor: Colors.white.withOpacity(0.2),
                  child: const Icon(
                    Icons.admin_panel_settings,
                    size: 30,
                    color: Colors.white,
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Welcome back, ${user?.username ?? 'Admin'}!',
                        style: const TextStyle(
                          fontSize: 24,
                          fontWeight: FontWeight.bold,
                          color: Colors.white,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'Here\'s what\'s happening with your heart disease prediction system',
                        style: TextStyle(
                          fontSize: 16,
                          color: Colors.white.withOpacity(0.9),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            if (_dashboardData != null) ...[
              Row(
                children: [
                  Icon(Icons.schedule, color: Colors.white.withOpacity(0.8)),
                  const SizedBox(width: 8),
                  Text(
                    'Last updated: ${_dashboardData!['last_updated'] ?? 'Now'}',
                    style: TextStyle(
                      color: Colors.white.withOpacity(0.8),
                      fontSize: 14,
                    ),
                  ),
                ],
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildStatisticsCards() {
    return GridView.count(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      crossAxisCount: 2,
      crossAxisSpacing: 16,
      mainAxisSpacing: 16,
      childAspectRatio: 1.3,
      children: [
        _buildAnimatedStatCard(
          'Total Users',
          _totalUsersAnimation,
          Icons.people,
          AppTheme.primaryColor,
        ),
        _buildAnimatedStatCard(
          'Total Predictions',
          _totalPredictionsAnimation,
          Icons.analytics,
          AppTheme.successColor,
        ),
        _buildAnimatedStatCard(
          'High Risk Cases',
          _positivePredictionsAnimation,
          Icons.warning,
          AppTheme.dangerColor,
        ),
        _buildAnimatedStatCard(
          'Low Risk Cases',
          _negativePredictionsAnimation,
          Icons.check_circle,
          AppTheme.successColor,
        ),
      ],
    );
  }

  Widget _buildAnimatedStatCard(
      String title, Animation<double> animation, IconData icon, Color color) {
    return AnimatedBuilder(
      animation: animation,
      builder: (context, child) {
        return Card(
          elevation: 4,
          child: Container(
            padding: const EdgeInsets.all(12), // reduced padding
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: color.withOpacity(0.2)),
            ),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              mainAxisSize: MainAxisSize.min,
              children: [
                Flexible(
                  child: Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: color.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Icon(icon, color: color, size: 28), // smaller icon
                  ),
                ),
                const SizedBox(height: 8),
                Flexible(
                  child: Text(
                    animation.value.toInt().toString(),
                    style: Theme.of(context).textTheme.titleLarge?.copyWith(
                          color: color,
                          fontWeight: FontWeight.bold,
                          fontSize: 20, // smaller font
                        ),
                  ),
                ),
                const SizedBox(height: 2),
                Flexible(
                  child: Text(
                    title,
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                          color: AppTheme.gray600,
                          fontWeight: FontWeight.w500,
                          fontSize: 12, // smaller font
                        ),
                    textAlign: TextAlign.center,
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildCharts() {
    if (_dashboardData == null) {
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

    final data = _dashboardData!;
    final totalPredictions = data['totalPredictions'] ?? 0;
    final highRiskCases = data['positivePredictions'] ?? 0;
    final lowRiskCases = totalPredictions - highRiskCases;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Analytics Overview',
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
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'Prediction Distribution',
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(
                            fontWeight: FontWeight.w600,
                          ),
                    ),
                    Text(
                      'Total: $totalPredictions',
                      style: Theme.of(context).textTheme.bodySmall?.copyWith(
                            color: AppTheme.gray600,
                          ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                SizedBox(
                  height: 200,
                  child: PieChart(
                    PieChartData(
                      sections: [
                        PieChartSectionData(
                          value: highRiskCases.toDouble(),
                          title: 'High Risk\n$highRiskCases',
                          color: AppTheme.dangerColor,
                          radius: 60,
                          titleStyle: const TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                            color: Colors.white,
                          ),
                        ),
                        PieChartSectionData(
                          value: lowRiskCases.toDouble(),
                          title: 'Low Risk\n$lowRiskCases',
                          color: AppTheme.successColor,
                          radius: 60,
                          titleStyle: const TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                            color: Colors.white,
                          ),
                        ),
                      ],
                      centerSpaceRadius: 40,
                      sectionsSpace: 2,
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

  Widget _buildQuickActions() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Quick Actions',
              style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.w600,
                  ),
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                Expanded(
                  child: _buildActionCard(
                    'Manage Users',
                    Icons.people,
                    AppTheme.primaryColor,
                    () => AppRouter.navigateTo(AppRouter.manageUsers),
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: _buildActionCard(
                    'View Reports',
                    Icons.assessment,
                    AppTheme.successColor,
                    () => AppRouter.navigateTo(AppRouter.adminReports),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                Expanded(
                  child: _buildActionCard(
                    'New Prediction',
                    Icons.add_circle,
                    AppTheme.warningColor,
                    () => AppRouter.navigateTo(AppRouter.prediction),
                  ),
                ),
                // Removed Import Dataset action
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildActionCard(
      String title, IconData icon, Color color, VoidCallback onTap) {
    return Card(
      elevation: 2,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            children: [
              Icon(icon, color: color, size: 32),
              const SizedBox(height: 8),
              Text(
                title,
                style: Theme.of(context).textTheme.titleSmall?.copyWith(
                      fontWeight: FontWeight.w600,
                    ),
                textAlign: TextAlign.center,
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildRecentActivities() {
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
                  'Recent Activities',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.w600,
                      ),
                ),
                Text(
                  '${_recentActivities.length} activities',
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                        color: AppTheme.gray600,
                      ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            if (_recentActivities.isEmpty)
              Center(
                child: Padding(
                  padding: const EdgeInsets.all(40),
                  child: Column(
                    children: [
                      const Icon(Icons.history,
                          size: 48, color: AppTheme.gray400),
                      const SizedBox(height: 16),
                      Text(
                        'No recent activities',
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
              ...(_recentActivities.take(5).map((activity) => Padding(
                    padding: const EdgeInsets.only(bottom: 12),
                    child: Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color: (activity['prediction_result'] == 1
                                    ? AppTheme.dangerColor
                                    : AppTheme.successColor)
                                .withOpacity(0.1),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Icon(
                            activity['prediction_result'] == 1
                                ? Icons.warning
                                : Icons.check_circle,
                            color: activity['prediction_result'] == 1
                                ? AppTheme.dangerColor
                                : AppTheme.successColor,
                            size: 16,
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                '${activity['username'] ?? 'Unknown'} made a prediction',
                                style: const TextStyle(
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                              Text(
                                activity['created_at'] ?? 'Unknown time',
                                style: TextStyle(
                                  fontSize: 12,
                                  color: Colors.grey[600],
                                ),
                              ),
                            ],
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: (activity['prediction_result'] == 1
                                    ? AppTheme.dangerColor
                                    : AppTheme.successColor)
                                .withOpacity(0.1),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Text(
                            activity['prediction_result'] == 1
                                ? 'High Risk'
                                : 'Low Risk',
                            style: TextStyle(
                              color: activity['prediction_result'] == 1
                                  ? AppTheme.dangerColor
                                  : AppTheme.successColor,
                              fontWeight: FontWeight.w600,
                              fontSize: 12,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ))),
          ],
        ),
      ),
    );
  }

  Widget _buildTopUsers() {
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
                  'Top Users',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.w600,
                      ),
                ),
                Text(
                  '${_topUsers.length} users',
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                        color: AppTheme.gray600,
                      ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            if (_topUsers.isEmpty)
              Center(
                child: Padding(
                  padding: const EdgeInsets.all(40),
                  child: Column(
                    children: [
                      const Icon(Icons.people_outline,
                          size: 48, color: AppTheme.gray400),
                      const SizedBox(height: 16),
                      Text(
                        'No user data available',
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
              ...(_topUsers.take(5).map((user) => Padding(
                    padding: const EdgeInsets.only(bottom: 12),
                    child: Row(
                      children: [
                        CircleAvatar(
                          radius: 20,
                          backgroundColor:
                              AppTheme.primaryColor.withOpacity(0.1),
                          child: Text(
                            (user['username'] ?? 'U')[0].toUpperCase(),
                            style: const TextStyle(
                              color: AppTheme.primaryColor,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                user['username'] ?? 'Unknown User',
                                style: const TextStyle(
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                              Text(
                                '${user['prediction_count'] ?? 0} predictions',
                                style: TextStyle(
                                  fontSize: 12,
                                  color: Colors.grey[600],
                                ),
                              ),
                            ],
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: AppTheme.successColor.withOpacity(0.1),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Text(
                            '${user['accuracy'] ?? 0}%',
                            style: const TextStyle(
                              color: AppTheme.successColor,
                              fontWeight: FontWeight.w600,
                              fontSize: 12,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ))),
          ],
        ),
      ),
    );
  }
}

// --- AdminDrawer widget for admin navigation ---
class AdminDrawer extends StatelessWidget {
  const AdminDrawer({super.key});

  @override
  Widget build(BuildContext context) {
    final authProvider = context.watch<AuthProvider>();
    final user = authProvider.currentUser;

    return Drawer(
      child: Column(
        children: [
          Expanded(
            child: ListView(
              padding: EdgeInsets.zero,
              children: [
                DrawerHeader(
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: [
                        AppTheme.primaryColor,
                        AppTheme.primaryColor.withOpacity(0.8),
                      ],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      CircleAvatar(
                        radius: 30,
                        backgroundColor: Colors.white.withOpacity(0.2),
                        child: const Icon(
                          Icons.admin_panel_settings,
                          size: 30,
                          color: Colors.white,
                        ),
                      ),
                      const SizedBox(height: 12),
                      Text(
                        user?.username ?? 'Admin',
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      Text(
                        user?.email ?? 'admin@example.com',
                        style: TextStyle(
                          color: Colors.white.withOpacity(0.8),
                          fontSize: 14,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: Colors.white.withOpacity(0.2),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: const Text(
                          'Administrator',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                ListTile(
                  leading: const Icon(Icons.dashboard),
                  title: const Text('Dashboard'),
                  selected: true,
                  onTap: () {
                    Navigator.pop(context);
                    AppRouter.navigateTo(AppRouter.adminDashboard);
                  },
                ),
                ListTile(
                  leading: const Icon(Icons.add_circle),
                  title: const Text('New Prediction'),
                  onTap: () {
                    Navigator.pop(context);
                    AppRouter.navigateTo(AppRouter.prediction);
                  },
                ),
                ListTile(
                  leading: const Icon(Icons.people),
                  title: const Text('Manage Users'),
                  onTap: () {
                    Navigator.pop(context);
                    AppRouter.navigateTo(AppRouter.manageUsers);
                  },
                ),
                ListTile(
                  leading: const Icon(Icons.assessment),
                  title: const Text('Admin Reports'),
                  onTap: () {
                    Navigator.pop(context);
                    AppRouter.navigateTo(AppRouter.adminReports);
                  },
                ),
                const Divider(),
                ListTile(
                  leading: const Icon(Icons.person),
                  title: const Text('Profile'),
                  onTap: () {
                    Navigator.pop(context);
                    AppRouter.navigateTo(AppRouter.profile);
                  },
                ),
              ],
            ),
          ),
          // Logout button always at the bottom
          SafeArea(
            child: ListTile(
              leading: const Icon(Icons.logout),
              title: const Text('Logout'),
              onTap: () async {
                Navigator.pop(context);
                await authProvider.logout();
                AppRouter.navigateToAndClear(AppRouter.login);
              },
            ),
          ),
        ],
      ),
    );
  }
}
