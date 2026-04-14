<?php
/**
 * 皜祈岫?餉??璈銵冽?箏??寞?
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
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>皜祈岫?餉??璈銵冽?箏??寞?</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background: #f8f9fa; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 3px 15px rgba(0,0,0,0.1); }
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
        
        .test-btn-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .test-btn-warning:hover {
            background: #e0a800;
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
        
        .result-error {
            border-color: #dc3545;
            background: #f8d7da;
        }
        
        .result-info {
            border-color: #17a2b8;
            background: #d1ecf1;
        }
        
        .device-info {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        
        .device-info h3 {
            margin-top: 0;
            color: #495057;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>?? 皜祈岫?餉??璈銵冽?箏??寞?</h1>
        
        <div class="device-info">
            <h3>鋆蔭鞈?</h3>
            <p><strong>雿輻??</strong> <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['role']); ?>)</p>
            <p><strong>?Ｗ?撖砍漲嚗?/strong> <span id="screenWidth">閮?銝?..</span></p>
            <p><strong>鋆蔭憿?嚗?/strong> <span id="deviceType">瑼Ｘ葫銝?..</span></p>
            <p><strong>?汗?剁?</strong> <span id="browserInfo">瑼Ｘ葫銝?..</span></p>
        </div>
        
        <div class="test-section">
            <h2>皜祈岫?</h2>
            
            <div style="margin: 20px 0;">
                <button class="test-btn" onclick="testMonthlyReport()">皜祈岫?漲?梯”</button>
                <button class="test-btn test-btn-success" onclick="testUniversalFix()">皜祈岫??箏??寞?</button>
                <button class="test-btn test-btn-warning" onclick="testMobileSimulation()">璅⊥??皜祈岫</button>
            </div>
            
            <div id="testResult" style="margin-top: 20px;"></div>
        </div>
        
        <div class="test-section">
            <h2>皜祈岫隤芣?</h2>
            
            <div class="result result-info">
                <h3>??撌脣祕?賜?靽格迤</h3>
                <p>1. <strong>CSS 璅??瘛餃?</strong>嚗?漲?梯”銝剜溶???銵冽?箏?璅??</p>
                <p>2. <strong>JavaScript 蝔?蝣潭溶??/strong>嚗?摨瘛餃?鈭?箏??摩</p>
                <p>3. <strong>銵冽蝯?蝪∪?</strong>嚗?瑹誨???迂撌脣?雿萄??甈?/p>
                <p>4. <strong>?踵?撘身閮?/strong>嚗???血???鋆蔭</p>
            </div>
            
            <div class="result result-info">
                <h3>? ??撠?芸?</h3>
                <p>??摮?憭批??芸?隤踵嚗?4px嚗?/p>
                <p>???折?頝??10px 6px嚗?/p>
                <p>??撌行?撖砍漲隤踵嚗?40px嚗?/p>
                <p>??閫豢皛曉??芸?</p>
            </div>
        </div>
        
        <div class="test-section">
            <h2>皜祈岫甇仿?</h2>
            <ol>
                <li>暺??葫閰行?摨血銵具??祕????/li>
                <li>璈怠?皛曉?銵冽嚗Ⅱ隤”?剖?撌行??臬?箏?</li>
                <li>暺??葫閰阡?箏??寞???霅???/li>
                <li>暺??芋?祆?璈葫閰艾葫閰行?璈?撽?/li>
                <li>?其???蝵桐?皜祈岫嚗?艾?璈像?選?</li>
            </ol>
        </div>
        
        <div class="result result-success">
            <h3>? ??蝯?</h3>
            <p>???交?銵券皛曉??摰?銝</p>
            <p>??摨?鞈?皛曉??摰撌血</p>
            <p>???餉??璈?賣迤撣賊＊蝷?/p>
            <p>??皛曉??嚗????/p>
            <p>????汗?函摰?/p>
        </div>
    </div>
    
    <script>
        // 鋆蔭瑼Ｘ葫
        function detectDevice() {
            const screenWidth = window.innerWidth;
            const isMobile = screenWidth <= 768;
            const isTablet = screenWidth > 768 && screenWidth <= 1024;
            const isDesktop = screenWidth > 1024;
            
            let deviceType = '? 獢?餉';
            if (isMobile) deviceType = '? ??';
            if (isTablet) deviceType = '?儭?撟單';
            
            document.getElementById('screenWidth').textContent = screenWidth + 'px';
            document.getElementById('deviceType').textContent = deviceType;
            document.getElementById('browserInfo').textContent = navigator.userAgent.split(' ')[0];
        }
        
        // 皜祈岫?漲?梯”
        function testMonthlyReport() {
            const resultDiv = document.getElementById('testResult');
            resultDiv.innerHTML = '<div class="result result-info"><p>甇????漲?梯”?...</p></div>';
            
            setTimeout(() => {
                window.open('sales/monthly_report.php?month=2026-03', '_blank');
                resultDiv.innerHTML = '<div class="result result-success"><p>???漲?梯”撌脤???</p><p>隢葫閰佗?</p><ol><li>璈怠?皛曉?銵冽</li><li>蝣箄??交?銵券?臬?箏?</li><li>蝣箄?摨?鞈??臬?箏?</li><li>皜祈岫??銝?憿舐內??</li></ol></div>';
            }, 500);
        }
        
        // 皜祈岫??箏??寞?
        function testUniversalFix() {
            const resultDiv = document.getElementById('testResult');
            resultDiv.innerHTML = '<div class="result result-info"><p>甇?皜祈岫??箏??寞?...</p></div>';
            
            // 璅⊥皜祈岫?摩
            setTimeout(() => {
                const isMobile = window.innerWidth <= 768;
                const hasTable = document.querySelector('.monthly-table') !== null;
                const hasJSClass = document.querySelector('.monthly-table.js-fixed') !== null;
                
                let testResults = [];
                
                if (hasTable) {
                    testResults.push('???曉銵冽??');
                } else {
                    testResults.push('???芣?啗”?澆?蝝?);
                }
                
                if (hasJSClass) {
                    testResults.push('??JS ?箏?憿撌脫???);
                } else {
                    testResults.push('?? JS ?箏?憿?芣??剁??航銝?漲?梯”?嚗?);
                }
                
                if (isMobile) {
                    testResults.push('? 瑼Ｘ葫?唳?璈?蝵殷?撌脣??冽?璈??);
                } else {
                    testResults.push('? 瑼Ｘ葫?唳??Ｚ?蝵殷?雿輻獢璅??');
                }
                
                resultDiv.innerHTML = '<div class="result result-success"><h3>????箏??寞?皜祈岫蝯?</h3><ul>' + 
                    testResults.map(result => `<li>${result}</li>`).join('') + 
                    '</ul><p>隢祕?葫閰行?摨血銵券??ＹⅡ隤??賣迤撣詻?/p></div>';
            }, 1000);
        }
        
        // 璅⊥??皜祈岫
        function testMobileSimulation() {
            const resultDiv = document.getElementById('testResult');
            resultDiv.innerHTML = '<div class="result result-info"><p>甇?璅⊥??皜祈岫...</p></div>';
            
            // ?脣???閬閮剖?
            const viewport = document.querySelector('meta[name="viewport"]');
            const originalContent = viewport.content;
            
            // ???唳?璈???            viewport.content = 'width=375, initial-scale=1.0, maximum-scale=1.0, user-scalable=no';
            
            // 撘瑕??
            document.body.style.width = '375px';
            window.dispatchEvent(new Event('resize'));
            
            // ?湔鋆蔭瑼Ｘ葫
            detectDevice();
            
            resultDiv.innerHTML = '<div class="result result-success"><p>??撌脣????璅⊥璅∪? (375px)</p>' +
                '<p>隢葫閰佗?</p>' +
                '<ol>' +
                '<li>璈怠?皛曉?銵冽</li>' +
                '<li>閫撖”?剖?撌行??臬?箏?</li>' +
                '<li>皜祈岫閫豢皛曉?擃?</li>' +
                '<li>蝣箄?摮?憭批??拍</li>' +
                '</ol>' +
                '<p>10蝘??芸??Ｗ儔甇?虜璅∪???/p></div>';
            
            alert('? 撌脣????璅⊥璅∪?\n\n隢葫閰佗?\n1. 璈怠?皛曉?銵冽\n2. 閫撖”?剖?撌行??臬?箏?\n3. 皜祈岫閫豢皛曉?擃?\n\n10蝘??芸??Ｗ儔');
            
            // 10蝘??Ｗ儔
            setTimeout(() => {
                viewport.content = originalContent;
                document.body.style.width = '';
                window.dispatchEvent(new Event('resize'));
                detectDevice();
                
                resultDiv.innerHTML = '<div class="result result-success"><p>??撌脫敺拇迤撣豢芋撘?/p></div>';
            }, 10000);
        }
        
        // ????        document.addEventListener('DOMContentLoaded', function() {
            detectDevice();
            
            // ??閬?憭批?霈?
            window.addEventListener('resize', detectDevice);
            
            console.log('皜祈岫撌亙撌脰???);
        });
    </script>
</body>
</html>
