<?php
/**
 * 皜祈岫?寥?蝺刻摩擃漁?
 */

// ?? Session
session_start();

require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/auth.php';

echo "<h1>皜祈岫?寥?蝺刻摩擃漁?</h1>";

// 璅⊥蝞∠??∠??$_SESSION['user_id'] = 'admin';
$_SESSION['username'] = 'admin';
$_SESSION['name'] = '蝟餌絞蝞∠???;
$_SESSION['role'] = 'admin';
$_SESSION['logged_in'] = true;

$user = get_current_session_user();

echo "<h2>? 擃漁?隤芣?</h2>";

echo "<p>?冽?楊頛舫??Ｖ葉嚗蝞∠??楊頛舀???憿?嚗???<strong>撌行摨?</strong>??strong>銝?交?</strong>?澆??＊蝷粹?鈭格?霈嚗?蝞∠??敹恍儘霅迤?函楊頛舐?雿蔭??/p>";

echo "<h3>銝餉?擃漁?嚗?/h3>";
echo "<ol>";
echo "<li><strong>頛詨獢??阡?鈭?/strong>嚗??遙雿?憿撓?交????芸?擃漁撠???瑹??交?</li>";
echo "<li><strong>憭芋撘???/strong>嚗隞亙???蝔桅?鈭格芋撘?</li>";
echo "<ul>";
echo "<li><strong>?? ??擃漁</strong>嚗???鈭桀?瑹??交?嚗?閮哨?</li>";
echo "<li><strong>? ?芷?鈭桀?瑹?/strong>嚗擃漁撠???瑹?</li>";
echo "<li><strong>?? ?芷?鈭格??/strong>嚗擃漁撠????</li>";
echo "<li><strong>? ??擃漁</strong>嚗?憿舐內擃漁??</li>";
echo "</ul>";
echo "<li><strong>暺?銵券擃漁</strong>嚗???瑹”?剝?鈭格銵?暺??交?銵券擃漁?游?</li>";
echo "<li><strong>閬死?內??/strong>嚗銝?憿舐內?嗅?蝺刻摩??瑹??交?</li>";
echo "<li><strong>??擃漁</strong>嚗?撠?瑹?嚗泵??隞嗥?摨???鈭桅＊蝷?/li>";
echo "</ol>";

echo "<h3>?銵祕?橘?</h3>";
echo "<pre><code>// 擃漁?摩
function highlightCell(element) {
    const date = element.dataset.date;
    const store = element.dataset.store;
    
    // ?寞?璅∪?瘛餃?擃漁
    if (highlightMode === 'both' || highlightMode === 'store') {
        // 擃漁撠???瑹?
        const storeHeaders = document.querySelectorAll(`.store-header[data-store=\"\${store}\"]`);
        storeHeaders.forEach(header => header.classList.add('highlight'));
    }
    
    if (highlightMode === 'both' || highlightMode === 'date') {
        // 擃漁撠????
        const dateHeaders = document.querySelectorAll(`.date-header[data-date=\"\${date}\"]`);
        dateHeaders.forEach(header => header.classList.add('highlight'));
    }
}</code></pre>";

echo "<h2>?? 蝡皜祈岫</h2>";

echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;'>";

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; border: 2px solid #28a745;'>";
echo "<h3 style='color: #28a745;'>?寥?蝺刻摩擃漁?</h3>";
echo "<p><strong>銝餉?皜祈岫?</strong></p>";
echo "<p><a href='admin/bulk_edit_monthly.php' target='_blank' style='display: inline-block; background: #28a745; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold; width: 100%; text-align: center;'>皜祈岫擃漁?</a></p>";
echo "</div>";

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; border: 2px solid #007bff;'>";
echo "<h3 style='color: #007bff;'>蝞∠??∪?銵冽</h3>";
echo "<p><strong>??亙皜祈岫</strong></p>";
echo "<p><a href='dashboard.php' target='_blank' style='display: inline-block; background: #007bff; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold; width: 100%; text-align: center;'>皜祈岫蝞∠??∪?銵冽</a></p>";
echo "</div>";

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; border: 2px solid #6c757d;'>";
echo "<h3 style='color: #6c757d;'>蝬?皜祈岫</h3>";
echo "<p><strong>????賣葫閰?/strong></p>";
echo "<p><a href='test_admin_features.php' target='_blank' style='display: inline-block; background: #6c757d; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold; width: 100%; text-align: center;'>蝬??皜祈岫</a></p>";
echo "</div>";

