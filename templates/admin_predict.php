<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Predict - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --light-color: #f8f9fc;
            --dark-color: #5a5c69;
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-color);
            color: var(--dark-color);
            overflow-x: hidden;
        }

        #sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: linear-gradient(180deg, var(--primary-color) 0%, #224abe 100%);
            transition: var(--transition);
            z-index: 1000;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        #content {
            margin-left: 250px;
            padding: 20px;
            transition: var(--transition);
        }

        .sidebar-header {
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            padding: 15px 20px;
            transition: var(--transition);
            border-radius: 5px;
            margin: 5px 10px;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: #fff !important;
            transform: translateX(5px);
        }

        .nav-link i {
            width: 24px;
            height: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 6px;
            margin-right: 10px;
            transition: var(--transition);
        }

        .nav-link:hover i {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.1);
        }

        .predict-card {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: var(--transition);
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

        .profile-section {
            padding: 20px;
            color: #fff;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: auto;
        }

        .profile-section .profile-img {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            margin-right: 10px;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .form-control, .form-select {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 2px solid rgba(0,0,0,0.05);
            transition: var(--transition);
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
            transition: var(--transition);
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
            transition: var(--transition);
        }

        .btn-clear:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(133,135,150,0.2);
        }

        .result-section {
            background: transparent !important;
        }

        .card {
            background: transparent !important;
            box-shadow: none !important;
        }

        .card-body {
            background: transparent !important;
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
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="sidebar-header">
            <h4 class="text-white mb-0">Heart Disease</h4>
            <small class="text-white-50">Prediction System</small>
        </div>
        
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a class="nav-link" href="index.php?route=admin_dashboard">
                    <i class="fas fa-chart-line"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="index.php?route=admin_predict">
                    <i class="fas fa-heartbeat"></i>
                    Predict
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="index.php?route=admin_appointments">
                    <i class="fas fa-calendar-check"></i>
                    Appointments
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="index.php?route=manage_users">
                    <i class="fas fa-users"></i>
                 Users
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="index.php?route=admin_reports">
                    <i class="fas fa-file-alt"></i>
                    Reports
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="index.php?route=admin_profile">
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
                <div>
                    <h6 class="mb-0 text-white"><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin'; ?></h6>
                    <small class="text-white-50"><?php echo isset($_SESSION['user']['role']) ? htmlspecialchars($_SESSION['user']['role']) : 'Administrator'; ?></small>
                </div>
            </div>
            <a href="index.php?route=logout" class="btn btn-danger mt-3 w-100">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div id="content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Heart Disease Prediction</h2>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="predict-card">
                        <form id="predictionForm">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Age</label>
                                    <input type="number" class="form-control" name="age" required placeholder="Enter age (e.g., 45)">
                                    <small class="text-muted">Typical range: 20-80 years</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Sex</label>
                                    <select class="form-select" name="sex" required>
                                        <option value="">Select gender</option>
                                        <option value="1">Male</option>
                                        <option value="0">Female</option>
                                    </select>
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
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Resting Blood Pressure (mm Hg)</label>
                                    <input type="number" class="form-control" name="trestbps" required placeholder="Enter blood pressure (e.g., 120)">
                                    <small class="text-muted">Typical range: 90-200 mm Hg</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Serum Cholesterol (mg/dl)</label>
                                    <input type="number" class="form-control" name="chol" required placeholder="Enter cholesterol (e.g., 200)">
                                    <small class="text-muted">Typical range: 120-600 mg/dl</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fasting Blood Sugar</label>
                                    <select class="form-select" name="fbs" required>
                                        <option value="">Select fasting blood sugar</option>
                                        <option value="1">> 120 mg/dl</option>
                                        <option value="0">≤ 120 mg/dl</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Resting ECG Results</label>
                                    <select class="form-select" name="restecg" required>
                                        <option value="">Select ECG results</option>
                                        <option value="0">Normal</option>
                                        <option value="1">ST-T wave abnormality</option>
                                        <option value="2">Left ventricular hypertrophy</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Maximum Heart Rate (beats/min)</label>
                                    <input type="number" class="form-control" name="thalach" required placeholder="Enter heart rate (e.g., 150)">
                                    <small class="text-muted">Typical range: 60-200 beats/min</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Exercise Induced Angina</label>
                                    <select class="form-select" name="exang" required>
                                        <option value="">Select exercise induced angina</option>
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">ST Depression</label>
                                    <input type="number" class="form-control" name="oldpeak" step="0.1" required placeholder="Enter ST depression (e.g., 2.3)">
                                    <small class="text-muted">Typical range: 0.0-6.2</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Slope of Peak Exercise ST Segment</label>
                                    <select class="form-select" name="slope" required>
                                        <option value="">Select slope</option>
                                        <option value="0">Upsloping</option>
                                        <option value="1">Flat</option>
                                        <option value="2">Downsloping</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Number of Major Vessels</label>
                                    <select class="form-select" name="ca" required>
                                        <option value="">Select number of vessels</option>
                                        <option value="0">0</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Thalassemia</label>
                                    <select class="form-select" name="thal" required>
                                        <option value="">Select thalassemia type</option>
                                        <option value="1">Normal</option>
                                        <option value="2">Fixed defect</option>
                                        <option value="3">Reversable defect</option>
                                    </select>
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
    <script src="https://js.puter.com/v2/"></script>
    <script>
        $(document).ready(function() {
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
                
                // Convert form data to JSON object
                const formData = {};
                $(this).serializeArray().forEach(function(item) {
                    formData[item.name] = parseFloat(item.value);
                });
                
                // Send AJAX request
                $.ajax({
                    url: 'index.php?route=admin_predict',
                    method: 'POST',
                    data: JSON.stringify(formData),
                    dataType: 'json',
                    contentType: 'application/json',
                    success: function(response) {
                        if (response.success) {
                            const isPositive = response.prediction === 1;
                            
                            // Save prediction to database
                            $.ajax({
                                url: 'api/save_prediction.php',
                                method: 'POST',
                                data: {
                                    prediction_data: JSON.stringify(formData),
                                    prediction_result: response.prediction,
                                    user_id: <?php echo isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : 'null'; ?>
                                },
                                success: function(saveResponse) {
                                    if (saveResponse.success) {
                                        console.log('Prediction saved successfully');
                                        updateDashboardData();
                                    }
                                    }
                            });
                            
                            // Show prediction result
                            resultSection.html(`
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title text-center mb-4">Prediction Result</h4>
                                        <div class="text-center mb-4">
                                            <h2 class="${isPositive ? 'text-danger' : 'text-success'}">
                                                ${isPositive ? 'Positive' : 'Negative'}
                                            </h2>
                                        </div>
                                        <div class="consultation-section">
                                            <div class="consultation-header">
                                                <i class="fas fa-stethoscope"></i>
                                                <h5>Medical Consultation</h5>
                                            </div>
                                            <div id="consultationText" class="consultation-content">
                                                <div class="text-center">
                                                    <div class="spinner-border text-primary" role="status">
                                                        <span class="visually-hidden">Loading...</span>
                                                    </div>
                                                    <p class="mt-2">Generating consultation...</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `);

                            // Get consultation using OpenRouter API
                            const prompt = `As a medical expert, provide a single, concise sentence with the most important advice for this heart disease prediction. Do NOT repeat or list the input data. Only give one actionable medical advice point in plain English, and add a short, practical healthy lifestyle tip (such as exercise, diet, or stress).\n\n` +
                                `Age: ${formData.age}\n` +
                                `Sex: ${formData.sex == 1 ? 'Male' : 'Female'}\n` +
                                `Chest Pain Type: ${formData.cp}\n` +
                                `Resting Blood Pressure: ${formData.trestbps} mm Hg\n` +
                                `Cholesterol: ${formData.chol} mg/dl\n` +
                                `Fasting Blood Sugar: ${formData.fbs == 1 ? 'High (>120 mg/dl)' : 'Normal (≤120 mg/dl)'}\n` +
                                `ECG Results: ${formData.restecg}\n` +
                                `Maximum Heart Rate: ${formData.thalach} beats/min\n` +
                                `Exercise Induced Angina: ${formData.exang == 1 ? 'Yes' : 'No'}\n` +
                                `ST Depression: ${formData.oldpeak}\n` +
                                `Slope: ${formData.slope}\n` +
                                `Number of Major Vessels: ${formData.ca}\n` +
                                `Thalassemia: ${formData.thal}\n\n` +
                                `Prediction Result: ${isPositive ? 'High Risk' : 'Low Risk'}\n`;

                            // Use OpenRouter API
                            $.ajax({
                                url: 'api/consultation_openrouter.php',
                                method: 'POST',
                                contentType: 'application/json',
                                dataType: 'json',
                                data: JSON.stringify({ prompt }),
                                success: function(res) {
                                    console.log('Consultation API response:', res);
                                    if (res.success) {
                                        $('#consultationText').html(res.consultation);
                                    } else {
                                        showError($('#consultationText'), res.message || 'Unable to generate consultation at this time.');
                                    }
                                },
                                error: function(xhr, status, error) {
                                    showError($('#consultationText'), 'Unable to generate consultation at this time. Please try again later.');
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

            // Function to update dashboard data
            function updateDashboardData() {
                $.get('api/dashboard_stats.php', function(data) {
                    if (data.success) {
                        $('#totalPredictions').text(data.totalPredictions);
                        $('#positivePredictions').text(data.positivePredictions);
                        $('#negativePredictions').text(data.negativePredictions);
                    }
                });
            }
        });
    </script>
</body>
</html> 