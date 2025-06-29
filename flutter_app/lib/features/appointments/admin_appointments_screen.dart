import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'appointment_model.dart';
import 'appointment_provider.dart';

class AdminAppointmentsScreen extends StatefulWidget {
  const AdminAppointmentsScreen({Key? key}) : super(key: key);

  @override
  State<AdminAppointmentsScreen> createState() =>
      _AdminAppointmentsScreenState();
}

class _AdminAppointmentsScreenState extends State<AdminAppointmentsScreen> {
  String _selectedStatus = 'all';

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<AppointmentProvider>().loadAllAppointments();
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

  Future<void> _updateAppointmentStatus(
      Appointment appointment, String newStatus) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('Confirm $newStatus'),
        content: Text('Are you sure you want to $newStatus this appointment?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            child: Text(newStatus),
          ),
        ],
      ),
    );

    if (confirmed == true) {
      final success =
          await context.read<AppointmentProvider>().updateAppointmentStatus(
                appointment.id!,
                newStatus,
              );

      if (success) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('Appointment $newStatus successfully'),
              backgroundColor: Colors.green,
            ),
          );
        }
      } else {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(context.read<AppointmentProvider>().error ??
                  'Failed to update appointment'),
              backgroundColor: Colors.red,
            ),
          );
        }
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Manage Appointments'),
        backgroundColor: const Color(0xFF4e73df),
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () {
              context.read<AppointmentProvider>().loadAllAppointments(
                    statusFilter:
                        _selectedStatus == 'all' ? null : _selectedStatus,
                  );
            },
          ),
        ],
      ),
      body: Column(
        children: [
          // Status Filter
          Container(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                const Text(
                  'Filter by status: ',
                  style: TextStyle(fontWeight: FontWeight.bold),
                ),
                const SizedBox(width: 8),
                DropdownButton<String>(
                  value: _selectedStatus,
                  items: [
                    const DropdownMenuItem(value: 'all', child: Text('All')),
                    const DropdownMenuItem(
                        value: 'pending', child: Text('Pending')),
                    const DropdownMenuItem(
                        value: 'approved', child: Text('Approved')),
                    const DropdownMenuItem(
                        value: 'rejected', child: Text('Rejected')),
                    const DropdownMenuItem(
                        value: 'completed', child: Text('Completed')),
                  ],
                  onChanged: (value) {
                    setState(() {
                      _selectedStatus = value!;
                    });
                    context.read<AppointmentProvider>().loadAllAppointments(
                          statusFilter: value == 'all' ? null : value,
                        );
                  },
                ),
              ],
            ),
          ),

          // Appointments List
          Expanded(
            child: Consumer<AppointmentProvider>(
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
                            appointmentProvider.loadAllAppointments(
                              statusFilter: _selectedStatus == 'all'
                                  ? null
                                  : _selectedStatus,
                            );
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
                          'No appointments found',
                          style: TextStyle(
                            fontSize: 18,
                            color: Colors.grey,
                          ),
                        ),
                        const SizedBox(height: 8),
                        const Text(
                          'There are no appointments matching your filter.',
                          textAlign: TextAlign.center,
                          style: TextStyle(color: Colors.grey),
                        ),
                      ],
                    ),
                  );
                }

                return RefreshIndicator(
                  onRefresh: () async {
                    await appointmentProvider.loadAllAppointments(
                      statusFilter:
                          _selectedStatus == 'all' ? null : _selectedStatus,
                    );
                  },
                  child: ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: appointmentProvider.appointments.length,
                    itemBuilder: (context, index) {
                      final appointment =
                          appointmentProvider.appointments[index];
                      return Card(
                        margin: const EdgeInsets.only(bottom: 16),
                        child: ExpansionTile(
                          leading: CircleAvatar(
                            backgroundColor:
                                _getStatusColor(appointment.status),
                            child: Icon(
                              _getStatusIcon(appointment.status),
                              color: Colors.white,
                            ),
                          ),
                          title: Text(
                            appointment.patientName,
                            style: const TextStyle(fontWeight: FontWeight.bold),
                          ),
                          subtitle: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Date: ${_formatDate(appointment.appointmentDate)}',
                                style: const TextStyle(fontSize: 12),
                              ),
                              Text(
                                'Time: ${appointment.appointmentTime}',
                                style: const TextStyle(fontSize: 12),
                              ),
                              Text(
                                'Address: ${appointment.address ?? ''}',
                                style: const TextStyle(fontSize: 12),
                              ),
                              Text(
                                'Comment: ${appointment.reason ?? ''}',
                                style: const TextStyle(fontSize: 12),
                              ),
                            ],
                          ),
                          trailing: Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 8,
                              vertical: 4,
                            ),
                            decoration: BoxDecoration(
                              color: _getStatusColor(appointment.status),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Text(
                              _getStatusText(appointment.status),
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 12,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                          children: [
                            Padding(
                              padding: const EdgeInsets.all(16),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  _buildInfoRow(
                                      'Email', appointment.patientEmail),
                                  _buildInfoRow(
                                      'Phone', appointment.patientPhone),
                                  _buildInfoRow('Address', appointment.address),
                                  _buildInfoRow('Reason', appointment.reason),
                                  if (appointment.adminNotes != null &&
                                      appointment.adminNotes!.isNotEmpty)
                                    _buildInfoRow(
                                        'Admin Notes', appointment.adminNotes!),
                                  _buildInfoRow('Created',
                                      _formatDateTime(appointment.createdAt)),
                                  if (appointment.updatedAt != null)
                                    _buildInfoRow(
                                        'Updated',
                                        _formatDateTime(
                                            appointment.updatedAt!)),

                                  const SizedBox(height: 16),

                                  // Action Buttons
                                  if (appointment.status == 'pending')
                                    Row(
                                      children: [
                                        Expanded(
                                          child: ElevatedButton.icon(
                                            onPressed: () =>
                                                _updateAppointmentStatus(
                                                    appointment, 'approved'),
                                            icon: const Icon(Icons.check),
                                            label: const Text('Approve'),
                                            style: ElevatedButton.styleFrom(
                                              backgroundColor: Colors.green,
                                              foregroundColor: Colors.white,
                                            ),
                                          ),
                                        ),
                                        const SizedBox(width: 8),
                                        Expanded(
                                          child: ElevatedButton.icon(
                                            onPressed: () =>
                                                _updateAppointmentStatus(
                                                    appointment, 'rejected'),
                                            icon: const Icon(Icons.close),
                                            label: const Text('Reject'),
                                            style: ElevatedButton.styleFrom(
                                              backgroundColor: Colors.red,
                                              foregroundColor: Colors.white,
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),

                                  if (appointment.status == 'approved')
                                    ElevatedButton.icon(
                                      onPressed: () => _updateAppointmentStatus(
                                          appointment, 'completed'),
                                      icon: const Icon(Icons.done_all),
                                      label: const Text('Mark as Completed'),
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: Colors.blue,
                                        foregroundColor: Colors.white,
                                      ),
                                    ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      );
                    },
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInfoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 80,
            child: Text(
              '$label:',
              style: const TextStyle(
                fontWeight: FontWeight.bold,
                fontSize: 12,
              ),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(fontSize: 12),
            ),
          ),
        ],
      ),
    );
  }

  IconData _getStatusIcon(String status) {
    switch (status.toLowerCase()) {
      case 'pending':
        return Icons.schedule;
      case 'approved':
        return Icons.check_circle;
      case 'rejected':
        return Icons.cancel;
      case 'completed':
        return Icons.done_all;
      default:
        return Icons.help;
    }
  }

  String _formatDate(DateTime date) {
    return '${date.day}/${date.month}/${date.year}';
  }

  String _formatDateTime(DateTime dateTime) {
    return '${dateTime.day}/${dateTime.month}/${dateTime.year} ${dateTime.hour}:${dateTime.minute.toString().padLeft(2, '0')}';
  }
}
