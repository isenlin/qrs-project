<?php
/**
 * 摨?撠?銵冽
 * - 璈怠?憿舐內璆剔蜀蝯梯?
 * - ?湔?餅??嗅予璆剔蜀
 * - 銝＊蝷箄?鞎砍?瑹?銵? * - ?芸???
 */

// ?? Session
session_start();

// ?航炊?勗?
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once __DIR__ . '/config/settings.php';
    require_once __DIR__ . '/config/auth.php';
} catch (Exception $e) {
    die("頛閮剖?瑼仃?? " . $e->getMessage());
}

// ?閬?瑹???if (!is_logged_in()) {
    header('Location: index.php');
    exit;
}

$user = get_current_session_user();

// 瑼Ｘ甈?嚗??摨?
if ($user['role'] !== 'store') {
    header('Location: dashboard.php');
    exit;
}

// 頛摨?鞈?
try {
    $stores = load_data('stores');
    
    // 頛?祆?璆剔蜀鞈?
    $current_month = date('Y-m');
    $sales_summary = load_monthly_sales($current_month);
} catch (Exception $e) {
    die("頛鞈?憭望?: " . $e->getMessage());
}

// ??摨?鈭箏??瑹?$user_stores = $user['stores'] ?? [];
$store_code = !empty($user_stores) ? $user_stores[0] : '';

// ??摨?鞈?
$store_info = [];
foreach ($stores as $store) {
    if ($store['code'] === $store_code) {
        $store_info = $store;
        break;
    }
}

// 閮?璆剔蜀蝯梯?
$today = date('Y-m-d');
$today_sales = $sales_summary[$today] ?? [];

// 隞璆剔蜀
$today_amount = 0;
if (!empty($store_code) && isset($today_sales[$store_code])) {
    $today_amount = $today_sales[$store_code]['amount'] ?? 0;
}

// ?芸???嚗?5??嚗?$refresh_interval = 300; // 5??

// ??璆剔蜀?餅?嚗??怨??脤??
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['amount']) && isset($_POST['selected_role'])) {
    $amount = (int)$_POST['amount'];
    $role = $_POST['selected_role']; // 'main' ??'substitute'
    
    if ($amount >= 0 && in_array($role, ['main', 'substitute'])) {
        // 雿輻?啁????脣??孵?嚗??怨??脰?閮?        $result = save_daily_sales_with_role($today, $store_code, $amount, $role);
        
        if ($result) {
            // ?頛?祆?璆剔蜀鞈?
            $sales_summary = load_monthly_sales($current_month);
            $today_amount = $amount;
            $today_role = $role;
            
            // 憿舐內??閮嚗?蝘??芸?瘨仃嚗?            $role_text = $role === 'main' ? '銝餅?' : '隞?';
            $success_message = '??' . $role_text . '璆剔蜀?餅???嚗?憿? NT$ ' . number_format($amount);
        } else {
            $success_message = '??璆剔蜀?脣?憭望?嚗?蝔??岫';
        }
    } else {
        $success_message = '??隢???脖蒂頛詨????憿?;
    }
}

