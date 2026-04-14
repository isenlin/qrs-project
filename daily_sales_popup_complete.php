<?php
/**
 * 本月各店櫃每日業績 - 彈出視窗版本
 * 簡化版，只保留表格、月份選擇和列印功能
 */

// 啟動 Session
session_start();

require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/auth_simple.php';

// 檢查登入
require_login();

$user = get_current_session_user();

// 檢查權限：業務、督導或管理員
if (!in_array($user['role'], ['sales', 'supervisor', 'admin'])) {
    echo '<script>alert("權限不足"); window.close();</script>';
    exit;
}

// 取得查詢月份
$month = $_GET['month'] ?? date('Y-m');
$prev_month = date('Y-m', strtotime($month . ' -1 month'));
$next_month = date('Y-m', strtotime($month . ' +1 month'));

// 載入資料
$stores = load_data('stores');
$users = load_data('users');
$sales_summary = load_monthly_sales($month);

// 建立使用者代號到姓名的映射
$user_name_map = [];
foreach ($users as $user_data) {
    $user_name_map[$user_data['id']] = $user_data['name'];
}

// 根據角色篩選店櫃
$user_stores = [];
if ($user['role'] === 'admin') {
    $user_stores = $stores;
} elseif ($user['role'] === 'store') {
    foreach ($stores as $store) {
        if ($store['code'] === $user['id']) {
            $user_stores[] = $store;
            break;
        }
    }
} else {
    foreach ($stores as $store) {
        if ($user['role'] === 'sales' && $store['sales_person'] === $user['id']) {
            $user_stores[] = $store;
        } elseif ($user['role'] === 'supervisor' && $store['supervisor'] === $user['id']) {
            $user_stores[] = $store;
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
$month_total = 0;
$store_totals = [];
$entry_counts = [];

$responsible_store_codes = array_column($user_stores, 'code');

// 計算每個店櫃的總業績和登打次數
foreach ($user_stores as $store) {
    $store_code = $store['code'];
    $store_total = 0;
    $store_entries = 0;
    
    foreach ($month_sales as $date => $daily_sales) {
        if (isset($daily_sales[$store_code])) {
            $amount = $daily_sales[$store_code]['amount'] ?? 0;
            $store_total += $amount;
            $store_entries++; // 只要有記錄就算一次
        }
    }
    
    $store_totals[$store_code] = $store_total;
    $entry_counts[$store_code] = $store_entries;
    $month_total += $store_total;
}

// 計算平均（除以登打次數，不是天數）
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
            font-family: "Microsoft JhengHei", "Segoe UI", sans-serif; 
            background: #f8f9fa; 
            padding: 20px;
            color: #333;
        }
        
        /* 彈出視窗容器 */
        .popup-container {
            max-width: 95vw;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.15);
            overflow: hidden;
        }
        
        /* 頁面標題 */
        .popup-header {
            background: linear-gradient(135deg, #4a6fa5 0%, #166088 100%);
            color: white;
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .popup-header h1 {
            font-size: 22px;
            font-weight: 700;
            margin: 0;
        }
        
        /* 月份導航 */
        .month-navigation {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .month-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            border: 1px solid rgba(255,255,255,0.3);
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .month-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }
        
        .current-month {
            font-size: 18px;
            font-weight: 700;
            background: rgba(255,255,255,0.1);
            padding: 8px 16px;
            border-radius: 6px;
            min-width: 140px;
            text-align: center;
        }
        
        /* 控制按鈕 */
        .control-buttons {
            display: flex;
            gap: 10px;
        }
        
        .control-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .print-btn {
            background: #17a2b8;
            color: white;
        }
        
        .print-btn:hover {
            background: #138496;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(23, 162, 184, 0.3);
        }
        
        .close-btn {
            background: #6c757d;
            color: white;
        }
        
        .close-btn:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        /* 表格容器 */
        .table-container { 
            overflow-x: auto; 
            background: #fff; 
            position: relative;
            max-height: 70vh;
        }
        
        /* 表格樣式 */
        .daily-table { 
            width: 100%; 
            border-collapse: collapse; 
            min-width: 1000px; 
            font-size: 14px;
            table-layout: auto;
        }
        
        .daily-table th, .daily-table td { 
            border: 1px solid #dee2e6; 
            padding: 10px 6px; 
            text-align: center; 
            vertical-align: middle;
        }
        
        /* 表頭樣式 */
        .daily-table thead th { 
            background: #f8f9fa; 
            font-weight: 700; 
            color: #495057;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        /* 店櫃資訊欄位 */
        .daily-table tbody td:first-child {
            background: #f8f9fa;
            font-weight: 600;
            text-align: left;
            padding-left: 12px;
            position: sticky;
            left: 0;
            z-index: 5;
            min-width: 150px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        
        /* 斑馬條紋 */
        .daily-table tbody tr:nth-child(even) { 
            background-color: #ffffff; 
        }
        
        .daily-table tbody tr:nth-child(odd) { 
            background-color: #f8fbfe; 
        }
        
        /* 店櫃資訊顯示 */
        .store-info {
            line-height: 1.4;
        }
        
        .store-code {
            font-weight: 700;
            font-size: 14px;
            color: #2d3748;
        }
        
        .store-name {
            color: #718096;
            font-size: 12px;
            margin-top: 2px;
        }
        
        /* 週末樣式 */
        .weekend { 
            background-color: rgba(255, 248, 225, 0.5) !important; 
            color: #d69e2e; 
        }
        
        /* 金額樣式 */
        .positive { color: #28a745; }
        .zero { color: #adb5bd; }
        
        /* 總計行 */
        .total-row { 
            background-color: #e8f5e9 !important; 
            font-weight: 700; 
            color: #155724;
        }
        
        .average-row { 
            background-color: #fff3e0 !important; 
            font-style: italic; 
            color: #856404;
        }
        
        /* 代班標記 */
        .substitute-mark {
            color: #999;
            font-size: 10px;
            margin-top: 2px;
            display: block;
        }
        
        /* 滾動提示 */
        .scroll-hint {
            text-align: center;
            padding: 10px;
            background: #fffaf0;
            color: #d69e2e;
            border-bottom: 1px solid #feebc8;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .scroll-hint::before {
            content: '↔️';
            font-size: 14px;
        }
        
        /* 手機響應式 */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .popup-container {
                max-width: 100vw;
                border-radius: 8px;
            }
            
            .popup-header {
                padding: 15px;
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }
            
            .popup-header h1 {
                font-size: 18px;
                text-align: center;
            }
            
            .month-navigation {
                justify-content: center;
            }
            
            .control-buttons {
                justify-content: center;
            }
            
            .table-container {
                max-height: 65vh;
            }
            
            .daily-table {
                font-size: 13px;
                min-width: 800px;
            }
            
            .daily-table th, 
            .daily-table td {
                padding: 8px 4px;
            }
            
            .daily-table tbody td:first-child {
                min-width: 130px;
                padding-left: 8px;
            }
            
            .store-code {
                font-size: 13px;
            }
            
            .store-name {
                font-size: 11px;
            }
            
            .scroll-hint {
                font-size: 12px;
                padding: 8px;
            }
        }
        
        /* 列印樣式 */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .popup-container {
                max-width: 100%;
                box-shadow: none;
                border-radius: 0;
            }
            
            .popup-header {
                background: #f8f9fa !important;
                color: #333 !important;
                border-bottom: 2px solid #007bff;
            }
            
            .month-btn, .close-btn {
                display: none;
            }
            
            .print-btn {
                display: none;
            }
            
            .scroll-hint {
                display: none;
            }
            
            .table-container {
                overflow: visible;
                max-height: none;
            }
            
            .daily-table {
                min-width: auto;
                width: 100%;
            }
            
            /* 確保表格在列印時完整 */
            .daily-table th, 
            .daily-table td {
                page-break-inside: avoid;
            }
        }
        
        /* 動畫效果 */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in {
            animation: fadeIn 0.3s ease forwards;
        }
    </style>
</head>
<body>
    <div class="popup-container fade-in">
        <div class="popup-header">
            <h1>📊 本月各店櫃每日業績</h1>
            
            <div class="month-navigation">
                <a href="?month=<?php echo $prev_month; ?>" class="month-btn">
                    <span>◀</span>
                    <span>上個月</span>
                </a>
                
                <div class="current-month">
                    <?php echo date('Y年m月', strtotime($month)); ?>
                </div>
                
                <a href="?month=<?php echo $next_month; ?>" class="month-btn">
                    <span>下個月</span>
                    <span>▶</span>
                </a>
            </div>
            
            <div class="control-buttons">
                <button onclick="window.print()" class="control-btn print-btn">
                    <span>🖨️</span>
                    <span>列印報表</span>
                </button>
                
                <button onclick="window.close()" class="control-btn close-btn">
                    <span>✕</span>
                    <span>關閉</span>
                </button>
            </div>
        </div>
        
        <?php if (empty($user_stores)): ?>
            <div style="padding: 40px; text-align: center; color: #6c757d;">
                <div style="font-size: 48px; margin-bottom: 20px;">📭</div>
                <h3 style="margin-bottom: 10px;">無負責店櫃</h3>
                <p>您目前沒有負責的店櫃，請聯繫管理員設定。</p>
            </div>
        <?php else: ?>
            <div class="scroll-hint print-hide">
                <span>可左右滑動查看完整表格</span>
            </div>
            
            <div class="table-container">
                <table class="daily-table">
                    <thead>
                        <tr>
                            <th rowspan="2" style="min-width: 150px;">店櫃</th>
                            <?php foreach ($dates as $date): 
                                $day_w = date('w', strtotime($date));
                                $is_we = ($day_w == 0 || $day_w == 6);
                            ?>
                            <th class="<?php echo $is_we ? 'weekend' : ''; ?>" style="min-width: 70px;">
                                <?php echo date('d', strtotime($date)); ?><br>
                                <small style="font-size: 11px; font-weight: normal;">
                                    <?php 
                                    $weekdays = ['日', '一', '二', '三', '四', '五', '六'];
                                    echo $weekdays[$day_w];
                                    ?>
                                </small>
                            </th>
                            <?php endforeach; ?>
                            <th rowspan="2" style="min-width: 90px;">總計</th>
                            <th rowspan="2" style="min-width: 90px;">平均</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($user_stores as $store): 
                            $sc = $store['code'];
                            $s_total = $store_totals[$sc] ?? 0;
                            $s_avg = $store_averages[$sc] ?? 0;
                        ?>
                        <tr>
                            <td style="text-align: left; padding-left: 12px;">
                                <div class="store-info">
                                    <div class="store-code"><?php echo htmlspecialchars($sc); ?></div>
                                    <div class="store-name"><?php echo htmlspecialchars($store['name']); ?></div>
                                </div>
                            </td>
                            <?php foreach ($dates as $date): 
                                $day_w = date('w', strtotime($date));
                                $is_we = ($day_w == 0 || $day_w == 6);
                                $val = isset($month_sales[$date][$sc]) ? ($month_sales[$date][$sc]['amount'] ?? 0