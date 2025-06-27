import sys
import json

def predict_heart_disease(features):
    # Calculate risk score based on medical guidelines
        risk_score = 0
        
        # Age factor
    age = features['age']
    if age >= 65:
        risk_score += 3
    elif age >= 55:
        risk_score += 2
    elif age >= 45:
            risk_score += 1
            
    # Sex factor
    if features['sex'] == 1:  # Male
        risk_score += 2
            
    # Chest pain type
    cp = features['cp']
    if cp == 2:  # Non-anginal pain
        risk_score += 3
    elif cp == 1:  # Atypical angina
        risk_score += 2
    elif cp == 0:  # Typical angina
            risk_score += 1
        
    # Blood pressure
    trestbps = features['trestbps']
    if trestbps >= 180:
        risk_score += 3
    elif trestbps >= 140:
        risk_score += 2
    elif trestbps >= 120:
            risk_score += 1
            
    # Cholesterol
    chol = features['chol']
    if chol >= 300:
        risk_score += 3
    elif chol >= 240:
        risk_score += 2
    elif chol >= 200:
            risk_score += 1
            
    # Fasting blood sugar
    if features['fbs'] == 1:  # > 120 mg/dl
            risk_score += 1
            
    # ECG results
    restecg = features['restecg']
    if restecg == 2:  # Left ventricular hypertrophy
        risk_score += 3
    elif restecg == 1:  # ST-T wave abnormality
        risk_score += 2
    
    # Maximum heart rate
    thalach = features['thalach']
    if thalach < 100:
        risk_score += 3
    elif thalach < 120:
        risk_score += 2
    elif thalach < 140:
            risk_score += 1
            
    # Exercise induced angina
    if features['exang'] == 1:
        risk_score += 2
            
    # ST depression
    oldpeak = features['oldpeak']
    if oldpeak >= 2.0:
        risk_score += 3
    elif oldpeak >= 1.0:
        risk_score += 2
    elif oldpeak > 0:
            risk_score += 1
            
    # Slope of ST segment
    slope = features['slope']
    if slope == 2:  # Downsloping
        risk_score += 2
    elif slope == 1:  # Flat
            risk_score += 1
            
    # Number of major vessels
    ca = features['ca']
    risk_score += ca
        
    # Thalassemia
    thal = features['thal']
    if thal == 3:  # Reversible defect
        risk_score += 2
    elif thal == 2:  # Fixed defect
            risk_score += 1
            
    # Make prediction based on risk score (threshold: 15)
    prediction = 1 if risk_score >= 15 else 0
    
        # Calculate probability (simplified)
    probability = min(risk_score / 20.0, 0.95) + 0.05
        
        return {
        'success': True,
        'prediction': prediction,
        'probability': probability,
        'message': 'Positive' if prediction == 1 else 'Negative'
    }

def main():
    try:
        if len(sys.argv) != 2:
            raise Exception("Please provide input file path")
        
        # Read input file
        with open(sys.argv[1], 'r') as f:
            data = json.load(f)
        
        # Make prediction
        result = predict_heart_disease(data)
        
        # Print result as JSON
        print(json.dumps(result))
        
    except Exception as e:
        print(json.dumps({
            'success': False,
            'message': str(e)
        }))

if __name__ == '__main__':
    main() 