echo "</div>";

echo "<h2>?妒 皜祈岫甇仿?</h2>";

echo "<h3>皜祈岫 1嚗撓?交??擃漁</h3>";
echo "<ol>";
echo "<li>?脣?寥?蝺刻摩?</li>";
echo "<li>暺?隞颱?銝??憿撓?交?</li>";
echo "<li><strong>蝣箄?嚗?/strong>撠???瑹?擃漁憿舐內嚗??脰??荔?</li>";
echo "<li><strong>蝣箄?嚗?/strong>撠????擃漁憿舐內嚗滓??嚗?/li>";
echo "<li><strong>蝣箄?嚗?/strong>?喃?閫＊蝷箇?楊頛舐?摨????/li>";
echo "<li>暺??嗡?頛詨獢?/li>";
echo "<li><strong>蝣箄?嚗?/strong>擃漁??頝蝘餃?</li>";
echo "</ol>";

echo "<h3>皜祈岫 2嚗?鈭格芋撘???/h3>";
echo "<ol>";
echo "<li>暺??喃?閫? ?? ??</li>";
echo "<li><strong>蝣箄?嚗?/strong>????霈 ?嚗擃漁摨?嚗?/li>";
echo "<li>暺???頛詨獢?/li>";
echo "<li><strong>蝣箄?嚗?/strong>?芷?鈭桀?瑹?嚗??銝?鈭?/li>";
echo "<li>?活暺? ?? ????璅∪?</li>";
echo "<li>皜祈岫???蝔格芋撘??? ??? ???? ???</li>";
echo "<li><strong>蝣箄?嚗?/strong>瘥車璅∪???鈭格??迤蝣?/li>";
</ol>";

echo "<h3>皜祈岫 3嚗??”?剝?鈭?/h3>";
echo "<ol>";
echo "<li>暺?隞颱?銝??”?哨?憒?03/24 ?曹?嚗?/li>";
echo "<li><strong>蝣箄?嚗?/strong>?游??交?擃漁憿舐內</li>";
echo "<li><strong>蝣箄?嚗?/strong>?喃?閫＊蝷箝???交?</li>";
echo "<li>暺?隞颱?銝??瑹”?哨?憒?277嚗?/li>";
echo "<li><strong>蝣箄?嚗?/strong>?渲?摨?擃漁憿舐內</li>";
echo "<li><strong>蝣箄?嚗?/strong>?喃?閫＊蝷箏?瑹??銵?/li>";
</ol>";

echo "<h3>皜祈岫 4嚗?撠?鈭?/h3>";
echo "<ol>";
echo "<li>?冽?撠?銝剛撓?乓?77??/li>";
echo "<li><strong>蝣箄?嚗?/strong>277 摨???憿舐內嚗隞?瑹??/li>";
echo "<li><strong>蝣箄?嚗?/strong>277 摨??”?剜?暺?</li>";
echo "<li>暺????斗?撠???/li>";
echo "<li><strong>蝣箄?嚗?/strong>???瑹敺拚＊蝷綽?擃漁瘨仃</li>";
</ol>";

echo "<h3>皜祈岫 5嚗翰?琿?</h3>";
echo "<ol>";
echo "<li>??<strong>Ctrl + S</strong></li>";
echo "<li><strong>蝣箄?嚗?/strong>閫貊?脣??</li>";
echo "<li>??<strong>Ctrl + R</strong></li>";
echo "<li><strong>蝣箄?嚗?/strong>閫貉絲?身?</li>";
echo "<li>??<strong>Ctrl + F</strong></li>";
echo "<li><strong>蝣箄?嚗?/strong>??獢敺暺?/li>";
echo "<li>??<strong>Ctrl + H</strong></li>";
echo "<li><strong>蝣箄?嚗?/strong>??擃漁璅∪?</li>";
</ol>";

echo "<h2>? 閬死??隤芣?</h2>";

