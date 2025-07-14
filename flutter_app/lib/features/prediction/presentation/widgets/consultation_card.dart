import 'package:flutter/material.dart';
import '../../../../core/theme/app_theme.dart';

class ConsultationCard extends StatelessWidget {
  final Map<String, dynamic> consultation;

  const ConsultationCard({super.key, required this.consultation});

  @override
  Widget build(BuildContext context) {
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
                    color: AppTheme.primaryColor.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(
                    Icons.medical_services,
                    color: AppTheme.primaryColor,
                    size: 28,
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'AI Consultation',
                        style: Theme.of(context).textTheme.titleLarge?.copyWith(
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      Text(
                        'Personalized medical advice',
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

            // Risk Factors
            if (consultation['risk_factors'] != null) ...[
              _buildSection(
                context,
                'Key Risk Factors',
                Icons.warning,
                AppTheme.warningColor,
                consultation['risk_factors'],
              ),
              const SizedBox(height: 16),
            ],

            // Recommendations
            if (consultation['recommendations'] != null) ...[
              _buildSection(
                context,
                'Recommendations',
                Icons.lightbulb,
                AppTheme.successColor,
                consultation['recommendations'],
              ),
              const SizedBox(height: 16),
            ],

            // Lifestyle Changes
            if (consultation['lifestyle_changes'] != null) ...[
              _buildSection(
                context,
                'Lifestyle Changes',
                Icons.fitness_center,
                AppTheme.primaryColor,
                consultation['lifestyle_changes'],
              ),
              const SizedBox(height: 16),
            ],

            // Follow-up
            if (consultation['follow_up'] != null) ...[
              _buildSection(
                context,
                'Follow-up Actions',
                Icons.schedule,
                AppTheme.secondaryColor,
                consultation['follow_up'],
              ),
            ],

            const SizedBox(height: 20),

            // Disclaimer
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: AppTheme.gray100,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppTheme.gray300),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      const Icon(
                        Icons.info_outline,
                        color: AppTheme.gray600,
                        size: 20,
                      ),
                      const SizedBox(width: 8),
                      Text(
                        'Important Notice',
                        style: Theme.of(context).textTheme.titleSmall?.copyWith(
                          color: AppTheme.gray700,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'This AI consultation is for informational purposes only and should not replace professional medical advice. Always consult with a qualified healthcare provider for diagnosis and treatment.',
                    style: Theme.of(
                      context,
                    ).textTheme.bodySmall?.copyWith(color: AppTheme.gray600),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSection(
    BuildContext context,
    String title,
    IconData icon,
    Color color,
    dynamic content,
  ) {
    List<String> items = [];

    if (content is List) {
      items = content.cast<String>();
    } else if (content is String) {
      items = [content];
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Icon(icon, color: color, size: 20),
            const SizedBox(width: 8),
            Text(
              title,
              style: Theme.of(context).textTheme.titleMedium?.copyWith(
                color: color,
                fontWeight: FontWeight.bold,
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        ...items
            .map(
              (item) => Padding(
                padding: const EdgeInsets.only(bottom: 8),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      margin: const EdgeInsets.only(top: 6),
                      width: 6,
                      height: 6,
                      decoration: BoxDecoration(
                        color: color,
                        shape: BoxShape.circle,
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        item,
                        style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                          color: AppTheme.gray700,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            )
            ,
      ],
    );
  }
}
