# Heart Disease Prediction System - Complete Overview

## System Architecture

This is a comprehensive heart disease prediction system with both web and mobile applications, featuring AI-powered predictions, blockchain integration, and advanced analytics.

## 🏗️ System Components

### 1. Web Application (PHP/HTML/CSS/JavaScript)
- **Location**: Root directory
- **Technology**: PHP, MySQL, Bootstrap, JavaScript
- **Features**: Full-featured web interface with admin panel

### 2. Mobile Application (Flutter)
- **Location**: `flutter_app/` directory
- **Technology**: Flutter, Dart, Provider pattern
- **Features**: Native mobile experience with blockchain integration

### 3. Backend API (PHP)
- **Location**: `api/` directory
- **Technology**: PHP, MySQL, Python integration
- **Features**: RESTful API endpoints for both web and mobile

### 4. Machine Learning Model (Python)
- **Location**: Root directory (`predict.py`, `train_model.py`)
- **Technology**: Python, scikit-learn, joblib
- **Features**: Heart disease prediction model

## 📁 Project Structure

```
heart_disease/
├── flutter_app/                 # Flutter mobile application
│   ├── lib/
│   │   ├── core/               # Core functionality
│   │   │   ├── models/         # Data models
│   │   │   ├── providers/      # State management
│   │   │   ├── services/       # API services
│   │   │   ├── theme/          # App theming
│   │   │   └── routes/         # Navigation
│   │   ├── features/           # Feature modules
│   │   │   ├── auth/           # Authentication
│   │   │   ├── dashboard/      # User dashboard
│   │   │   ├── prediction/     # Heart disease prediction
│   │   │   ├── profile/        # User profile
│   │   │   ├── reports/        # Reports and analytics
│   │   │   ├── admin/          # Admin features
│   │   │   └── splash/         # App initialization
│   │   └── main.dart           # App entry point
│   ├── assets/                 # App assets
│   └── pubspec.yaml           # Dependencies
├── api/                        # Backend API endpoints
│   ├── login.php              # Authentication
│   ├── signup.php             # User registration
│   ├── predict.php            # Heart disease prediction
│   ├── chatgpt_consultation.php # AI consultation
│   ├── dashboard_stats.php    # Dashboard statistics
│   ├── reports.php            # User reports
│   ├── save_prediction.php    # Save predictions
│   ├── get_users.php          # Admin: get users
│   ├── update_user.php        # Admin: update user
│   ├── delete_user.php        # Admin: delete user
│   ├── import_dataset.php     # Admin: import datasets
│   └── ...                    # Other API endpoints
├── templates/                  # Web application templates
│   ├── index.php              # Landing page
│   ├── login.php              # Login page
│   ├── signup.php             # Signup page
│   ├── dashboard.php          # User dashboard
│   ├── predict.php            # Prediction form
│   ├── admin_dashboard.php    # Admin dashboard
│   ├── manage_users.php       # User management
│   ├── admin_reports.php      # Admin reports
│   └── ...                    # Other templates
├── static/                     # Static assets
│   ├── style.css              # Main stylesheet
│   └── js/                    # JavaScript files
├── controllers/                # Web application controllers
│   ├── dashboard.php          # Dashboard logic
│   ├── reports.php            # Reports logic
│   └── dataset.php            # Dataset management
├── cron/                       # Scheduled tasks
│   └── generate_reports.php   # Automated report generation
├── config/                     # Configuration files
│   └── database.php           # Database configuration
├── models/                     # Machine learning models
│   └── best_heart_model.joblib # Trained model
├── predict.py                  # Python prediction script
├── train_model.py             # Model training script
├── heart_disease.sql          # Database schema
├── config.php                 # Main configuration
├── index.php                  # Web application entry point
└── README.md                  # Project documentation
```

## 🔧 Technology Stack

### Frontend Technologies
- **Web**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Mobile**: Flutter 3.0+, Dart, Material Design 3
- **State Management**: Provider pattern (Flutter)
- **Charts**: Fl Chart, Syncfusion Charts

