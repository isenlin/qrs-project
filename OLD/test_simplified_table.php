<?php
/**
 * 皜祈岫蝪∪?銵冽蝯?
 */

// ?? Session
session_start();

require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/auth.php';

echo "<!DOCTYPE html>
<html lang='zh-TW'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>皜祈岫蝪∪?銵冽蝯?</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background: #f8f9fa; }
        .container { max-width: 1200px; margin: 0 auto; }
        
        .test-section {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        h2 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        
        /* 蝪∪?銵冽璅?? */
        .simple-table-container {
            overflow-x: auto;
            overflow-y: visible;
            position: relative;
            border: 2px solid #28a745;
            border-radius: 8px;
            margin: 20px 0;
            max-height: 500px;
            background: white;
        }
        
        .simple-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
            position: relative;
            font-size: 14px;
        }
        
        /* 銵券?箏? */
        .simple-table thead {
            position: -webkit-sticky;
            position: sticky;
            top: 0;
            z-index: 20;
            transform: translateZ(0);
        }
        
        .simple-table thead th {
            position: -webkit-sticky;
            position: sticky;
            top: 0;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 12px 8px;
            font-weight: bold;
            white-space: nowrap;
            transform: translateZ(0);
        }
        
        /* 撌行??箏? - ?芣?銝甈? */
        .simple-table tbody td:first-child {
            position: -webkit-sticky;
            position: sticky;
            left: 0;
            background: #f8f9fa;
            z-index: 15;
            border: 1px solid #dee2e6;
            padding: 12px 8px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            transform: translateZ(0);
            min-width: 150px;
            text-align: left;
        }
        
        .simple-table thead th:first-child {
            position: -webkit-sticky;
            position: sticky;
            left: 0;
            z-index: 25;
            background: #f8f9fa;
            transform: translateZ(0);
        }
        
        .simple-table tbody td {
            border: 1px solid #dee2e6;
            padding: 12px 8px;
            text-align: center;
            background: white;
            min-width: 70px;
        }
        
        .simple-table tbody tr:nth-child(even) td:not(:first-child) {
            background-color: #f8f9fa;
        }
        
        .simple-table tbody tr:nth-child(odd) td:not(:first-child) {
            background-color: #ffffff;
        }
        
        /* 摨?鞈?璅?? */
        .store-info {
            line-height: 1.4;
        }
        
        .store-code {
            font-weight: bold;
            color: #333;
            font-size: 14px;
        }
        
        .store-name {
            color: #666;
            font-size: 12px;
            margin-top: 2px;
        }
        
        /* 皜祈岫蝯? */
        .test-result {
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 5px solid;
        }
        
        .success { border-color: #28a745; background: #d4edda; }
        .error { border-color: #dc3545; background: #f8d7da; }
        .info { border-color: #17a2b8; background: #d1ecf1; }
        
        /* ?? */
        .test-btn {
            padding: 10px 20px;
            margin: 5px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            background: #007bff;
            color: white;
        }
        
        .scroll-hint {
            text-align: center;
            padding: 10px;
            background: #f8f9fa;
            color: #6c757d;
            border-bottom: 1px solid #dee2e6;
            font-size: 14px;
        }
        
        /* ???芸? */
        @media (max-width: 768px) {
            .simple-table {
                font-size: 13px;
                min-width: 700px;
            }
            
            .simple-table thead th,
            .simple-table tbody td {
                padding: 8px 5px;
            }
            
            .simple-table tbody td:first-child {
                min-width: 120px;
            }
            
            .simple-table thead th:first-child {
                min-width: 120px;
            }
            
            .store-code { font-size: 13px; }
            .store-name { font-size: 11px; }
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>?? 皜祈岫蝪∪?銵冽蝯?</h1>
        
        <div class='test-section'>
            <h2>???圾瘙箸獢?/h2>
            <div class='test-result info'>
                <h3>??嚗”?澆摰???/h3>
                <p>??甈?閬摰?隞?? + ?迂嚗?撠 CSS sticky ??誑?舫?撌乩???/p>
                
                <h3>閫?捱?寞?嚗?雿萇銝甈?/h3>
                <p>撠?瑹誨???迂?蔥?啣?銝?摮?</p>
                <ul>
                    <li><strong>蝪∪?蝯?</strong>嚗??拇??箏?霈銝甈摰?/li>
                    <li><strong>???舫???/strong>嚗SS sticky ?游捆????/li>
                    <li><strong>?孵???擃?</strong>嚗?撠偌撟喟征????/li>
                    <li><strong>靽?鞈?摰</strong>嚗誨???迂?賡＊蝷?/li>
                </ul>
            </div>
        </div>
        
        <div class='test-section'>
            <h2>蝪∪?銵冽皜祈岫</h2>
            <p>?”?澆?摨?隞????蝔勗?雿萄??甈??芣?銝甈?閬摰?/p>
            
            <div class='scroll-hint'>? ?臬椰?單?????渲”?潘?銵券?椰甈?靽??箏?嚗?/div>
            
            <div class='simple-table-container'>
                <table class='simple-table'>
                    <thead>
                        <tr>
                            <th style='min-width: 150px;'>摨?</th>
                            <th style='min-width: 70px;'>3/1<br><small>銝</small></th>
                            <th style='min-width: 70px;'>3/2<br><small>鈭?/small></th>
                            <th style='min-width: 70px;'>3/3<br><small>銝?/small></th>
                            <th style='min-width: 70px;'>3/4<br><small>??/small></th>
                            <th style='min-width: 70px;'>3/5<br><small>鈭?/small></th>
                            <th style='min-width: 70px;'>3/6<br><small>??/small></th>
                            <th style='min-width: 70px;'>3/7<br><small>??/small></th>
                            <th style='min-width: 70px;'>3/8<br><small>銝</small></th>
                            <th style='min-width: 70px;'>3/9<br><small>鈭?/small></th>
                            <th style='min-width: 70px;'>3/10<br><small>銝?/small></th>
                            <th style='min-width: 70px;'>3/11<br><small>??/small></th>
                            <th style='min-width: 70px;'>3/12<br><small>鈭?/small></th>
                            <th style='min-width: 70px;'>3/13<br><small>??/small></th>
                            <th style='min-width: 70px;'>3/14<br><small>??/small></th>
                            <th style='min-width: 80px;'>蝮質?</th>
                            <th style='min-width: 80px;'>撟喳?</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($i = 1; $i <= 8; $i++): ?>
                        <tr>
                            <td>
                                <div class='store-info'>
                                    <div class='store-code'>27<?php echo $i; ?></div>
                                    <div class='store-name'>摨? <?php echo $i; ?> ?迂</div>
                                </div>
                            </td>
                            <td><?php echo number_format(rand(10000, 20000)); ?></td>
                            <td><?php echo number_format(rand(10000, 20000)); ?></td>
                            <td><?php echo number_format(rand(10000, 20000)); ?></td>
                            <td><?php echo number_format(rand(10000, 20000)); ?></td>
                            <td><?php echo number_format(rand(10000, 20000)); ?></td>
                            <td><?php echo number_format(rand(8000, 12000)); ?></td>
                            <td><?php echo number_format(rand(7000, 11000)); ?></td>
                            <td><?php echo number_format(rand(10000, 20000)); ?></td>
                            <td><?php echo number_format(rand(10000, 20000)); ?></td>
                            <td><?php echo number_format(rand(10000, 20000)); ?></td>
                            <td><?php echo number_format(rand(10000, 20000)); ?></td>
                            <td><?php echo number_format(rand(10000, 20000)); ?></td>
                            <td><?php echo number_format(rand(8000, 12000)); ?></td>
                            <td><?php echo number_format(rand(7000, 11000)); ?></td>
                            <td><?php echo number_format(rand(150000, 250000)); ?></td>
                            <td><?php echo number_format(rand(10000, 15000)); ?></td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
            
            <div class='test-result info'>
                <h3>皜祈岫隤芣?</h3>
                <p>1. <strong>璈怠?皛曉?</strong>嚗椰?單??”??/p>
                <p>2. <strong>閫撖”??/strong>嚗?”?剜?血摰?銝</p>
                <p>3. <strong>閫撖椰甈?/strong>嚗?瑹?閮?血摰撌血</p>
                <p>4. <strong>??皜祈岫</strong>嚗蝙?冽?璈?璅⊥??鋆蔭</p>
            </div>
            
            <button class='test-btn' onclick='runTest()'>?瑁?皜祈岫</button>
        </div>
        
        <div class='test-section'>
            <h2>CSS 閮剖?</h2>
            <pre><code>/* 銵券?箏? */
.simple-table thead {
    position: -webkit-sticky;
    position: sticky;
    top: 0;
    z-index: 20;
    transform: translateZ(0);
}

.simple-table thead th {
    position: -webkit-sticky;
    position: sticky;
    top: 0;
    transform: translateZ(0);
}

/* 撌行??箏? - ?芣?銝甈? */
.simple-table tbody td:first-child {
    position: -webkit-sticky;
    position: sticky;
    left: 0;
    z-index: 15;
    transform: translateZ(0);
}

.simple-table thead th:first-child {
    position: -webkit-sticky;
    position: sticky;
    left: 0;
    z-index: 25;
    transform: translateZ(0);
}</code></pre>
            
            <div class='test-result success'>
                <h3>蝪∪??芸</h3>
                <ul>
                    <li>??<strong>皜??箏???</strong>嚗?2甈?1甈?/li>
                    <li>??<strong>???舫???/strong>嚗SS sticky ?游捆?極雿?/li>
                    <li>??<strong>?孵??扯</strong>嚗?撠汗?刻?蝞?/li>
                    <li>??<strong>?游末蝬剛風</strong>嚗?撘Ⅳ?渡陛??/li>
                </ul>
            </div>
        </div>
        
        <div class='test-section'>
            <h2>撖阡?皜祈岫???</h2>
            <div style='display: grid; grid-template-columns: 1fr 1fr; gap: 20px;'>
                <div style='text-align: center;'>
                    <h3>蝪∪?皜祈岫?</h3>
                    <p>???Ｙ?銵冽</p>
                    <p><button class='test-btn' onclick='testThisPage()'>皜祈岫甇日???/button></p>
                </div>
                
                <div style='text-align: center;'>
                    <h3>撖阡??漲?梯”</h3>
                    <p>撌脫??函陛?耨??/p>
                    <p><a href='sales/monthly_report.php' target='_blank' style='display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>皜祈岫撖阡??</a></p>
                </div>
            </div>
        </div>
        
        <div id='testResults'></div>
    </div>
    
    <script>
        function runTest() {
            const table = document.querySelector('.simple-table');
            const container = document.querySelector('.simple-table-container');
            
            if (!table || !container) return;
            
            // 璅⊥皛曉?皜祈岫
            container.scrollLeft = 300;
            container.scrollTop = 100;
            
            setTimeout(() => {
                // 瑼Ｘ?箏???
                const thead = table.querySelector('thead');
                const firstCell = table.querySelector('tbody td:first-child');
                
                const theadRect = thead.getBoundingClientRect();
                const cellRect = firstCell.getBoundingClientRect();
                
                let result = '<div class=\"test-section\">';
                result += '<h2>皜祈岫蝯?</h2>';
                result += '<div class=\"test-result ' + (theadRect.top === 0 ? 'success' : 'error') + '\">';
                result += '<h3>銵券?箏?嚗? + (theadRect.top === 0 ? '????' : '??憭望?') + '</h3>';
                result += '<p>銵券雿蔭嚗op=' + theadRect.top + 'px</p>';
                result += '</div>';
                
                result += '<div class=\"test-result ' + (cellRect.left === 0 ? 'success' : 'error') + '\">';
                result += '<h3>撌行??箏?嚗? + (cellRect.left === 0 ? '????' : '??憭望?') + '</h3>';
                result += '<p>撌行?雿蔭嚗eft=' + cellRect.left + 'px</p>';
                result += '</div>';
                
                result += '<div class=\"test-result info\">';
                result += '<h3>??皜祈岫撱箄降</h3>';
                result += '<p>1. 璈怠?皛曉?銵冽嚗?撖”?剜?血摰?/p>';
                result += '<p>2. ?皛曉?銵冽嚗?撖椰甈?血摰?/p>';
                result += '<p>3. 雿輻???芋?祆?璈葫閰?/p>';
                result += '</div>';
                
                result += '</div>';
                
                document.getElementById('testResults').innerHTML = result;
                
                // 皛曉???雿?                setTimeout(() => {
                    container.scrollLeft = 0;
                    container.scrollTop = 0;
                }, 2000);
                
            }, 500);
        }
        
        function testThisPage() {
            alert('隢??葫閰佗?\n1. 璈怠?皛曉?銵冽\n2. 閫撖”?剖?撌行??臬?箏?\n3. 雿輻??璅∪?皜祈岫嚗12 ??鋆蔭撌亙甈?');
        }
        
        // ?芸?瑼Ｘ sticky ?舀
        document.addEventListener('DOMContentLoaded', function() {
            const testEl = document.createElement('div');
            testEl.style.position = 'sticky';
            testEl.style.position = '-webkit-sticky';
            const supportsSticky = testEl.style.position.indexOf('sticky') !== -1;
            
            console.log('?汗?冽??sticky:', supportsSticky);
            
            if (!supportsSticky) {
                const warning = document.createElement('div');
                warning.className = 'test-result error';
                warning.innerHTML = '<h3>?? ?汗?刻郎??/h3><p>?函??汗?典?賭?摰?舀 CSS sticky嚗遣霅唬蝙?冽??啁? Chrome?irefox ??Safari??/p>';
                document.querySelector('.container').insertBefore(warning, document.querySelector('.test-section
