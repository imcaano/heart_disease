<?php
// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: index.php?route=login');
    exit;
}

// Check if prediction_id is provided
$prediction_id = $_GET['prediction_id'] ?? null;
$prediction_result = $_GET['result'] ?? null;

// If no prediction data, redirect to predict page
if (!$prediction_id || !$prediction_result || $prediction_id === 'null' || $prediction_id === 'pending') {
    header('Location: index.php?route=predict');
    exit;
}

// Get user's latest prediction details for better pre-filling
$user_id = $_SESSION['user']['id'];
$prediction_details = null;

try {
    require_once 'config.php';
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get prediction details if prediction_id is valid
    if ($prediction_id !== 'null' && $prediction_id !== 'pending') {
        $stmt = $pdo->prepare("SELECT * FROM predictions WHERE id = ? AND user_id = ?");
        $stmt->execute([$prediction_id, $user_id]);
        $prediction_details = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // If prediction not found, redirect to predict page
        if (!$prediction_details) {
            header('Location: index.php?route=predict');
            exit;
        }
    }
} catch (PDOException $e) {
    // Silently handle database errors
    error_log("Error fetching prediction details: " . $e->getMessage());
    header('Location: index.php?route=predict');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Consultation - Heart Disease Prediction</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="static/style.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4e73df;
            --success-color: #1cc88a;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --secondary-color: #858796;
            --light-color: #f8f9fc;
            --dark-color: #5a5c69;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }

        .appointment-card {
            background: #fff;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            animation: slideInUp 0.6s ease-out;
        }

        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-warning {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            border: 1px solid #ffeaa7;
            color: #856404;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .form-control, .form-select {
            border-radius: 12px;
            padding: 12px 16px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
            font-size: 16px;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(78,115,223,0.1);
        }

        .form-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 8px;
        }

        .btn-book {
            background: linear-gradient(135deg, var(--primary-color), #224abe);
            border: none;
            padding: 15px 30px;
            color: white;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-book:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(78,115,223,0.3);
            color: white;
        }

        .btn-secondary {
            background: linear-gradient(135deg, var(--secondary-color), #6e707e);
            border: none;
            padding: 15px 30px;
            color: white;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(133,135,150,0.3);
            color: white;
        }

        .calendar-icon {
            color: var(--primary-color);
            font-size: 24px;
            margin-right: 10px;
        }

        .time-slots {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }

        .time-slot {
            padding: 10px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #fff;
        }

        .time-slot:hover {
            border-color: var(--primary-color);
            background: #f8f9ff;
        }

        .time-slot.selected {
            border-color: var(--primary-color);
            background: var(--primary-color);
            color: white;
        }

        .time-slot.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: #f8f9fc;
        }

        .success-message {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            border: 1px solid #c3e6cb;
            color: #155724;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            margin-bottom: 30px;
        }

        .error-message {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            border: 1px solid #f5c6cb;
            color: #721c24;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="appointment-card">
                    <div class="text-center mb-4">
                        <i class="fas fa-calendar-plus calendar-icon"></i>
                        <h2 class="mb-2">Book Your Consultation</h2>
                        <p class="text-muted">Schedule an appointment with our medical experts</p>
                    </div>

                    <div class="alert-warning">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle me-3" style="font-size: 24px;"></i>
                            <div>
                                <h5 class="mb-1">Important Notice</h5>
                                <p class="mb-0">Based on your heart disease prediction result (<?php echo $prediction_result == 1 ? 'High Risk' : 'Low Risk'; ?>), we strongly recommend scheduling a consultation with our medical experts for a thorough evaluation and personalized care plan.</p>
                            </div>
                        </div>
                    </div>

                    <div id="successMessage" class="success-message" style="display: none;">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Appointment booked successfully!</strong>
                        <p class="mb-0 mt-2">We've sent a confirmation email to your registered email address. Our team will review your appointment and get back to you within 24 hours.</p>
                    </div>

                    <div id="errorMessage" class="error-message" style="display: none;">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Booking failed!</strong>
                        <p class="mb-0 mt-2" id="errorText"></p>
                    </div>

                    <form id="appointmentForm">
                        <input type="hidden" name="prediction_id" value="<?php echo htmlspecialchars($prediction_id); ?>">
                        <input type="hidden" name="prediction_result" value="<?php echo htmlspecialchars($prediction_result); ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="patient_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="patient_name" name="patient_name" required 
                                       value="<?php echo htmlspecialchars($_SESSION['user']['username'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="patient_email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="patient_email" name="patient_email" required
                                       value="<?php echo htmlspecialchars($_SESSION['user']['email'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="patient_phone" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control" id="patient_phone" name="patient_phone" required 
                                       placeholder="Enter your phone number">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="appointment_date" class="form-label">Preferred Date *</label>
                                <input type="date" class="form-control" id="appointment_date" name="appointment_date" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Preferred Time *</label>
                            <div class="time-slots">
                                <div class="time-slot" data-time="09:00">9:00 AM</div>
                                <div class="time-slot" data-time="10:00">10:00 AM</div>
                                <div class="time-slot" data-time="11:00">11:00 AM</div>
                                <div class="time-slot" data-time="14:00">2:00 PM</div>
                                <div class="time-slot" data-time="15:00">3:00 PM</div>
                                <div class="time-slot" data-time="16:00">4:00 PM</div>
                            </div>
                            <input type="hidden" id="appointment_time" name="appointment_time" required>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Address *</label>
                            <textarea class="form-control" id="address" name="address" rows="3" required 
                                      placeholder="Enter your full address"><?php echo htmlspecialchars($_SESSION['user']['wallet_address'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="reason" class="form-label">Reason for Consultation *</label>
                            <textarea class="form-control" id="reason" name="reason" rows="3" required 
                                      placeholder="Please describe your symptoms or concerns">Based on my heart disease prediction result showing <?php echo $prediction_result == 1 ? 'high risk (Positive)' : 'low risk (Negative)'; ?>, I would like to schedule a consultation with a medical expert for a thorough evaluation and personalized care plan.<?php if ($prediction_details): ?> My recent prediction showed concerning risk factors that require professional medical attention.<?php endif; ?></textarea>
                        </div>

                        <div class="d-flex gap-3 justify-content-center">
                            <button type="submit" class="btn btn-book">
                                <i class="fas fa-calendar-check me-2"></i>Book Appointment
                            </button>
                            <a href="index.php?route=predict" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Predict
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Set minimum date to today
            const today = new Date().toISOString().split('T')[0];
            $('#appointment_date').attr('min', today);

            // Time slot selection
            $('.time-slot').click(function() {
                if (!$(this).hasClass('disabled')) {
                    $('.time-slot').removeClass('selected');
                    $(this).addClass('selected');
                    $('#appointment_time').val($(this).data('time'));
                }
            });

            // Form submission
            $('#appointmentForm').submit(function(e) {
                e.preventDefault();
                
                // Validate time selection
                if (!$('#appointment_time').val()) {
                    showError('Please select a preferred time.');
                    return;
                }

                // Format date properly for MySQL (YYYY-MM-DD)
                const dateInput = $('#appointment_date').val();
                if (dateInput) {
                    const date = new Date(dateInput);
                    const formattedDate = date.toISOString().split('T')[0];
                    $('#appointment_date').val(formattedDate);
                }

                const formData = $(this).serialize();
                
                // Show loading state
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Booking...').prop('disabled', true);
                
                $.ajax({
                    url: 'index.php?route=book_appointment',
                    method: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showSuccess();
                            $('#appointmentForm')[0].reset();
                            $('.time-slot').removeClass('selected');
                        } else {
                            showError(response.message || 'Failed to book appointment. Please try again.');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', xhr.responseText);
                        showError('An error occurred. Please try again later. Error: ' + error);
                    },
                    complete: function() {
                        // Restore button state
                        submitBtn.html(originalText).prop('disabled', false);
                    }
                });
            });

            function showSuccess() {
                $('#successMessage').show();
                $('#errorMessage').hide();
                $('html, body').animate({
                    scrollTop: $('#successMessage').offset().top - 100
                }, 500);
            }

            function showError(message) {
                $('#errorText').text(message);
                $('#errorMessage').show();
                $('#successMessage').hide();
                $('html, body').animate({
                    scrollTop: $('#errorMessage').offset().top - 100
                }, 500);
            }
        });
    </script>
</body>
</html> 