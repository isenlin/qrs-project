<?php
/**
 * 皜祈岫?蝯??箄?蝒??? */

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
$last_month = date('Y-m', strtotime('-1 month'));
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>皜祈岫?蝯??箄?蝒???/title>
    <style>
        body { font-family: sans-serif; padding: 20px; background: #f8f9fa; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 3px 15px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 15px; }
        h2 { color: #444; margin-top: 30px; }
        
        .test-section {
            border: 2px solid #007bff;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            background: #f8f9fa;
        }
        
        .demo-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin: 20px 0;
        }
        
        .demo-btn {
            flex: 1;
            min-width: 200px;
            padding: 15px;
            background: #f8f9fa;
            border: 2px solid #007bff;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .demo-btn:hover {
            background: #e9ecef;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .demo-btn h4 {
            margin: 0 0 10px 0;
            color: #007bff;
        }
        
        .demo-btn p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
        
        .result {
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            border-left: 5px solid;
        }
        
        .result-success {
            border-color: #28a745;
            background: #d4edda;
        }
        
        .result-info {
            border-color: #17a2b8;
            background: #d1ecf1;
        }
        
        .feature-list {
            background: #e9ecef;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .feature-list h3 {
            margin-top: 0;
            color: #495057;
        }
        
        .feature-list ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        .feature-list li {
            margin: 5px 0;
        }
        
        .test-steps {
            background: #fff3cd;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 5px solid #ffc107;
        }
        
        .test-steps h3 {
            margin-top: 0;
            color: #856404;
        }
        
        .test-steps ol {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        .test-steps li {
            margin: 8px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>???蝯??箄?蝒??賣葫閰?/h1>
        
        <div class="feature-list">
            <h3>? 撌脣????</h3>
            <ul>
                <li><strong>?? PDF ?臬</strong>嚗蝙?函汗?典??啣??踝??芸??璅??</li>
                <li><strong>?? CSV ?臬</strong>嚗??渲”?潸???箇 CSV 瑼?</li>
                <li><strong>?? 敶閬?</strong>嚗?嗅之撠?雿蔭???箄?蝒?/li>
                <li><strong>? 銵冽?箏?</strong>嚗”?剖?撌行?皛曉??摰?/li>
                <li><strong>?? ?遢?豢?</strong>嚗???/銝????</li>
                <li><strong>? 隞?璅?</strong>嚗??誨?剝?格?閮???/li>
                <li><strong>? ?踵?撘身閮?/strong>嚗?湧?艾?璈像??/li>
            </ul>
        </div>
        
        <div class="test-section">
            <h2>蝡皜祈岫</h2>
            
            <div class="demo-buttons">
                <div class="demo-btn" onclick="openPopup('?祆?')">
                    <h4>?? ?祆???瑹??交平蝮?/h4>
                    <p>敶閬??亦? <?php echo $current_month; ?> ?遢閰喟敦?梯”</p>
                </div>
                
                <div class="demo-btn" onclick="openPopup('銝?')">
                    <h4>?? 銝???瑹??交平蝮?/h4>
                    <p>敶閬??亦? <?php echo $last_month; ?> ?遢閰喟敦?梯”</p>
                </div>
                
                <div class="demo-btn" onclick="openPopup('?芾?')">
                    <h4>?? ?芾??遢</h4>
                    <p>敶閬??亦????遢?底蝝啣銵?/p>
                </div>
            </div>
            
            <div id="testResult" style="margin-top: 20px;"></div>
        </div>
        
        <div class="test-steps">
            <h3>? 皜祈岫甇仿?</h3>
            <ol>
                <li><strong>??敶閬?</strong>嚗????寞??????箄?蝒?/li>
                <li><strong>皜祈岫銵冽?箏?</strong>嚗?                    <ul>
                        <li>?皛曉?嚗Ⅱ隤?瑹?閮摰撌血</li>
                        <li>??皛曉?嚗Ⅱ隤?”?剖摰銝</li>
                    </ul>
                </li>
                <li><strong>皜祈岫 PDF ?臬</strong>嚗?                    <ul>
                        <li>暺?蝝???PDF????/li>
                        <li>蝣箄???汗憿舐內摰銵冽</li>
                        <li>蝣箄???????賣迤蝣粹＊蝷?/li>
                    </ul>
                </li>
                <li><strong>皜祈岫 CSV ?臬</strong>嚗?                    <ul>
                        <li>暺?蝬???CSV????/li>
                        <li>蝣箄? CSV 瑼?甇?Ⅱ銝?</li>
                        <li>??Excel ??摮楊頛臬??蝣箄??批捆</li>
                    </ul>
                </li>
                <li><strong>皜祈岫?遢??</strong>嚗?                    <ul>
                        <li>暺?????????????/li>
                        <li>蝣箄??遢甇?Ⅱ??</li>
                        <li>蝣箄?銵冽鞈?甇?Ⅱ?湔</li>
                    </ul>
                </li>
                <li><strong>皜祈岫???</strong>嚗??? ??????/li>
                <li><strong>皜祈岫??憿舐內</strong>嚗蝙?冽?璈?璅⊥??皜祈岫</li>
            </ol>
        </div>
        
        <div class="result result-success">
            <h3>? ??蝯?</h3>
            <p>??敶閬?甇?虜??嚗＊蝷箇陛??銵冽隞</p>
            <p>??銵冽?箏??甇?虜嚗”?剖?撌行?皛曉??摰?/p>
            <p>??PDF ?臬甇?虜嚗??圈?閬賡＊蝷箏??渲”?潘??????閬?/p>
            <p>??CSV ?臬甇?虜嚗SV 瑼?甇?Ⅱ銝?嚗??怠??渲???/p>
            <p>???遢??甇?虜嚗隞亙?????隞?/p>
            <p>?????甇?虜嚗隞仿????箄?蝒?/p>
            <p>???踵?撘身閮迤撣賂??其???蝵桐??質甇?虜憿舐內</p>
        </div>
        
        <div class="result result-info">
            <h3>?? ?銵牧??/h3>
            <p><strong>PDF ?臬?寞?</strong>嚗蝙?函汗?典撱箏??啣??踝????芸????唳見撘?蝣箔???????賢????湧＊蝷箝?/p>
            <p><strong>CSV ?臬?寞?</strong>嚗蝙?函? JavaScript ?? CSV 瑼?嚗??怠??渡?銵冽鞈??葉?楊蝣潭?氬?/p>
            <p><strong>銵冽?箏??寞?</strong>嚗蝙??CSS <code>position: sticky</code>嚗陛?桀????隞?汗?券?舀??/p>
            <p><strong>敶閬??批</strong>嚗??蝞?嗥?閬?憭批???蝵殷????臬末?蝙?刻?撽?/p>
        </div>
    </div>
    
    <script>
        // ??敶閬?
        function openPopup(type) {
            const resultDiv = document.getElementById('testResult');
            
            let url = '';
            let title = '';
            
            if (type === '?祆?') {
                url = 'daily_sales_final.php?month=<?php echo $current_month; ?>';
                title = '?祆???瑹??交平蝮?;
            } else if (type === '銝?') {
                url = 'daily_sales_final.php?month=<?php echo $last_month; ?>';
                title = '銝???瑹??交平蝮?;
            } else {
                const customMonth = prompt('隢撓?交?隞?(?澆?: YYYY-MM嚗?憒? 2026-03):', '<?php echo $current_month; ?>');
                if (!customMonth) return;
                if (!/^\d{4}-\d{2}$/.test(customMonth)) {
                    alert('?遢?澆??航炊嚗?雿輻 YYYY-MM ?澆?');
                    return;
                }
                url = `daily_sales_final.php?month=${customMonth}`;
                title = `${customMonth} ??瑹??交平蝮霉;
            }
            
            resultDiv.innerHTML = `<div class="result result-info"><p>甇???敶閬?: ${title}...</p></div>`;
            
            // 閮剖?敶閬??
            const width = Math.min(1200, window.innerWidth - 40);
            const height = Math.min(800, window.innerHeight - 40);
            const left = (window.innerWidth - width) / 2;
            const top = (window.innerHeight - height) / 2;
            
            // 敶閬?閮剖?
            const features = [
                `width=${width}`,
                `height=${height}`,
                `left=${left}`,
                `top=${top}`,
                'menubar=no',
                'toolbar=no',
                'location=no',
                'status=no',
                'resizable=yes',
                'scrollbars=yes'
            ].join(',');
            
            // ??敶閬?
            const popup = window.open(url, 'daily_sales_popup', features);
            
            if (popup) {
                resultDiv.innerHTML = `<div class="result result-success"><p>??敶閬?撌脤??? ${title}</p><p>隢??找??寞葫閰行郊撽脰?皜祈岫??/p></div>`;
            } else {
                resultDiv.innerHTML = `<div class="result result-info"><p>?? 敶閬?鋡恍??隢?閮勗??箄?蝒?暺?甇日??嚗?a href="${url}" target="_blank">${title}</a></p></div>`;
            }
        }
        
        console.log('皜祈岫撌亙撌脰???);
    </script>
</body>
</html>
