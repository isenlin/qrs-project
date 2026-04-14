<?php
/**
 * 敹恍葫閰西”?澆摰??? */

// ?? Session
session_start();

require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/auth.php';

echo "<!DOCTYPE html>
<html lang='zh-TW'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>敹恍葫閰西”?澆摰???/title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .test-container { max-width: 800px; margin: 0 auto; }
        .test-result { padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        
        /* 皜祈岫銵冽璅?? */
        .test-table-container {
            overflow-x: auto;
            overflow-y: visible;
            position: relative;
            border: 2px solid #007bff;
            border-radius: 8px;
            margin: 20px 0;
            max-height: 400px;
        }
        
        .test-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
            position: relative;
        }
        
        .test-table thead {
            position: sticky;
            top: 0;
            z-index: 20;
        }
        
        .test-table thead th {
            background: #f8f9fa;
            position: sticky;
            top: 0;
            border: 1px solid #dee2e6;
            padding: 10px;
            font-weight: bold;
        }
        
        .test-table tbody td:first-child {
            position: sticky;
            left: 0;
            background: #f8f9fa;
            z-index: 15;
            border: 1px solid #dee2e6;
            padding: 10px;
            font-weight: bold;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        
        .test-table tbody td {
            border: 1px solid #dee2e6;
            padding: 10px;
            text-align: center;
        }
        
        .test-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .test-table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }
        
        /* ??皜祈岫璅?? */
        @media (max-width: 768px) {
            .test-table-container {
                border-color: #28a745;
            }
            
            .test-table {
                min-width: 600px;
                font-size: 14px;
            }
            
            .test-table thead th,
            .test-table tbody td {
                padding: 8px 5px;
            }
        }
        
        .scroll-hint {
            text-align: center;
            padding: 10px;
            background: #f8f9fa;
            color: #6c757d;
            border-bottom: 1px solid #dee2e6;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class='test-container'>
        <h1>敹恍葫閰西”?澆摰???/h1>
        
        <div class='test-result info'>
            <h3>皜祈岫隤芣?</h3>
            <p>?葫閰阡??Ｘ芋?祆?摨行平蝮曉銵函?銵冽蝯?嚗葫閰西”?剖?撌行??箏????/p>
            <p><strong>??嚗?/strong>摨??摰?雿???摰?/p>
            <p><strong>閫?捱?寞?嚗?/strong>靽格迤 CSS 霈”?剖?????雿?賢摰?/p>
        </div>
        
        <div class='test-result warning'>
            <h3>皜祈岫?寞?</h3>
            <ol>
                <li>?其??寞葫閰西”?潔葉璈怠?皛曉?</li>
                <li>蝣箄?銵券嚗??靽??箏??冽?銝</li>
                <li>蝣箄?撌行?嚗?瑹?靽??箏??典椰??/li>
                <li>雿輻???芋?祆?璈?蝵格葫閰?/li>
            </ol>
        </div>
        
        <h2>皜祈岫銵冽</h2>
        <div class='scroll-hint'>? ?臬椰?單?????渲”?潘?銵券?椰甈?靽??箏?嚗?/div>
        
        <div class='test-table-container'>
            <table class='test-table'>
                <thead>
                    <tr>
                        <th style='min-width: 80px;'>隞??</th>
                        <th style='min-width: 120px;'>摨??迂</th>
                        <th>3/1<br><small>銝</small></th>
                        <th>3/2<br><small>鈭?/small></th>
                        <th>3/3<br><small>銝?/small></th>
                        <th>3/4<br><small>??/small></th>
                        <th>3/5<br><small>鈭?/small></th>
                        <th>3/6<br><small>??/small></th>
                        <th>3/7<br><small>??/small></th>
                        <th>3/8<br><small>銝</small></th>
                        <th>3/9<br><small>鈭?/small></th>
                        <th>3/10<br><small>銝?/small></th>
                        <th>3/11<br><small>??/small></th>
                        <th>3/12<br><small>鈭?/small></th>
                        <th>3/13<br><small>??/small></th>
                        <th>3/14<br><small>??/small></th>
                        <th>蝮質?</th>
                        <th>撟喳?</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>277</td>
                        <td>?啣??摨?/td>
                        <td>15,000</td>
                        <td>18,000</td>
                        <td>12,000</td>
                        <td>16,500</td>
                        <td>19,000</td>
                        <td>8,000</td>
                        <td>7,500</td>
                        <td>17,000</td>
                        <td>14,500</td>
                        <td>13,000</td>
                        <td>18,500</td>
                        <td>20,000</td>
                        <td>9,000</td>
                        <td>8,500</td>
                        <td>196,500</td>
                        <td>14,036</td>
                    </tr>
                    <tr>
                        <td>282</td>
                        <td>?唬葉??</td>
                        <td>12,000</td>
                        <td>14,500</td>
                        <td>11,000</td>
                        <td>13,500</td>
                        <td>16,000</td>
                        <td>6,500</td>
                        <td>5,800</td>
                        <td>15,000</td>
                        <td>12,800</td>
                        <td>11,500</td>
                        <td>14,200</td>
                        <td>17,500</td>
                        <td>7,200</td>
                        <td>6,800</td>
                        <td>164,300</td>
                        <td>11,736</td>
                    </tr>
                    <tr>
                        <td>290</td>
                        <td>擃???</td>
                        <td>10,500</td>
                        <td>12,000</td>
                        <td>9,800</td>
                        <td>11,200</td>
                        <td>14,500</td>
                        <td>5,500</td>
                        <td>4,900</td>
                        <td>13,000</td>
                        <td>10,800</td>
                        <td>9,500</td>
                        <td>12,500</td>
                        <td>15,800</td>
                        <td>6,000</td>
                        <td>5,500</td>
                        <td>141,500</td>
                        <td>10,107</td>
                    </tr>
                    <tr>
                        <td>291</td>
                        <td>?啣???</td>
                        <td>11,200</td>
                        <td>13,500</td>
                        <td>10,500</td>
                        <td>12,800</td>
                        <td>15,200</td>
                        <td>7,000</td>
                        <td>6,200</td>
                        <td>14,500</td>
                        <td>11,900</td>
                        <td>10,200</td>
                        <td>13,800</td>
                        <td>16,500</td>
                        <td>7,500</td>
                        <td>6,800</td>
                        <td>157,500</td>
                        <td>11,250</td>
                    </tr>
                    <tr>
                        <td>295</td>
                        <td>?啁姘??</td>
                        <td>9,800</td>
                        <td>11,200</td>
                        <td>8,900</td>
                        <td>10,500</td>
                        <td>13,800</td>
                        <td>4,800</td>
                        <td>4,200</td>
                        <td>12,000</td>
                        <td>9,500</td>
                        <td>8,800</td>
                        <td>11,500</td>
                        <td>14,200</td>
                        <td>5,500</td>
                        <td>5,000</td>
                        <td>129,700</td>
                        <td>9,264</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class='test-result'>
            <h3>皜祈岫蝯??</h3>
            <form id='testForm'>
                <div style='margin: 10px 0;'>
                    <label>
                        <input type='checkbox' name='header_fixed' value='1'>
                        銵券嚗??皛曉????摰?                    </label>
                </div>
                
                <div style='margin: 10px 0;'>
                    <label>
                        <input type='checkbox' name='left_column_fixed' value='1'>
                        撌行?嚗?瑹?皛曉????摰?                    </label>
                </div>
                
                <div style='margin: 10px 0;'>
                    <label>
                        <input type='checkbox' name='mobile_works' value='1'>
                        ??銝??賣迤撣?                    </label>
                </div>
                
                <div style='margin: 10px 0;'>
                    <label>???憿?</label><br>
                    <textarea name='issues' rows='3' style='width: 100%; max-width: 500px; padding: 8px;' placeholder='?膩???憿?..'></textarea>
                </div>
                
                <button type='button' onclick='submitTest()' style='padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;'>?漱皜祈岫蝯?</button>
            </form>
        </div>
        
        <div class='test-result info'>
            <h3>CSS 靽格迤??</h3>
            <pre><code>/* 1. 銵券?箏? */
.monthly-table thead {
    position: sticky;
    top: 0;
    z-index: 20;
}

.monthly-table thead th {
    position: sticky;
    top: 0;
    background: #f8f9fa;
}

/* 2. 撌行??箏? */
.monthly-table tbody td:first-child {
    position: sticky;
    left: 0;
    background: #f8f9fa;
    z-index: 15;
}

/* 3. 摰孵閮剖? */
.table-container {
    overflow-x: auto;
    overflow-y: visible; /* ??嚗?*/
    position: relative;
}

.monthly-table {
    position: relative; /* ??嚗?*/
}</code></pre>
        </div>
        
        <div class='test-result'>
            <h3>撖阡?皜祈岫???</h3>
            <ul>
                <li><a href='sales/monthly_report.php' target='_blank'>?漲璆剔蜀?梯”嚗祕???ｇ?</a></li>
                <li><a href='test_mobile_table_fix.php' target='_blank'>摰?皜祈岫</a></li>
            </ul>
        </div>
    </div>
    
    <script>
        function submitTest() {
            const form = document.getElementById('testForm');
            const formData = new FormData(form);
            const results = {};
            
            for (let [key, value] of formData.entries()) {
                results[key] = value;
            }
            
            let message = '皜祈岫蝯?嚗n';
            message += results.header_fixed ? '??銵券?箏?甇?虜\n' : '??銵券?箏???憿n';
            message += results.left_column_fixed ? '??撌行??箏?甇?虜\n' : '??撌行??箏???憿n';
            message += results.mobile_works ? '?????甇?虜\n' : '???????憿n';
            message += results.issues ? `???膩嚗?{results.issues}\n` : '';
            
            alert(message);
            
            // 憿舐內蝯?
            const resultDiv = document.createElement('div');
            resultDiv.className = 'test-result success';
            resultDiv.innerHTML = `<h3>皜祈岫蝯?撌脰???/h3><pre>${message}</pre>`;
            document.querySelector('.test-container').appendChild(resultDiv);
        }
        
        // 瑼Ｘ葫?臬?箸?璈身??        function isMobileDevice() {
            return window.innerWidth <= 768;
        }
        
        // 憿舐內閮剖?鞈?
        document.addEventListener('DOMContentLoaded', function() {
            const deviceInfo = `閮剖?鞈?嚗?{window.innerWidth}px ? ${window.innerHeight}px嚗?{isMobileDevice() ? '??/撟單' : '獢'}`;
            console.log(deviceInfo);
            
            // 瘛餃?閮剖?鞈?憿舐內
            const infoDiv = document.createElement('div');
            infoDiv.className = 'test-result info';
            infoDiv.innerHTML = `<p><strong>${deviceInfo}</strong></p>`;
            document.querySelector('.test-container').insertBefore(infoDiv, document.querySelector('.test-result.warning'));
        });
    </script>
</body>
</html>";
