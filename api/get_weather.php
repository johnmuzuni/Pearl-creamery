<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

$lat = isset($_GET['lat']) ? (float)$_GET['lat'] : DEFAULT_LAT;
$lng = isset($_GET['lng']) ? (float)$_GET['lng'] : DEFAULT_LNG;

function fetch_url($url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
    ]);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($res === false || $code < 200 || $code >= 300) return null;
    return $res;
}

if (defined('WEATHER_API_KEY') && WEATHER_API_KEY !== '') {
    $url = 'https://api.openweathermap.org/data/2.5/weather?lat=' . urlencode($lat)
         . '&lon=' . urlencode($lng) . '&units=metric&appid=' . urlencode(WEATHER_API_KEY);
    $raw = fetch_url($url);
    $data = $raw ? json_decode($raw, true) : null;
    if (is_array($data) && isset($data['main']['temp'])) {
        $place = trim(($data['name'] ?? '') . (isset($data['sys']['country']) ? ', ' . $data['sys']['country'] : ''));
        echo json_encode(['temp' => (float)$data['main']['temp'], 'place' => $place ?: 'your location']);
        exit;
    }
}

// Fallback: Open-Meteo (no API key required)
$url = 'https://api.open-meteo.com/v1/forecast?latitude=' . urlencode($lat)
     . '&longitude=' . urlencode($lng) . '&current=temperature_2m';
$raw = fetch_url($url);
$data = $raw ? json_decode($raw, true) : null;
if (is_array($data) && isset($data['current']['temperature_2m'])) {
    echo json_encode(['temp' => (float)$data['current']['temperature_2m'], 'place' => 'your location']);
    exit;
}

http_response_code(502);
echo json_encode(['error' => 'Weather service unavailable']);