### Backend Technologies
- **API**: PHP 8.0+, RESTful architecture
- **Database**: MySQL 8.0+
- **Machine Learning**: Python 3.8+, scikit-learn, joblib
- **AI Consultation**: OpenAI GPT API, Hugging Face

### Blockchain Integration
- **Web3**: Web3dart (Flutter), MetaMask integration
- **Smart Contracts**: Ethereum-based contracts
- **Digital Signatures**: Message signing and verification

### Development Tools
- **Version Control**: Git
- **Package Management**: Composer (PHP), pub (Flutter)
- **Development Server**: XAMPP, Flutter DevTools

## 🚀 Key Features

### 1. Heart Disease Prediction
- **13 Medical Parameters**: Age, sex, chest pain type, blood pressure, cholesterol, etc.
- **AI Model**: Machine learning model trained on UCI Heart Disease dataset
- **Real-time Results**: Instant prediction with confidence scores
- **Medical Consultation**: AI-generated medical advice

### 2. Authentication & Security
- **Multi-factor Authentication**: Username, password, and wallet address
- **Blockchain Integration**: MetaMask wallet authentication
- **Role-based Access**: Admin and user roles
- **Secure Storage**: Encrypted local storage

### 3. User Management
- **User Registration**: Email and wallet-based registration
- **Profile Management**: User profile updates and statistics
- **Admin Panel**: Complete user management system
- **Activity Tracking**: User activity and prediction history

### 4. Analytics & Reporting
- **Dashboard Analytics**: Real-time statistics and charts
- **Prediction History**: Complete prediction records
- **Export Reports**: PDF and Excel report generation
- **System Monitoring**: Admin dashboard with system metrics

### 5. Dataset Management
- **Bulk Import**: CSV dataset import functionality
- **Data Validation**: Comprehensive data validation
- **Model Training**: Automated model retraining
- **Data Export**: Export prediction data

## 🔄 Data Flow

### 1. User Authentication Flow
```
User Input → Web/Mobile App → API → Database → Blockchain Verification → Response
```

### 2. Prediction Flow
```
Medical Data → Form Validation → API → Python Model → Prediction Result → Database → AI Consultation → Response
```

### 3. Admin Management Flow
```
Admin Action → API → Database → Validation → Response → UI Update
```

## 🗄️ Database Design

### Core Tables
1. **users**: User accounts and profiles
2. **predictions**: Heart disease predictions
3. **datasets**: Imported medical datasets
4. **reports**: Generated reports
5. **activity_logs**: User activity tracking

### Relationships
- Users have many predictions (1:N)
- Users have many reports (1:N)
- Datasets have many predictions (1:N)

## 🔐 Security Features

### Authentication Security
- **Password Hashing**: bcrypt password encryption
- **Wallet Verification**: Blockchain-based identity verification
- **Session Management**: Secure session handling
- **Input Validation**: Comprehensive form validation

### Data Security
- **HTTPS Communication**: Secure API communication
- **SQL Injection Prevention**: Prepared statements
- **XSS Protection**: Input sanitization
- **CSRF Protection**: Token-based protection

### Blockchain Security
- **Digital Signatures**: Message signing for verification
- **Transaction Recording**: Immutable audit trail
- **Decentralized Identity**: User identity verification
- **Smart Contract Integration**: Automated security rules

## 📊 API Endpoints

### Authentication Endpoints
- `POST /api/login.php` - User login
- `POST /api/signup.php` - User registration
- `POST /api/logout.php` - User logout

### Prediction Endpoints
- `POST /api/predict.php` - Make prediction
- `GET /api/reports.php` - Get user predictions
- `POST /api/save_prediction.php` - Save prediction

### Consultation Endpoints
- `POST /api/chatgpt_consultation.php` - Get AI consultation

### Admin Endpoints
- `GET /api/dashboard_stats.php` - System statistics
- `GET /api/get_users.php` - Get all users
- `POST /api/update_user.php` - Update user
- `POST /api/delete_user.php` - Delete user
- `POST /api/import_dataset.php` - Import dataset

## 🚀 Deployment

