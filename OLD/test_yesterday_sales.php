<?php
/**
 * 皜祈岫?冽璆剔蜀?
 */

// ?? Session
session_start();

require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/auth.php';

echo "<h1>皜祈岫?冽璆剔蜀?</h1>";

// 皜祈岫銝?閫???$test_users = [
    'admin' => [
        'id' => 'admin',
        'username' => 'admin',
        'name' => '蝟餌絞蝞∠???,
        'role' => 'admin'
    ],
    'sales' => [
        'id' => 'U004',
        'username' => 'sales1',
        'name' => '?喳之??,
        'role' => 'sales'
    ],
    'supervisor' => [
        'id' => 'S002',
        'username' => 'supervisor1',
        'name' => '???,
        'role' => 'supervisor'
    ]
];

echo "<h2>?? ?隤芣?</h2>";
echo "<p>??dashboard.php ??亙?摨?璆剔蜀??憿???啣?銝???交平蝮整???霈恣??平??????賢翰???唳憭拍?璆剔蜀?”??/p>";

echo "<h3>銝餉??嚗?/h3>";
echo "<ol>";
echo "<li><strong>敶閬?憿舐內</strong>嚗?????隞亙??箄?蝒＊蝷箸?交平蝮?/li>";
echo "<li><strong>閫甈??批</strong>嚗????脩??唬???摨?鞈?</li>";
echo "<li><strong>摰蝯梯?</strong>嚗＊蝷箇蜇璆剔蜀?歇?餅??詻?餅??詻誨?剝?格</li>";
echo "<li><strong>鞈??臬</strong>嚗??CSV ?澆??臬</li>";
echo "<li><strong>?踵?撘身閮?/strong>嚗????蝵株撟之撠?/li>";
echo "<li><strong>???</strong>嚗??亙??怠??腹??</li>";
echo "</ol>";

echo "<h2>?? 蝡皜祈岫</h2>";

echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;'>";

