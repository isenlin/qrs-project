<?php
/**
 * 皜祈岫瘥璆剔蜀?亥岷?
 */

// ?? Session
session_start();

require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/auth.php';

echo "<h1>皜祈岫瘥璆剔蜀?亥岷?</h1>";

// 皜祈岫蝞∠??∠??$_SESSION['user_id'] = 'admin';
$_SESSION['username'] = 'admin';
$_SESSION['name'] = '蝟餌絞蝞∠???;
$_SESSION['role'] = 'admin';
$_SESSION['logged_in'] = true;

$user = get_current_session_user();

echo "<h2>皜祈岫???/h2>";
echo "<p>雿輻?? " . $user['name'] . " (" . $user['role'] . ")</p>";
echo "<p>皜祈岫??: " . date('Y-m-d H:i:s') . "</p>";

echo "<h2>?啣??賭?蝝?/h2>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 30px;'>";
echo "<h3>?? 瘥璆剔蜀?亥岷?</h3>";
echo "<p>???亙?摨?璆剔蜀?”?澆歇蝘駁嚗?箸?暑???交平蝮暹閰Ｗ??踝?</p>";
echo "<ul>";
echo "<li><strong>敹恍????/strong>嚗??乓?乓??乓??勗?</li>";
echo "<li><strong>?芾??交??豢?</strong>嚗蝙?冽???亦??孵??交?</li>";
echo "<li><strong>敶閬?憿舐內</strong>嚗??湔平蝮曇”?潘??舀 CSV ?臬</li>";
echo "<li><strong>閫甈??批</strong>嚗????脩??唬???摨?鞈?</li>";
echo "<li><strong>隞??瑕璅?</strong>嚗??唳?蝷箔誨?剝??/li>";
echo "</ul>";
echo "</div>";

echo "<h2>皜祈岫???</h2>";

echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;'>";

// 皜祈岫銝?閫
$test_roles = [
    'admin' => [
        'id' => 'admin',
        'username' => 'admin',
        'name' => '蝟餌絞蝞∠???,
        'role' => 'admin',
        'color' => '#dc3545',
        'stores_count' => 16
    ],
    'sales' => [
        'id' => 'U004',
        'username' => 'sales1',
        'name' => '?喳之??,
        'role' => 'sales',
        'color' => '#007bff',
        'stores_count' => '鞎痊??瑹'
    ],
    'supervisor' => [
        'id' => 'S002',
        'username' => 'supervisor1',
        'name' => '???,
        'role' => 'supervisor',
        'color' => '#fd7e14',
        'stores_count' => '鞎痊??瑹'
    ]
];

foreach ($test_roles as $role => $user_info) {
    echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; border: 2px solid {$user_info['color']};'>";
    echo "<h3 style='color: {$user_info['color']};'>{$user_info['name']} ({$role})</h3>";
    echo "<p><strong>皜祈岫閫嚗?/strong>{$role}</p>";
    echo "<p><strong>?????瑹?</strong>{$user_info['stores_count']}</p>";
    
    // 璅⊥?餃
    $_SESSION['user_id'] = $user_info['id'];
    $_SESSION['username'] = $user_info['username'];
    $_SESSION['name'] = $user_info['name'];
    $_SESSION['role'] = $user_info['role'];
    $_SESSION['logged_in'] = true;
    
    $user = get_current_session_user();
    $stores = load_data('stores');
    
    // ?寞?閫蝭拚摨?
    $user_stores = [];
    if ($user['role'] === 'admin') {
        $user_stores = $stores;
    } else {
        foreach ($stores as $store) {
            if ($user['role'] === 'sales' && $store['sales_person'] === $user['id']) {
                $user_stores[] = $store;
            } elseif ($user['role'] === 'supervisor' && $store['supervisor'] === $user['id']) {
                $user_stores[] = $store;
            }
        }
    }
    
    echo "<p><strong>撖阡?鞎痊摨??賂?</strong>" . count($user_stores) . "</p>";
    
    if (count($user_stores) > 0) {
        echo "<p><strong>????瑹?</strong></p>";
        echo "<ul>";
        for ($i = 0; $i < min(3, count($user_stores)); $i++) {
            echo "<li>{$user_stores[$i]['code']} - {$user_stores[$i]['name']}</li>";
        }
        if (count($user_stores) > 3) {
            echo "<li>... ?? " . (count($user_stores) - 3) . " ??瑹?/li>";
        }
        echo "</ul>";
    }
    
    echo "<p><a href='dashboard.php' target='_blank' style='display: inline-block; background: {$user_info['color']}; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold; width: 100%; text-align: center;'>皜祈岫 {$role} ?銵冽</a></p>";
    echo "</div>";
}

echo "</div>";

echo "<h2>?皜祈岫甇仿?</h2>";

echo "<h3>皜祈岫 1嚗??ａ?霅?/h3>";
echo "<ol>";
echo "<li>?餃 dashboard.php</li>";
echo "<li><strong>蝣箄?嚗?/strong>瘝???亙?摨?璆剔蜀?”??/li>";
echo "<li><strong>蝣箄?嚗?/strong>????交平蝮暹閰Ｕ?憛?/li>";
echo "<li><strong>蝣箄?嚗?/strong>??翰?????隞??乓??乓??勗?嚗?/li>";
echo "<li><strong>蝣箄?嚗?/strong>??交??豢??典??亥岷??</li>";
echo "</ol>";

