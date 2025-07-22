<?php
// Test script for Puter.js connection
header('Content-Type: text/html');

// Test data
$testData = [
    'age' => 45,
    'sex' => 1,
    'cp' => 0,
    'trestbps' => 120,
    'chol' => 200,
    'fbs' => 0,
    'restecg' => 0,
    'thalach' => 150,
    'exang' => 0,
    'oldpeak' => 2.3,
    'slope' => 0,
    'ca' => 0,
    'thal' => 1
];

// Convert test data to JSON
$testDataJson = json_encode($testData);

// Test prediction result
$testResult = 0;

// Create HTML test page
$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Puter.js Test</title>
    <script src="https://puter.com/puter.js"></script>
    <script src="/static/js/puter-consultation.js"></script>
</head>
<body>
    <h1>Puter.js Connection Test</h1>
    <div id="result">Testing connection...</div>

    <script>
        async function runTest() {
            try {
                const testData = {$testDataJson};
                const result = await window.puterConsultation.getConsultation(
                    testData,
                    {$testResult}
                );
                document.getElementById('result').innerHTML = 
                    'Success: ' + result.success + '<br>' +
                    'Message: ' + (result.consultation || result.message);
            } catch (error) {
                document.getElementById('result').innerHTML = 
                    'Error: ' + error.message;
            }
        }

        runTest();
    </script>
</body>
</html>
HTML;

// Output the test page
echo $html; 