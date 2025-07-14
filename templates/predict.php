<?php
// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: index.php?route=login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Prediction - Heart Disease Prediction</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="static/style.css" rel="stylesheet">
    <style>
        .predict-card {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary-color);
            animation: fadeIn 0.5s ease-out forwards;
        }

        .predict-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-control, .form-select {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 2px solid rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(78,115,223,0.1);
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
        }

        .btn-predict {
            background: linear-gradient(135deg, var(--primary-color), #224abe);
            border: none;
            padding: 0.75rem 2rem;
            color: white;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-predict:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(78,115,223,0.2);
        }

        .btn-clear {
            background: linear-gradient(135deg, var(--secondary-color), #6e707e);
            border: none;
            padding: 0.75rem 2rem;
            color: white;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-clear:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(133,135,150,0.2);
        }

        .result-section {
            display: none;
            margin-top: 2rem;
            padding: 2rem;
            border-radius: 15px;
            background: transparent !important;
            color: white;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .loading-spinner {
            display: none;
            text-align: center;
            padding: 2rem;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
            color: var(--primary-color);
        }

        .consultation-section {
            background: transparent !important;
            border-radius: 10px;
            padding: 1rem 0.5rem 1rem 0.5rem;
            margin-top: 1.5rem;
            box-shadow: none;
            /* max-height: 4.5em; */
            /* overflow: hidden; */
        }

        .consultation-section .consultation-header {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e3e6f0;
        }

        .consultation-section .consultation-header i {
            font-size: 1.5rem;
            color: #4e73df;
            margin-right: 0.5rem;
        }

        .consultation-section .consultation-header h5 {
            margin: 0;
            color: #2e59d9;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .consultation-section .consultation-content {
            font-size: 1.08rem;
            color: #222;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-weight: 500;
            line-height: 1.5;
            white-space: pre-line;
            word-break: break-word;
            padding: 0 0.5rem;
            max-width: 100%;
        }

        .consultation-section .consultation-content p {
            margin-bottom: 1rem;
        }

        .consultation-section .consultation-content strong {
            color: #2e59d9;
            font-weight: 600;
        }

        .consultation-section .consultation-content ul {
            list-style: none;
            padding-left: 0;
        }

        .consultation-section .consultation-content li {
            margin-bottom: 0.8rem;
            padding-left: 1.5rem;
            position: relative;
        }

        .consultation-section .consultation-content li:before {
            content: "•";
            color: #4e73df;
            font-weight: bold;
            position: absolute;
            left: 0;
        }

        .card {
            background: transparent !important;
            box-shadow: none !important;
        }

        .card-body {
            background: transparent !important;
        }
    </style>
</head>
<body>
    <!-- Sidebar Toggle Button -->
    <button id="sidebarCollapse" class="btn">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="sidebar-header">
            <h4 class="mb-0">Heart Disease</h4>
            <small>Prediction System</small>
        </div>
        
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a class="nav-link" href="index.php?route=dashboard">
                    <i class="fas fa-chart-line"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="index.php?route=predict">
                    <i class="fas fa-heartbeat"></i>
                    New Prediction
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="index.php?route=profile">
                    <i class="fas fa-user-circle"></i>
                    Profile
                </a>
            </li>
        </ul>

        <div class="profile-section mt-auto">
            <div class="d-flex align-items-center">
                <div class="profile-img">
                    <i class="fas fa-user"></i>
                </div>
                <div class="profile-info">
                    <h6 class="mb-0"><?php echo isset($_SESSION['user']['username']) ? htmlspecialchars($_SESSION['user']['username']) : 'User'; ?></h6>
                    <small><?php echo isset($_SESSION['user']['role']) ? htmlspecialchars($_SESSION['user']['role']) : 'User'; ?></small>
                </div>
            </div>
            <a href="index.php?route=logout" class="logout-btn mt-3">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div id="content">
        <div class="container-fluid">
                                <div class="row">
                <div class="col-lg-8">
                    <div class="predict-card">
                        <form id="predictionForm">
                            <div class="row g-3">
                                    <div class="col-md-6">
                                            <label class="form-label">Age</label>
                                    <input type="number" class="form-control" name="age" min="1" max="120" step="1" required>
                                    <small class="text-muted">Enter patient's age in years (1-120)</small>
                                    </div>
                                <div class="col-md-6">
                                    <label class="form-label">Sex</label>
                                    <select class="form-select" name="sex" required>
                                        <option value="">Select gender</option>
                                        <option value="1">Male</option>
                                        <option value="0">Female</option>
                                    </select>
                                    <small class="text-muted">Select patient's gender</small>
                                    </div>
                                <div class="col-md-6">
                                    <label class="form-label">Chest Pain Type</label>
                                    <select class="form-select" name="cp" required>
                                        <option value="">Select chest pain type</option>
                                        <option value="0">Typical angina</option>
                                        <option value="1">Atypical angina</option>
                                        <option value="2">Non-anginal pain</option>
                                        <option value="3">Asymptomatic</option>
                                    </select>
                                    <small class="text-muted">Select type of chest pain</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Resting Blood Pressure</label>
                                    <input type="number" class="form-control" name="trestbps" min="90" max="200" step="1" required>
                                    <small class="text-muted">Enter in mm Hg (90-200)</small>
                                    </div>
                                <div class="col-md-6">
                                            <label class="form-label">Cholesterol</label>
                                    <input type="number" class="form-control" name="chol" min="100" max="600" step="1" required>
                                    <small class="text-muted">Enter in mg/dl (100-600)</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fasting Blood Sugar</label>
                                    <select class="form-select" name="fbs" required>
                                        <option value="">Select blood sugar level</option>
                                        <option value="1">> 120 mg/dl</option>
                                        <option value="0">≤ 120 mg/dl</option>
                                    </select>
                                    <small class="text-muted">Select fasting blood sugar level</small>
                                    </div>
                                    <div class="col-md-6">
                                    <label class="form-label">Resting ECG</label>
                                    <select class="form-select" name="restecg" required>
                                        <option value="">Select ECG results</option>
                                        <option value="0">Normal</option>
                                        <option value="1">ST-T wave abnormality</option>
                                        <option value="2">Left ventricular hypertrophy</option>
                                    </select>
                                    <small class="text-muted">Select resting ECG results</small>
                                    </div>
                                <div class="col-md-6">
                                            <label class="form-label">Max Heart Rate</label>
                                    <input type="number" class="form-control" name="thalach" min="60" max="202" step="1" required>
                                    <small class="text-muted">Enter maximum heart rate (60-202)</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Exercise Induced Angina</label>
                                    <select class="form-select" name="exang" required>
                                        <option value="">Select angina status</option>
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                    <small class="text-muted">Select if exercise induced angina</small>
                                    </div>
                                <div class="col-md-6">
                                            <label class="form-label">ST Depression</label>
                                    <input type="number" class="form-control" name="oldpeak" min="0" max="6.2" step="0.1" required>
                                    <small class="text-muted">Enter ST depression (0-6.2)</small>
                                    </div>
                                <div class="col-md-6">
                                    <label class="form-label">Slope of Peak Exercise ST</label>
                                    <select class="form-select" name="slope" required>
                                        <option value="">Select slope</option>
                                        <option value="0">Upsloping</option>
                                        <option value="1">Flat</option>
                                        <option value="2">Downsloping</option>
                                    </select>
                                    <small class="text-muted">Select slope of peak exercise ST</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Number of Major Vessels</label>
                                    <select class="form-select" name="ca" required>
                                        <option value="">Select number of vessels</option>
                                        <option value="0">0 vessels</option>
                                        <option value="1">1 vessel</option>
                                        <option value="2">2 vessels</option>
                                        <option value="3">3 vessels</option>
                                    </select>
                                    <small class="text-muted">Select number of major vessels (0-3)</small>
                                    </div>
                                <div class="col-md-6">
                                    <label class="form-label">Thalassemia</label>
                                    <select class="form-select" name="thal" required>
                                        <option value="">Select thalassemia type</option>
                                        <option value="0">Normal</option>
                                        <option value="1">Fixed defect</option>
                                        <option value="2">Reversable defect</option>
                                        <option value="3">Not applicable</option>
                                    </select>
                                    <small class="text-muted">Select thalassemia type</small>
                                    </div>
                                <div class="col-12 mt-4">
                                    <div class="d-flex gap-3">
                                        <button type="submit" class="btn btn-predict">
                                            <i class="fas fa-heartbeat me-2"></i>Predict
                                        </button>
                                        <button type="reset" class="btn btn-clear">
                                            <i class="fas fa-undo me-2"></i>Clear Form
                                        </button>
                                    </div>
                                </div>
                                </div>
                            </form>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="predict-card">
                        <h5 class="mb-4">Prediction Result</h5>
                        <div class="loading-spinner">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-3 mb-0">Processing prediction...</p>
                        </div>
                        <div id="resultSection" class="result-section">
                            <!-- Will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Define generateConsultation at the top so it is available everywhere
    function generateConsultation(formData, isHighRisk) {
        let advice = '';
        let lifestyleTip = '';
        if (isHighRisk) {
            advice = 'Based on your risk factors, we strongly recommend scheduling a consultation with a cardiologist for a comprehensive evaluation.';
            lifestyleTip = 'Consider adopting a heart-healthy diet rich in fruits, vegetables, and whole grains, and aim for at least 150 minutes of moderate exercise weekly.';
        } else {
            advice = 'Your current risk factors suggest a low probability of heart disease. Continue maintaining a healthy lifestyle.';
            lifestyleTip = 'Keep up with regular exercise, maintain a balanced diet, and schedule regular check-ups with your healthcare provider.';
        }
        return `
            <p><strong>Medical Advice:</strong> ${advice}</p>
            <p><strong>Lifestyle Tip:</strong> ${lifestyleTip}</p>
        `;
    }

    $(document).ready(function() {
            // Sidebar Toggle
            $('#sidebarCollapse').on('click', function() {
                $('#sidebar').toggleClass('active');
            });

            // Show loading state
            function showLoading(element) {
                element.html(`
                    <div class="text-center p-4">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mb-0">Processing...</p>
                    </div>
                `);
            }

            // Show error state
            function showError(element, message) {
                element.html(`
                <div class="alert alert-danger m-3">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    ${message}
                </div>
            `);
            }

            $('#predictionForm').on('submit', function(e) {
                e.preventDefault();
                
                const resultSection = $('#resultSection');
                showLoading(resultSection);
                resultSection.show();
                
                // Collect form data
                const formData = {};
                $(this).serializeArray().forEach(item => {
                    // Ensure the value is a valid number
                    const value = parseFloat(item.value);
                    if (!isNaN(value)) {
                        formData[item.name] = value;
                    }
                });

                // Validate all required fields
                const requiredFields = ['age', 'sex', 'cp', 'trestbps', 'chol', 'fbs', 'restecg', 
                                      'thalach', 'exang', 'oldpeak', 'slope', 'ca', 'thal'];
                
                const missingFields = requiredFields.filter(field => !formData.hasOwnProperty(field));
                
                if (missingFields.length > 0) {
                    showError(resultSection, 'Please fill in all required fields: ' + missingFields.join(', '));
                    return;
                }

                // Send prediction request
        $.ajax({
            url: 'index.php?route=predict',
            method: 'POST',
                    data: JSON.stringify(formData),
                    contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const isHighRisk = response.prediction === 1;
                    
                    // Save prediction to database first
                    $.ajax({
                        url: 'index.php?route=save_prediction',
                        method: 'POST',
                        data: {
                                    prediction_data: JSON.stringify(formData),
                                    prediction_result: response.prediction,
                                    user_id: <?php echo isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : 'null'; ?>
                        },
                        success: function(saveResponse) {
                                    if (saveResponse.success) {
                                        console.log('Prediction saved successfully');
                                        const predictionId = saveResponse.prediction_id;
                                        
                                        // Now show the result with the correct prediction ID
                                        showPredictionResult(formData, response, predictionId, isHighRisk);
                                    } else {
                                        showError(resultSection, 'Failed to save prediction. Please try again.');
                                    }
                                },
                                error: function() {
                                    showError(resultSection, 'Failed to save prediction. Please try again.');
                                }
                            });
                        } else {
                            showError(resultSection, response.message || 'Prediction failed. Please try again.');
                        }
                    },
                    error: function(xhr, status, error) {
                        showError(resultSection, 'An error occurred while processing your request. Please try again.');
                    }
                });
            });
        });

        // Function to show prediction result after saving
        function showPredictionResult(formData, response, predictionId, isHighRisk) {
            const resultSection = $('#resultSection');
            
            resultSection.html(`
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title text-center mb-4">Prediction Result</h4>
                        <div class="text-center mb-4">
                            <h2 class="${isHighRisk ? 'text-danger' : 'text-success'}">
                                ${isHighRisk ? 'Positive' : 'Negative'}
                            </h2>
                        </div>
                        ${isHighRisk ? `
                            <div class="alert alert-warning mb-4">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-exclamation-triangle me-3" style="font-size: 24px;"></i>
                                    <div>
                                        <h5 class="mb-1">Medical Consultation Recommended</h5>
                                        <p class="mb-0">Based on your prediction result, we strongly recommend scheduling a consultation with our medical experts for a thorough evaluation.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center mb-4">
                                <button class="btn btn-danger btn-lg" onclick="bookAppointment(${predictionId}, ${response.prediction})">
                                    <i class="fas fa-calendar-plus me-2"></i>Book Consultation
                                </button>
                            </div>
                        ` : `
                            <div class="alert alert-success mb-4">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-check-circle me-3" style="font-size: 24px;"></i>
                                    <div>
                                        <h5 class="mb-1">Low Risk Result</h5>
                                        <p class="mb-0">Your prediction indicates a low risk of heart disease. Continue maintaining a healthy lifestyle!</p>
                                    </div>
                                </div>
                            </div>
                        `}
                        <div class="consultation-section">
                            <div class="consultation-header">
                                <i class="fas fa-stethoscope"></i>
                                <h5>Medical Consultation</h5>
                            </div>
                            <div id="consultationText" class="consultation-content">
                                ${generateConsultation(formData, isHighRisk)}
                            </div>
                        </div>
                    </div>
                </div>
            `);
        }

        // Function to redirect to appointment booking
        function bookAppointment(predictionId, result) {
            if (!predictionId || predictionId === 'null' || predictionId === 'pending') {
                alert('Prediction data is not ready. Please try again in a moment.');
                return;
            }
            window.location.href = `index.php?route=book_appointment&prediction_id=${predictionId}&result=${result}`;
        }
    </script>
</body>
</html> 