// 瑼Ｘ隞?臬撌脫?璆剔蜀鞈?
$has_today_sales = false;
$today_role = '';
if (!empty($store_code) && isset($today_sales[$store_code])) {
    $has_today_sales = true;
    $today_amount = $today_sales[$store_code]['amount'] ?? 0;
    $today_role = $today_sales[$store_code]['role'] ?? ''; // 霈???脰?閮?}

// 閮??祆?璆剔蜀嚗??銵典??敺?蝞?
$current_month = date('Y-m');
$month_total = 0;
$month_days = 0;
foreach ($sales_summary as $date => $daily_sales) {
    if (strpos($date, $current_month) === 0) {
        $month_days++;
        if (isset($daily_sales[$store_code])) {
            $month_total += $daily_sales[$store_code]['amount'] ?? 0;
        }
    }
}

$month_avg = $month_days > 0 ? round($month_total / $month_days, 2) : 0;
?>
<!DOCTYPE html>
<html lang='zh-TW'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>摨?璆剔蜀?餅? - <?php echo htmlspecialchars($store_info['name'] ?? '摨?'); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .store-dashboard {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .store-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #007bff;
        }
        
        .store-info h1 {
            margin: 0;
            color: #333;
        }
        
        .store-code {
            font-size: 18px;
            color: #666;
            margin-top: 5px;
        }
        
        .user-info {
            text-align: right;
        }
        
        .stats-horizontal {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card-horizontal {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
            border: 1px solid #e9ecef;
        }
        
        .stat-card-horizontal h3 {
            margin: 0 0 15px 0;
            color: #495057;
            font-size: 18px;
        }
        
        .stat-value-horizontal {
            font-size: 32px;
            font-weight: bold;
            color: #007bff;
            margin: 10px 0;
        }
        
        .stat-date-horizontal {
            color: #6c757d;
            font-size: 14px;
            margin: 0;
        }
        
        .input-section {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .input-section h2 {
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #28a745;
            padding-bottom: 10px;
            margin-bottom: 25px;
        }
        
        .today-date {
            font-size: 18px;
            color: #28a745;
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .amount-input {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }
        
        /* 憓之???擃之撠?*/
        .input-section h2 {
            font-size: 28px; /* ??航??4px */
        }
        
        .today-date {
            font-size: 22px; /* ??航??8px */
        }
        
        .amount-input label {
            font-size: 22px; /* ??航??8px */
            font-weight: bold;
            color: #495057;
            margin-bottom: 15px;
        }
        
        .amount-input input {
            font-size: 28px; /* ??航??4px */
            padding: 18px;
            width: 250px;
            text-align: center;
            border: 3px solid #007bff;
            border-radius: 10px;
            outline: none;
            transition: all 0.3s;
        }
        
        .amount-input input:focus {
            border-color: #28a745;
            box-shadow: 0 0 10px rgba(40, 167, 69, 0.3);
        }
        
        /* 銝餅?/隞??豢?璅?? */
        .role-selection {
            margin-bottom: 30px;
            text-align: center;
        }
        
        .role-label {
            font-size: 22px;
            font-weight: bold;
            color: #495057;
            margin-bottom: 20px;
        }
        
        .role-buttons {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 20px;
        }
        
        .role-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 180px;
            height: 80px;
            border: 3px solid #6c757d;
            border-radius: 10px;
            background: white;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 28px;
            font-weight: bold;
            color: #333;
        }
        
        .role-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .role-btn.selected {
            border-color: #28a745;
            background: #e8f5e9;
            color: #155724;
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.2);
        }
        
        /* ??頛詨? */
        .amount-section {
            margin: 30px 0;
            text-align: center;
        }
        
        .submit-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 20px 60px;
            font-size: 24px;
            border-radius: 12px;
            cursor: not-allowed;
            transition: all 0.3s;
            margin-top: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            opacity: 0.6;
        }
        
        .submit-btn.enabled {
            background: #28a745;
            cursor: pointer;
            opacity: 1;
        }
        
        .submit-btn.enabled:hover {
            background: #218838;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(40, 167, 69, 0.3);
        }
        
        .submit-icon {
            font-size: 28px;
        }
        
        .submit-text {
            font-size: 24px;
            font-weight: bold;
        }
        
        /* ???踵?撘身閮?*/
        @media (max-width: 768px) {
            .role-buttons {
                flex-direction: column;
                align-items: center;
                gap: 15px;
            }
            
            .role-btn {
                width: 250px;
                height: 70px;
                font-size: 24px;
            }
            
            .amount-input input {
                width: 90%;
                max-width: 300px;
            }
            
            .submit-btn {
                width: 90%;
                max-width: 300px;
                padding: 18px;
            }
        }
        
        @media (max-width: 480px) {
            .input-section {
                padding: 20px;
            }
            
            .input-section h2 {
                font-size: 24px;
            }
            
            .today-date {
                font-size: 18px;
            }
            
            .role-label {
                font-size: 18px;
            }
            
            .role-btn {
                width: 100%;
                max-width: 280px;
                height: 65px;
                font-size: 22px;
            }
            
            .amount-input label {
                font-size: 18px;
            }
            
            .amount-input input {
                font-size: 24px;
                padding: 15px;
            }
            
            .submit-btn {
                font-size: 20px;
                padding: 15px;
            }
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            text-align: center;
            font-weight: bold;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .auto-refresh {
            text-align: center;
            color: #6c757d;
            font-size: 14px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="store-dashboard">
        <div class="store-header">
            <div class="store-info">
                <h1><?php echo htmlspecialchars($store_info['name'] ?? '摨?璆剔蜀?餅?'); ?></h1>
                <div class="store-code">摨?隞??: <?php echo htmlspecialchars($store_code); ?></div>
            </div>
            <div class="user-info">
                <span>甇∟?嚗??php echo htmlspecialchars($user['name']); ?></span>
                <a href="logout.php" class="btn btn-logout">?餃</a>
            </div>
        </div>

        <!-- 璆剔蜀蝯梯?嚗帖???? -->
        <div class="stats-horizontal">
            <div class="stat-card-horizontal">
                <h3>隞璆剔蜀</h3>
                <p class="stat-value-horizontal">NT$ <?php echo number_format($today_amount); ?></p>
                <p class="stat-date-horizontal"><?php echo $today; ?></p>
            </div>
            <div class="stat-card-horizontal">
                <h3>?祆?蝝航?</h3>
                <p class="stat-value-horizontal">NT$ <?php echo number_format($month_total); ?></p>
                <p class="stat-date-horizontal"><?php echo $current_month; ?> (<?php echo $month_days; ?>憭?</p>
            </div>
            <div class="stat-card-horizontal">
                <h3>?祆??亙?</h3>
                <p class="stat-value-horizontal">NT$ <?php echo number_format($month_avg); ?></p>
                <p class="stat-date-horizontal">撟喳?瘥璆剔蜀</p>
            </div>
        </div>

        <!-- 璆剔蜀?餅??憛?-->
        <div class="input-section">
            <h2>隞璆剔蜀?餅?</h2>
            <div class="today-date">隞?交?: <?php echo $today; ?></div>
            
            <?php if (isset($success_message)): ?>
                <div class="message success" id="success-message">
                    <?php echo $success_message; ?>
                </div>
                <script>
                    setTimeout(function() {
                        const msg = document.getElementById("success-message");
                        if (msg) msg.style.display = "none";
                    }, 3000);
                </script>
            <?php endif; ?>
            
            <form method="post" class="amount-input" id="sales-form">
                <!-- 銝餅?/隞??豢? -->
                <div class="role-selection">
                    <div class="role-label">隢????莎?</div>
                    <div class="role-buttons">
                        <button type="button" class="role-btn" data-role="main" id="main-btn">
                            銝餅?
                        </button>
                        <button type="button" class="role-btn" data-role="substitute" id="substitute-btn">
                            隞?
                        </button>
                    </div>
                    <input type="hidden" name="role" id="role-input" value="">
                </div>
                
                <!-- ??頛詨 -->
                <div class="amount-section">
                    <label for="amount">隢撓?乩??交平蝮暸?憿?</label>
                    <input type="number" 
                           id="amount" 
                           name="amount" 
                           value="<?php echo $today_amount; ?>" 
                           min="0" 
                           step="1" 
                           required 
                           placeholder="頛詨??">
                </div>
                
                <!-- ?漱?? -->
                <button type="button" class="submit-btn" id="submit-btn" disabled onclick="submitSalesForm(event)">
                    <span class="submit-icon">??</span>
                    <span class="submit-text">?餅?璆剔蜀</span>
                </button>
                
                <!-- ?梯?甈?嚗????閫 -->
                <input type="hidden" name="selected_role" id="selected-role" value="">
            </form>
            
            <div class="auto-refresh">
                ???撠 <span id="countdown"><?php echo $refresh_interval; ?></span> 蝘??芸??
            </div>
        </div>
    </div>

    <script>
        // ?芸???閮?
        let countdown = <?php echo $refresh_interval; ?>;
        const countdownElement = document.getElementById('countdown');
        
        const countdownTimer = setInterval(() => {
            countdown--;
            countdownElement.textContent = countdown;
            
            if (countdown <= 0) {
                clearInterval(countdownTimer);
                location.reload();
            }
        }, 1000);
        
        // 蝘駁?ａ??蝣箄?嚗??瘙?
        
        // 銝餅?/隞????豢??
        document.addEventListener('DOMContentLoaded', function() {
            const mainBtn = document.getElementById('main-btn');
            const substituteBtn = document.getElementById('substitute-btn');
            const submitBtn = document.getElementById('submit-btn');
            const roleInput = document.getElementById('selected-role');
            const amountInput = document.getElementById('amount');
            
            let selectedRole = '';
            
            // 瑼Ｘ?臬?歇?脣????脤??            const savedRole = '<?php echo isset($_POST['role']) ? $_POST['role'] : ''; ?>';
            if (savedRole === 'main') {
                selectRole('main');
            } else if (savedRole === 'substitute') {
                selectRole('substitute');
            }
            
            // 瑼Ｘ?臬???交平蝮曇???            const hasTodaySales = <?php echo $has_today_sales ? 'true' : 'false'; ?>;
            if (hasTodaySales) {
                // 憒????交平蝮橘?敺??澈霈????                const todayRole = '<?php echo $today_role ?? ''; ?>';
                if (todayRole === 'main') {
                    selectRole('main');
                } else if (todayRole === 'substitute') {
                    selectRole('substitute');
                }
                
                // ??漱??
                if (selectedRole && amountInput.value) {
                    enableSubmit();
                }
            }
            
            // 銝餅???暺?鈭辣
            mainBtn.addEventListener('click', function() {
                selectRole('main');
            });
            
            // 隞???暺?鈭辣
            substituteBtn.addEventListener('click', function() {
                selectRole('substitute');
            });
            
            // ??頛詨霈?鈭辣
            amountInput.addEventListener('input', function() {
                checkFormValidity();
            });
            
            // ?豢?閫?賣
            function selectRole(role) {
                // 蝘駁??????訾葉???                mainBtn.classList.remove('selected');
                substituteBtn.classList.remove('selected');
                
                // 閮剖??訾葉????                if (role === 'main') {
                    mainBtn.classList.add('selected');
                    selectedRole = 'main';
                } else if (role === 'substitute') {
                    substituteBtn.classList.add('selected');
                    selectedRole = 'substitute';
                }
                
                // ?湔?梯?甈?
                roleInput.value = selectedRole;
                
                // 瑼Ｘ銵典????                checkFormValidity();
                
                // ??圈?憿撓?交?
                amountInput.focus();
                amountInput.select();
            }
            
            // 瑼Ｘ銵典????            function checkFormValidity() {
                if (selectedRole && amountInput.value) {
                    enableSubmit();
                } else {
                    disableSubmit();
                }
            }
            
            // ??漱??
            function enableSubmit() {
                submitBtn.disabled = false;
                submitBtn.classList.add('enabled');
            }
            
            // 蝳?漱??
            function disableSubmit() {
                submitBtn.disabled = true;
                submitBtn.classList.remove('enabled');
            }
            
            // 銵典?漱鈭辣嚗??AJAX嚗?            document.getElementById('sales-form').addEventListener('submit', function(e) {
                // ??隞嗥?函 submitSalesForm ?賣??
                // 靽?蝛綽??踹?????
            });
            
            // ?芸???啗撓?交?
            if (amountInput) {
                amountInput.focus();
                amountInput.select();
            }
        });
        
        // AJAX ?漱銵典銝行?啁絞閮?        function submitSalesForm(event) {
            event.preventDefault();
            
            const form = document.getElementById('sales-form');
            const formData = new FormData(form);
            
            // 瑼Ｘ銵典????            const selectedRole = document.getElementById('selected-role').value;
            const amountInput = document.getElementById('amount');
            const submitBtn = document.getElementById('submit-btn');
            
            if (!selectedRole) {
                alert('隢?蜓瑹?隞?閫嚗?);
                return false;
            }
            
            if (!amountInput.value || amountInput.value <= 0) {
                alert('隢撓?交???璆剔蜀??嚗?);
                return false;
            }
            
            // 憿舐內頛???            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="submit-icon">??/span><span class="submit-text">??銝?..</span>';
            submitBtn.disabled = true;
            
            // ?潮?AJAX 隢?
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(html => {
                // 敺??葉???湔敺?蝯梯?鞈?
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // ?湔隞璆剔蜀
                const newTodayAmount = doc.querySelector('.stat-card-horizontal:nth-child(1) .stat-value-horizontal');
                if (newTodayAmount) {
                    document.querySelector('.stat-card-horizontal:nth-child(1) .stat-value-horizontal').innerHTML = newTodayAmount.innerHTML;
                }
                
                // ?湔?祆?蝝航?
                const newMonthTotal = doc.querySelector('.stat-card-horizontal:nth-child(2) .stat-value-horizontal');
                if (newMonthTotal) {
                    document.querySelector('.stat-card-horizontal:nth-child(2) .stat-value-horizontal').innerHTML = newMonthTotal.innerHTML;
                }
                
                // ?湔?祆??亙?
                const newMonthAvg = doc.querySelector('.stat-card-horizontal:nth-child(3) .stat-value-horizontal');
                if (newMonthAvg) {
                    document.querySelector('.stat-card-horizontal:nth-child(3) .stat-value-horizontal').innerHTML = newMonthAvg.innerHTML;
                }
                
                // 憿舐內??閮
                const successMessage = doc.querySelector('.message.success');
                if (successMessage) {
                    // 蝘駁?暹???????                    const existingMessage = document.querySelector('.message.success');
                    if (existingMessage) {
                        existingMessage.remove();
                    }
                    
                    // 瘛餃??啁???閮
                    const inputSection = document.querySelector('.input-section');
                    const formElement = document.querySelector('.amount-input');
                    const clonedMessage = successMessage.cloneNode(true);
                    inputSection.insertBefore(clonedMessage, formElement);
                    
                    // 3蝘??芸??梯?
                    setTimeout(() => {
                        if (clonedMessage.parentNode) {
                            clonedMessage.style.display = 'none';
                        }
                    }, 3000);
                }
                
                // ?蔭?????- 靽??????閫撌脤??
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                submitBtn.classList.add('enabled');
                
                // ?蔭銵典嚗????脤??皜征??嚗?                amountInput.value = '';
                amountInput.focus();
                
                // 憿舐內???內
                console.log('璆剔蜀?餅???嚗絞閮歇?湔');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('璆剔蜀?餅?憭望?嚗?蝔??岫');
                
                // ?Ｗ儔?????                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                if (selectedRole && amountInput.value) {
                    submitBtn.classList.add('enabled');
                }
            });
            
            return false;
        }
    </script>
</body>
</html>
