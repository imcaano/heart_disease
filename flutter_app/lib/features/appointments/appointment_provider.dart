import 'package:flutter/foundation.dart';
import 'appointment_model.dart';
import 'appointment_service.dart';
import '../../core/providers/auth_provider.dart';

class AppointmentProvider with ChangeNotifier {
  List<Appointment> _appointments = [];
  bool _isLoading = false;
  String? _error;
  Map<String, dynamic> _stats = {};

  List<Appointment> get appointments => _appointments;
  bool get isLoading => _isLoading;
  String? get error => _error;
  Map<String, dynamic> get stats => _stats;

  // Book a new appointment
  Future<bool> bookAppointment(Appointment appointment) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final result = await AppointmentService.bookAppointment(appointment);

      if (result['success']) {
        // Add the new appointment to the list
        _appointments.add(appointment);
        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _error = result['message'] ?? 'Failed to book appointment';
        _isLoading = false;
        notifyListeners();
        return false;
      }
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  // Load user appointments
  Future<void> loadUserAppointments(int userId) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final appointments = await AppointmentService.getUserAppointments(userId);
      _appointments = appointments;
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
    }
  }

  // Load all appointments (admin)
  Future<void> loadAllAppointments({String? statusFilter}) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final appointments = await AppointmentService.getAllAppointments(
        statusFilter: statusFilter,
      );
      _appointments = appointments;
      
      // Also load statistics if no status filter or filter is 'all'
      if (statusFilter == null || statusFilter == 'all') {
        await loadAdminStats();
      }
      
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
    }
  }

  // Load admin statistics
  Future<void> loadAdminStats() async {
    try {
      final stats = await AppointmentService.getAdminAppointmentStats();
      _stats = stats;
      notifyListeners();
    } catch (e) {
      _error = e.toString();
      notifyListeners();
    }
  }

  // Update appointment status (admin)
  Future<bool> updateAppointmentStatus(int appointmentId, String status,
      {String? adminNotes}) async {
    try {
      final result = await AppointmentService.updateAppointmentStatus(
        appointmentId,
        status,
        adminNotes: adminNotes,
      );

      if (result['success']) {
        // Update the appointment in the list
        final index =
            _appointments.indexWhere((app) => app.id == appointmentId);
        if (index != -1) {
          _appointments[index] = _appointments[index].copyWith(
            status: status,
            adminNotes: adminNotes,
            updatedAt: DateTime.now(),
          );
          notifyListeners();
        }
        return true;
      } else {
        _error = result['message'] ?? 'Failed to update appointment status';
        notifyListeners();
        return false;
      }
    } catch (e) {
      _error = e.toString();
      notifyListeners();
      return false;
    }
  }

  // Add these methods for admin screen compatibility
  Future<bool> updateStatus(int appointmentId, String status) async {
    return await updateAppointmentStatus(appointmentId, status);
  }

  Future<bool> updateAdminNotes(int appointmentId, String adminNotes) async {
    // Find the current status of the appointment
    final appointment = getAppointmentById(appointmentId);
    final currentStatus = appointment?.status ?? 'pending';
    return await updateAppointmentStatus(appointmentId, currentStatus,
        adminNotes: adminNotes);
  }

  // Get appointment by ID
  Appointment? getAppointmentById(int id) {
    try {
      return _appointments.firstWhere((appointment) => appointment.id == id);
    } catch (e) {
      return null;
    }
  }

  // Get appointments by status
  List<Appointment> getAppointmentsByStatus(String status) {
    return _appointments
        .where((appointment) => appointment.status == status)
        .toList();
  }

  // Clear error
  void clearError() {
    _error = null;
    notifyListeners();
  }

  // Clear appointments
  void clearAppointments() {
    _appointments.clear();
    notifyListeners();
  }
}
