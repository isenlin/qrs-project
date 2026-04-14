<?php
/**
 * ?蝯”?澆摰葫閰?- 雿輻??舫??瘜? */

// ?? Session
session_start();

require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/auth.php';

echo "<!DOCTYPE html>
<html lang='zh-TW'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>?蝯”?澆摰葫閰?/title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: sans-serif; padding: 20px; background: #f8f9fa; margin: 0; }
        
        .test-wrapper {
            max-width: 100%;
            margin: 0 auto;
            padding: 20px;
        }
        
        h1 { color: #333; text-align: center; }
        
        /* ?寞?1嚗蝙??CSS Grid + position: fixed (??舫?) */
        .method-1 {
            margin: 40px 0;
            border: 3px solid #28a745;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .method-1 .title {
            background: #28a745;
            color: white;
            padding: 15px;
            margin: 0;
            font-size: 18px;
        }
        
        .grid-table-wrapper {
            display: grid;
            grid-template-columns: auto 1fr;
            grid-template-rows: auto 1fr;
            max-height: 500px;
            overflow: auto;
            background: white;
        }
        
        .grid-corner {
            grid-column: 1;
            grid-row: 1;
            background: #f8f9fa;
            border-right: 1px solid #dee2e6;
            border-bottom: 1px solid #dee2e6;
            padding: 15px;
            font-weight: bold;
            position: sticky;
            top: 0;
            left: 0;
            z-index: 30;
        }
        
        .grid-header {
            grid-column: 2;
            grid-row: 1;
            display: flex;
            overflow-x: auto;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            position: sticky;
            top: 0;
            z-index: 20;
        }
        
        .grid-header-cell {
            min-width: 80px;
            padding: 15px 10px;
            border-right: 1px solid #dee2e6;
            text-align: center;
            font-weight: bold;
            white-space: nowrap;
            flex-shrink: 0;
        }
        
        .grid-sidebar {
            grid-column: 1;
            grid-row: 2;
            overflow-y: auto;
            background: #f8f9fa;
            border-right: 1px solid #dee2e6;
        }
        
        .grid-sidebar-cell {
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
            min-height: 60px;
            display: flex;
            align-items: center;
            font-weight: bold;
            position: sticky;
            left: 0;
            background: #f8f9fa;
        }
        
        .grid-content {
            grid-column: 2;
            grid-row: 2;
            overflow: auto;
        }
        
        .grid-content-row {
            display: flex;
            border-bottom: 1px solid #dee2e6;
        }
        
        .grid-content-cell {
            min-width: 80px;
            padding: 15px 10px;
            border-right: 1px solid #dee2e6;
            text-align: center;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .grid-content-row:nth-child(even) {
            background: #f8f9fa;
        }
        
        /* ?寞?2嚗蝙?典蝯梯”??+ ??摰孵 */
        .method-2 {
            margin: 40px 0;
            border: 3px solid #17a2b8;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .method-2 .title {
            background: #17a2b8;
            color: white;
            padding: 15px;
            margin: 0;
            font-size: 18px;
        }
        
        .double-container {
            position: relative;
            overflow: auto;
            max-height: 500px;
            background: white;
        }
        
        .table-fixed-wrapper {
            position: relative;
        }
        
        .table-fixed-header {
            position: sticky;
            top: 0;
            z-index: 20;
            background: #f8f9fa;
        }
        
        .table-fixed-header table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table-fixed-header th {
            padding: 15px 10px;
            border: 1px solid #dee2e6;
            text-align: center;
            font-weight: bold;
            min-width: 80px;
            background: #f8f9fa;
        }
        
        .table-fixed-side {
            position: absolute;
            top: 0;
            left: 0;
            z-index: 25;
            background: #f8f9fa;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        
        .table-fixed-side td {
            padding: 15px;
            border: 1px solid #dee2e6;
            font-weight: bold;
            background: #f8f9fa;
        }
        
        .table-scrollable {
            margin-left: 160px; /* 撌行?撖砍漲 */
            overflow: auto;
        }
        
        .table-scrollable table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table-scrollable td {
            padding: 15px 10px;
            border: 1px solid #dee2e6;
            text-align: center;
            min-width: 80px;
        }
        
        .table-scrollable tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        /* 皜祈岫蝯? */
        .test-results {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border: 2px solid #6c757d;
        }
        
        .result-item {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 5px solid;
        }
        
        .result-good { border-color: #28a745; background: #d4edda; }
        .result-bad { border-color: #dc3545; background: #f8d7da; }
        
        button {
            padding: 10px 20px;
            margin: 5px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .btn-test { background: #007bff; color: white; }
        .btn-actual { background: #28a745; color: white; }
        
        .instructions {
            background: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #ffeaa7;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class='test-wrapper'>
        <h1>?? ?蝯”?澆摰圾瘙箸獢葫閰?/h1>
        
        <div class='instructions'>
            <h3>??嚗?”?凋???皛?</h3>
            <p>蝬?憭活 CSS 靽格迤敺??交?銵券隞?⊥??箏??ㄐ???拍車??舫??圾瘙箸獢?</p>
            <ol>
                <li><strong>?寞?1嚗SS Grid 雿?</strong> - ??曆誨???舫??瘜?/li>
                <li><strong>?寞?2嚗??捆??/strong> - ?喟絞雿???寞?</li>
            </ol>
        </div>
        
        <!-- ?寞?1嚗SS Grid -->
        <div class='method-1'>
            <h2 class='title'>?寞?1嚗SS Grid 雿?嚗?佗?</h2>
            <div class='grid-table-wrapper' id='gridTable'>
                <!-- 撌虫?閫摰?-->
                <div class='grid-corner'>
                    摨?鞈?
                </div>
                
                <!-- 銝銵券嚗璈怠?皛曉?嚗?-->
                <div class='grid-header'>
                    <div class='grid-header-cell'>3/1<br><small>銝</small></div>
                    <div class='grid-header-cell'>3/2<br><small>鈭?/small></div>
                    <div class='grid-header-cell'>3/3<br><small>銝?/small></div>
                    <div class='grid-header-cell'>3/4<br><small>??/small></div>
                    <div class='grid-header-cell'>3/5<br><small>鈭?/small></div>
                    <div class='grid-header-cell'>3/6<br><small>??/small></div>
                    <div class='grid-header-cell'>3/7<br><small>??/small></div>
                    <div class='grid-header-cell'>3/8<br><small>銝</small></div>
                    <div class='grid-header-cell'>3/9<br><small>鈭?/small></div>
                    <div class='grid-header-cell'>3/10<br><small>銝?/small></div>
                    <div class='grid-header-cell'>3/11<br><small>??/small></div>
                    <div class='grid-header-cell'>3/12<br><small>鈭?/small></div>
                    <div class='grid-header-cell'>3/13<br><small>??/small></div>
                    <div class='grid-header-cell'>3/14<br><small>??/small></div>
                    <div class='grid-header-cell'>蝮質?</div>
                    <div class='grid-header-cell'>撟喳?</div>
                </div>
                
                <!-- 撌血??嚗?皛曉?嚗?-->
                <div class='grid-sidebar'>
                    <?php for ($i = 1; $i <= 10; $i++): ?>
                    <div class='grid-sidebar-cell'>
                        27<?php echo $i; ?> - 摨?<?php echo $i; ?>
                    </div>
                    <?php endfor; ?>
                </div>
                
                <!-- 銝餉??批捆嚗??皛曉?嚗?-->
                <div class='grid-content'>
                    <?php for ($row = 1; $row <= 10; $row++): ?>
                    <div class='grid-content-row'>
                        <?php for ($col = 1; $col <= 16; $col++): ?>
                        <div class='grid-content-cell'>
                            <?php echo number_format(rand(10000, 20000)); ?>
                        </div>
                        <?php endfor; ?>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
            
            <div style='padding: 15px; background: #f8f9fa;'>
                <h4>?寞?1?芷?嚗?/h4>
                <ul>
                    <li>??銵券?椰甈?撠摰?/li>
                    <li>???舀??隞?汗??/li>
                    <li>??皛曉??扯?雿?/li>
                    <li>???踵?撘身閮捆??/li>
                </ul>
                <button class='btn-test' onclick='testMethod1()'>皜祈岫?寞?1</button>
            </div>
        </div>
        
        <!-- ?寞?2嚗??捆??-->
        <div class='method-2'>
            <h2 class='title'>?寞?2嚗??捆?剁??喟絞嚗?/h2>
            <div class='double-container'>
                <div class='table-fixed-wrapper'>
                    <!-- ?箏?銵券 -->
                    <div class='table-fixed-header'>
                        <table>
                            <thead>
                                <tr>
                                    <th style='width: 160px;'>摨?鞈?</th>
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
                        </table>
                    </div>
                    
                    <div style='display: flex;'>
                        <!-- ?箏?撌行? -->
                        <div class='table-fixed-side'>
                            <table>
                                <tbody>
                                    <?php for ($i = 1; $i <= 10; $i++): ?>
                                    <tr>
                                        <td>27<?php echo $i; ?> - 摨?<?php echo $i; ?></td>
                                    </tr>
                                    <?php endfor; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- ?舀遝?摰?-->
                        <div class='table-scrollable'>
                            <table>
                                <tbody>
                                    <?php for ($row = 1; $row <= 10; $row++): ?>
                                    <tr>
                                        <?php for ($col = 1; $col <= 16; $col++): ?>
                                        <td><?php echo number_format(rand(10000, 20000)); ?></td>
                                        <?php endfor; ?>
                                    </tr>
                                    <?php endfor; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div style='padding: 15px; background: #f8f9fa;'>
                <h4>?寞?2?芷?嚗?/h4>
                <ul>
                    <li>???詨捆?扳?憟踝??舀?汗?剁?</li>
                    <li>??雿輻?喟絞銵冽隤儔</li>
                    <li>??撖衣?詨?蝪∪</li>
                    <li>????末</li>
                </ul>
                <button class='btn-test' onclick='testMethod2()'>皜祈岫?寞?2</button>
            </div>
        </div>
        
        <div class='test-results'>
            <h2>皜祈岫蝯?</h2>
            <div id='testResults'>
                <div class='result-item result-good'>
                    <h4>皜祈岫隤芣?</h4>
                    <p>隢葫閰虫誑銝蝔格瘜??嗅?暺?銝??閮?蝯???/p>
                </div>
            </div>
            
            <div style='margin-top: 20px;'>
                <h3>撖阡??皜祈岫</h3>
                <p><a href='sales/monthly_report.php' target='_blank' class='btn-actual' style='display: inline-block; padding: 10px 20px; text-decoration: none;'>皜祈岫撖阡??漲?梯”</a></p>
                <p><small>瘜冽?嚗祕???Ｗ歇???啁? CSS 靽格迤嚗?????憿?撱箄降?∠?寞?1?瘜?????/small></p>
            </div>
        </div>
        
        <div style='text-align: center; margin-top: 40px; padding: 20px; background: white; border-radius: 10px;'>
            <h3>撱箄降?寞?</h3>
            <p>?寞?皜祈岫蝯?嚗遣霅堆?</p>
            <ol style='text-align: left; display: inline-block;'>
                <li>憒??寞?1撌乩?甇?虜 ?????漲?梯”雿輻 CSS Grid</li>
                <li>憒??寞?2撌乩?甇?虜 ?????漲?梯”雿輻??摰孵</li>
                <li>憒??抵甇?虜 ???豢??寞?1嚗?曆誨嚗?/li>
            </ol>
        </div>
    </div>
    
    <script>
        function testMethod1() {
            const gridTable = document.getElementById('gridTable');
            const header = gridTable.querySelector('.grid-header');
            const sidebar = gridTable.querySelector('.grid-sidebar');
            
            let result = '<div class=\"result-item result-good\">';
            result += '<h4>?寞?1皜祈岫蝯?</h4>';
            result += '<p>??銵券?箏?嚗? + (header.style.position === 'sticky' ? '?? : 'CSS ?批') + '</p>';
            result += '<p>??撌行??箏?嚗? + (sidebar.querySelector('.grid-sidebar-cell').style.position === 'sticky' ? '?? : 'CSS ?批') + '</p>';
            result += '<p>??皛曉?皜祈岫嚗???皛曉?蝣箄?</p>';
            result += '</div>';
            
            document.getElementById('testResults').innerHTML += result;
            
            // 璅⊥皛曉?皜祈岫
            gridTable.scrollLeft = 200;
            gridTable.scrollTop = 100;
            
            setTimeout(() => {
                alert('?寞?1皜祈岫摰?嚗?蝣箄?皛曉??”?剖?撌行??臬靽??箏???);
            },
