<?php
/**
 * 皜祈岫??銵冽?箏?銵券?椰甈??? */

// ?? Session
session_start();

require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/auth.php';

echo "<h1>皜祈岫??銵冽?箏?銵券?椰甈???/h1>";

// 皜祈岫蝞∠??∠??$_SESSION['user_id'] = 'admin';
$_SESSION['username'] = 'admin';
$_SESSION['name'] = '蝟餌絞蝞∠???;
$_SESSION['role'] = 'admin';
$_SESSION['logged_in'] = true;

$user = get_current_session_user();

echo "<h2>皜祈岫???/h2>";
echo "<p>雿輻?? " . $user['name'] . " (" . $user['role'] . ")</p>";
echo "<p>皜祈岫??: " . date('Y-m-d H:i:s') . "</p>";

echo "<h2>???膩</h2>";
echo "<div style='background: #fff3cd; padding: 20px; border-radius: 10px; border: 1px solid #ffeaa7; margin-bottom: 30px;'>";
echo "<h3 style='color: #856404;'>? ??銵冽皛曉???</h3>";
echo "<p><strong>??嚗?/strong>?嗥??皛??漲璆剔蜀?梯”???交???瑹???蝘餃?嚗??渡??啣??Ｙ?璆剔蜀??銝?????交???瑹?/p>";
echo "<p><strong>敶梢嚗?/strong>雿輻??撽榆嚗隞仿霈??閫????/p>";
echo "<p><strong>閫?捱?寞?嚗?/strong>雿輻 CSS <code>position: sticky</code> ?箏?銵券?椰甈?/p>";
echo "</div>";

echo "<h2>閫?捱?寞??銵敦蝭</h2>";

echo "<h3>1. ?箏?銵券嚗??Ｗ???嚗?/h3>";
echo "<pre><code>/* ?箏?銵券 */
.monthly-table thead th { 
    position: sticky; 
    top: 0; 
    z-index: 20; 
    background: #f8f9fa;
}</code></pre>";

echo "<h3>2. ?箏?撌行?嚗?瑹誨???迂嚗?/h3>";
echo "<pre><code>/* ?箏?撌行? */
.monthly-table tbody td:first-child,
.monthly-table tbody td:nth-child(2) {
    position: sticky;
    left: 0;
    background: #f8f9fa;
    z-index: 15;
    box-shadow: 2px 0 5px rgba(0,0,0,0.1);
}

.monthly-table tbody td:nth-child(2) {
    left: 80px; /* 隞??甈?撖砍漲 */
}</code></pre>";

echo "<h3>3. ??撠?芸?</h3>";
echo "<pre><code>@media (max-width: 768px) {
    /* ???箏?銵券?椰甈?*/
    .monthly-table thead th { 
        min-width: 60px;
        font-size: 13px;
        padding: 8px 3px;
    }
    
    .monthly-table tbody td:first-child {
        left: 0;
        min-width: 60px;
    }
    
    .monthly-table tbody td:nth-child(2) {
        left: 60px; /* ???誨??雿祝摨?*/
        min-width: 100px;
    }
}</code></pre>";

echo "<h3>4. 皛曉??內?</h3>";
echo "<pre><code>/* ??皛曉??內 */
.scroll-hint {
    display: none;
    text-align: center;
    padding: 10px;
    background: #f8f9fa;
    color: #6c757d;
    animation: fadeInOut 3s ease-in-out;
}

@keyframes fadeInOut {
    0% { opacity: 0; }
    20% { opacity: 1; }
    80% { opacity: 1; }
    100% { opacity: 0; }
}</code></pre>";

echo "<h2>皜祈岫???</h2>";

echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;'>";

// 皜祈岫銝??遢
$test_months = [
    'current' => [
        'month' => date('Y-m'),
        'name' => '?祆?',
        'color' => '#28a745'
    ],
    'last' => [
        'month' => date('Y-m', strtotime('-1 month')),
        'name' => '銝?',
        'color' => '#17a2b8'
    ],
    'next' => [
        'month' => date('Y-m', strtotime('+1 month')),
        'name' => '銝?',
        'color' => '#007bff'
    ]
];

