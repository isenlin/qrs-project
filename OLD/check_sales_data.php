<?php
/**
 * 瑼Ｘ璆剔蜀鞈??脣?雿蔭
 */

// ?? Session
session_start();

require_once __DIR__ . '/config/settings.php';

echo "<h1>瑼Ｘ璆剔蜀鞈??脣?雿蔭</h1>";

// 瑼Ｘ鞈?瑼?頝臬?
echo "<h2>鞈?瑼?頝臬?閮剖?</h2>";
echo "<pre>";
print_r($GLOBALS['config']['data_files']);
echo "</pre>";

// 瑼Ｘ DATA_PATH
echo "<h2>DATA_PATH 撣豢</h2>";
echo "<p>DATA_PATH: " . DATA_PATH . "</p>";

// 瑼Ｘ瑼??臬摮
echo "<h2>瑼Ｘ瑼??臬摮</h2>";

$files_to_check = [
    'users' => '雿輻????,
    'stores' => '摨?鞈?', 
    'sales_summary' => '璆剔蜀??鞈?'
];

foreach ($files_to_check as $key => $description) {
    $file_path = $GLOBALS['config']['data_files'][$key];
    $exists = file_exists($file_path);
    
    echo "<p>";
    echo "<strong>{$description} ({$key}.json):</strong> ";
    echo $exists ? "??瑼?摮" : "??瑼?銝???;
    echo "<br>";
    echo "頝臬?: " . htmlspecialchars($file_path);
    echo "</p>";
    
    if ($exists) {
        $file_size = filesize($file_path);
        $file_mtime = date('Y-m-d H:i:s', filemtime($file_path));
        echo "<p>瑼?憭批?: " . number_format($file_size) . " 雿?蝯?br>";
        echo "?敺耨?寞??? {$file_mtime}</p>";
        
        // 霈?蒂憿舐內?典??批捆
        if ($file_size > 0 && $file_size < 100000) { // ?芷＊蝷箏???100KB ??獢?            $content = file_get_contents($file_path);
            $data = json_decode($content, true);
            echo "<details><summary>?亦??批捆嚗? 10 蝑?</summary>";
            echo "<pre>";
            print_r(array_slice($data, 0, 10));
            echo "</pre>";
            echo "</details>";
        }
    }
    echo "<hr>";
}

// 皜祈岫 load_data ?賣
echo "<h2>皜祈岫 load_data ?賣</h2>";

try {
    $sales_summary = load_data('sales_summary');
    echo "<p>??load_data('sales_summary') ??</p>";
    echo "<p>鞈?蝑: " . count($sales_summary) . "</p>";
    
    if (!empty($sales_summary)) {
        echo "<details><summary>?亦? sales_summary 鞈?蝯?</summary>";
        echo "<pre>";
        // ?芷＊蝷箸?餈?5 憭拍?鞈?
        $recent_dates = array_slice(array_keys($sales_summary), 0, 5, true);
        foreach ($recent_dates as $date) {
            echo "?交?: {$date}\n";
            print_r($sales_summary[$date]);
            echo "\n";
        }
        echo "</pre>";
        echo "</details>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>??load_data('sales_summary') 憭望?: " . $e->getMessage() . "</p>";
}

// 皜祈岫?啁????脣??賣
echo "<h2>皜祈岫???脣??賣</h2>";

$test_date = date('Y-m-d');
$test_store = 'TEST001';
$test_amount = 8888;

echo "<p>皜祈岫?脣??桃?璆剔蜀:</p>";
echo "<ul>";
echo "<li>?交?: {$test_date}</li>";
echo "<li>摨?: {$test_store}</li>";
echo "<li>??: {$test_amount}</li>";
echo "</ul>";

try {
    $result = save_daily_sales($test_date, $test_store, $test_amount);
    echo "<p>??save_daily_sales() ??: " . ($result ? "?? : "??) . "</p>";
    
    // 霈??靘炎??    $test_month = substr($test_date, 0, 7);
    $saved_data = load_monthly_sales($test_month);
    echo "<p>?脣?敺??????賂?閰脫?隞踝?: " . count($saved_data) . " 憭?/p>";
    
    if (isset($saved_data[$test_date][$test_store])) {
        $loaded_amount = $saved_data[$test_date][$test_store]['amount'];
        echo "<p>霈??憿? {$loaded_amount} (" . ($loaded_amount == $test_amount ? "???寥?" : "??銝??) . ")</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>??save_daily_sales() 憭望?: " . $e->getMessage() . "</p>";
}

// 瑼Ｘ?桅?甈?
echo "<h2>瑼Ｘ?桅?甈?</h2>";

$directories_to_check = [
    DATA_PATH,
    DATA_PATH . '/sales',
    DATA_PATH . '/sales/daily',
    LOG_PATH
];

foreach ($directories_to_check as $dir) {
    echo "<p>";
    echo "<strong>" . htmlspecialchars($dir) . ":</strong> ";
    
    if (!file_exists($dir)) {
        echo "???桅?銝???;
        echo "<br>?岫撱箇??桅?: ";
        if (mkdir($dir, 0755, true)) {
            echo "????";
        } else {
            echo "??憭望?";
        }
    } else {
        echo "???桅?摮";
        echo "<br>?臬?航?: " . (is_readable($dir) ? "?? : "??);
        echo "<br>?臬?臬神: " . (is_writable($dir) ? "?? : "??);
    }
    echo "</p>";
}

// 瑼Ｘ隞璆剔蜀鞈?
echo "<h2>瑼Ｘ隞璆剔蜀鞈?</h2>";

$today = date('Y-m-d');
$sales_summary = load_data('sales_summary');

if (isset($sales_summary[$today])) {
    echo "<p>??隞 ({$today}) ?平蝮曇???/p>";
    echo "<p>隞摨?璆剔蜀蝑: " . count($sales_summary[$today]) . "</p>";
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>摨?隞??</th><th>??</th><th>?湔??</th></tr>";
    
    foreach ($sales_summary[$today] as $store_code => $data) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($store_code) . "</td>";
        echo "<td>NT$ " . number_format($data['amount'] ?? 0) . "</td>";
        echo "<td>" . htmlspecialchars($data['updated_at'] ?? '') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>??隞 ({$today}) 瘝?璆剔蜀鞈?</p>";
}

echo "<h2>皜祈岫摰?</h2>";
echo "<p><a href='store_dashboard.php'>餈?摨??銵冽</a></p>";
echo "<p><a href='index.php'>餈??餃?</a></p>";
?>
