<?php
/**
 * 店櫃業績管理系統 - 儀表板（業務/督導專用版）
 */

// 啟動 Session（關鍵修正！）
session_start();

require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/auth_simple.php'; // 使用簡化版驗證

// 檢查登入
require_login();

// 取得目前使用者（使用正確的函式名稱）
$user = get_current_session_user();

// 店櫃人員自動重定向到專用頁面
if ($user['role'] === 'store') {
    header('Location: store_dashboard.php');
    exit;
}

$role_name = $GLOBALS['config']['roles'][$user['role']]['name'] ?? $user['role'];

// 載入店櫃資料和使用者資料
$stores = load_data('stores');
$users = load_data('users');

// 建立使用者代號到姓名的映射
$user_name_map = [];
foreach ($users as $user_data) {
    $user_name_map[$user_data['id']] = $user_data['name'];
}

// 根據角色篩選店櫃
$user_stores = [];
if (in_array($user['role'], ['boss', 'admin'])) {
    // 老闆和管理員：所有店櫃
    $user_stores = $stores;
} elseif ($user['role'] === 'store') {
    // 店櫃：自己的店櫃
    foreach ($stores as $store) {
        if ($store['code'] === $user['id']) {
            $user_stores[] = $store;
            break;
        }
    }
} else {
    // 業務/督導：從 stores.json 讀取負責店櫃
    // 注意：stores.json 中的 sales_person 和 supervisor 儲存的是人員代號
    foreach ($stores as $store) {
        if ($user['role'] === 'sales' && $store['sales_person'] === $user['id']) {
            $user_stores[] = $store;
        } elseif ($user['role'] === 'supervisor' && $store['supervisor'] === $user['id']) {
            $user_stores[] = $store;
        }
    }
}

// 載入銷售資料（使用按月儲存方式）
$current_month = date('Y-m');
$sales_summary = load_monthly_sales($current_month);
$today = date('Y-m-d');

// 設定基礎URL（用於API呼叫）
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
$script_path = dirname($_SERVER['SCRIPT_NAME']);
$site_url = rtrim($base_url . $script_path, '/') . '/';
$today_sales = $sales_summary[$today] ?? [];

// 計算業績統計（只計算負責店櫃）
$today_total = 0;
$yesterday_total = 0;
$month_total = 0;
$month_days = 0;

// 取得昨日日期
$yesterday = date('Y-m-d', strtotime('-1 day'));

// 取得負責店櫃的代號列表（用於快速匹配）
$responsible_store_codes = [];
foreach ($user_stores as $store) {
    $responsible_store_codes[] = $store['code'];
}

foreach ($sales_summary as $date => $daily_sales) {
    if (strpos($date, $current_month) === 0) {
        $month_days++;
        foreach ($daily_sales as $store_code => $store_sales) {
            // 只計算負責店櫃（使用代號列表快速匹配）
            if (in_array($store_code, $responsible_store_codes)) {
                $amount = $store_sales['amount'] ?? 0;
                $month_total += $amount;
                
                // 如果是今天，加到今日總業績
                if ($date === $today) {
                    $today_total += $amount;
                }
                
                // 如果是昨天，加到昨日總業績
                if ($date === $yesterday) {
                    $yesterday_total += $amount;
                }
            }
        }
    }
}

$month_avg = $month_days > 0 ? round($month_total / $month_days, 2) : 0;

