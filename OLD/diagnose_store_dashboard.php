<?php
/**
 * 閰喟敦閮箸 store_dashboard.php ??
 */

// ?? Session
session_start();

require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/auth.php';

echo "<h1>閰喟敦閮箸 store_dashboard.php ??</h1>";

// 璅⊥摨??餃
$_SESSION['user_id'] = '277';
$_SESSION['username'] = '277';
$_SESSION['name'] = '277敺抵?摨?;
$_SESSION['role'] = 'store';
$_SESSION['stores'] = ['277'];
$_SESSION['logged_in'] = true;

$user = get_current_session_user();

echo "<h2>1. 瑼Ｘ?嗅????/h2>";

echo "<h3>雿輻??閮?</h3>";
echo "<pre>";
print_r($user);
echo "</pre>";

echo "<h3>Session ???</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>2. 瑼Ｘ AJAX ?漱??</h2>";

// 霈??store_dashboard.php 瑼?
$file_path = __DIR__ . '/store_dashboard.php';
$file_content = file_get_contents($file_path);

echo "<h3>瑼Ｘ submitSalesForm ?賣</h3>";

// 瑼Ｘ?賣摰儔
if (strpos($file_content, 'function submitSalesForm') !== false) {
    echo "<p style='color: green;'>??submitSalesForm ?賣撌脣?蝢?/p>";
    
    // ???賣?批捆
    $function_start = strpos($file_content, 'function submitSalesForm');
    $function_end = strpos($file_content, '}', $function_start);
    $function_code = substr($file_content, $function_start, $function_end - $function_start + 1);
    
    echo "<h4>?賣蝔?蝣潘?</h4>";
    echo "<pre>" . htmlspecialchars($function_code) . "</pre>";
} else {
    echo "<p style='color: red;'>??submitSalesForm ?賣?芸?蝢?/p>";
}

echo "<h3>瑼Ｘ銵典 HTML</h3>";

// 瑼Ｘ銵典??
$form_elements = [
    'id="sales-form"' => '銵典 ID',
    'id="selected-role"' => '閫?梯?甈?',
    'id="amount"' => '??頛詨獢?,
    'id="submit-btn"' => '?漱??',
    'onclick="submitSalesForm(event)"' => '暺?鈭辣'
];

foreach ($form_elements as $element => $description) {
    if (strpos($file_content, $element) !== false) {
        echo "<p style='color: green;'>??{$description} 摮</p>";
    } else {
        echo "<p style='color: red;'>??{$description} 銝???/p>";
    }
}

echo "<h2>3. 皜祈岫 AJAX ?漱</h2>";

echo "<h3>璅⊥ AJAX 隢?</h3>";
echo "<div id='test-results'></div>";