foreach ($test_users as $role => $user_info) {
    echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; border: 2px solid ";
    
    switch ($role) {
        case 'admin': echo '#dc3545'; break;
        case 'sales': echo '#007bff'; break;
        case 'supervisor': echo '#fd7e14'; break;
        default: echo '#6c757d';
    }
    
    echo ";'>";
    echo "<h3 style='color: ";
    
    switch ($role) {
        case 'admin': echo '#dc3545'; break;
        case 'sales': echo '#007bff'; break;
        case 'supervisor': echo '#fd7e14'; break;
        default: echo '#6c757d';
    }
    
    echo ";'>" . $user_info['name'] . " (" . $role . ")</h3>";
    echo "<p><strong>皜祈岫閫嚗?/strong>" . $role . "</p>";
    echo "<p><strong>?????瑹?</strong></p>";
    
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
    if (in_array($user['role'], ['boss', 'admin'])) {
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
    
    echo "<ul>";
    foreach ($user_stores as $store) {
        echo "<li>" . $store['code'] . " - " . $store['name'] . "</li>";
    }
    echo "</ul>";
    
    echo "<p><a href='dashboard.php' target='_blank' style='display: inline-block; background: ";
    
    switch ($role) {
        case 'admin': echo '#dc3545'; break;
        case 'sales': echo '#007bff'; break;
        case 'supervisor': echo '#fd7e14'; break;
        default: echo '#6c757d';
    }
    
    echo "; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold; width: 100%; text-align: center;'>皜祈岫 " . $role . " ?銵冽</a></p>";
    echo "</div>";
}

echo "</div>";

echo "<h2>?妒 皜祈岫甇仿?</h2>";

echo "<h3>皜祈岫 1嚗??＊蝷箄??</h3>";
echo "<ol>";
echo "<li>隞乩????脩??dashboard.php</li>";
echo "<li><strong>蝣箄?嚗?/strong>?具?亙?摨?璆剔蜀??憿???啜??交平蝮整???/li>";
echo "<li><strong>蝣箄?嚗?/strong>??憿舐內?冽?交?嚗? . date('Y-m-d', strtotime('-1 day')) . "嚗?/li>";
echo "<li>暺????交平蝮整???/li>";
echo "<li><strong>蝣箄?嚗?/strong>敶閬?憿舐內嚗?頛?</li>";
echo "<li><strong>蝣箄?嚗?/strong>頛摰?敺＊蝷箸?交平蝮曇”??/li>";
echo "</ol>";

echo "<h3>皜祈岫 2嚗??脫???霅?/h3>";
echo "<ol>";
echo "<li>隞亦恣?頨思遢?餃</li>";
echo "<li><strong>蝣箄?嚗?/strong>????瑹??冽璆剔蜀</li>";
echo "<li>隞交平?澈隞賜??/li>";
echo "<li><strong>蝣箄?嚗?/strong>?芰??啗撌梯?鞎祉?摨?璆剔蜀</li>";
echo "<li>隞亦撠澈隞賜??/li>";
echo "<li><strong>蝣箄?嚗?/strong>?芰??啗撌梯?鞎祉?摨?璆剔蜀</li>";
echo "<li><strong>蝣箄?嚗?/strong>銵冽甈??寞?閫??憿舐內嚗平??憿舐內璆剖?甈?嚗撠?憿舐內???甈?嚗?/li>";
</ol>";

echo "<h3>皜祈岫 3嚗??＊蝷箄?蝯梯?</h3>";
echo "<ol>";
echo "<li>?亦??冽璆剔蜀銵冽</li>";
echo "<li><strong>蝣箄?嚗?/strong>憿舐內摨?隞????蝔晞平蝮暸?憿?/li>";
echo "<li><strong>蝣箄?嚗?/strong>隞??瑕??隞?)??閮?/li>";
echo "<li><strong>蝣箄?嚗?/strong>憿舐內???雿?撌脩???芰??</li>";
echo "<li><strong>蝣箄?嚗?/strong>?憿舐內蝮賣平蝮曄絞閮?/li>";
echo "<li><strong>蝣箄?嚗?/strong>摨憿舐內蝯梯???嚗蜇摨??詻歇?餅??詻?餅??詻誨?剝?格嚗?/li>";
</ol>";

echo "<h3>皜祈岫 4嚗?雿???/h3>";
echo "<ol>";
echo "<li>暺?敶閬??喃?閫? ? ??</li>";
echo "<li><strong>蝣箄?嚗?/strong>敶閬???</li>";
echo "<li>暺?敶閬?憭???/li>";
echo "<li><strong>蝣箄?嚗?/strong>敶閬???</li>";
echo "<li>??ESC ??/li>";
echo "<li><strong>蝣箄?嚗?/strong>敶閬???</li>";
echo "<li>暺????CSV????/li>";
echo "<li><strong>蝣箄?嚗?/strong>銝? CSV 瑼?</li>";
</ol>";

echo "<h3>皜祈岫 5嚗PI ?</h3>";
echo "<ol>";
echo "<li>?湔閮芸? API嚗?a href='get_yesterday_sales.php' target='_blank'>get_yesterday_sales.php</a></li>";
echo "<li><strong>蝣箄?嚗?/strong>? JSON ?澆?鞈?</li>";
echo "<li><strong>蝣箄?嚗?/strong>鞈???交???瑹?蜇璆剔蜀蝑?閮?/li>";
echo "<li>皜祈岫撣嗆???賂?<a href='get_yesterday_sales.php?date=" . date('Y-m-d', strtotime('-2 days')) . "' target='_blank'>get_yesterday_sales.php?date=" . date('Y-m-d', strtotime('-2 days')) . "</a></li>";
echo "<li><strong>蝣箄?嚗?/strong>????交??平蝮曇???/li>";
</ol>";

echo "<h2>? ?銵瑽?/h2>";

echo "<h3>瑼?蝯?嚗?/h3>";
echo "<ul>";
echo "<li><strong>dashboard.php</strong>嚗蜓閬??ｇ?瘛餃??冽璆剔蜀?????箄?蝒?/li>";
echo "<li><strong>get_yesterday_sales.php</strong>嚗PI 蝡舫?嚗?靘?交平蝮曇???/li>";
echo "<li><strong>?暹??賣?</strong>嚗蝙??load_monthly_sales() ??load_data() ?賣</li>";
</ul>";

echo "<h3>?垢?銵?</h3>";
echo "<pre><code>// 憿舐內?冽璆剔蜀
function showYesterdaySales() {
    const modal = document.getElementById('yesterday-modal');
    modal.classList.add('show');
    loadYesterdaySales();
}

// AJAX 頛鞈?
fetch(`get_yesterday_sales.php?date=\${yesterday}`)
    .then(response => response.json())
    .then(data => displayYesterdaySales(data.data, yesterday));

// ????銵冽
function displayYesterdaySales(data, date) {
    // ?寞?雿輻???脣????”??HTML
}</code></pre>";

echo "<h3>敺垢?銵?</h3>";
echo "<pre><code>// ?寞?閫蝭拚摨?
if (\$user['role'] === 'admin') {
    \$user_stores = \$stores; // ???瑹?} else {
    // 璆剖?/???嚗憿舐內鞎痊??瑹?    foreach (\$stores as \$store) {
        if (\$user['role'] === 'sales' && \$store['sales_person'] === \$user['id']) {
            \$user_stores[] = \$store;
        }
    }
}

// ? JSON ?澆?鞈?
echo json_encode([
    'success' => true,
    'data' => \$response_data
]);</code></pre>";

