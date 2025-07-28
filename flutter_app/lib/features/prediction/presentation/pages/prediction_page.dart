import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../../core/providers/prediction_provider.dart';
import '../../../../core/providers/auth_provider.dart';
import '../../../../core/theme/app_theme.dart';
import '../../../../core/widgets/app_drawer.dart';
import '../widgets/prediction_result_card.dart';
import '../widgets/field_guide_card.dart';

class PredictionPage extends StatefulWidget {
  const PredictionPage({super.key});

  @override
  State<PredictionPage> createState() => _PredictionPageState();
}

class _PredictionPageState extends State<PredictionPage> {
  final _formKey = GlobalKey<FormState>();

  // Form controllers
  final _ageController = TextEditingController();
  final _trestbpsController = TextEditingController();
  final _cholController = TextEditingController();
  final _thalachController = TextEditingController();
  final _oldpeakController = TextEditingController();
  final _caController = TextEditingController();

  // Form values
  int? _sex = null;
  int? _cp = null;
  int? _fbs = null;
  int? _restecg = null;
  int? _exang = null;
  int? _slope = null;
  int? _thal = null;

  @override
  void dispose() {
    _ageController.dispose();
    _trestbpsController.dispose();
    _cholController.dispose();
    _thalachController.dispose();
    _oldpeakController.dispose();
    _caController.dispose();
    super.dispose();
  }

