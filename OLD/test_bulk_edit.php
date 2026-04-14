<?php
/**
 * 皜祈岫?寥?蝺刻摩?漲璆剔蜀?
 */

// ?? Session
session_start();

require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/auth.php';

echo "<h1>皜祈岫?寥?蝺刻摩?漲璆剔蜀?</h1>";

// 璅⊥蝞∠??∠??$_SESSION['user_id'] = 'admin';
$_SESSION['username'] = 'admin';
$_SESSION['name'] = '蝟餌絞蝞∠???;
$_SESSION['role'] = 'admin';
$_SESSION['logged_in'] = true;

$user = get_current_session_user();

echo "<h2>?隤芣?</h2>";
echo "<p>???賢?閮梁恣?銝甈∠楊頛舀???瘥?摨?瘥?憭拍?璆剔蜀??嚗翰?耨甇?府????瑹?璆剔蜀鞈???/p>";

echo "<h3>銝餉??嚗?/h3>";
echo "<ol>";
echo "<li><strong>?豢??遢</strong>嚗隞仿??蝺刻摩??隞?/li>";
echo "<li><strong>?寥?蝺刻摩銵冽</strong>嚗＊蝷箄府????瑹?銝憭拍?璆剔蜀頛詨獢?/li>";
echo "<li><strong>閫?豢?</strong>嚗???憿?臭誑?豢?銝餅??誨??/li>";
echo "<li><strong>敹恍?雿?/strong>嚗?‵撖怒?刻身????蝛箸???/li>";
echo "<li><strong>銝?萄摮?/strong>嚗?甈∪摮??耨??/li>";
echo "<li><strong>鞈?撽?</strong>嚗Ⅱ靽撓?亦?鞈?甇?Ⅱ</li>";
echo "</ol>";

echo "<h2>?銵瑽?/h2>";

echo "<h3>瑼?蝯?嚗?/h3>";
echo "<ul>";
echo "<li><strong>admin_bulk_edit.php</strong>嚗蜓閬??賡???/li>";
echo "<li><strong>dashboard.php</strong>嚗恣???憛溶???</li>";
echo "<li><strong>settings.php</strong>嚗蝙?函?? load_monthly_sales ??save_monthly_sales ?賣</li>";
echo "</ul>";

echo "<h3>鞈?瘚?嚗?/h3>";
echo "<pre><code>1. 雿輻???隞?2. 頛閰脫?隞賜?璆剔蜀鞈? (load_monthly_sales)
3. 憿舐內蝺刻摩銵冽
4. 雿輻?楊頛航???5. ?園??????(JavaScript)
6. 頧???JSON ?澆?
7. ?漱?唬撩? (PHP)
8. ?湔鞈?摨?(save_monthly_sales)
9. 憿舐內??閮</code></pre>";

echo "<h2>蝡皜祈岫</h2>";

echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;'>";

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; border: 2px solid #28a745;'>";
echo "<h3 style='color: #28a745;'>?寥?蝺刻摩?</h3>";
echo "<p><strong>銝餉?皜祈岫?</strong></p>";
echo "<p>???豢??遢?汗</p>";
echo "<p>??蝺刻摩璆剔蜀??</p>";
echo "<p>???豢?銝餅?/隞?</p>";
echo "<p>??敹恍?雿???/p>";
echo "<p><a href='admin/bulk_edit_monthly.php' target='_blank' style='display: inline-block; background: #28a745; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold;'>皜祈岫?寥?蝺刻摩</a></p>";
echo "</div>";

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; border: 2px solid #007bff;'>";
echo "<h3 style='color: #007bff;'>蝞∠??∪?銵冽</h3>";
echo "<p><strong>??亙皜祈岫</strong></p>";
echo "<p>??蝞∠??∪??典??賢?憛?/p>";
echo "<p>???寥?蝺刻摩璆剔蜀???</p>";
echo "<p>??鈭箏蝞∠????</p>";
echo "<p>??摨?蝞∠????</p>";
echo "<p><a href='dashboard.php' target='_blank' style='display: inline-block; background: #007bff; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold;'>皜祈岫蝞∠??∪?銵冽</a></p>";
echo "</div>";

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; border: 2px solid #6c757d;'>";
echo "<h3 style='color: #6c757d;'>甈?皜祈岫</h3>";
echo "<p><strong>閫甈?撽?</strong></p>";
echo "<p>?? ?恣??⊥?閮芸?</p>";
echo "<p>??蝞∠??∪隞交迤撣訾蝙??/p>";
echo "<p>?? ?芸?????霅?/p>";
echo "</div>";

echo "</div>";

echo "<h2>皜祈岫甇仿?</h2>";

echo "<h3>皜祈岫 1嚗恣??銵冽</h3>";
echo "<ol>";
echo "<li>閮芸? dashboard.php嚗恣??餃???</li>";
echo "<li><strong>蝣箄?嚗?/strong>??恣????憛?/li>";
echo "<li><strong>蝣箄?嚗?/strong>???楊頛舀平蝮整???蝬嚗?/li>";
echo "<li>暺???楊頛舀平蝮整???/li>";
echo "<li><strong>蝣箄?嚗?/strong>頝唾???admin_bulk_edit.php</li>";
echo "</ol>";

