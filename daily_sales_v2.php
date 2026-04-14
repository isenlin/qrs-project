<?php
/**
 * 本月各店櫃每日業績 - 最終彈出視窗版本
 * 簡化介面，包含 PDF（瀏覽器列印）和 CSV 匯出功能
 */

// 啟動 Session
session_start();

require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/auth_simple.php';

// 檢查登入
require_login();

$user = get_current_session_user();

// 檢查權限：業務、督導、管理員或老闆
if (!in_array($user['role'], ['sales', 'supervisor', 'admin', 'boss'])) {
    echo '<script>alert("權限不足"); window.close();</script>';
    exit;
}

// 取得查詢月份
$month = $_GET['month'] ?? date('Y-m');
$prev_month = date('Y-m', strtotime($month . ' -1 month'));
$next_month = date('Y-m', strtotime($month . ' +1 month'));

// 取得今天日期（用於判斷是否為當天之前）
$today = date('Y-m-d');

// 載入資料
$stores = load_data('stores');
$users = load_data('users');
$sales_summary = load_monthly_sales($month);

// 檢查是否要顯示所有店櫃（從「本月各店櫃每日業績」卡片點擊）
$show_all_stores = isset($_GET['all_stores']) && $_GET['all_stores'] == '1';

// 根據角色篩選店櫃（排除已結束的店櫃）
$user_stores = [];
if ($show_all_stores || in_array($user['role'], ['boss', 'admin'])) {
    // 顯示所有店櫃：1) 有all_stores參數 2) 老闆/管理員（只顯示 active 狀態）
    foreach ($stores as $store) {
        if (($store['status'] ?? 'active') === 'active') {
            $user_stores[] = $store;
        }
    }
} elseif ($user['role'] === 'store') {
    // 店櫃人員：只看到自己的店櫃（不檢查狀態，因為店櫃人員需要看到自己的店）
    foreach ($stores as $store) {
        if ($store['code'] === $user['id']) {
            $user_stores[] = $store;
            break;
        }
    }
} else {
    // 業務/督導：只看到自己負責的店櫃（只包含 active 狀態）
    foreach ($stores as $store) {
        if (($store['status'] ?? 'active') === 'active') {
            if ($user['role'] === 'sales' && $store['sales_person'] === $user['id']) {
                $user_stores[] = $store;
            } elseif ($user['role'] === 'supervisor' && $store['supervisor'] === $user['id']) {
                $user_stores[] = $store;
            }
        }
    }
}

// 篩選指定月份的銷售資料
$month_sales = [];
foreach ($sales_summary as $date => $daily_sales) {
    if (strpos($date, $month) === 0) {
        $month_sales[$date] = $daily_sales;
    }
}

// 計算統計
$store_totals = [];
$entry_counts = [];

foreach ($user_stores as $store) {
    $store_code = $store['code'];
    $store_total = 0;
    $store_entries = 0;
    
    foreach ($month_sales as $date => $daily_sales) {
        if (isset($daily_sales[$store_code])) {
            $amount = $daily_sales[$store_code]['amount'] ?? 0;
            $store_total += $amount;
            $store_entries++;
        }
    }
    
    $store_totals[$store_code] = $store_total;
    $entry_counts[$store_code] = $store_entries;
}

// 計算平均
$store_averages = [];
foreach ($store_totals as $store_code => $total) {
    $entries = $entry_counts[$store_code];
    $store_averages[$store_code] = $entries > 0 ? round($total / $entries) : 0;
}

// 生成日期列表
$dates = [];
$current = strtotime($month . '-01');
$end = strtotime(date('Y-m-t', $current));

while ($current <= $end) {
    $dates[] = date('Y-m-d', $current);
    $current = strtotime('+1 day', $current);
}

// 計算未登打和未收款統計
$missing_counts = []; // 每個日期未填業績的店櫃數量
$unpaid_counts = []; // 每個日期未收款的店櫃數量

