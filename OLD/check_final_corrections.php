<?php
/**
 * 瑼Ｘ?蝯耨甇? */

// ?? Session
session_start();

// 璅⊥蝞∠??∠??$_SESSION['user_id'] = 'admin';
$_SESSION['username'] = 'admin';
$_SESSION['name'] = '蝟餌絞蝞∠???;
$_SESSION['role'] = 'admin';
$_SESSION['logged_in'] = true;

// 頛閮剖?
require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/auth.php';

$user = get_current_session_user();
$current_month = date('Y-m');
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>瑼Ｘ?蝯耨甇?/title>
    <style>
        body { font-family: sans-serif; padding: 20px; background: #f8f9fa; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 3px 15px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 15px; }
        
        .check-item {
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 5px solid;
            background: #f8f9fa;
        }
        
        .check-success {
            border-color: #28a745;
        }
        
        .check-error {
            border-color: #dc3545;
        }
        
        .check-warning {
            border-color: #ffc107;
        }
        
        .check-title {
            font-weight: bold;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .check-desc {
            color: #666;
            font-size: 14px;
            margin-left: 30px;
        }
        
        .test-btn {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
            font-weight: bold;
        }
        
        .test-btn:hover {
            background: #0056b3;
        }
        
        .code-block {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 12px;
            margin: 10px 0;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>?? 瑼Ｘ?蝯耨甇?/h1>
        
        <div class="check-item check-success">
            <div class="check-title">
                <span>????1嚗蝮質?銝?鈭?/span>
            </div>
            <div class="check-desc">
                <p><strong>???撌脖耨甇?/strong></p>
                <p>撌脣銵冽?敺溶???亙摨蜇閮?嚗??恬?</p>
                <ul>
                    <li>瘥蝮質???</li>
                    <li>?遢蝮質???</li>
                    <li>?望璅??璅?</li>
                    <li>?拍?見撘身閮?/li>
                </ul>
            </div>
        </div>
        
        <div class="check-item check-success">
            <div class="check-title">
                <span>????2嚗宏??PDF ??????/span>
            </div>
            <div class="check-desc">
                <p><strong>???撌脩宏??/strong></p>
                <p>撌脣?瑼?銝剔宏?支誑銝摰對?</p>
                <ul>
                    <li>HTML 銝剔? PDF ??</li>
                    <li>CSS 銝剔? PDF ??璅??</li>
                    <li>JavaScript 銝剔? exportToPDF() ?賣</li>
                    <li>?璅??嚗media print嚗?/li>
                </ul>
                <p>?曉?芸銝?CSV ?臬?????賬?/p>
            </div>
        </div>
        
        <div class="check-item check-warning">
            <div class="check-title">
                <span>?? ?閬葫閰衣??</span>
            </div>
            <div class="check-desc">
                <p>隢葫閰虫誑銝??賣?行迤撣賂?</p>
                <ol>
                    <li><strong>?亦蜇閮＊蝷?/strong>嚗Ⅱ隤”?潭?敺?銵＊蝷箝??亙摨蜇閮?/li>
                    <li><strong>CSV ?臬</strong>嚗????脯??CSV???葫閰血?箏???/li>
                    <li><strong>銵冽?箏?</strong>嚗遝??蝣箄?銵券?椰甈摰?/li>
                    <li><strong>?遢??</strong>嚗葫閰虫???/銝??</li>
                    <li><strong>???</strong>嚗??? ??????/li>
                </ol>
            </div>
        </div>
        
        <div class="check-item">
            <div class="check-title">
                <span>?? 皜祈岫???</span>
            </div>
            <div class="check-desc">
                <a href="daily_sales_final.php?month=<?php echo $current_month; ?>" target="_blank" class="test-btn">
                    皜祈岫?蝯??箄?蝒?                </a>
                <p>??交???<code>daily_sales_final.php?month=<?php echo $current_month; ?></code></p>
            </div>
        </div>
        
        <div class="check-item">
            <div class="check-title">
                <span>?? 靽格迤??</span>
            </div>
            <div class="check-desc">
                <p><strong>撌脖耨甇??瑼?嚗?/strong></p>
                <ul>
                    <li><code>daily_sales_final.php</code> - 瘛餃??亦蜇閮?嚗宏??PDF ?</li>
                    <li><code>dashboard.php</code> - ?湔??隤芣???</li>
                </ul>
                
                <p><strong>靽格迤?批捆嚗?/strong></p>
                <div class="code-block">
// 瘛餃??亦蜇閮???PHP 隞?Ⅳ
&lt;tr class="total-row"&gt;
    &lt;td colspan="2" style="text-align: center; font-weight: bold;"&gt;瘥?典?蝮質?&lt;/td&gt;
    &lt;?php foreach ($dates as $date): ?&gt;
    &lt;td&gt;&lt;?php echo number_format($daily_totals[$date]); ?&gt;&lt;/td&gt;
    &lt;?php endforeach; ?&gt;
    &lt;td&gt;&lt;?php echo number_format($month_total); ?&gt;&lt;/td&gt;
    &lt;td&gt;-&lt;/td&gt;
&lt;/tr&gt;
                </div>
                
                <p><strong>蝘駁?摰對?</strong></p>
                <div class="code-block">
// 蝘駁??PDF ??
&lt;button onclick="exportToPDF()" class="ctrl-btn pdf-btn"&gt;
    &lt;span&gt;??&lt;/span&gt;
    &lt;span&gt;PDF&lt;/span&gt;
&lt;/button&gt;

// 蝘駁??PDF ?賣
function exportToPDF() {
    window.print();
}

// 蝘駁???唳見撘?@media print {
    /* ... */
}
                </div>
            </div>
        </div>
    </div>
    
    <script>
        console.log('瑼Ｘ撌亙撌脰???);
    </script>
</body>
</html>