// 計算上個月的日期
$last_month = date('Y-m', strtotime('-1 month'));
?>
<!DOCTYPE html>
<html lang='zh-TW'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>儀表板 - 店櫃業績管理系統</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .dashboard {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #007bff;
        }
        
        .user-info {
            text-align: right;
        }
        
        .role-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 14px;
            font-weight: bold;
            margin-left: 10px;
        }
        
        .role-admin { background: #dc3545; color: white; }
        .role-supervisor { background: #fd7e14; color: white; }
        .role-sales { background: #007bff; color: white; }
        .role-store { background: #28a745; color: white; }
        
        .btn {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            border: none;
            margin-left: 10px;
            transition: background 0.3s;
        }
        
        .btn-change-password {
            background: #ffc107;
            color: #212529;
        }
        
        .btn-change-password:hover {
            background: #e0a800;
        }
        
        .btn-new-layout {
            background: #17a2b8;
            color: white;
        }
        
        .btn-new-layout:hover {
            background: #138496;
        }
        
        .btn-logout {
            background: #6c757d;
            color: white;
        }
        
        .btn-logout:hover {
            background: #5a6268;
        }
        
        /* 業績統計橫向排列 */
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
            transition: all 0.3s ease;
        }
        
        .stat-card-horizontal:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.15);
        }
        
        /* 特定卡片的懸停邊框顏色 */
        .stat-card-horizontal[style*="border-color: #d6d6ff"]:hover {
            border-color: #6f42c1;
        }
        
        .stat-card-horizontal[style*="border-color: #ffd6b3"]:hover {
            border-color: #fd7e14;
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
        
        /* 今日店櫃業績表格 */
        .today-stores {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .today-stores h2 {
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #28a745;
            padding-bottom: 10px;
            margin-bottom: 25px;
        }
        
        .store-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .store-table th, .store-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        
        .store-table th {
            background: #f8f9fa;
            font-weight: bold;
        }
        
        .store-table tr:hover {
            background: #f8f9fa;
        }
        
        .positive { color: #28a745; font-weight: bold; }
        .zero { color: #6c757d; }
        
        /* 報表按鈕區 */
        .report-buttons {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 30px;
        }
        
        .report-btn {
            background: #6f42c1;
            color: white;
            border: none;
            padding: 20px;
            border-radius: 10px;
            font-size: 18px;
            cursor: pointer;
            transition: background 0.3s;
            text-align: center;
            text-decoration: none;
            display: block;
        }
        
        .report-btn:hover {
            background: #5a32a3;
        }
        
        .report-btn .subtitle {
            font-size: 14px;
            opacity: 0.9;
            margin-top: 5px;
        }
        
        .no-data {
            text-align: center;
            color: #6c757d;
            padding: 40px;
            font-style: italic;
        }
        
        /* 日期查詢按鈕 */
        .date-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 15px 20px;
            border-radius: 10px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-width: 120px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .date-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0,0,0,0.15);
        }
        
        .date-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .date-btn.today {
            background: #28a745;
        }
        
        .date-btn.today:hover {
            background: #218838;
        }
        
        .date-btn.yesterday {
            background: #17a2b8;
        }
        
        .date-btn.yesterday:hover {
            background: #138496;
        }
        
        .date-btn.custom {
            background: #007bff;
            min-width: 100px;
            padding: 12px 20px;
        }
        
        .date-btn.custom:hover {
            background: #0069d9;
        }
        
        .date-btn small {
            font-size: 12px;
            opacity: 0.9;
            margin-top: 5px;
        }
        
        /* 業績彈出視窗 */
        .yesterday-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .yesterday-modal.show {
            display: flex;
            animation: fadeIn 0.3s;
        }
        
        .yesterday-content {
            background: white;
            border-radius: 15px;
            width: 90%;
            max-width: 1200px;
            max-height: 80vh;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .yesterday-header {
            background: #17a2b8;
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .yesterday-header h3 {
            margin: 0;
            font-size: 20px;
        }
        
        .close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background 0.3s;
        }
        
        .close-btn:hover {
            background: rgba(255,255,255,0.2);
        }
        
        .yesterday-body {
            padding: 20px;
            overflow-y: auto;
            max-height: calc(80vh - 70px);
        }
        
        .yesterday-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        
        .yesterday-table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .yesterday-table td {
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .yesterday-table tr:hover {
            background: #f8f9fa;
        }
        
        .yesterday-table .positive {
            color: #28a745;
            font-weight: bold;
        }
        
        .yesterday-table .zero {
            color: #6c757d;
        }
        

        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        /* 響應式設計 */
        @media (max-width: 768px) {
            .yesterday-content {
                width: 95%;
                max-height: 90vh;
            }
            
            .yesterday-body {
                max-height: calc(90vh - 70px);
            }
            
            .yesterday-table {
                font-size: 12px;
            }
            
            .yesterday-table th,
            .yesterday-table td {
                padding: 8px;
            }
            
            .yesterday-btn {
                padding: 10px 15px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="header">
            <div>
                <h1>店櫃業績管理系統</h1>
                <p>歡迎使用業績管理系統</p>
            </div>
            <div class="user-info">
                <span>歡迎，<?php echo htmlspecialchars($user['name']); ?></span>
                <span class="role-badge <?php echo $user['role']; ?>"><?php echo htmlspecialchars($role_name); ?></span>
                <!-- 變更密碼按鈕已隱藏 -->
                <!-- <button type="button" class="btn btn-change-password" onclick="showChangePassword()">變更密碼</button> -->
                <a href="logout.php" class="btn btn-logout">登出</a>
            </div>
        </div>

        <!-- 業績統計（橫向排列） -->
        <div class="stats-horizontal">
            <div class="stat-card-horizontal" onclick="showDailySales('<?php echo $today; ?>')" style="cursor: pointer;">
                <h3>今日總業績</h3>
                <p class="stat-value-horizontal"><?php echo $today_total > 0 ? number_format($today_total) : '---'; ?></p>
                <p class="stat-date-horizontal"><?php echo $today; ?></p>
            </div>
            <div class="stat-card-horizontal" onclick="showDailySales('<?php echo $yesterday; ?>')" style="cursor: pointer;">
                <h3>昨日總業績</h3>
                <p class="stat-value-horizontal"><?php echo $yesterday_total > 0 ? number_format($yesterday_total) : '---'; ?></p>
                <p class="stat-date-horizontal"><?php echo $yesterday; ?></p>
            </div>
            <div class="stat-card-horizontal" onclick="openDailySalesPopup('daily_sales_final.php?month=<?php echo $current_month; ?>')" style="cursor: pointer;">
                <h3>本月累計</h3>
                <p class="stat-value-horizontal"><?php echo number_format($month_total); ?></p>
                <p class="stat-date-horizontal"><?php echo $current_month; ?> (<?php echo $month_days; ?>天)</p>
            </div>
            <div class="stat-card-horizontal">
                <h3>本月日均</h3>
                <p class="stat-value-horizontal"><?php echo number_format($month_avg); ?></p>
                <p class="stat-date-horizontal">平均每日業績</p>
            </div>
        </div>

        <!-- 月業績查詢 -->
        <div class="monthly-sales-query">
            <div style="text-align: center; margin-bottom: 30px;">
                <h2 style="margin: 0 0 20px 0; color: #333;">月業績查詢-全區域</h2>
                <p style="color: #6c757d; margin-bottom: 25px;">查看全區域各月份店櫃業績統計報表</p>
            </div>
        </div>

        <!-- 月業績報表卡片（兩個並排） -->
        <div class="stats-horizontal" style="margin-top: 40px; display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
            <!-- 左側：本月各店櫃每日業績 -->
            <div class="stat-card-horizontal" onclick="openDailySalesPopup('daily_sales_final.php?month=<?php echo $current_month; ?>&all_stores=1')" style="cursor: pointer; background: linear-gradient(135deg, #e6e6ff 0%, #f0f0ff 100%); border-color: #d6d6ff;">
                <h3 style="color: #6f42c1;">本月各店櫃每日業績</h3>
                <p class="stat-value-horizontal" style="color: #6f42c1; font-size: 24px;"><?php echo $current_month; ?> 月份</p>
                <p class="stat-date-horizontal" style="color: #6f42c1; opacity: 0.8;">彈出視窗查看詳細報表 (CSV匯出)</p>
            </div>
            
            <!-- 右側：月業績區域統計表 -->
            <div class="stat-card-horizontal" onclick="showMonthlyAreaStats()" style="cursor: pointer; background: linear-gradient(135deg, #fff0e6 0%, #fff5f0 100%); border-color: #ffd6b3;">
                <h3 style="color: #fd7e14;">月業績區域統計表</h3>
                <p class="stat-value-horizontal" style="color: #fd7e14; font-size: 20px;">督導/業務區域統計</p>
                <p class="stat-date-horizontal" style="color: #fd7e14; opacity: 0.8;">彈出視窗查看區域統計報表</p>
            </div>
        </div>
        
        <?php if ($user['role'] === 'admin'): ?>
        <!-- 管理員專用功能 -->
        <div class="admin-actions" style="margin-top: 40px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
            <h2 style="margin-top: 0; color: #333;">管理員功能</h2>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                <a href="admin/manage_users.php" class="report-btn" style="background: #dc3545;">
                    人員管理
                    <div class="subtitle">管理使用者帳號與權限</div>
                </a>
                
                <a href="admin/manage_stores.php" class="report-btn" style="background: #fd7e14;">
                    店櫃管理
                    <div class="subtitle">管理店櫃資料與設定</div>
                </a>
                
                <a href="admin/bulk_edit_monthly.php" class="report-btn" style="background: #28a745;">
                    批量編輯業績
                    <div class="subtitle">快速編輯月度所有店櫃業績</div>
                </a>
                
                <a href="admin_payment_review.php" target="_blank" class="report-btn" style="background: #ffc107; color: #333;" onclick="openPaymentReview(this.href); return false;">
                    收款審核
                    <div class="subtitle">審核店櫃是否已寄回款項（前十天）</div>
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- 業績彈出視窗 -->
    <div id="yesterday-modal" class="yesterday-modal">
        <div class="yesterday-content">
            <div class="yesterday-header">
                <h3>每日各店櫃業績</h3>
                <button class="close-btn" onclick="closeModal()">×</button>
            </div>
            <div class="yesterday-body" id="yesterday-body">
                <!-- 日期選擇器 -->
                <div class="date-selector" style="margin-bottom: 25px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 15px; flex-wrap: wrap;">
                        <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                            <div style="font-weight: 600; color: #495057; font-size: 16px;">選擇日期：</div>
                            <input type="date" id="report-date-selector" 
                                   value="<?php echo date('Y-m-d'); ?>" 
                                   max="<?php echo date('Y-m-d'); ?>"
                                   style="padding: 10px 15px; border: 2px solid #ced4da; border-radius: 6px; font-size: 16px;">
                            <button onclick="loadSelectedDateReport()" 
                                    style="padding: 10px 25px; background: #007bff; color: white; border: none; border-radius: 6px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background 0.3s;">
                                查詢
                            </button>
                        </div>
                        <div>
                            <button onclick="exportYesterdayCSV()" 
                                    style="padding: 10px 25px; background: #28a745; color: white; border: none; border-radius: 6px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background 0.3s;">
                                📊 匯出 CSV
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- 報表內容區域 -->
                <div id="report-content-area">
                    <!-- 這裡會動態載入業績資料 -->
                    <div style="text-align: center; padding: 40px;">
                        <div style="font-size: 20px; color: #6c757d; margin-bottom: 15px;">選擇日期後點擊查詢</div>
                        <div style="font-size: 16px; color: #adb5bd;">或使用快速按鈕查看今日/昨日業績</div>
                    </div>
                </div>
                
                <!-- 底部關閉按鈕 -->
                <div style="padding: 20px; border-top: 1px solid #e9ecef; text-align: center;">
                    <button onclick="closeModal()" style="padding: 12px 30px; background: #6c757d; color: white; border: none; border-radius: 6px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background 0.3s;">
                        關閉
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // 顯示每日業績（並設置日期選擇器）
        function showDailySales(date) {
            const modal = document.getElementById('yesterday-modal');
            
            // 更新彈出視窗標題
            document.querySelector('.yesterday-header h3').textContent = date + ' 各店櫃業績';
            
            // 設置日期選擇器的值
            const dateSelector = document.getElementById('report-date-selector');
            if (dateSelector) {
                dateSelector.value = date;
            }
            
            // 顯示彈出視窗
            modal.classList.add('show');
            
            // 載入業績資料
            loadSelectedDateReport();
        }
        
        // 載入選擇日期的報表
        function loadSelectedDateReport() {
            const dateSelector = document.getElementById('report-date-selector');
            const date = dateSelector ? dateSelector.value : '<?php echo date('Y-m-d'); ?>';
            const contentArea = document.getElementById('report-content-area');
            
            // 更新彈出視窗標題
            document.querySelector('.yesterday-header h3').textContent = date + ' 各店櫃業績';
            
            // 顯示載入中
            contentArea.innerHTML = `
                <div style="text-align: center; padding: 60px 40px;">
                    <div style="font-size: 22px; color: #6c757d; margin-bottom: 20px;">載入 ${date} 業績資料...</div>
                    <div style="margin-top: 30px;">
                        <div class="spinner" style="width: 50px; height: 50px; border: 5px solid #f3f3f3; border-top: 5px solid #17a2b8; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto;"></div>
                    </div>
                </div>
            `;
            
            // 載入業績資料
            loadDailySales(date);
        }
        

        

        
        // 統一關閉彈出視窗函數
        function closeModal() {
            // 嘗試關閉「業績彈出視窗」（使用CSS class方法）
            const salesModal = document.getElementById('yesterday-modal');
            if (salesModal && salesModal.classList.contains('show')) {
                salesModal.classList.remove('show');
                return;
            }
            
            // 嘗試關閉「月業績區域統計表」（使用移除元素方法）
            const statsModal = document.querySelector('.monthly-stats-modal');
            if (statsModal) {
                statsModal.remove();
                return;
            }
        }
        
        // 保持向後相容性
        function closeYesterdaySales() {
            closeModal();
        }
        
        // 載入每日業績資料
        function loadDailySales(date) {
            const contentArea = document.getElementById('report-content-area');
            
            // 使用 AJAX 載入資料
            fetch(`get_dataset_sales.php?date=${date}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('網路請求失敗');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        displayYesterdaySales(data.data, date);
                    } else {
                        showError(data.message || '載入失敗');
                    }
                })
                .catch(error => {
                    console.error('載入錯誤:', error);
                    showError('無法載入業績資料');
                });
        }
        
        // 顯示業績表格
        function displayYesterdaySales(salesData, date) {
            const contentArea = document.getElementById('report-content-area');
            const userRole = '<?php echo $user['role']; ?>';
            
            // 取得使用者負責的店櫃
            const userStores = <?php echo json_encode($user_stores); ?>;
            const userNameMap = <?php echo json_encode($user_name_map); ?>;
            
            // 計算統計資料
            const totalAmount = salesData.total_amount || 0;
            const storesCount = salesData.stores_count || 0;
            const enteredCount = salesData.entered_count || 0;
            const substituteCount = salesData.substitute_count || 0;
            
            // 建立表格 HTML（文字全部變大一級）
            let html = `
                <div style="margin-bottom: 25px; padding: 20px; background: #f8f9fa; border-radius: 10px; font-size: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                        <div style="font-size: 16px;">
                            <strong style="font-size: 17px;">日期：</strong>${date}
                            <strong style="margin-left: 25px; font-size: 17px;">總業績：</strong>
                            <span style="color: #28a745; font-weight: bold; font-size: 22px;">
                                ${formatNumber(totalAmount)}
                            </span>
                        </div>
                    </div>
                </div>
                
                <div style="overflow-x: auto; margin-top: 20px;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 16px;">
                        <thead>
                            <tr style="background: #f8f9fa;">
                                <th style="padding: 15px 20px; text-align: left; border-bottom: 3px solid #dee2e6; font-size: 17px; font-weight: 700;">店櫃代號</th>
                                <th style="padding: 15px 20px; text-align: left; border-bottom: 3px solid #dee2e6; font-size: 17px; font-weight: 700;">店櫃名稱</th>
                                <th style="padding: 15px 20px; text-align: left; border-bottom: 3px solid #dee2e6; font-size: 17px; font-weight: 700;">業績金額</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            // 顯示每個店櫃的業績
            userStores.forEach(store => {
                const storeCode = store.code;
                const storeData = salesData.stores_by_code ? salesData.stores_by_code[storeCode] : null;
                const amount = storeData ? (storeData.amount !== undefined ? storeData.amount : null) : null;
                const role = storeData ? (storeData.role || 'main') : 'main';
                

                
                html += `
                    <tr style="font-size: 16px;">
                        <td style="padding: 12px 20px; border-bottom: 1px solid #e9ecef;">${escapeHtml(storeCode)}</td>
                        <td style="padding: 12px 20px; border-bottom: 1px solid #e9ecef;">${escapeHtml(store.name)}</td>
                        <td style="padding: 12px 20px; border-bottom: 1px solid #e9ecef;" class="${amount !== null && amount > 0 ? 'positive' : 'zero'}">
                            ${amount !== null ? formatNumber(amount) : '-'}
                            ${role === 'substitute' ? '<span style="font-size: 12px; color: #adb5bd; margin-left: 5px;">(代班)</span>' : ''}
                        </td>
                    </tr>
                `;
            });
            
            html += `
                        </tbody>
                    </table>
                </div>
                
                <div style="margin-top: 25px; padding: 20px; background: #f8f9fa; border-radius: 10px; font-size: 16px;">
                    <strong style="font-size: 17px;">統計摘要：</strong>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-top: 15px;">
                        <div style="font-size: 16px;">
                            <strong style="font-size: 17px;">總店櫃數：</strong> ${storesCount}
                        </div>
                        <div style="font-size: 16px;">
                            <strong style="font-size: 17px;">已登打：</strong> 
                            <span style="color: #28a745; font-size: 17px;">${enteredCount}</span>
                        </div>
                        <div style="font-size: 16px;">
                            <strong style="font-size: 17px;">未登打：</strong> 
                            <span style="color: #dc3545; font-size: 17px;">${storesCount - enteredCount}</span>
                        </div>
                        <div style="font-size: 16px;">
                            <strong style="font-size: 17px;">代班銷售：</strong> 
                            <span style="color: #6c757d; font-size: 17px;">${substituteCount}</span>
                        </div>
                    </div>
                </div>
            `;
            
            contentArea.innerHTML = html;
        }
        
        // 計算總業績（備用函數）
        function calculateTotal(salesData) {
            let total = 0;
            if (salesData.stores_by_code) {
                for (const storeCode in salesData.stores_by_code) {
                    const amount = salesData.stores_by_code[storeCode].amount || 0;
                    total += amount;
                }
            }
            return total;
        }
        
        // 顯示錯誤訊息
        function showError(message) {
            const contentArea = document.getElementById('report-content-area');
            contentArea.innerHTML = `
                <div style="text-align: center; padding: 60px 40px;">
                    <div style="color: #dc3545; font-size: 22px; margin-bottom: 25px; font-weight: 600;">
                        ❌ ${message}
                    </div>
                    <button onclick="loadSelectedDateReport()" style="padding: 12px 25px; background: #007bff; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: 600;">
                        重新查詢
                    </button>
                </div>
            `;
        }
        
        // 格式化數字（千分位）
        function formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }
        
        // 跳脫 HTML 字元
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // 匯出 CSV
        function exportYesterdayCSV() {
            // 從彈出視窗標題取得日期
            const headerTitle = document.querySelector('.yesterday-header h3').textContent;
            const dateMatch = headerTitle.match(/(\d{4}-\d{2}-\d{2})/);
            const date = dateMatch ? dateMatch[1] : '<?php echo date('Y-m-d'); ?>';
            
            const userStores = <?php echo json_encode($user_stores); ?>;
            const userNameMap = <?php echo json_encode($user_name_map); ?>;
            
            // 建立 CSV 內容 (已移除業務和督導欄位)
            let csv = '店櫃代號,店櫃名稱,業績金額,角色,日期\n';
            
            // 這裡需要實際的資料，暫時先使用模擬資料
            userStores.forEach(store => {
                const storeCode = store.code;
                // 業務和督導欄位已移除
                
                // 模擬資料 - 實際應該從伺服器取得
                const amount = Math.floor(Math.random() * 50000);
                const role = Math.random() > 0.8 ? 'substitute' : 'main';
                
                csv += `${storeCode},"${store.name}",${amount},${role},${date}\n`; // 移除業務和督導欄位
            });
            
            // 建立下載連結
            const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `業績報表_${date}.csv`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
        
        // 點擊彈出視窗外關閉
        document.getElementById('yesterday-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        // ESC 鍵關閉
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeYesterdaySales();
            }
        });
        
        // 添加 CSS 動畫
        const style = document.createElement('style');
        style.textContent = `
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);
        
        // 打開每日業績彈出視窗
        function openDailySalesPopup(url) {
            // 判斷是否為本月業績報表（月份報表需要更大高度）
            const isMonthlyReport = url.includes('daily_sales_final.php');
            
            // 設定彈出視窗參數
            const width = Math.min(1200, window.innerWidth - 40);
            
            // 根據報表類型設定不同高度
            let height;
            if (isMonthlyReport) {
                // 本月業績報表：使用更大高度，避免雙重滾動
                height = Math.min(1000, window.innerHeight - 20);
            } else {
                // 每日業績報表：原有高度
                height = Math.min(800, window.innerHeight - 40);
            }
            
            const left = (window.innerWidth - width) / 2;
            const top = (window.innerHeight - height) / 2;
            
            // 彈出視窗設定
            const features = [
                `width=${width}`,
                `height=${height}`,
                `left=${left}`,
                `top=${top}`,
                'menubar=no',
                'toolbar=no',
                'location=no',
                'status=no',
                'resizable=yes',
                'scrollbars=yes'
            ].join(',');
            
            // 打開彈出視窗
            const popup = window.open(url, 'daily_sales_popup', features);
            
            // 如果彈出視窗被阻擋，顯示提示
            if (!popup || popup.closed || typeof popup.closed === 'undefined') {
                // 嘗試直接打開連結（可能會在新分頁）
                window.open(url, '_blank');
                
                // 顯示提示訊息
                if (window.confirm('彈出視窗被阻擋。是否要在新分頁中打開？')) {
                    window.open(url, '_blank');
                }
            }
            
            return false; // 防止預設連結行為
        }
        
        // 打開收款審核彈出視窗
        function openPaymentReview(url) {
            // 設定彈出視窗參數（較小的視窗適合收款審核）
            const width = Math.min(900, window.innerWidth - 40);
            const height = Math.min(700, window.innerHeight - 40);
            const left = (window.innerWidth - width) / 2;
            const top = (window.innerHeight - height) / 2;
            
            // 彈出視窗設定
            const features = [
                `width=${width}`,
                `height=${height}`,
                `left=${left}`,
                `top=${top}`,
                'menubar=no',
                'toolbar=no',
                'location=no',
                'status=no',
                'resizable=yes',
                'scrollbars=yes'
            ].join(',');
            
            // 打開彈出視窗
            const popup = window.open(url, 'payment_review', features);
            
            // 如果彈出視窗被阻擋，顯示提示
            if (!popup || popup.closed || typeof popup.closed === 'undefined') {
                // 嘗試直接打開連結（可能會在新分頁）
                window.open(url, '_blank');
                
                // 顯示提示訊息
                if (window.confirm('彈出視窗被阻擋。是否要在新分頁中打開？')) {
                    window.open(url, '_blank');
                }
            }
            
            return false; // 防止預設連結行為
        }
        
        // 顯示變更密碼對話框（與store_dashboard.php相同功能）
        function showChangePassword() {
            // 創建彈出視窗
            const modal = document.createElement('div');
            modal.id = 'change-password-modal';
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 1000;
            `;
            
            // 創建對話框內容
            const dialog = document.createElement('div');
            dialog.style.cssText = `
                background: white;
                padding: 30px;
                border-radius: 10px;
                width: 90%;
                max-width: 400px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            `;
            
            dialog.innerHTML = `
                <h2 style="margin-top: 0; color: #333; text-align: center;">變更密碼</h2>
                <form id="change-password-form">
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">舊密碼</label>
                        <input type="password" id="old-password" 
                               style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"
                               required>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">新密碼 (至少4碼)</label>
                        <input type="password" id="new-password" 
                               style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"
                               minlength="4" required>
                    </div>
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">確認新密碼</label>
                        <input type="password" id="confirm-password" 
                               style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"
                               minlength="4" required>
                    </div>
                    <div id="password-error" style="color: #dc3545; margin-bottom: 15px; display: none;"></div>
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" style="flex: 1; padding: 12px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;">
                            確認變更
                        </button>
                        <button type="button" onclick="closeChangePassword()" style="flex: 1; padding: 12px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer;">
                            取消
                        </button>
                    </div>
                </form>
            `;
            
            modal.appendChild(dialog);
            document.body.appendChild(modal);
            
            // 處理表單提交
            document.getElementById('change-password-form').addEventListener('submit', function(e) {
                e.preventDefault();
                submitChangePassword();
            });
            
            // 自動聚焦到舊密碼欄位
            document.getElementById('old-password').focus();
        }
        
        // 關閉變更密碼對話框
        function closeChangePassword() {
            const modal = document.getElementById('change-password-modal');
            if (modal) {
                modal.remove();
            }
        }
        
        // 提交變更密碼
        function submitChangePassword() {
            const oldPassword = document.getElementById('old-password').value;
            const newPassword = document.getElementById('new-password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            const errorElement = document.getElementById('password-error');
            
            // 清除錯誤訊息
            errorElement.style.display = 'none';
            errorElement.textContent = '';
            
            // 驗證輸入
            if (!oldPassword) {
                showPasswordError('請輸入舊密碼');
                return;
            }
            
            if (!newPassword) {
                showPasswordError('請輸入新密碼');
                return;
            }
            
            if (newPassword.length < 4) {
                showPasswordError('新密碼必須至少4個字元');
                return;
            }
            
            if (newPassword !== confirmPassword) {
                showPasswordError('新密碼與確認密碼不一致');
                return;
            }
            
            // 發送AJAX請求
            const formData = new FormData();
            formData.append('action', 'change_password');
            formData.append('old_password', oldPassword);
            formData.append('new_password', newPassword);
            
            // 顯示處理中
            const submitBtn = document.querySelector('#change-password-form button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '處理中...';
            submitBtn.disabled = true;
            
            fetch('change_password.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('密碼變更成功！');
                    closeChangePassword();
                } else {
                    showPasswordError(data.message || '密碼變更失敗');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                showPasswordError('網路錯誤，請稍後再試');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                console.error('Error:', error);
            });
        }
        
        // 顯示月業績區域統計表（即時數據）
        function showMonthlyAreaStats() {
            // 顯示載入中訊息
            const loadingHtml = `
                <div class="monthly-stats-modal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center; z-index: 1000;">
                    <div style="background: white; border-radius: 10px; width: 90%; max-width: 800px; max-height: 90vh; overflow: auto; box-shadow: 0 5px 30px rgba(0,0,0,0.3);">
                        <!-- 標題區 -->
                        <div style="background: #fd7e14; color: white; padding: 20px; border-radius: 10px 10px 0 0; display: flex; justify-content: space-between; align-items: center;">
                            <h2 style="margin: 0; font-size: 22px; font-weight: 700;">📊 月業績區域統計表</h2>
                            <button onclick="closeModal()" style="background: rgba(255,255,255,0.2); color: white; border: none; width: 36px; height: 36px; border-radius: 50%; font-size: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center;">×</button>
                        </div>
                        
                        <!-- 載入中 -->
                        <div style="padding: 60px 25px; text-align: center;">
                            <div style="font-size: 18px; color: #495057; margin-bottom: 20px;">正在載入統計數據...</div>
                            <div style="display: inline-block; width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #fd7e14; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                        </div>
                    </div>
                </div>
            `;
            
            // 建立並顯示載入中視窗
            const modal = document.createElement('div');
            modal.innerHTML = loadingHtml;
            document.body.appendChild(modal);
            
            // 為載入中視窗添加背景點擊關閉功能
            const loadingModal = modal.querySelector('.monthly-stats-modal');
            if (loadingModal) {
                loadingModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeModal();
                    }
                });
            }
            
            // 取得當前月份
            const currentMonth = '<?php echo date("Y-m"); ?>';
            
            // 從API取得即時統計數據（使用完整URL避免路徑問題）
            const apiUrl = '<?php echo $site_url; ?>get_monthly_stats.php?month=' + currentMonth;
            console.log('API URL:', apiUrl);
            
            fetch(apiUrl)
                .then(response => {
                    console.log('API Response status:', response.status, response.statusText);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('API Data received:', data);
                    if (!data.success) {
                        alert('取得統計數據失敗：' + (data.error || '未知錯誤') + (data.debug ? '\n除錯資訊：' + data.debug : ''));
                        closeModal();
                        return;
                    }
                    
                    // 更新彈出視窗內容為實際數據
                    updateStatsModal(data);
                })
                .catch(error => {
                    console.error('Error fetching stats:', error);
                    alert('網路錯誤，無法取得統計數據\n錯誤詳情：' + error.message);
                    closeModal();
                });
        }
        
        // 更新統計數據彈出視窗
        function updateStatsModal(data) {
            // 先移除載入中視窗
            const loadingModal = document.querySelector('.monthly-stats-modal');
            if (loadingModal) {
                loadingModal.remove();
            }
            
            // 建立彈出視窗HTML（使用實際數據）
            const html = `
                <div class="monthly-stats-modal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center; z-index: 1000;">
                    <div style="background: white; border-radius: 10px; width: 90%; max-width: 800px; max-height: 90vh; overflow: auto; box-shadow: 0 5px 30px rgba(0,0,0,0.3);">
                        <!-- 標題區 -->
                        <div style="background: #fd7e14; color: white; padding: 20px; border-radius: 10px 10px 0 0; display: flex; justify-content: space-between; align-items: center;">
                            <h2 style="margin: 0; font-size: 22px; font-weight: 700;">📊 月業績區域統計表 - ${data.month}</h2>
                            <button onclick="closeModal()" style="background: rgba(255,255,255,0.2); color: white; border: none; width: 36px; height: 36px; border-radius: 50%; font-size: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center;">×</button>
                        </div>
                        
                        <!-- 內容區 -->
                        <div style="padding: 25px;">
                            <!-- 督導統計 -->
                            <div style="margin-bottom: 30px;">
                                <h3 style="color: #495057; margin: 0 0 15px 0; font-size: 18px; font-weight: 700; border-bottom: 2px solid #fd7e14; padding-bottom: 8px;">督導統計</h3>
                                <table style="width: 100%; border-collapse: collapse; font-size: 15px;">
                                    <thead>
                                        <tr style="background: #f8f9fa;">
                                            <th style="padding: 12px 15px; text-align: left; border: 1px solid #dee2e6; font-weight: 700;">督導</th>
                                            <th style="padding: 12px 15px; text-align: right; border: 1px solid #dee2e6; font-weight: 700;">總業績</th>
                                            <th style="padding: 12px 15px; text-align: right; border: 1px solid #dee2e6; font-weight: 700;">店平均</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${data.supervisors.map(supervisor => `
                                            <tr>
                                                <td style="padding: 10px 15px; border: 1px solid #dee2e6; font-weight: 600;">${supervisor.name}</td>
                                                <td style="padding: 10px 15px; border: 1px solid #dee2e6; text-align: right; color: #28a745; font-weight: 600;">${supervisor.total.toLocaleString()}</td>
                                                <td style="padding: 10px 15px; border: 1px solid #dee2e6; text-align: right; color: #17a2b8; font-weight: 600;">${supervisor.avg.toLocaleString()}</td>
                                            </tr>
                                        `).join('')}
                                        <tr style="background: #f8f9fa; font-weight: 700;">
                                            <td style="padding: 12px 15px; border: 1px solid #dee2e6;">總計</td>
                                            <td style="padding: 12px 15px; border: 1px solid #dee2e6; text-align: right; color: #28a745;">${data.totals.supervisorTotal.toLocaleString()}</td>
                                            <td style="padding: 12px 15px; border: 1px solid #dee2e6; text-align: right; color: #17a2b8;">${data.totals.supervisorAvg.toLocaleString()}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- 分隔線 -->
                            <div style="text-align: center; margin: 25px 0; color: #6c757d; font-size: 14px;">
                                ────────
                            </div>
                            
                            <!-- 業務統計 -->
                            <div style="margin-bottom: 30px;">
                                <h3 style="color: #495057; margin: 0 0 15px 0; font-size: 18px; font-weight: 700; border-bottom: 2px solid #28a745; padding-bottom: 8px;">業務統計</h3>
                                <table style="width: 100%; border-collapse: collapse; font-size: 15px;">
                                    <thead>
                                        <tr style="background: #f8f9fa;">
                                            <th style="padding: 12px 15px; text-align: left; border: 1px solid #dee2e6; font-weight: 700;">業務</th>
                                            <th style="padding: 12px 15px; text-align: right; border: 1px solid #dee2e6; font-weight: 700;">總業績</th>
                                            <th style="padding: 12px 15px; text-align: right; border: 1px solid #dee2e6; font-weight: 700;">店平均</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${data.sales.map(salesPerson => `
                                            <tr>
                                                <td style="padding: 10px 15px; border: 1px solid #dee2e6; font-weight: 600;">${salesPerson.name}</td>
                                                <td style="padding: 10px 15px; border: 1px solid #dee2e6; text-align: right; color: #28a745; font-weight: 600;">${salesPerson.total.toLocaleString()}</td>
                                                <td style="padding: 10px 15px; border: 1px solid #dee2e6; text-align: right; color: #17a2b8; font-weight: 600;">${salesPerson.avg.toLocaleString()}</td>
                                            </tr>
                                        `).join('')}
                                        <tr style="background: #f8f9fa; font-weight: 700;">
                                            <td style="padding: 12px 15px; border: 1px solid #dee2e6;">總業績</td>
                                            <td style="padding: 12px 15px; border: 1px solid #dee2e6; text-align: right; color: #28a745;">${data.totals.salesTotal.toLocaleString()}</td>
                                            <td style="padding: 12px 15px; border: 1px solid #dee2e6; text-align: right; color: #17a2b8;">${data.totals.salesAvg.toLocaleString()}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- 分隔線 -->
                            <div style="text-align: center; margin: 25px 0; color: #6c757d; font-size: 14px;">
                                ────────
                            </div>
                            
                            <!-- 全區統計 -->
                            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center;">
                                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                                    <div>
                                        <div style="color: #6c757d; font-size: 14px; margin-bottom: 5px;">全區店平均</div>
                                        <div style="color: #fd7e14; font-size: 24px; font-weight: 700;">${data.totals.overallStoreAvg.toLocaleString()}</div>
                                    </div>
                                    <div>
                                        <div style="color: #6c757d; font-size: 14px; margin-bottom: 5px;">全區日平均</div>
                                        <div style="color: #28a745; font-size: 24px; font-weight: 700;">${data.totals.overallDailyAvg.toLocaleString()}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 關閉按鈕 -->
                            <div style="text-align: center; margin-top: 25px;">
                                <button onclick="closeModal()" style="padding: 12px 30px; background: #6c757d; color: white; border: none; border-radius: 6px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background 0.3s;">
                                    關閉
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // 建立並顯示彈出視窗
            const modal = document.createElement('div');
            modal.innerHTML = html;
            document.body.appendChild(modal);
            
            // 為彈出視窗添加背景點擊關閉功能
            const statsModal = modal.querySelector('.monthly-stats-modal');
            if (statsModal) {
                statsModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeModal();
                    }
                });
            }
        }
        
        // 顯示密碼錯誤訊息
        function showPasswordError(message) {
            const errorElement = document.getElementById('password-error');
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }
    </script>
</body>
</html>