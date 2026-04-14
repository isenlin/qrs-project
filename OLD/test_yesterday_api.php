<?php
/**
 * 皜祈岫?冽璆剔蜀 API
 */

// ?? Session
session_start();

require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/auth.php';

echo "<h1>皜祈岫?冽璆剔蜀 API</h1>";

// 皜祈岫蝞∠??∠??$_SESSION['user_id'] = 'admin';
$_SESSION['username'] = 'admin';
$_SESSION['name'] = '蝟餌絞蝞∠???;
$_SESSION['role'] = 'admin';
$_SESSION['logged_in'] = true;

$user = get_current_session_user();

echo "<h2>皜祈岫???/h2>";
echo "<p>雿輻?? " . $user['name'] . " (" . $user['role'] . ")</p>";
echo "<p>?冽?交?: " . date('Y-m-d', strtotime('-1 day')) . "</p>";

echo "<h2>皜祈岫 API ??</h2>";

// 皜祈岫 API
$yesterday = date('Y-m-d', strtotime('-1 day'));
$api_url = "get_yesterday_sales.php?date=" . $yesterday;

echo "<p>API 蝬脣?: <a href='{$api_url}' target='_blank'>{$api_url}</a></p>";

// 皜祈岫 API ??嚗蝙??file_get_contents ?蹂誨 cURL嚗?$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "User-Agent: Test Script\r\n"
    ]
]);

$response = @file_get_contents($api_url, false, $context);

// ?? HTTP ??Ⅳ
$http_code = 200; // ?身??if ($response === false) {
    $http_code = 500;
    $error = error_get_last();
    echo "<p style='color: orange;'>?? 雿輻 file_get_contents 隢?憭望?: " . ($error['message'] ?? '?芰?航炊') . "</p>";
    
    // ?岫雿輻銝??瘜?    echo "<p>?岫雿輻 include ?孵?皜祈岫...</p>";
    
    // ?湔? API 瑼?靘葫閰?    ob_start();
    $_GET['date'] = $yesterday;
    include 'get_yesterday_sales.php';
    $response = ob_get_clean();
    
    if ($response) {
        echo "<p style='color: green;'>??雿輻 include ?孵???????</p>";
        $http_code = 200;
    }
}

if ($response) {
    $data = json_decode($response, true);
    
    if ($data && isset($data['success'])) {
        if ($data['success']) {
            echo "<p style='color: green;'>??API 皜祈岫??</p>";
            echo "<p>?鞈?蝯?:</p>";
            
            echo "<h3>?箸鞈?</h3>";
            echo "<ul>";
            echo "<li>?交?: " . ($data['data']['date'] ?? 'N/A') . "</li>";
            echo "<li>蝮賢?瑹: " . ($data['data']['stores_count'] ?? '0') . "</li>";
            echo "<li>撌脩?: " . ($data['data']['entered_count'] ?? '0') . "</li>";
            echo "<li>蝮賣平蝮? " . number_format($data['data']['total_amount'] ?? 0) . "</li>";
            echo "<li>隞??瑕?? " . ($data['data']['substitute_count'] ?? '0') . "</li>";
            echo "</ul>";
            
            echo "<h3>鞈?蝯?瑼Ｘ</h3>";
            echo "<ul>";
            echo "<li>stores_by_code 摮: " . (isset($data['data']['stores_by_code']) ? '???? : '????) . "</li>";
            if (isset($data['data']['stores_by_code'])) {
                echo "<li>stores_by_code 憿?: " . gettype($data['data']['stores_by_code']) . "</li>";
                echo "<li>stores_by_code ??? " . count($data['data']['stores_by_code']) . "</li>";
                
                // 憿舐內?嗾??瑹???                $count = 0;
                foreach ($data['data']['stores_by_code'] as $storeCode => $storeData) {
                    if ($count < 3) {
                        echo "<li>摨? {$storeCode}: 璆剔蜀 " . number_format($storeData['amount'] ?? 0) . 
                             ", 閫 " . ($storeData['role'] ?? 'main') . "</li>";
                        $count++;
                    }
                }
                if (count($data['data']['stores_by_code']) > 3) {
                    echo "<li>... ?? " . (count($data['data']['stores_by_code']) - 3) . " ??瑹?/li>";
                }
            }
            echo "<li>stores ???摮: " . (isset($data['data']['stores']) ? '???? : '????) . "</li>";
            if (isset($data['data']['stores'])) {
                echo "<li>stores ?????? " . count($data['data']['stores']) . "</li>";
            }
            echo "</ul>";
            
            echo "<h3>摰 JSON ??</h3>";
            echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px; max-height: 400px; overflow: auto;'>";
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            echo "</pre>";
        } else {
            echo "<p style='color: red;'>??API 皜祈岫憭望?: " . ($data['message'] ?? '?芰?航炊') . "</p>";
        }
    } else {
        echo "<p style='color: red;'>??API ??澆??航炊</p>";
        echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>" . htmlspecialchars($response) . "</pre>";
    }
} else {
    echo "<p style='color: red;'>??API 隢?憭望? (HTTP {$http_code})</p>";
}

echo "<h2>皜祈岫?銵冽?</h2>";
echo "<p><a href='dashboard.php' target='_blank'>?? dashboard.php</a></p>";
echo "<p><strong>皜祈岫甇仿?:</strong></p>";
echo "<ol>";
echo "<li>蝣箄????交平蝮整??＊蝷箏??亙?摨?璆剔蜀??憿??/li>";
echo "<li>暺???</li>";
echo "<li>蝣箄?敶閬?憿舐內</li>";
echo "<li>蝣箄?頛?憿舐內</li>";
echo "<li>蝣箄?鞈?頛摰?</li>";
echo "<li>蝣箄?銵冽憿舐內?冽璆剔蜀鞈?嚗??府?賣 -嚗?/li>";
echo "<li>皜祈岫???</li>";
echo "</ol>";

echo "<h2>撣貉???閮箸</h2>";
echo "<h3>憒?鞈??賣????</h3>";
echo "<ol>";
echo "<li>瑼Ｘ?冽?臬?平蝮曇???/li>";
echo "<li>瑼Ｘ load_monthly_sales() ?賣?臬?賣迤蝣箄??亥???/li>";
echo "<li>瑼Ｘ stores.json 瑼??臬摮銝撘迤蝣?/li>";
echo "<li>瑼Ｘ users.json 瑼??臬摮銝撘迤蝣?/li>";
echo "<li>瑼Ｘ PHP ?航炊?亥?</li>";
echo "</ol>";

echo "<h3>憒?敶閬??⊥?憿舐內嚗?/h3>";
echo "<ol>";
echo "<li>瑼Ｘ?汗?券??潸極?瑚葉??Console ?航炊</li>";
echo "<li>瑼Ｘ JavaScript ?臬??瘜隤?/li>";
echo "<li>瑼Ｘ CSS 璅???臬甇?Ⅱ頛</li>";
echo "<li>瑼Ｘ API ???臬甇?Ⅱ</li>";
echo "</ol>";

// 皜 Session
session_destroy();

echo "<p style='margin-top: 30px; color: #666;'>皜祈岫摰???: " . date('Y-m-d H:i:s') . "</p>";
?>
