import joblib
import numpy as np
from sklearn.ensemble import RandomForestClassifier
from pathlib import Path

# Create a simple model
model = RandomForestClassifier(n_estimators=10, random_state=42)

# Create some dummy data for training
X = np.random.rand(100, 13)  # 100 samples, 13 features
y = np.random.randint(0, 2, 100)  # Binary classification

# Train the model
model.fit(X, y)

# Create models directory if it doesn't exist
models_dir = Path('models')
models_dir.mkdir(exist_ok=True)

# Save the model
model_path = models_dir / 'best_heart_model.joblib'
joblib.dump(model, model_path)

print(f"Model saved to {model_path}") 