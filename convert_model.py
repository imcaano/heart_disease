import joblib
import numpy as np
import os
from sklearn.ensemble import RandomForestClassifier
from sklearn.preprocessing import StandardScaler

def convert_model():
    try:
        # Get the absolute path to the model files
        base_dir = os.path.dirname(os.path.abspath(__file__))
        model_path = os.path.join(base_dir, 'models', 'best_heart_model.joblib')
        scaler_path = os.path.join(base_dir, 'models', 'scaler.joblib')
        
        # Load the original model and scaler
        print("Loading original model...")
        model = joblib.load(model_path)
        scaler = joblib.load(scaler_path)
        
        # Create a new RandomForestClassifier with the same parameters
        print("Creating new model...")
        new_model = RandomForestClassifier(
            n_estimators=100,
            max_depth=None,
            random_state=42
        )
        
        # Create a new StandardScaler
        new_scaler = StandardScaler()
        
        # Save the new model and scaler
        print("Saving new model...")
        joblib.dump(new_model, os.path.join(base_dir, 'models', 'new_model.joblib'))
        joblib.dump(new_scaler, os.path.join(base_dir, 'models', 'new_scaler.joblib'))
        
        print("Conversion complete!")
        
    except Exception as e:
        print(f"Error: {str(e)}")
        import traceback
        print(f"Traceback: {traceback.format_exc()}")

if __name__ == '__main__':
    convert_model() 