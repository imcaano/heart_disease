import 'package:flutter/material.dart';
import '../../../../core/theme/app_theme.dart';

class FieldGuideCard extends StatelessWidget {
  const FieldGuideCard({super.key});

  @override
  Widget build(BuildContext context) {
    return Card(
      child: ExpansionTile(
        leading: const Icon(
          Icons.help_outline,
          color: AppTheme.primaryColor,
        ),
        title: Text(
          'Field Guide & Data Ranges',
          style: Theme.of(context).textTheme.titleMedium?.copyWith(
                fontWeight: FontWeight.bold,
              ),
        ),
        subtitle:
            const Text('Click to view field descriptions and valid ranges'),
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _buildFieldInfo(
                  context,
                  'Age',
                  'Patient age in years',
                  'Range: 1-120 years',
                  'Typical range: 29-77 years',
                ),
                const Divider(),
                _buildFieldInfo(
                  context,
                  'Sex',
                  'Patient gender',
                  '0 = Female, 1 = Male',
                  'Binary classification',
                ),
                const Divider(),
                _buildFieldInfo(
                  context,
                  'Chest Pain Type (CP)',
                  'Type of chest pain experienced',
                  '0 = Typical angina\n1 = Atypical angina\n2 = Non-anginal pain\n3 = Asymptomatic',
                  'Higher values indicate more severe symptoms',
                ),
                const Divider(),
                _buildFieldInfo(
                  context,
                  'Resting Blood Pressure',
                  'Systolic blood pressure at rest (mm Hg)',
                  'Range: 80-300 mm Hg',
                  'Normal: <120, Elevated: 120-129, High: ≥130',
                ),
                const Divider(),
                _buildFieldInfo(
                  context,
                  'Cholesterol',
                  'Serum cholesterol level (mg/dl)',
                  'Range: 100-600 mg/dl',
                  'Normal: <200, Borderline: 200-239, High: ≥240',
                ),
                const Divider(),
                _buildFieldInfo(
                  context,
                  'Fasting Blood Sugar',
                  'Fasting blood sugar > 120 mg/dl',
                  '0 = No (≤120 mg/dl)\n1 = Yes (>120 mg/dl)',
                  'Indicates diabetes or pre-diabetes',
                ),
                const Divider(),
                _buildFieldInfo(
                  context,
                  'ECG Results',
                  'Resting electrocardiographic results',
                  '0 = Normal\n1 = ST-T wave abnormality\n2 = Left ventricular hypertrophy',
                  'Abnormal ECG suggests heart disease',
                ),
                const Divider(),
                _buildFieldInfo(
                  context,
                  'Maximum Heart Rate',
                  'Maximum heart rate achieved during exercise',
                  'Range: 60-250 bpm',
                  'Lower values may indicate heart disease',
                ),
                const Divider(),
                _buildFieldInfo(
                  context,
                  'Exercise Induced Angina',
                  'Exercise induced chest pain',
                  '0 = No\n1 = Yes',
                  'Presence of angina during exercise',
                ),
                const Divider(),
                _buildFieldInfo(
                  context,
                  'ST Depression',
                  'ST depression induced by exercise relative to rest',
                  'Range: 0-10 mm',
                  'Higher values indicate more severe ischemia',
                ),
                const Divider(),
                _buildFieldInfo(
                  context,
                  'Slope',
                  'Slope of the peak exercise ST segment',
                  '0 = Upsloping\n1 = Flat\n2 = Downsloping',
                  'Downsloping is most concerning',
                ),
                const Divider(),
                _buildFieldInfo(
                  context,
                  'Number of Major Vessels',
                  'Number of major vessels colored by fluoroscopy',
                  'Range: 0-4 vessels',
                  'More vessels affected = higher risk',
                ),
                const Divider(),
                _buildFieldInfo(
                  context,
                  'Thalassemia',
                  'Thalassemia type',
                  '0 = Normal\n1 = Fixed defect\n2 = Reversible defect\n3 = Not applicable',
                  'Blood disorder affecting oxygen transport',
                ),
                const SizedBox(height: 16),
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: AppTheme.primaryColor.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(
                      color: AppTheme.primaryColor.withOpacity(0.3),
                    ),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          const Icon(
                            Icons.info_outline,
                            color: AppTheme.primaryColor,
                            size: 20,
                          ),
                          const SizedBox(width: 8),
                          Text(
                            'Important Notes:',
                            style: Theme.of(context)
                                .textTheme
                                .titleSmall
                                ?.copyWith(
                                  fontWeight: FontWeight.bold,
                                  color: AppTheme.primaryColor,
                                ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Text(
                        '• All fields are required for accurate prediction\n'
                        '• Use actual medical measurements when possible\n'
                        '• The model uses a rule-based algorithm for demonstration\n'
                        '• Results should not replace professional medical advice',
                        style: Theme.of(context).textTheme.bodySmall?.copyWith(
                              color: AppTheme.gray600,
                            ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFieldInfo(
    BuildContext context,
    String fieldName,
    String description,
    String range,
    String notes,
  ) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          fieldName,
          style: Theme.of(context).textTheme.titleSmall?.copyWith(
                fontWeight: FontWeight.bold,
                color: AppTheme.primaryColor,
              ),
        ),
        const SizedBox(height: 4),
        Text(
          description,
          style: Theme.of(context).textTheme.bodyMedium,
        ),
        const SizedBox(height: 4),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
          decoration: BoxDecoration(
            color: AppTheme.successColor.withOpacity(0.1),
            borderRadius: BorderRadius.circular(4),
            border: Border.all(
              color: AppTheme.successColor.withOpacity(0.3),
            ),
          ),
          child: Text(
            range,
            style: Theme.of(context).textTheme.bodySmall?.copyWith(
                  color: AppTheme.successColor,
                  fontWeight: FontWeight.w500,
                ),
          ),
        ),
        const SizedBox(height: 4),
        Text(
          notes,
          style: Theme.of(context).textTheme.bodySmall?.copyWith(
                color: AppTheme.gray600,
                fontStyle: FontStyle.italic,
              ),
        ),
      ],
    );
  }
}