  Future<void> _makePrediction() async {
    if (!_formKey.currentState!.validate()) return;

    final predictionData = {
      'age': int.parse(_ageController.text),
      'sex': _sex,
      'cp': _cp,
      'trestbps': int.parse(_trestbpsController.text),
      'chol': int.parse(_cholController.text),
      'fbs': _fbs,
      'restecg': _restecg,
      'thalach': int.parse(_thalachController.text),
      'exang': _exang,
      'oldpeak': double.parse(_oldpeakController.text),
      'slope': _slope,
      'ca': int.parse(_caController.text),
      'thal': _thal,
    };

    final predictionProvider = context.read<PredictionProvider>();
    final success = await predictionProvider.makePrediction(predictionData);

    if (success && mounted) {
      // Save the prediction to the database
      final prediction = predictionProvider.currentPrediction;
      if (prediction != null) {
        final saved = await predictionProvider.savePrediction(prediction);
        if (saved) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Prediction completed and saved successfully!'),
              backgroundColor: AppTheme.successColor,
            ),
          );
        } else {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Prediction completed but failed to save.'),
              backgroundColor: Colors.orange,
            ),
          );
        }
      }
    }
  }

  void _clearForm() {
    _formKey.currentState?.reset();
    _ageController.clear();
    _trestbpsController.clear();
    _cholController.clear();
    _thalachController.clear();
    _oldpeakController.clear();
    _caController.clear();

    setState(() {
      _sex = null;
      _cp = null;
      _fbs = null;
      _restecg = null;
      _exang = null;
      _slope = null;
      _thal = null;
    });
  }

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(
      builder: (context, constraints) {
        return Scaffold(
          appBar: AppBar(
            title: const Text('Heart Disease Prediction'),
            actions: [
              IconButton(
                icon: const Icon(Icons.history),
                onPressed: () {
                  // Navigate to prediction history
                },
              ),
            ],
          ),
          drawer: const AppDrawer(),
          body: SingleChildScrollView(
            padding: const EdgeInsets.all(16),
            child: ConstrainedBox(
              constraints: BoxConstraints(
                minHeight: constraints.maxHeight - 32,
              ),
              child: IntrinsicHeight(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    // Welcome Card
                    Card(
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                const Icon(
                                  Icons.favorite,
                                  color: AppTheme.primaryColor,
                                  size: 24,
                                ),
                                const SizedBox(width: 8),
                                Text(
                                  'New Prediction',
                                  style: Theme.of(context)
                                      .textTheme
                                      .titleLarge
                                      ?.copyWith(fontWeight: FontWeight.bold),
                                ),
                              ],
                            ),
                            const SizedBox(height: 8),
                            Text(
                              'Enter the patient\'s medical data to predict heart disease risk.',
                              style: Theme.of(
                                context,
                              )
                                  .textTheme
                                  .bodyMedium
                                  ?.copyWith(color: AppTheme.gray600),
                            ),
                          ],
                        ),
                      ),
                    ),

                    const SizedBox(height: 16),

                    // Field Guide Card
                    const FieldGuideCard(),

                    const SizedBox(height: 16),

                    // Prediction Form
                    Card(
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: Form(
                          key: _formKey,
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.stretch,
                            children: [
                              Text(
                                'Patient Information',
                                style: Theme.of(context)
                                    .textTheme
                                    .titleMedium
                                    ?.copyWith(fontWeight: FontWeight.bold),
                              ),

                              const SizedBox(height: 16),

                              // Age
                              TextFormField(
                                controller: _ageController,
                                keyboardType: TextInputType.number,
                                decoration: const InputDecoration(
                                  labelText: 'Age',
                                  prefixIcon: Icon(Icons.person),
                                  hintText: 'Enter age (1-120)',
                                ),
                                validator: (value) {
                                  if (value == null || value.isEmpty) {
                                    return 'Please enter age';
                                  }
                                  final age = int.tryParse(value);
                                  if (age == null || age < 1 || age > 120) {
                                    return 'Please enter a valid age (1-120)';
                                  }
                                  return null;
                                },
                              ),

                              const SizedBox(height: 16),

                              // Sex
                              DropdownButtonFormField<int>(
                                value: _sex,
                                decoration: const InputDecoration(
                                  labelText: 'Gender',
                                  prefixIcon: Icon(Icons.person_outline),
                                ),
                                items: const [
                                  DropdownMenuItem(
                                      value: null,
                                      child: Text('Choose Gender')),
                                  DropdownMenuItem(
                                      value: 0, child: Text('Female')),
                                  DropdownMenuItem(
                                      value: 1, child: Text('Male')),
                                ],
                                validator: (value) => value == null
                                    ? 'Please choose gender'
                                    : null,
                                onChanged: (value) {
                                  setState(() {
                                    _sex = value;
                                  });
                                },
                              ),

                              const SizedBox(height: 16),

                              // Chest Pain Type
                              DropdownButtonFormField<int>(
                                value: _cp,
                                isExpanded: true,
                                decoration: const InputDecoration(
                                  labelText: 'Chest Pain Type',
                                  prefixIcon: Icon(Icons.medical_services),
                                ),
                                items: const [
                                  DropdownMenuItem(
                                      value: null,
                                      child: Text('Choose Chest Pain Type')),
                                  DropdownMenuItem(
                                      value: 0, child: Text('Typical angina')),
                                  DropdownMenuItem(
                                      value: 1, child: Text('Atypical angina')),
                                  DropdownMenuItem(
                                      value: 2,
                                      child: Text('Non-anginal pain')),
                                  DropdownMenuItem(
                                      value: 3, child: Text('Asymptomatic')),
                                ],
                                validator: (value) => value == null
                                    ? 'Please choose chest pain type'
                                    : null,
                                onChanged: (value) {
                                  setState(() {
                                    _cp = value;
                                  });
                                },
                              ),

                              const SizedBox(height: 16),

                              // Resting Blood Pressure
                              TextFormField(
                                controller: _trestbpsController,
                                keyboardType: TextInputType.number,
                                decoration: const InputDecoration(
                                  labelText: 'Resting Blood Pressure (mm Hg)',
                                  prefixIcon: Icon(Icons.favorite),
                                  hintText: 'Enter BP (80-300)',
                                ),
                                validator: (value) {
                                  if (value == null || value.isEmpty) {
                                    return 'Please enter blood pressure';
                                  }
                                  final bp = int.tryParse(value);
                                  if (bp == null || bp < 80 || bp > 300) {
                                    return 'Please enter a valid blood pressure (80-300)';
                                  }
                                  return null;
                                },
                              ),

                              const SizedBox(height: 16),

                              // Cholesterol
                              TextFormField(
                                controller: _cholController,
                                keyboardType: TextInputType.number,
                                decoration: const InputDecoration(
                                  labelText: 'Cholesterol (mg/dl)',
                                  prefixIcon: Icon(Icons.water_drop),
                                  hintText: 'Enter cholesterol (100-600)',
                                ),
                                validator: (value) {
                                  if (value == null || value.isEmpty) {
                                    return 'Please enter cholesterol level';
                                  }
                                  final chol = int.tryParse(value);
                                  if (chol == null ||
                                      chol < 100 ||
                                      chol > 600) {
                                    return 'Please enter a valid cholesterol level (100-600)';
                                  }
                                  return null;
                                },
                              ),

                              const SizedBox(height: 16),

                              // Fasting Blood Sugar
                              DropdownButtonFormField<int>(
                                value: _fbs,
                                isExpanded: true,
                                decoration: const InputDecoration(
                                  labelText: 'Fasting Blood Sugar > 120 mg/dl',
                                  prefixIcon: Icon(Icons.bloodtype),
                                ),
                                items: const [
                                  DropdownMenuItem(
                                      value: null,
                                      child:
                                          Text('Choose Fasting Blood Sugar')),
                                  DropdownMenuItem(value: 0, child: Text('No')),
                                  DropdownMenuItem(
                                      value: 1, child: Text('Yes')),
                                ],
                                validator: (value) => value == null
                                    ? 'Please choose fasting blood sugar'
                                    : null,
                                onChanged: (value) {
                                  setState(() {
                                    _fbs = value;
                                  });
                                },
                              ),

                              const SizedBox(height: 16),

                              // ECG Results
                              DropdownButtonFormField<int>(
                                value: _restecg,
                                isExpanded: true,
                                decoration: const InputDecoration(
                                  labelText: 'ECG Results',
                                  prefixIcon: Icon(Icons.monitor_heart),
                                ),
                                items: const [
                                  DropdownMenuItem(
                                      value: null,
                                      child: Text('Choose ECG Results')),
                                  DropdownMenuItem(
                                      value: 0, child: Text('Normal')),
                                  DropdownMenuItem(
                                      value: 1,
                                      child: Text('ST-T wave abnormality')),
                                  DropdownMenuItem(
                                      value: 2,
                                      child:
                                          Text('Left ventricular hypertrophy')),
                                ],
                                validator: (value) => value == null
                                    ? 'Please choose ECG results'
                                    : null,
                                onChanged: (value) {
                                  setState(() {
                                    _restecg = value;
                                  });
                                },
                              ),

                              const SizedBox(height: 16),

                              // Maximum Heart Rate
                              TextFormField(
                                controller: _thalachController,
                                keyboardType: TextInputType.number,
                                decoration: const InputDecoration(
                                  labelText: 'Maximum Heart Rate',
                                  prefixIcon: Icon(Icons.trending_up),
                                  hintText: 'Enter heart rate (60-250)',
                                ),
                                validator: (value) {
                                  if (value == null || value.isEmpty) {
                                    return 'Please enter maximum heart rate';
                                  }
                                  final hr = int.tryParse(value);
                                  if (hr == null || hr < 60 || hr > 250) {
                                    return 'Please enter a valid heart rate (60-250)';
                                  }
                                  return null;
                                },
                              ),

                              const SizedBox(height: 16),

                              // Exercise Induced Angina
                              DropdownButtonFormField<int>(
                                value: _exang,
                                isExpanded: true,
                                decoration: const InputDecoration(
                                  labelText: 'Exercise Induced Angina',
                                  prefixIcon: Icon(Icons.fitness_center),
                                ),
                                items: const [
                                  DropdownMenuItem(
                                      value: null,
                                      child: Text(
                                          'Choose Exercise Induced Angina')),
                                  DropdownMenuItem(value: 0, child: Text('No')),
                                  DropdownMenuItem(
                                      value: 1, child: Text('Yes')),
                                ],
                                validator: (value) => value == null
                                    ? 'Please choose exercise induced angina'
                                    : null,
                                onChanged: (value) {
                                  setState(() {
                                    _exang = value;
                                  });
                                },
                              ),

                              const SizedBox(height: 16),

                              // ST Depression
                              TextFormField(
                                controller: _oldpeakController,
                                keyboardType: TextInputType.number,
                                decoration: const InputDecoration(
                                  labelText: 'ST Depression',
                                  prefixIcon: Icon(Icons.show_chart),
                                  hintText: 'Enter ST depression (0-10)',
                                ),
                                validator: (value) {
                                  if (value == null || value.isEmpty) {
                                    return 'Please enter ST depression';
                                  }
                                  final st = double.tryParse(value);
                                  if (st == null || st < 0 || st > 10) {
                                    return 'Please enter a valid ST depression (0-10)';
                                  }
                                  return null;
                                },
                              ),

                              const SizedBox(height: 16),

                              // Slope
                              DropdownButtonFormField<int>(
                                value: _slope,
                                isExpanded: true,
                                decoration: const InputDecoration(
                                  labelText: 'Slope',
                                  prefixIcon: Icon(Icons.trending_down),
                                ),
                                items: const [
                                  DropdownMenuItem(
                                      value: null, child: Text('Choose Slope')),
                                  DropdownMenuItem(
                                      value: 0, child: Text('Upsloping')),
                                  DropdownMenuItem(
                                      value: 1, child: Text('Flat')),
                                  DropdownMenuItem(
                                      value: 2, child: Text('Downsloping')),
                                ],
                                validator: (value) => value == null
                                    ? 'Please choose slope'
                                    : null,
                                onChanged: (value) {
                                  setState(() {
                                    _slope = value;
                                  });
                                },
                              ),

                              const SizedBox(height: 16),

                              // Number of Major Vessels
                              TextFormField(
                                controller: _caController,
                                keyboardType: TextInputType.number,
                                decoration: const InputDecoration(
                                  labelText: 'Number of Major Vessels',
                                  prefixIcon: Icon(Icons.account_tree),
                                  hintText: 'Enter number (0-4)',
                                ),
                                validator: (value) {
                                  if (value == null || value.isEmpty) {
                                    return 'Please enter number of vessels';
                                  }
                                  final vessels = int.tryParse(value);
                                  if (vessels == null ||
                                      vessels < 0 ||
                                      vessels > 4) {
                                    return 'Please enter a valid number (0-4)';
                                  }
                                  return null;
                                },
                              ),

                              const SizedBox(height: 16),

                              // Thalassemia
                              DropdownButtonFormField<int>(
                                value: _thal,
                                isExpanded: true,
                                decoration: const InputDecoration(
                                  labelText: 'Thalassemia',
                                  prefixIcon: Icon(Icons.medical_information),
                                ),
                                items: const [
                                  DropdownMenuItem(
                                      value: null,
                                      child: Text('Choose Thalassemia')),
                                  DropdownMenuItem(
                                      value: 0, child: Text('Normal')),
                                  DropdownMenuItem(
                                      value: 1, child: Text('Fixed defect')),
                                  DropdownMenuItem(
                                      value: 2,
                                      child: Text('Reversible defect')),
                                  DropdownMenuItem(
                                      value: 3, child: Text('Not applicable')),
                                ],
                                validator: (value) => value == null
                                    ? 'Please choose thalassemia'
                                    : null,
                                onChanged: (value) {
                                  setState(() {
                                    _thal = value;
                                  });
                                },
                              ),

                              const SizedBox(height: 24),

                              // Action Buttons
                              Row(
                                children: [
                                  Expanded(
                                    child: OutlinedButton(
                                      onPressed: _clearForm,
                                      style: OutlinedButton.styleFrom(
                                        padding: const EdgeInsets.symmetric(
                                          vertical: 16,
                                        ),
                                      ),
                                      child: const Text('Clear'),
                                    ),
                                  ),
                                  const SizedBox(width: 16),
                                  Expanded(
                                    child: ElevatedButton(
                                      onPressed: context.watch<PredictionProvider>().isLoading
                                          ? null
                                          : _makePrediction,
                                      style: ElevatedButton.styleFrom(
                                        padding: const EdgeInsets.symmetric(
                                          vertical: 16,
                                        ),
                                      ),
                                      child: context.watch<PredictionProvider>().isLoading
                                          ? const SizedBox(
                                              height: 20,
                                              width: 20,
                                              child: CircularProgressIndicator(
                                                strokeWidth: 2,
                                                valueColor:
                                                    AlwaysStoppedAnimation<
                                                        Color>(
                                                  Colors.white,
                                                ),
                                              ),
                                            )
                                          : const Text('Predict'),
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),

                    const SizedBox(height: 16),

                    // Prediction Result
                    if (context.watch<PredictionProvider>().currentPrediction != null) ...[
                      PredictionResultCard(
                        prediction: context.watch<PredictionProvider>().currentPrediction!,
                      ),
                      const SizedBox(height: 16),
                    ],
                  ],
                ),
              ),
            ),
          ),
        );
      },
    );
  }
}