foreach ($dates as $date) {
    $missing_count = 0;
    $unpaid_count = 0;
    
    foreach ($user_stores as $store) {
        $store_code = $store['code'];
        
        // 檢查是否有資料
        $has_data = isset($month_sales[$date][$store_code]);
        $amount = $has_data ? ($month_sales[$date][$store_code]['amount'] ?? null) : null;
        $payment_status = $has_data ? ($month_sales[$date][$store_code]['payment_status'] ?? 'unpaid') : 'unpaid';
        
        // 未登打：當天之前無資料且非店休
        if ($amount === null && $date < $today && $payment_status !== 'dayoff') {
            $missing_count++;
        }
        
        // 未收款：有資料且收款狀態為未收（排除店休和無資料的情況）
        if ($has_data && $payment_status === 'unpaid' && $payment_status !== 'dayoff') {
            $unpaid_count++;
        }
    }
    
    $missing_counts[$date] = $missing_count;
    $unpaid_counts[$date] = $unpaid_count;
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>本月各店櫃每日業績 - <?php echo $month; ?></title>
    <style>
        /* 基礎重置 */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: "Microsoft JhengHei", sans-serif; 
            background: #f8f9fa; 
            padding: 0; /* 改為 0，避免撐開視窗 */
            color: #333;
            margin: 0;
            height: 100vh;
            overflow: hidden; /* 強制隱藏最外層捲動條 */
            display: flex;
            justify-content: center;
            align-items: center; /* 居中對齊 */
        }
        
        /* 彈出視窗容器 - 使用flexbox佔滿視窗 */
        .popup-container {
            width: 95vw;
            height: 95vh; /* 不要用 100vh，留一點邊界感 */
            margin: 0; 
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        /* 頁面標題 - 固定高度，不伸縮 */
        .popup-header {
            background: #4a6fa5;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            flex-shrink: 0; /* 不伸縮 */
        }
        
        .popup-header h1 {
            font-size: 20px;
            font-weight: 700;
            margin: 0;
        }
        
        /* 月份導航 */
        .month-nav {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        
        .month-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            border: 1px solid rgba(255,255,255,0.3);
            transition: all 0.2s;
        }
        
        .month-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .current-month {
            font-size: 16px;
            font-weight: 700;
            background: rgba(255,255,255,0.1);
            padding: 6px 12px;
            border-radius: 4px;
            min-width: 120px;
            text-align: center;
        }
        
        /* 控制按鈕 */
        .control-btns {
            display: flex;
            gap: 8px;
        }
        
        .ctrl-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .csv-btn {
            background: #28a745;
            color: white;
        }
        
        .csv-btn:hover {
            background: #218838;
        }
        
        .close-btn {
            background: #6c757d;
            color: white;
        }
        
        .close-btn:hover {
            background: #5a6268;
        }
        
        /* 表格容器 - 佔據剩餘空間，避免雙重滾動 */
        .table-wrapper { 
            overflow: auto; /* 讓系統自動判斷 x 與 y */
            background: #fff; 
            flex: 1; 
            width: 100%;
            /* 這裡不需要 min-height，因為 flex: 1 會自動撐開 */
        }
        
        /* 自定義滾動條 - Webkit瀏覽器 */
        .table-wrapper::-webkit-scrollbar {
            width: 20px;  /* 垂直滾動條寬度 - 調大 */
            height: 20px; /* 水平滾動條高度 - 調大 */
        }
        
        .table-wrapper::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;  /* 調大圓角 */
        }
        
        .table-wrapper::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;  /* 調大圓角 */
            border: 3px solid #f1f1f1;  /* 調大邊框 */
        }
        
        .table-wrapper::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        
        .table-wrapper::-webkit-scrollbar-corner {
            background: #f1f1f1;
        }
        
        /* 表格樣式 */
        .sales-table { 
            width: 100%; 
            border-collapse: collapse; 
            min-width: 800px; 
            font-size: 13px;
        }
        
        .sales-table th, .sales-table td { 
            border: 1px solid #dee2e6; 
            padding: 8px 5px; 
            text-align: center; 
        }
        
        /* 表頭固定 */
        .sales-table thead th { 
            background: #f8f9fa; 
            font-weight: 700; 
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        /* 左欄固定 */
        .sales-table tbody td:first-child {
            background: #f8f9fa;
            font-weight: 600;
            text-align: left;
            padding-left: 8px;
            position: sticky;
            left: 0;
            z-index: 5;
            min-width: 70px;  /* 縮減一半 */
            max-width: 70px;  /* 限制最大寬度 */
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        /* 斑馬條紋 */
        .sales-table tbody tr:nth-child(even) { 
            background-color: #ffffff; 
        }
        
        .sales-table tbody tr:nth-child(odd) { 
            background-color: #f8fbfe; 
        }
        
        /* 店櫃資訊 */
        .store-cell {
            line-height: 1.3;  /* 調整行高適應放大字體 */
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .store-code {
            font-weight: 700;
            font-size: 13px;  /* 放大2級：11px → 13px */
            color: #333;
        }
        
        .store-name {
            color: #666;
            font-size: 12px;  /* 再放大一級：11px → 12px */
            margin-top: 2px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        /* 週末樣式 */
        .weekend { 
            background-color: #fff8e1 !important; 
        }
        
        /* 金額樣式 */
        .amount {
            font-weight: 500;
        }
        
        .amount-zero {
            color: #999;
        }
        
        /* 總計行 */
        .total-row { 
            background-color: #e8f5e9 !important; 
            font-weight: 700; 
        }
        
        .average-row { 
            background-color: #fff3e0 !important; 
            font-style: italic; 
        }
        
        /* 代班標記 */
        .substitute {
            color: #666;
            font-size: 9px;
            margin-top: 1px;
            font-weight: bold;
            display: block;
            text-align: center;
        }
        
        /* 收款狀態標記 */
        .payment-status {
            font-size: 9px;
            font-weight: bold;
            padding: 1px 4px;
            border-radius: 3px;
            margin-top: 2px;
            display: block;
            text-align: center;
            min-width: 36px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .payment-unpaid {
            background-color: #ffebee;  /* 淺粉紅 */
            color: #c62828;            /* 深紅 */
            border: 1px solid #ffcdd2;
        }
        
        .payment-paid {
            background-color: #e8f5e9;  /* 淺綠 */
            color: #2e7d32;            /* 深綠 */
            border: 1px solid #c8e6c9;
        }
        
        .payment-dayoff {
            background-color: #fff3e0;  /* 淺橙 */
            color: #ef6c00;            /* 深橙 */
            border: 1px solid #ffe0b2;
        }
        
        /* 金額容器 */
        .amount-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 2px 0;
        }
        
        /* 未填業績高亮樣式 */
        .missing-highlight {
            font-size: 16px; /* 放大一級 */
            color: #ff0000 !important; /* 紅色 */
            font-weight: bold !important;
            animation: missing-blink 2s infinite;
        }
        
        /* 未填業績單元格高亮樣式 */
        .missing-cell-highlight {
            background-color: #ffebee !important; /* 淺紅色背景 */
            box-shadow: inset 0 0 0 1px #ff0000 !important; /* 紅色內陰影模擬邊框（細線條） */
            position: relative;
            z-index: 1;
        }
        
        .missing-cell-highlight .amount {
            color: #c62828 !important; /* 深紅色文字 */
            font-weight: bold !important;
        }
        
        @keyframes missing-blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0.7; }
        }
        
        /* 未登打和未收款統計行 */
        .stats-row {
            background-color: #fff3e0 !important; /* 淺橙色背景 */
            font-weight: 600;
        }
        
        .stats-row .stats-label {
            background-color: #ff9800 !important; /* 橙色標籤 */
            color: white;
            text-align: center;
            font-weight: 700;
        }
        
        .stats-row .stats-value {
            background-color: #ffe0b2 !important; /* 更淺的橙色 */
            text-align: center;
            font-weight: 700;
            color: #333;
        }
        
        .stats-row .stats-value.highlight {
            background-color: #ffcdd2 !important; /* 淺紅色 */
            color: #c62828 !important; /* 深紅色 */
            font-weight: 700;
        }
        
        /* 狀態指示器容器 */
        .status-indicators {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1px;
            margin-top: 2px;
            width: 100%;
        }
        
        /* 當兩個狀態都顯示時的排列 */
        .status-indicators .substitute + .payment-status {
            margin-top: 1px;
        }
        
        /* 表格控制區 */
        .table-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 10px 0;
            padding: 0 5px;
            flex-wrap: wrap;
            gap: 10px;
            flex-shrink: 0; /* 不伸縮 */
        }
        
        /* 滾動提示 */
        .scroll-hint {
            text-align: center;
            padding: 8px 12px;
            background: #fff8e1;
            color: #d69e2e;
            border-radius: 5px;
            font-size: 12px;
            flex-grow: 1;
            min-width: 200px;
        }
        
        /* 切換按鈕區 */
        .toggle-buttons {
            display: flex;
            gap: 8px;
        }
        
        /* 切換按鈕 */
        .toggle-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 15px;
            border: 2px solid #6c757d;
            background: white;
            color: #6c757d;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }
        
        .toggle-btn:hover {
            background: #f8f9fa;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .toggle-btn.active {
            background: #007bff;
            border-color: #007bff;
            color: white;
        }
        
        .toggle-btn.active:hover {
            background: #0056b3;
            border-color: #0056b3;
        }
        
        /* 編輯模式按鈕 */
        .toggle-btn.edit-mode-btn {
            border-color: #6f42c1;
            color: #6f42c1;
        }
        
        .toggle-btn.edit-mode-btn:hover {
            background: #f8f9fa;
            border-color: #5a32a3;
            color: #5a32a3;
        }
        
        .toggle-btn.edit-mode-btn.active {
            background: #6f42c1;
            border-color: #6f42c1;
            color: white;
        }
        
        .toggle-btn.edit-mode-btn.active:hover {
            background: #5a32a3;
            border-color: #5a32a3;
        }
        
        .toggle-icon {
            font-size: 13px;
        }
        
        .toggle-text {
            font-size: 11px;
            font-weight: 600;
        }
        
        /* 可編輯單元格樣式（編輯模式） */
        .editable-cell {
            cursor: pointer;
            position: relative;
        }
        
        .editable-cell:hover {
            background-color: #e8f4fd !important;
            box-shadow: inset 0 0 0 2px #007bff;
        }
        
        .editable-cell::after {
            content: '✏️';
            position: absolute;
            top: 2px;
            right: 2px;
            font-size: 10px;
            opacity: 0.7;
        }
        
        /* 無資料提示 */
        .no-data {
            padding: 40px;
            text-align: center;
            color: #6c757d;
        }
        
        /* 手機響應式 */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .popup-container {
                width: 100vw;
                height: 100vh;
                border-radius: 0;
            }
            
            .popup-header {
                padding: 12px;
                flex-direction: column;
                gap: 8px;
            }
            
            .popup-header h1 {
                font-size: 18px;
                text-align: center;
            }
            
            .month-nav, .control-btns {
                width: 100%;
                justify-content: center;
            }
            
            .sales-table {
                font-size: 12px;
                min-width: 700px;
            }
            
            .sales-table th, 
            .sales-table td {
                padding: 6px 4px;
            }
            
            .sales-table tbody td:first-child {
                min-width: 120px;
                padding-left: 8px;
            }
        }
        
        /* 載入中樣式 */
        .loading {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
            margin-right: 8px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        

    </style>
</head>
<body>
    <div class="popup-container">
        <div class="popup-header">
            <h1>📊 <?php echo $show_all_stores ? '全公司' : ''; ?>本月各店櫃每日業績</h1>
            
            <div class="month-nav">
                <a href="?month=<?php echo $prev_month; ?><?php echo $show_all_stores ? '&all_stores=1' : ''; ?>" class="month-btn">◀ 上個月</a>
                <div class="current-month"><?php echo date('Y年m月', strtotime($month)); ?></div>
                <a href="?month=<?php echo $next_month; ?><?php echo $show_all_stores ? '&all_stores=1' : ''; ?>" class="month-btn">下個月 ▶</a>
            </div>
            
            <div class="control-btns">
                <button onclick="exportToCSV()" class="ctrl-btn csv-btn">
                    <span>📊</span>
                    <span>CSV</span>
                </button>
                <button onclick="window.close()" class="ctrl-btn close-btn">
                    <span>✕</span>
                    <span>關閉</span>
                </button>
            </div>
        </div>
        
        <?php if (empty($user_stores)): ?>
            <div class="no-data">
                <h3>無負責店櫃</h3>
                <p>您目前沒有負責的店櫃</p>
            </div>
        <?php else: ?>
            <div class="table-controls">
                <div class="scroll-hint">
                    可左右滑動查看完整表格
                </div>
                <div class="toggle-buttons">
                    <?php if (in_array($user['role'], ['admin', 'boss'])): ?>
                    <button id="toggle-edit" class="toggle-btn edit-mode-btn" data-type="edit" title="進入編輯模式">
                        <span class="toggle-icon">✏️</span>
                        <span class="toggle-text">編輯</span>
                    </button>
                    <?php endif; ?>
                    <button id="toggle-substitute" class="toggle-btn active" data-type="substitute">
                        <span class="toggle-icon">👥</span>
                        <span class="toggle-text">代班顯示</span>
                    </button>
                    <button id="toggle-missing" class="toggle-btn" data-type="missing">
                        <span class="toggle-icon">📝</span>
                        <span class="toggle-text">未填業績</span>
                    </button>
                    <button id="toggle-payment" class="toggle-btn" data-type="payment">
                        <span class="toggle-icon">💰</span>
                        <span class="toggle-text">收款狀態</span>
                    </button>
                </div>
            </div>
            
            <div class="table-wrapper">
                <table class="sales-table">
                    <thead>
                        <tr>
                            <th rowspan="2" style="min-width: 70px;">店櫃</th>
                            <?php foreach ($dates as $date): 
                                $day_w = date('w', strtotime($date));
                                $is_we = ($day_w == 0 || $day_w == 6);
                            ?>
                            <th class="<?php echo $is_we ? 'weekend' : ''; ?>" style="min-width: 65px;">
                                <?php echo date('d', strtotime($date)); ?><br>
                                <small style="font-size: 10px;">
                                    <?php echo ['日','一','二','三','四','五','六'][$day_w]; ?>
                                </small>
                            </th>
                            <?php endforeach; ?>
                            <th rowspan="2" style="min-width: 80px;">總計</th>
                            <th rowspan="2" style="min-width: 80px;">平均</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($user_stores as $store): 
                            $sc = $store['code'];
                            $s_total = $store_totals[$sc] ?? 0;
                            $s_avg = $store_averages[$sc] ?? 0;
                        ?>
                        <tr>
                            <td>
                                <div class="store-cell" title="<?php echo htmlspecialchars($store['name']); ?>">
                                    <div class="store-code"><?php echo htmlspecialchars($sc); ?></div>
                                    <div class="store-name"><?php 
                                        // 顯示縮短後的店櫃名稱
                                        $shortName = mb_strlen($store['name'], 'UTF-8') > 6 ? 
                                            mb_substr($store['name'], 0, 6, 'UTF-8') . '...' : 
                                            $store['name'];
                                        echo htmlspecialchars($shortName);
                                    ?></div>
                                </div>
                            </td>
                            <?php foreach ($dates as $date): 
                                $day_w = date('w', strtotime($date));
                                $is_we = ($day_w == 0 || $day_w == 6);
                                $val = isset($month_sales[$date][$sc]) ? ($month_sales[$date][$sc]['amount'] ?? 0) : null;
                                $role = isset($month_sales[$date][$sc]) ? ($month_sales[$date][$sc]['role'] ?? 'main') : 'main';
                                
                                // 獲取收款狀態（從銷售資料中）
                                $payment_status = isset($month_sales[$date][$sc]['payment_status']) ? $month_sales[$date][$sc]['payment_status'] : 'unpaid';
                                
                                // 判斷是否應該顯示 -（店休或無資料）
                                $should_show_dash = false;
                                $display_amount = $val;
                                
                                if ($val === null) {
                                    // 無資料
                                    $should_show_dash = true;
                                } elseif ($payment_status === 'dayoff') {
                                    // 店休狀態，無論 amount 是什麼都顯示 -
                                    $should_show_dash = true;
                                    $display_amount = null;
                                }
                                
                                // 判斷是否為當天之前未填業績（無資料且非店休）
                                $is_missing_data = false;
                                if ($val === null && $date < $today && $payment_status !== 'dayoff') {
                                    $is_missing_data = true;
                                }
                            ?>
                            <td class="<?php echo $is_we ? 'weekend' : ''; ?>" data-date="<?php echo $date; ?>" data-store="<?php echo $sc; ?>" data-missing-cell="<?php echo $is_missing_data ? 'true' : 'false'; ?>">
                                
                                <?php if ($should_show_dash): ?>
                                    <div class="amount-container">
                                        <span class="amount amount-zero" 
                                              data-missing="<?php echo $is_missing_data ? 'true' : 'false'; ?>"
                                              data-date="<?php echo $date; ?>">
                                            -
                                        </span>
                                        <?php if ($payment_status === 'dayoff'): ?>
                                        <div class="status-indicators">
                                            <span class="payment-status payment-<?php echo $payment_status; ?>" data-type="payment">
                                                店休
                                            </span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="amount-container">
                                        <span class="amount <?php echo $display_amount === 0 ? 'amount-zero' : ''; ?>">
                                            <?php echo number_format($display_amount); ?>
                                        </span>
                                        <div class="status-indicators">
                                            <?php if ($role === 'substitute'): ?>
                                                <span class="substitute" data-type="substitute">代</span>
                                            <?php endif; ?>
                                            <span class="payment-status payment-<?php echo $payment_status; ?>" data-type="payment">
                                                <?php 
                                                if ($payment_status === 'paid') {
                                                    echo '已收';
                                                } elseif ($payment_status === 'dayoff') {
                                                    echo '店休';
                                                } else {
                                                    echo '未收';
                                                }
                                                ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <?php endforeach; ?>
                            <td class="total-row"><?php echo number_format($s_total); ?></td>
                            <td class="average-row"><?php echo number_format($s_avg); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <!-- 每日全店總計行 -->
                        <tr class="total-row">
                            <td colspan="1" style="text-align: center; font-weight: bold;">店總計</td>
                            <?php 
                            // 計算每日總計
                            $daily_totals = [];
                            foreach ($dates as $date) {
                                $d_total = 0;
                                foreach ($user_stores as $store) {
                                    $store_code = $store['code'];
                                    if (isset($month_sales[$date][$store_code])) {
                                        $d_total += $month_sales[$date][$store_code]['amount'] ?? 0;
                                    }
                                }
                                $daily_totals[$date] = $d_total;
                            }
                            
                            // 計算月份總計
                            $month_total = array_sum($daily_totals);
                            
                            // 輸出每日總計
                            foreach ($dates as $date): 
                                $day_w = date('w', strtotime($date));
                                $is_we = ($day_w == 0 || $day_w == 6);
                            ?>
                            <td class="<?php echo $is_we ? 'weekend' : ''; ?>" style="font-weight: bold;">
                                <?php echo number_format($daily_totals[$date]); ?>
                            </td>
                            <?php endforeach; ?>
                            <td style="font-weight: bold; background-color: #d4edda;"><?php echo number_format($month_total); ?></td>
                            <td style="font-style: italic;">-</td>
                        </tr>
                        
                        <!-- 未登打統計行 -->
                        <tr class="stats-row">
                            <td class="stats-label">未登打</td>
                            <?php foreach ($dates as $date): 
                                $day_w = date('w', strtotime($date));
                                $is_we = ($day_w == 0 || $day_w == 6);
                                $missing_count = $missing_counts[$date] ?? 0;
                            ?>
                            <td class="<?php echo $is_we ? 'weekend' : ''; ?> stats-value <?php echo $missing_count > 0 ? 'highlight' : ''; ?>" 
                                style="font-weight: bold;">
                                <?php echo $missing_count > 0 ? $missing_count : ''; ?>
                            </td>
                            <?php endforeach; ?>
                            <td class="stats-value">-</td>
                            <td class="stats-value">-</td>
                        </tr>
                        
                        <!-- 未收款統計行 -->
                        <tr class="stats-row">
                            <td class="stats-label">未收款</td>
                            <?php foreach ($dates as $date): 
                                $day_w = date('w', strtotime($date));
                                $is_we = ($day_w == 0 || $day_w == 6);
                                $unpaid_count = $unpaid_counts[$date] ?? 0;
                            ?>
                            <td class="<?php echo $is_we ? 'weekend' : ''; ?> stats-value <?php echo $unpaid_count > 0 ? 'highlight' : ''; ?>" 
                                style="font-weight: bold;">
                                <?php echo $unpaid_count > 0 ? $unpaid_count : ''; ?>
                            </td>
                            <?php endforeach; ?>
                            <td class="stats-value">-</td>
                            <td class="stats-value">-</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // 自動調整表格高度
        // 表格高度現在由CSS flexbox自動管理，無需JavaScript調整
        
        // 匯出為 CSV
        function exportToCSV() {
            const button = event.target.closest('button');
            const originalHTML = button.innerHTML;
            const month = document.querySelector('.current-month').textContent.trim();
            
            // 顯示載入中
            button.innerHTML = '<span class="loading"></span> 處理中...';
            button.disabled = true;
            
            // 收集表格資料
            const table = document.querySelector('.sales-table');
            const headers = [];
            const rows = [];
            
            // 取得表頭
            const headerCells = table.querySelectorAll('thead th');
            headerCells.forEach(cell => {
                let text = cell.textContent.trim();
                text = text.replace(/\n/g, ' ').replace(/\s+/g, ' ');
                headers.push(`"${text}"`);
            });
            
            // 取得資料行
            const dataRows = table.querySelectorAll('tbody tr');
            dataRows.forEach(row => {
                const rowData = [];
                const cells = row.querySelectorAll('td');
                
                cells.forEach(cell => {
                    let text = cell.textContent.trim();
                    text = text.replace(/\n/g, ' ').replace(/\s+/g, ' ');
                    
                    // 如果是金額，移除千分位逗號
                    if (/^\d{1,3}(,\d{3})*$/.test(text)) {
                        text = text.replace(/,/g, '');
                    }
                    
                    rowData.push(`"${text}"`);
                });
                
                rows.push(rowData.join(','));
            });
            
            // 建立 CSV 內容
            const csvContent = [
                `"${month} 各店櫃每日業績報表"`,
                `"匯出時間: ${new Date().toLocaleString('zh-TW')}"`,
                '',
                headers.join(','),
                ...rows
            ].join('\n');
            
            // 建立下載連結
            const blob = new Blob(['\ufeff' + csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `${month.replace('年', '-').replace('月', '')}_各店櫃每日業績報表.csv`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // 恢復按鈕狀態
            setTimeout(() => {
                button.innerHTML = originalHTML;
                button.disabled = false;
            }, 500);
        }
        
        console.log('彈出視窗已載入');
        
        // 代班顯示、未填業績和收款狀態切換功能
        document.addEventListener('DOMContentLoaded', function() {
            const substituteBtn = document.getElementById('toggle-substitute');
            const missingBtn = document.getElementById('toggle-missing');
            const paymentBtn = document.getElementById('toggle-payment');
            
            // 初始化狀態
            let showSubstitute = true;  // 預設顯示代班
            let showMissing = false;    // 預設不顯示未填業績
            let showPayment = false;    // 預設不顯示收款狀態
            
            // 更新未填業績顯示
            function updateMissingDisplay() {
                const missingSpans = document.querySelectorAll('.amount[data-missing="true"]');
                missingSpans.forEach(span => {
                    if (showMissing) {
                        span.textContent = '---';
                        span.classList.add('missing-highlight');
                    } else {
                        span.textContent = '-';
                        span.classList.remove('missing-highlight');
                    }
                });
                
                // 更新未填業績單元格高亮
                const missingCells = document.querySelectorAll('td[data-missing-cell="true"]');
                missingCells.forEach(cell => {
                    if (showMissing) {
                        cell.classList.add('missing-cell-highlight');
                    } else {
                        cell.classList.remove('missing-cell-highlight');
                    }
                });
            }
            
            // 更新顯示狀態
            function updateDisplay() {
                // 更新按鈕狀態
                substituteBtn.classList.toggle('active', showSubstitute);
                missingBtn.classList.toggle('active', showMissing);
                paymentBtn.classList.toggle('active', showPayment);
                
                // 更新所有狀態指示器
                const allSubstitute = document.querySelectorAll('.substitute[data-type="substitute"]');
                const allPayment = document.querySelectorAll('.payment-status[data-type="payment"]');
                
                // 代班顯示
                allSubstitute.forEach(el => {
                    el.style.display = showSubstitute ? 'block' : 'none';
                });
                
                // 收款狀態顯示
                allPayment.forEach(el => {
                    el.style.display = showPayment ? 'block' : 'none';
                });
                
                // 調整金額容器高度
                const amountContainers = document.querySelectorAll('.amount-container');
                amountContainers.forEach(container => {
                    const hasSubstitute = container.querySelector('.substitute[data-type="substitute"]');
                    const hasPayment = container.querySelector('.payment-status[data-type="payment"]');
                    
                    let minHeight = 30; // 基本高度
                    if (showSubstitute && hasSubstitute) minHeight += 12;
                    if (showPayment && hasPayment) minHeight += 12;
                    
                    container.style.minHeight = minHeight + 'px';
                });
                
                // 更新未填業績顯示
                updateMissingDisplay();
            }
            
            // 代班顯示按鈕點擊
            substituteBtn.addEventListener('click', function() {
                showSubstitute = !showSubstitute;
                updateDisplay();
                
                // 儲存到 localStorage
                localStorage.setItem('dailySales_showSubstitute', showSubstitute);
            });
            
            // 未填業績按鈕點擊
            missingBtn.addEventListener('click', function() {
                showMissing = !showMissing;
                updateDisplay();
                
                // 儲存到 localStorage
                localStorage.setItem('dailySales_showMissing', showMissing);
            });
            
            // 收款狀態按鈕點擊
            paymentBtn.addEventListener('click', function() {
                showPayment = !showPayment;
                updateDisplay();
                
                // 儲存到 localStorage
                localStorage.setItem('dailySales_showPayment', showPayment);
            });
            
            // 從 localStorage 讀取設定
            const savedSubstitute = localStorage.getItem('dailySales_showSubstitute');
            const savedMissing = localStorage.getItem('dailySales_showMissing');
            const savedPayment = localStorage.getItem('dailySales_showPayment');
            
            if (savedSubstitute !== null) {
                showSubstitute = savedSubstitute === 'true';
            }
            
            if (savedMissing !== null) {
                showMissing = savedMissing === 'true';
            }
            
            if (savedPayment !== null) {
                showPayment = savedPayment === 'true';
            }
            
            // 初始更新
            updateDisplay();
            
            console.log('代班顯示:', showSubstitute, '未填業績:', showMissing, '收款狀態:', showPayment);

            // 編輯模式功能
            const editBtn = document.getElementById('toggle-edit');
            if (editBtn) {
                // 從 localStorage 讀取編輯模式狀態
                let isEditMode = localStorage.getItem('dailySales_editMode') === 'true';
                
                // 如果編輯模式為 true，自動進入編輯模式
                if (isEditMode) {
                    editBtn.classList.add('active');
                    enterEditMode();
                }
                
                // 編輯按鈕點擊事件
                editBtn.addEventListener('click', function() {
                    isEditMode = !isEditMode;
                    editBtn.classList.toggle('active', isEditMode);
                    
                    // 儲存編輯模式狀態到 localStorage
                    localStorage.setItem('dailySales_editMode', isEditMode);
                    
                    if (isEditMode) {
                        enterEditMode();
                    } else {
                        exitEditMode();
                    }
                });
                
                // 進入編輯模式
                function enterEditMode() {
                    // 為所有業績單元格添加點擊事件
                    const salesCells = document.querySelectorAll('td[data-date][data-store]');
                    salesCells.forEach(cell => {
                        cell.classList.add('editable-cell');
                        cell.addEventListener('click', handleCellClick);
                    });
                    console.log('進入編輯模式，點擊業績單元格進行編輯');
                }
                
                // 退出編輯模式
                function exitEditMode() {
                    // 移除所有業績單元格的點擊事件
                    const salesCells = document.querySelectorAll('td[data-date][data-store]');
                    salesCells.forEach(cell => {
                        cell.classList.remove('editable-cell');
                        cell.removeEventListener('click', handleCellClick);
                    });
                    console.log('退出編輯模式');
                }
                
                // 處理單元格點擊
                function handleCellClick(event) {
                    const cell = event.currentTarget;
                    const date = cell.getAttribute('data-date');
                    const storeCode = cell.getAttribute('data-store');
                    
                    // 儲存當前點擊的位置（用於重新整理後定位）
                    localStorage.setItem('lastEditedCell', JSON.stringify({
                        date: date,
                        storeCode: storeCode,
                        timestamp: Date.now()
                    }));
                    
                    // 開啟編輯頁面
                    openEditPopup(date, storeCode);
                }
                
                // 開啟編輯彈出視窗
                function openEditPopup(date, storeCode) {
                    // 使用類似 dashboard.php 中的 openDailySalesPopup 邏輯
                    const url = `edit_daily_sales.php?date=${date}&store=${storeCode}&month=<?php echo $month; ?>`;
                    const width = 600;
                    const height = 700;
                    const left = (window.innerWidth - width) / 2;
                    const top = (window.innerHeight - height) / 2;
                    
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
                    
                    const popup = window.open(url, 'edit_daily_sales', features);
                    
                    if (!popup || popup.closed || typeof popup.closed === 'undefined') {
                        alert('彈出視窗被阻擋，請允許彈出視窗後重試');
                    }
                }
                
                // 添加 CSS 樣式用於最近編輯的單元格
                if (!document.querySelector('#recently-edited-style')) {
                    const style = document.createElement('style');
                    style.id = 'recently-edited-style';
                    style.textContent = `
                        .recently-edited {
                            animation: flashCell 0.5s ease 3;
                            position: relative;
                            z-index: 100;
                        }
                        
                        @keyframes flashCell {
                            0%, 100% { background-color: transparent; }
                            50% { background-color: rgba(111, 66, 193, 0.2); }
                        }
                    `;
                    document.head.appendChild(style);
                }
            }
            
            // 頁面載入完成後，滾動到上次編輯的單元格
            // 使用 setTimeout 確保 DOM 完全渲染完成
            setTimeout(() => {
                const lastEditedStr = localStorage.getItem('lastEditedCell');
                if (!lastEditedStr) return;
                
                try {
                    const lastEdited = JSON.parse(lastEditedStr);
                    const date = lastEdited.date;
                    const storeCode = lastEdited.storeCode;
                    
                    // 查找對應的單元格
                    const selector = `td[data-date="${date}"][data-store="${storeCode}"]`;
                    const targetCell = document.querySelector(selector);
                    
                    if (targetCell) {
                        // 滾動到該單元格
                        targetCell.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center',
                            inline: 'center'
                        });
                        
                        // 添加視覺提示（閃爍效果）
                        targetCell.classList.add('recently-edited');
                        setTimeout(() => {
                            targetCell.classList.remove('recently-edited');
                        }, 2000);
                        
                        console.log('已滾動到上次編輯的單元格:', date, storeCode);
                    }
                    
                    // 清除記錄（避免下次載入時再次滾動）
                    localStorage.removeItem('lastEditedCell');
                } catch (e) {
                    console.error('解析 lastEditedCell 時發生錯誤:', e);
                    localStorage.removeItem('lastEditedCell');
                }
            }, 300); // 等待 300ms 確保表格完全渲染
        });
    </script>
</body>
</html>