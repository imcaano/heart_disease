# Heart Disease Prediction System

## 🚀 How to Run This Project

### Step 1: Install Python Virtual Environment
```bash
# Navigate to project folder
cd C:\xampp\htdocs\heart_disease

# Create virtual environment
python -m venv venv

# Activate virtual environment (Windows)
venv\Scripts\activate

# Install Python packages
pip install flask pandas scikit-learn numpy
```

### Step 2: Start XAMPP
1. Open XAMPP Control Panel
2. Click **Start** for **Apache**
3. Click **Start** for **MySQL**
4. Make sure both show **green** status

### Step 3: Install Flutter Packages
```bash
# Navigate to Flutter app folder
cd flutter_app

# Install Flutter dependencies
flutter pub get
```

### Step 4: Start Python Server
```bash
# Go back to project root (make sure venv is activated)
cd C:\xampp\htdocs\heart_disease
venv\Scripts\activate

# Start Python server
python app.py
```
**Python server will run on: http://127.0.0.1:5000**

### Step 5: Run Flutter App in Chrome
```bash
# Navigate to Flutter app folder
cd flutter_app

# Run Flutter app in Chrome
flutter run -d web-server

```
**Flutter app will open in Chrome at: http://localhost:8080**

### Step 6: Add Sample Data
Open this URL in browser to add sample users and predictions:
```
http://localhost/heart_disease/api/sample_data.php
```

## ✅ That's It!

Your project is now running:
- **Flutter App**: http://localhost:8080
- **Python Server**: http://127.0.0.1:5000
- **XAMPP**: Running Apache and MySQL

## 🔧 If Something Doesn't Work

### Check if everything is running:
1. **XAMPP**: Both Apache and MySQL should be green
2. **Python**: Terminal should show "Running on http://127.0.0.1:5000"
3. **Flutter**: Should open Chrome automatically

### Common Issues:
- **Port 8080 busy**: Close other apps using port 8080
- **Python not found**: Make sure Python is installed and in PATH
- **Flutter not found**: Make sure Flutter SDK is installed


## 📁 Project Structure

```
heart_disease/
├── api/                    # PHP API endpoints
│   ├── login.php
│   ├── dashboard_stats.php
│   ├── get_users.php
│   ├── get_all_predictions.php
│   └── sample_data.php
├── flutter_app/           # Flutter frontend
│   ├── lib/
│   │   ├── core/
│   │   ├── features/
│   │   └── main.dart
│   └── pubspec.yaml
├── templates/             # Web templates
├── app.py                # Python Flask server
├── config.php            # Database configuration
└── venv/                 # Python virtual environment
```

## 🔧 Configuration Files

### Database Configuration (`config.php`)
```php
$host = 'localhost';
$dbname = 'heart_disease';
$username = 'root';
$password = '';
```

### API Configuration (`flutter_app/lib/core/services/api_service.dart`)
```dart
static const String baseUrl = 'http://localhost/heart_disease/api';
```

### Python Server (`app.py`)
- Runs on `http://127.0.0.1:5000`
- Handles heart disease predictions

## 🌐 Access Points

- **Flutter App**: `http://localhost:8080`
- **Web Dashboard**: `http://localhost/heart_disease`
- **Python API**: `http://127.0.0.1:5000`
- **PHP APIs**: `http://localhost/heart_disease/api/`



## 🚨 Troubleshooting

### Common Issues

1. **XAMPP Not Starting**
   - Check if ports 80, 443, 3306 are free
   - Run XAMPP as administrator

2. **Flutter Web Not Loading**
   - Make sure Chrome is installed
   - Check if port 8080 is available

3. **Python Server Error**
   - Activate virtual environment: `venv\Scripts\activate`
   - Install missing packages: `pip install package_name`

4. **API Connection Issues**
   - Check if Apache is running
   - Verify API URLs in `api_service.dart`
   - Check browser console for CORS errors

### Port Requirements
- **Apache**: 80, 443
- **MySQL**: 3306
- **Flutter Web**: 8080
- **Python Flask**: 5000

## 📱 Features

### User Features
- Heart disease prediction
- Medical consultation
- Prediction history
- User dashboard

### Admin Features
- System dashboard
- User management (view only - blockchain restricted)
- Prediction reports
- Dataset import

### Blockchain Features
- Immutable user data
- Secure wallet authentication
- Audit trail
- Data integrity protection

## 🔄 Development Workflow

1. **Start Services**
   ```bash
   # Terminal 1: XAMPP
   # Start Apache and MySQL
   
   # Terminal 2: Python
   cd heart_disease
   venv\Scripts\activate
   python app.py
   
   # Terminal 3: Flutter
   cd flutter_app
   flutter run -d chrome
   ```

2. **Make Changes**
   - Edit Flutter code → Hot reload automatically
   - Edit PHP code → Refresh browser
   - Edit Python code → Restart Python server

3. **Test Changes**
   - Flutter app: `http://localhost:8080`
   - Web version: `http://localhost/heart_disease`


If you encounter issues:
1. Check all services are running
2. Verify port configurations
3. Check browser console for errors
4. Ensure virtual environment is activated
5. Restart services if needed 