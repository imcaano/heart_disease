import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../routes/app_router.dart';
import '../providers/auth_provider.dart';
import '../theme/app_theme.dart';
import 'package:shared_preferences/shared_preferences.dart';

class AppDrawer extends StatelessWidget {
  const AppDrawer({super.key});

  @override
  Widget build(BuildContext context) {
    final authProvider = context.watch<AuthProvider>();
    final user = authProvider.currentUser;

    return Drawer(
      child: Column(
        children: [
          // Drawer Header
          SafeArea(
            child: Container(
              width: double.infinity,
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [AppTheme.primaryColor, AppTheme.primaryDark],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: const BorderRadius.only(
                  bottomLeft: Radius.circular(24),
                  bottomRight: Radius.circular(24),
                ),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.08),
                    blurRadius: 8,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
              child: LayoutBuilder(
                builder: (context, constraints) {
                  final isSmall = constraints.maxHeight < 180;
                  return Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      CircleAvatar(
                        radius: isSmall ? 22 : 30,
                        backgroundColor: Colors.white.withOpacity(0.2),
                        child: Icon(
                          Icons.person,
                          size: isSmall ? 22 : 30,
                          color: Colors.white,
                        ),
                      ),
                      SizedBox(height: isSmall ? 8 : 14),
                      if (user != null && user.role.toLowerCase() == 'admin')
                        Text(
                          'Welcome to Admin Dashboard',
                          style:
                              Theme.of(context).textTheme.bodyMedium?.copyWith(
                                    color: Colors.amberAccent,
                                    fontWeight: FontWeight.bold,
                                    fontSize: isSmall ? 12 : 16,
                                  ),
                        ),
                      Text(
                        (user != null && user.username.isNotEmpty)
                            ? user.username
                            : 'User',
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: Theme.of(context).textTheme.titleLarge?.copyWith(
                              color: Colors.white,
                              fontWeight: FontWeight.bold,
                              fontSize: isSmall ? 18 : 22,
                            ),
                      ),
                      Text(
                        (user != null && user.email.isNotEmpty)
                            ? user.email
                            : 'user@example.com',
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                              color: Colors.white.withOpacity(0.8),
                              fontSize: isSmall ? 12 : 14,
                            ),
                      ),
                      const SizedBox(height: 6),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 10,
                          vertical: 4,
                        ),
                        decoration: BoxDecoration(
                          color:
                              user != null && user.role.toLowerCase() == 'admin'
                                  ? Colors.amber.withOpacity(0.3)
                                  : Colors.white.withOpacity(0.2),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Text(
                          (user != null && user.role.isNotEmpty)
                              ? user.role.toUpperCase()
                              : 'USER',
                          style:
                              Theme.of(context).textTheme.bodySmall?.copyWith(
                                    color: user != null &&
                                            user.role.toLowerCase() == 'admin'
                                        ? Colors.amber[900]
                                        : Colors.white,
                                    fontWeight: FontWeight.bold,
                                    fontSize: isSmall ? 10 : 12,
                                  ),
                        ),
                      ),
                    ],
                  );
                },
              ),
            ),
          ),

          // Drawer Menu Items
          Expanded(
            child: ListView(
              padding: EdgeInsets.zero,
              children: [
                if (user != null && user.role.toLowerCase() == 'admin') ...[
                  _buildDrawerItem(
                    context,
                    icon: Icons.dashboard,
                    title: 'Admin Dashboard',
                    onTap: () {
                      Navigator.pop(context);
                      AppRouter.navigateTo(AppRouter.adminDashboard);
                    },
                  ),
                  _buildDrawerItem(
                    context,
                    icon: Icons.add_circle,
                    title: 'New Prediction',
                    onTap: () {
                      Navigator.pop(context);
                      AppRouter.navigateTo(AppRouter.prediction);
                    },
                  ),
                  _buildDrawerItem(
                    context,
                    icon: Icons.people,
                    title: 'Manage Users',
                    onTap: () {
                      Navigator.pop(context);
                      AppRouter.navigateTo(AppRouter.manageUsers);
                    },
                  ),
                  _buildDrawerItem(
                    context,
                    icon: Icons.assessment,
                    title: 'Admin Reports',
                    onTap: () {
                      Navigator.pop(context);
                      AppRouter.navigateTo(AppRouter.adminReports);
                    },
                  ),
                  _buildDrawerItem(
                    context,
                    icon: Icons.calendar_today,
                    title: 'Appointments',
                    onTap: () {
                      Navigator.pop(context);
                      AppRouter.navigateTo(AppRouter.adminAppointments);
                    },
                  ),
                  const Divider(),
                ] else ...[
                  _buildDrawerItem(
                    context,
                    icon: Icons.dashboard,
                    title: 'Dashboard',
                    onTap: () {
                      Navigator.pop(context);
                      AppRouter.navigateTo(AppRouter.dashboard);
                    },
                  ),
                  _buildDrawerItem(
                    context,
                    icon: Icons.favorite,
                    title: 'Prediction',
                    onTap: () {
                      Navigator.pop(context);
                      AppRouter.navigateTo(AppRouter.prediction);
                    },
                  ),
                  if (user != null &&
                      (user.role == 'admin' || user.role == 'expert'))
                  _buildDrawerItem(
                    context,
                      icon: Icons.bar_chart,
                    title: 'Reports',
                    onTap: () {
                      Navigator.pop(context);
                      AppRouter.navigateTo(AppRouter.reports);
                    },
                  ),
                  _buildDrawerItem(
                    context,
                    icon: Icons.calendar_today,
                    title: 'Appointments',
                    onTap: () {
                      Navigator.pop(context);
                      AppRouter.navigateTo(AppRouter.appointments);
                    },
                  ),
                  const Divider(),
                ],
                _buildDrawerItem(
                  context,
                  icon: Icons.person,
                  title: 'Profile',
                  onTap: () {
                    Navigator.pop(context);
                    AppRouter.navigateTo(AppRouter.profile);
                  },
                ),
                const Divider(),
                _buildDrawerItem(
                  context,
                  icon: Icons.logout,
                  title: 'Logout',
                  onTap: () async {
                    Navigator.pop(context);
                    final prefs = await SharedPreferences.getInstance();
                    await prefs.remove('auth_token');
                    await authProvider.logout();
                    AppRouter.navigateToAndClear(AppRouter.login);
                  },
                ),
              ],
            ),
          ),

          // App Version
          Padding(
            padding: const EdgeInsets.all(16),
            child: Text(
              'Heart Disease Prediction v1.0',
              style: Theme.of(context).textTheme.bodySmall?.copyWith(
                    color: AppTheme.gray500,
                  ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDrawerItem(
    BuildContext context, {
    required IconData icon,
    required String title,
    required VoidCallback onTap,
  }) {
    return ListTile(
      leading: Icon(
        icon,
        color: AppTheme.primaryColor,
      ),
      title: Text(
        title,
        style: Theme.of(context).textTheme.bodyLarge,
      ),
      onTap: onTap,
      hoverColor: AppTheme.primaryColor.withOpacity(0.1),
    );
  }
}
