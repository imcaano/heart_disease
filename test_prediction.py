import json
import sys
from pathlib import Path

# Sample data for testing
test_data = {
    "age": 22,
    "sex": 1,
    "cp": 0,
    "trestbps": 120,
    "chol": 536,
    "fbs": 1,
    "restecg": 0,
    "thalach": 90,
    "exang": 1,
    "oldpeak": 3,
    "slope": 0,
    "ca": 1,
    "thal": 0
}

# Create a temporary file with the test data
temp_file = Path("test_data.json")
with open(temp_file, "w") as f:
    json.dump(test_data, f)

# Run the prediction script
print("Running prediction with test data...")
print(f"Test data: {json.dumps(test_data, indent=2)}")

# Execute the prediction script
import subprocess
result = subprocess.run(["python", "predict.py", str(temp_file)], 
                        capture_output=True, text=True)

# Print the output
print("\nPrediction script output:")
print(result.stdout)
if result.stderr:
    print("\nErrors:")
    print(result.stderr)

# Clean up
temp_file.unlink() 