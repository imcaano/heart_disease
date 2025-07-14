import 'package:flutter/material.dart';
import '../../features/auth/presentation/pages/login_page.dart';
import '../../features/auth/presentation/pages/signup_page.dart';
import '../../features/dashboard/presentation/pages/dashboard_page.dart';
import '../../features/prediction/presentation/pages/prediction_page.dart';
import '../../features/profile/presentation/pages/profile_page.dart';
import '../../features/reports/presentation/pages/reports_page.dart';
import '../../features/admin/presentation/pages/admin_dashboard_page.dart';
import '../../features/admin/presentation/pages/manage_users_page.dart';
import '../../features/admin/presentation/pages/admin_reports_page.dart';
import '../../features/admin/presentation/pages/import_dataset_page.dart';
import '../../features/splash/presentation/pages/splash_page.dart';
import '../../features/appointments/admin_appointments_screen.dart';
import '../../features/appointments/user_appointments_screen.dart';

class AppRouter {
  static const String splash = '/';
  static const String login = '/login';
  static const String signup = '/signup';
  static const String dashboard = '/dashboard';
  static const String prediction = '/prediction';
  static const String profile = '/profile';
  static const String reports = '/reports';
  static const String appointments = '/appointments';
  static const String adminDashboard = '/admin/dashboard';
  static const String manageUsers = '/admin/users';
  static const String adminReports = '/admin/reports';
  static const String adminAppointments = '/admin/appointments';
  static const String importDataset = '/admin/import';

  static final GlobalKey<NavigatorState> navigatorKey =
      GlobalKey<NavigatorState>();

  static Route<dynamic> onGenerateRoute(RouteSettings settings) {
    switch (settings.name) {
      case splash:
        return MaterialPageRoute(
          builder: (_) => const SplashPage(),
          settings: settings,
        );

      case login:
        return MaterialPageRoute(
          builder: (_) => const LoginPage(),
          settings: settings,
        );

      case signup:
        return MaterialPageRoute(
          builder: (_) => const SignupPage(),
          settings: settings,
        );

      case dashboard:
        return MaterialPageRoute(
          builder: (_) => const DashboardPage(),
          settings: settings,
        );

      case prediction:
        return MaterialPageRoute(
          builder: (_) => const PredictionPage(),
          settings: settings,
        );

      case profile:
        return MaterialPageRoute(
          builder: (_) => const ProfilePage(),
          settings: settings,
        );

      case reports:
        return MaterialPageRoute(
          builder: (_) => const ReportsPage(),
          settings: settings,
        );

      case appointments:
        return MaterialPageRoute(
          builder: (_) => const UserAppointmentsScreen(),
          settings: settings,
        );

      case adminDashboard:
        return MaterialPageRoute(
          builder: (_) => const AdminDashboardPage(),
          settings: settings,
        );

      case manageUsers:
        return MaterialPageRoute(
          builder: (_) => const ManageUsersPage(),
          settings: settings,
        );

      case adminReports:
        return MaterialPageRoute(
          builder: (_) => const AdminReportsPage(),
          settings: settings,
        );

      case adminAppointments:
        return MaterialPageRoute(
          builder: (_) => const AdminAppointmentsScreen(),
          settings: settings,
        );

      case importDataset:
        return MaterialPageRoute(
          builder: (_) => const ImportDatasetPage(),
          settings: settings,
        );

      default:
        return MaterialPageRoute(
          builder: (_) => Scaffold(
            body: Center(child: Text('Route ${settings.name} not found')),
          ),
        );
    }
  }

  static String get initial => splash;

  static void navigateTo(String routeName) {
    navigatorKey.currentState?.pushNamed(routeName);
  }

  static void navigateToAndClear(String routeName) {
    navigatorKey.currentState?.pushNamedAndRemoveUntil(
      routeName,
      (route) => false,
    );
  }

  static void goBack() {
    navigatorKey.currentState?.pop();
  }
}
