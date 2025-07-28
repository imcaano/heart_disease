import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/widgets/app_drawer.dart';
import 'appointment_provider.dart';
import '../../../core/theme/app_theme.dart';
import 'appointment_model.dart';
import '../../../core/providers/auth_provider.dart';

class AdminAppointmentsScreen extends StatefulWidget {
  const AdminAppointmentsScreen({super.key});

  @override
  State<AdminAppointmentsScreen> createState() =>
      _AdminAppointmentsScreenState();
}

class _AdminAppointmentsScreenState extends State<AdminAppointmentsScreen> {
  String _statusFilter = 'all';
  bool _firstLoad = true;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _loadAppointments());
  }

  Future<void> _loadAppointments() async {
    await context
        .read<AppointmentProvider>()
        .loadAllAppointments(statusFilter: _statusFilter);
    setState(() {
      _firstLoad = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(
      builder: (context, constraints) {
        return Scaffold(
          appBar: AppBar(
            title: const Text('Manage Appointments',
                style: TextStyle(color: Colors.white)),
            backgroundColor: AppTheme.primaryColor,
            foregroundColor: Colors.white,
            elevation: 0,
            actions: [
              IconButton(
                icon: const Icon(Icons.refresh),
                onPressed: _loadAppointments,
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
          body: Consumer<AppointmentProvider>(
            builder: (context, provider, child) {
              final appointments = provider.appointments;
              final stats = provider.stats;
              final error = provider.error;

              return RefreshIndicator(
                onRefresh: _loadAppointments,
                child: SingleChildScrollView(
                  padding: const EdgeInsets.all(16),
                  child: ConstrainedBox(
                    constraints: BoxConstraints(
                      minHeight: constraints.maxHeight - 32,
                    ),
                    child: IntrinsicHeight(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          if (stats.isNotEmpty) ...[
                            SizedBox(
                              height: constraints.maxWidth < 600 ? 320 : 180,
                              child: _buildStatisticsCards(stats),
                            ),
                            const SizedBox(height: 24),
                          ],
                          _buildFilterSection(provider),
                          const SizedBox(height: 16),
                          if (provider.isLoading && _firstLoad)
                            const Center(child: CircularProgressIndicator()),
                          if (error != null && error.isNotEmpty)
                            _buildErrorState(error),
                          if (!provider.isLoading &&
                              appointments.isEmpty &&
                              (error == null || error.isEmpty))
                            _buildEmptyState(),
                          if (!provider.isLoading && appointments.isNotEmpty)
                            _buildAppointmentsList(
                                appointments, provider, constraints),
                        ],
                      ),
                    ),
                  ),
                ),
              );
            },
          ),
        );
      },
    );
  }

  Widget _buildStatisticsCards(Map<String, dynamic> stats) {
    return GridView.count(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      crossAxisCount: 2,
      crossAxisSpacing: 16,
      mainAxisSpacing: 16,
      childAspectRatio: 1.0, // Reduce aspect ratio to avoid overflow
      children: [
        _buildStatCard('Pending', stats['pending']?.toString() ?? '0',
            Icons.hourglass_top, Colors.orange),
        _buildStatCard('Approved', stats['approved']?.toString() ?? '0',
            Icons.check, Colors.green),
        _buildStatCard('Rejected', stats['rejected']?.toString() ?? '0',
            Icons.close, Colors.red),
        _buildStatCard('Completed', stats['completed']?.toString() ?? '0',
            Icons.check_circle, AppTheme.primaryColor),
      ],
    );
  }

  Widget _buildStatCard(
      String label, String value, IconData icon, Color color) {
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15)),
      child: Container(
        height: 110, // Increased height to prevent overflow
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(15),
          border: Border.all(color: color.withOpacity(0.2)),
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: color.withOpacity(0.1),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(icon, color: color, size: 28), // Reduced icon size
            ),
            const SizedBox(height: 8),
            FittedBox(
              fit: BoxFit.scaleDown,
              child: Text(value,
                  style: TextStyle(
                      fontSize: 24,
                      fontWeight: FontWeight.bold,
                      color: color)), // Reduced font size
            ),
            const SizedBox(height: 2),
            FittedBox(
              fit: BoxFit.scaleDown,
              child: Text(label,
                  style: TextStyle(
                      color: AppTheme.gray600,
                      fontWeight: FontWeight.w500,
                      fontSize: 13)),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFilterSection(AppointmentProvider provider) {
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15)),
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: SingleChildScrollView(
          scrollDirection: Axis.horizontal,
          child: Row(
            children: [
              const Text('Filter by status: ',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
              const SizedBox(width: 16),
              SizedBox(
                width: 220,
                child: DropdownButtonFormField<String>(
                  value: _statusFilter,
                  isExpanded: true,
                  decoration: InputDecoration(
                    border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8)),
                    contentPadding:
                        const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  ),
                  items: const [
                    DropdownMenuItem(
                        value: 'all', child: Text('All Appointments')),
                    DropdownMenuItem(value: 'pending', child: Text('Pending')),
                    DropdownMenuItem(
                        value: 'approved', child: Text('Approved')),
                    DropdownMenuItem(
                        value: 'rejected', child: Text('Rejected')),
                    DropdownMenuItem(
                        value: 'completed', child: Text('Completed')),
                  ],
                  onChanged: (value) {
                    if (value != null) {
                      setState(() {
                        _statusFilter = value;
                        _firstLoad = true;
                      });
                      provider.loadAllAppointments(statusFilter: value);
                    }
                  },
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildEmptyState() {
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15)),
      child: Padding(
        padding: const EdgeInsets.all(40),
        child: Column(
          children: [
            Icon(Icons.calendar_today_outlined,
                size: 64, color: Colors.grey.shade300),
            const SizedBox(height: 16),
            Text('No appointments found',
                style: TextStyle(
                    fontSize: 18,
                    color: Colors.grey.shade600,
                    fontWeight: FontWeight.w600)),
            const SizedBox(height: 8),
            Text('There are no appointments matching your current filter.',
                style: TextStyle(color: Colors.grey.shade500),
                textAlign: TextAlign.center),
          ],
        ),
      ),
    );
  }

  Widget _buildErrorState(String error) {
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15)),
      child: Padding(
        padding: const EdgeInsets.all(40),
        child: Column(
          children: [
            Icon(Icons.error_outline, size: 64, color: Colors.red.shade300),
            const SizedBox(height: 16),
            Text('Error',
                style: TextStyle(
                    fontSize: 18,
                    color: Colors.red.shade600,
                    fontWeight: FontWeight.w600)),
            const SizedBox(height: 8),
            Text(error,
                style: const TextStyle(color: Colors.red),
                textAlign: TextAlign.center),
          ],
        ),
      ),
    );
  }

  Widget _buildAppointmentsList(List<Appointment> appointments,
      AppointmentProvider provider, BoxConstraints constraints) {
    return ListView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      itemCount: appointments.length,
      itemBuilder: (context, index) {
        final appointment = appointments[index];
        return _buildAppointmentCard(appointment, provider, constraints);
      },
    );
  }

  Widget _buildAppointmentCard(Appointment appointment,
      AppointmentProvider provider, BoxConstraints constraints) {
    final isHighRisk = appointment.predictionResult == 1;
    return Card(
      elevation: 2,
      margin: const EdgeInsets.only(bottom: 16),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15)),
      child: Container(
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(15),
          border: Border.all(color: AppTheme.primaryColor.withOpacity(0.1)),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Wrap(
              alignment: WrapAlignment.spaceBetween,
              runSpacing: 8,
              children: [
                SizedBox(
                  width: constraints.maxWidth < 600
                      ? double.infinity
                      : constraints.maxWidth * 0.6,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        '${appointment.patientName} (${appointment.patientEmail})',
                        style: const TextStyle(
                            fontWeight: FontWeight.bold, fontSize: 16),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'Date: ${appointment.appointmentDate} • Time: ${appointment.appointmentTime}',
                        style: TextStyle(color: AppTheme.gray600, fontSize: 14),
                      ),
                    ],
                  ),
                ),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    _buildStatusBadge(appointment.status),
                    if (isHighRisk) ...[
                      const SizedBox(height: 4),
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 8, vertical: 2),
                        decoration: BoxDecoration(
                            color: Colors.red.withOpacity(0.1),
                            borderRadius: BorderRadius.circular(12)),
                        child: Text('High Risk',
                            style: TextStyle(
                                color: Colors.red,
                                fontSize: 10,
                                fontWeight: FontWeight.bold)),
                      ),
                    ],
                  ],
                ),
              ],
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('Phone: ${appointment.patientPhone}',
                          style:
                              TextStyle(color: AppTheme.gray600, fontSize: 14)),
                      const SizedBox(height: 4),
                      Text('Address: ${appointment.address}',
                          style:
                              TextStyle(color: AppTheme.gray600, fontSize: 14),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Text('Reason: ${appointment.reason}',
                style: TextStyle(color: AppTheme.gray600, fontSize: 14),
                maxLines: 2,
                overflow: TextOverflow.ellipsis),
            if (appointment.adminNotes != null &&
                appointment.adminNotes!.isNotEmpty) ...[
              const SizedBox(height: 8),
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                    color: Colors.blue.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(8)),
                child: Text('Admin Notes: ${appointment.adminNotes}',
                    style: const TextStyle(color: Colors.blue, fontSize: 12)),
              ),
            ],
            const SizedBox(height: 16),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [
                if (appointment.status == 'pending') ...[
                  SizedBox(
                    width: constraints.maxWidth < 600 ? double.infinity : 160,
                    child: ElevatedButton.icon(
                      onPressed: () =>
                          _updateStatus(appointment.id!, 'approved', provider),
                      icon: const Icon(Icons.check, size: 16),
                      label: const Text('Approve'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.green,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(8)),
                        minimumSize: const Size(0, 40),
                        padding: const EdgeInsets.symmetric(horizontal: 8),
                      ),
                    ),
                  ),
                  SizedBox(
                    width: constraints.maxWidth < 600 ? double.infinity : 160,
                    child: ElevatedButton.icon(
                      onPressed: () =>
                          _updateStatus(appointment.id!, 'rejected', provider),
                      icon: const Icon(Icons.close, size: 16),
                      label: const Text('Reject'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.red,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(8)),
                        minimumSize: const Size(0, 40),
                        padding: const EdgeInsets.symmetric(horizontal: 8),
                      ),
                    ),
                  ),
                ] else if (appointment.status == 'approved') ...[
                  SizedBox(
                    width: constraints.maxWidth < 600 ? double.infinity : 200,
                    child: ElevatedButton.icon(
                      onPressed: () =>
                          _updateStatus(appointment.id!, 'completed', provider),
                      icon: const Icon(Icons.check_circle, size: 16),
                      label: const Text('Mark Completed'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppTheme.primaryColor,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(8)),
                        minimumSize: const Size(0, 40),
                        padding: const EdgeInsets.symmetric(horizontal: 8),
                      ),
                    ),
                  ),
                ],
                SizedBox(
                  width: constraints.maxWidth < 600 ? double.infinity : 200,
                  child: OutlinedButton.icon(
                    onPressed: () =>
                        _showNotesDialog(context, appointment, provider),
                    icon: const Icon(Icons.edit_note, size: 16),
                    label: const Text('Add/Edit Notes'),
                    style: OutlinedButton.styleFrom(
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(8)),
                      minimumSize: const Size(0, 40),
                      padding: const EdgeInsets.symmetric(horizontal: 8),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatusBadge(String status) {
    Color color;
    IconData icon;
    switch (status) {
      case 'pending':
        color = Colors.orange;
        icon = Icons.hourglass_top;
        break;
      case 'approved':
        color = Colors.green;
        icon = Icons.check;
        break;
      case 'rejected':
        color = Colors.red;
        icon = Icons.close;
        break;
      case 'completed':
        color = AppTheme.primaryColor;
        icon = Icons.check_circle;
        break;
      default:
        color = Colors.grey;
        icon = Icons.help;
    }
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
          color: color.withOpacity(0.1),
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: color.withOpacity(0.3))),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, color: color, size: 12),
          const SizedBox(width: 4),
          Text(status.toUpperCase(),
              style: TextStyle(
                  color: color, fontWeight: FontWeight.bold, fontSize: 10)),
        ],
      ),
    );
  }

  Future<void> _updateStatus(
      int appointmentId, String status, AppointmentProvider provider) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('Confirm Action'),
        content: Text('Are you sure you want to $status this appointment?'),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(context, false),
              child: const Text('Cancel')),
          ElevatedButton(
              onPressed: () => Navigator.pop(context, true),
              child: const Text('Confirm')),
        ],
      ),
    );
    if (confirmed == true) {
      final success = await provider.updateStatus(appointmentId, status);
      if (success && mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
            content: Text('Appointment status updated to $status'),
            backgroundColor: AppTheme.successColor));
        _loadAppointments();
      } else if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
            content:
                Text(provider.error ?? 'Failed to update appointment status'),
            backgroundColor: AppTheme.dangerColor));
      }
    }
  }

  void _showNotesDialog(BuildContext context, Appointment appointment,
      AppointmentProvider provider) {
    final controller =
        TextEditingController(text: appointment.adminNotes ?? '');
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Admin Notes'),
        content: TextField(
          controller: controller,
          maxLines: 4,
          decoration: const InputDecoration(
              hintText: 'Enter notes...', border: OutlineInputBorder()),
        ),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Cancel')),
          ElevatedButton(
            onPressed: () async {
              final success = await provider.updateAdminNotes(
                  appointment.id!, controller.text);
              Navigator.pop(context);
              if (success && mounted) {
                ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
                    content: Text('Notes updated successfully'),
                    backgroundColor: AppTheme.successColor));
                _loadAppointments();
              } else if (mounted) {
                ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                    content: Text(provider.error ?? 'Failed to update notes'),
                    backgroundColor: AppTheme.dangerColor));
              }
            },
            child: const Text('Save'),
          ),
        ],
      ),
    );
  }
}
