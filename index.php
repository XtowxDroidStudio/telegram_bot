<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$token = '8502513865:AAFHowxSJKFZFZel2a6wL_7DNcZpuXQ43Ss';

// تسجيل للتصحيح
file_put_contents('log.txt', date('Y-m-d H:i:s') . " - Bot started\n", FILE_APPEND);

$input = file_get_contents("php://input");
$update = json_decode($input, true);

if(empty($update)) {
    echo "🤖 البوت يعمل بنجاح!";
    exit;
}

// تسجيل الرسالة الواردة
file_put_contents('messages.log', date('Y-m-d H:i:s') . " - " . $input . "\n", FILE_APPEND);

$message = $update["message"] ?? null;
$text = $message["text"] ?? null;
$chat_id = $message["chat"]["id"] ?? null;
$user_id = $message["from"]["id"] ?? null;

if($text == "/start") {
    $response = "🎉 أهلاً وسهلاً!\n";
    $response .= "✅ البوت يعمل بنجاح\n";
    $response .= "👤 رقمك: $user_id\n";
    $response .= "🕒 " . date('Y-m-d H:i:s');
    
    $url = "https://api.telegram.org/bot$token/sendMessage";
    $data = [
        'chat_id' => $chat_id,
        'text' => $response
    ];
    
    // استخدام cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_exec($ch);
    curl_close($ch);
    
    file_put_contents('log.txt', date('Y-m-d H:i:s') . " - Sent welcome to $user_id\n", FILE_APPEND);
}
?>
