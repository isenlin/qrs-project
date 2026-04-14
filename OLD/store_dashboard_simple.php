<?php
/**
 * 蝪∪?? store_dashboard.php - 雿輻?喟絞銵典?漱
 * 閫?捱 AJAX ??嚗撓?交?雿?蝛箇?絞閮??湔
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
    $stores = load_stores();
} catch (Exception $e) {
    die("頛摨?鞈?憭望?: " . $e->getMessage());
}

// ??雿輻??鞎祉?摨?嚗?瑹犖?∪????瑹?
$user_stores = $user['stores'] ?? [];
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
$current_month = date('Y-m');

// 頛?祆?璆剔蜀鞈?
$sales_summary = load_monthly_sales($current_month);
$today_sales = $sales_summary[$today] ?? [];

// 隞璆剔蜀
$today_amount = 0;
if (!empty($store_code) && isset($today_sales[$store_code])) {
    $today_amount = $today_sales[$store_code]['amount'] ?? 0;
}

// ?祆?蝝航?
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

// ?祆??亙?
$month_avg = $month_days > 0 ? round($month_total / $month_days) : 0;

// ?芸???嚗?5??嚗?$refresh_interval = 300; // 5??

// ??璆剔蜀?餅?嚗蝯梯”?格?鈭歹?
$success_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['amount']) && isset($_POST['selected_role'])) {
    $amount = (int)$_POST['amount'];
    $role = $_POST['selected_role']; // 'main' ??'substitute'
    
    if ($amount >= 0 && in_array($role, ['main', 'substitute'])) {
        // 雿輻?啁????脣??孵?嚗??怨??脰?閮?        $result = save_daily_sales_with_role($today, $store_code, $amount, $role);
        
        if ($result) {
            // ?頛?祆?璆剔蜀鞈?
            $sales_summary = load_monthly_sales($current_month);
            $today_sales = $sales_summary[$today] ?? [];
            
            // ?閮?蝯梯?
            $today_amount = 0;
            if (!empty($store_code) && isset($today_sales[$store_code])) {
                $today_amount = $today_sales[$store_code]['amount'] ?? 0;
            }
            
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
            $month_avg = $month_days > 0 ? round($month_total / $month_days) : 0;
            
            $success_message = '璆剔蜀?餅???嚗?憿? NT$ ' . number_format($amount) . ' (' . ($role === 'main' ? '銝餅?' : '隞?') . ')';
        } else {
            $success_message = '璆剔蜀?餅?憭望?嚗?蝔??岫';
        }
    }
}

// 瑼Ｘ?嗅予?臬撌脩??璆剔蜀
$has_today_sales = !empty($store_code) && isset($today_sales[$store_code]);
$today_role = $has_today_sales ? ($today_sales[$store_code]['role'] ?? 'main') : '';
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>摨??銵冽 - <?php echo htmlspecialchars($store_info['name'] ?? $store_code); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }
        
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
            font-size: 28px;
        }
        
        .store-code {
            font-size: 18px;
            color: #666;
            margin-top: 5px;
        }
        
        .logout-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
        }
        
        .logout-btn:hover {
            background: #c82333;
        }
        
        .stats-container {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card-horizontal {
            flex: 1;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card-horizontal h3 {
            color: #666;
            font-size: 18px;
            margin-bottom: 15px;
        }
        
        .stat-value-horizontal {
            font-size: 36px;
            font-weight: bold;
            color: #007bff;
            margin: 10px 0;
        }
        
        .stat-date-horizontal {
            color: #999;
            font-size: 14px;
        }
        
        .input-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .input-section h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
        }
        
        .today-date {
            font-size: 18px;
            color: #666;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 16px;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .role-selection {
            margin-bottom: 25px;
        }
        
        .role-selection h3 {
            margin-bottom: 15px;
            color: #555;
            font-size: 18px;
        }
        
        .role-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
        }
        
        .role-btn {
            flex: 1;
            max-width: 250px;
            padding: 25px 15px;
            border: 3px solid #ddd;
            border-radius: 10px;
            background: white;
            color: #666;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .role-btn:hover {
            border-color: #007bff;
            color: #007bff;
        }
        
        .role-btn.selected {
            border-color: #28a745;
            background: #f0fff4;
            color: #28a745;
        }
        
        .amount-input {
            margin-bottom: 25px;
        }
        
        .amount-input h3 {
            margin-bottom: 15px;
            color: #555;
            font-size: 18px;
        }
        
        .amount-input input {
            width: 100%;
            max-width: 300px;
            padding: 20px;
            font-size: 24px;
            border: 2px solid #ddd;
            border-radius: 8px;
            text-align: center;
            margin: 0 auto;
            display: block;
        }
        
        .amount-input input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.25);
        }
        
        .submit-btn {
            width: 100%;
            max-width: 300px;
            padding: 20px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 0 auto;
            display: block;
        }
        
        .submit-btn.enabled {
            background: #28a745;
            cursor: pointer;
        }
        
        .submit-btn.enabled:hover {
            background: #218838;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(40, 167, 69, 0.3);
        }
        
        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .submit-icon {
            margin-right: 10px;
        }
        
        .already-logged {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            border: 1px solid #ffeaa7;
            text-align: center;
        }
        
        .already-logged strong {
            display: block;
            margin-bottom: 5px;
            font-size: 18px;
        }
        
        /* ???踵?撘身閮?*/
        @media (max-width: 768px) {
            .store-dashboard {
                padding: 15px;
            }
            
            .store-header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .stats-container {
                flex-direction: column;
                gap: 15px;
            }
            
            .stat-card-horizontal {
                padding: 20px;
            }
            
            .stat-value-horizontal {
                font-size: 32px;
            }
            
            .input-section {
                padding: 20px;
            }
            
            .role-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .role-btn {
                width: 100%;
                max-width: 300px;
                padding: 20px;
                font-size: 24px;
            }
            
            .amount-input input {
                max-width: 100%;
                padding: 18px;
                font-size: 22px;
            }
            
            .submit-btn {
                max-width: 100%;
                padding: 18px;
                font-size: 22px;
            }
        }
        
        @media (max-width: 480px) {
            .store-info h1 {
                font-size: 24px;
            }
            
            .stat-value-horizontal {
                font-size: 28px;
            }
            
            .role-btn {
                padding: 18px;
                font-size: 22px;
            }
            
            .amount-input input {
                padding: 16px;
                font-size: 20px;
            }
            
            .submit-btn {
                padding: 16px;
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="store-dashboard">
        <!-- 摨?璅 -->
        <div class="store-header">
            <div class="store-info">
                <h1><?php echo htmlspecialchars($store_info['name'] ?? $store_code); ?></h1>
                <div class="store-code">摨?隞??: <?php echo htmlspecialchars($store_code); ?></div>
            </div>
            <a href="logout.php" class="logout-btn">?餃</a>
        </div>
        
        <!-- 璆剔蜀蝯梯??憛?-->
        <div class="stats-container">
            <div class="stat-card-horizontal">
                <h3>隞璆剔蜀</h3>
                <p class="stat-value-horizontal">NT$ <?php echo number_format($today_amount); ?></p>
                <p class="stat-date-horizontal">隞蝝航?</p>
            </div>
            <div class="stat-card-horizontal">
                <h3>?祆?蝝航?</h3>
                <p class="stat-value-horizontal">NT$ <?php echo number_format($month_total); ?></p>
                <p class="stat-date-horizontal">?祆??喃?</p>
            </div>
            <div class="stat-card-horizontal">
                <h3>?祆??亙?</h3>
                <p class="stat-value-horizontal">NT$ <?php echo number_format($month_avg); ?></p>
                <p class="stat-date-horizontal">撟喳?瘥</p>
            </div>
        </div>
        
        <!-- 璆剔蜀?餅??憛?-->
        <div class="input-section">
            <h2>隞璆剔蜀?餅?</h2>
            <div class="today-date">隞?交?: <?php echo $today; ?></div>
            
            <?php if ($success_message): ?>
                <div class="message success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($has_today_sales): ?>
                <div class="already-logged">
                    <strong>?? 隞撌脩??璆剔蜀</strong>
                    <p>??: NT$ <?php echo number_format($today_amount); ?> | 閫: <?php echo $today_role === 'main' ? '銝餅?' : '隞?'; ?></p>
                    <p>?典隞仿??啁??唳平蝮?/p>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="sales-form">
                <!-- 閫?豢? -->
                <div class="role-selection">
                    <h3>隢???莎?</h3>
                    <div class="role-buttons">
                        <button type="button" class="role-btn" id="main-btn" onclick="selectRole('main')">銝餅?</button>
                        <button type="button" class="role-btn" id="substitute-btn" onclick="selectRole('substitute')">隞?</button>
                    </div>
                    <input type="hidden" name="selected_role" id="selected-role" value="">
                </div>
                
                <!-- ??頛詨 -->
                <div class="amount-input">
                    <h3>頛詨璆剔蜀??嚗?/h3>
                    <input type="number" name="amount" id="amount" placeholder="頛詨??" min="0" required autofocus>
                </div>
                
                <!-- ?漱?? -->
                <button type="submit" class="submit-btn" id="submit-btn" disabled>
                    <span class="submit-icon">?</span>
                    <span class="submit-text">?餅?璆剔蜀</span>
                </button>
            </form>
        </div>
    </div>
    
    <script>
        // 閫?豢?
        let selectedRole = '';
        
        function selectRole(role) {
            selectedRole = role;
            
            // ?湔?梯?甈?
            document.getElementById('selected-role').value = role;
            
            // ?湔??璅??
            const mainBtn = document.getElementById('main-btn');
            const substituteBtn = document.getElementById('substitute-btn');
            const submitBtn = document.getElementById('submit-btn');
            
            if (role === 'main') {
                mainBtn.classList.add('selected');
                substituteBtn.classList.remove('selected');
            } else {
                mainBtn.classList.remove('selected');
                substituteBtn.classList.add('selected');
            }
            
            // ??漱??
            submitBtn.disabled = false;
            submitBtn.classList.add('enabled');
            
            // ??圈?憿撓?交?
            document.getElementById('amount').focus();
        }
        
        // ??頛詨?炎??        document.getElementById('amount').addEventListener('input', function() {
            const amount = this.value;
            const submitBtn = document.getElementById('submit-btn');
            
            if (selectedRole && amount && amount > 0) {
                submitBtn.disabled = false;
                submitBtn.classList.add('enabled');
            } else {
                submitBtn.disabled = true;
                submitBtn.classList.remove('enabled');
            }
        });
        
        // 銵典?漱?炎??        document.getElementById('sales-form').addEventListener('submit', function(event) {
            const amountInput = document.getElementById('amount');
            const amount = amountInput.value;
            
            if (!selectedRole) {
                event.preventDefault();
                alert('隢?蜓瑹?隞?閫嚗?);
                return false;
            }
            
            if (!amount || amount <= 0) {
                event.preventDefault();
                alert('隢撓?交???璆剔蜀??嚗?);
                return false;
            }
            
            // 憿舐內??銝剔???            const submitBtn = document.getElementById('submit-btn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="submit-icon">??/span><span class="submit-text">??銝?..</span>';
            submitBtn.disabled = true;
            
            // ?喟絞銵典?漱嚗??閬?AJAX
            return true;
        });
        
        // ?頛?炎?交?行?撌脤??閫嚗?撌脩??鞈?嚗?        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($has_today_sales && $today_role): ?>
                // ?芸??豢?撌脩??閫
                selectRole('<?php echo $today_role; ?>');
                
                // 閮剖???
                const amountInput = document.getElementById('amount');
                amountInput.value = <?php echo $today_amount; ?>;
                amountInput.focus();
                amountInput.select();
            <?php endif; ?>
            
            // ?芸???嚗?5??嚗?            setTimeout(function() {
                window.location.reload();
            }, <?php echo $refresh_interval * 1000; ?>);
        });
    </script>
</body>
</html>