echo "<script>
async function testAjaxSubmission() {
    const resultsDiv = document.getElementById('test-results');
    resultsDiv.innerHTML = '<p>皜祈岫銝?..</p>';
    
    try {
        // 皜祈岫 1嚗炎??JavaScript ?賣
        if (typeof submitSalesForm !== 'function') {
            resultsDiv.innerHTML += '<p style=\"color: red;\">??submitSalesForm ?賣?芸?蝢?/p>';
            return;
        }
        resultsDiv.innerHTML += '<p style=\"color: green;\">??submitSalesForm ?賣撌脣?蝢?/p>';
        
        // 皜祈岫 2嚗炎?亥”?桀?蝝?        const form = document.getElementById('sales-form');
        const roleInput = document.getElementById('selected-role');
        const amountInput = document.getElementById('amount');
        const submitBtn = document.getElementById('submit-btn');
        
        if (!form) resultsDiv.innerHTML += '<p style=\"color: red;\">???曆??啗”??/p>';
        else resultsDiv.innerHTML += '<p style=\"color: green;\">???曉銵典</p>';
        
        if (!roleInput) resultsDiv.innerHTML += '<p style=\"color: red;\">???曆??啗??脰撓??/p>';
        else resultsDiv.innerHTML += '<p style=\"color: green;\">???曉閫頛詨</p>';
        
        if (!amountInput) resultsDiv.innerHTML += '<p style=\"color: red;\">???曆??圈?憿撓??/p>';
        else resultsDiv.innerHTML += '<p style=\"color: green;\">???曉??頛詨</p>';
        
        if (!submitBtn) resultsDiv.innerHTML += '<p style=\"color: red;\">???曆??唳?鈭斗???/p>';
        else resultsDiv.innerHTML += '<p style=\"color: green;\">???曉?漱??</p>';
        
        // 皜祈岫 3嚗芋?祆?鈭?        if (form && roleInput && amountInput && submitBtn) {
            // 閮剖?皜祈岫??            roleInput.value = 'main';
            amountInput.value = '9999';
            
            // 撱箇? FormData
            const formData = new FormData(form);
            
            // ?潮葫閰西?瘙?            const response = await fetch('store_dashboard.php', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            
            const html = await response.text();
            
            // 瑼Ｘ??
            if (html.includes('璆剔蜀?餅???') || html.includes('message success')) {
                resultsDiv.innerHTML += '<p style=\"color: green;\">??隡箸??典???????/p>';
            } else {
                resultsDiv.innerHTML += '<p style=\"color: orange;\">?? 隡箸??冽???閮</p>';
            }
            
            // 瑼Ｘ蝯梯?鞈?
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const stats = doc.querySelectorAll('.stat-value-horizontal');
            
            if (stats.length >= 3) {
                resultsDiv.innerHTML += '<p style=\"color: green;\">???曉蝯梯?鞈??? (' + stats.length + ' ??</p>';
                stats.forEach((stat, index) => {
                    resultsDiv.innerHTML += '<p>蝯梯? ' + (index + 1) + ': ' + stat.textContent + '</p>';
                });
            } else {
                resultsDiv.innerHTML += '<p style=\"color: red;\">???曆??啁絞閮???蝝?/p>';
            }
        }
        
    } catch (error) {
        resultsDiv.innerHTML += '<p style=\"color: red;\">??AJAX 皜祈岫憭望?: ' + error.message + '</p>';
        console.error('皜祈岫?航炊:', error);
    }
}

// ?芸??瑁?皜祈岫
setTimeout(testAjaxSubmission, 1000);
</script>";

echo "<h2>4. 瑼Ｘ PHP ???摩</h2>";

echo "<h3>銵典??瑼Ｘ</h3>";

// 瑼Ｘ?臬??POST ???摩
if (strpos($file_content, '$_SERVER[\'REQUEST_METHOD\'] === \'POST\'') !== false) {
    echo "<p style='color: green;'>????POST ???摩</p>";
    
    // ?? POST ???典?
    $post_start = strpos($file_content, '$_SERVER[\'REQUEST_METHOD\'] === \'POST\'');
    $post_end = strpos($file_content, '}', $post_start);
    $post_code = substr($file_content, $post_start, $post_end - $post_start + 1);
    
    echo "<h4>POST ??蝔?蝣潘?</h4>";
    echo "<pre>" . htmlspecialchars($post_code) . "</pre>";
} else {
    echo "<p style='color: red;'>??瘝? POST ???摩</p>";
}

echo "<h3>瑼Ｘ鞈??脣??賣</h3>";

// 瑼Ｘ save_daily_sales_with_role ?賣
if (function_exists('save_daily_sales_with_role')) {
    echo "<p style='color: green;'>??save_daily_sales_with_role ?賣摮</p>";
} else {
    echo "<p style='color: red;'>??save_daily_sales_with_role ?賣銝???/p>";
}

echo "<h2>5. 撖阡?鞈?皜祈岫</h2>";

$today = date('Y-m-d');
$store_code = '277';
$test_amount = 8888;
$test_role = 'main';

echo "<p>皜祈岫?交?: {$today}</p>";
echo "<p>皜祈岫摨?: {$store_code}</p>";
echo "<p>皜祈岫??: {$test_amount}</p>";
echo "<p>皜祈岫閫: {$test_role}</p>";

