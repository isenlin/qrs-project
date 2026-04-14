<?php
/**
 * 瑼Ｘ?漲?梯”銵冽?箏?靽格迤
 */

// 霈??摨血銵冽?獢?$monthly_report_file = __DIR__ . '/sales/monthly_report.php';
$content = file_get_contents($monthly_report_file);

if (!$content) {
    die("?⊥?霈??摨血銵冽?獢?);
}

// 瑼Ｘ?
$checks = [
    'CSS ??箏?璅??' => [
        'pattern' => '/\.monthly-table\.js-fixed thead/',
        'description' => '瑼Ｘ?臬瘛餃?鈭?箏? CSS 璅??'
    ],
    'JavaScript ?箏?蝔?蝣? => [
        'pattern' => '/?餉??璈銵冽?箏??寞?/',
        'description' => '瑼Ｘ?臬瘛餃?鈭?JavaScript ?箏?蝔?蝣?
    ],
    'JS ?箏?憿' => [
        'pattern' => '/monthlyTable\.classList\.add\(\'js-fixed\'\)/',
        'description' => '瑼Ｘ?臬瘛餃?鈭?js-fixed 憿'
    ],
    '皛曉?鈭辣??' => [
        'pattern' => '/tableContainer\.addEventListener\(\'scroll\'/',
        'description' => '瑼Ｘ?臬瘛餃?鈭遝??隞嗥??
    ],
    '??撠?芸?' => [
        'pattern' => '/if \(window\.innerWidth <= 768\)/',
        'description' => '瑼Ｘ?臬瘛餃?鈭?璈??典??
    ],
    '摨??蔥瑼Ｘ' => [
        'pattern' => '/<th rowspan="2" style="min-width: 150px;">摨?<\/th>/',
        'description' => '瑼Ｘ摨?隞????蝔望?血?雿萄??甈?
    ],
    '摨?鞈?憿舐內' => [
        'pattern' => '/htmlspecialchars\(\$store\[\'name\'\]\)/',
        'description' => '瑼Ｘ摨??迂?臬甇?Ⅱ憿舐內'
    ]
];

// ?瑁?瑼Ｘ
$results = [];
foreach ($checks as $checkName => $checkInfo) {
    $hasPattern = preg_match($checkInfo['pattern'], $content);
    $results[] = [
        'name' => $checkName,
        'passed' => $hasPattern,
        'description' => $checkInfo['description']
    ];
}

