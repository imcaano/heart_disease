class ApiResponse<T> {
  final bool success;
  final T? data;
  final String? message;
  final String? error;

  ApiResponse._({required this.success, this.data, this.message, this.error});

  factory ApiResponse.success(T data) {
    return ApiResponse._(success: true, data: data);
  }

  factory ApiResponse.error(String error) {
    return ApiResponse._(success: false, error: error);
  }

  factory ApiResponse.message(String message) {
    return ApiResponse._(success: true, message: message);
  }

  bool get isSuccess => success;
  bool get isError => !success;

  @override
  String toString() {
    return 'ApiResponse{success: $success, data: $data, message: $message, error: $error}';
  }
}
