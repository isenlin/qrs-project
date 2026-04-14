<?php
/**
 * ?”?潸圾瘙箸獢?- ??喟絞雿??舫??瘜? */

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

// 璅⊥鞈?嚗??10憭抬?靽?蝪∪嚗?$dates = [];
for ($i = 1; $i <= 10; $i++) {
    $dates[] = sprintf('2026-03-%02d', $i);
}

$stores = [];
for ($i = 1; $i <= 8; $i++) {
    $stores[] = [
        'code' => '27' . $i,
        'name' => '摨? ' . $i
    ];
}

echo "<!DOCTYPE html>
<html lang='zh-TW'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>?”?潸圾瘙箸獢?/title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: sans-serif; padding: 20px; background: #f8f9fa; margin: 0; }
        
        .solution-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .solution-header {
            background: #28a745;
            color: white;
            padding: 20px;
        }
        
        .solution-header h1 {
            margin: 0;
            font-size: 24px;
        }
        
        .solution-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }
        
        /* ==================== ?”?潭敹?==================== */
        .dual-table-container {
            position: relative;
            overflow: auto;
            max-height: 500px;
            border: 1px solid #dee2e6;
        }
        
        /* ?箏?銵券銵冽 */
        .fixed-header-table {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #f8f9fa;
            margin-bottom: 0;
        }
        
        .fixed-header-table table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        
        .fixed-header-table th {
            padding: 15px 10px;
            border: 1px solid #dee2e6;
            text-align: center;
            font-weight: bold;
            background: #f8f9fa;
            min-width: 150px;
        }
        
        .fixed-header-table th:first-child {
            min-width: 150px;
            position: sticky;
            left: 0;
            z-index: 110;
            background: #f8f9fa;
        }
        
        /* ?箏?撌行?銵冽 */
        .fixed-sidebar {
            position: absolute;
            top: 0;
            left: 0;
            z-index: 90;
            background: #f8f9fa;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        
        .fixed-sidebar table {
            border-collapse: collapse;
        }
        
        .fixed-sidebar td {
            padding: 15px;
            border: 1px solid #dee2e6;
            font-weight: bold;
            background: #f8f9fa;
            min-width: 150px;
            height: 60px;
            display: flex;
            align-items: center;
        }
        
        /* ?舀遝?摰寡”??*/
        .scrollable-content {
            margin-left: 150px; /* 撌行?撖砍漲 */
            overflow: auto;
        }
        
        .scrollable-content table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .scrollable-content td {
            padding: 15px 10px;
            border: 1px solid #dee2e6;
            text-align: center;
            min-width: 100px;
            height: 60px;
        }
        
        .scrollable-content tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .scrollable-content tr:nth-child(odd) {
            background: white;
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
            font-size: 13px;
            margin-top: 3px;
        }
        
        /* ??璅?? */
        .amount {
            font-size: 14px;
            font-weight: 500;
        }
        
        .substitute {
            font-size: 10px;
            color: #999;
            margin-top: 2px;
        }
        
        /* ?望璅?? */
        .weekend {
            background: rgba(255, 255, 0, 0.03) !important;
            color: #d9534f;
        }
        
        /* 蝮質?銵?*/
        .total-row {
            background: #e8f5e9 !important;
            font-weight: bold;
        }
        
        /* ==================== ?批?Ｘ ==================== */
        .control-panel {
            padding: 15px 20px;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .control-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
        }
        
        .btn-test {
            background: #007bff;
            color: white;
        }
        
        .btn-test:hover {
            background: #0056b3;
        }
        
        .btn-reset {
            background: #6c757d;
            color: white;
        }
        
        .btn-reset:hover {
            background: #545b62;
        }
        
        .status-box {
            padding: 8px 15px;
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
        
        /* ==================== 隤芣????==================== */
        .explanation {
            padding: 20px;
            background: white;
            border-top: 1px solid #dee2e6;
        }
        
        .explanation h3 {
            color: #333;
            margin-bottom: 15px;
        }
        
        .explanation ul {
            margin-left: 20px;
            margin-bottom: 15px;
        }
        
        .explanation li {
            margin-bottom: 8px;
            line-height: 1.5;
        }
        
        .advantages {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .advantage-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #28a745;
        }
        
        .advantage-card h4 {
            color: #28a745;
            margin-bottom: 8px;
        }
        
        /* ==================== 皜祈岫蝯? ==================== */
        .test-results {
            padding: 20px;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }
        
        .test-item {
            padding: 12px;
            margin: 8px 0;
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
        
        /* ==================== ???踵?撘?==================== */
        @media (max-width: 768px) {
            .dual-table-container {
                max-height: 400px;
            }
            
            .fixed-header-table th,
            .fixed-sidebar td,
            .scrollable-content td {
                padding: 10px 5px;
                font-size: 13px;
            }
            
            .fixed-header-table th:first-child,
            .fixed-sidebar td {
                min-width: 120px;
            }
            
            .scrollable-content {
                margin-left: 120px;
            }
            
            .store-code {
                font-size: 13px;
            }
            
            .store-name {
                font-size: 11px;
            }
            
            .amount {
                font-size: 13px;
            }
            
            .control-panel {
                flex-direction: column;
            }
            
            .control-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class='solution-wrapper'>
        <div class='solution-header'>
            <h1>?? ?”?潸圾瘙箸獢?/h1>
            <p>??喟絞雿??舫??瘜?- 雿輻銝”?澆??亥??摰?皛曉?</p>
        </div>
        
        <div class='dual-table-container' id='tableContainer'>
            <!-- ?箏?銵券 -->
            <div class='fixed-header-table' id='fixedHeader'>
                <table>
                    <thead>
                        <tr>
                            <th>摨?</th>
                            <?php foreach ($dates as $date): 
                                $day_w = date('w', strtotime($date));
                                $is_we = ($day_w == 0 || $day_w == 6);
                            ?>
                            <th class='<?php echo $is_we ? 'weekend' : ''; ?>'>
                                <?php echo date('d', strtotime($date)); ?><br>
                                <small><?php echo ['??,'銝','鈭?,'銝?,'??,'鈭?,'??][$day_w]; ?></small>
                            </th>
                            <?php endforeach; ?>
                            <th>蝮質?</th>
                            <th>撟喳?</th>
                        </tr>
                    </thead>
                </table>
            </div>
            
            <div style='display: flex;'>
                <!-- ?箏?撌行? -->
                <div class='fixed-sidebar' id='fixedSidebar'>
                    <table>
                        <tbody>
                            <?php foreach ($stores as $store): ?>
                            <tr>
                                <td>
                                    <div class='store-info'>
                                        <div class='store-code'><?php echo htmlspecialchars($store['code']); ?></div>
                                        <div class='store-name'><?php echo htmlspecialchars($store['name']); ?></div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- ?舀遝?摰?-->
                <div class='scrollable-content' id='scrollableContent'>
                    <table>
                        <tbody>
                            <?php foreach ($stores as $store): ?>
                            <tr>
                                <?php foreach ($dates as $date): 
                                    $day_w = date('w', strtotime($date));
                                    $is_we = ($day_w == 0 || $day_w == 6);
                                    $amount = rand(0, 1) ? rand(5000, 20000) : 0;
                                    $is_substitute = rand(0, 4) === 0;
                                ?>
                                <td class='<?php echo $is_we ? 'weekend' : ''; ?>'>
                                    <div class='amount'><?php echo $amount > 0 ? number_format($amount) : '-'; ?></div>
                                    <?php if ($is_substitute && $amount > 0): ?>
                                    <div class='substitute'>隞?/div>
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
            </div>
        </div>
        
        <div class='control-panel'>
            <button class='control-btn btn-test' onclick='testFixedHeader()'>皜祈岫銵券?箏?</button>
            <button class='control-btn btn-test' onclick='testFixedSidebar()'>皜祈岫撌行??箏?</button>
            <button class='control-btn btn-test' onclick='testScrollSync()'>皜祈岫皛曉??郊</button>
            <button class='control-btn btn-reset' onclick='resetScroll()'>?蔭皛曉?</button>
            
            <div class='status-box' id='statusBox'>
                ???蝑?皜祈岫
            </div>
        </div>
        
        <div class='explanation'>
            <h3>? ?箔?暻潮獢??</h3>
            <ul>
                <li><strong>??釣暺?/strong>嚗”?准椰甈摰孵??芰蝡?/li>
                <li><strong>蝯??箏?</strong>嚗”?凋蝙??sticky嚗椰甈蝙??absolute</li>
                <li><strong>?嗡?鞈?/strong>嚗? CSS + 撠? JavaScript嚗蝚砌??孵澈</li>
                <li><strong>摰??詨捆</strong>嚗?湔??汗?剁?? IE11</li>
            </ul>
            
            <div class='advantages'>
                <div class='advantage-card'>
                    <h4>??100% ?舫?</h4>
                    <p>銝蝙??CSS sticky ?芋蝟?雿?瘥?蝝??蝣箔?蝵?/p>
                </div>
                
                <div class='advantage-card'>
                    <h4>???扯?雿?/h4>
                    <p>銵冽蝯?蝪∪嚗汗?冽葡????</p>
                </div>
                
                <div class='advantage-card'>
                    <h4>??蝬剛風蝪∪</h4>
                    <p>銝蝡”?潘??日?耨?寥摰寞?</p>
                </div>
                
                <div class='advantage-card'>
                    <h4>?????詨捆</h4>
                    <p>?舀?汗?剁?蝣箔???蝙?刻?賣迤撣訾蝙??/p>
                </div>
            </div>
        </div>
        
        <div class='test-results'>
            <h3>皜祈岫蝯?</h3>
            <div id='testResults'>
                <div class='test-item test-pass'>
                    <strong>?寞?瑼Ｘ嚗?/strong> ?”?潭獢歇頛
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // 皛曉??郊
        function setupScrollSync() {
            const container = document.getElementById('tableContainer');
            const header = document.getElementById('fixedHeader');
            const content = document.getElementById('scrollableContent');
            
            if (!container || !header || !content) return;
            
            // ?郊瘞游像皛曉?
            content.addEventListener('scroll', function() {
                header.scrollLeft = this.scrollLeft;
            });
            
            // ?郊?皛曉?
            container.addEventListener('scroll', function() {
                const sidebar = document.getElementById('fixedSidebar');
                if (sidebar) {
                    sidebar.style.top = this.scrollTop + 'px';
                }
            });
        }
        
        // 皜祈岫銵券?箏?
        function testFixedHeader() {
            const container = document.getElementById('tableContainer');
            const header = document.getElementById('fixedHeader');
            const status = document.getElementById('statusBox');
            
            if (!container || !header) return;
            
            // 璅⊥皛曉?
            container.scrollLeft = 300;
            container.scrollTop = 100;
            
            setTimeout(() => {
                const headerRect = header.getBoundingClientRect();
                const isFixed = headerRect.top === 0;
                
                const result = document.createElement('div');
                result.className = isFixed ? 'test-item test-pass' : 'test-item test-fail';
                result.innerHTML = `<strong>銵券?箏?皜祈岫嚗?/strong> ${isFixed ? '???? - 銵券?箏??券??? : '??憭望?'}`;
                
                document.getElementById('testResults').appendChild(result);
                
                status.textContent = `???銵券?箏? ${isFixed ? '??' : '憭望?'}`;
                status.className = `status-box ${isFixed ? 'status-good' : 'status-bad'}`;
                
            }, 300);
        }
        
        // 皜祈岫撌行??箏?
        function testFixedSidebar() {
            const container = document.getElementById('tableContainer');
            const sidebar = document.getElementById('fixedSidebar');
            const status = document.getElementById('statusBox');
            
            if (!container || !sidebar) return;
            
            // 璅⊥皛曉?
            container.scrollLeft = 400;
            container.scrollTop = 150;
            
            setTimeout(() => {
                const sidebarRect = sidebar.getBoundingClientRect();
                const isFixed = sidebarRect.left === 0;
                
                const result = document.createElement('div');
                result.className = isFixed ? 'test-item test-pass' : 'test-item
