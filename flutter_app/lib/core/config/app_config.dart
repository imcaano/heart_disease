class AppConfig {
  // Network Configuration
  static const String wifiIpAddress = '192.168.18.12';
  static const String ethernetIpAddress = '192.168.56.1';

  // API Endpoints
  static const String phpApiBaseUrl = 'http://$wifiIpAddress/heart_disease/api';
  static const String pythonApiBaseUrl = 'http://$wifiIpAddress:5000';

  // Ports
  static const int phpPort = 80; // XAMPP Apache
  static const int pythonPort = 5000; // Flask server
  static const int flutterPort = 8080; // Flutter web

  // URLs
  static const String flutterAppUrl = 'http://$wifiIpAddress:$flutterPort';
  static const String webDashboardUrl = 'http://$wifiIpAddress/heart_disease';

  // Get current IP based on network preference
  static String get currentIpAddress => wifiIpAddress;

  // Get PHP API URL
  static String get phpApiUrl => phpApiBaseUrl;

  // Get Python API URL
  static String get pythonApiUrl => pythonApiBaseUrl;
}
