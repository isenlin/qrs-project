<?php
/**
 * ?蝯?霅?- ???憿耨甇?Ⅱ隤? */

// ?? Session
session_start();

require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/auth.php';

echo "<h1>?蝯?霅?- ???憿耨甇?Ⅱ隤?/h1>";
echo "<p>撽???: " . date('Y-m-d H:i:s') . "</p>";

// 璅⊥摨??餃
$_SESSION['user_id'] = '277';
$_SESSION['username'] = '277';
$_SESSION['name'] = '277敺抵?摨?;
$_SESSION['role'] = 'store';
$_SESSION['stores'] = ['277'];
$_SESSION['logged_in'] = true;

$user = get_current_session_user();

echo "<h2>????靽格迤蝮賜?</h2>";

echo "<h3>?? 1嚗tore_dashboard.php AJAX ??</h3>";
echo "<table border='1' cellpadding='10' style='width: 100%;'>";
echo "<tr><th>??</th><th>?寞??</th><th>閫?捱?寞?</th><th>???/th></tr>";
echo "<tr><td>?餅?璆剔蜀敺撓?交?雿?蝛箇</td><td>AJAX ??敺隤文皜征頛詨獢?瘝?????</td><td>?寧?喟絞銵典?漱嚗??ａ??啗??乩?霅???甇?/td><td>??撌脖耨甇?/td></tr>";
echo "<tr><td>隞璆剔蜀銝???/td><td>AJAX ??銝剔? DOM ?豢??典?賡隤歹?蝯梯?鞈?瘝?甇?Ⅱ?湔</td><td>?喟絞?漱敺??啗?蝞蒂憿舐內??啁絞閮?/td><td>??撌脖耨甇?/td></tr>";
echo "<tr><td>?餅?璆剔蜀??銝??/td><td>JavaScript ?????園隤?/td><td>雿輻蝪∪??disabled/enabled ?批嚗?閬箏?擖?蝣?/td><td>??撌脖耨甇?/td></tr>";
echo "</table>";

echo "<h3>?? 2嚗onthly_report.php ?收璇???</h3>";
echo "<table border='1' cellpadding='10' style='width: 100%;'>";
echo "<tr><th>??</th><th>?寞??</th><th>閫?捱?寞?</th><th>???/th></tr>";
echo "<tr><td>?收璇?憭望?嚗????賣?</td><td>CSS ?芸?蝝?憿??嗡?璅??閬?鈭?擐祆?蝝?/td><td>雿輻?渡摰??豢??剁?瘛餃? !important嚗Ⅱ靽??隞??航閬?</td><td>??撌脖耨甇?/td></tr>";
echo "</table>";

echo "<h3>?? 3嚗onthly_report.php ???</h3>";
echo "<table border='1' cellpadding='10' style='width: 100%;'>";
echo "<tr><th>??</th><th>?寞??</th><th>閫?捱?寞?</th><th>???/th></tr>";
echo "<tr><td>?芸??啗”?潭??仃??/td><td>銴????箄?蝒?頛臬?質◤?汗?券??/td><td>蝪∪???賣嚗蝙?冽??body ?批捆?撘???/td><td>??撌脖耨甇?/td></tr>";
echo "<tr><td>??湧???憭望?</td><td>JavaScript ?賣?航??航炊?葉??/td><td>靽?蝪∪??window.print() 雿??</td><td>??撌脖耨甇?/td></tr>";
echo "</table>";

echo "<h2>?? 蝡皜祈岫???</h2>";

echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;'>";

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; border: 2px solid #28a745;'>";
echo "<h3 style='color: #28a745;'>store_dashboard.php</h3>";
echo "<p><strong>?喟絞銵典?漱?</strong></p>";
echo "<p>??頛詨甈?銝?霈征??/p>";
echo "<p>??隞璆剔蜀?單??湔</p>";
echo "<p>???????嗆迤蝣?/p>";
echo "<p><a href='store_dashboard.php' target='_blank' style='display: inline-block; background: #28a745; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold;'>皜祈岫靽格迤?</a></p>";
echo "</div>";

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; border: 2px solid #007bff;'>";
echo "<h3 style='color: #007bff;'>monthly_report.php</h3>";
echo "<p><strong>?收璇? + ??靽格迤</strong></p>";
echo "<p>???＊??擐祆?蝝???/p>";
echo "<p>???芸??啗”?澆??賣迤撣?/p>";
echo "<p>????湧??甇?虜</p>";
$current_month = date('Y-m');
echo "<p><a href='sales/monthly_report.php?month={$current_month}' target='_blank' style='display: inline-block; background: #007bff; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold;'>皜祈岫?漲?梯”</a></p>";
echo "</div>";

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; border: 2px solid #6c757d;'>";
echo "<h3 style='color: #6c757d;'>皜祈岫撌亙</h3>";
echo "<p><strong>閮箸??霅極??/strong></p>";
echo "<p><a href='diagnose_store_dashboard.php' target='_blank' style='display: inline-block; background: #6c757d; color: white; padding: 8px 15px; border-radius: 5px; text-decoration: none; margin: 5px;'>閮箸撌亙</a></p>";
echo "<p><a href='test_simple_version.php' target='_blank' style='display: inline-block; background: #6c757d; color: white; padding: 8px 15px; border-radius: 5px; text-decoration: none; margin: 5px;'>蝪∪??皜祈岫</a></p>";
echo "<p><a href='test_comprehensive_fixes.php' target='_blank' style='display: inline-block; background: #6c757d; color: white; padding: 8px 15px; border-radius: 5px; text-decoration: none; margin: 5px;'>蝬?皜祈岫</a></p>";
echo "</div>";

echo "</div>";

echo "<h2>?? 皜祈岫甇仿?蝣箄?</h2>";

echo "<h3>store_dashboard.php 皜祈岫甇仿?嚗?/h3>";
echo "<ol>";
echo "<li>閮芸? store_dashboard.php</li>";
echo "<li>?豢??蜓瑹??誨?准??莎???璅???寡?嚗?/li>";
echo "<li>頛詨璆剔蜀??嚗?憒?12345嚗?/li>";
echo "<li>暺???平蝮整?????霈??莎?</li>";
echo "<li><strong>蝣箄?嚗??ａ??啗???/strong></li>";
echo "<li><strong>蝣箄?嚗＊蝷箸?????/strong></li>";
echo "<li><strong>蝣箄?嚗??交平蝮曄絞閮?啁?圈?憿?/strong></li>";
echo "<li><strong>蝣箄?嚗?憿撓?交?皜征嚗?敺?銝甈∟撓??/strong></li>";
echo "<li><strong>蝣箄?嚗??脤????霈?/strong></li>";
echo "<li>?活頛詨??嚗葫閰行?血隞亙?甈⊥?鈭?/li>";
echo "</ol>";

echo "<h3>monthly_report.php 皜祈岫甇仿?嚗?/h3>";
echo "<ol>";
echo "<li>閮芸??漲?梯”?</li>";
echo "<li><strong>蝣箄?嚗??貉??舀滓?嚗?貉??舀滓璈</strong></li>";
echo "<li><strong>蝣箄?嚗?曌??銵??脰?瘛?/strong></li>";
echo "<li>暺?????芸??啗”?潦???/li>";
echo "<li><strong>蝣箄?嚗孛?澆??啣?閰望?嚗?銵冽?批捆</strong></li>";
echo "<li>暺????唳??????嚗?/li>";
echo "<li><strong>蝣箄?嚗孛?澆??啣?閰望?嚗??唳????/strong></li>";
echo "</ol>";

echo "<h2>??儭??銵祕?曄敦蝭</h2>";

echo "<h3>store_dashboard.php 靽格迤閬?嚗?/h3>";
echo "<pre><code>// 1. ?喟絞銵典?漱??
if (\$_SERVER['REQUEST_METHOD'] === 'POST' && isset(\$_POST['amount']) && isset(\$_POST['selected_role'])) {
    \$amount = (int)\$_POST['amount'];
    \$role = \$_POST['selected_role'];
    
    if (\$amount > 0 && in_array(\$role, ['main', 'substitute'])) {
        \$result = save_daily_sales_with_role(\$today, \$store_code, \$amount, \$role);
        
        if (\$result) {
            // ?閮?蝯梯?鞈?
            \$sales_summary = load_monthly_sales(\$current_month);
            // ... ?閮?銝阡＊蝷?            \$success_message = '璆剔蜀?餅???嚗?;
        }
    }
}

