<?php
header('Content-Type: application/json');

// Hugging Face API configuration
$api_key = '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';
$api_url = 'https://api-inference.huggingface.co/models/facebook/blenderbot-400M-distill';

// Test API connection
function testApiConnection() {
    global $api_key, $api_url;
    
    $data = [
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            [
                'role' => 'user',
                'content' => 'Hello, this is a test message. Please respond with "API connection successful" if you can read this.'
            ]
        ],
        'max_tokens' => 50
    ];

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        return [
            'success' => false,
            'message' => 'Curl error: ' . $error
        ];
    }
    
    curl_close($ch);
    
    if ($httpCode === 200) {
        $result = json_decode($response, true);
        if (isset($result['choices'][0]['message']['content'])) {
            return [
                'success' => true,
                'message' => 'API connection successful',
                'response' => $result['choices'][0]['message']['content']
            ];
        }
    }
    
    return [
        'success' => false,
        'message' => 'API test failed. HTTP Code: ' . $httpCode,
        'response' => $response
    ];
}

function getConsultation($predictionData, $predictionResult) {
    global $api_key, $api_url;
    
    // Prepare the prompt for the model
    $prompt = "As a medical expert, please provide a consultation based on the following heart disease prediction data:\n\n";
    $prompt .= "Age: {$predictionData['age']}\n";
    $prompt .= "Sex: " . ($predictionData['sex'] == 1 ? 'Male' : 'Female') . "\n";
    $prompt .= "Chest Pain Type: " . getChestPainType($predictionData['cp']) . "\n";
    $prompt .= "Resting Blood Pressure: {$predictionData['trestbps']} mm Hg\n";
    $prompt .= "Cholesterol: {$predictionData['chol']} mg/dl\n";
    $prompt .= "Fasting Blood Sugar: " . ($predictionData['fbs'] == 1 ? 'High (>120 mg/dl)' : 'Normal (≤120 mg/dl)') . "\n";
    $prompt .= "ECG Results: " . getECGResults($predictionData['restecg']) . "\n";
    $prompt .= "Maximum Heart Rate: {$predictionData['thalach']} beats/min\n";
    $prompt .= "Exercise Induced Angina: " . ($predictionData['exang'] == 1 ? 'Yes' : 'No') . "\n";
    $prompt .= "ST Depression: {$predictionData['oldpeak']}\n";
    $prompt .= "Slope: " . getSlopeType($predictionData['slope']) . "\n";
    $prompt .= "Number of Major Vessels: {$predictionData['ca']}\n";
    $prompt .= "Thalassemia: " . getThalassemiaType($predictionData['thal']) . "\n\n";
    $prompt .= "Prediction Result: " . ($predictionResult == 1 ? 'High Risk' : 'Low Risk') . "\n\n";
    $prompt .= "Please provide a detailed medical consultation including:\n";
    $prompt .= "1. Interpretation of the results\n";
    $prompt .= "2. Recommended lifestyle changes\n";
    $prompt .= "3. Suggested medical follow-up steps\n";
    $prompt .= "4. Preventive measures\n";
    $prompt .= "Please format the response in a clear, professional manner.";

    // Prepare the API request
    $data = [
        'inputs' => $prompt
    ];

    // Make the API request
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        return [
            'success' => false,
            'message' => 'Curl error: ' . $error
        ];
    }
    
    curl_close($ch);

    if ($httpCode === 200) {
        $result = json_decode($response, true);
        if (isset($result['generated_text'])) {
            return [
                'success' => true,
                'consultation' => $result['generated_text']
            ];
        }
    }

    return [
        'success' => false,
        'message' => 'Failed to get consultation. HTTP Code: ' . $httpCode,
        'response' => $response
    ];
}

// Helper functions to convert numeric values to meaningful text
function getChestPainType($cp) {
    $types = [
        0 => 'Typical angina',
        1 => 'Atypical angina',
        2 => 'Non-anginal pain',
        3 => 'Asymptomatic'
    ];
    return $types[$cp] ?? 'Unknown';
}

function getECGResults($restecg) {
    $results = [
        0 => 'Normal',
        1 => 'ST-T wave abnormality',
        2 => 'Left ventricular hypertrophy'
    ];
    return $results[$restecg] ?? 'Unknown';
}

function getSlopeType($slope) {
    $types = [
        0 => 'Upsloping',
        1 => 'Flat',
        2 => 'Downsloping'
    ];
    return $types[$slope] ?? 'Unknown';
}

function getThalassemiaType($thal) {
    $types = [
        0 => 'Normal',
        1 => 'Fixed defect',
        2 => 'Reversible defect',
        3 => 'Not applicable'
    ];
    return $types[$thal] ?? 'Unknown';
}

// Handle the request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // If it's a test request
    if (isset($input['test']) && $input['test'] === true) {
        $result = testApiConnection();
        echo json_encode($result);
        exit;
    }
    
    if (isset($input['predictionData']) && isset($input['predictionResult'])) {
        $result = getConsultation($input['predictionData'], $input['predictionResult']);
        echo json_encode($result);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required data'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
} 