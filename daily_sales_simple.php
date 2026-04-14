<?php
/**
 * 本月各店櫃每日業績 - 簡化彈出視窗版本
 * 只保留表格、月份選擇和列印功能
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
            
            .month-btn, .close-btn, .print-btn, .scroll-hint {
                display: none;
            }
            
            .table-wrapper {
                overflow: visible;
                max-height: none;
            }
            
            .sales-table {
                min-width: auto;
                width: 100%;
            }
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
                <button onclick="exportToPDF()" class="ctrl-btn pdf-btn">📄 PDF</button>
                <button onclick="exportToCSV()" class="ctrl-btn csv-btn">📊 CSV</button>
                <button onclick="window.close()" class="ctrl-btn close-btn">✕ 關閉</button>
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
                const availableHeight = windowHeight - headerHeight - 40; // 減去 padding 和 margin
                tableWrapper.style.maxHeight = Math.max(300, availableHeight) + 'px';
            }
        }
        
        // 初始調整
        adjustTableHeight();
        
        // 監聽視窗大小變化
        window.addEventListener('resize', adjustTableHeight);
        
        // 監聽表格滾動，更新固定效果
        const tableWrapper = document.querySelector('.table-wrapper');
        if (tableWrapper) {
            tableWrapper.addEventListener('scroll', function() {
                // 這裡可以添加滾動效果，但 CSS sticky 已經足夠
            });
        }
        
        console.log('彈出視窗已載入');
        
        // 匯出為 PDF
        function exportToPDF() {
            const month = document.querySelector('.current-month').textContent.trim();
            const table = document.querySelector('.sales-table');
            
            if (!table) {
                alert('找不到表格資料');
                return;
            }
            
            // 顯示處理中訊息
            const originalText = event.target.textContent;
            event.target.textContent = '處理中...';
            event.target.disabled = true;
            
            // 使用 html2canvas 和 jsPDF 生成 PDF
            if (typeof html2canvas === 'undefined' || typeof jsPDF === 'undefined') {
                // 載入需要的函式庫
                loadPDFLibraries().then(() => {
                    generatePDF(table, month, event.target, originalText);
                }).catch(error => {
                    alert('載入 PDF 函式庫失敗: ' + error.message);
                    event.target.textContent = originalText;
                    event.target.disabled = false;
                });
            } else {
                generatePDF(table, month, event.target, originalText);
            }
        }
        
        // 載入 PDF 相關函式庫
        function loadPDFLibraries() {
            return new Promise((resolve, reject) => {
                // 檢查是否已載入
                if (typeof html2canvas !== 'undefined' && typeof jsPDF !== 'undefined') {
                    resolve();
                    return;
                }
                
                let loadedCount = 0;
                const totalLibs = 2;
                
                // 載入 html2canvas
                const html2canvasScript = document.createElement('script');
                html2canvasScript.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
                html2canvasScript.onload = () => {
                    loadedCount++;
                    if (loadedCount === totalLibs) resolve();
                };
                html2canvasScript.onerror = reject;
                
                // 載入 jsPDF
                const jsPDFScript = document.createElement('script');
                jsPDFScript.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
                jsPDFScript.onload = () => {
                    loadedCount++;
                    if (loadedCount === totalLibs) resolve();
                };
                jsPDFScript.onerror = reject;
                
                document.head.appendChild(html2canvasScript);
                document.head.appendChild(jsPDFScript);
            });
        }
        
        // 生成 PDF
        function generatePDF(table, month, button, originalText) {
            // 複製表格並調整樣式以適合 PDF
            const tableClone = table.cloneNode(true);
            
            // 移除固定樣式
            tableClone.querySelectorAll('[style*="position: sticky"]').forEach(el => {
                el.style.position = 'static';
            });
            
            // 調整寬度
            tableClone.style.width = '100%';
            tableClone.style.fontSize = '10px';
            
            // 添加到臨時容器
            const tempContainer = document.createElement('div');
            tempContainer.style.position = 'absolute';
            tempContainer.style.left = '-9999px';
            tempContainer.style.top = '0';
            tempContainer.style.width = '800px';
            tempContainer.style.padding = '20px';
            tempContainer.style.backgroundColor = 'white';
            tempContainer.appendChild(tableClone);
            document.body.appendChild(tempContainer);
            
            // 使用 html2canvas 轉換為圖片
            html2canvas(tempContainer, {
                scale: 2,
                useCORS: true,
                logging: false,
                backgroundColor: '#ffffff'
            }).then(canvas => {
                // 移除臨時容器
                document.body.removeChild(tempContainer);
                
                // 創建 PDF
                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF('l', 'mm', 'a4'); // 橫向 A4
                
                // 添加標題
                pdf.setFontSize(16);
                pdf.text(`${month} 各店櫃每日業績報表`, 20, 20);
                
                // 添加日期
                pdf.setFontSize(10);
                pdf.text(`匯出時間: ${new Date().toLocaleString('zh-TW')}`, 20, 30);
                
                // 計算圖片尺寸
                const imgWidth = 280; // A4 橫向寬度減去邊距
                const imgHeight = (canvas.height * imgWidth) / canvas.width;
                
                // 添加圖片到 PDF
                const imgData = canvas.toDataURL('image/png');
                pdf.addImage(imgData, 'PNG', 10, 40, imgWidth, imgHeight);
                
                // 儲存 PDF
                pdf.save(`${month}_各店櫃每日業績報表.pdf`);
                
                // 恢復按鈕狀態
                button.textContent = originalText;
                button.disabled = false;
                
            }).catch(error => {
                console.error('PDF 生成失敗:', error);
                alert('PDF 生成失敗: ' + error.message);
                
                // 恢復按鈕狀態
                button.textContent = originalText;
                button.disabled = false;
                
                // 移除臨時容器
                if (tempContainer.parentNode) {
                    document.body.removeChild(tempContainer);
                }
            });
        }
        
        // 匯出為 CSV
        function exportToCSV() {
            const month = document.querySelector('.current-month').textContent.trim();
            const table = document.querySelector('.sales-table');
            
            if (!table) {
                alert('找不到表格資料');
                return;
            }
            
            // 顯示處理中訊息
            const originalText = event.target.textContent;
            event.target.textContent = '處理中...';
            event.target.disabled = true;
            
            // 收集表格資料
            const headers = [];
            const rows = [];
            
            // 取得表頭
            const headerCells = table.querySelectorAll('thead th');
            headerCells.forEach(cell => {
                // 移除換行和額外空格
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
                    // 移除換行和額外空格，處理代班標記
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
            link.download = `${month}_各店櫃每日業績報表.csv`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // 恢復按鈕狀態
            setTimeout(() => {
                event.target.textContent = originalText;
                event.target.disabled = false;
            }, 500);
        }
    </script>
</body>
</html>