echo "<h2>?? 皜祈岫閮?</h2>";

echo "<form id='yesterday-test-record'>";
echo "<table border='1' cellpadding='10' style='width: 100%;'>";
echo "<tr><th>皜祈岫?</th><th>??蝯?</th><th>撖阡?蝯?</th><th>?酉</th></tr>";

$test_items = [
    ['??憿舐內', '璅??喲?憿舐內???交平蝮整???, '', ''],
    ['???交?', '??憿舐內?冽?交?', '', ''],
    ['敶閬?', '暺???憿舐內敶閬?', '', ''],
    ['頛?', '憿舐內頛?', '', ''],
    ['蝞∠??⊥???, '蝞∠??∠??唳???瑹?, '', ''],
    ['璆剖?甈?', '璆剖??芰??啗?鞎砍?瑹?, '', ''],
    ['???甈?', '????芰??啗?鞎砍?瑹?, '', ''],
    ['甈???憿舐內', '璆剖?銝＊蝷箸平??雿????銝＊蝷箇撠?雿?, '', ''],
    ['隞?璅?', '隞??瑕憿舐內(隞?)璅?', '', ''],
    ['??＊蝷?, '憿舐內撌脩???芰????, '', ''],
    ['蝯梯???', '憿舐內蝮賣平蝮曉?蝯梯???', '', ''],
    ['???', '暺??????閬?', '', ''],
    ['暺?憭??', '暺?閬?憭?????, '', ''],
    ['ESC?菟???, '?SC?菟???蝒?, '', ''],
    ['CSV?臬', '暺??臬CSV??銝?瑼?', '', ''],
    ['API?', 'API?甇?ⅡJSON鞈?', '', ''],
    ['?踵?撘身閮?, '??銝迤撣賊＊蝷?, '', '']
];

foreach ($test_items as $index => $item) {
    $id = 'yesterday_test_' . ($index + 1);
    echo "<tr>";
    echo "<td>{$item[0]}</td>";
    echo "<td>{$item[1]}</td>";
    echo "<td>
        <select name='{$id}_result'>
            <option value=''>?芣葫閰?/option>
            <option value='pass'>????</option>
            <option value='fail'>??憭望?</option>
            <option value='na'>銝??/option>
        </select>
    </td>";
    echo "<td><input type='text' name='{$id}_notes' placeholder='?酉' style='width: 100%;'></td>";
    echo "</tr>";
}
echo "</table>";
echo "<button type='button' onclick='saveYesterdayTestRecord()' style='padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; margin-top: 20px;'>?脣?皜祈岫閮?</button>";
echo "</form>";

echo "<script>
function saveYesterdayTestRecord() {
    const form = document.getElementById('yesterday-test-record');
    const results = {};
    
    // ?園?蝯?
    const inputs = form.querySelectorAll('select, input[type=\"text\"]');
    inputs.forEach(input => {
        if (input.value) {
            results[input.name] = input.value;
        }
    });
    
    console.log('?冽璆剔蜀皜祈岫閮?:', results);
    
    // 閮?蝯梯?
    const total = Object.keys(results).filter(k => k.includes('_result')).length;
    const passed = Object.values(results).filter(v => v === 'pass').length;
    const failed = Object.values(results).filter(v => v === 'fail').length;
    const na = Object.values(results).filter(v => v === 'na').length;
    const passRate = total > 0 ? Math.round((passed / total) * 100) : 0;
    
    alert(`?冽璆剔蜀?皜祈岫摰?嚗\n??: \${passed} | 憭望?: \${failed} | 銝?? \${na}\\n???? \${passRate}%`);
    
    // 憿舐內??
    let summary = '<h3>?冽璆剔蜀?皜祈岫蝯???</h3><ul>';
    for (let i = 0; i < <?php echo count($test_items); ?>; i++) {
        const testId = 'yesterday_test_' + (i + 1) + '_result';
        const noteId = 'yesterday_test_' + (i + 1) + '_notes';
        const result = results[testId] || '?芣葫閰?;
        const note = results[noteId] || '';
        
        summary += '<li>' + <?php echo json_encode(array_column($test_items, 0)); ?>[i] + ': ' + 
                  (result === 'pass' ? '????' : result === 'fail' ? '??憭望?' : result === 'na' ? '??銝?? : '???芣葫閰?);
        if (note) summary += ' (' + note + ')';
        summary += '</li>';
    }
    summary += '</ul>';
    
    const summaryDiv = document.createElement('div');
    summaryDiv.innerHTML = summary;
    document.body.appendChild(summaryDiv);
}
</script>";

// 皜 Session
session_destroy();

echo "<p style='margin-top: 30px; text-align: center; color: #666;'>皜祈岫摰???: " . date('Y-m-d H:i:s') . "</p>";
?>
