<?php
/**
 * 蝞∠??∪??賜??葫閰? */

// ?? Session
session_start();

require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/auth.php';

echo "<h1>蝞∠??∪??賜??葫閰?/h1>";

// 璅⊥蝞∠??∠??$_SESSION['user_id'] = 'admin';
$_SESSION['username'] = 'admin';
$_SESSION['name'] = '蝟餌絞蝞∠???;
$_SESSION['role'] = 'admin';
$_SESSION['logged_in'] = true;

$user = get_current_session_user();

echo "<h2>?? ?蝮質汗</h2>";

echo "<table border='1' cellpadding='10' style='width: 100%;'>";
echo "<tr><th>??迂</th><th>瑼?</th><th>銝餉??券?/th><th>皜祈岫???/th><th>皜祈岫???</th></tr>";

$admin_features = [
    [
        'name' => '?寥?蝺刻摩?漲璆剔蜀',
        'file' => 'admin/bulk_edit_monthly.php',
        'purpose' => '銝甈∠楊頛舀???瘥?摨?瘥?憭拍?璆剔蜀??',
        'status' => '?啣??',
        'link' => 'admin/bulk_edit_monthly.php'
    ],
    [
        'name' => '鈭箏蝞∠?',
        'file' => 'admin/manage_users.php',
        'purpose' => '蝞∠?雿輻?董????撖Ⅳ',
        'status' => '?暹??',
        'link' => 'admin/manage_users.php'
    ],
    [
        'name' => '摨?蝞∠?',
        'file' => 'admin/manage_stores.php',
        'purpose' => '蝞∠?摨?鞈??平?????閮剖?',
        'status' => '?暹??',
        'link' => 'admin/manage_stores.php'
    ],
    [
        'name' => '蝞∠??∪?銵冽',
        'file' => 'dashboard.php',
        'purpose' => '蝞∠??∪??典??賢???璆剔蜀蝮質汗',
        'status' => '?暹??嚗歇憓撥嚗?,
        'link' => 'dashboard.php'
    ]
];

