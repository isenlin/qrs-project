<?php
/**
 * 簡化版本 store_dashboard.php - 使用傳統表單提交
 * 解決 AJAX 問題：輸入欄位變空白、統計不更新
 */

// 啟動 Session
session_start();

// 錯誤報告
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once __DIR__ . '/config/settings.php';
    require_once __DIR__ . '/config/auth_simple.php';
} catch (Exception $e) {
    die("載入設定檔失敗: " . $e->getMessage());
}

// 需要店櫃權限
if (!is_logged_in()) {
    header('Location: index.php');
    exit;
}

$user = get_current_session_user();

// 檢查權限：必須是店櫃
if ($user['role'] !== 'store') {
    header('Location: dashboard.php');
    exit;
}

// 載入店櫃資料
try {
    $stores = load_data('stores');
} catch (Exception $e) {
    die("載入店櫃資料失敗: " . $e->getMessage());
}

// 取得使用者負責的店櫃（店櫃人員只有一個店櫃）
$user_stores = $user['stores'] ?? [];
$store_code = !empty($user_stores) ? $user_stores[0] : '';

// 取得店櫃資訊
$store_info = [];
foreach ($stores as $store) {
    if ($store['code'] === $store_code) {
        $store_info = $store;
        break;
    }
}

// 計算業績統計
$today = date('Y-m-d');
$current_month = date('Y-m');

// 載入本月業績資料
$sales_summary = load_monthly_sales($current_month);
$today_sales = $sales_summary[$today] ?? [];

// 今日業績
$today_amount = 0;
if (!empty($store_code) && isset($today_sales[$store_code])) {
    $today_amount = $today_sales[$store_code]['amount'] ?? 0;
}

// 本月累計
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

// 本月日均
$month_avg = $month_days > 0 ? round($month_total / $month_days) : 0;

// 自動重整頁面（每5分鐘）
$refresh_interval = 300; // 5分鐘

// 處理業績登打（傳統表單提交）
$success_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['amount']) && isset($_POST['selected_role'])) {
    $amount = (int)$_POST['amount'];
    $role = $_POST['selected_role']; // 'main' 或 'substitute'
    
    if ($amount >= 0 && in_array($role, ['main', 'substitute'])) {
        // 使用新的按月儲存方式，包含角色資訊
        $result = save_daily_sales_with_role($today, $store_code, $amount, $role);
        
        if ($result) {
            // 重新載入本月業績資料
            $sales_summary = load_monthly_sales($current_month);
            $today_sales = $sales_summary[$today] ?? [];
            
            // 重新計算統計
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
            
            $success_message = '業績登打成功！金額: NT$ ' . number_format($amount) . ' (' . ($role === 'main' ? '主櫃' : '代班') . ')';
        } else {
            $success_message = '業績登打失敗，請稍後再試';
        }
    }
}