foreach ($test_months as $key => $month_info) {
    echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; border: 2px solid {$month_info['color']};'>";
    echo "<h3 style='color: {$month_info['color']};'>{$month_info['name']}?梯”</h3>";
    echo "<p><strong>?遢嚗?/strong>{$month_info['month']}</p>";
    echo "<p><strong>??憭拇嚗?/strong>" . date('t', strtotime($month_info['month'] . '-01')) . "憭?/p>";
    echo "<p><strong>皜祈岫??嚗?/strong>銵冽?箏?????閮剛?</p>";
    echo "<p><a href='sales/monthly_report.php?month={$month_info['month']}' target='_blank' style='display: inline-block; background: {$month_info['color']}; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold; width: 100%; text-align: center;'>皜祈岫 {$month_info['name']}?梯”</a></p>";
    echo "</div>";
}

echo "</div>";

echo "<h2>皜祈岫甇仿?</h2>";

echo "<h3>獢皜祈岫嚗?佗?</h3>";
echo "<ol>";
echo "<li>???漲璆剔蜀?梯”</li>";
echo "<li><strong>蝣箄?嚗?/strong>銵冽甇?虜憿舐內嚗?璈怠?皛曉?璇?/li>";
echo "<li><strong>蝣箄?嚗?/strong>皛曉??”?凋??摰?/li>";
echo "<li><strong>蝣箄?嚗?/strong>皛曉??椰甈?隞????蝔梧?靽??箏?</li>";
echo "<li><strong>蝣箄?嚗?/strong>?收璇?甇?虜憿舐內</li>";
echo "<li><strong>蝣箄?嚗?/strong>隞?璅?甇?虜憿舐內</li>";
echo "</ol>";

echo "<h3>??皜祈岫嚗祕??璈?璅⊥嚗?/h3>";
echo "<h4>?寞? 1嚗蝙?函汗?券??潸極??/h4>";
echo "<ol>";
echo "<li>?券?衣汗?其葉???漲璆剔蜀?梯”</li>";
echo "<li>??F12 ????極??/li>";
echo "<li>暺?????蝵桀極?瑟???璅??嚗?/li>";
echo "<li>?豢???鋆蔭嚗? iPhone 12嚗?/li>";
echo "<li>?瑟?</li>";
echo "</ol>";

echo "<h4>?寞? 2嚗祕??璈葫閰?/h4>";
echo "<ol>";
echo "<li>?冽?璈汗?冽???摨行平蝮曉銵?/li>";
echo "<li><strong>蝣箄?嚗?/strong>?皛曉??內????臬椰?單?????渲”?潦?/li>";
echo "<li><strong>蝣箄?嚗?/strong>?內5蝘??芸?瘨仃</li>";
echo "<li>撌血皛?銵冽</li>";
echo "<li><strong>蝣箄?嚗?/strong>銵券靽??箏??冽?銝</li>";
echo "<li><strong>蝣箄?嚗?/strong>撌行?嚗誨??蝔梧?靽??箏??典椰??/li>";
echo "<li><strong>蝣箄?嚗?/strong>皛曉???蝯?????交???瑹?/li>";
echo "<li><strong>蝣箄?嚗?/strong>銵冽摮?憭批??拙????梯?</li>";
echo "<li><strong>蝣箄?嚗?/strong>隞?璅?皜?航?</li>";
echo "</ol>";

echo "<h3>?踵?撘葫閰?/h3>";
echo "<ol>";
echo "<li>???漲璆剔蜀?梯”</li>";
echo "<li>隤踵?汗?刻?蝒之撠?/li>";
echo "<li><strong>蝣箄?嚗?/strong>銵冽?芸??拇?銝?撖砍漲</li>";
echo "<li><strong>蝣箄?嚗?/strong>?典?閬?銝剝＊蝷箸遝??蝷?/li>";
echo "<li><strong>蝣箄?嚗?/strong>?箏?甈??甇?虜</li>";
echo "</ol>";

echo "<h2>??蝯?</h2>";

echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>皜祈岫?</th><th>獢??/th><th>????/th><th>??璅?</th></tr>";