foreach ($admin_features as $feature) {
    echo "<tr>";
    echo "<td><strong>{$feature['name']}</strong></td>";
    echo "<td>{$feature['file']}</td>";
    echo "<td>{$feature['purpose']}</td>";
    echo "<td>{$feature['status']}</td>";
    echo "<td><a href='{$feature['link']}' target='_blank' style='display: inline-block; background: #007bff; color: white; padding: 5px 10px; border-radius: 3px; text-decoration: none;'>皜祈岫</a></td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>? ?寥?蝺刻摩?閰喟敦隤芣?</h2>";

echo "<h3>?詨??嚗?/h3>";
echo "<ol>";
echo "<li><strong>?遢?豢?</strong>嚗蝙?冽?隞賡??汗銝??遢</li>";
echo "<li><strong>銵冽蝺刻摩</strong>嚗雯?潛?銵冽憿舐內???瑹????/li>";
echo "<li><strong>??頛詨</strong>嚗????賣???頛詨獢?/li>";
echo "<li><strong>閫?豢?</strong>嚗???憿?臭誑?豢?銝餅??誨??/li>";
echo "<li><strong>敹恍?雿?/strong>嚗?‵撖怒?刻身????蝛箸???/li>";
echo "<li><strong>銝?萄摮?/strong>嚗?????港蒂銝甈∪摮?/li>";
echo "<li><strong>鞈?撽?</strong>嚗撩?蝡舫?霅??撘?/li>";
echo "</ol>";

echo "<h3>雿輻???ｇ?</h3>";
echo "<ul>";
echo "<li><strong>?踵?撘身閮?/strong>嚗???撟之撠?/li>";
echo "<li><strong>?收璇?</strong>嚗?擃霈??/li>";
echo "<li><strong>?望璅?</strong>嚗望?交??????航</li>";
echo "<li><strong>?箏?銵券</strong>嚗遝??銵券靽??航?</li>";
echo "<li><strong>?箏?撌行?</strong>嚗遝??摨?鞈?靽??航?</li>";
echo "<li><strong>敹急??/strong>嚗trl+S ?脣?嚗trl+R ?身</li>";
echo "</ul>";

echo "<h3>?銵?改?</h3>";
echo "<ul>";
echo "<li><strong>AJAX 憸冽</strong>嚗蝙??JavaScript ?園?鞈?嚗蝯梯”?格?鈭?/li>";
echo "<li><strong>JSON 鞈??唾撓</strong>嚗?銵冽鞈?頧???JSON ?澆??喲?/li>";
echo "<li><strong>?暹? API ?</strong>嚗蝙??load_monthly_sales ??save_monthly_sales</li>";
echo "<li><strong>甈?瑼Ｘ</strong>嚗?恣??臭誑閮芸?</li>";
echo "<li><strong>?航炊??</strong>嚗??渡??航炊閮?敺拇???/li>";
</ul>";

echo "<h2>?? 皜祈岫???</h2>";

echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;'>";

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; border: 2px solid #28a745;'>";
echo "<h3 style='color: #28a745;'>?寥?蝺刻摩?</h3>";
echo "<p><strong>銝餉?皜祈岫?</strong></p>";
echo "<p><a href='admin/bulk_edit_monthly.php' target='_blank' style='display: inline-block; background: #28a745; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold; width: 100%; text-align: center;'>皜祈岫?寥?蝺刻摩</a></p>";
echo "<p><a href='test_bulk_edit.php' target='_blank' style='display: inline-block; background: #6c757d; color: white; padding: 8px 15px; border-radius: 5px; text-decoration: none; width: 100%; text-align: center; margin-top: 10px;'>閰喟敦皜祈岫隤芣?</a></p>";
echo "</div>";

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; border: 2px solid #007bff;'>";
echo "<h3 style='color: #007bff;'>蝞∠??∪?銵冽</h3>";
echo "<p><strong>??亙皜祈岫</strong></p>";
echo "<p><a href='dashboard.php' target='_blank' style='display: inline-block; background: #007bff; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold; width: 100%; text-align: center;'>皜祈岫蝞∠??∪?銵冽</a></p>";
echo "<p><small>蝣箄??臭誑???楊頛舀平蝮整???/small></p>";
echo "</div>";

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; border: 2px solid #dc3545;'>";
echo "<h3 style='color: #dc3545;'>甈?皜祈岫</h3>";
echo "<p><strong>?恣?閮芸?皜祈岫</strong></p>";
echo "<p><a href='test_permission.php' target='_blank' style='display: inline-block; background: #dc3545; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold; width: 100%; text-align: center;'>皜祈岫甈?靽風</a></p>";
echo "<p><small>蝣箄??恣??⊥?閮芸?</small></p>";
echo "</div>";

echo "</div>";

echo "<h2>?妒 皜祈岫甇仿?</h2>";

echo "<h3>皜祈岫 1嚗??賢??湔?/h3>";
echo "<ol>";
echo "<li>閮芸? dashboard.php嚗恣?頨思遢嚗?/li>";
echo "<li><strong>蝣箄?嚗?/strong>??恣????憛?/li>";
echo "<li><strong>蝣箄?嚗?/strong>???楊頛舀平蝮整???蝬嚗?/li>";
echo "<li><strong>蝣箄?嚗?/strong>??犖?∠恣????瑹恣????/li>";
echo "<li>暺???楊頛舀平蝮整???/li>";
echo "<li><strong>蝣箄?嚗?/strong>頝唾???admin_bulk_edit.php</li>";
echo "</ol>";

echo "<h3>皜祈岫 2嚗?楊頛舀?雿?/h3>";
echo "<ol>";
echo "<li>?冽?楊頛舫??ｇ??豢?銝??隞?/li>";
echo "<li><strong>蝣箄?嚗?/strong>銵冽憿舐內甇?Ⅱ??瑹??交?</li>";
echo "<li>蝺刻摩撟曉?憿?甇?????蝛綽?</li>";
echo "<li>皜祈岫??‵撖怎征?賬???/li>";
echo "<li>皜祈岫??刻身??????/li>";
echo "<li>皜祈岫??蝛箸?????/li>";
echo "<li>暺??摮????氬?/li>";
echo "<li><strong>蝣箄?嚗?/strong>憿舐內蝣箄?撠店獢?/li>";
echo "<li><strong>蝣箄?嚗?/strong>?脣?敺＊蝷箸?????/li>";
echo "<li>瑼Ｘ?漲?梯”蝣箄?靽格撌脩???/li>";
</ol>";

echo "<h3>皜祈岫 3嚗隤方???/h3>";
echo "<ol>";
echo "<li>?岫頛詨鞎??</li>";
echo "<li><strong>蝣箄?嚗?/strong>?汗?券甇Ｚ??貉撓?伐?min=0嚗?/li>";
echo "<li>?岫頛詨?摮?/li>";
echo "<li><strong>蝣箄?嚗?/strong>?汗?券甇ａ??詨?頛詨嚗ype=number嚗?/li>";
echo "<li>皜祈岫蝬脰楝銝剜??</li>";
echo "<li><strong>蝣箄?嚗?/strong>??嗥??航炊閮</li>";
</ol>";

echo "<h2>?? 皜祈岫閮?</h2>";

echo "<form id='test-record'>";
echo "<table border='1' cellpadding='10' style='width: 100%;'>";
echo "<tr><th>皜祈岫?</th><th>??蝯?</th><th>撖阡?蝯?</th><th>?酉</th></tr>";

$test_items = [
    ['蝞∠??∪?銵冽憿舐內', '???楊頛舀平蝮整???, '', ''],
    ['?遢?豢??', '?臭誑??銝??遢', '', ''],
    ['銵冽頛', '憿舐內???瑹??交?', '', ''],
    ['??蝺刻摩', '?臭誑頛詨?耨?寥?憿?, '', ''],
    ['閫?豢?', '?臭誑?豢?銝餅??誨??, '', ''],
    ['?寥?憛怠神蝛箇', '??征?賣?雿‵撖急?摰?憿?, '', ''],
    ['?券閮剔0', '???憿?雿身??', '', ''],
    ['皜征???, '???憿?雿?蝛?, '', ''],
    ['鞈??脣?', '?脣?敺＊蝷箸?????, '', ''],
    ['鞈?撽?', '靽格?冽?摨血銵其葉??', '', ''],
    ['甈?靽風', '?恣??⊥?閮芸?', '', ''],
    ['?踵?撘身閮?, '?冽?璈?甇?虜憿舐內', '', '']
];

foreach ($test_items as $index => $item) {
    $id = 'test_' . ($index + 1);
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
echo "<button type='button' onclick='saveTestRecord()' style='padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; margin-top: 20px;'>?脣?皜祈岫閮?</button>";
echo "</form>";

echo "<script>
function saveTestRecord() {
    const form = document.getElementById('test-record');
    const results = {};
    
    // ?園?蝯?
    const inputs = form.querySelectorAll('select, input[type=\"text\"]');
    inputs.forEach(input => {
        if (input.value) {
            results[input.name] = input.value;
        }
    });
    
    console.log('皜祈岫閮?:', results);
    
    // 閮?蝯梯?
    const total = Object.keys(results).filter(k => k.includes('_result')).length;
    const passed = Object.values(results).filter(v => v === 'pass').length;
    const failed = Object.values(results).filter(v => v === 'fail').length;
    const na = Object.values(results).filter(v => v === 'na').length;
    const passRate = total > 0 ? Math.round((passed / total) * 100) : 0;
    
    alert(`皜祈岫摰?嚗\n??: \${passed} | 憭望?: \${failed} | 銝?? \${na}\\n???? \${passRate}%`);
    
    // 憿舐內??
    let summary = '<h3>皜祈岫蝯???</h3><ul>';
    for (let i = 0; i < <?php echo count($test_items); ?>; i++) {
        const testId = 'test_' + (i + 1) + '_result';
        const noteId = 'test_' + (i + 1) + '_notes';
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