// 檢查當天是否已登打過業績
$has_today_sales = !empty($store_code) && isset($today_sales[$store_code]);
$today_role = $has_today_sales ? ($today_sales[$store_code]['role'] ?? 'main') : '';
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>店櫃儀表板 - <?php echo htmlspecialchars($store_info['name'] ?? $store_code); ?></title>
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
        
        .refresh-btn {
            background: #17a2b8;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
        }
        
        .refresh-btn:hover {
            background: #138496;
        }
        

        
        .header-buttons {
            display: flex;
            align-items: center;
            gap: 10px;
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
            font-size: 50px !important;
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
        
        /* 手機響應式設計 */
        @media (max-width: 768px) {
            .store-dashboard {
                padding: 15px;
            }
            
            .store-header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .header-buttons {
                flex-direction: column;
                width: 100%;
                max-width: 300px;
                margin: 0 auto;
            }
            
            .refresh-btn, .logout-btn {
                width: 100%;
                text-align: center;
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
        <!-- 店櫃標頭 -->
        <div class="store-header">
            <div class="store-info">
                <h1><?php echo htmlspecialchars($store_info['name'] ?? $store_code); ?></h1>
                <div class="store-code">店櫃代號: <?php echo htmlspecialchars($store_code); ?></div>
            </div>
            <div class="header-buttons">
                <button type="button" class="refresh-btn" onclick="location.reload()">重新整理</button>
                <a href="logout.php" class="logout-btn" onclick="return confirmLogout()">登出</a>
            </div>
        </div>
        
        <!-- 業績統計區塊 -->
        <div class="stats-container">
            <div class="stat-card-horizontal">
                <h3>今日業績</h3>
                <p class="stat-value-horizontal">NT$ <?php echo number_format($today_amount); ?></p>
                <p class="stat-date-horizontal">今日累計</p>
            </div>
            <div class="stat-card-horizontal">
                <h3>本月累計</h3>
                <p class="stat-value-horizontal">NT$ <?php echo number_format($month_total); ?></p>
                <p class="stat-date-horizontal">本月至今</p>
            </div>
            <div class="stat-card-horizontal">
                <h3>本月日均</h3>
                <p class="stat-value-horizontal">NT$ <?php echo number_format($month_avg); ?></p>
                <p class="stat-date-horizontal">平均每日</p>
            </div>
        </div>
        
        <!-- 業績登打區塊 -->
        <div class="input-section">
            <h2>今日業績登打</h2>
            <div class="today-date">今日日期: <?php echo $today; ?></div>
            
            <?php if ($success_message): ?>
                <div class="message success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($has_today_sales): ?>
                <div class="already-logged">
                    <strong>⚠️ 今日已登打過業績</strong>
                    <p>金額: NT$ <?php echo number_format($today_amount); ?> | 角色: <?php echo $today_role === 'main' ? '主櫃' : '代班'; ?></p>
                    <p>您可以重新登打更新業績</p>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="sales-form">
                <!-- 角色選擇 -->
                <div class="role-selection">
                    <h3>請選擇角色：</h3>
                    <div class="role-buttons">
                        <button type="button" class="role-btn" id="main-btn" onclick="selectRole('main')">主櫃</button>
                        <button type="button" class="role-btn" id="substitute-btn" onclick="selectRole('substitute')">代班</button>
                    </div>
                    <input type="hidden" name="selected_role" id="selected-role" value="">
                </div>
                
                <!-- 金額輸入 -->
                <div class="amount-input">
                    <h3>輸入業績金額：</h3>
                    <input type="number" name="amount" id="amount" placeholder="輸入金額" min="0" required autofocus>
                </div>
                
                <!-- 提交按鈕 -->
                <button type="submit" class="submit-btn" id="submit-btn" disabled>
                    <span class="submit-icon">💰</span>
                    <span class="submit-text">登打業績</span>
                </button>
            </form>
        </div>
    </div>
    
    <script>
        // 角色選擇
        let selectedRole = '';
        
        function selectRole(role) {
            selectedRole = role;
            
            // 更新隱藏欄位
            document.getElementById('selected-role').value = role;
            
            // 更新按鈕樣式
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
            
            // 啟用提交按鈕
            submitBtn.disabled = false;
            submitBtn.classList.add('enabled');
            
            // 聚焦到金額輸入框
            document.getElementById('amount').focus();
        }
        
        // 金額輸入時檢查
        document.getElementById('amount').addEventListener('input', function() {
            const amount = this.value;
            const submitBtn = document.getElementById('submit-btn');
            
            // 修改：金額可以是0，但不能空白
            if (selectedRole && amount !== '' && amount >= 0) {
                submitBtn.disabled = false;
                submitBtn.classList.add('enabled');
            } else {
                submitBtn.disabled = true;
                submitBtn.classList.remove('enabled');
            }
        });
        
        // 表單提交前檢查
        document.getElementById('sales-form').addEventListener('submit', function(event) {
            const amountInput = document.getElementById('amount');
            const amount = amountInput.value;
            
            if (!selectedRole) {
                event.preventDefault();
                alert('請選擇主櫃或代班角色！');
                return false;
            }
            
            if (amount === '' || amount < 0) {
                event.preventDefault();
                alert('請輸入有效的業績金額（可以是0，但不能空白）！');
                return false;
            }
            
            // 顯示處理中狀態
            const submitBtn = document.getElementById('submit-btn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="submit-icon">⏳</span><span class="submit-text">處理中...</span>';
            submitBtn.disabled = true;
            
            // 傳統表單提交，不需要 AJAX
            return true;
        });
        
        // 頁面載入時檢查是否有已選擇的角色（從已登打的資料）
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($has_today_sales && $today_role): ?>
                // 自動選擇已登打的角色
                selectRole('<?php echo $today_role; ?>');
                
                // 設定金額
                const amountInput = document.getElementById('amount');
                amountInput.value = <?php echo $today_amount; ?>;
                amountInput.focus();
                amountInput.select();
            <?php endif; ?>
            
            // 自動重整頁面（每5分鐘）
            setTimeout(function() {
                window.location.reload();
            }, <?php echo $refresh_interval * 1000; ?>);
        });
        
        // 登出確認對話框
        function confirmLogout() {
            const result = confirm('是否確認要登出？\n\n是 或 否?');
            if (result) {
                // 使用者點擊「是」，繼續登出
                return true;
            } else {
                // 使用者點擊「否」或關閉對話框，取消登出
                return false;
            }
        }
        

    </script>
</body>
</html>