<?php
/**
 * 本月各店櫃每日業績 - 簡化彈出視窗版本（含匯出功能）
 * 只保留表格、月份選擇和匯出功能
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

// 準備匯出資料
$export_data = [
    'month' => $month,
    'stores' => $user_stores,
    'dates' => $dates,
    'month_sales' => $month_sales,
    'store_totals' => $store_totals,
    'store_averages' => $store_averages
];
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
            padding: 15px;
            color: #333;
        }
        
        /* 彈出視窗容器 */
        .popup-container {
            max-width: 95vw;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        /* 頁面標題 */
        .popup-header {
            background: #4a6fa5;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
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
        
        .pdf-btn {
            background: #dc3545;
            color: white;
        }
        
        .pdf-btn:hover {
            background: #c82333;
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
        
        /* 表格容器 */
        .table-wrapper { 
            overflow-x: auto; 
            background: #fff; 
            max-height: 75vh;
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
            padding-left: 10px;
            position: sticky;
            left: 0;
            z-index: 5;
            min-width: 140px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
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
            line-height: 1.3;
        }
        
        .store-code {
            font-weight: 700;
            font-size: 13px;
        }
        
        .store-name {
            color: #666;
            font-size: 11px;
            margin-top: 2px;
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
            color: #999;
            font-size: 10px;
            margin-top: 1px;
        }
        
        /* 滾動提示 */
        .scroll-hint {
            text-align: center;
            padding: 8px;
            background: #fff8e1;
            color: #d69e2e;
            border-bottom: 1px solid #ffeaa7;
            font-size: 12px;
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
                max-width: 100vw;
                border-radius: 8px;
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
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="popup-container">
        <div class="popup-header">
            <h1>📊 本月各店櫃每日業績</h1>
            
            <div class="month-nav">
                <a href="?month=<?php echo $prev_month; ?>" class="month-btn">◀ 上個月</a>
                <div class="current-month"><?php echo date('Y年m月', strtotime($month)); ?></div>
                <a href="?month=<?php echo $next_month; ?>" class="month-btn">下個月 ▶</a>
            </div>
            
            <div class="control-btns">
                <button onclick="exportToPDF()" class="ctrl-btn pdf-btn">
                    <span>📄</span>
                    <span>PDF</span>
                </button>
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
            <div class="scroll-hint">
                可左右滑動查看完整表格
            </div>
            
            <div class="table-wrapper">
                <table class="sales-table">
                    <thead>
                        <tr>
                            <th rowspan="2" style="min-width: 140px;">店櫃</th>
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
                                <div class="store-cell">
                                    <div class="store-code"><?php echo htmlspecialchars($sc); ?></div>
                                    <div class="store-name"><?php echo htmlspecialchars($store['name']); ?></div>
                                </div>
                            </td>
                            <?php foreach ($dates as $date): 
                                $day_w = date('w', strtotime($date));
                                $is_we = ($day_w == 0 || $day_w == 6);
                                $val = isset($month_sales[$date][$sc]) ? ($month_sales[$date][$sc]['amount'] ?? 0) : null;
                                $role = isset($month_sales[$date][$sc]) ? ($month_sales[$date][$sc]['role'] ?? 'main') : 'main';
                            ?>
                            <td class="<?php echo $is_we ? 'weekend' : ''; ?>">
                                <?php if ($val === null): ?>
                                    <span class="amount amount-zero">-</span>
                                <?php else: ?>
                                    <div>
                                        <span class="amount <?php echo $val === 0 ? 'amount-zero' : ''; ?>">
                                            <?php echo number_format($val); ?>
                                        </span>
                                        <?php if ($role === 'substitute' && $val > 0): ?>
                                            <div class="substitute">代</div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <?php endforeach; ?>
                            <td class="total-row"><?php echo number_format($s_total); ?></td>
                            <td class="average-row"><?php echo number_format($s_avg); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // 自動調整表格高度
        function adjustTableHeight() {
            const headerHeight = document.querySelector('.popup-header').offsetHeight;
            const windowHeight = window.innerHeight;
            const tableWrapper = document.querySelector('.table-wrapper');
            
            if (tableWrapper) {
                const availableHeight = windowHeight - headerHeight - 40;
                tableWrapper.style.maxHeight = Math.max(300, availableHeight) + 'px';
            }
        }
        
        // 初始調整
        adjustTableHeight();
        
        // 監聽視窗大小變化
        window.addEventListener('resize', adjustTableHeight);
        
        // 匯出資料（PHP 傳遞過來的）
        const exportData = <?php echo json_encode($export_data, JSON_UNESCAPED_UNICODE); ?>;
        
        // 匯出為 PDF（使用瀏覽器列印功能）
        function exportToPDF() {
            const button = event.target.closest('button');
            const originalHTML = button.innerHTML;
            
            // 顯示載入中
            button.innerHTML = '<span class="loading"></span> 處理中...';
            button.disabled = true;
            
            // 使用瀏覽器列印功能，但優化樣式
