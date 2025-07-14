import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'appointment_provider.dart';
import '../../core/providers/auth_provider.dart';
import '../../core/theme/app_theme.dart';
import '../../core/widgets/app_drawer.dart';

class UserAppointmentsScreen extends StatefulWidget {
  const UserAppointmentsScreen({super.key});

  @override
  State<UserAppointmentsScreen> createState() => _UserAppointmentsScreenState();
}

class _UserAppointmentsScreenState extends State<UserAppointmentsScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final userId = context.read<AuthProvider>().currentUser!.id;
      context.read<AppointmentProvider>().loadUserAppointments(userId);
    });
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'pending':
        return Colors.orange;
      case 'approved':
        return Colors.green;
      case 'rejected':
        return Colors.red;
      case 'completed':
        return Colors.blue;
      default:
        return Colors.grey;
    }
  }

  String _getStatusText(String status) {
    switch (status.toLowerCase()) {
      case 'pending':
        return 'Pending';
      case 'approved':
        return 'Approved';
      case 'rejected':
        return 'Rejected';
      case 'completed':
        return 'Completed';
      default:
        return status;
    }
  }

  IconData _getStatusIcon(String status) {
    switch (status.toLowerCase()) {
      case 'pending':
        return Icons.hourglass_top;
      case 'approved':
        return Icons.check_circle;
      case 'rejected':
        return Icons.cancel;
      case 'completed':
        return Icons.check;
      default:
        return Icons.info;
    }
  }

  String _formatDate(DateTime date) {
    return '${_weekday(date.weekday)}, ${_month(date.month)} ${date.day}, ${date.year}';
  }

  String _formatTime(String time) {
    final parts = time.split(':');
    final hour = int.parse(parts[0]);
    final minute = int.parse(parts[1]);
    final ampm = hour >= 12 ? 'PM' : 'AM';
    final hour12 = hour > 12 ? hour - 12 : hour;
    return '${hour12.toString().padLeft(2, '0')}:${minute.toString().padLeft(2, '0')} $ampm';
  }

  String _formatDateTime(DateTime dateTime) {
    return '${_month(dateTime.month)} ${dateTime.day}, ${dateTime.year} ${_formatTime('${dateTime.hour}:${dateTime.minute}:00')}';
  }

  String _weekday(int weekday) {
    const days = [
      'Monday',
      'Tuesday',
      'Wednesday',
      'Thursday',
      'Friday',
      'Saturday',
      'Sunday'
    ];
    return days[weekday - 1];
  }

  String _month(int month) {
    const months = [
      'January',
      'February',
      'March',
      'April',
      'May',
      'June',
      'July',
      'August',
      'September',
      'October',
      'November',
      'December'
    ];
    return months[month - 1];
  }

  Widget _infoItem(String label, String value) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.grey.shade100,
        borderRadius: BorderRadius.circular(10),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            label,
            style: const TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.bold,
              color: AppTheme.gray600,
            ),
          ),
          const SizedBox(height: 2),
          Text(
            value,
            style: const TextStyle(
              fontSize: 14,
              color: AppTheme.gray800,
            ),
          ),
        ],
      ),
    );
  }

  Widget _statusAlert(IconData icon, String message, Color color) {
    return Container(
      margin: const EdgeInsets.only(top: 12),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: color.withOpacity(0.1),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Row(
        children: [
          Icon(icon, color: color),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              message,
              style: TextStyle(color: color, fontWeight: FontWeight.w500),
            ),
          ),
        ],
      ),
    );
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
                  child: Text('Go to Login'),
                ),
              ],
            ),
          );
        }

        return Scaffold(
          appBar: AppBar(
            title: const Text('My Appointments'),
            backgroundColor: AppTheme.primaryColor,
            foregroundColor: Colors.white,
            actions: [
              IconButton(
                icon: const Icon(Icons.refresh),
                onPressed: () {
                  final userId = context.read<AuthProvider>().currentUser!.id;
                  context.read<AppointmentProvider>().loadUserAppointments(userId);
                },
              ),
            ],
          ),
          drawer: const AppDrawer(),
          body: Consumer<AppointmentProvider>(
            builder: (context, appointmentProvider, child) {
              if (appointmentProvider.isLoading) {
                return const Center(
                  child: CircularProgressIndicator(),
                );
              }

              if (appointmentProvider.error != null) {
                return Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        Icons.error_outline,
                        size: 64,
                        color: Colors.red.shade300,
                      ),
                      const SizedBox(height: 16),
                      Text(
                        'Error loading appointments',
                        style: TextStyle(
                          fontSize: 18,
                          color: Colors.red.shade600,
                        ),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        appointmentProvider.error!,
                        textAlign: TextAlign.center,
                        style: const TextStyle(color: Colors.grey),
                      ),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: () {
                          appointmentProvider.clearError();
                          final userId =
                              context.read<AuthProvider>().currentUser!.id;
                          appointmentProvider.loadUserAppointments(userId);
                        },
                        child: const Text('Retry'),
                      ),
                    ],
                  ),
                );
              }

              if (appointmentProvider.appointments.isEmpty) {
                return Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        Icons.calendar_today_outlined,
                        size: 64,
                        color: Colors.grey.shade300,
                      ),
                      const SizedBox(height: 16),
                      const Text(
                        'No Appointments Yet',
                        style: TextStyle(
                          fontSize: 18,
                          color: Colors.grey,
                        ),
                      ),
                      const SizedBox(height: 8),
                      const Text(
                        'You haven\'t booked any consultations yet. Start by making a heart disease prediction to see if you need a consultation.',
                        textAlign: TextAlign.center,
                        style: TextStyle(color: Colors.grey),
                      ),
                      const SizedBox(height: 16),
                      ElevatedButton.icon(
                        onPressed: () {
                          Navigator.pushNamed(context, '/prediction');
                        },
                        icon: const Icon(Icons.favorite),
                        label: const Text('Make Prediction'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppTheme.primaryColor,
                        ),
                      ),
                    ],
                  ),
                );
              }

              return ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: appointmentProvider.appointments.length,
                  itemBuilder: (context, index) {
                    final appointment = appointmentProvider.appointments[index];
                  final isHighRisk = appointment.predictionResult == 1;
                    return Card(
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(20),
                    ),
                    elevation: 8,
                    margin: const EdgeInsets.only(bottom: 24),
                    child: Padding(
                      padding: const EdgeInsets.all(20),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Header
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    children: [
                                      Text(
                                        _formatDate(
                                            appointment.appointmentDate),
                                        style: const TextStyle(
                                          fontSize: 18,
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                      if (isHighRisk)
                                        Container(
                                          margin:
                                              const EdgeInsets.only(left: 10),
                                          padding: const EdgeInsets.symmetric(
                                              horizontal: 8, vertical: 2),
                                          decoration: BoxDecoration(
                                            gradient: LinearGradient(colors: [
                                              Colors.red,
                                              Colors.deepOrange,
                                            ]),
                                            borderRadius:
                                                BorderRadius.circular(12),
                                          ),
                                          child: const Text(
                                            'High Risk',
                                            style: TextStyle(
                        color: Colors.white,
                                              fontSize: 10,
                                              fontWeight: FontWeight.bold,
                                              letterSpacing: 1,
                                            ),
                                  ),
                                ),
                                ],
                            ),
                              const SizedBox(height: 4),
                              Row(
                      children: [
                                  const Icon(Icons.access_time,
                                          size: 16,
                                          color: AppTheme.primaryColor),
                                  const SizedBox(width: 4),
                        Text(
                                        _formatTime(
                                            appointment.appointmentTime),
                                    style: const TextStyle(
                                      fontSize: 15,
                                      color: AppTheme.primaryColor,
                                      fontWeight: FontWeight.w500,
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 12, vertical: 6),
                            decoration: BoxDecoration(
                              color: _getStatusColor(appointment.status)
                                  .withOpacity(0.1),
                              borderRadius: BorderRadius.circular(25),
                            ),
                            child: Row(
                              children: [
                                Icon(_getStatusIcon(appointment.status),
                                    size: 16,
                                        color: _getStatusColor(
                                            appointment.status)),
                                const SizedBox(width: 4),
                        Text(
                                  _getStatusText(appointment.status),
                                  style: TextStyle(
                                        color:
                                            _getStatusColor(appointment.status),
                                    fontWeight: FontWeight.bold,
                                    fontSize: 12,
                                  ),
                        ),
                      ],
                    ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      // Info grid
                      Wrap(
                        spacing: 16,
                        runSpacing: 8,
                        children: [
                          _infoItem('Username', appointment.patientName),
                          _infoItem('Email', appointment.patientEmail),
                          _infoItem('Phone', appointment.patientPhone),
                          _infoItem('Booked On',
                              _formatDateTime(appointment.createdAt)),
                        ],
                      ),
                      const SizedBox(height: 12),
                      _infoItem('Address', appointment.address ?? ''),
                      const SizedBox(height: 12),
                      // Reason
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: Colors.grey.shade100,
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Row(
                          children: const [
                            Icon(Icons.medical_services,
                                color: AppTheme.primaryColor),
                            SizedBox(width: 8),
                            Text(
                              'Reason for Consultation',
                              style: TextStyle(fontWeight: FontWeight.bold),
                            ),
                          ],
                        ),
                      ),
                      Padding(
                            padding: const EdgeInsets.only(
                                left: 36, top: 4, bottom: 8),
                        child: Text(
                          appointment.reason ?? '',
                          style: const TextStyle(color: AppTheme.gray600),
                        ),
                      ),
                      // Admin notes
                      if (appointment.adminNotes != null &&
                          appointment.adminNotes!.isNotEmpty)
                        Container(
                          margin: const EdgeInsets.only(top: 8),
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            gradient: LinearGradient(colors: [
                              Colors.blue.shade50,
                                  Colors.blue.shade100,
                            ]),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Row(
                            children: const [
                                  Icon(Icons.comment,
                                      color: AppTheme.primaryColor),
                              SizedBox(width: 8),
                              Text(
                                'Admin Notes',
                                    style:
                                        TextStyle(fontWeight: FontWeight.bold),
                              ),
                            ],
                          ),
                        ),
                      if (appointment.adminNotes != null &&
                          appointment.adminNotes!.isNotEmpty)
                        Padding(
                          padding: const EdgeInsets.only(
                              left: 36, top: 4, bottom: 8),
                          child: Text(
                            appointment.adminNotes!,
                            style: const TextStyle(color: AppTheme.gray800),
                          ),
                        ),
                      // Status-specific alert
                      if (appointment.status == 'pending')
                        _statusAlert(
                          Icons.info,
                          'Your appointment is under review. We\'ll notify you once it\'s approved or if any changes are needed.',
                          Colors.blue,
                        ),
                      if (appointment.status == 'approved')
                        _statusAlert(
                          Icons.check_circle,
                          'Your appointment has been approved! Please arrive 10 minutes before your scheduled time. You will also receive a confirmation email.',
                          Colors.green,
                        ),
                      if (appointment.status == 'rejected')
                        _statusAlert(
                          Icons.warning,
                          'Your appointment was not approved. Please contact us for more information or book a new appointment.',
                          Colors.orange,
                        ),
                      if (appointment.status == 'completed')
                        _statusAlert(
                          Icons.check,
                          'This appointment has been completed. Thank you for choosing our services!',
                          Colors.green,
                        ),
                    ],
                  ),
                  ),
                );
              },
          );
        },
      ),
    );
      },
    );
  }
}
