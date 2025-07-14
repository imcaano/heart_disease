import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import StandardScaler
import xgboost as xgb
import joblib
import os

def train_and_save_model():
    try:
        # Load the dataset
        print("Loading dataset...")
        df = pd.read_csv('heart.csv')
        
        # Check if target column exists
        if 'target' not in df.columns:
            raise ValueError("Dataset must contain a 'target' column")
        
        # Split features and target
        X = df.drop('target', axis=1)
        y = df['target']
        
        # Split into training and testing sets
        X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)
        
        # Scale the features
        print("Scaling features...")
        scaler = StandardScaler()
        X_train_scaled = scaler.fit_transform(X_train)
        X_test_scaled = scaler.transform(X_test)
        
        # Train the model
        print("Training model...")
        model = xgb.XGBClassifier(
            n_estimators=100,
            max_depth=5,
            random_state=42
        )
        model.fit(X_train_scaled, y_train)
        
        # Evaluate the model
        train_score = model.score(X_train_scaled, y_train)
        test_score = model.score(X_test_scaled, y_test)
        print(f"Training accuracy: {train_score:.4f}")
        print(f"Testing accuracy: {test_score:.4f}")
        
        # Save the model and scaler
        print("Saving model and scaler...")
        models_dir = 'models'
        if not os.path.exists(models_dir):
            os.makedirs(models_dir)
            
        joblib.dump(model, os.path.join(models_dir, 'best_heart_model.joblib'))
        joblib.dump(scaler, os.path.join(models_dir, 'scaler.joblib'))
        
        # Save feature names for reference
        with open(os.path.join(models_dir, 'feature_names.txt'), 'w') as f:
            f.write('\n'.join(X.columns))
            
        print("Model and scaler saved successfully!")
        return True
        
    except Exception as e:
        print(f"Error: {str(e)}")
        return False

if __name__ == '__main__':
    train_and_save_model() 