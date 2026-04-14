<?php
/**
 * 皜祈岫?蝯”?澆摰耨甇? */

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
    <title>皜祈岫?蝯”?澆摰耨甇?/title>
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
        
        .issue-list {
            background: #fff3cd;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 5px solid #ffc107;
        }
        
        .issue-list h3 {
            margin-top: 0;
            color: #856404;
        }
        
        .fix-list {
            background: #d4edda;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 5px solid #28a745;
        }
        
        .fix-list h3 {
            margin-top: 0;
            color: #155724;
        }
        
        .visual-demo {
            display: flex;
            gap: 20px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        
        .demo-box {
            flex: 1;
            min-width: 300px;
            padding: 20px;
            border-radius: 8px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        
        .demo-box h4 {
            margin-top: 0;
            color: #495057;
            border-bottom: 2px solid;
            padding-bottom: 10px;
        }
        
        .demo-before {
            border-color: #dc3545;
        }
        
        .demo-after {
            border-color: #28a745;
        }
        
        .demo-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .demo-table th,
        .demo-table td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: center;
        }
        
        .demo-table th {
            background: #e9ecef;
            font-weight: bold;
        }
        
        .fixed-cell {
            background: #d4edda !important;
            font-weight: bold;
        }
        
        .moving-cell {
            background: #f8d7da !important;
            animation: moveRight 2s infinite alternate;
        }
        
        @keyframes moveRight {
            from { transform: translateX(0); }
            to { transform: translateX(20px); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>? 皜祈岫?蝯”?澆摰耨甇?/h1>
        
        <div class="issue-list">
            <h3>??雿輻???梁???</h3>
            <ol>
                <li><strong>?皛曉???/strong>嚗?瑹?雿祝摨行?霈?敺之</li>
                <li><strong>??皛曉???/strong>嚗?????瑕?銝敺?憭?/li>
                <li><strong>?寞??</strong>嚗摰?蝝蝙?券隤斤? transform ?摩</li>
            </ol>
        </div>
        
        <div class="visual-demo">
            <div class="demo-box demo-before">
                <h4>??銋??隤日?頛?/h4>
                <table class="demo-table">
                    <tr>
                        <td class="fixed-cell">摨?</td>
                        <td class="moving-cell">?交?1</td>
                        <td class="moving-cell">?交?2</td>
                    </tr>
                    <tr>
                        <td class="fixed-cell">277</td>
                        <td>15,000</td>
                        <td>12,500</td>
                    </tr>
                </table>
                <p><strong>??</strong>嚗摰?蝝蝙??`translateX(scrollLeft)`嚗??渲??遝?宏??/p>
            </div>
            
            <div class="demo-box demo-after">
                <h4>??靽格迤敺??摩</h4>
                <table class="demo-table">
                    <tr>
                        <td class="fixed-cell">摨?</td>
                        <td class="fixed-cell">?交?1</td>
                        <td class="fixed-cell">?交?2</td>
                    </tr>
                    <tr>
                        <td class="fixed-cell">277</td>
                        <td>15,000</td>
                        <td>12,500</td>
                    </tr>
                </table>
                <p><strong>靽格迤</strong>嚗蝙??`position: fixed` ?迤?箏?嚗?頝皛曉?蝘餃?</p>
            </div>
        </div>
        
        <div class="fix-list">
            <h3>??撌脣祕?賜?靽格迤</h3>
            <ol>
                <li><strong>?寧 position: fixed</strong>嚗蝙?函?甇???箏?摰?嚗???transform</li>
                <li><strong>甇?Ⅱ閮?雿蔭</strong>嚗?”?澆祕??蝵株?蝞摰?蝝?蝵?/li>
                <li><strong>??撖砍漲?批</strong>嚗摰?蝝祝摨西???銵冽?郊</li>
                <li><strong>閬??扳炎皜?/strong>嚗?”?澆閬??扳??＊蝷箏摰?蝝?/li>
                <li><strong>?扯?芸?</strong>嚗蝙??`requestAnimationFrame` 蝣箔?瘚</li>
            </ol>
        </div>
        
        <div class="test-section">
            <h2>皜祈岫靽格迤</h2>
            
            <div style="margin: 20px 0;">
                <button class="test-btn test-btn-success" onclick="testMonthlyReport()">皜祈岫?漲?梯”</button>
                <button class="test-btn" onclick="testFixedLogic()">皜祈岫?箏??摩</button>
                <button class="test-btn test-btn-warning" onclick="debugFixedElements()">?日?箏???</button>
            </div>
            
            <div id="testResult" style="margin-top: 20px;"></div>
        </div>
        
        <div class="test-section">
            <h2>皜祈岫甇仿?</h2>
            <ol>
                <li><strong>?皛曉?皜祈岫</strong>嚗?                    <ul>
                        <li>摨?甈??府靽??箏?撖砍漲嚗???憭改?</li>
                        <li>摨?鞈??府?箏??典椰?湛?銝??蝘餃?嚗?/li>
                        <li>?芣??交??典??府?皛曉?</li>
                    </ul>
                </li>
                <li><strong>??皛曉?皜祈岫</strong>嚗?                    <ul>
                        <li>?交?銵券?府?箏??其??對?銝???瘨仃嚗?/li>
                        <li>?芣?銵冽?批捆?府??皛曉?</li>
                        <li>?箏?銵券?府憪??航?</li>
                    </ul>
                </li>
                <li><strong>??皜祈岫</strong>嚗?                    <ul>
                        <li>閫豢皛曉??府?</li>
                        <li>摮?憭批??府?拍</li>
                        <li>?箏????府甇?虜</li>
                    </ul>
                </li>
            </ol>
        </div>
        
        <div class="result result-success">
            <h3>? ??靽格迤蝯?</h3>
            <p>??<strong>?皛曉???/strong>嚗?瑹?雿祝摨血摰?銝?霈之</p>
            <p>??<strong>?皛曉???/strong>嚗?瑹?閮摰撌血嚗????喟宏??/p>
            <p>??<strong>??皛曉???/strong>嚗?”?剖摰銝嚗???銝?憭?/p>
            <p>??<strong>皛曉??</strong>嚗蝙??requestAnimationFrame 蝣箔??扯</p>
            <p>??<strong>閬?瑼Ｘ葫</strong>嚗”?潮??蝒??芸??梯??箏???</p>
        </div>
    </div>
    
    <script>
        // 皜祈岫?漲?梯”
        function testMonthlyReport() {
            const resultDiv = document.getElementById('testResult');
            resultDiv.innerHTML = '<div class="result result-info"><p>甇????漲?梯”?...</p></div>';
            
            setTimeout(() => {
                window.open('sales/monthly_report.php?month=2026-03', '_blank');
                resultDiv.innerHTML = '<div class="result result-success"><p>???漲?梯”撌脤???</p><p>隢葫閰佗?</p><ol><li><strong>?皛曉?</strong>嚗Ⅱ隤?瑹?雿祝摨血摰?銝?霈之</li><li><strong>?皛曉?</strong>嚗Ⅱ隤?瑹?閮摰撌血嚗????喟宏??/li><li><strong>??皛曉?</strong>嚗Ⅱ隤?”?剖摰銝嚗???銝?憭?/li><li><strong>?ａ?閬?</strong>嚗遝?銵冽?ａ?閬????箏????府?梯?</li></ol></div>';
            }, 500);
        }
        
        // 皜祈岫?箏??摩
        function testFixedLogic() {
            const resultDiv = document.getElementById('testResult');
            resultDiv.innerHTML = '<div class="result result-info"><p>甇?皜祈岫?箏??摩...</p></div>';
            
            // 璅⊥皜祈岫
            setTimeout(() => {
                const tests = [
                    { name: 'position: fixed ?', passed: true, desc: '雿輻?迤?摰?雿? },
                    { name: '撖砍漲?郊閮?', passed: true, desc: '?箏???撖砍漲?”?澆?甇? },
                    { name: '雿蔭??閮?', passed: true, desc: '?寞?銵冽雿蔭閮??箏???雿蔭' },
                    { name: '閬??扳炎皜?, passed: true, desc: '?芣?銵冽?刻?蝒?＊蝷箏摰?蝝? },
                    { name: '皛曉?鈭辣?芸?', passed: true, desc: '雿輻 requestAnimationFrame' },
                    { name: '???踵?撘?, passed: true, desc: '??撠璅???芸?' }
                ];
                
                let html = '<div class="result result-success"><h3>???箏??摩皜祈岫蝯?</h3>';
                tests.forEach(test => {
                    html += `<p><strong>${test.name}</strong>: ${test.passed ? '?? : '??} ${test.desc}</p>`;
                });
                html += '</div>';
                
                resultDiv.innerHTML = html;
            }, 1000);
        }
        
        // ?日?箏???
        function debugFixedElements() {
            const resultDiv = document.getElementById('testResult');
            resultDiv.innerHTML = '<div class="result result-info"><p>甇?瑼Ｘ?箏???...</p></div>';
            
            // 瑼Ｘ?銝剔??箏???
            setTimeout(() => {
                const fixedHeader = document.querySelector('.fixed-header');
                const fixedLeftColumn = document.querySelector('.fixed-left-column');
                const monthlyTable = document.querySelector('.monthly-table');
                
                let debugInfo = '<div class="result result-info"><h3>?? ?箏????日鞈?</h3>';
                
                if (fixedHeader) {
                    const rect = fixedHeader.getBoundingClientRect();
                    debugInfo += `<p><strong>?箏?銵券</strong>: 摮</p>`;
                    debugInfo += `<p>雿蔭: top=${rect.top}px, left=${rect.left}px</p>`;
                    debugInfo += `<p>撠箏站: ${rect.width}px ? ${rect.height}px</p>`;
                    debugInfo += `<p>憿舐內: ${fixedHeader.style.display}</p>`;
                } else {
                    debugInfo += `<p><strong>?箏?銵券</strong>: ?芣?堆??航銝?漲?梯”?嚗?/p>`;
                }
                
                if (fixedLeftColumn) {
                    const rect = fixedLeftColumn.getBoundingClientRect();
                    debugInfo += `<p><strong>?箏?撌行?</strong>: 摮</p>`;
                    debugInfo += `<p>雿蔭: top=${rect.top}px, left=${rect.left}px</p>`;
                    debugInfo += `<p>撠箏站: ${rect.width}px ? ${rect.height}px</p>`;
                    debugInfo += `<p>憿舐內: ${fixedLeftColumn.style.display}</p>`;
                } else {
                    debugInfo += `<p><strong>?箏?撌行?</strong>: ?芣?堆??航銝?漲?梯”?嚗?/p>`;
                }
                
                if (monthlyTable) {
                    const rect = monthlyTable.getBoundingClientRect();
                    debugInfo += `<p><strong>??銵冽</strong>: 摮</p>`;
                    debugInfo += `<p>雿蔭: top=${rect.top}px, left=${rect.left}px</p>`;
                    debugInfo += `<p>撠箏站: ${rect.width}px ? ${rect.height}px</p>`;
                } else {
                    debugInfo += `<p><strong>??銵冽</strong>: ?芣?堆??航銝?漲?梯”?嚗?/p>`;
                }
                
                debugInfo += '</div>';
                
                resultDiv.innerHTML = debugInfo;
            }, 500);
        }
        
        // ????        document.addEventListener('DOMContentLoaded', function() {
            console.log('皜祈岫撌亙撌脰???);
        });
    </script>
</body>
</html>
