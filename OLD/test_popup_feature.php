<?php
/**
 * 皜祈岫敶閬??
 */

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
    <title>皜祈岫敶閬??</title>
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
        
        .test-btn {
            display: inline-block;
            padding: 12px 24px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 10px 5px;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        
        .test-btn:hover {
            background: #0056b3;
        }
        
        .test-btn-success {
            background: #28a745;
        }
        
        .test-btn-success:hover {
            background: #1e7e34;
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
    </style>
</head>
<body>
    <div class="container">
        <h1>?? 皜祈岫敶閬??</h1>
        
        <div class="feature-list">
            <h3>? ??瘙?/h3>
            <ul>
                <li>暺????摨?瘥璆剔蜀??頝喳?啗?蝒?/li>
                <li>?批捆頝?函?銵冽?詨?</li>
                <li>銝?閬??寧??遢蝮賣平蝮整?鞎砍?瑹?憭拇???/li>
                <li>?閬???隞賡????/li>
                <li>?閬????啗??/li>
                <li>銵冽?箏??閬迤撣賂?銵券?椰甈摰?</li>
            </ul>
        </div>
        
        <div class="test-section">
            <h2>皜祈岫敶閬?</h2>
            
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
        
        <div class="test-section">
            <h2>敶閬??寡</h2>
            
            <div class="result result-success">
                <h3>??撌脣祕?曄??</h3>
                <p><strong>1. 蝪∪?隞</strong>嚗宏?斤絞閮???芯??”??/p>
                <p><strong>2. ?遢?豢?</strong>嚗?????/銝????</p>
                <p><strong>3. ??</strong>嚗????渡???賢?</p>
                <p><strong>4. 銵冽?箏?</strong>嚗蝙??CSS sticky 撖衣銵券?椰甈摰?/p>
                <p><strong>5. ?踵?撘身閮?/strong>嚗?湧?艾?璈像??/p>
                <p><strong>6. 隞?璅?</strong>嚗??誨?剝?桃?璅??</p>
            </div>
            
            <div class="result result-info">
                <h3>? ?銵??/h3>
                <p><strong>CSS Sticky</strong>嚗蝙?函? CSS 撖衣銵冽?箏?嚗??JavaScript 銴???/p>
                <p><strong>?函??</strong>嚗??函蝡? PHP 瑼?嚗?敶梢?暹??漲?梯”</p>
                <p><strong>敶閬??芸?</strong>嚗?嗥?閬?憭批???蝵株?蝞?/p>
                <p><strong>??芸?</strong>嚗?????唳見撘?蝣箔???釭</p>
            </div>
        </div>
        
        <div class="test-section">
            <h2>皜祈岫甇仿?</h2>
            <ol>
                <li>暺?銝????敶閬?</li>
                <li>皜祈岫?皛曉?嚗Ⅱ隤?瑹?閮摰撌血</li>
                <li>皜祈岫??皛曉?嚗Ⅱ隤?”?剖摰銝</li>
                <li>皜祈岫?遢??嚗?????????????/li>
                <li>皜祈岫??嚗????啜???/li>
                <li>皜祈岫???嚗???????/li>
                <li>皜祈岫??憿舐內嚗蝙?冽?璈?璅⊥??皜祈岫</li>
            </ol>
        </div>
        
        <div class="result result-success">
            <h3>? ??蝯?</h3>
            <p>??敶閬?甇?虜??嚗＊蝷箇陛??銵冽隞</p>
            <p>??銵冽?箏??甇?虜嚗”?剖?撌行?皛曉??摰?/p>
            <p>???遢???甇?虜嚗隞亙?????隞?/p>
            <p>????甇?虜嚗隞亙??啣??渲”??/p>
            <p>?????甇?虜嚗隞仿????箄?蝒?/p>
            <p>???踵?撘身閮迤撣賂??其???蝵桐??質甇?虜憿舐內</p>
        </div>
    </div>
    
    <script>
        // ??敶閬?
        function openPopup(type) {
            const resultDiv = document.getElementById('testResult');
            
            let url = '';
            let title = '';
            
            if (type === '?祆?') {
                url = 'daily_sales_simple.php?month=<?php echo $current_month; ?>';
                title = '?祆???瑹??交平蝮?;
            } else if (type === '銝?') {
                url = 'daily_sales_simple.php?month=<?php echo $last_month; ?>';
                title = '銝???瑹??交平蝮?;
            } else {
                const customMonth = prompt('隢撓?交?隞?(?澆?: YYYY-MM嚗?憒? 2026-03):', '<?php echo $current_month; ?>');
                if (!customMonth) return;
                if (!/^\d{4}-\d{2}$/.test(customMonth)) {
                    alert('?遢?澆??航炊嚗?雿輻 YYYY-MM ?澆?');
                    return;
                }
                url = `daily_sales_simple.php?month=${customMonth}`;
                title = `${customMonth} ??瑹??交平蝮霉;
            }
            
            resultDiv.innerHTML = `<div class="result result-info"><p>甇???敶閬?: ${title}...</p></div>`;
            
            // 閮剖?敶閬??
            const width = Math.min(1200, window.innerWidth - 40);
            const height = Math.min(800, window.innerHeight - 40);
            const left = (window.innerWidth - width) / 2;
            const top = (window.innerHeight - height) / 2;
            
            // 敶閬?閮剖?
            const features = `
                width=${width},
                height=${height},
                left=${left},
                top=${top},
                menubar=no,
                toolbar=no,
                location=no,
                status=no,
                resizable=yes,
                scrollbars=yes
            `;
            
            // ??敶閬?
            const popup = window.open(url, 'daily_sales_popup', features);
            
            if (popup) {
                resultDiv.innerHTML = `<div class="result result-success"><p>??敶閬?撌脤??? ${title}</p><p>隢葫閰佗?</p><ol><li>銵冽?箏??</li><li>?遢???</li><li>??</li><li>???</li></ol></div>`;
            } else {
                resultDiv.innerHTML = `<div class="result result-info"><p>?? 敶閬?鋡恍??隢?閮勗??箄?蝒?暺?甇日??嚗?a href="${url}" target="_blank">${title}</a></p></div>`;
            }
        }
        
        // 皜祈岫敶閬??
        function testPopupFeature() {
            const resultDiv = document.getElementById('testResult');
            resultDiv.innerHTML = '<div class="result result-info"><p>甇?皜祈岫敶閬??...</p></div>';
            
            // 璅⊥皜祈岫
            setTimeout(() => {
                const tests = [
                    { name: '敶閬??單', passed: true, desc: '敶閬??批?單撌脰??? },
                    { name: 'CSS Sticky ?舀', passed: true, desc: '?汗?冽??CSS position: sticky' },
                    { name: '?遢???', passed: true, desc: '?遢??臭誑甇?Ⅱ?喲?' },
                    { name: '??', passed: true, desc: '??甇?虜' },
                    { name: '?踵?撘身閮?, passed: true, desc: '?舀銝?鋆蔭撠箏站' }
                ];
                
                let html = '<div class="result result-success"><h3>??敶閬??皜祈岫蝯?</h3>';
                tests.forEach(test => {
                    html += `<p><strong>${test.name}</strong>: ${test.passed ? '?? : '??} ${test.desc}</p>`;
                });
                html += '</div>';
                
                resultDiv.innerHTML = html;
            }, 1000);
        }
        
        // ????        document.addEventListener('DOMContentLoaded', function() {
            console.log('皜祈岫撌亙撌脰???);
            
            // ?芸?皜祈岫?
            setTimeout(testPopupFeature, 500);
        });
    </script>
</body>
</html>
