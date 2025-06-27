class Prediction {
  final int? id;
  final int age;
  final int sex;
  final int cp;
  final int trestbps;
  final int chol;
  final int fbs;
  final int restecg;
  final int thalach;
  final int exang;
  final double oldpeak;
  final int slope;
  final int ca;
  final int thal;
  final int prediction;
  final int userId;
  final DateTime? predictionDate;
  final DateTime? createdAt;
  final double? probability;

  Prediction({
    this.id,
    required this.age,
    required this.sex,
    required this.cp,
    required this.trestbps,
    required this.chol,
    required this.fbs,
    required this.restecg,
    required this.thalach,
    required this.exang,
    required this.oldpeak,
    required this.slope,
    required this.ca,
    required this.thal,
    required this.prediction,
    required this.userId,
    this.predictionDate,
    this.createdAt,
    this.probability,
  });

  bool get isHighRisk => prediction == 1;
  bool get isLowRisk => prediction == 0;

  factory Prediction.fromJson(Map<String, dynamic> json) {
    return Prediction(
      id: json['id'],
      age: json['age'] ?? 0,
      sex: json['sex'] ?? 0,
      cp: json['cp'] ?? 0,
      trestbps: json['trestbps'] ?? 0,
      chol: json['chol'] ?? 0,
      fbs: json['fbs'] ?? 0,
      restecg: json['restecg'] ?? 0,
      thalach: json['thalach'] ?? 0,
      exang: json['exang'] ?? 0,
      oldpeak: (json['oldpeak'] ?? 0.0).toDouble(),
      slope: json['slope'] ?? 0,
      ca: json['ca'] ?? 0,
      thal: json['thal'] ?? 0,
      prediction: json['prediction'] ?? 0,
      userId: json['user_id'] ?? 0,
      predictionDate: json['prediction_date'] != null
          ? DateTime.parse(json['prediction_date'])
          : null,
      createdAt: json['created_at'] != null
          ? DateTime.parse(json['created_at'])
          : null,
      probability: json['probability'] != null
          ? (json['probability'] as num).toDouble()
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'age': age,
      'sex': sex,
      'cp': cp,
      'trestbps': trestbps,
      'chol': chol,
      'fbs': fbs,
      'restecg': restecg,
      'thalach': thalach,
      'exang': exang,
      'oldpeak': oldpeak,
      'slope': slope,
      'ca': ca,
      'thal': thal,
      'prediction': prediction,
      'user_id': userId,
      'prediction_date': predictionDate?.toIso8601String(),
      'created_at': createdAt?.toIso8601String(),
      'probability': probability,
    };
  }

  Prediction copyWith({
    int? id,
    int? age,
    int? sex,
    int? cp,
    int? trestbps,
    int? chol,
    int? fbs,
    int? restecg,
    int? thalach,
    int? exang,
    double? oldpeak,
    int? slope,
    int? ca,
    int? thal,
    int? prediction,
    int? userId,
    DateTime? predictionDate,
    DateTime? createdAt,
    double? probability,
  }) {
    return Prediction(
      id: id ?? this.id,
      age: age ?? this.age,
      sex: sex ?? this.sex,
      cp: cp ?? this.cp,
      trestbps: trestbps ?? this.trestbps,
      chol: chol ?? this.chol,
      fbs: fbs ?? this.fbs,
      restecg: restecg ?? this.restecg,
      thalach: thalach ?? this.thalach,
      exang: exang ?? this.exang,
      oldpeak: oldpeak ?? this.oldpeak,
      slope: slope ?? this.slope,
      ca: ca ?? this.ca,
      thal: thal ?? this.thal,
      prediction: prediction ?? this.prediction,
      userId: userId ?? this.userId,
      predictionDate: predictionDate ?? this.predictionDate,
      createdAt: createdAt ?? this.createdAt,
      probability: probability ?? this.probability,
    );
  }

  String get riskLevel => isHighRisk ? 'High Risk' : 'Low Risk';
  String get riskColor => isHighRisk ? '#EF4444' : '#10B981';

  String get probabilityPercentage =>
      '${(probability != null ? probability! * 100 : 0.0).toStringAsFixed(1)}%';

  // Helper methods for medical interpretation
  String get chestPainType {
    switch (cp) {
      case 0:
        return 'Typical angina';
      case 1:
        return 'Atypical angina';
      case 2:
        return 'Non-anginal pain';
      case 3:
        return 'Asymptomatic';
      default:
        return 'Unknown';
    }
  }

  String get ecgResults {
    switch (restecg) {
      case 0:
        return 'Normal';
      case 1:
        return 'ST-T wave abnormality';
      case 2:
        return 'Left ventricular hypertrophy';
      default:
        return 'Unknown';
    }
  }

  String get slopeType {
    switch (slope) {
      case 0:
        return 'Upsloping';
      case 1:
        return 'Flat';
      case 2:
        return 'Downsloping';
      default:
        return 'Unknown';
    }
  }

  String get thalassemiaType {
    switch (thal) {
      case 0:
        return 'Normal';
      case 1:
        return 'Fixed defect';
      case 2:
        return 'Reversible defect';
      case 3:
        return 'Not applicable';
      default:
        return 'Unknown';
    }
  }

  @override
  String toString() {
    return 'Prediction{id: $id, prediction: $prediction, probability: $probability}';
  }

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is Prediction && runtimeType == other.runtimeType && id == other.id;

  @override
  int get hashCode => id.hashCode;
}