// 皜祈岫?脣??
if (function_exists('save_daily_sales_with_role')) {
    echo "<h3>皜祈岫鞈??脣?</h3>";
    
    // ???????    $month = substr($today, 0, 7);
    $sales_data = load_monthly_sales($month);
    
    echo "<p>?脣?????</p>";
    if (isset($sales_data[$today][$store_code])) {
        echo "<pre>" . print_r($sales_data[$today][$store_code], true) . "</pre>";
    } else {
        echo "<p>?嗅予???平蝮曇???/p>";
    }
    
    // 皜祈岫?脣?
    $result = save_daily_sales_with_role($today, $store_code, $test_amount, $test_role);
    
    if ($result) {
        echo "<p style='color: green;'>??鞈??脣???</p>";
        
        // ?霈??霅?        $sales_data = load_monthly_sales($month);
        
        if (isset($sales_data[$today][$store_code])) {
            $saved_data = $sales_data[$today][$store_code];
            echo "<p>?脣?敺?鞈?嚗?/p>";
            echo "<pre>" . print_r($saved_data, true) . "</pre>";
            
            // 瑼Ｘ?臬???脰?閮?            if (isset($saved_data['role'])) {
                echo "<p style='color: green;'>??閫鞈?撌脣摮? " . $saved_data['role'] . "</p>";
            } else {
                echo "<p style='color: red;'>??閫鞈??芸摮?/p>";
            }
        }
    } else {
        echo "<p style='color: red;'>??鞈??脣?憭望?</p>";
    }
}

echo "<h2>6. ?????圾瘙箸獢?/h2>";

echo "<h3>?航??嚗?/h3>";
echo "<ol>";
echo "<li><strong>JavaScript ?航炊</strong>嚗炎?亦汗??Console ?臬?隤?/li>";
echo "<li><strong>AJAX 隢?憭望?</strong>嚗炎??Network 璅惜銝剔?隢????/li>";
echo "<li><strong>PHP ???航炊</strong>嚗炎??PHP ?航炊?亥?</li>";
echo "<li><strong>CSS ?豢??典?憿?/strong>嚗絞閮??? CSS ?豢??典?賡隤?/li>";
echo "<li><strong>敹怠???</strong>嚗汗?典?賢翰???? JavaScript</li>";
echo "</ol>";

echo "<h3>蝡閫?捱?寞?嚗?/h3>";
echo "<ol>";
echo "<li><strong>皜?汗?典翰??/strong>嚗trl+Shift+Delete ??Ctrl+F5</li>";
echo "<li><strong>瑼Ｘ JavaScript ?航炊</strong>嚗? F12 ??Console 璅惜</li>";
echo "<li><strong>瑼Ｘ AJAX 隢?</strong>嚗? F12 ??Network 璅惜嚗??唳?鈭方”??/li>";
echo "<li><strong>雿輻?喟絞銵典?漱</strong>嚗?敺拙蝯望?鈭斗撘葫閰?/li>";
</ol>";

echo "<h2>7. 撱箇?蝪∪?皜祈岫?</h2>";

echo "<p><button onclick='createSimpleVersion()'>撱箇?蝪∪?皜祈岫?</button></p>";