// 2. 蝪∪??JavaScript ?批
function selectRole(role) {
    selectedRole = role;
    document.getElementById('selected-role').value = role;
    // 閬死????????}</code></pre>";

echo "<h3>monthly_report.php 靽格迤閬?嚗?/h3>";
echo "<pre><code>/* CSS ?收璇?靽格迤 */
table.monthly-table tbody tr:nth-of-type(odd) {
    background-color: #f0f8ff !important;
}
table.monthly-table tbody tr:nth-of-type(even) {
    background-color: #fff8f0 !important;
}
table.monthly-table tbody tr {
    background-color: transparent !important;
}

/* JavaScript ??賣蝪∪? */
function printReport() {
    try {
        const originalContent = document.body.innerHTML;
        const tableContainer = document.querySelector('.table-container');
        document.body.innerHTML = tableContainer.innerHTML;
        window.print();
        document.body.innerHTML = originalContent;
    } catch (error) {
        window.print(); // ?
    }
}</code></pre>";

echo "<h2>?? 瑼??遢?敺?/h2>";

echo "<h3>撌脣遣蝡??遢瑼?嚗?/h3>";
echo "<ul>";
echo "<li><code>store_dashboard_backup_ajax.php</code> - ????AJAX ??遢</li>";
echo "<li><code>store_dashboard_simple.php</code> - 蝪∪??嚗??嗅? store_dashboard.php ?詨?嚗?/li>";
echo "</ul>";

echo "<h3>憒??閬敺拙??嚗?/h3>";
echo "<pre><code># ?Ｗ儔 AJAX ?
cp store_dashboard_backup_ajax.php store_dashboard.php

# ?蝙?函陛????cp store_dashboard_simple.php store_dashboard.php</code></pre>";

echo "<h2>? 蝺亙?憿???/h2>";

echo "<h3>憒???隞摮嚗?/h3>";
echo "<ol>";
echo "<li><strong>皜?汗?典翰??/strong>嚗trl+Shift+Delete ??Ctrl+F5</li>";
echo "<li><strong>瑼Ｘ JavaScript ?航炊</strong>嚗? F12 ??Console 璅惜</li>";
echo "<li><strong>瑼Ｘ PHP ?航炊</strong>嚗?撩??航炊?亥?</li>";
echo "<li><strong>皜祈岫蝪∪獢?</strong>嚗蝙?刻那?瑕極?瑟?箏?憿?/li>";
echo "<li><strong>?Ｗ儔?遢</strong>嚗???閬??Ｗ儔?啁帘摰???/li>";
</ol>";

echo "<h3>?舐窗?舀嚗?/h3>";
echo "<p>憒???葫閰阡憭望?嚗???隞乩?鞈?嚗?/p>";
echo "<ul>";
echo "<li>?汗?典?蝔勗??</li>";
echo "<li>Console 銝剔??航炊閮嚗??</li>";
echo "<li>Network 璅惜銝剔?隢?/??嚗??</li>";
echo "<li>?琿???雿郊撽???蝯?</li>";
echo "</ul>";

echo "<h2>???蝯Ⅱ隤?/h2>";

echo "<div style='background: #d4edda; color: #155724; padding: 20px; border-radius: 10px; border: 1px solid #c3e6cb;'>";
echo "<h3 style='color: #155724;'>???憿歇靽格迤嚗?/h3>";
echo "<p><strong>store_dashboard.php</strong>嚗?典蝯梯”?格?鈭歹?閫?捱 AJAX ??</p>";
echo "<p><strong>monthly_report.php</strong>嚗耨甇??擐祆?蝝???</p>";
echo "<p><strong>皜祈岫撌亙</strong>嚗?靘??渡?閮箸??霅極??/p>";
echo "<p><strong>?遢瑼?</strong>嚗???憪??砍?隞踝??舫?敺?/p>";
echo "<p><strong>蝟餌絞???/strong>嚗?冽?閰脣??冽迤撣賊?雿?/p>";
echo "</div>";

// 皜 Session
session_destroy();

echo "<p style='margin-top: 30px; text-align: center; color: #666;'>撽?摰???: " . date('Y-m-d H:i:s') . "</p>";
?>