echo "<h3>皜祈岫 2嚗翰?????/h3>";
echo "<ol>";
echo "<li>暺????乓???/li>";
echo "<li><strong>蝣箄?嚗?/strong>敶閬?憿舐內隞璆剔蜀</li>";
echo "<li>暺???乓???/li>";
echo "<li><strong>蝣箄?嚗?/strong>敶閬?憿舐內?冽璆剔蜀</li>";
echo "<li>暺????乓???/li>";
echo "<li><strong>蝣箄?嚗?/strong>敶閬?憿舐內?璆剔蜀</li>";
echo "<li>暺????勗?????/li>";
echo "<li><strong>蝣箄?嚗?/strong>敶閬?憿舐內銝?勗?璆剔蜀</li>";
echo "</ol>";

echo "<h3>皜祈岫 3嚗閮?閰?/h3>";
echo "<ol>";
echo "<li>雿輻?交??豢??券?????/li>";
echo "<li>暺??閰Ｕ???/li>";
echo "<li><strong>蝣箄?嚗?/strong>敶閬?憿舐內?豢??交??平蝮?/li>";
echo "<li>皜祈岫銝????隞予銋?嚗?/li>";
echo "</ol>";

echo "<h3>皜祈岫 4嚗??箄?蝒???/h3>";
echo "<ol>";
echo "<li>蝣箄?敶閬?璅?憿舐內甇?Ⅱ?交?</li>";
echo "<li>蝣箄?銵冽憿舐內甇?Ⅱ??瑹???/li>";
echo "<li>蝣箄??平蝮曄?摨?憿舐內??</li>";
echo "<li>蝣箄?瘝?璆剔蜀??瑹＊蝷箝???/li>";
echo "<li>蝣箄?隞??瑕??隞?)??閮?/li>";
echo "<li>蝣箄?蝯梯???甇?Ⅱ憿舐內</li>";
echo "<li>皜祈岫 CSV ?臬?</li>";
echo "<li>皜祈岫???嚗??????具SC?蛛?</li>";
echo "</ol>";

echo "<h3>皜祈岫 5嚗??脫???/h3>";
echo "<ol>";
echo "<li>隞亦恣?頨思遢?餃</li>";
echo "<li><strong>蝣箄?嚗?/strong>????瑹?璆剔蜀</li>";
echo "<li>隞交平?澈隞賜??/li>";
echo "<li><strong>蝣箄?嚗?/strong>?芰??啗撌梯?鞎祉?摨?璆剔蜀</li>";
echo "<li>隞亦撠澈隞賜??/li>";
echo "<li><strong>蝣箄?嚗?/strong>?芰??啗撌梯?鞎祉?摨?璆剔蜀</li>";
echo "</ol>";

echo "<h2>?銵瑽?/h2>";

echo "<h3>靽格??獢?/h3>";
echo "<ul>";
echo "<li><strong>dashboard.php</strong>嚗宏?扎?亙?摨?璆剔蜀?”?潘??啣????交平蝮暹閰Ｕ???/li>";
echo "<li><strong>get_yesterday_sales.php</strong>嚗?啗酉閫???舀隞餅??交??亥岷</li>";
echo "</ul>";

echo "<h3>?啣??</h3>";
echo "<pre><code>// 憿舐內瘥璆剔蜀
function showDailySales(date) {
    // ?湔敶閬?璅?
    document.querySelector('.yesterday-header h3').textContent = date + ' ??瑹平蝮?;
    // 頛鞈?
    loadDailySales(date);
}

// 敹恍????&lt;button onclick=\"showDailySales('2026-03-24')\"&gt;隞&lt;/button&gt;
&lt;button onclick=\"showDailySales('2026-03-23')\"&gt;?冽&lt;/button&gt;

// ?芾??交??豢?
&lt;input type=\"date\" id=\"custom-date\"&gt;
&lt;button onclick=\"showCustomDateSales()\"&gt;?亥岷&lt;/button&gt;
</code></pre>";

echo "<h2>?芷???</h2>";
echo "<div style='background: #e7f3ff; padding: 20px; border-radius: 10px;'>";
echo "<h3>?? 隞蝪∪?</h3>";
echo "<ul>";
echo "<li><strong>皜?鞈???</strong>嚗宏?文摰?隞璆剔蜀銵冽</li>";
echo "<li><strong>???暑??/strong>嚗蝙?刻隞交?遙???璆剔蜀</li>";
echo "<li><strong>?孵?雿輻??撽?/strong>嚗??箄?蝒?撟脫銝駁??Ｘ?雿?/li>";
echo "</ul>";

echo "<h3>?? ?憓撥</h3>";
echo "<ul>";
echo "<li><strong>憭???/strong>嚗??乓?乓??乓??勗??閮??/li>";
echo "<li><strong>摰?梯”?</strong>嚗”?潮＊蝷箝絞閮?閬SV ?臬</li>";
echo "<li><strong>閫甈??游?</strong>嚗????脩祟?詨?瑹???/li>";
echo "<li><strong>隞??瑕璅?</strong>嚗??唳?蝷粹?桐犖?∟???/li>";
echo "</ul>";

echo "<h3>? ?銵??/h3>";
echo "<ul>";
echo "<li><strong>蝔?蝣潮???/strong>嚗蝙?函?? API ???箄?蝒瑽?/li>";
echo "<li><strong>?踵?撘身閮?/strong>嚗???Ｕ像?踴?璈?/li>";
echo "<li><strong>?航炊??</strong>嚗??渡??航炊閮?敺拇???/li>";
echo "<li><strong>??芸?</strong>嚗??頛鞈?嚗?撠?憪??交???/li>";
echo "</ul>";
echo "</div>";

// 皜 Session
session_destroy();

echo "<p style='margin-top: 30px; color: #666;'>皜祈岫摰???: " . date('Y-m-d H:i:s') . "</p>";
?>
