class Appointment {
  final int? id;
  final int userId;
  final String patientName;
  final String patientEmail;
  final String patientPhone;
  final DateTime appointmentDate;
  final String appointmentTime;
  final String address;
  final String reason;
  final int? predictionId;
  final int? predictionResult;
  final String status;
  final String? adminNotes;
  final DateTime createdAt;
  final DateTime? updatedAt;

  Appointment({
    this.id,
    required this.userId,
    required this.patientName,
    required this.patientEmail,
    required this.patientPhone,
    required this.appointmentDate,
    required this.appointmentTime,
    required this.address,
    required this.reason,
    this.predictionId,
    this.predictionResult,
    this.status = 'pending',
    this.adminNotes,
    required this.createdAt,
    this.updatedAt,
  });

  factory Appointment.fromJson(Map<String, dynamic> json) {
    return Appointment(
      id: json['id'],
      userId: json['user_id'],
      patientName: json['patient_name'],
      patientEmail: json['patient_email'],
      patientPhone: json['patient_phone'],
      appointmentDate: DateTime.parse(json['appointment_date']),
      appointmentTime: json['appointment_time'],
      address: json['address'],
      reason: json['reason'],
      predictionId: json['prediction_id'],
      predictionResult: json['prediction_result'] != null
          ? int.tryParse(json['prediction_result'].toString())
          : null,
      status: json['status'],
      adminNotes: json['admin_notes'],
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: json['updated_at'] != null
          ? DateTime.parse(json['updated_at'])
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'user_id': userId,
      'patient_name': patientName,
      'patient_email': patientEmail,
      'patient_phone': patientPhone,
      'appointment_date': appointmentDate.toIso8601String().split('T')[0],
      'appointment_time': appointmentTime,
      'address': address,
      'reason': reason,
      'prediction_id': predictionId,
      'prediction_result': predictionResult,
      'status': status,
      'admin_notes': adminNotes,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt?.toIso8601String(),
    };
  }

  Appointment copyWith({
    int? id,
    int? userId,
    String? patientName,
    String? patientEmail,
    String? patientPhone,
    DateTime? appointmentDate,
    String? appointmentTime,
    String? address,
    String? reason,
    int? predictionId,
    int? predictionResult,
    String? status,
    String? adminNotes,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return Appointment(
      id: id ?? this.id,
      userId: userId ?? this.userId,
      patientName: patientName ?? this.patientName,
      patientEmail: patientEmail ?? this.patientEmail,
      patientPhone: patientPhone ?? this.patientPhone,
      appointmentDate: appointmentDate ?? this.appointmentDate,
      appointmentTime: appointmentTime ?? this.appointmentTime,
      address: address ?? this.address,
      reason: reason ?? this.reason,
      predictionId: predictionId ?? this.predictionId,
      predictionResult: predictionResult ?? this.predictionResult,
      status: status ?? this.status,
      adminNotes: adminNotes ?? this.adminNotes,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }
}
