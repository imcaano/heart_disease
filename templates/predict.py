from flask import Flask, render_template, request, jsonify
import joblib
import numpy as np
from sklearn.preprocessing import StandardScaler
import os

app = Flask(__name__)

# Load model and scaler
base_dir = os.path.dirname(os.path.abspath(__file__))
model_path = os.path.join(base_dir, '..', 'models', 'new_model.joblib')
scaler_path = os.path.join(base_dir, '..', 'models', 'new_scaler.joblib')

model = joblib.load(model_path)
scaler = joblib.load(scaler_path)

@app.route('/')
def index():
    return render_template('predict.html')

@app.route('/predict', methods=['POST'])
def predict():
    try:
        data = request.get_json()
        
        # Validate required fields
        required_fields = [
            'age', 'sex', 'cp', 'trestbps', 'chol', 'fbs', 
            'restecg', 'thalach', 'exang', 'oldpeak', 'slope', 
            'ca', 'thal'
        ]
        
        for field in required_fields:
            if field not in data:
                return jsonify({'error': f'Missing required field: {field}'}), 400
        
        # Prepare features
        features = [
            float(data['age']),
            float(data['sex']),
            float(data['cp']),
            float(data['trestbps']),
            float(data['chol']),
            float(data['fbs']),
            float(data['restecg']),
            float(data['thalach']),
            float(data['exang']),
            float(data['oldpeak']),
            float(data['slope']),
            float(data['ca']),
            float(data['thal'])
        ]
        
        # Scale features
        features_scaled = scaler.transform([features])
        
        # Make prediction
        prediction = model.predict(features_scaled)[0]
        probability = model.predict_proba(features_scaled)[0][1]
        
        return jsonify({
            'prediction': int(prediction),
            'probability': float(probability)
        })
        
    except Exception as e:
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    app.run(debug=True) 