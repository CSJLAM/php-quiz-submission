<?php
// Load configuration
function loadConfig() {
    $configFile = __DIR__ . '/../config/config.php';
    if (!file_exists($configFile)) {
        throw new Exception('Configuration file not found. Please create config/config.php based on config.php.example');
    }
    return require $configFile;
}

function isValidPhoneNumber($phone_number, $customer_id, $api_key) {
  
    $api_url = "https://rest-ww.telesign.com/v1/phoneid/$phone_number";
    // edit base64 encode and header
    $headers = [
        "Authorization: Basic " . base64_encode($customer_id . ":" . $api_key),
        "Content-Type: application/json",
        "Accept: application/json"
    ];
    
    $ch = curl_init();
    // add secutity-related options and "POST" request
    curl_setopt_array($ch, [
        CURLOPT_URL => $api_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CUSTOMREQUEST => "POST"
    ]);
    

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        return false; // API request failed
    }
    
    $data = json_decode($response, true);
    // API response structure is different
    if (!isset($data['phone_type']['description'])) {
        return false + $response; // Unexpected API response
    }
    
    $valid_types = ["FIXED_LINE", "MOBILE", "VALID"];
    return in_array(strtoupper($data['phone_type']['description']), $valid_types);
}

// Usage example
$phone_number = "1234567890"; // Replace with actual phone number
$config = loadConfig();
$customer_id = $config['telesign']['customer_id'];
$api_key = $config['telesign']['api_key'];

$result = isValidPhoneNumber($phone_number, $customer_id, $api_key);
var_dump($result);
?>