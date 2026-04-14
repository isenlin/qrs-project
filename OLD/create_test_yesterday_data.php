<?php
/**
 * 撱箇?皜祈岫?函??冽璆剔蜀鞈?
 */

require_once __DIR__ . '/config/settings.php';

echo "<h1>撱箇?皜祈岫?函??冽璆剔蜀鞈?</h1>";

// 頛摨?鞈?
$stores = load_data('stores');
$yesterday = date('Y-m-d', strtotime('-1 day'));
$month = substr($yesterday, 0, 7);

echo "<p>?冽?交?: {$yesterday}</p>";
echo "<p>?遢: {$month}</p>";

// 頛?暹??平蝮曇???$sales_summary = load_monthly_sales($month);

// 撱箇?皜祈岫鞈?
$test_data = [];
foreach ($stores as $store) {
    $store_code = $store['code'];
    
    // ?冽?瘙箏??臬?平蝮橘?70%璈??平蝮橘?
    if (rand(1, 10) <= 7) {
        // ?冽?璆剔蜀??嚗?000-50000嚗?        $amount = rand(10, 50) * 1000;
        
        // ?冽?瘙箏??臬?箔誨?剝?殷?20%璈?嚗?        $role = (rand(1, 10) <= 2) ? 'substitute' : 'main';
        
        $test_data[$store_code] = [
            'amount' => $amount,
            'role' => $role,
            'store_code' => $store_code,
            'timestamp' => $yesterday . ' ' . sprintf('%02d:%02d:%02d', rand(8, 20), rand(0, 59), rand(0, 59))
        ];
    }
}

// ?湔璆剔蜀鞈?
if (!isset($sales_summary[$yesterday])) {
    $sales_summary[$yesterday] = [];
}

foreach ($test_data as $store_code => $sales_data) {
    $sales_summary[$yesterday][$store_code] = $sales_data;
}

// ?脣?鞈?
$result = save_monthly_sales($month, $sales_summary);

if ($result) {
    echo "<p style='color: green;'>??皜祈岫鞈?撱箇???嚗?/p>";
    
    // 憿舐內撱箇?????    echo "<h2>撱箇??葫閰西???/h2>";
    echo "<p>蝮賢??" . count($test_data) . " ??瑹遣蝡??冽璆剔蜀鞈?</p>";
    
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
    echo "<tr><th>摨?隞??</th><th>璆剔蜀??</th><th>閫</th><th>??</th></tr>";
    
    $count = 0;
    foreach ($test_data as $store_code => $sales_data) {
        if ($count < 10) { // ?芷＊蝷箏?10蝑?            echo "<tr>";
            echo "<td>{$store_code}</td>";
            echo "<td style='text-align: right;'>" . number_format($sales_data['amount']) . "</td>";
            echo "<td>" . ($sales_data['role'] === 'substitute' ? '隞?' : '銝餅?') . "</td>";
            echo "<td>{$sales_data['timestamp']}</td>";
            echo "</tr>";
            $count++;
        }
    }
    
    if (count($test_data) > 10) {
        echo "<tr><td colspan='4' style='text-align: center;'>... ?? " . (count($test_data) - 10) . " 蝑???/td></tr>";
    }
    
    echo "</table>";
    
    // 蝯梯?鞈?
    $total_amount = 0;
    $substitute_count = 0;
    foreach ($test_data as $sales_data) {
        $total_amount += $sales_data['amount'];
        if ($sales_data['role'] === 'substitute') {
            $substitute_count++;
        }
    }
    
    echo "<h2>蝯梯?鞈?</h2>";
    echo "<ul>";
    echo "<li>蝮賢?瑹: " . count($stores) . "</li>";
    echo "<li>?平蝮曄?摨??? " . count($test_data) . " (" . round(count($test_data) / count($stores) * 100, 1) . "%)</li>";
    echo "<li>蝮賣平蝮暸?憿? " . number_format($total_amount) . "</li>";
    echo "<li>隞??瑕?? {$substitute_count} (" . round($substitute_count / count($test_data) * 100, 1) . "%)</li>";
    echo "<li>撟喳?瘥?瑹平蝮? " . number_format(round($total_amount / max(1, count($test_data)))) . "</li>";
    echo "</ul>";
    
    echo "<h2>皜祈岫???</h2>";
    echo "<ol>";
    echo "<li><a href='test_yesterday_api.php' target='_blank'>皜祈岫 API ??</a></li>";
    echo "<li><a href='dashboard.php' target='_blank'>皜祈岫?銵冽?冽璆剔蜀?</a></li>";
    echo "<li><a href='sales/monthly_report.php?month={$month}' target='_blank'>?亦??漲?梯”</a></li>";
    echo "</ol>";
    
    echo "<h2>瘜冽?鈭?</h2>";
    echo "<ul>";
    echo "<li>???舀葫閰西???銝?敶梢?祕?平蝮曇???/li>";
    echo "<li>皜祈岫摰?敺隞亙?日?鞈?</li>";
    echo "<li>鞈??脣??? data/sales/{$month}.json</li>";
    echo "</ul>";
} else {
    echo "<p style='color: red;'>??皜祈岫鞈?撱箇?憭望?</p>";
    echo "<p>隢炎??data/sales/ ?桅??神?交???/p>";
}

echo "<p style='margin-top: 30px; color: #666;'>撱箇???: " . date('Y-m-d H:i:s') . "</p>";
?>
