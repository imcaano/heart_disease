import 'dart:convert';
import 'package:http/http.dart' as http;
import 'appointment_model.dart';
import 'package:shared_preferences/shared_preferences.dart';

class AppointmentService {
  static const String baseUrl = 'http://192.168.18.12/heart_disease/api';

  // Book a new appointment
  static Future<Map<String, dynamic>> bookAppointment(
      Appointment appointment) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/book_appointment.php'),
        headers: {
          'Content-Type': 'application/json',
        },
        body: jsonEncode(appointment.toJson()),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return data;
      } else {
        throw Exception('Failed to book appointment: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Error booking appointment: $e');
    }
  }

  // Get user's appointments
  static Future<List<Appointment>> getUserAppointments([int? userId]) async {
    try {
      String url = '$baseUrl/get_appointment_details.php';
      if (userId != null) {
        url += '?user_id=$userId';
      }
      final response = await http.get(
        Uri.parse(url),
        headers: {
          'Content-Type': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true) {
          final List<dynamic> appointments = data['appointments'] ?? [];
          return appointments
              .map((json) => Appointment.fromJson(json))
              .toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to fetch appointments');
        }
      } else {
        throw Exception('Failed to fetch appointments: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Error fetching appointments: $e');
    }
  }

  // Get appointment details
  static Future<Appointment> getAppointmentDetails(int appointmentId) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/get_appointment_details.php'),
        headers: {
          'Content-Type': 'application/json',
        },
        body: jsonEncode({'appointment_id': appointmentId}),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success']) {
          return Appointment.fromJson(data['appointment']);
        } else {
          throw Exception(
              data['message'] ?? 'Failed to get appointment details');
        }
      } else {
        throw Exception(
            'Failed to get appointment details: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Error getting appointment details: $e');
    }
  }

  // Update appointment status (admin only)
  static Future<Map<String, dynamic>> updateAppointmentStatus(
      int appointmentId, String status,
      {String? adminNotes}) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/update_appointment_status.php'),
        headers: {
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          'appointment_id': appointmentId,
          'status': status,
          'admin_notes': adminNotes,
        }),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return data;
      } else {
        throw Exception(
            'Failed to update appointment status: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Error updating appointment status: $e');
    }
  }

  // Get all appointments (admin only)
  static Future<List<Appointment>> getAllAppointments(
      {String? statusFilter}) async {
    try {
      String url = '$baseUrl/admin_appointments_stats.php';
      if (statusFilter != null && statusFilter != 'all') {
        url += '?status=$statusFilter';
      }

      final response = await http.get(
        Uri.parse(url),
        headers: {
          'Content-Type': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true) {
          final List<dynamic> appointments = data['appointments'] ?? [];
          return appointments
              .map((json) => Appointment.fromJson(json))
              .toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to fetch appointments');
        }
      } else {
        throw Exception('Failed to fetch appointments: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Error fetching appointments: $e');
    }
  }

  // Get admin appointment statistics
  static Future<Map<String, dynamic>> getAdminAppointmentStats() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/admin_appointments_stats.php'),
        headers: {
          'Content-Type': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true) {
          return data['stats'] ?? {};
        } else {
          throw Exception(data['message'] ?? 'Failed to fetch statistics');
        }
      } else {
        throw Exception('Failed to fetch statistics: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Error fetching statistics: $e');
    }
  }
}
