<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Heart Disease Prediction - AI Expert System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
            --background-color: #f8f9fc;
            --dark-color: #2e384d;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
            background: linear-gradient(135deg, var(--background-color) 0%, #ffffff 100%);
            position: relative;
            margin: 0;
            padding: 0;
        }

        .container {
            overflow-x: hidden;
        }

        /* Decorative Shapes */
        .shape-blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(40px);
            z-index: -1;
            opacity: 0.4;
        }

        .shape-blob-1 {
            width: 400px;
            height: 400px;
            background: linear-gradient(45deg, var(--primary-color), #2e59d9);
            top: -100px;
            right: -100px;
            animation: float 8s ease-in-out infinite;
        }

        .shape-blob-2 {
            width: 300px;
            height: 300px;
            background: linear-gradient(45deg, var(--success-color), #15a173);
            bottom: 10%;
            left: -100px;
            animation: float 10s ease-in-out infinite;
        }

        .shape-circle {
            position: absolute;
            border: 2px solid rgba(78, 115, 223, 0.1);
            border-radius: 50%;
        }

        .shape-circle-1 {
            width: 100px;
            height: 100px;
            top: 20%;
            left: 10%;
            animation: spin 15s linear infinite;
        }

        .shape-circle-2 {
            width: 150px;
            height: 150px;
            bottom: 15%;
            right: 5%;
            animation: spin 20s linear infinite reverse;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Modern Navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            background: rgba(255, 255, 255, 0.98);
            padding: 0.8rem 0;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-link {
            color: var(--dark-color) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--primary-color);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-link:hover::after {
            width: 80%;
        }

        .btn-login, .btn-signup {
            padding: 0.6rem 1.8rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
        }

        .btn-login {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            margin-right: 1rem;
            background: transparent;
        }

        .btn-signup {
            background: linear-gradient(135deg, var(--primary-color), #2e59d9);
            color: white;
            border: none;
        }

        .btn-login:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        .btn-signup:hover {
            background: linear-gradient(135deg, #2e59d9, #1e49c9);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(78, 115, 223, 0.4);
        }

        /* Hero Section */
        .hero {
            padding: 10rem 0 6rem;
            background: linear-gradient(135deg, #fff 0%, #f8f9fc 100%);
            position: relative;
            overflow: hidden;
        }

        .hero h1 {
            font-size: 4rem;
            font-weight: 800;
            color: var(--dark-color);
            margin-bottom: 1.5rem;
            line-height: 1.2;
            position: relative;
            z-index: 1;
            background: linear-gradient(135deg, var(--dark-color), var(--primary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero p {
            font-size: 1.25rem;
            color: var(--secondary-color);
            margin-bottom: 2rem;
            line-height: 1.8;
            position: relative;
            z-index: 1;
        }

        /* Features Section */
        .features {
            padding: 6rem 0;
            background: white;
        }

        .feature-card {
            padding: 2.5rem;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }

        .feature-card::after {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, rgba(78, 115, 223, 0.03), rgba(28, 200, 138, 0.03));
            border-radius: 50%;
            top: -100%;
            left: -100%;
            transition: all 0.8s ease;
            opacity: 0;
        }

        .feature-card:hover::after {
            opacity: 1;
            transform: scale(1.2) rotate(45deg);
        }

        .feature-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--dark-color);
        }

        .feature-card p {
            color: var(--secondary-color);
            line-height: 1.7;
            margin-bottom: 0;
        }

        /* Perfect Icons Set */
        .icon-perfect {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 3rem;
            height: 3rem;
            border-radius: 50%;
            background: rgba(78, 115, 223, 0.1);
            color: var(--primary-color);
            margin-right: 1rem;
            transition: all 0.3s ease;
        }

        .icon-perfect:hover {
            background: var(--primary-color);
            color: white;
            transform: scale(1.1);
        }

        /* Call to Action */
        .cta {
            padding: 6rem 0;
            background: linear-gradient(135deg, var(--primary-color), #2e59d9);
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .cta::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,112C672,96,768,96,864,112C960,128,1056,160,1152,160C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
            background-size: cover;
            opacity: 0.1;
        }

        .cta h2 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        .cta p {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .btn-cta {
            background: white;
            color: var(--primary-color);
            padding: 1rem 2.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-cta:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            background: var(--background-color);
        }

        /* Animations */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }

        .floating {
            animation: float 3s ease-in-out infinite;
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .hero p {
                font-size: 1.1rem;
            }
            
            .feature-card {
                margin-bottom: 2rem;
            }
            
            .cta h2 {
                font-size: 2rem;
            }
        }

        /* Enhanced Shapes and Icons */
        .shape-expert {
            position: absolute;
            z-index: -1;
            pointer-events: none;
            overflow: hidden;
        }

        .shape-expert-1 {
            width: 300px;
            height: 300px;
            background: linear-gradient(45deg, rgba(78, 115, 223, 0.1), rgba(28, 200, 138, 0.1));
            border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
            top: 10%;
            right: 5%;
            animation: morphShape 15s ease-in-out infinite;
        }

        .shape-expert-2 {
            width: 250px;
            height: 250px;
            background: linear-gradient(45deg, rgba(28, 200, 138, 0.1), rgba(78, 115, 223, 0.1));
            border-radius: 58% 42% 38% 62% / 42% 55% 45% 58%;
            bottom: 10%;
            left: 5%;
            animation: morphShape 12s ease-in-out infinite reverse;
        }

        @keyframes morphShape {
            0% { border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%; }
            50% { border-radius: 58% 42% 38% 62% / 42% 55% 45% 58%; }
            100% { border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%; }
        }

        .icon-expert {
            position: relative;
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .icon-expert::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary-color), #2e59d9);
            border-radius: 16px;
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 0;
        }

        .icon-expert i {
            font-size: 1.8rem;
            color: var(--primary-color);
            position: relative;
            z-index: 1;
            transition: all 0.3s ease;
        }

        .icon-expert:hover {
            transform: translateY(-5px);
        }

        .icon-expert:hover::before {
            opacity: 1;
        }

        .icon-expert:hover i {
            color: white;
        }

        /* Hero Icons Animation */
        .hero-icons-container {
            position: relative;
            width: 100%;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .hero-icon {
            position: absolute;
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            animation: iconFloat 6s ease-in-out infinite;
        }

        .hero-icon i {
            font-size: 2rem;
            color: var(--primary-color);
            transition: all 0.3s ease;
        }

        .hero-icon::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--primary-color), #2e59d9);
            opacity: 0;
            transition: all 0.3s ease;
        }

        .hero-icon:hover::before {
            opacity: 1;
        }

        .hero-icon:hover i {
            color: white;
            transform: scale(1.1);
        }

        .hero-icon:nth-child(1) {
            top: 20%;
            left: 20%;
            animation-delay: 0s;
        }

        .hero-icon:nth-child(2) {
            top: 40%;
            right: 25%;
            animation-delay: 1s;
        }

        .hero-icon:nth-child(3) {
            bottom: 30%;
            left: 35%;
            animation-delay: 2s;
        }

        .hero-icon:nth-child(4) {
            top: 50%;
            right: 30%;
            animation-delay: 3s;
        }

        .hero-icon:nth-child(5) {
            bottom: 20%;
            right: 20%;
            animation-delay: 4s;
        }

        @keyframes iconFloat {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-heartbeat me-2"></i>
                HeartGuard AI
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <a class="btn btn-login" href="index.php?route=login">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-signup" href="index.php?route=signup">Sign Up</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1>Predict Heart Disease with AI</h1>
                    <p>Our advanced AI system helps you predict heart disease risk with high accuracy. Get instant results and personalized recommendations to maintain a healthy heart.</p>
                    <a href="index.php?route=signup" class="btn btn-signup btn-lg">Get Started</a>
                </div>
                <div class="col-lg-6">
                    <div class="hero-icons-container">
                        <div class="hero-icon">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                        <div class="hero-icon">
                            <i class="fas fa-stethoscope"></i>
                        </div>
                        <div class="hero-icon">
                            <i class="fas fa-brain"></i>
                        </div>
                        <div class="hero-icon">
                            <i class="fas fa-shield-virus"></i>
                        </div>
                        <div class="hero-icon">
                            <i class="fas fa-notes-medical"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Add shapes to the hero section -->
    <div class="shape-blob shape-blob-1"></div>
    <div class="shape-blob shape-blob-2"></div>
    <div class="shape-circle shape-circle-1"></div>
    <div class="shape-circle shape-circle-2"></div>

    <!-- Add expert shapes -->
    <div class="shape-expert shape-expert-1"></div>
    <div class="shape-expert shape-expert-2"></div>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="h1 mb-3">Why Choose HeartGuard AI?</h2>
                    <p class="text-muted">Experience the power of AI in predicting heart disease risk factors</p>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="feature-card">
                        <div class="icon-expert">
                            <i class="fas fa-brain"></i>
                        </div>
                        <div class="icon-expert">
                            <i class="fas fa-microchip"></i>
                        </div>
                        <h3>AI-Powered Analysis</h3>
                        <p>Advanced machine learning algorithms analyze multiple risk factors to provide accurate predictions.</p>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="feature-card">
                        <div class="icon-expert">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="icon-expert">
                            <i class="fas fa-lock"></i>
                        </div>
                        <h3>Secure & Private</h3>
                        <p>Your health data is encrypted and protected with blockchain technology for maximum security.</p>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="feature-card">
                        <div class="icon-expert">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="icon-expert">
                            <i class="fas fa-analytics"></i>
                        </div>
                        <h3>Real-time Results</h3>
                        <p>Get instant predictions and track your heart health progress over time.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h2>Ready to Take Control of Your Heart Health?</h2>
                    <p>Join thousands of users who trust HeartGuard AI for their heart health predictions.</p>
                    <a href="index.php?route=signup" class="btn btn-cta">Start Now</a>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                document.querySelector('.navbar').classList.add('scrolled');
            } else {
                document.querySelector('.navbar').classList.remove('scrolled');
            }
        });
    </script>
</body>
</html>