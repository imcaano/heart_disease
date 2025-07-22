import 'package:flutter/material.dart';
import '../../../../core/models/prediction.dart';
import '../../../../core/theme/app_theme.dart';
import '../../../appointments/book_appointment_screen.dart';
import 'package:provider/provider.dart';
import '../../../../core/providers/auth_provider.dart';
import '../../../../core/routes/app_router.dart';

class PredictionResultCard extends StatelessWidget {
  final Prediction prediction;

  const PredictionResultCard({super.key, required this.prediction});

  @override
  Widget build(BuildContext context) {
    final isHighRisk = prediction.prediction == 1;
    final riskColor = isHighRisk ? AppTheme.dangerColor : AppTheme.successColor;
    final riskText = isHighRisk ? 'Positive' : 'Negative';
    final riskIcon = isHighRisk ? Icons.warning : Icons.check_circle;

    return Card(
      elevation: 4,
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: riskColor.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Icon(riskIcon, color: riskColor, size: 28),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Prediction Result',
                        style: Theme.of(context).textTheme.titleLarge?.copyWith(
                              fontWeight: FontWeight.bold,
                            ),
                      ),
                      Text(
                        'Analysis completed',
                        style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                              color: AppTheme.gray600,
                            ),
                      ),
                    ],
                  ),
                ),
              ],
            ),

            const SizedBox(height: 20),

            // Risk Level
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: riskColor.withOpacity(0.1),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: riskColor.withOpacity(0.3)),
              ),
              child: Column(
                children: [
                  Text(
                    riskText,
                    style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                          color: riskColor,
                          fontWeight: FontWeight.bold,
                        ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    isHighRisk
                        ? 'Heart disease risk detected'
                        : 'No significant heart disease risk',
                    style: Theme.of(
                      context,
                    ).textTheme.bodyMedium?.copyWith(color: riskColor),
                    textAlign: TextAlign.center,
                  ),
                ],
              ),
            ),

            const SizedBox(height: 20),

            // Confidence Score
            Row(
              children: [
                Expanded(
                  child: _buildInfoCard(
                    context,
                    'Confidence',
                    '${((prediction.probability ?? 0.0) * 100).toStringAsFixed(1)}%',
                    Icons.analytics,
                    AppTheme.primaryColor,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: _buildInfoCard(
                    context,
                    'Probability',
                    '${((prediction.probability ?? 0.0) * 100).toStringAsFixed(1)}%',
                    Icons.trending_up,
                    AppTheme.warningColor,
                  ),
                ),
              ],
            ),

            const SizedBox(height: 20),

            // Timestamp
            Row(
              children: [
                const Icon(Icons.schedule, size: 16, color: AppTheme.gray500),
                const SizedBox(width: 8),
                Text(
                  'Predicted on ${_formatDate(prediction.createdAt ?? DateTime.now())}',
                  style: Theme.of(
                    context,
                  ).textTheme.bodySmall?.copyWith(color: AppTheme.gray500),
                ),
              ],
            ),

            const SizedBox(height: 16),

            // Recommendations
            if (isHighRisk) ...[
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: AppTheme.dangerColor.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(
                    color: AppTheme.dangerColor.withOpacity(0.3),
                  ),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        const Icon(
                          Icons.medical_services,
                          color: AppTheme.dangerColor,
                          size: 20,
                        ),
                        const SizedBox(width: 8),
                        Text(
                          'Immediate Action Required',
                          style: Theme.of(
                            context,
                          ).textTheme.titleMedium?.copyWith(
                                color: AppTheme.dangerColor,
                                fontWeight: FontWeight.bold,
                              ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'Please consult with a healthcare professional immediately for further evaluation and treatment.',
                      style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                            color: AppTheme.dangerColor,
                          ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 16),

              // Contact Expert Button
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  onPressed: () {
                    final authProvider =
                        Provider.of<AuthProvider>(context, listen: false);
                    print(
                        'Current user: ${authProvider.currentUser}'); // Debug print
                    if (authProvider.currentUser == null) {
                      // Redirect to login if not logged in
                      Navigator.pushNamed(context, AppRouter.login);
                      return;
                    }
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (context) => BookAppointmentScreen(
                          predictionId: prediction.id,
                          predictionResult: prediction.prediction.toString(),
                        ),
                      ),
                    );
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppTheme.dangerColor,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    elevation: 2,
                  ),
                  icon: const Icon(Icons.medical_services, size: 20),
                  label: const Text(
                    'Contact Expert',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ),
            ] else ...[
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: AppTheme.successColor.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(
                    color: AppTheme.successColor.withOpacity(0.3),
                  ),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        const Icon(
                          Icons.health_and_safety,
                          color: AppTheme.successColor,
                          size: 20,
                        ),
                        const SizedBox(width: 8),
                        Text(
                          'Preventive Measures',
                          style: Theme.of(
                            context,
                          ).textTheme.titleMedium?.copyWith(
                                color: AppTheme.successColor,
                                fontWeight: FontWeight.bold,
                              ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'Continue maintaining a healthy lifestyle with regular exercise and balanced diet.',
                      style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                            color: AppTheme.successColor,
                          ),
                    ),
                  ],
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildInfoCard(
    BuildContext context,
    String title,
    String value,
    IconData icon,
    Color color,
  ) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: color.withOpacity(0.1),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: color.withOpacity(0.3)),
      ),
      child: Column(
        children: [
          Icon(icon, color: color, size: 24),
          const SizedBox(height: 8),
          Text(
            title,
            style: Theme.of(context).textTheme.bodySmall?.copyWith(
                  color: color,
                  fontWeight: FontWeight.w500,
                ),
          ),
          const SizedBox(height: 4),
          Text(
            value,
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                  color: color,
                  fontWeight: FontWeight.bold,
                ),
          ),
        ],
      ),
    );
  }

  String _formatDate(DateTime date) {
    return '${date.day}/${date.month}/${date.year} at ${date.hour}:${date.minute.toString().padLeft(2, '0')}';
  }
}