echo "<h3>皜祈岫 2嚗?楊頛舫???/h3>";
echo "<ol>";
echo "<li>閮芸? admin_bulk_edit.php</li>";
echo "<li><strong>蝣箄?嚗?/strong>憿舐內?嗅??遢?楊頛航”??/li>";
echo "<li><strong>蝣箄?嚗?/strong>銵冽憿舐內???瑹??交?</li>";
echo "<li>雿輻?遢?豢??典???嗡??遢</li>";
echo "<li><strong>蝣箄?嚗?/strong>銵冽?批捆?湔?粹摰?隞?/li>";
echo "<li>?刻”?潔葉蝺刻摩銝鈭?憿?/li>";
echo "<li>皜祈岫??‵撖怎征?賬???/li>";
echo "<li>皜祈岫??刻身??????/li>";
echo "<li>皜祈岫??蝛箸?????/li>";
echo "<li>暺??摮????氬???/li>";
echo "<li><strong>蝣箄?嚗?/strong>憿舐內蝣箄?撠店獢?/li>";
echo "<li><strong>蝣箄?嚗?/strong>?脣?敺＊蝷箸?????/li>";
</ol>";

echo "<h3>皜祈岫 3嚗???霅?/h3>";
echo "<ol>";
echo "<li>?餃蝞∠??∪董??/li>";
echo "<li>隞交平?澈隞賜??/li>";
echo "<li>?岫閮芸? admin_bulk_edit.php</li>";
echo "<li><strong>蝣箄?嚗?/strong>?芸???? dashboard.php</li>";
echo "<li><strong>蝣箄?嚗?/strong>???啜?楊頛舀平蝮整???/li>";
echo "</ol>";

echo "<h2>鞈?撽?皜祈岫</h2>";

// 撱箇?皜祈岫鞈?
$test_month = date('Y-m');
$test_date = date('Y-m-d');
$test_store = '277';
$test_amount = 9999;
$test_role = 'substitute';

echo "<h3>撱箇?皜祈岫鞈?嚗?/h3>";

if (function_exists('save_daily_sales_with_role')) {
    // ?遢?暹?鞈?
    $monthly_sales = load_monthly_sales($test_month);
    $backup_data = isset($monthly_sales[$test_date][$test_store]) ? $monthly_sales[$test_date][$test_store] : null;
    
    // 撱箇?皜祈岫鞈?
    $result = save_daily_sales_with_role($test_date, $test_store, $test_amount, $test_role);
    
    if ($result) {
        echo "<p style='color: green;'>??皜祈岫鞈?撱箇???</p>";
        echo "<p>?交?: {$test_date} | 摨?: {$test_store} | ??: {$test_amount} | 閫: {$test_role}</p>";
        
        // 撽?鞈?
        $monthly_sales = load_monthly_sales($test_month);
        if (isset($monthly_sales[$test_date][$test_store])) {
            $saved_data = $monthly_sales[$test_date][$test_store];
            echo "<p>?脣?????</p>";
            echo "<pre>" . print_r($saved_data, true) . "</pre>";
        }
        
        // ?脣??遢
        $_SESSION['bulk_test_backup'] = $backup_data;
        $_SESSION['bulk_test_date'] = $test_date;
        $_SESSION['bulk_test_store'] = $test_store;
        $_SESSION['bulk_test_month'] = $test_month;
        
        echo "<p style='color: green;'>???遢撌脣摮?/p>";
    } else {
        echo "<p style='color: red;'>??皜祈岫鞈?撱箇?憭望?</p>";
    }
}

echo "<h2>皜?皜祈岫鞈?</h2>";

if (isset($_POST['cleanup']) && isset($_SESSION['bulk_test_backup'])) {
    $month = $_SESSION['bulk_test_month'];
    $date = $_SESSION['bulk_test_date'];
    $store = $_SESSION['bulk_test_store'];
    $backup = $_SESSION['bulk_test_backup'];
    
    $monthly_sales = load_monthly_sales($month);
    
    if ($backup === null) {
        // ?芷皜祈岫鞈?
        if (isset($monthly_sales[$date][$store])) {
            unset($monthly_sales[$date][$store]);
        }
    } else {
        // ?Ｗ儔?遢鞈?
        $monthly_sales[$date][$store] = $backup;
    }
    
    save_monthly_sales($month, $monthly_sales);
    
    echo "<p style='color: green;'>??皜祈岫鞈?撌脫???/p>";
    
    unset($_SESSION['bulk_test_backup']);
    unset($_SESSION['bulk_test_date']);
    unset($_SESSION['bulk_test_store']);
    unset($_SESSION['bulk_test_month']);
}

echo "<form method='POST' action=''>";
echo "<button type='submit' name='cleanup' style='padding: 10px 20px; background: #dc3545; color: white; border: none; border-radius: 5px; cursor: pointer;'>皜?皜祈岫鞈?</button>";
echo "</form>";

// 皜 Session嚗鈭?隞質???
$backup_data = $_SESSION['bulk_test_backup'] ?? null;
$backup_info = [
    'date' => $_SESSION['bulk_test_date'] ?? null,
    'store' => $_SESSION['bulk_test_store'] ?? null,
    'month' => $_SESSION['bulk_test_month'] ?? null
];

session_destroy();

if ($backup_data !== null) {
    session_start();
    $_SESSION['bulk_test_backup'] = $backup_data;
    $_SESSION['bulk_test_date'] = $backup_info['date'];
    $_SESSION['bulk_test_store'] = $backup_info['store'];
    $_SESSION['bulk_test_month'] = $backup_info['month'];
}

echo "<p><a href='final_verification.php'>餈??蝯?霅?/a> | <a href='test_all_substitute_marks.php'>餈?隞?璅?皜祈岫</a></p>";
?>