$test_cases = [
    ['銵券?箏?', '??皛曉??摰?, '??皛曉??摰?, '皛曉??”?剖?蝯閬?],
    ['撌行??箏?', '??皛曉??摰?, '??皛曉??摰?, '皛曉??誨???迂憪??航?'],
    ['皛曉??內', '??銝＊蝷?, '???芸?憿舐內', '??憿舐內?內嚗??Ｖ?憿舐內'],
    ['摮?憭批?', '??甇?虜憭批?', '???拍蝮桀?', '??摮???'],
    ['閫豢皛曉?', '??銝??, '??撟單?皛曉?', '??閫豢皛曉??'],
    ['?收璇?', '??甇?虜憿舐內', '??甇?虜憿舐內', '璈怠??收璇?皜'],
    ['隞?璅?', '??甇?虜憿舐內', '??甇?虜憿舐內', '隞?璅?皜?航?'],
    ['?望璅?', '??甇?虜憿舐內', '??甇?虜憿舐內', '?望?交??寞?璅?'],
    ['??', '??甇?虜??', '??甇?虜??', '????銵冽??'],
    ['?踵?撘身閮?, '???芸??拇?', '???芸??拇?', '銝?鋆蔭?芸?隤踵']
];

foreach ($test_cases as $case) {
    echo "<tr>";
    echo "<td>{$case[0]}</td>";
    echo "<td>{$case[1]}</td>";
    echo "<td>{$case[2]}</td>";
    echo "<td>{$case[3]}</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>?銵???/h2>";

echo "<h3>CSS position: sticky 撌乩???</h3>";
echo "<div style='background: #e7f3ff; padding: 20px; border-radius: 10px; margin-bottom: 20px;'>";
echo "<p><strong>sticky 摰?</strong>蝯?鈭?<strong>relative</strong> ??<strong>fixed</strong> 摰??暺?</p>";
echo "<ul>";
echo "<li><strong>甇?虜??</strong>嚗?蝝甇?虜?辣瘚葉嚗? relative嚗?/li>";
echo "<li><strong>皛曉???/strong>嚗???圈???雿蔭??霈??箏?摰?嚗? fixed嚗?/li>";
echo "<li><strong>?拍?湔</strong>嚗”?准?????芣?蝑?閬遝??靽??航???蝝?/li>";
echo "</ul>";
echo "</div>";

echo "<h3>z-index 撅斤?蝞∠?</h3>";
echo "<pre><code>/* 撅斤?閮剖? */
銵券撌行?: z-index: 35  (?擃?
銵券?嗡?: z-index: 30
撌行??批捆: z-index: 25
銵冽?批捆: z-index: 10 (?雿?</code></pre>";

echo "<h3>?踵?撘身閮???/h3>";
echo "<ul>";
echo "<li><strong>蝘餃??芸?</strong>嚗?閮剛??????撅獢??/li>";
echo "<li><strong>慦??亥岷</strong>嚗?撟祝摨行??其??見撘?/li>";
echo "<li><strong>敶找?撅</strong>嚗蝙?函???in-width?ax-width</li>";
echo "<li><strong>閫豢?芸?</strong>嚗?webkit-overflow-scrolling: touch</li>";
echo "</ul>";

echo "<h2>撣貉???閫?捱</h2>";

echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>??</th><th>?航??</th><th>閫?捱?寞?</th></tr>";

$problems = [
    ['?箏?甈?銝???, '?嗅?蝝??身摰?overflow', '蝣箔? .table-container ??overflow-x: auto'],
    ['z-index 銵?', '?嗡????擃?z-index', '隤踵?箏?????z-index ??],
    ['??皛曉??⊿?', 'CSS ??敶梢?憭?, '?芸? CSS嚗?撠?????],
    ['?箏?甈????, '?收璇?閬????, '雿輻 !important 蝣箔???脣??],
    ['iOS 皛曉???', 'Safari 撠?sticky ?舀??', '瘛餃? -webkit-sticky ?韌']
];

foreach ($problems as $problem) {
    echo "<tr>";
    echo "<td>{$problem[0]}</td>";
    echo "<td>{$problem[1]}</td>";
    echo "<td>{$problem[2]}</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>?汗?函摰寞?/h2>";
echo "<ul>";
echo "<li><strong>Chrome 56+</strong>嚗? 摰?舀</li>";
echo "<li><strong>Firefox 32+</strong>嚗? 摰?舀</li>";
echo "<li><strong>Safari 6.1+</strong>嚗? 摰?舀嚗? -webkit- ?韌嚗?/li>";
echo "<li><strong>Edge 16+</strong>嚗? 摰?舀</li>";
echo "<li><strong>iOS Safari 6.1+</strong>嚗? 摰?舀</li>";
echo "<li><strong>Android Browser 4.4+</strong>嚗? 摰?舀</li>";
echo "</ul>";

// 皜 Session
session_destroy();

echo "<p style='margin-top: 30px; color: #666;'>皜祈岫摰???: " . date('Y-m-d H:i:s') . "</p>";
?>
