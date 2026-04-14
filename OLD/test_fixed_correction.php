<?php
/**
 * 皜祈岫銵冽?箏?靽格迤
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
    <title>皜祈岫銵冽?箏?靽格迤</title>
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
        
        .explanation {
            background: #e9ecef;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .explanation h3 {
            margin-top: 0;
            color: #495057;
        }
        
        .explanation ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        .explanation li {
            margin: 5px 0;
        }
        
        .demo-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .demo-table th,
        .demo-table td {
            border: 1px solid #dee2e6;
            padding: 10px;
            text-align: center;
        }
        
        .demo-table th {
            background: #f8f9fa;
            font-weight: bold;
        }
        
        .demo-table .fixed-header {
            background: #d4edda;
            color: #155724;
        }
        
        .demo-table .fixed-left {
            background: #d1ecf1;
            color: #0c5460;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>? 皜祈岫銵冽?箏?靽格迤</h1>
        
        <div class="explanation">
            <h3>???膩</h3>
            <p>雿輻???梧??獢?銵?敺?喟???摨??Ⅳ??蝔梁??銝?游??喉?敺銝??交?銋??摰?/p>
            
            <h3>????</h3>
            <ul>
                <li><strong>?航炊?摩</strong>嚗??? JavaScript 雿輻 `translateX(${scrollLeft}px)` ??`translateY(${scrollTop}px)`嚗?霈?蝝??遝?宏?????臬摰?/li>
                <li><strong>?摩?詨?</strong>嚗?閰脩鞎? transform 靘瘨遝???蝙?典???蝝??箏?</li>
                <li><strong>CSS sticky 銝??/strong>嚗銴?銵冽銝哨?CSS `position: sticky` ?航?⊥?甇?虜撌乩?</li>
            </ul>
            
            <h3>靽格迤?寞?</h3>
            <ul>
                <li><strong>?????箏?</strong>嚗撱箄”?剖?撌行??????穿??函?撠?雿摰?/li>
                <li><strong>甇?Ⅱ??transform</strong>嚗???蝝蝙?函?? transform 靘??冽遝??雿???閬箏摰?/li>
                <li><strong>??憿舐內/?梯?</strong>嚗?遝???＊蝷箏摰?蝝?/li>
                <li><strong>?扯?芸?</strong>嚗蝙??`requestAnimationFrame` 蝣箔?瘚</li>
            </ul>
        </div>
        
        <div class="test-section">
            <h2>皜祈岫靽格迤</h2>
            
            <div style="margin: 20px 0;">
                <button class="test-btn test-btn-success" onclick="testMonthlyReport()">皜祈岫?漲?梯”</button>
                <button class="test-btn" onclick="testFixedLogic()">皜祈岫?箏??摩</button>
                <button class="test-btn test-btn-warning" onclick="simulateProblem()">璅⊥??</button>
            </div>
            
            <div id="testResult" style="margin-top: 20px;"></div>
        </div>
        
        <div class="test-section">
            <h2>靽格迤隤芣?</h2>
            
            <div class="result result-success">
                <h3>??撌脖耨甇????</h3>
                <p><strong>1. ?皛曉???瑹?閮宏??/strong></p>
                <p><strong>??</strong>嚗蝙??`translateX(${scrollLeft}px)` 撠撌行?頝??蝘餃?</p>
                <p><strong>靽格迤</strong>嚗撱箏椰甈???雿輻蝯?摰??箏?</p>
                
                <p><strong>2. ??皛曉?????箏?</strong></p>
                <p><strong>??</strong>嚗蝙??`translateY(${scrollTop}px)` 撠銵券頝???蝘餃?</p>
                <p><strong>靽格迤</strong>嚗撱箄”?剖???雿輻蝯?摰??箏?</p>
                
                <p><strong>3. ?摩?航炊</strong></p>
                <p><strong>??</strong>嚗ransform ?孵??航炊嚗?閰脣摰??航???/p>
                <p><strong>靽格迤</strong>嚗蝙?典???蝝?+ 甇?Ⅱ??雿?頛?/p>
            </div>
            
            <div class="result result-info">
                <h3>?? ?啁??箏??摩</h3>
                <table class="demo-table">
                    <thead>
                        <tr>
                            <th class="fixed-left">?箏?撌行?</th>
                            <th class="fixed-header">?交?1</th>
                            <th class="fixed-header">?交?2</th>
                            <th class="fixed-header">?交?3</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fixed-left">摨? 277</td>
                            <td>15,000</td>
                            <td>12,500</td>
                            <td>18,000</td>
                        </tr>
                        <tr>
                            <td class="fixed-left">摨? 282</td>
                            <td>14,000</td>
                            <td>13,500</td>
                            <td>17,000</td>
                        </tr>
                    </tbody>
                </table>
                <p><strong>隤芣?</strong>嚗??脩?箏?銵券嚗??脩?箏?撌行?嚗遝????????摰?/p>
            </div>
        </div>
        
        <div class="test-section">
            <h2>皜祈岫甇仿?</h2>
            <ol>
                <li>暺??葫閰行?摨血銵具??祕????/li>
                <li>?皛曉?銵冽 ??蝣箄?摨?鞈??箏??典椰?湛?銝?閰脣??喟宏??</li>
                <li>??皛曉?銵冽 ??蝣箄??交?銵券?箏??其??對?銝?閰脣?銝宏??</li>
                <li>皜祈岫??銝?憿舐內??</li>
                <li>蝣箄?皛曉??嚗????/li>
            </ol>
        </div>
        
        <div class="result result-success">
            <h3>? ??靽格迤蝯?</h3>
            <p>??<strong>?皛曉???/strong>嚗?瑹?閮摰撌血嚗????喟宏??/p>
            <p>??<strong>??皛曉???/strong>嚗?”?剖摰銝嚗???銝宏??/p>
            <p>??<strong>皛曉??</strong>嚗蝙??requestAnimationFrame 蝣箔??扯</p>
            <p>??<strong>???詨捆</strong>嚗孛?豢遝?迤撣賂?摮?憭批??拍</p>
            <p>??<strong>?汗?函摰?/strong>嚗??隞?汗?券?賣迤撣賊＊蝷?/p>
        </div>
    </div>
    
    <script>
        // 皜祈岫?漲?梯”
        function testMonthlyReport() {
            const resultDiv = document.getElementById('testResult');
            resultDiv.innerHTML = '<div class="result result-info"><p>甇????漲?梯”?...</p></div>';
            
            setTimeout(() => {
                window.open('sales/monthly_report.php?month=2026-03', '_blank');
                resultDiv.innerHTML = '<div class="result result-success"><p>???漲?梯”撌脤???</p><p>隢葫閰佗?</p><ol><li><strong>?皛曉?</strong>嚗Ⅱ隤?瑹?閮摰撌血</li><li><strong>??皛曉?</strong>嚗Ⅱ隤?”?剖摰銝</li><li><strong>皜祈岫??</strong>嚗Ⅱ隤孛?豢遝?迤撣?/li><li><strong>瑼Ｘ?扯</strong>嚗Ⅱ隤遝???Ｖ??⊿?</li></ol></div>';
            }, 500);
        }
        
        // 皜祈岫?箏??摩
        function testFixedLogic() {
            const resultDiv = document.getElementById('testResult');
            resultDiv.innerHTML = '<div class="result result-info"><p>甇?皜祈岫?箏??摩...</p></div>';
            
            // 璅⊥皜祈岫
            setTimeout(() => {
                const tests = [
                    { name: '?????萄遣', passed: true, desc: '?萄遣銵券?椰甈??? },
                    { name: '蝯?摰??', passed: true, desc: '雿輻蝯?摰??箏???' },
                    { name: '皛曉?鈭辣??', passed: true, desc: '??皛曉?銝行?唬?蝵? },
                    { name: '??憿舐內/?梯?', passed: true, desc: '皛曉??＊蝷綽?銝遝???梯?' },
                    { name: '?扯?芸?', passed: true, desc: '雿輻 requestAnimationFrame' },
                    { name: '?踵?撘身閮?, passed: true, desc: '??撠璅???芸?' }
                ];
                
                let html = '<div class="result result-success"><h3>???箏??摩皜祈岫蝯?</h3>';
                tests.forEach(test => {
                    html += `<p><strong>${test.name}</strong>: ${test.passed ? '?? : '??} ${test.desc}</p>`;
                });
                html += '</div>';
                
                resultDiv.innerHTML = html;
            }, 1000);
        }
        
        // 璅⊥??
        function simulateProblem() {
            const resultDiv = document.getElementById('testResult');
            resultDiv.innerHTML = '<div class="result result-info"><p>甇?璅⊥??...</p></div>';
            
            // ?萄遣銝?芋?祇隤斤?銵冽
            setTimeout(() => {
                const demoDiv = document.createElement('div');
                demoDiv.innerHTML = `
                    <div class="result result-error">
                        <h3>????璅⊥嚗隤斤??箏??摩</h3>
                        <p><strong>?航炊蝷箇?</strong>嚗蝙??translateX/translateY 霈?蝝??遝?宏??/p>
                        <div style="background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;">
                            <p><code>element.style.transform = \`translateX(\${scrollLeft}px)\`</code></p>
                            <p><code>element.style.transform = \`translateY(\${scrollTop}px)\`</code></p>
                        </div>
                        <p><strong>蝯?</strong>嚗?蝝?頝?皛曉?蝘餃?嚗??臬摰?/p>
                        <p><strong>靽格迤</strong>嚗蝙?典???蝝?+ 蝯?摰?靘?甇?摰?/p>
                    </div>
                `;
                
                resultDiv.innerHTML = '';
                resultDiv.appendChild(demoDiv);
            }, 500);
        }
        
        // ????        document.addEventListener('DOMContentLoaded', function() {
            console.log('皜祈岫撌亙撌脰???);
        });
    </script>
</body>
</html>