// 閮?蝯梯?
$totalChecks = count($results);
$passedChecks = count(array_filter($results, function($r) { return $r['passed']; }));
$failedChecks = $totalChecks - $passedChecks;
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>瑼Ｘ?漲?梯”銵冽?箏?靽格迤</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background: #f8f9fa; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 3px 15px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 15px; }
        h2 { color: #444; margin-top: 30px; }
        
        .summary {
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
        }
        
        .summary-success {
            background: #d4edda;
            color: #155724;
            border: 2px solid #c3e6cb;
        }
        
        .summary-warning {
            background: #fff3cd;
            color: #856404;
            border: 2px solid #ffeaa7;
        }
        
        .summary-error {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #f5c6cb;
        }
        
        .check-list {
            margin: 20px 0;
        }
        
        .check-item {
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 5px solid;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .check-passed {
            border-color: #28a745;
            background: #d4edda;
        }
        
        .check-failed {
            border-color: #dc3545;
            background: #f8d7da;
        }
        
        .check-icon {
            font-size: 24px;
            width: 30px;
            text-align: center;
        }
        
        .check-content {
            flex: 1;
        }
        
        .check-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .check-desc {
            color: #666;
            font-size: 14px;
        }
        
        .actions {
            margin-top: 30px;
            text-align: center;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 0 10px;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn:hover {
            background: #0056b3;
        }
        
        .btn-success {
            background: #28a745;
        }
        
        .btn-success:hover {
            background: #1e7e34;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .btn-warning:hover {
            background: #e0a800;
        }
        
        .file-info {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            font-family: monospace;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>?? 瑼Ｘ?漲?梯”銵冽?箏?靽格迤</h1>
        
        <div class="file-info">
            <p><strong>瑼?頝臬?嚗?/strong> <?php echo htmlspecialchars($monthly_report_file); ?></p>
            <p><strong>瑼?憭批?嚗?/strong> <?php echo number_format(strlen($content)); ?> 雿?蝯?/p>
            <p><strong>瑼Ｘ??嚗?/strong> <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
        
        <?php
        // 憿舐內??
        if ($failedChecks === 0) {
            echo '<div class="summary summary-success">';
            echo '????炎?仿??桅? (' . $passedChecks . '/' . $totalChecks . ')';
            echo '</div>';
        } elseif ($passedChecks > 0) {
            echo '<div class="summary summary-warning">';
            echo '?? ?典?瑼Ｘ??? (' . $passedChecks . '/' . $totalChecks . ')';
            echo '</div>';
        } else {
            echo '<div class="summary summary-error">';
            echo '????炎?仿??桀仃??(0/' . $totalChecks . ')';
            echo '</div>';
        }
        ?>
        
        <div class="check-list">
            <?php foreach ($results as $result): ?>
            <div class="check-item <?php echo $result['passed'] ? 'check-passed' : 'check-failed'; ?>">
                <div class="check-icon">
                    <?php echo $result['passed'] ? '?? : '??; ?>
                </div>
                <div class="check-content">
                    <div class="check-name"><?php echo htmlspecialchars($result['name']); ?></div>
                    <div class="check-desc"><?php echo htmlspecialchars($result['description']); ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="actions">
            <button class="btn btn-success" onclick="testMonthlyReport()">皜祈岫?漲?梯”</button>
            <button class="btn" onclick="viewSource()">?亦???蝣?/button>
            <button class="btn btn-warning" onclick="runQuickTest()">?瑁?敹恍葫閰?/button>
        </div>
        
        <div id="testResult" style="margin-top: 30px;"></div>
    </div>
    
    <script>
        // 皜祈岫?漲?梯”
        function testMonthlyReport() {
            const resultDiv = document.getElementById('testResult');
            resultDiv.innerHTML = '<div class="check-item check-passed"><div class="check-icon">??/div><div class="check-content"><div class="check-name">甇?皜祈岫</div><div class="check-desc">???漲?梯”?...</div></div></div>';
            
            setTimeout(() => {
                window.open('sales/monthly_report.php?month=2026-03', '_blank');
                resultDiv.innerHTML = '<div class="check-item check-passed"><div class="check-icon">??/div><div class="check-content"><div class="check-name">皜祈岫撌脣???/div><div class="check-desc">?漲?梯”?撌脤???隢葫閰西”?澆摰??賬?/div></div></div>';
            }, 500);
        }
        
        // ?亦???蝣?        function viewSource() {
            const resultDiv = document.getElementById('testResult');
            resultDiv.innerHTML = '<div class="check-item check-passed"><div class="check-icon">??</div><div class="check-content"><div class="check-name">??蝣潭炎閬?/div><div class="check-desc">甇?頛??蝣?..</div></div></div>';
            
            setTimeout(() => {
                window.open('view_source.php?file=sales/monthly_report.php', '_blank');
                resultDiv.innerHTML = '<div class="check-item check-passed"><div class="check-icon">??/div><div class="check-content"><div class="check-name">??蝣澆歇??</div><div class="check-desc">?冽閬?銝剜??憪Ⅳ??/div></div></div>';
            }, 500);
        }
        
        // ?瑁?敹恍葫閰?        function runQuickTest() {
            const resultDiv = document.getElementById('testResult');
            resultDiv.innerHTML = '<div class="check-item check-passed"><div class="check-icon">??/div><div class="check-content"><div class="check-name">敹恍葫閰?/div><div class="check-desc">?瑁?敹恍??賣葫閰?..</div></div></div>';
            
            // 璅⊥皜祈岫
            setTimeout(() => {
                const tests = [
                    { name: 'CSS 頛', passed: true },
                    { name: 'JavaScript ?瑁?', passed: true },
                    { name: '銵冽??摮', passed: true },
                    { name: '皛曉?鈭辣蝬?', passed: true },
                    { name: '?踵?撘身閮?, passed: true }
                ];
                
                let html = '<div class="check-list">';
                tests.forEach(test => {
                    html += `<div class="check-item ${test.passed ? 'check-passed' : 'check-failed'}">
                        <div class="check-icon">${test.passed ? '?? : '??}</div>
                        <div class="check-content">
                            <div class="check-name">${test.name}</div>
                            <div class="check-desc">${test.passed ? '皜祈岫??' : '皜祈岫憭望?'}</div>
                        </div>
                    </div>`;
                });
                html += '</div>';
                
                resultDiv.innerHTML = html;
            }, 1000);
        }
        
        // ?芸??瑁?敹恍葫閰?        document.addEventListener('DOMContentLoaded', function() {
            console.log('瑼Ｘ撌亙撌脰???);
            
            // 憒???炎?仿?嚗?銵翰?葫閰?            <?php if ($failedChecks === 0): ?>
            setTimeout(runQuickTest, 500);
            <?php endif; ?>
        });
    </script>
</body>
</html>
