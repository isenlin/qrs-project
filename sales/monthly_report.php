<?php
/**
 * 月度業績報表 - 顯示指定月份各店櫃每日業績
 */

// 啟動 Session
session_start();

require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../config/auth_simple.php';

// 檢查登入
require_login();

$user = get_current_session_user();

// 檢查權限：業務、督導或管理員
if (!in_array($user['role'], ['sales', 'supervisor', 'admin'])) {
    header('Location: ../dashboard.php');
    exit;
}

// 取得角色名稱
$role_name = $GLOBALS['config']['roles'][$user['role']]['name'] ?? $user['role'];

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
if (in_array($user['role'], ['boss', 'admin'])) {
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

$responsible_store_codes = [];
foreach ($user_stores as $store) {
    $responsible_store_codes[] = $store['code'];
}

foreach ($month_sales as $date => $daily_sales) {
    foreach ($daily_sales as $store_code => $store_sales) {
        if (in_array($store_code, $responsible_store_codes)) {
            $amount = $store_sales['amount'] ?? 0;
            $month_total += $amount;
            $store_totals[$store_code] = ($store_totals[$store_code] ?? 0) + $amount;
            $entry_counts[$store_code] = ($entry_counts[$store_code] ?? 0) + 1;
        }
    }
}

$store_averages = [];
foreach ($store_totals as $store_code => $total) {
    $count = $entry_counts[$store_code] ?? 0;
    $store_averages[$store_code] = $count > 0 ? round($total / $count, 2) : 0;
}

$month_timestamp = strtotime($month . '-01');
$month_days = date('t', $month_timestamp);
$dates = [];
for ($day = 1; $day <= $month_days; $day++) {
    $date = sprintf('%s-%02d', $month, $day);
    $dates[] = $date;
}
?>
<!DOCTYPE html>
<html lang='zh-TW'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title><?php echo $month; ?> 月份業績報表</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* 基礎配置 */
        .report-container { max-width: 1400px; margin: 0 auto; padding: 20px; font-family: "Microsoft JhengHei", sans-serif; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 3px solid #007bff; padding-bottom: 15px; }
        .month-navigation { display: flex; gap: 10px; align-items: center; }
        .month-btn { background: #6c757d; color: white; padding: 8px 15px; border-radius: 5px; text-decoration: none; font-size: 14px; }
        .current-month { font-size: 22px; font-weight: bold; }

        /* 統計卡片 */
        .summary-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 30px; }
        .summary-card { background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-align: center; border-top: 4px solid #007bff; }
        .summary-value { font-size: 24px; font-weight: bold; color: #007bff; margin-top: 5px; }

        /* 表格與橫向斑馬紋關鍵設定 */
        .table-container { 
            overflow-x: auto; 
            overflow-y: visible; /* 重要：讓 sticky 生效 */
            background: #fff; 
            border-radius: 8px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.05); 
            position: relative; /* 幫助 sticky 定位 */
        }
        .monthly-table { 
            width: 100%; 
            border-collapse: collapse; 
            min-width: 1000px; 
            font-size: 15px; 
            table-layout: auto; 
            position: relative; /* 幫助 sticky 定位 */
        }
        .monthly-table th, .monthly-table td { border: 1px solid #dee2e6; padding: 10px 4px; text-align: center; }
        
        /* 固定表頭和左欄 - 桌面版 */
        .monthly-table thead { 
            position: -webkit-sticky; /* Safari */
            position: sticky; 
            top: 0; 
            z-index: 20; 
            transform: translateZ(0); /* 創建新的層級上下文 */
        }
        
        .monthly-table thead th { 
            background: #f8f9fa; 
            font-weight: bold; 
            position: -webkit-sticky; /* Safari */
            position: sticky;
            top: 0;
            transform: translateZ(0); /* 創建新的層級上下文 */
        }
        
        /* 固定左欄（店櫃資訊） */
        .monthly-table tbody td:first-child {
            position: -webkit-sticky; /* Safari */
            position: sticky;
            left: 0;
            background: #f8f9fa;
            z-index: 15;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            transform: translateZ(0); /* 創建新的層級上下文 */
            min-width: 150px;
        }
        
        /* 確保表頭左欄也固定 */
        .monthly-table thead th:first-child {
            position: -webkit-sticky; /* Safari */
            position: sticky;
            left: 0;
            z-index: 25;
            background: #f8f9fa;
            transform: translateZ(0); /* 創建新的層級上下文 */
        }

        /* --- 橫向斑馬條紋 --- */
        .monthly-table tbody tr:nth-child(even) { background-color: #ffffff !important; } /* 雙數行：白色 */
        .monthly-table tbody tr:nth-child(odd) { background-color: #f4f8ff !important; }  /* 單數行：極淺藍 */
        
        /* 店櫃標題列特別高亮 (橫向) */
        .store-row { transition: background 0.2s; }
        .store-row:hover { background-color: #fff9db !important; } /* 懸停變淡黃色方便對其 */

        /* 欄位顏色 */
        .weekend { background-color: rgba(255, 255, 0, 0.03) !important; color: #d9534f; }
        .positive { color: #28a745; font-weight: bold; }
        .zero { color: #adb5bd; }
        .total-row { background-color: #e8f5e9 !important; font-weight: bold; }
        .average-row { background-color: #fff3e0 !important; font-style: italic; }

        /* 按鈕 */
        .print-btn { background: #17a2b8; color: white; padding: 10px 20px; border-radius: 5px; border: none; cursor: pointer; font-size: 16px; font-weight: bold; }
        .print-btn:hover { background: #138496; }

        /* ==================== 手機響應式設計 ==================== */
        @media (max-width: 768px) {
            .report-container { padding: 10px; }
            .header { flex-direction: column; align-items: flex-start; gap: 15px; }
            .month-navigation { width: 100%; justify-content: space-between; }
            .summary-stats { grid-template-columns: 1fr; gap: 10px; }
            
            /* 手機表格優化 */
            .table-container { 
                position: relative;
                overflow-x: auto;
                overflow-y: visible; /* 重要：讓 sticky 生效 */
                -webkit-overflow-scrolling: touch; /* iOS 平滑滾動 */
            }
            
            .monthly-table {
                position: relative; /* 幫助 sticky 定位 */
            }
            
            /* 手機固定表頭和左欄 */
            .monthly-table thead { 
                position: -webkit-sticky; /* Safari */
                position: sticky;
                top: 0;
                z-index: 30;
                transform: translateZ(0); /* 創建新的層級上下文 */
            }
            
            .monthly-table thead th { 
                position: -webkit-sticky; /* Safari */
                position: sticky;
                top: 0;
                background: #f8f9fa;
                min-width: 60px;
                font-size: 13px;
                padding: 8px 3px;
                transform: translateZ(0); /* 創建新的層級上下文 */
            }
            
            .monthly-table tbody td:first-child {
                position: -webkit-sticky; /* Safari */
                position: sticky;
                left: 0;
                background: #f8f9fa;
                z-index: 25;
                box-shadow: 2px 0 5px rgba(0,0,0,0.1);
                font-size: 13px;
                padding: 8px 3px;
                min-width: 120px;
            }
            
            .monthly-table thead th:first-child {
                position: -webkit-sticky; /* Safari */
                position: sticky;
                left: 0;
                z-index: 35;
                background: #f8f9fa;
                transform: translateZ(0); /* 創建新的層級上下文 */
                min-width: 120px;
            }
            
            /* 手機表格字體調整 */
            .monthly-table {
                font-size: 13px;
                min-width: 800px; /* 保持足夠寬度以容納所有日期 */
            }
            
            /* 手機按鈕調整 */
            .print-btn, .month-btn {
                padding: 8px 12px;
                font-size: 14px;
            }
            
            /* 手機日期顯示優化 */
            .monthly-table th small {
                display: block;
                font-size: 10px;
            }
            
            /* 手機業績金額顯示 */
            .monthly-table td div {
                font-size: 12px;
                line-height: 1.2;
            }
            
            /* 手機代班標記 */
            .monthly-table td div span[style*="color: #999"] {
                font-size: 9px;
            }
        }
        
        /* 超小螢幕手機 */
        @media (max-width: 480px) {
            .monthly-table {
                font-size: 12px;
                min-width: 700px;
            }
            
            .monthly-table th, .monthly-table td {
                padding: 6px 2px;
            }
            
            .monthly-table tbody td:nth-child(2) {
                left: 50px;
                min-width: 90px;
            }
            
            .monthly-table thead th:nth-child(2) {
                left: 50px;
                min-width: 90px;
            }
        }

        /* ==================== A4 列印優化 (橫向) ==================== */
        @media print {
            @page { size: A4 landscape; margin: 0.5cm; }
            body { background: white; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .header, .summary-stats, .month-navigation, .back-btn, .print-hide, .no-data { display: none !important; }
            .report-container { width: 100%; max-width: none; padding: 0; }
            .table-container { overflow: visible !important; }
            .monthly-table { font-size: 8pt !important; width: 100% !important; border: 1px solid #000 !important; }
            .monthly-table th, .monthly-table td { padding: 2px !important; border: 1px solid #333 !important; }
            
            /* 列印時強制保留橫向斑馬紋 */
            .monthly-table tbody tr:nth-child(odd) { background-color: #f0f0f0 !important; }
            .print-only { display: block !important; text-align: center; margin-bottom: 10px; }
        }
        .print-only { display: none; }
        
        /* 滾動提示動畫 */
        .scroll-hint {
            animation: fadeInOut 3s ease-in-out;
        }
        
        @keyframes fadeInOut {
            0% { opacity: 0; }
            20% { opacity: 1; }
            80% { opacity: 1; }
            100% { opacity: 0; }
        }
        
        /* ========== 電腦與手機通用表格固定 ========== */
        /* 這些樣式會被 JavaScript 動態應用 */
        .monthly-table.js-fixed thead {
            /* 隱藏原始表頭的 sticky，使用克隆版本 */
            position: relative !important;
        }

        .monthly-table.js-fixed tbody td:first-child {
            /* 隱藏原始左欄的 sticky，使用克隆版本 */
            position: relative !important;
            box-shadow: none !important;
        }

        /* 固定表頭和左欄的克隆元素樣式 */
        .fixed-header {
            position: fixed;
            background: #f8fafc;
            z-index: 1000;
            overflow: hidden;
            pointer-events: none;
            display: none;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        
        .fixed-header table {
            margin: 0;
            border-collapse: collapse;
            table-layout: fixed;
        }
        
        .fixed-header th {
            background: #f8fafc !important;
            border: 1px solid #dee2e6;
            padding: 10px 4px;
            text-align: center;
            font-weight: bold;
            box-sizing: border-box;
        }
        
        .fixed-left-column {
            position: fixed;
            background: #f8fafc;
            z-index: 999;
            overflow: hidden;
            pointer-events: none;
            display: none;
            box-shadow: 2px 0 8px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        
        .fixed-left-column table {
            margin: 0;
            border-collapse: collapse;
            table-layout: fixed;
        }
        
        .fixed-left-column td {
            background: #f8fafc !important;
            border: 1px solid #dee2e6;
            padding: 10px 4px;
            text-align: left;
            box-sizing: border-box;
        }

        /* 手機專用優化 */
        @media (max-width: 768px) {
            .monthly-table.js-fixed {
                font-size: 14px !important;
            }
            
            .monthly-table.js-fixed th,
            .monthly-table.js-fixed td {
                padding: 10px 6px !important;
            }
            
            .fixed-header th,
            .fixed-left-column td {
                padding: 10px 6px !important;
                font-size: 14px !important;
            }
        }
    </style>
    
    <script>
        // 檢測是否為手機設備並顯示滾動提示
        document.addEventListener('DOMContentLoaded', function() {
            const scrollHint = document.querySelector('.scroll-hint');
            const tableContainer = document.querySelector('.table-container');
            const table = document.querySelector('.monthly-table');
            const thead = table ? table.querySelector('thead') : null;
            
            // 檢測是否為手機設備
            function isMobileDevice() {
                return window.innerWidth <= 768;
            }
            
            // 檢測表格是否需要橫向滾動
            function needsHorizontalScroll() {
                if (!tableContainer) return false;
                return tableContainer.scrollWidth > tableContainer.clientWidth;
            }
            
            // 顯示滾動提示
            if (isMobileDevice() && needsHorizontalScroll() && scrollHint) {
                scrollHint.style.display = 'block';
                
                // 5秒後淡出
                setTimeout(() => {
                    scrollHint.style.opacity = '0';
                    scrollHint.style.transition = 'opacity 1s';
                    setTimeout(() => {
                        scrollHint.style.display = 'none';
                    }, 1000);
                }, 5000);
            }
            
            // 監聽視窗大小變化
            window.addEventListener('resize', function() {
                if (isMobileDevice() && needsHorizontalScroll() && scrollHint) {
                    scrollHint.style.display = 'block';
                    scrollHint.style.opacity = '1';
                } else if (scrollHint) {
                    scrollHint.style.display = 'none';
                }
            });
            
            // 添加觸摸滾動指示
            let touchStartX = 0;
            let touchStartY = 0;
            
            tableContainer.addEventListener('touchstart', function(e) {
                touchStartX = e.touches[0].clientX;
                touchStartY = e.touches[0].clientY;
            }, { passive: true });
            
            tableContainer.addEventListener('touchmove', function(e) {
                if (!isMobileDevice()) return;
                
                const touchX = e.touches[0].clientX;
                const touchY = e.touches[0].clientY;
                
                // 檢測是否為橫向滾動
                const deltaX = Math.abs(touchX - touchStartX);
                const deltaY = Math.abs(touchY - touchStartY);
                
                if (deltaX > deltaY && deltaX > 10) {
                    // 橫向滾動時，確保固定欄位可見
                    const fixedCells = document.querySelectorAll('.monthly-table td:first-child, .monthly-table td:nth-child(2)');
                    fixedCells.forEach(cell => {
                        cell.style.backgroundColor = '#f0f8ff'; // 輕微高亮
                    });
                    
                    setTimeout(() => {
                        fixedCells.forEach(cell => {
                            cell.style.backgroundColor = '#f8f9fa';
                        });
                    }, 300);
                }
            }, { passive: true });
            
            // ==================== 備用固定方案 ====================
            // 如果 CSS sticky 無效，使用 JavaScript 固定
            
            function checkStickySupport() {
                // 檢查瀏覽器是否支援 sticky
                const testEl = document.createElement('div');
                testEl.style.position = 'sticky';
                testEl.style.position = '-webkit-sticky';
                return testEl.style.position.indexOf('sticky') !== -1;
            }
            
            function applyJavaScriptFix() {
                if (!tableContainer || !thead) return;
                
                console.log('應用 JavaScript 固定方案');
                
                // 複製表頭作為固定層
                const fixedHeader = thead.cloneNode(true);
                fixedHeader.className = 'fixed-header-js';
                fixedHeader.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    background: #f8f9fa;
                    z-index: 1000;
                    display: none;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                `;
                
                // 複製左欄作為固定層
                const firstColCells = table.querySelectorAll('tbody td:first-child');
                const secondColCells = table.querySelectorAll('tbody td:nth-child(2)');
                
                // 監聽滾動
                let isScrolling = false;
                
                tableContainer.addEventListener('scroll', function() {
                    if (!isScrolling) {
                        isScrolling = true;
                        
                        requestAnimationFrame(() => {
                            const scrollLeft = this.scrollLeft;
                            const scrollTop = this.scrollTop;
                            
                            // 固定表頭
                            if (scrollTop > 0) {
                                fixedHeader.style.display = 'table';
                                fixedHeader.style.transform = `translateY(${scrollTop}px)`;
                                
                                // 同步表頭水平滾動
                                const headerCells = fixedHeader.querySelectorAll('th');
                                const originalCells = thead.querySelectorAll('th');
                                
                                headerCells.forEach((cell, index) => {
                                    if (index >= 2) { // 跳過前兩欄（已固定）
                                        cell.style.transform = `translateX(-${scrollLeft}px)`;
                                    }
                                });
                            } else {
                                fixedHeader.style.display = 'none';
                            }
                            
                            // 固定左欄
                            firstColCells.forEach(cell => {
                                cell.style.transform = `translateX(${scrollLeft}px)`;
                            });
                            
                            secondColCells.forEach(cell => {
                                cell.style.transform = `translateX(${scrollLeft}px)`;
                            });
                            
                            isScrolling = false;
                        });
                    }
                });
                
                // 插入固定表頭
                tableContainer.parentNode.insertBefore(fixedHeader, tableContainer);
                
                // 初始檢查
                setTimeout(() => {
                    const scrollTop = tableContainer.scrollTop;
                    if (scrollTop > 0) {
                        fixedHeader.style.display = 'table';
                    }
                }, 100);
            }
            
            // 檢查並應用備用方案
            setTimeout(() => {
                // 檢查 sticky 是否有效
                if (thead) {
                    const rect = thead.getBoundingClientRect();
                    const isStickyWorking = rect.top === 0;
                    
                    if (!isStickyWorking && isMobileDevice()) {
                        console.log('CSS sticky 無效，啟用 JavaScript 備用方案');
                        applyJavaScriptFix();
                    }
                }
            }, 500);
        });
    </script>
</head>
<body>
    <div class="report-container">
        <div class="header print-hide">
            <div>
                <h1>月度業績報表</h1>
                <p>角色: <?php echo $role_name; ?> | 查詢範圍: <?php echo $month; ?></p>
            </div>
            <div class="month-navigation">
                <a href="?month=<?php echo $prev_month; ?>" class="month-btn">← 上個月</a>
                <span class="current-month"><?php echo $month; ?></span>
                <a href="?month=<?php echo $next_month; ?>" class="month-btn">下個月 →</a>
            </div>
        </div>

        <div class="print-only">
            <h2>店櫃業績月報表 (<?php echo $month; ?>)</h2>
            <p>列印時間: <?php echo date('Y-m-d H:i'); ?></p>
        </div>

        <div class="summary-stats print-hide">
            <div class="summary-card"><div>月份總業績</div><div class="summary-value"><?php echo number_format($month_total); ?></div></div>
            <div class="summary-card"><div>負責店櫃數</div><div class="summary-value"><?php echo count($user_stores); ?></div></div>
            <div class="summary-card"><div>當月天數</div><div class="summary-value"><?php echo $month_days; ?></div></div>
        </div>

        <?php if (empty($user_stores)): ?>
            <div class="no-data"><p>目前沒有可顯示的店櫃資料</p></div>
        <?php else: ?>
            <div class="table-container">
                <!-- 手機滾動提示 -->
                <div class="scroll-hint print-hide" style="display: none; text-align: center; padding: 10px; background: #f8f9fa; color: #6c757d; font-size: 14px; border-radius: 5px 5px 0 0;">
                    <span>📱 可左右滑動查看完整表格</span>
                    <div style="font-size: 12px; margin-top: 5px; opacity: 0.7;">店櫃和日期欄位會固定顯示</div>
                </div>
                
                <table class="monthly-table">
                    <thead>
                        <tr>
                            <th rowspan="2" style="min-width: 150px;">店櫃</th>
                            <?php foreach ($dates as $date): 
                                $day_w = date('w', strtotime($date));
                                $is_we = ($day_w == 0 || $day_w == 6);
                            ?>
                            <th class="<?php echo $is_we ? 'weekend' : ''; ?>">
                                <?php echo date('d', strtotime($date)); ?><br>
                                <small><?php echo ['日','一','二','三','四','五','六'][$day_w]; ?></small>
                            </th>
                            <?php endforeach; ?>
                            <th>總計</th>
                            <th>平均</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($user_stores as $store): 
                            $sc = $store['code'];
                            $s_total = $store_totals[$sc] ?? 0;
                            $s_avg = $store_averages[$sc] ?? 0;
                        ?>
                        <tr class="store-row">
                            <td style="text-align: left; padding-left: 10px;">
                                <div style="font-weight: bold; color: #333;"><?php echo htmlspecialchars($sc); ?></div>
                                <div style="font-size: 13px; color: #666; margin-top: 2px;"><?php echo htmlspecialchars($store['name']); ?></div>
                            </td>
                            <?php foreach ($dates as $date): 
                                $day_w = date('w', strtotime($date));
                                $is_we = ($day_w == 0 || $day_w == 6);
                                $val = isset($month_sales[$date][$sc]) ? ($month_sales[$date][$sc]['amount'] ?? 0) : null;
                                $role = isset($month_sales[$date][$sc]) ? ($month_sales[$date][$sc]['role'] ?? 'main') : 'main';
                            ?>
                        <td class="<?php echo $is_we ? 'weekend' : ''; ?> <?php echo $val === null ? 'zero' : ($val > 0 ? 'positive' : 'zero'); ?>">
                            <?php if ($val === null): ?>
                                -
                            <?php else: ?>
                                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; line-height: 1;">
                                    <span><?php echo number_format($val); ?></span>
                                    
                                    <?php if ($role === 'substitute'): ?>
                                        <span style="color: #999; font-size: 10px; transform: scale(1); margin-top: 2px;">
                                            代
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                            <?php endforeach; ?>
                            <td class="total-row"><?php echo number_format($s_total); ?></td>
                            <td class="average-row"><?php echo number_format($s_avg); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <tr class="total-row">
                            <td colspan="2">每日全店總計</td>
                            <?php foreach ($dates as $date): 
                                $d_total = 0;
                                foreach ($user_stores as $st) {
                                    $d_total += $month_sales[$date][$st['code']]['amount'] ?? 0;
                                }
                            ?>
                            <td><?php echo number_format($d_total); ?></td>
                            <?php endforeach; ?>
                            <td><?php echo number_format($month_total); ?></td>
                            <td>-</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div style="text-align: center; margin-top: 30px;" class="print-hide">
                <a href="../dashboard.php" class="back-btn" style="margin-right: 10px;">返回儀表板</a>
                <button onclick="window.print()" class="print-btn">📄 列印本月報表 (A4 橫向)</button>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // 電腦與手機通用表格固定方案 - 最終修正版
        document.addEventListener('DOMContentLoaded', function() {
            const tableContainer = document.querySelector('.table-container');
            const monthlyTable = document.querySelector('.monthly-table');
            
            if (!tableContainer || !monthlyTable) return;
            
            // 添加 JS 固定類別
            monthlyTable.classList.add('js-fixed');
            
            const thead = monthlyTable.querySelector('thead');
            const firstCells = monthlyTable.querySelectorAll('tbody td:first-child');
            
            if (!thead || firstCells.length === 0) return;
            
            // 獲取原始表格的寬度和高度
            const tableRect = monthlyTable.getBoundingClientRect();
            const firstCellRect = firstCells[0].getBoundingClientRect();
            
            // 創建固定表頭
            const fixedHeader = document.createElement('div');
            fixedHeader.className = 'fixed-header';
            fixedHeader.style.cssText = `
                position: fixed;
                top: ${tableRect.top}px;
                left: ${tableRect.left}px;
                width: ${tableRect.width}px;
                background: #f8fafc;
                z-index: 1000;
                overflow: hidden;
                pointer-events: none;
                display: none;
            `;
            
            // 創建固定左欄
            const fixedLeftColumn = document.createElement('div');
            fixedLeftColumn.className = 'fixed-left-column';
            fixedLeftColumn.style.cssText = `
                position: fixed;
                top: ${tableRect.top}px;
                left: ${tableRect.left}px;
                width: ${firstCellRect.width}px;
                background: #f8fafc;
                z-index: 999;
                overflow: hidden;
                pointer-events: none;
                display: none;
                box-shadow: 2px 0 8px rgba(0,0,0,0.1);
            `;
            
            // 複製表頭內容（只複製日期部分，排除第一欄）
            const headerClone = thead.cloneNode(true);
            // 移除第一欄（店櫃欄位）
            const firstHeaderCell = headerClone.querySelector('th:first-child');
            if (firstHeaderCell) {
                firstHeaderCell.remove();
            }
            
            // 設定表頭克隆的樣式
            const headerTable = document.createElement('table');
            headerTable.className = 'monthly-table';
            headerTable.style.cssText = `
                width: ${tableRect.width - firstCellRect.width}px;
                margin: 0;
                border-collapse: collapse;
                table-layout: fixed;
            `;
            headerTable.appendChild(headerClone);
            fixedHeader.appendChild(headerTable);
            
            // 複製左欄內容
            const leftColumnTable = document.createElement('table');
            leftColumnTable.className = 'monthly-table';
            leftColumnTable.style.cssText = `
                width: ${firstCellRect.width}px;
                margin: 0;
                border-collapse: collapse;
                table-layout: fixed;
            `;
            
            const tbody = document.createElement('tbody');
            firstCells.forEach(cell => {
                const row = document.createElement('tr');
                const cellClone = cell.cloneNode(true);
                cellClone.style.cssText = `
                    background: #f8fafc;
                    border: 1px solid #dee2e6;
                    padding: 10px 4px;
                    text-align: left;
                    width: ${firstCellRect.width}px;
                    box-sizing: border-box;
                `;
                row.appendChild(cellClone);
                tbody.appendChild(row);
            });
            leftColumnTable.appendChild(tbody);
            fixedLeftColumn.appendChild(leftColumnTable);
            
            // 添加到 body
            document.body.appendChild(fixedHeader);
            document.body.appendChild(fixedLeftColumn);
            
            // 計算固定元素應該顯示的位置
            let headerTop = tableRect.top;
            let leftColumnLeft = tableRect.left;
            
            // 監聽滾動事件
            let isScrolling = false;
            let lastScrollTop = 0;
            let lastScrollLeft = 0;
            
            function updateFixedElements() {
                if (!isScrolling) {
                    isScrolling = true;
                    
                    requestAnimationFrame(() => {
                        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                        const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
                        
                        // 獲取表格當前位置
                        const currentTableRect = monthlyTable.getBoundingClientRect();
                        const tableTop = currentTableRect.top + scrollTop;
                        const tableLeft = currentTableRect.left + scrollLeft;
                        const tableBottom = tableTop + currentTableRect.height;
                        const tableRight = tableLeft + currentTableRect.width;
                        
                        // 計算視窗範圍
                        const windowTop = scrollTop;
                        const windowBottom = scrollTop + window.innerHeight;
                        const windowLeft = scrollLeft;
                        const windowRight = scrollLeft + window.innerWidth;
                        
                        // 檢查表格是否在視窗內
                        const isTableInView = 
                            tableBottom > windowTop && 
                            tableTop < windowBottom &&
                            tableRight > windowLeft && 
                            tableLeft < windowRight;
                        
                        if (isTableInView) {
                            // 表格在視窗內，顯示固定元素
                            
                            // 更新固定表頭位置
                            fixedHeader.style.display = 'block';
                            fixedHeader.style.top = `${Math.max(windowTop, tableTop)}px`;
                            fixedHeader.style.left = `${tableLeft + firstCellRect.width}px`;
                            fixedHeader.style.width = `${currentTableRect.width - firstCellRect.width}px`;
                            
                            // 更新固定左欄位置
                            fixedLeftColumn.style.display = 'block';
                            fixedLeftColumn.style.top = `${Math.max(windowTop, tableTop)}px`;
                            fixedLeftColumn.style.left = `${tableLeft}px`;
                            fixedLeftColumn.style.width = `${firstCellRect.width}px`;
                            
                            // 更新表頭表格寬度
                            headerTable.style.width = `${currentTableRect.width - firstCellRect.width}px`;
                            
                        } else {
                            // 表格不在視窗內，隱藏固定元素
                            fixedHeader.style.display = 'none';
                            fixedLeftColumn.style.display = 'none';
                        }
                        
                        lastScrollTop = scrollTop;
                        lastScrollLeft = scrollLeft;
                        isScrolling = false;
                    });
                }
            }
            
            // 監聽多種滾動事件
            window.addEventListener('scroll', updateFixedElements);
            tableContainer.addEventListener('scroll', updateFixedElements);
            
            // 監聽視窗大小變化
            window.addEventListener('resize', function() {
                // 重新計算位置
                const tableRect = monthlyTable.getBoundingClientRect();
                const firstCellRect = firstCells[0].getBoundingClientRect();
                
                // 更新固定元素寬度
                fixedHeader.style.width = `${tableRect.width - firstCellRect.width}px`;
                fixedLeftColumn.style.width = `${firstCellRect.width}px`;
                headerTable.style.width = `${tableRect.width - firstCellRect.width}px`;
                
                // 重新計算位置
                updateFixedElements();
            });
            
            // 初始更新
            updateFixedElements();
            
            // 手機專用優化
            if (window.innerWidth <= 768) {
                monthlyTable.style.fontSize = '14px';
                leftColumnTable.style.fontSize = '14px';
                headerTable.style.fontSize = '14px';
                
                const cells = monthlyTable.querySelectorAll('th, td');
                cells.forEach(cell => {
                    cell.style.padding = '10px 6px';
                });
                
                const fixedCells = leftColumnTable.querySelectorAll('td');
                fixedCells.forEach(cell => {
                    cell.style.padding = '10px 6px';
                });
                
                const headerCells = headerTable.querySelectorAll('th');
                headerCells.forEach(cell => {
                    cell.style.padding = '10px 6px';
                });
            }
            
            console.log('✅ 電腦與手機通用表格固定已啟用（最終修正版）');
        });
    </script>
</body>
</html>