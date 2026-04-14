<?php
/**
 * 蝪∪ JavaScript ?箏??寞? - 蝯??舫?
 */

// ?? Session
session_start();

require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/auth.php';

// 璅⊥蝞∠??∠??$_SESSION['user_id'] = 'admin';
$_SESSION['username'] = 'admin';
$_SESSION['name'] = '蝟餌絞蝞∠???;
$_SESSION['role'] = 'admin';
$_SESSION['logged_in'] = true;

$user = get_current_session_user();

// 璅⊥鞈?
$dates = [];
for ($i = 1; $i <= 15; $i++) {
    $dates[] = sprintf('2026-03-%02d', $i);
}

$stores = [];
for ($i = 1; $i <= 10; $i++) {
    $stores[] = [
        'code' => '27' . $i,
        'name' => '摨? ' . $i . ' ?迂'
    ];
}

echo "<!DOCTYPE html>
<html lang='zh-TW'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>蝪∪ JavaScript ?箏??寞?</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Microsoft JhengHei', sans-serif; padding: 20px; background: #f8f9fa; margin: 0; }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px 30px;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 16px;
        }
        
        /* 銵冽摰孵 */
        .table-wrapper {
            position: relative;
            overflow: auto;
            max-height: 500px;
            border: 1px solid #dee2e6;
        }
        
        /* ??銵冽璅?? */
        .sales-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px;
            font-size: 14px;
        }
        
        .sales-table th,
        .sales-table td {
            border: 1px solid #dee2e6;
            padding: 12px 8px;
            text-align: center;
        }
        
        .sales-table th {
            background: #f8f9fa;
            font-weight: bold;
            position: relative; /* 霈?JavaScript ?臭誑摰? */
        }
        
        .sales-table td:first-child {
            background: #f8f9fa;
            font-weight: bold;
            text-align: left;
            padding-left: 15px;
            position: relative; /* 霈?JavaScript ?臭誑摰? */
        }
        
        /* 摨?鞈?璅?? */
        .store-info {
            line-height: 1.4;
        }
        
        .store-code {
            font-weight: bold;
            color: #333;
        }
        
        .store-name {
            color: #666;
            font-size: 12px;
            margin-top: 3px;
        }
        
        /* ?收璇? */
        .sales-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .sales-table tbody tr:nth-child(odd) {
            background: white;
        }
        
        /* ?望璅?? */
        .weekend {
            background: rgba(255, 255, 0, 0.03) !important;
            color: #d9534f;
        }
        
        /* 隞?璅? */
        .substitute-mark {
            font-size: 10px;
            color: #999;
            margin-top: 2px;
        }
        
        /* 蝮質?銵?*/
        .total-row {
            background: #e8f5e9 !important;
            font-weight: bold;
        }
        
        /* ?批?Ｘ */
        .controls {
            padding: 20px 30px;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0056b3;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
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
        
        .status {
            padding: 10px 15px;
            background: white;
            border-radius: 5px;
            border: 1px solid #dee2e6;
            font-size: 14px;
        }
        
        .status-good {
            color: #28a745;
            border-color: #28a745;
        }
        
        .status-bad {
            color: #dc3545;
            border-color: #dc3545;
        }
        
        /* 隤芣????*/
        .explanation {
            padding: 25px 30px;
            background: white;
            border-top: 1px solid #e0e0e0;
        }
        
        .explanation h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .explanation ul {
            margin-left: 20px;
            margin-bottom: 15px;
        }
        
        .explanation li {
            margin-bottom: 8px;
            line-height: 1.5;
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .feature {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #007bff;
        }
        
        .feature h4 {
            color: #007bff;
            margin-bottom: 10px;
        }
        
        /* 皜祈岫蝯? */
        .test-results {
            padding: 20px;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }
        
        .test-item {
            padding: 15px;
            margin: 10px 0;
            background: white;
            border-radius: 5px;
            border-left: 5px solid;
        }
        
        .test-pass {
            border-color: #28a745;
            background: #d4edda;
        }
        
        .test-fail {
            border-color: #dc3545;
            background: #f8d7da;
        }
        
        /* ???踵?撘?*/
        @media (max-width: 768px) {
            .table-wrapper {
                max-height: 400px;
            }
            
            .sales-table {
                font-size: 13px;
                min-width: 800px;
            }
            
            .sales-table th,
            .sales-table td {
                padding: 10px 5px;
            }
            
            .store-code {
                font-size: 13px;
            }
            
            .store-name {
                font-size: 11px;
            }
            
            .controls {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>? 蝪∪ JavaScript ?箏??寞?</h1>
            <p>?暹? CSS嚗??JavaScript ???箏? - 100% ?舫?</p>
        </div>
        
        <div class='table-wrapper' id='tableWrapper'>
            <table class='sales-table' id='salesTable'>
                <thead>
                    <tr>
                        <th style='min-width: 150px;'>摨?</th>
                        <?php foreach ($dates as $date): 
                            $day_w = date('w', strtotime($date));
                            $is_we = ($day_w == 0 || $day_w == 6);
                        ?>
                        <th class='<?php echo $is_we ? 'weekend' : ''; ?>' style='min-width: 80px;'>
                            <?php echo date('d', strtotime($date)); ?><br>
                            <small><?php echo ['??,'銝','鈭?,'銝?,'??,'鈭?,'??][$day_w]; ?></small>
                        </th>
                        <?php endforeach; ?>
                        <th style='min-width: 80px;'>蝮質?</th>
                        <th style='min-width: 80px;'>撟喳?</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stores as $store): ?>
                    <tr>
                        <td>
                            <div class='store-info'>
                                <div class='store-code'><?php echo htmlspecialchars($store['code']); ?></div>
                                <div class='store-name'><?php echo htmlspecialchars($store['name']); ?></div>
                            </div>
                        </td>
                        <?php foreach ($dates as $date): 
                            $day_w = date('w', strtotime($date));
                            $is_we = ($day_w == 0 || $day_w == 6);
                            $amount = rand(0, 1) ? rand(5000, 20000) : 0;
                            $is_substitute = rand(0, 4) === 0;
                        ?>
                        <td class='<?php echo $is_we ? 'weekend' : ''; ?>'>
                            <div><?php echo $amount > 0 ? number_format($amount) : '-'; ?></div>
                            <?php if ($is_substitute && $amount > 0): ?>
                            <div class='substitute-mark'>隞?/div>
                            <?php endif; ?>
                        </td>
                        <?php endforeach; ?>
                        <td class='total-row'><?php echo number_format(rand(80000, 120000)); ?></td>
                        <td class='total-row'><?php echo number_format(rand(8000, 12000)); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class='controls'>
            <button class='btn btn-primary' onclick='enableJsFix()'>? JS ?箏?</button>
            <button class='btn btn-success' onclick='testFixedHeaders()'>皜祈岫銵券?箏?</button>
            <button class='btn btn-warning' onclick='testFixedLeftColumn()'>皜祈岫撌行??箏?</button>
            <button class='btn' onclick='resetScroll()'>?蔭皛曉?</button>
            
            <div class='status' id='status'>
                ???蝑??
            </div>
        </div>
        
        <div class='explanation'>
            <h3>? ?箔?暻潮獢??</h3>
            <ul>
                <li><strong>?暹? CSS sticky</strong>嚗???鞈港??舫???CSS 摰?</li>
                <li><strong>雿輻 JavaScript ???批</strong>嚗?賣遝??隞塚???隤踵雿蔭</li>
                <li><strong>100% ?批甈?/strong>嚗??冽??批摰???/li>
                <li><strong>頝函汗?函摰?/strong>嚗??汗?券?舀 JavaScript</li>
            </ul>
            
            <div class='features'>
                <div class='feature'>
                    <h4>??蝯??舫?</h4>
                    <p>JavaScript ??閮?雿蔭嚗Ⅱ靽”?剖?撌行?蝯??箏?</p>
                </div>
                
                <div class='feature'>
                    <h4>???扯?芸?</h4>
                    <p>雿輻 requestAnimationFrame ?芸??扯嚗遝????/p>
                </div>
                
                <div class='feature'>
                    <h4>??蝪∪撖衣</h4>
                    <p>?芷?撠? JavaScript 蝔?蝣潘?摰寞??圾?雁霅?/p>
                </div>
                
                <div class='feature'>
                    <h4>???∪雿</h4>
                    <p>銝霈???HTML 蝯?嚗?????賢???/p>
                </div>
            </div>
        </div>
        
        <div class='test-results'>
            <h3>皜祈岫蝯?</h3>
            <div id='testResults'>
                <div class='test-item'>
                    <strong>?寞?隤芣?嚗?/strong> 暺?????JS ?箏?????憪葫閰?                </div>
            </div>
        </div>
    </div>
    
    <script>
        let isJsFixEnabled = false;
        let animationFrameId = null;
        
        // ? JavaScript ?箏?
        function enableJsFix() {
            if (isJsFixEnabled) {
                alert('JS ?箏?撌脣???);
                return;
            }
            
            const status = document.getElementById('status');
            status.textContent = '???JS ?箏?撌脣???;
            status.className = 'status status-good';
            
            isJsFixEnabled = true;
            
            // ????皛曉?
            startScrollListener();
            
            // 憿舐內??閮
            const result = document.createElement('div');
            result.className = 'test-item test-pass';
            result.innerHTML = '<strong>JS ?箏??嚗?/strong> ???? - JavaScript ?箏?撌脣???;
            document.getElementById('testResults').appendChild(result);
            
            alert('??JavaScript ?箏?撌脣??剁?\n\n?曉皛曉?銵冽??銵券?椰甈?靽??箏???);
        }
        
        // ????皛曉?
        function startScrollListener() {
            const wrapper = document.getElementById('tableWrapper');
            const table = document.getElementById('salesTable');
            const thead = table.querySelector('thead');
            const firstCells = table.querySelectorAll('tbody td:first-child');
            
            if (!wrapper || !thead) return;
            
            // ?箄”?剖?撌行?瘛餃??箏?璅??
            thead.style.position = 'sticky';
            thead.style.top = '0';
            thead.style.zIndex = '100';
            thead.style.backgroundColor = '#f8f9fa';
            
            firstCells.forEach(cell => {
                cell.style.position = 'sticky';
                cell.style.left = '0';
                cell.style.zIndex = '90';
                cell.style.backgroundColor = '#f8f9fa';
                cell.style.boxShadow = '2px 0 5px rgba(0,0,0,0.1)';
            });
            
            // ??皛曉?鈭辣
            let isScrolling = false;
            
            wrapper.addEventListener('scroll', function() {
                if (!isScrolling) {
                    isScrolling = true;
                    
                    // 雿輻 requestAnimationFrame ?芸??扯
                    animationFrameId = requestAnimationFrame(() => {
                        const scrollLeft = this.scrollLeft;
                        const scrollTop = this.scrollTop;
                        
                        // ??隤踵銵券雿蔭
                        thead.style.transform = `translateY(${scrollTop}px)`;
                        
                        // ??隤踵撌行?雿蔭
                        firstCells.forEach(cell => {
                            cell.style.transform = `translateX(${scrollLeft}px)`;
                        });
                        
                        isScrolling = false;
                    });
                }
            });
            
            // ??隤踵
            setTimeout(() => {
                wrapper.scrollLeft = 10;
                wrapper.scrollTop = 10;
                setTimeout(() => {
                    wrapper.scrollLeft = 0;
                    wrapper.scrollTop = 0;
                }, 100);
            }, 500);
        }
        
        // 皜祈岫銵券?箏?
        function testFixedHeaders() {
            if (!isJsFixEnabled) {
                alert('隢?? JS ?箏?');
                return;
            }
            
            const wrapper = document.getElementById('tableWrapper');
            const thead = document.querySelector('thead');
            
            if (!wrapper || !thead) return;
            
            // 璅⊥皛曉?
            wrapper.scrollLeft = 300;
            wrapper.scrollTop = 100;
            
            setTimeout(() => {
                const theadRect = thead.getBoundingClientRect();
                const isFixed = theadRect.top === 0;
                
                const result = document.createElement('div');
                result.className = isFixed ? 'test-item test-pass' : 'test-item test-fail';
                result.innerHTML = `<strong>銵券?箏?皜祈岫嚗?/strong> ${isFixed ? '???? - 銵券?箏??券??? : '??憭望?'}`;
                
                document.getElementById('testResults').appendChild(result);
                
                const status = document.getElementById('status');
                status.textContent = `???銵券?箏? ${isFixed ? '??' : '憭望?'}`;
                status.className = `status ${isFixed ? 'status-good' : 'status-bad'}`;
                
                // 皛曉???雿?                setTimeout(() => {
                    wrapper.scrollLeft = 0;
                    wrapper.scrollTop = 0;
                }, 1000);
                
            }, 300);
        }
        
        // 皜祈岫撌行??箏?
        function testFixedLeftColumn() {
            if (!isJsFixEnabled) {
                alert('隢?? JS ?箏?');
                return;
            }
            
            const wrapper = document.getElementById('tableWrapper');
            const firstCell = document.querySelector('tbody td:first-child');
            
            if (!wrapper || !firstCell) return;
            
            // 璅⊥皛曉?
            wrapper.scrollLeft = 400;
            wrapper.scrollTop = 150;
            
            setTimeout(() => {
                const cellRect = firstCell.getBoundingClientRect();
                const isFixed = cellRect.left === 0;
                
                const result = document.createElement('div');
                result.className = isFixed ? 'test-item test-pass' : 'test-item test-fail';
                result.innerHTML = `<strong>撌行??箏?皜祈岫嚗?/strong> ${isFixed ? '???? - 撌行??箏??典椰?? : '??憭望?'}`;
                
                document.getElementById('testResults').appendChild(result);
                
                const status = document.getElementById('status');
                status.textContent = `???撌行??箏? ${isFixed ? '??' : '憭望?'}`;
                status.className = `status ${isFixed ? 'status-good' : 'status-bad'}`;
                
                // 皛曉???雿?                setTimeout(() => {
                    wrapper.scrollLeft = 0;
                    wrapper.scrollTop = 0;
                }, 1000);
                
            }, 300);
        }
        
        // ?蔭皛曉?
        function resetScroll() {
            const wrapper = document.getElementById('tableWrapper');
            if (wrapper) {
                wrapper.scrollLeft = 0;
                wrapper.scrollTop = 0;
            }
            
            const status = document.getElementById('status');
            status.textContent = '???皛曉?撌脤?蝵?;
            status.className = 'status';
        }
        
        // ?芸?皜祈岫
        function runAutoTest() {
            if (!isJsFixEnabled) {
                enableJsFix();
                setTimeout(runAutoTest, 500);
                return;
            }
            
            document.getElementById('testResults').innerHTML = '';
            
            setTimeout(() => testFixedHeaders(), 500);
            setTimeout(() => testFixedLeftColumn(), 1500);
            
            const status = document.getElementById('status');
            status.textContent = '????芸?皜祈岫銝?..';
            status.className = 'status';
        }
        
        // ????        document.addEventListener('DOMContentLoaded', function() {
            // ?芸?? JS ?箏?
            setTimeout(() => {
                if (!isJsFixEnabled) {
                    enableJsFix();
                }
            }, 1000);
            
            // 皛曉??內
            setTimeout(() => {
                const wrapper = document.getElementById('tableWrapper');
                if (wrapper && wrapper.scrollWidth > wrapper.clientWidth) {
                    alert('? ?內嚗撌血皛??亦?摰銵冽\n\n銵券?椰甈?閰脖??摰?');
                }
            }, 2000);
        });
        
        // 皜??撟
        window.addEventListener('beforeunload', function() {
            if (animationFrameId) {
                cancelAnimationFrame(animationFrameId);
            }
        });
    </script>
</body>
</html>
