<?php
/**
 * 管理員批量編輯月度業績
 * 位置：admin/bulk_edit_monthly.php
 */

// 啟動 Session
session_start();

require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../config/auth_simple.php';

// 檢查登入
require_login();

// 取得目前使用者
$user = get_current_session_user();

// 只有老闆和管理員可以使用此功能
if (!in_array($user['role'], ['boss', 'admin'])) {
    header('Location: ../dashboard.php');
    exit;
}

// 載入店櫃資料
$stores = load_data('stores');

// 預設顯示當前月份
$current_month = date('Y-m');
$selected_month = $_GET['month'] ?? $current_month;

// 處理表單提交
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['month']) && isset($_POST['bulk_data'])) {
        $month = $_POST['month'];
        $bulk_data = json_decode($_POST['bulk_data'], true);
        
        if ($bulk_data && is_array($bulk_data)) {
            // 載入該月份的業績資料
            $monthly_sales = load_monthly_sales($month);
            
            // 更新資料
            $updated_count = 0;
            foreach ($bulk_data as $date => $stores_data) {
                if (!isset($monthly_sales[$date])) {
                    $monthly_sales[$date] = [];
                }
                
                foreach ($stores_data as $store_code => $store_data) {
                    if (isset($store_data['amount']) && $store_data['amount'] !== '') {
                        $amount = (int)$store_data['amount'];
                        $role = $store_data['role'] ?? 'main';
                        $payment_status = $store_data['payment_status'] ?? 'unpaid';
                        
                        $monthly_sales[$date][$store_code] = [
                            'amount' => $amount,
                            'role' => $role,
                            'payment_status' => $payment_status,
                            'store_code' => $store_code,
                            'timestamp' => date('Y-m-d H:i:s')
                        ];
                        $updated_count++;
                    } elseif (isset($monthly_sales[$date][$store_code])) {
                        // 如果金額為空，刪除該筆記錄
                        unset($monthly_sales[$date][$store_code]);
                    }
                }
                
                // 如果該日期沒有資料，刪除日期鍵
                if (empty($monthly_sales[$date])) {
                    unset($monthly_sales[$date]);
                }
            }
            
            // 儲存更新後的資料
            if (save_monthly_sales($month, $monthly_sales)) {
                $success_message = "業績更新成功！共更新 {$updated_count} 筆記錄。";
                // 重新載入資料
                $monthly_sales = load_monthly_sales($month);
            } else {
                $error_message = "儲存失敗，請檢查檔案權限。";
            }
        } else {
            $error_message = "資料格式錯誤，請重新操作。";
        }
    }
}

// 載入選定月份的業績資料
$monthly_sales = load_monthly_sales($selected_month);

// 取得該月份的所有日期
$year = substr($selected_month, 0, 4);
$month_num = substr($selected_month, 5, 2);
$days_in_month = date('t', strtotime($selected_month . '-01'));

$dates = [];
for ($day = 1; $day <= $days_in_month; $day++) {
    $date = sprintf('%s-%02d-%02d', $year, $month_num, $day);
    $dates[] = $date;
}

