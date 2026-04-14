<?php
/**
 * 蝪∪皜祈岫?冽璆剔蜀 API嚗?雿輻 cURL嚗? */

// ?? Session
session_start();

require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/auth.php';

echo "<h1>蝪∪皜祈岫?冽璆剔蜀 API</h1>";

// 皜祈岫蝞∠??∠??$_SESSION['user_id'] = 'admin';
$_SESSION['username'] = 'admin';
$_SESSION['name'] = '蝟餌絞蝞∠???;
$_SESSION['role'] = 'admin';
$_SESSION['logged_in'] = true;

$user = get_current_session_user();

echo "<h2>皜祈岫???/h2>";
echo "<p>雿輻?? " . $user['name'] . " (" . $user['role'] . ")</p>";
echo "<p>?冽?交?: " . date('Y-m-d', strtotime('-1 day')) . "</p>";

echo "<h2>?寞? 1嚗?亙銵?API 瑼?</h2>";

$yesterday = date('Y-m-d', strtotime('-1 day'));
$_GET['date'] = $yesterday;

// ?湔?瑁? API 瑼?
ob_start();
include 'get_yesterday_sales.php';
$response = ob_get_clean();

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
    echo "<p style='color: red;'>???⊥??? API ??</p>";
}

echo "<h2>?寞? 2嚗?亥赤??API</h2>";
echo "<p>?湔閮芸????嚗?a href='get_yesterday_sales.php?date={$yesterday}' target='_blank'>get_yesterday_sales.php?date={$yesterday}</a></p>";
echo "<p>?府? JSON ?澆?????/p>";

echo "<h2>?寞? 3嚗??葫閰西???瑽?/h2>";

// ??皜祈岫鞈?蝯?
echo "<h3>皜祈岫 load_monthly_sales() ?賣</h3>";
$month = substr($yesterday, 0, 7);
$sales_summary = load_monthly_sales($month);

if ($sales_summary) {
    echo "<p style='color: green;'>??load_monthly_sales() ??</p>";
    echo "<p>?遢: {$month}</p>";
    echo "<p>蝮賢予?? " . count($sales_summary) . "</p>";
    
    if (isset($sales_summary[$yesterday])) {
        echo "<p style='color: green;'>???曉?冽璆剔蜀鞈?</p>";
        echo "<p>?冽?平蝮曄?摨??? " . count($sales_summary[$yesterday]) . "</p>";
        
        // 憿舐內?嗾??瑹?        $count = 0;
        foreach ($sales_summary[$yesterday] as $storeCode => $salesData) {
            if ($count < 5) {
                echo "<p>摨? {$storeCode}: 璆剔蜀 " . number_format($salesData['amount'] ?? 0) . 
                     ", 閫 " . ($salesData['role'] ?? 'main') . "</p>";
                $count++;
            }
        }
    } else {
        echo "<p style='color: orange;'>?? 瘝??冽璆剔蜀鞈?</p>";
        echo "<p>撱箄降?瑁? <a href='create_test_yesterday_data.php'>create_test_yesterday_data.php</a> 撱箇?皜祈岫鞈?</p>";
    }
} else {
    echo "<p style='color: red;'>??load_monthly_sales() 憭望???????/p>";
}

echo "<h3>皜祈岫 load_data('stores') ?賣</h3>";
$stores = load_data('stores');
if ($stores) {
    echo "<p style='color: green;'>??load_data('stores') ??</p>";
    echo "<p>蝮賢?瑹: " . count($stores) . "</p>";
} else {
    echo "<p style='color: red;'>??load_data('stores') 憭望?</p>";
}

echo "<h2>皜祈岫?銵冽?</h2>";
echo "<p><a href='dashboard.php' target='_blank'>?? dashboard.php</a></p>";

echo "<h2>??閮箸</h2>";

echo "<h3>憒??冽璆剔蜀鞈??賣????</h3>";
echo "<ol>";
echo "<li>瑼Ｘ?臬??交平蝮曇????瑁?銝?瘜?3?葫閰?/li>";
echo "<li>憒?瘝?鞈?嚗銵?<a href='create_test_yesterday_data.php'>create_test_yesterday_data.php</a></li>";
echo "<li>瑼Ｘ API ??嚗銵瘜?2??亥赤??API</li>";
echo "<li>瑼Ｘ JavaScript Console嚗? F12 ????極??/li>";
echo "</ol>";

echo "<h3>憒?敶閬??⊥?憿舐內嚗?/h3>";
echo "<ol>";
echo "<li>瑼Ｘ?汗??Console ?臬??JavaScript ?航炊</li>";
echo "<li>瑼Ｘ CSS 璅???臬甇?Ⅱ頛</li>";
echo "<li>瑼Ｘ API ???臬甇?Ⅱ嚗蝙?具瘜?2??</li>";
echo "</ol>";

// 皜 Session
session_destroy();

echo "<p style='margin-top: 30px; color: #666;'>皜祈岫摰???: " . date('Y-m-d H:i:s') . "</p>";
?>
