<?php
/**
 * 敹恍葫閰行?交平蝮曉??? */

// ?? Session
session_start();

require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/auth.php';

echo "<h1>敹恍葫閰行?交平蝮曉???/h1>";

// 皜祈岫蝞∠??∠??$_SESSION['user_id'] = 'admin';
$_SESSION['username'] = 'admin';
$_SESSION['name'] = '蝟餌絞蝞∠???;
$_SESSION['role'] = 'admin';
$_SESSION['logged_in'] = true;

$user = get_current_session_user();

echo "<h2>皜祈岫???/h2>";
echo "<p>雿輻?? " . $user['name'] . " (" . $user['role'] . ")</p>";
echo "<p>?冽?交?: " . date('Y-m-d', strtotime('-1 day')) . "</p>";

echo "<h2>皜祈岫 API</h2>";

// 皜祈岫 API
$yesterday = date('Y-m-d', strtotime('-1 day'));
$api_url = "get_yesterday_sales.php?date=" . $yesterday;

echo "<p>API 蝬脣?: <a href='{$api_url}' target='_blank'>{$api_url}</a></p>";

// 皜祈岫 API ??
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response) {
    $data = json_decode($response, true);
    
    if ($data && isset($data['success'])) {
        if ($data['success']) {
            echo "<p style='color: green;'>??API 皜祈岫??</p>";
            echo "<p>?鞈?:</p>";
            echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
        } else {
            echo "<p style='color: red;'>??API 皜祈岫憭望?: " . ($data['message'] ?? '?芰?航炊') . "</p>";
        }
    } else {
        echo "<p style='color: red;'>??API ??澆??航炊</p>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
    }
} else {
    echo "<p style='color: red;'>??API 隢?憭望? (HTTP {$http_code})</p>";
}

echo "<h2>皜祈岫?銵冽</h2>";
echo "<p><a href='dashboard.php' target='_blank'>?? dashboard.php</a></p>";
echo "<p><strong>皜祈岫甇仿?:</strong></p>";
echo "<ol>";
echo "<li>蝣箄????交平蝮整??＊蝷箏??亙?摨?璆剔蜀??憿??/li>";
echo "<li>暺???</li>";
echo "<li>蝣箄?敶閬?憿舐內</li>";
echo "<li>蝣箄?頛?憿舐內</li>";
echo "<li>蝣箄?鞈?頛摰?</li>";
echo "<li>皜祈岫???</li>";
echo "</ol>";

// 皜 Session
session_destroy();

echo "<p style='margin-top: 30px; color: #666;'>皜祈岫摰???: " . date('Y-m-d H:i:s') . "</p>";
?>