// 取得前後月份
$prev_month = date('Y-m', strtotime($selected_month . '-01 -1 month'));
$next_month = date('Y-m', strtotime($selected_month . '-01 +1 month'));
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>批量編輯月度業績 - 管理員功能</title>
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
        
        .container {
            max-width: 1400px;
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
        
        .header h1 {
            margin: 0;
            color: #333;
        }
        
        .back-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
        }
        
        .back-btn:hover {
            background: #5a6268;
        }
        
        .month-selector {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            text-align: center;
        }
        
        .month-nav {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .month-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 16px;
        }
        
        .month-btn:hover {
            background: #0056b3;
        }
        
        .current-month {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            min-width: 200px;
            text-align: center;
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
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .bulk-edit-table {
            width: 100%;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .table-container {
            overflow-x: auto;
            max-height: 600px;
            overflow-y: auto;
        }
        
        .bulk-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;  /* 放大一級：12px → 14px */
        }
        
        .bulk-table th {
            background: #007bff;
            color: white;
            padding: 10px;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .bulk-table td {
            border: 1px solid #dee2e6;
            padding: 5px;
            text-align: center;
        }
        
        .store-header {
            background: #f8f9fa;
            font-weight: bold;
            position: sticky;
            left: 0;
            z-index: 5;
            transition: background-color 0.2s;
        }
        /* 店櫃高亮 */
        .store-header.highlight {
            background: #ffd763 !important;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        
        .date-header {
            background: #e9ecef;
            font-weight: bold;
            transition: background-color 0.2s;
        }
        /* 日期高亮 */
        .date-header.highlight {
            background: #ebaf00 !important;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .amount-input {
            width: 80px;
            padding: 5px;
            border: 1px solid #ced4da;
            border-radius: 3px;
            text-align: center;
            font-size: 14px;  /* 放大一級：12px → 14px */
            transition: all 0.2s;
            /* 移除數字輸入框的上下箭頭 */
            -moz-appearance: textfield;
        }
        
        /* 針對Webkit瀏覽器（Chrome, Safari, Edge）隱藏上下箭頭 */
        .amount-input::-webkit-outer-spin-button,
        .amount-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        
        .amount-input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
            background: #fff;
            transform: scale(1.05);
        }
        /* 輸入框高亮 */
        .amount-input.highlight {
            background: #fff3cd;
            border-color: #ffc107;
            box-shadow: 0 0 0 2px rgba(255,193,7,0.25);
            transform: scale(1.05);
        }
        
        .role-select {
            width: 60px;
            padding: 5px;
            border: 1px solid #ced4da;
            border-radius: 3px;
            font-size: 14px;  /* 放大一級：12px → 14px */
            background: white;
            transition: all 0.2s;
        }
        
        .role-select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
        }
        
        .payment-status-select {
            width: 60px;  /* 與角色選擇器一致 */
            padding: 5px;
            border: 1px solid #ced4da;
            border-radius: 3px;
            font-size: 14px;  /* 與角色選擇器一致：12px → 14px */
            background: white;
            transition: all 0.2s;
        }
        
        .payment-status-select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
        }
        
        /* 付款狀態顏色提示 - 與 daily_sales_final.php 一致 */
        .payment-status-select option[value="unpaid"] {
            background-color: #ffebee;  /* 淺粉紅 */
            color: #c62828;            /* 深紅 */
            font-weight: bold;
        }
        
        .payment-status-select option[value="paid"] {
            background-color: #e8f5e9;  /* 淺綠 */
            color: #2e7d32;            /* 深綠 */
            font-weight: bold;
        }
        
        .payment-status-select option[value="dayoff"] {
            background-color: #fff3e0;  /* 淺橙 */
            color: #ef6c00;            /* 深橙 */
            font-weight: bold;
        }
        
        .actions {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }
        
        .save-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
        }
        
        .save-btn:hover {
            background: #218838;
        }
        

        
        .instructions {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #ffeaa7;
        }
        
        .instructions h3 {
            margin-top: 0;
            color: #856404;
        }
        
        /* 響應式設計 */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .month-nav {
                flex-direction: column;
                gap: 10px;
            }
            
            .bulk-table {
                font-size: 11px;  /* 放大一級：10px → 11px */
            }
            
            .amount-input {
                width: 60px;
                font-size: 11px;  /* 放大一級：10px → 11px */
                padding: 3px;
                /* 移除數字輸入框的上下箭頭 */
                -moz-appearance: textfield;
            }
            
            /* 針對Webkit瀏覽器（Chrome, Safari, Edge）隱藏上下箭頭 */
            .amount-input::-webkit-outer-spin-button,
            .amount-input::-webkit-inner-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }
            
            .role-select {
                width: 50px;
                font-size: 11px;  /* 放大一級：10px → 11px */
                padding: 3px;
            }
            
            .actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .save-btn {
                width: 100%;
            }
        }
        
        /* 斑馬條紋 */
        .bulk-table tbody tr:nth-child(odd) {
            background-color: #f8f9fa;
        }
        
        .bulk-table tbody tr:nth-child(even) {
            background-color: white;
        }
        
        .bulk-table tbody tr:hover {
            background-color: #e9ecef;
        }
        
        /* 週末樣式 */
        .weekend-cell {
            background-color:#dee2e6 !important;
        }
        
        /* 空值樣式 */
        .empty-cell {
            background-color: #fff3cd !important;
        }
        
        /* 高亮指示器 */
        .highlight-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 14px;
            z-index: 1000;
            display: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .highlight-indicator.show {
            display: block;
            animation: fadeIn 0.3s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* 快速導航 */
        .quick-nav {
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            z-index: 1000;
        }
        
        .quick-nav-btn {
            background: #007bff;
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: all 0.2s;
        }
        
        .quick-nav-btn:hover {
            background: #0056b3;
            transform: scale(1.1);
        }
        
        /* 搜尋功能 */
        .search-box {
            margin: 15px 0;
            text-align: center;
        }
        
        .search-input {
            padding: 8px 15px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            width: 300px;
            max-width: 100%;
            font-size: 14px;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
        }
        
        /* 焦點樣式 */
        .focused-store {
            background: linear-gradient(90deg, #ffd763 0%, #ffd763 100%) !important;
            font-weight: bold;
        }
        
        .focused-date {
            background: linear-gradient(180deg, #ffd763 0%, #ffd763 100%) !important;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- 頁首 -->
        <div class="header">
            <h1>批量編輯月度業績 - 管理員功能</h1>
            <a href="../dashboard.php" class="back-btn">返回儀表板</a>
        </div>
        

        
        <!-- 訊息顯示 -->
        <?php if ($success_message): ?>
            <div class="message success">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="message error">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <!-- 月份選擇器 -->
        <div class="month-selector">
            <div class="month-nav">
                <a href="?month=<?php echo $prev_month; ?>" class="month-btn">← 上個月</a>
                <div class="current-month"><?php echo $selected_month; ?></div>
                <a href="?month=<?php echo $next_month; ?>" class="month-btn">下個月 →</a>
            </div>
            
            <div style="margin-top: 15px;">
                <form method="GET" action="" style="display: inline-block;">
                    <input type="month" name="month" value="<?php echo $selected_month; ?>" 
                           style="padding: 8px; border: 1px solid #ced4da; border-radius: 5px; font-size: 16px;">
                    <button type="submit" style="padding: 8px 15px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
                        跳轉月份
                    </button>
                </form>
            </div>
        </div>
        
        <!-- 搜尋功能 -->
        <div class="search-box">
            <input type="text" id="store-search" class="search-input" placeholder="搜尋店櫃代號或名稱..." 
                   onkeyup="highlightStores(this.value)">
            <button onclick="clearSearch()" style="padding: 8px 15px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">
                清除搜尋
            </button>
        </div>
        
        <!-- 高亮指示器 -->
        <div id="highlight-indicator" class="highlight-indicator">
            正在編輯：<span id="current-store"></span> - <span id="current-date"></span>
        </div>
        
        <!-- 批量編輯表格 -->
        <div class="bulk-edit-table">
            <div class="table-container">
                <form id="bulk-edit-form" method="POST">
                    <input type="hidden" name="month" value="<?php echo $selected_month; ?>">
                    <input type="hidden" name="bulk_data" id="bulk-data">
                    
                    <table class="bulk-table">
                        <thead>
                            <tr>
                                <th rowspan="2" class="store-header" style="min-width: 80px;">店櫃代號</th>
                                <th rowspan="2" class="store-header" style="min-width: 120px;">店櫃名稱</th>
                                <?php foreach ($dates as $date): 
                                    $day_of_week = date('w', strtotime($date));
                                    $is_weekend = ($day_of_week == 0 || $day_of_week == 6);
                                ?>
                                <th class="date-header <?php echo $is_weekend ? '' : ''; ?>" style="min-width: 100px;"
                                    data-date="<?php echo $date; ?>">
                                    <?php echo date('m/d', strtotime($date)); ?><br>
                                    <small><?php echo ['日','一','二','三','四','五','六'][$day_of_week]; ?></small>
                                </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stores as $store): 
                                $store_code = $store['code'];
                            ?>
                            <tr data-store="<?php echo $store_code; ?>">
                                <td class="store-header" data-store="<?php echo $store_code; ?>">
                                    <?php echo htmlspecialchars($store_code); ?>
                                </td>
                                <td class="store-header" style="text-align: left; padding-left: 10px;" 
                                    data-store="<?php echo $store_code; ?>">
                                    <?php echo htmlspecialchars($store['name']); ?>
                                </td>
                                <?php foreach ($dates as $date): 
                                    $day_of_week = date('w', strtotime($date));
                                    $is_weekend = ($day_of_week == 0 || $day_of_week == 6);
                                    
                                    $amount = isset($monthly_sales[$date][$store_code]) ? 
                                              $monthly_sales[$date][$store_code]['amount'] ?? '' : '';
                                    $role = isset($monthly_sales[$date][$store_code]) ? 
                                            ($monthly_sales[$date][$store_code]['role'] ?? 'main') : 'main';
                                ?>
                                <td class="<?php echo $is_weekend ? 'weekend-cell' : ''; ?> <?php echo $amount === '' ? 'empty-cell' : ''; ?>"
                                    data-date="<?php echo $date; ?>"
                                    data-store="<?php echo $store_code; ?>">
                                    <div style="display: flex; flex-direction: column; gap: 5px; align-items: center;">
                                        <input type="number" 
                                               name="amount[<?php echo $date; ?>][<?php echo $store_code; ?>]" 
                                               class="amount-input" 
                                               value="<?php echo $amount !== '' ? htmlspecialchars($amount) : ''; ?>" 
                                               placeholder="金額" 
                                               min="0"
                                               data-date="<?php echo $date; ?>"
                                               data-store="<?php echo $store_code; ?>"
                                               onfocus="highlightCell(this)"
                                               onblur="unhighlightCell(this)">
                                        
                                        <select name="role[<?php echo $date; ?>][<?php echo $store_code; ?>]" 
                                                class="role-select"
                                                data-date="<?php echo $date; ?>"
                                                data-store="<?php echo $store_code; ?>"
                                                onfocus="highlightCell(this)"
                                                onblur="unhighlightCell(this)">
                                            <option value="main" <?php echo $role === 'main' ? 'selected' : ''; ?>>主櫃</option>
                                            <option value="substitute" <?php echo $role === 'substitute' ? 'selected' : ''; ?>>代班</option>
                                        </select>
                                        
                                        <?php 
                                        // 取得付款狀態
                                        $payment_status = isset($monthly_sales[$date][$store_code]) ? 
                                                         ($monthly_sales[$date][$store_code]['payment_status'] ?? 'unpaid') : 'unpaid';
                                        ?>
                                        <select name="payment_status[<?php echo $date; ?>][<?php echo $store_code; ?>]" 
                                                class="payment-status-select"
                                                data-date="<?php echo $date; ?>"
                                                data-store="<?php echo $store_code; ?>"
                                                onfocus="highlightCell(this)"
                                                onblur="unhighlightCell(this)">
                                            <option value="unpaid" <?php echo $payment_status === 'unpaid' ? 'selected' : ''; ?>>未收</option>
                                            <option value="paid" <?php echo $payment_status === 'paid' ? 'selected' : ''; ?>>已收</option>
                                            <option value="dayoff" <?php echo $payment_status === 'dayoff' ? 'selected' : ''; ?>>店休</option>
                                        </select>
                                    </div>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
        
        <!-- 操作按鈕 -->
        <div class="actions">
            <button type="button" class="save-btn" onclick="saveBulkData()">儲存所有變更</button>
        </div>
        
        <!-- 快速導航按鈕 -->
        <div class="quick-nav">
            <button class="quick-nav-btn" onclick="scrollToTop()" title="回到頂部">↑</button>
            <button class="quick-nav-btn" onclick="scrollToBottom()" title="到底部">↓</button>
            <button class="quick-nav-btn" onclick="toggleHighlightMode()" title="切換高亮模式" id="highlight-toggle">🔍</button>
        </div>
    </div>
    
    <script>
        // 高亮模式狀態
        let highlightMode = 'both'; // both, store, date, none
        let currentHighlightedStore = null;
        let currentHighlightedDate = null;
        
        // 高亮當前編輯的單元格
        function highlightCell(element) {
            const date = element.dataset.date;
            const store = element.dataset.store;
            
            // 更新指示器
            document.getElementById('current-store').textContent = store;
            document.getElementById('current-date').textContent = date;
            document.getElementById('highlight-indicator').classList.add('show');
            
            // 移除之前的高亮
            removeAllHighlights();
            
            // 根據模式添加高亮
            if (highlightMode === 'both' || highlightMode === 'store') {
                // 高亮對應的店櫃行
                const storeHeaders = document.querySelectorAll(`.store-header[data-store="${store}"]`);
                storeHeaders.forEach(header => {
                    header.classList.add('highlight');
                });
                
                // 高亮對應的店櫃行
                const storeRow = document.querySelector(`tr[data-store="${store}"]`);
                if (storeRow) {
                    storeRow.classList.add('focused-store');
                }
            }
            
            if (highlightMode === 'both' || highlightMode === 'date') {
                // 高亮對應的日期欄
                const dateHeaders = document.querySelectorAll(`.date-header[data-date="${date}"]`);
                dateHeaders.forEach(header => {
                    header.classList.add('highlight');
                });
                
                // 高亮對應的日期欄
                const dateCells = document.querySelectorAll(`td[data-date="${date}"]`);
                dateCells.forEach(cell => {
                    cell.classList.add('focused-date');
                });
            }
            
            // 高亮當前輸入框
            element.classList.add('highlight');
            
            // 儲存當前高亮狀態
            currentHighlightedStore = store;
            currentHighlightedDate = date;
        }
        
        // 移除高亮
        function unhighlightCell(element) {
            // 移除輸入框高亮
            element.classList.remove('highlight');
            
            // 延遲移除其他高亮，讓使用者有時間查看
            setTimeout(() => {
                // 檢查是否還有其他輸入框在焦點中
                const focusedInputs = document.querySelectorAll('.amount-input:focus, .role-select:focus, .payment-status-select:focus');
                if (focusedInputs.length === 0) {
                    removeAllHighlights();
                    document.getElementById('highlight-indicator').classList.remove('show');
                }
            }, 100);
        }
        
        // 移除所有高亮
        function removeAllHighlights() {
            // 移除店櫃高亮
            document.querySelectorAll('.store-header.highlight').forEach(el => {
                el.classList.remove('highlight');
            });
            
            // 移除日期高亮
            document.querySelectorAll('.date-header.highlight').forEach(el => {
                el.classList.remove('highlight');
            });
            
            // 移除行高亮
            document.querySelectorAll('.focused-store').forEach(el => {
                el.classList.remove('focused-store');
            });
            
            // 移除列高亮
            document.querySelectorAll('.focused-date').forEach(el => {
                el.classList.remove('focused-date');
            });
            
            // 移除輸入框高亮
            document.querySelectorAll('.amount-input.highlight').forEach(el => {
                el.classList.remove('highlight');
            });
            
            currentHighlightedStore = null;
            currentHighlightedDate = null;
        }
        
        // 切換高亮模式
        function toggleHighlightMode() {
            const modes = ['both', 'store', 'date', 'none'];
            const currentIndex = modes.indexOf(highlightMode);
            highlightMode = modes[(currentIndex + 1) % modes.length];
            
            const toggleBtn = document.getElementById('highlight-toggle');
            const icons = ['🔍', '🏪', '📅', '🚫'];
            toggleBtn.textContent = icons[modes.indexOf(highlightMode)];
            
            // 更新指示器標題
            const titles = ['高亮店櫃和日期', '只高亮店櫃', '只高亮日期', '關閉高亮'];
            toggleBtn.title = titles[modes.indexOf(highlightMode)];
            
            // 如果當前有高亮，重新應用
            if (currentHighlightedStore && currentHighlightedDate) {
                removeAllHighlights();
                
                // 模擬重新聚焦
                const input = document.querySelector(`.amount-input[data-store="${currentHighlightedStore}"][data-date="${currentHighlightedDate}"]`);
                if (input) {
                    highlightCell(input);
                }
            }
        }
        
        // 搜尋店櫃
        function highlightStores(searchTerm) {
            if (!searchTerm.trim()) {
                // 清除搜尋
                document.querySelectorAll('tr[data-store]').forEach(row => {
                    row.style.display = '';
                });
                return;
            }
            
            const searchLower = searchTerm.toLowerCase();
            document.querySelectorAll('tr[data-store]').forEach(row => {
                const storeCode = row.dataset.store;
                const storeName = row.querySelector('.store-header[data-store]').textContent.toLowerCase();
                
                if (storeCode.includes(searchTerm) || storeName.includes(searchLower)) {
                    row.style.display = '';
                    // 添加搜尋高亮
                    row.querySelectorAll('.store-header[data-store]').forEach(header => {
                        header.style.backgroundColor = '#fff3cd';
                    });
                } else {
                    row.style.display = 'none';
                }
            });
        }
        
        // 清除搜尋
        function clearSearch() {
            document.getElementById('store-search').value = '';
            highlightStores('');
            
            // 移除搜尋高亮
            document.querySelectorAll('.store-header[data-store]').forEach(header => {
                header.style.backgroundColor = '';
            });
        }
        
        // 收集表單資料
        function collectBulkData() {
            const formData = {};
            const amountInputs = document.querySelectorAll('.amount-input');
            const roleSelects = document.querySelectorAll('.role-select');
            const paymentStatusSelects = document.querySelectorAll('.payment-status-select');
            
            // 收集金額資料
            amountInputs.forEach(input => {
                const date = input.dataset.date;
                const store = input.dataset.store;
                const amount = input.value.trim();
                
                if (!formData[date]) {
                    formData[date] = {};
                }
                
                formData[date][store] = {
                    amount: amount,
                    role: 'main', // 預設值，下面會更新
                    payment_status: 'unpaid' // 預設值，下面會更新
                };
            });
            
            // 更新角色資料
            roleSelects.forEach(select => {
                const date = select.dataset.date;
                const store = select.dataset.store;
                
                if (formData[date] && formData[date][store]) {
                    formData[date][store].role = select.value;
                }
            });
            
            // 更新付款狀態資料
            paymentStatusSelects.forEach(select => {
                const date = select.dataset.date;
                const store = select.dataset.store;
                
                if (formData[date] && formData[date][store]) {
                    formData[date][store].payment_status = select.value;
                }
            });
            
            return formData;
        }
        
        // 儲存批量資料
        function saveBulkData() {
            const formData = collectBulkData();
            const bulkDataInput = document.getElementById('bulk-data');
            
            // 轉換為 JSON
            bulkDataInput.value = JSON.stringify(formData);
            
            // 顯示確認對話框
            if (confirm('確定要儲存所有變更嗎？這將更新該月份所有店櫃的業績資料。')) {
                // 提交表單
                document.getElementById('bulk-edit-form').submit();
            }
        }
        
        // 重設表單
        function resetForm() {
            if (confirm('確定要重設表格嗎？所有未儲存的變更將會遺失。')) {
                location.reload();
            }
        }
        
        // 快速導航功能
        function scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        
        function scrollToBottom() {
            window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
        }
        
        // 批量填寫功能
        function batchFill() {
            const amount = prompt('請輸入要批量填寫的金額：');
            if (amount !== null && !isNaN(amount) && amount >= 0) {
                const confirmMsg = `確定要將所有空白的金額欄位填寫為 ${amount} 嗎？`;
                if (confirm(confirmMsg)) {
                    const inputs = document.querySelectorAll('.amount-input');
                    inputs.forEach(input => {
                        if (!input.value.trim()) {
                            input.value = amount;
                        }
                    });
                }
            }
        }
        
        // 全部設為0
        function fillAllZero() {
            if (confirm('確定要將所有金額欄位設為 0 嗎？')) {
                const inputs = document.querySelectorAll('.amount-input');
                inputs.forEach(input => {
                    input.value = '0';
                });
            }
        }
        
        // 清空所有
        function clearAll() {
            if (confirm('確定要清空所有金額欄位嗎？')) {
                const inputs = document.querySelectorAll('.amount-input');
                inputs.forEach(input => {
                    input.value = '';
                });
            }
        }
        
        // 添加快速操作按鈕
        document.addEventListener('DOMContentLoaded', function() {
            // 註：已刪除所有批量操作按鈕，僅保留核心編輯功能
            // 已刪除按鈕：批量填寫(未填)、清空所有(特殊)、匯出CSV、重設表格
            
            // 添加鍵盤快捷鍵
            document.addEventListener('keydown', function(e) {
                // Ctrl + S 儲存
                if (e.ctrlKey && e.key === 's') {
                    e.preventDefault();
                    saveBulkData();
                }

                // Ctrl + F 搜尋
                if (e.ctrlKey && e.key === 'f') {
                    e.preventDefault();
                    document.getElementById('store-search').focus();
                }
                // Ctrl + H 切換高亮模式
                if (e.ctrlKey && e.key === 'h') {
                    e.preventDefault();
                    toggleHighlightMode();
                }
            });
            
            // 添加點擊日期表頭高亮整列功能
            document.querySelectorAll('.date-header').forEach(header => {
                header.style.cursor = 'pointer';
                header.addEventListener('click', function() {
                    const date = this.dataset.date;
                    highlightDateColumn(date);
                });
            });
            
            // 添加點擊店櫃表頭高亮整行功能
            document.querySelectorAll('.store-header[data-store]').forEach(header => {
                header.style.cursor = 'pointer';
                header.addEventListener('click', function() {
                    const store = this.dataset.store;
                    highlightStoreRow(store);
                });
            });
        });
        
        // 高亮整列（日期）
        function highlightDateColumn(date) {
            removeAllHighlights();
            
            // 高亮日期表頭
            document.querySelectorAll(`.date-header[data-date="${date}"]`).forEach(header => {
                header.classList.add('highlight');
            });
            
            // 高亮該日期所有單元格
            document.querySelectorAll(`td[data-date="${date}"]`).forEach(cell => {
                cell.classList.add('focused-date');
            });
            
            // 更新指示器
            document.getElementById('current-date').textContent = date;
            document.getElementById('current-store').textContent = '整列';
            document.getElementById('highlight-indicator').classList.add('show');
        }
        
        // 高亮整行（店櫃）
        function highlightStoreRow(store) {
            removeAllHighlights();
            
            // 高亮店櫃表頭
            document.querySelectorAll(`.store-header[data-store="${store}"]`).forEach(header => {
                header.classList.add('highlight');
            });
            
            // 高亮該店櫃所有單元格
            const storeRow = document.querySelector(`tr[data-store="${store}"]`);
            if (storeRow) {
                storeRow.classList.add('focused-store');
            }
            
            // 更新指示器
            document.getElementById('current-store').textContent = store;
            document.getElementById('current-date').textContent = '整行';
            document.getElementById('highlight-indicator').classList.add('show');
        }
        
        // 匯出 CSV 功能
        function exportToCSV() {
            const formData = collectBulkData();
            const month = '<?php echo $selected_month; ?>';
            
            // 建立 CSV 內容
            let csv = '店櫃代號,店櫃名稱,日期,金額,角色\n';
            
            for (const date in formData) {
                for (const store in formData[date]) {
                    const data = formData[date][store];
                    const storeName = document.querySelector(`.store-header[data-store="${store}"]`)?.textContent || store;
                    
                    csv += `${store},"${storeName}",${date},${data.amount},${data.role}\n`;
                }
            }
            
            // 建立下載連結
            const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `業績資料_${month}.csv`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
        
        // 自動儲存草稿（可選功能）
        let autoSaveTimer;
        function enableAutoSave() {
            const inputs = document.querySelectorAll('.amount-input, .role-select');
            inputs.forEach(input => {
                input.addEventListener('change', function() {
                    clearTimeout(autoSaveTimer);
                    autoSaveTimer = setTimeout(function() {
                        // 儲存到 localStorage
                        const formData = collectBulkData();
                        localStorage.setItem('bulk_edit_draft_' + '<?php echo $selected_month; ?>', JSON.stringify(formData));
                        console.log('草稿已自動儲存');
                    }, 2000);
                });
            });
            
            // 載入草稿
            const draft = localStorage.getItem('bulk_edit_draft_' + '<?php echo $selected_month; ?>');
            if (draft) {
                if (confirm('偵測到未儲存的草稿，是否要載入？')) {
                    loadDraft(JSON.parse(draft));
                }
            }
        }
        
        // 載入草稿
        function loadDraft(draftData) {
            for (const date in draftData) {
                for (const store in draftData[date]) {
                    const data = draftData[date][store];
                    const amountInput = document.querySelector(`.amount-input[data-date="${date}"][data-store="${store}"]`);
                    const roleSelect = document.querySelector(`.role-select[data-date="${date}"][data-store="${store}"]`);
                    
                    if (amountInput) {
                        amountInput.value = data.amount;
                    }
                    if (roleSelect) {
                        roleSelect.value = data.role;
                    }
                }
            }
        }
        
        // 啟用自動儲存（可選）
        // enableAutoSave();
    </script>
</body>
</html>