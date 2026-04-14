<?php
/**
 * 閮箸銵冽?箏???
 */

// ?? Session
session_start();

require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/auth.php';

// 皜祈岫蝞∠??∠??$_SESSION['user_id'] = 'admin';
$_SESSION['username'] = 'admin';
$_SESSION['name'] = '蝟餌絞蝞∠???;
$_SESSION['role'] = 'admin';
$_SESSION['logged_in'] = true;

$user = get_current_session_user();

echo "<!DOCTYPE html>
<html lang='zh-TW'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>閮箸銵冽?箏???</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background: #f8f9fa; }
        .container { max-width: 1200px; margin: 0 auto; }
        .section { background: white; padding: 20px; margin: 20px 0; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .info { color: #17a2b8; }
        
        /* 閮箸銵冽璅?? - 雿輻撖阡???CSS */
        .diagnostic-table-container {
            overflow-x: auto;
            overflow-y: visible;
            position: relative;
            border: 3px solid #dc3545;
            border-radius: 8px;
            margin: 20px 0;
            max-height: 500px;
            background: #fff;
        }
        
        .diagnostic-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1200px;
            position: relative;
            font-size: 14px;
        }
        
        /* ?岫銝???sticky 閮剖? */
        .diagnostic-table thead.diagnostic-thead {
            position: -webkit-sticky; /* Safari */
            position: sticky;
            top: 0;
            z-index: 100;
            background: #f8f9fa;
        }
        
        .diagnostic-table thead.diagnostic-thead th {
            position: -webkit-sticky; /* Safari */
            position: sticky;
            top: 0;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 12px 8px;
            font-weight: bold;
            white-space: nowrap;
        }
        
        /* 撌行??箏? */
        .diagnostic-table tbody td.diagnostic-first-col {
            position: -webkit-sticky; /* Safari */
            position: sticky;
            left: 0;
            background: #f8f9fa;
            z-index: 90;
            border: 1px solid #dee2e6;
            padding: 12px 8px;
            font-weight: bold;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        
        .diagnostic-table tbody td.diagnostic-second-col {
            position: -webkit-sticky; /* Safari */
            position: sticky;
            left: 80px; /* 蝚砌?甈祝摨?*/
            background: #f8f9fa;
            z-index: 90;
            border: 1px solid #dee2e6;
            padding: 12px 8px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        
        .diagnostic-table tbody td {
            border: 1px solid #dee2e6;
            padding: 12px 8px;
            text-align: center;
            background: white;
        }
        
        .diagnostic-table tbody tr:nth-child(even) td:not(.diagnostic-first-col):not(.diagnostic-second-col) {
            background-color: #f8f9fa;
        }
        
        .diagnostic-table tbody tr:nth-child(odd) td:not(.diagnostic-first-col):not(.diagnostic-second-col) {
            background-color: #ffffff;
        }
        
        /* 皜祈岫?? */
        .test-btn {
            padding: 10px 20px;
            margin: 5px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .test-fix { background: #28a745; color: white; }
        .test-original { background: #dc3545; color: white; }
        .test-mobile { background: #17a2b8; color: white; }
        
        /* 蝯?憿舐內 */
        .result-box {
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 5px solid;
        }
        
        .result-good { border-color: #28a745; background: #d4edda; }
        .result-bad { border-color: #dc3545; background: #f8d7da; }
        .result-info { border-color: #17a2b8; background: #d1ecf1; }
        
        /* 蝔?蝣潮＊蝷?*/
        code {
            background: #f8f9fa;
            padding: 2px 5px;
            border-radius: 3px;
            font-family: monospace;
        }
        
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            border: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>?? 閮箸銵冽?箏???</h1>
        
        <div class='section'>
            <h2>???膩</h2>
            <p class='error'><strong>??嚗?/strong>???舀????交?銵券隞?????/p>
            <p>摨?撌行??箏???嚗??交?銵券?箏??⊥???/p>
        </div>
        
        <div class='section'>
            <h2>閮箸皜祈岫銵冽</h2>
            <p>?”?潔蝙?刻??漲?梯”?詨???CSS ?銵?雿?陛?誑靘輯那?瑯?/p>
            
            <div class='diagnostic-table-container' id='testTable'>
                <table class='diagnostic-table'>
                    <thead class='diagnostic-thead'>
                        <tr>
                            <th style='min-width: 80px;'>隞??</th>
                            <th style='min-width: 120px;'>摨??迂</th>
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
                            <td class='diagnostic-first-col'>27<?php echo $i; ?></td>
                            <td class='diagnostic-second-col'>摨? <?php echo $i; ?></td>
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
            
            <div class='result-box result-info'>
                <h3>皜祈岫隤芣?</h3>
                <p>1. 璈怠?皛曉??”??/p>
                <p>2. 閫撖?”?剜?血摰?/p>
                <p>3. 閫撖?瑹椰甈?血摰?/p>
                <p>4. 雿輻銝??皜祈岫銝?閮剖?</p>
            </div>
            
            <div>
                <button class='test-btn test-fix' onclick='testCurrentFix()'>皜祈岫?嗅?閮剖?</button>
                <button class='test-btn test-original' onclick='testAlternativeFix()'>皜祈岫?蹂誨?寞?</button>
                <button class='test-btn test-mobile' onclick='simulateMobile()'>璅⊥??鋆蔭</button>
            </div>
        </div>
        
        <div class='section'>
            <h2>CSS 閮剖?瑼Ｘ</h2>
            
            <div class='result-box' id='cssCheckResult'>
                <h3>?嗅????CSS</h3>
                <div id='appliedCss'></div>
            </div>
            
            <h3>?航??憿?</h3>
            <ol>
                <li><strong>?嗅?蝝?overflow 閮剖?</strong>嚗?code>overflow-y</code> 敹???<code>visible</code> ??<code>auto</code></li>
                <li><strong>sticky ??擃漲</strong>嚗ticky ??銝??<code>height: 100%</code></li>
                <li><strong>銵冽雿?</strong>嚗?code>table-layout</code> ?航敶梢 sticky</li>
                <li><strong>?汗?典?蝬?/strong>嚗afari ?閬?<code>-webkit-sticky</code></li>
                <li><strong>z-index 銵?</strong>嚗隞?蝝?質???sticky ??</li>
            </ol>
        </div>
        
        <div class='section'>
            <h2>?蹂誨閫?捱?寞?</h2>
            
            <h3>?寞? A嚗蝙??JavaScript ?箏?</h3>
            <pre><code>// ??皛曉?鈭辣
tableContainer.addEventListener('scroll', function() {
    const scrollLeft = this.scrollLeft;
    const scrollTop = this.scrollTop;
    
    // ?箏?銵券
    thead.style.transform = `translateY(${scrollTop}px)`;
    
    // ?箏?撌行?
    firstColumns.forEach(col => {
        col.style.transform = `translateX(${scrollLeft}px)`;
    });
});</code></pre>
            
            <h3>?寞? B嚗蝙??CSS transform ?箏?</h3>
            <pre><code>/* 雿輻 transform ?? sticky */
.fixed-header {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    transform: translateY(var(--scroll-top));
}

.fixed-left {
    position: absolute;
    left: 0;
    transform: translateX(var(--scroll-left));
}</code></pre>
            
            <h3>?寞? C嚗??”??/h3>
            <pre><code>&lt;!-- ???箏????--&gt;
&lt;div class="table-wrapper"&gt;
    &lt;div class="corner"&gt;隞??/?迂&lt;/div&gt;
    &lt;div class="header"&gt;?交?銵券&lt;/div&gt;
    &lt;div class="sidebar"&gt;摨??”&lt;/div&gt;
    &lt;div class="content"&gt;璆剔蜀鞈?&lt;/div&gt;
&lt;/div&gt;</code></pre>
        </div>
        
        <div class='section'>
            <h2>蝡皜祈岫撖阡??</h2>
            <p><a href='sales/monthly_report.php' target='_blank' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>皜祈岫撖阡??漲?梯”</a></p>
            
            <h3>皜祈岫甇仿?嚗?/h3>
            <ol>
                <li>??撖阡??</li>
                <li>??F12 ????極??/li>
                <li>瑼Ｘ???CSS 璅??</li>
                <li>瑼Ｘ?臬?隤斗?霅血?</li>
                <li>皜祈岫璈怠?皛曉?</li>
            </ol>
            
            <h3>瑼Ｘ??嚗?/h3>
            <ul>
                <li>瑼Ｘ <code>.monthly-table thead th</code> ??<code>position</code> 撅祆?/li>
                <li>瑼Ｘ <code>.table-container</code> ??<code>overflow</code> 撅祆?/li>
                <li>瑼Ｘ?臬?隞?CSS 閬?鈭?sticky</li>
                <li>瑼Ｘ?汗??Console ?臬?隤?/li>
            </ul>
        </div>
    </div>
    
    <script>
        // 瑼Ｘ???CSS
        function checkAppliedCss() {
            const table = document.querySelector('.diagnostic-table');
            const thead = document.querySelector('.diagnostic-thead');
            const container = document.querySelector('.diagnostic-table-container');
            
            if (!table || !thead || !container) return;
            
            const styles = window.getComputedStyle(thead);
            const containerStyles = window.getComputedStyle(container);
            
            const cssInfo = `
                <p><strong>銵券 sticky 閮剖?嚗?/strong></p>
                <ul>
                    <li>position: ${styles.position}</li>
                    <li>top: ${styles.top}</li>
                    <li>z-index: ${styles.zIndex}</li>
                </ul>
                
                <p><strong>摰孵閮剖?嚗?/strong></p>
                <ul>
                    <li>overflow-x: ${containerStyles.overflowX}</li>
                    <li>overflow-y: ${containerStyles.overflowY}</li>
                    <li>position: ${containerStyles.position}</li>
                </ul>
                
                <p><strong>銵冽閮剖?嚗?/strong></p>
                <ul>
                    <li>position: ${window.getComputedStyle(table).position}</li>
                </ul>
            `;
            
            document.getElementById('appliedCss').innerHTML = cssInfo;
            
            // 瑼Ｘ?臬??
            const isSticky = styles.position.includes('sticky');
            const hasTop = styles.top !== 'auto';
            const overflowY = containerStyles.overflowY;
            
            let resultClass = 'result-bad';
            let resultText = '???航?⊥?';
            
            if (isSticky && hasTop && (overflowY === 'visible' || overflowY === 'auto')) {
                resultClass = 'result-good';
                resultText = '??閮剖?甇?Ⅱ';
            }
            
            document.getElementById('cssCheckResult').className = `result-box ${resultClass}`;
            document.getElementById('cssCheckResult').innerHTML = `<h3>CSS 瑼Ｘ蝯?嚗?{resultText}</h3>${cssInfo}`;
        }
        
        // 皜祈岫?嗅?閮剖?
        function testCurrentFix() {
            const container = document.querySelector('.diagnostic-table-container');
            const thead = document.querySelector('.diagnostic-thead');
            
            // ??嗅??漲?梯”?身摰?            container.style.overflowY = 'visible';
            container.style.position =
