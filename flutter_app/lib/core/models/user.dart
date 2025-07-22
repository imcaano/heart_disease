class User {
  final int id;
  final String username;
  final String email;
  final String walletAddress;
  final String role;
  final DateTime createdAt;
  final int totalPredictions;
  final double predictionAccuracy;
  final int reputationScore;
  final DateTime? lastLogin;
  final String status;

  User({
    required this.id,
    required this.username,
    required this.email,
    required this.walletAddress,
    required this.role,
    required this.createdAt,
    required this.totalPredictions,
    required this.predictionAccuracy,
    required this.reputationScore,
    this.lastLogin,
    required this.status,
  });

  bool get isAdmin => role.toLowerCase() == 'admin';
  bool get isActive => status == 'active';

  factory User.fromJson(Map<String, dynamic> json) {
    // Handle prediction_accuracy conversion from string to double
    double parsePredictionAccuracy(dynamic value) {
      if (value == null) return 0.0;
      if (value is double) return value;
      if (value is int) return value.toDouble();
      if (value is String) {
        try {
          return double.parse(value);
        } catch (e) {
          return 0.0;
        }
      }
      return 0.0;
    }

    return User(
      id: json['id'] ?? 0,
      username: json['username'] ?? '',
      email: json['email'] ?? '',
      walletAddress: json['wallet_address'] ?? '',
      role: json['role'] ?? 'user',
      createdAt: json['created_at'] != null
          ? DateTime.parse(json['created_at'])
          : DateTime.now(),
      totalPredictions: json['total_predictions'] ?? 0,
      predictionAccuracy: parsePredictionAccuracy(json['prediction_accuracy']),
      reputationScore: json['reputation_score'] ?? 0,
      lastLogin: json['last_login'] != null
          ? DateTime.parse(json['last_login'])
          : null,
      status: json['status'] ?? 'active',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'username': username,
      'email': email,
      'wallet_address': walletAddress,
      'role': role,
      'created_at': createdAt.toIso8601String(),
      'total_predictions': totalPredictions,
      'prediction_accuracy': predictionAccuracy,
      'reputation_score': reputationScore,
      'last_login': lastLogin?.toIso8601String(),
      'status': status,
    };
  }

  User copyWith({
    int? id,
    String? username,
    String? email,
    String? walletAddress,
    String? role,
    DateTime? createdAt,
    int? totalPredictions,
    double? predictionAccuracy,
    int? reputationScore,
    DateTime? lastLogin,
    String? status,
  }) {
    return User(
      id: id ?? this.id,
      username: username ?? this.username,
      email: email ?? this.email,
      walletAddress: walletAddress ?? this.walletAddress,
      role: role ?? this.role,
      createdAt: createdAt ?? this.createdAt,
      totalPredictions: totalPredictions ?? this.totalPredictions,
      predictionAccuracy: predictionAccuracy ?? this.predictionAccuracy,
      reputationScore: reputationScore ?? this.reputationScore,
      lastLogin: lastLogin ?? this.lastLogin,
      status: status ?? this.status,
    );
  }

  @override
  String toString() {
    return 'User{id: $id, username: $username, email: $email, role: $role}';
  }

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is User && runtimeType == other.runtimeType && id == other.id;

  @override
  int get hashCode => id.hashCode;
}
