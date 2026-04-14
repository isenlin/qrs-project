<?php
/**
 * 敹恍葫閰行?交平蝮?API 靽格迤
 */

// ?? Session
session_start();

require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/auth.php';

echo "<h1>敹恍葫閰行?交平蝮?API 靽格迤</h1>";

// 皜祈岫蝞∠??∠??$_SESSION['user_id'] = 'admin';
$_SESSION['username'] = 'admin';
$_SESSION['name'] = '蝟餌絞蝞∠???;
$_SESSION['role'] = 'admin';
$_SESSION['logged_in'] = true;

$user = get_current_session_user();

echo "<h2>皜祈岫???/h2>";
echo "<p>雿輻?? " . $user['name'] . " (" . $user['role'] . ")</p>";

echo "<h2>皜祈岫 JSON 蝺函Ⅳ</h2>";

// 皜祈岫銝???憪??孵?
$test1 = ['stores_by_code' => []];
$test2 = ['stores_by_code' => (object)[]];
$test3 = ['stores_by_code' => new stdClass()];

echo "<h3>皜祈岫 1: 蝛粹??[]</h3>";
echo "<pre>" . json_encode($test1, JSON_PRETTY_PRINT) . "</pre>";

echo "<h3>皜祈岫 2: (object)[]</h3>";
echo "<pre>" . json_encode($test2, JSON_PRETTY_PRINT) . "</pre>";

echo "<h3>皜祈岫 3: new stdClass()</h3>";
echo "<pre>" . json_encode($test3, JSON_PRETTY_PRINT) . "</pre>";

echo "<h2>?湔皜祈岫靽格迤敺? API</h2>";

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
            
            echo "<h3>stores_by_code 憿?瑼Ｘ</h3>";
            if (isset($data['data']['stores_by_code'])) {
                echo "<p>stores_by_code 憿?: " . gettype($data['data']['stores_by_code']) . "</p>";
                echo "<p>stores_by_code ?舫?? " . (is_array($data['data']['stores_by_code']) ? '?? : '??) . "</p>";
                echo "<p>stores_by_code ??? " . count($data['data']['stores_by_code']) . "</p>";
                
                // 瑼Ｘ?嗾????                $count = 0;
                foreach ($data['data']['stores_by_code'] as $storeCode => $storeData) {
                    if ($count < 3) {
                        echo "<p>摨? {$storeCode}: ";
                        echo "璆剔蜀: " . ($storeData['amount'] !== null ? number_format($storeData['amount']) : 'null');
                        echo ", 閫: " . ($storeData['role'] ?? 'main');
                        echo ", ??? " . ($storeData['status'] ?? 'N/A');
                        echo "</p>";
                        $count++;
                    }
                }
            } else {
                echo "<p style='color: red;'>??stores_by_code 銝???/p>";
            }
            
            echo "<h3>摰??嚗?500摮?</h3>";
            echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px; max-height: 300px; overflow: auto;'>";
            echo substr(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 0, 500) . "...";
            echo "</pre>";
        } else {
            echo "<p style='color: red;'>??API 皜祈岫憭望?: " . ($data['message'] ?? '?芰?航炊') . "</p>";
        }
    } else {
        echo "<p style='color: red;'>??API ??澆??航炊</p>";
        echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>" . htmlspecialchars($response) . "</pre>";
    }
}

echo "<h2>皜祈岫 JavaScript 鞈???</h2>";

echo "<h3>璅⊥ JavaScript ???摩</h3>";
echo "<pre><code>
// JavaScript 銝剔????摩
const storeData = salesData.stores_by_code ? salesData.stores_by_code[storeCode] : null;
const amount = storeData ? (storeData.amount || 0) : null;
const role = storeData ? (storeData.role || 'main') : 'main';
</code></pre>";

echo "<h3>皜祈岫鞈?</h3>";
if (isset($data['data']['stores_by_code'])) {
    echo "<p>皜祈岫摨? 277 ??????</p>";
    $store277 = $data['data']['stores_by_code']['277'] ?? null;
    if ($store277) {
        echo "<ul>";
        echo "<li>璆剔蜀: " . ($store277['amount'] !== null ? number_format($store277['amount']) : 'null') . "</li>";
        echo "<li>閫: " . ($store277['role'] ?? 'main') . "</li>";
        echo "<li>??? " . ($store277['status'] ?? 'N/A') . "</li>";
        echo "</ul>";
        
        // 璅⊥ JavaScript ??
        $amount = $store277['amount'] !== null ? $store277['amount'] : null;
        $role = $store277['role'] ?? 'main';
        
        echo "<p>JavaScript ??蝯?嚗?/p>";
        echo "<ul>";
        echo "<li>amount: " . ($amount !== null ? number_format($amount) : 'null') . "</li>";
        echo "<li>role: {$role}</li>";
        echo "<li>憿舐內: " . ($amount !== null ? number_format($amount) : '-') . "</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: orange;'>?? 摨? 277 瘝?鞈?</p>";
    }
}

// 皜 Session
session_destroy();

echo "<p style='margin-top: 30px; color: #666;'>皜祈岫摰???: " . date('Y-m-d H:i:s') . "</p>";
?>
