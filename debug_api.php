<?php
// Debug script to capture exact API response
$url = 'http://localhost:8080/api/create_game.php';
$data = json_encode([
    'game_name' => 'Debug Game',
    'galaxy_size' => 'medium',
    'difficulty' => 'normal'
]);

$options = [
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $data
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

echo "=== RAW RESPONSE ===\n";
echo $result . "\n";

echo "\n=== HEX DUMP ===\n";
for ($i = 0; $i < strlen($result); $i++) {
    printf("%02x ", ord($result[$i]));
    if (($i + 1) % 16 == 0) echo "\n";
}
echo "\n";

echo "\n=== CHARACTER ANALYSIS ===\n";
for ($i = 0; $i < min(100, strlen($result)); $i++) {
    $char = $result[$i];
    $ord = ord($char);
    if ($ord < 32 || $ord > 126) {
        echo "Position $i: [" . sprintf('%02x', $ord) . "] (non-printable)\n";
    } else {
        echo "Position $i: '$char' [" . sprintf('%02x', $ord) . "]\n";
    }
}

echo "\n=== JSON VALIDATION ===\n";
$decoded = json_decode($result, true);
if ($decoded === null) {
    echo "JSON decode failed. Error: " . json_last_error_msg() . "\n";
} else {
    echo "JSON decode successful!\n";
    print_r($decoded);
}
?>