### Web Application Deployment
1. **Server Requirements**: PHP 8.0+, MySQL 8.0+, Apache/Nginx
2. **Installation**: Upload files to web server
3. **Database Setup**: Import `heart_disease.sql`
4. **Configuration**: Update `config.php`
5. **Permissions**: Set proper file permissions

### Mobile Application Deployment
1. **Build**: `flutter build apk` or `flutter build ios`
2. **Testing**: Test on multiple devices
3. **Distribution**: Upload to app stores
4. **Configuration**: Update API endpoints

### Backend API Deployment
1. **Server Setup**: Configure web server
2. **PHP Configuration**: Enable required extensions
3. **Python Setup**: Install required packages
4. **Model Deployment**: Deploy trained model
5. **API Testing**: Test all endpoints

## 🔧 Configuration

### Environment Variables
```bash
# Database
DB_HOST=localhost
DB_NAME=heart_disease
DB_USER=root
DB_PASS=

# API Keys
OPENAI_API_KEY=your_openai_key
HUGGINGFACE_API_KEY=your_huggingface_key

# Blockchain
WEB3_PROVIDER_URI=http://127.0.0.1:8545
CONTRACT_ADDRESS=your_contract_address

# Application
SITE_URL=http://localhost/heart_disease
SECRET_KEY=your_secret_key
```

### API Configuration
- **Base URL**: Configure in both web and mobile apps
- **CORS**: Enable cross-origin requests
- **Rate Limiting**: Implement API rate limiting
- **Caching**: Configure response caching

## 📈 Performance Optimization

### Web Application
- **Asset Optimization**: Minify CSS/JS files
- **Image Optimization**: Compress images
- **Caching**: Implement browser caching
- **CDN**: Use content delivery network

### Mobile Application
- **State Management**: Efficient state updates
- **Image Caching**: Cache network images
- **API Caching**: Cache API responses
- **Background Processing**: Optimize background tasks

### Backend API
- **Database Optimization**: Index optimization
- **Query Optimization**: Efficient SQL queries
- **Caching**: Redis/Memcached integration
- **Load Balancing**: Multiple server instances

## 🧪 Testing

### Unit Testing
- **PHP**: PHPUnit for backend testing
- **Flutter**: Flutter testing framework
- **Python**: pytest for ML model testing

### Integration Testing
- **API Testing**: Postman/Newman
- **Database Testing**: Database integration tests
- **UI Testing**: Flutter widget tests

### Performance Testing
- **Load Testing**: Apache JMeter
- **Stress Testing**: High load scenarios
- **Security Testing**: Vulnerability scanning

## 📚 Documentation

### User Documentation
- **User Guide**: Complete user manual
- **API Documentation**: Swagger/OpenAPI docs
- **Installation Guide**: Step-by-step setup
- **Troubleshooting**: Common issues and solutions

### Developer Documentation
- **Code Documentation**: Inline code comments
- **Architecture Docs**: System architecture
- **API Reference**: Complete API reference
- **Contributing Guide**: Development guidelines

## 🔄 Maintenance

### Regular Tasks
- **Database Backup**: Automated daily backups
- **Log Rotation**: Manage log files
- **Security Updates**: Regular security patches
- **Performance Monitoring**: Monitor system performance

### Model Maintenance
- **Data Collection**: Collect new training data
- **Model Retraining**: Retrain with new data
- **Performance Evaluation**: Monitor model accuracy
- **Version Control**: Track model versions

## 🎯 Future Enhancements

### Planned Features
- **Multi-language Support**: Internationalization
- **Advanced Analytics**: Machine learning insights
- **Mobile Notifications**: Push notifications
- **Offline Mode**: Offline prediction capability

### Technical Improvements
- **Microservices**: Break down into microservices
- **Containerization**: Docker deployment
- **CI/CD Pipeline**: Automated deployment
- **Monitoring**: Advanced monitoring tools

This comprehensive system provides a complete solution for heart disease prediction with modern web and mobile interfaces, AI-powered predictions, blockchain security, and advanced analytics capabilities. 