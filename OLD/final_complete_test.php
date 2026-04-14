<?php
/**
 * ?蝯??湔葫閰行?交平蝮曉??? */

// ?? Session
session_start();

require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/auth.php';

echo "<h1>?蝯??湔葫閰行?交平蝮曉???/h1>";

// 皜祈岫蝞∠??∠??$_SESSION['user_id'] = 'admin';
$_SESSION['username'] = 'admin';
$_SESSION['name'] = '蝟餌絞蝞∠???;
$_SESSION['role'] = 'admin';
$_SESSION['logged_in'] = true;

$user = get_current_session_user();

echo "<h2>皜祈岫???/h2>";
echo "<p>雿輻?? " . $user['name'] . " (" . $user['role'] . ")</p>";
echo "<p>皜祈岫??: " . date('Y-m-d H:i:s') . "</p>";

echo "<h2>甇仿? 1嚗葫閰?API ?</h2>";

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
            
            echo "<h3>鞈?蝯?撽?</h3>";
            echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
            echo "<tr><th>瑼Ｘ?</th><th>??蝯?</th><th>撖阡?蝯?</th><th>???/th></tr>";
            
            // 瑼Ｘ stores_by_code
            $stores_by_code_exists = isset($data['data']['stores_by_code']);
            $stores_by_code_type = $stores_by_code_exists ? gettype($data['data']['stores_by_code']) : 'N/A';
            $stores_by_code_count = $stores_by_code_exists ? count($data['data']['stores_by_code']) : 0;
            $stores_by_code_is_object = $stores_by_code_exists && !is_array($data['data']['stores_by_code']);
            
            echo "<tr>";
            echo "<td>stores_by_code 摮</td>";
            echo "<td>??/td>";
            echo "<td>" . ($stores_by_code_exists ? '?? : '??) . "</td>";
            echo "<td>" . ($stores_by_code_exists ? '?? : '??) . "</td>";
            echo "</tr>";
            
            echo "<tr>";
            echo "<td>stores_by_code 憿?</td>";
            echo "<td>?拐辣 (object)</td>";
            echo "<td>{$stores_by_code_type}</td>";
            echo "<td>" . ($stores_by_code_is_object ? '?? : '??) . "</td>";
            echo "</tr>";
            
            echo "<tr>";
            echo "<td>stores_by_code ???/td>";
            echo "<td>16</td>";
            echo "<td>{$stores_by_code_count}</td>";
            echo "<td>" . ($stores_by_code_count == 16 ? '?? : '??') . "</td>";
            echo "</tr>";
            
            // 瑼Ｘ stores
            $stores_exists = isset($data['data']['stores']);
            $stores_count = $stores_exists ? count($data['data']['stores']) : 0;
            
            echo "<tr>";
            echo "<td>stores 摮</td>";
            echo "<td>??/td>";
            echo "<td>" . ($stores_exists ? '?? : '??) . "</td>";
            echo "<td>" . ($stores_exists ? '?? : '??) . "</td>";
            echo "</tr>";
            
            echo "<tr>";
            echo "<td>stores ???/td>";
            echo "<td>16</td>";
            echo "<td>{$stores_count}</td>";
            echo "<td>" . ($stores_count == 16 ? '?? : '??') . "</td>";
            echo "</tr>";
            
            // 瑼Ｘ蝯梯?鞈?
            $stats = [
                'stores_count' => ['??' => 16, '撖阡?' => $data['data']['stores_count'] ?? 0],
                'entered_count' => ['??' => 4, '撖阡?' => $data['data']['entered_count'] ?? 0],
                'total_amount' => ['??' => 60718, '撖阡?' => $data['data']['total_amount'] ?? 0],
                'substitute_count' => ['??' => 1, '撖阡?' => $data['data']['substitute_count'] ?? 0]
            ];
            
            foreach ($stats as $key => $values) {
                echo "<tr>";
                echo "<td>{$key}</td>";
                echo "<td>" . number_format($values['??']) . "</td>";
                echo "<td>" . number_format($values['撖阡?']) . "</td>";
                echo "<td>" . ($values['撖阡?'] == $values['??'] ? '?? : '??') . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
            
            echo "<h3>鞈?璅?</h3>";
            if ($stores_by_code_exists && $stores_by_code_count > 0) {
                echo "<p>????瑹???</p>";
                $count = 0;
                foreach ($data['data']['stores_by_code'] as $storeCode => $storeData) {
                    if ($count < 3) {
                        echo "<div style='background: #f8f9fa; padding: 10px; margin: 5px 0; border-radius: 5px;'>";
                        echo "<strong>摨? {$storeCode}</strong>: ";
                        echo "璆剔蜀: " . ($storeData['amount'] !== null ? number_format($storeData['amount']) : 'null');
                        echo ", 閫: " . ($storeData['role'] ?? 'main');
                        echo ", ??? " . ($storeData['status'] ?? 'N/A');
                        echo "</div>";
                        $count++;
                    }
                }
            }
            
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

echo "<h2>甇仿? 2嚗葫閰血?銵冽?</h2>";
echo "<p><a href='dashboard.php' target='_blank'>?? dashboard.php 皜祈岫</a></p>";

echo "<h3>皜祈岫甇仿?嚗?/h3>";
echo "<ol>";
echo "<li>?餃 dashboard.php</li>";
echo "<li>蝣箄????交平蝮整??＊蝷箏??亙?摨?璆剔蜀??憿??/li>";
echo "<li>暺????交平蝮整???/li>";
echo "<li>蝣箄?敶閬?憿舐內</li>";
echo "<li>蝣箄?頛?憿舐內</li>";
echo "<li>蝣箄?鞈?頛摰?</li>";
echo "<li>蝣箄?銵冽憿舐內甇?Ⅱ?平蝮曇???銝?閰脤?胯???</li>";
echo "<li>蝣箄??平蝮曄?摨?憿舐內??嚗?嚗?6,272嚗?/li>";
echo "<li>蝣箄?瘝?璆剔蜀??瑹＊蝷箝???/li>";
echo "<li>蝣箄?隞??瑕??隞?)??閮?/li>";
echo "<li>蝣箄?蝯梯???甇?Ⅱ憿舐內</li>";
echo "<li>皜祈岫???嚗??????具SC?蛛?</li>";
echo "</ol>";

echo "<h2>甇仿? 3嚗?憿那??/h2>";

echo "<h3>憒?隞???嚗?/h3>";
echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
echo "<tr><th>??</th><th>?航??</th><th>閫?捱?寞?</th></tr>";

$problems = [
    ['敶閬??⊥?憿舐內', 'JavaScript ?航炊', '??F12 ?? Console ?亦??航炊'],
    ['鞈??賣????, 'API ???澆??航炊', '皜祈岫 API ???澆?'],
    ['?芣??典?鞈?憿舐內', '鞈?蝭拚?摩?航炊', '瑼Ｘ stores_by_code 鞈?蝯?'],
    ['隞?璅?銝＊蝷?, '閫鞈??航炊', '瑼Ｘ role 甈??臬甇?Ⅱ'],
    ['蝯梯?鞈??航炊', '閮??摩?航炊', '瑼Ｘ entered_count ??total_amount'],
    ['Session ?航炊', 'session_start() ???澆', '瑼Ｘ?臬????session_start()']
];

foreach ($problems as $problem) {
    echo "<tr>";
    echo "<td>{$problem[0]}</td>";
    echo "<td>{$problem[1]}</td>";
    echo "<td>{$problem[2]}</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>甇仿? 4嚗??賡?霅???/h2>";

echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>??</th><th>??蝯?</th><th>皜祈岫蝯?</th><th>?酉</th></tr>";

$features = [
    ['??憿舐內', '璅??喲?憿舐內???交平蝮整???, '', ''],
    ['???交?', '??憿舐內?冽?交?', '', ''],
    ['敶閬?', '暺???憿舐內敶閬?', '', ''],
    ['頛?', '憿舐內頛?', '', ''],
    ['鞈?頛', '??頛?冽璆剔蜀鞈?', '', ''],
    ['銵冽憿舐內', '憿舐內16??瑹?鞈?', '', ''],
    ['璆剔蜀憿舐內', '?平蝮曄?憿舐內??嚗???憿舐內????, '', ''],
    ['隞?璅?', '隞??瑕憿舐內(隞?)璅?', '', ''],
    ['蝯梯???', '憿舐內蝮賣平蝮曉?蝯梯???', '', ''],
    ['???', '暺??????閬?', '', ''],
    ['憭??', '暺?閬?憭?????, '', ''],
    ['ESC??', '?SC?菟???蝒?, '', ''],
    ['CSV?臬', '暺??臬CSV??銝?瑼?', '', ''],
    ['閫甈?', '銝?閫?銝???瑹?, '', ''],
    ['?踵?撘身閮?, '??銝迤撣賊＊蝷?, '', '']
];

foreach ($features as $index => $feature) {
    $id = 'feature_' . ($index + 1);
    echo "<tr>";
    echo "<td>{$feature[0]}</td>";
    echo "<td>{$feature[1]}</td>";
    echo "<td>
        <select name='{$id}_result' style='width: 100%;'>
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

echo "<button type='button' onclick='saveTestResults()' style='padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; margin-top: 20px; font-size: 16px;'>?脣?皜祈岫蝯?</button>";

echo "<h2>甇仿? 5嚗頂蝯梁???/h2>";

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px;'>";
echo "<h3>撌脣?????/h3>";
echo "<ul>";
echo "<li>??隞??瑕璅??</li>";
echo "<li>??蝞∠??⊥?楊頛臬??踝??恍?鈭桅＊蝷綽?</li>";
echo "<li>???冽璆剔蜀敹恍????/li>";
echo "<li>??摰?葫閰血極?瑕?隞?/li>";
echo "<li>???踵?撘身閮?頝刻?蝵格??/li>";
echo "</ul>";

echo "<h3>靽格迤??憿?/h3>";
echo "<ul>";
echo "<li>??API 鞈?蝯???嚗tores_by_code ?澆?嚗?/li>";
echo "<li>??JavaScript null ????</li>";
echo "<li>??cURL ?游???</li>";
echo "<li>???拐辣鞈血潸?瘜?憿?/li>";
echo "<li>??array_values() 憿??航炊??</li>";
echo "</ul>";
echo "</div>";

// 皜 Session
session_destroy();

echo "<script>
function saveTestResults() {
    const selects = document.querySelectorAll('select[name$=\"_result\"]');
    const inputs = document.querySelectorAll('input[name$=\"_notes\"]');
    
    let passed = 0;
    let failed = 0;
    let notTested = 0;
    let notApplicable = 0;
    
    const results = [];
    
    selects.forEach((select, index) => {
        const value = select.value;
        const note = inputs[index] ? inputs[index].value : '';
        
        if (value === 'pass') passed++;
        else if (value === 'fail') failed++;
        else if (value === 'na') notApplicable++;
        else notTested++;
        
        results.push({
            feature: select.name.replace('_result', ''),
            result: value,
            note: note
        });
    });
    
    const total = selects.length;
    const passRate = total > 0 ? Math.round((passed / total) * 100) : 0;
    
    alert(`皜祈岫蝯???嚗\n蝮賡??? \${total}\\n??: \${passed}\\n憭望?: \${failed}\\n?芣葫閰? \${notTested}\\n銝?? \${notApplicable}\\n???? \${passRate}%`);
    
    // 憿舐內閰喟敦蝯?
    let summary = '<h3>閰喟敦皜祈岫蝯?</h3><table border=\"1\" cellpadding=\"8\" style=\"border-collapse: collapse; width: 100%; margin-top: 20px;\">';
    summary += '<tr><th>??</th><th>蝯?</th><th>?酉</th></tr>';
    
    results.forEach(result => {
        if (result.result) {
            summary += '<tr>';
            summary += '<td>' + result.feature + '</td>';
            summary += '<td>' + (result.result === 'pass' ? '????' : result.result === 'fail' ? '??憭望?' : result.result === 'na' ? '??銝?? : '???芣葫閰?) + '</td>';
            summary += '<td>' + (result.note || '') + '</td>';
            summary += '</tr>';
        }
    });
    
    summary += '</table>';
    
    const summaryDiv = document.createElement('div');
    summaryDiv.innerHTML = summary;
    document.body.appendChild(summaryDiv);
    
    // 皛曉??啁???    summaryDiv.scrollIntoView({ behavior: 'smooth' });
}
</script>";

echo "<p style='margin-top: 30px; color: #666;'>皜祈岫摰???: " . date('Y-m-d H:i:s') . "</p>";
?>
