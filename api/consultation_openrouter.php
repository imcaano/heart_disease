<?php
header('Content-Type: application/json');

// Get the prompt from POST data
$data = json_decode(file_get_contents('php://input'), true);
$prompt = isset($data['prompt']) ? $data['prompt'] : '';

if (!$prompt) {
    echo json_encode(['success' => false, 'message' => 'No prompt provided.']);
    exit;
}

$apiKey = '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';
$model = 'openai/gpt-3.5-turbo';

$url = 'https://openrouter.ai/api/v1/chat/completions';

$postData = [
    'model' => $model,
    'messages' => [
        [
            'role' => 'user',
            'content' => $prompt
        ]
    ],
    'max_tokens' => 512,
    'temperature' => 0.7
];

$headers = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($response === false) {
    echo json_encode(['success' => false, 'message' => 'Request failed: ' . curl_error($ch)]);
    curl_close($ch);
    exit;
}

curl_close($ch);

$result = json_decode($response, true);

if ($httpCode === 200 && isset($result['choices'][0]['message']['content'])) {
    echo json_encode([
        'success' => true,
        'consultation' => $result['choices'][0]['message']['content']
    ]);
} else {
    $errorMsg = isset($result['error']['message']) ? $result['error']['message'] : 'Unknown error.';
    echo json_encode([
        'success' => false,
        'message' => 'API error: ' . $errorMsg
    ]);
} 