echo "<script>
function createSimpleVersion() {
    // 撱箇?蝪∪????store_dashboard.php
    const simpleVersion = `
<?php
// 蝪∪?? store_dashboard.php - ?喟絞銵典?漱
session_start();
require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/auth.php';

// 璅⊥?餃
\$_SESSION['user_id'] = '277';
\$_SESSION['username'] = '277';
\$_SESSION['name'] = '277敺抵?摨?;
\$_SESSION['role'] = 'store';
\$_SESSION['stores'] = ['277'];
\$_SESSION['logged_in'] = true;

\$user = get_current_session_user();
\$store_code = '277';
\$today = date('Y-m-d');

// ???喟絞銵典?漱
if (\$_SERVER['REQUEST_METHOD'] === 'POST' && isset(\$_POST['amount']) && isset(\$_POST['selected_role'])) {
    \$amount = (int)\$_POST['amount'];
    \$role = \$_POST['selected_role'];
    
    if (\$amount > 0 && in_array(\$role, ['main', 'substitute'])) {
        \$result = save_daily_sales_with_role(\$today, \$store_code, \$amount, \$role);
        
        if (\$result) {
            \$success_message = '璆剔蜀?餅???嚗?憿? NT\$ ' . number_format(\$amount);
            // ?撠??啣?銝?嚗蝯望撘?
            header('Location: ?success=' . urlencode(\$success_message));
            exit;
        }
    }
}

// 頛璆剔蜀鞈?
\$sales_summary = load_monthly_sales(date('Y-m'));
\$today_sales = \$sales_summary[\$today] ?? [];
\$today_amount = isset(\$today_sales[\$store_code]) ? \$today_sales[\$store_code]['amount'] ?? 0 : 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>蝪∪?皜祈岫 - 摨??銵冽</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .stat { background: #f0f0f0; padding: 20px; margin: 10px; border-radius: 5px; }
        .stat-value { font-size: 24px; font-weight: bold; color: #007bff; }
        .input-section { margin: 30px 0; }
        .role-btn { padding: 15px 30px; margin: 5px; font-size: 18px; }
        .amount-input { padding: 10px; font-size: 18px; width: 200px; }
        .submit-btn { padding: 15px 30px; background: #28a745; color: white; border: none; font-size: 18px; cursor: pointer; }
        .success { background: #d4edda; color: #155724; padding: 10px; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>蝪∪?皜祈岫 - 摨??銵冽</h1>
    
    <?php if (isset(\$_GET['success'])): ?>
        <div class=\"success\"><?php echo htmlspecialchars(\$_GET['success']); ?></div>
    <?php endif; ?>
    
    <div class=\"stat\">
        <h3>隞璆剔蜀</h3>
        <div class=\"stat-value\">NT\$ <?php echo number_format(\$today_amount); ?></div>
    </div>
    
    <div class=\"input-section\">
        <h2>隞璆剔蜀?餅?</h2>
        <p>隞?交?: <?php echo \$today; ?></p>
        
        <form method=\"POST\" action=\"\">
            <div>
                <button type=\"button\" class=\"role-btn\" onclick=\"selectRole('main')\">銝餅?</button>
                <button type=\"button\" class=\"role-btn\" onclick=\"selectRole('substitute')\">隞?</button>
                <input type=\"hidden\" name=\"selected_role\" id=\"selected-role\" value=\"\">
            </div>
            
            <div style=\"margin: 20px 0;\">
                <input type=\"number\" name=\"amount\" class=\"amount-input\" placeholder=\"頛詨璆剔蜀??\" min=\"0\" required>
            </div>
            
            <div>
                <button type=\"submit\" class=\"submit-btn\" id=\"submit-btn\" disabled>?餅?璆剔蜀</button>
            </div>
        </form>
    </div>
    
    <script>
        let selectedRole = '';
        
        function selectRole(role) {
            selectedRole = role;
            document.getElementById('selected-role').value = role;
            document.getElementById('submit-btn').disabled = false;
            document.getElementById('submit-btn').style.backgroundColor = '#28a745';
            
            // 閬死??
            document.querySelectorAll('.role-btn').forEach(btn => {
                btn.style.backgroundColor = btn.textContent.includes(role === 'main' ? '銝餅?' : '隞?') ? '#4CAF50' : '#6c757d';
                btn.style.color = 'white';
            });
        }
        
        // 瑼Ｘ銵典?漱
        document.querySelector('form').addEventListener('submit', function(e) {
            if (!selectedRole) {
                e.preventDefault();
                alert('隢?蜓瑹?隞?閫嚗?);
                return false;
            }
            
            const amountInput = document.querySelector('[name=\"amount\"]');
            if (!amountInput.value || amountInput.value <= 0) {
                e.preventDefault();
                alert('隢撓?交???璆剔蜀??嚗?);
                return false;
            }
            
            // ?喟絞銵典?漱嚗??閬?AJAX
            return true;
        });
    </script>
</body>
</html>`;

    // 銝?瑼?
    const blob = new Blob([simple