echo "<h3>擃漁憿嚗?/h3>";
echo "<table border='1' cellpadding='10' style='width: 100%;'>";
echo "<tr><th>??</th><th>甇?虜憿</th><th>擃漁憿</th><th>??</th></tr>";
echo "<tr><td>摨?銵券</td><td>#f8f9fa嚗滓?堆?</td><td>#fff3cd嚗滓暺?</td><td>暺? + ?啣蔣</td></tr>";
echo "<tr><td>?交?銵券</td><td>#e9ecef嚗葉?堆?</td><td>#d1ecf1嚗滓??</td><td>?? + ?啣蔣</td></tr>";
echo "<tr><td>摨?銵?/td><td>?收璇?</td><td>蝺扳撓霈???/td><td>?渲?暺瞍貉?</td></tr>";
echo "<tr><td>?交???/td><td>?收璇?</td><td>蝺扳撓霈???/td><td>?游??瞍貉?</td></tr>";
echo "<tr><td>頛詨獢?/td><td>?質</td><td>#fff3cd嚗滓暺?</td><td>暺? + ?曉之??</td></tr>";
echo "</table>";

echo "<h3>???嚗?/h3>";
echo "<ul>";
echo "<li><strong>瘛∪瘛∪</strong>嚗?鈭格?蝷箏?楚?亙???/li>";
echo "<li><strong>撟單??腹</strong>嚗????脰????0.2 蝘?皜⊥???/li>";
echo "<li><strong>?曉之??</strong>嚗撓?交????頛凝?曉之??</li>";
echo "<li><strong>?啣蔣??</strong>嚗?鈭桀?蝝??啣蔣憓撥蝡???/li>";
</ul>";

echo "<h2>?? 皜祈岫閮?</h2>";

echo "<form id='highlight-test-record'>";
echo "<table border='1' cellpadding='10' style='width: 100%;'>";
echo "<tr><th>皜祈岫?</th><th>??蝯?</th><th>撖阡?蝯?</th><th>?酉</th></tr>";

$test_items = [
    ['頛詨獢??阡?鈭?, '摨??????鈭?, '', ''],
    ['擃漁璅∪???', '?車璅∪?甇?Ⅱ??', '', ''],
    ['暺??交?銵券', '?游??交?擃漁', '', ''],
    ['暺?摨?銵券', '?渲?摨?擃漁', '', ''],
    ['???擃漁', '??蝯?甇?Ⅱ擃漁', '', ''],
    ['敹急??Ctrl+S', '閫貊?脣??', '', ''],
    ['敹急??Ctrl+R', '閫貉絲?身?', '', ''],
    ['敹急??Ctrl+F', '??獢敺暺?, '', ''],
    ['敹急??Ctrl+H', '??擃漁璅∪?', '', ''],
    ['閬死?內??, '憿舐內?嗅?蝺刻摩雿蔭', '', ''],
    ['???', '憿霈?撟單??腹', '', ''],
    ['?踵?撘身閮?, '??銝迤撣賊＊蝷?, '', '']
];

foreach ($test_items as $index => $item) {
    $id = 'highlight_test_' . ($index + 1);
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
echo "<button type='button' onclick='saveHighlightTestRecord()' style='padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; margin-top: 20px;'>?脣?皜祈岫閮?</button>";
echo "</form>";

echo "<script>
function saveHighlightTestRecord() {
    const form = document.getElementById('highlight-test-record');
    const results = {};
    
    // ?園?蝯?
    const inputs = form.querySelectorAll('select, input[type=\"text\"]');
    inputs.forEach(input => {
        if (input.value) {
            results[input.name] = input.value;
        }
    });
    
    console.log('擃漁?皜祈岫閮?:', results);
    
    // 閮?蝯梯?
    const total = Object.keys(results).filter(k => k.includes('_result')).length;
    const passed = Object.values(results).filter(v => v === 'pass').length;
    const failed = Object.values(results).filter(v => v === 'fail').length;
    const na = Object.values(results).filter(v => v === 'na').length;
    const passRate = total > 0 ? Math.round((passed / total) * 100) : 0;
    
    alert(`擃漁?皜祈岫摰?嚗\n??: \${passed} | 憭望?: \${failed} | 銝?? \${na}\\n???? \${passRate}%`);
    
    // 憿舐內??
    let summary = '<h3>擃漁?皜祈岫蝯???</h3><ul>';
    for (let i = 0; i < <?php echo count($test_items); ?>; i++) {
        const testId = 'highlight_test_' + (i + 1) + '_result';
        const noteId = 'highlight_test_' + (i + 1) + '_notes';
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
