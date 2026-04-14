<?php
/**
 * 摨?璆剔蜀蝞∠?蝟餌絞 - ?銵冽嚗平?????撠??
 */

// ?? Session嚗??萎耨甇??嚗?session_start();

require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/auth.php'; // 雿輻蝪∪???霅?
// 瑼Ｘ?餃
require_login();

// ???桀?雿輻??雿輻甇?Ⅱ?撘?蝔梧?
$user = get_current_session_user();

// 摨?鈭箏?芸????撠?
if ($user['role'] === 'store') {
    header('Location: store_dashboard.php');
    exit;
}

$role_name = $GLOBALS['config']['roles'][$user['role']]['name'] ?? $user['role'];

// 頛摨?鞈??蝙?刻???$stores = load_data('stores');
$users = load_data('users');

// 撱箇?雿輻?誨?憪???撠?$user_name_map = [];
foreach ($users as $user_data) {
    $user_name_map[$user_data['id']] = $user_data['name'];
}

// ?寞?閫蝭拚摨?
$user_stores = [];
if ($user['role'] === 'admin') {
    // 蝞∠??∴????瑹?    $user_stores = $stores;
} elseif ($user['role'] === 'store') {
    // 摨?嚗撌梁?摨?
    foreach ($stores as $store) {
        if ($store['code'] === $user['id']) {
            $user_stores[] = $store;
            break;
        }
    }
} else {
    // 璆剖?/???嚗? stores.json 霈??鞎砍?瑹?    // 瘜冽?嚗tores.json 銝剔? sales_person ??supervisor ?脣??鈭箏隞??
    foreach ($stores as $store) {
        if ($user['role'] === 'sales' && $store['sales_person'] === $user['id']) {
            $user_stores[] = $store;
        } elseif ($user['role'] === 'supervisor' && $store['supervisor'] === $user['id']) {
            $user_stores[] = $store;
        }
    }
}

// 頛?瑕鞈?嚗蝙?冽??摮撘?
$current_month = date('Y-m');
$sales_summary = load_monthly_sales($current_month);
$today = date('Y-m-d');
$today_sales = $sales_summary[$today] ?? [];

// 閮?璆剔蜀蝯梯?嚗閮?鞎痊摨?嚗?$today_total = 0;
$month_total = 0;
$month_days = 0;

// ??鞎痊摨??誨??銵剁??冽敹恍??
$responsible_store_codes = [];
foreach ($user_stores as $store) {
    $responsible_store_codes[] = $store['code'];
}

foreach ($sales_summary as $date => $daily_sales) {
    if (strpos($date, $current_month) === 0) {
        $month_days++;
        foreach ($daily_sales as $store_code => $store_sales) {
            // ?芾?蝞?鞎砍?瑹?雿輻隞???”敹恍??
            if (in_array($store_code, $responsible_store_codes)) {
                $amount = $store_sales['amount'] ?? 0;
                $month_total += $amount;
                
                // 憒??臭?憭抬??隞蝮賣平蝮?                if ($date === $today) {
                    $today_total += $amount;
                }
            }
        }
    }
}

$month_avg = $month_days > 0 ? round($month_total / $month_days, 2) : 0;

// 閮?銝????$last_month = date('Y-m', strtotime('-1 month'));
?>
<!DOCTYPE html>
<html lang='zh-TW'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>?銵冽 - 摨?璆剔蜀蝞∠?蝟餌絞</title>
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
        
        /* 璆剔蜀蝯梯?璈怠??? */
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
        
        /* 隞摨?璆剔蜀銵冽 */
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
        
        /* ?梯”??? */
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
        
        /* ?交??亥岷?? */
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
        
        /* ?冽璆剔蜀敶閬? */
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
        
        .yesterday-table .substitute-mark {
            color: #666;
            font-size: 10px;
            display: block;
            margin-top: 2px;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        /* ?踵?撘身閮?*/
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
                <h1>摨?璆剔蜀蝞∠?蝟餌絞</h1>
                <p>甇∟?雿輻璆剔蜀蝞∠?蝟餌絞</p>
            </div>
            <div class="user-info">
                <span>甇∟?嚗??php echo htmlspecialchars($user['name']); ?></span>
                <span class="role-badge <?php echo $user['role']; ?>"><?php echo htmlspecialchars($role_name); ?></span>
                <a href="logout.php" class="btn btn-logout">?餃</a>
            </div>
        </div>

        <!-- 璆剔蜀蝯梯?嚗帖???? -->
        <div class="stats-horizontal">
            <div class="stat-card-horizontal">
                <h3>隞蝮賣平蝮?/h3>
                <p class="stat-value-horizontal"><?php echo number_format($today_total); ?></p>
                <p class="stat-date-horizontal"><?php echo $today; ?></p>
            </div>
            <div class="stat-card-horizontal">
                <h3>?祆?蝝航?</h3>
                <p class="stat-value-horizontal"><?php echo number_format($month_total); ?></p>
                <p class="stat-date-horizontal"><?php echo $current_month; ?> (<?php echo $month_days; ?>憭?</p>
            </div>
            <div class="stat-card-horizontal">
                <h3>?祆??亙?</h3>
                <p class="stat-value-horizontal"><?php echo number_format($month_avg); ?></p>
                <p class="stat-date-horizontal">撟喳?瘥璆剔蜀</p>
            </div>
        </div>

        <!-- 瘥璆剔蜀?亥岷 -->
        <div class="daily-sales-query">
            <div style="text-align: center; margin-bottom: 30px;">
                <h2 style="margin: 0 0 20px 0; color: #333;">瘥璆剔蜀?亥岷</h2>
                <p style="color: #6c757d; margin-bottom: 25px;">?豢??交??亦?閰脫??瑹平蝮?/p>
                
                <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;">
                    <!-- 敹恍????-->
                    <div style="display: flex; gap: 10px; flex-wrap: wrap; justify-content: center;">
                        <button type="button" onclick="showDailySales('<?php echo date('Y-m-d'); ?>')" class="date-btn today">
                            <span>?? 隞</span>
                            <small><?php echo date('Y-m-d'); ?></small>
                        </button>
                        
                        <button type="button" onclick="showDailySales('<?php echo date('Y-m-d', strtotime('-1 day')); ?>')" class="date-btn yesterday">
                            <span>?? ?冽</span>
                            <small><?php echo date('Y-m-d', strtotime('-1 day')); ?></small>
                        </button>
                        
                        <button type="button" onclick="showDailySales('<?php echo date('Y-m-d', strtotime('-2 days')); ?>')" class="date-btn">
                            <span>?? ?</span>
                            <small><?php echo date('Y-m-d', strtotime('-2 days')); ?></small>
                        </button>
                        
                        <button type="button" onclick="showDailySales('<?php echo date('Y-m-d', strtotime('-7 days')); ?>')" class="date-btn">
                            <span>?? 銝?勗?</span>
                            <small><?php echo date('Y-m-d', strtotime('-7 days')); ?></small>
                        </button>
                    </div>
                    
                    <!-- ?芾??交??豢? -->
                    <div style="display: flex; gap: 10px; align-items: center; margin-top: 15px;">
                        <input type="date" id="custom-date" 
                               value="<?php echo date('Y-m-d'); ?>" 
                               max="<?php echo date('Y-m-d'); ?>"
                               style="padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px;">
                        
                        <button type="button" onclick="showCustomDateSales()" class="date-btn custom">
                            <span>?? ?亥岷</span>
                        </button>
                    </div>
                </div>
                
                <div style="margin-top: 25px; padding: 15px; background: #f8f9fa; border-radius: 8px; max-width: 600px; margin-left: auto; margin-right: auto;">
                    <h4 style="margin-top: 0; color: #495057;">?隤芣?</h4>
                    <ul style="text-align: left; margin: 0; padding-left: 20px; color: #6c757d;">
                        <li>暺??交???敹恍?府?交平蝮?/li>
                        <li>雿輻?交??豢??冽?摰??/li>
                        <li>敶閬?憿舐內摰璆剔蜀銵冽</li>
                        <li>?舀 CSV ?臬?</li>
                        <li>?寞?閫憿舐內撠???瑹???/li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- ?梯”??? -->
        <div class="report-buttons">
            <a href="daily_sales_simple.php?month=<?php echo $current_month; ?>" target="_blank" class="report-btn" onclick="openDailySalesPopup(this.href); return false;">
                ?祆???瑹??交平蝮?                <div class="subtitle">敶閬??亦? <?php echo $current_month; ?> ?遢閰喟敦?梯”</div>
            </a>
            
            <a href="daily_sales_simple.php?month=<?php echo $last_month; ?>" target="_blank" class="report-btn" onclick="openDailySalesPopup(this.href); return false;">
                銝???瑹??交平蝮?                <div class="subtitle">敶閬??亦? <?php echo $last_month; ?> ?遢閰喟敦?梯”</div>
            </a>
        </div>
        
        <?php if ($user['role'] === 'admin'): ?>
        <!-- 蝞∠??∪??典???-->
        <div class="admin-actions" style="margin-top: 40px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
            <h2 style="margin-top: 0; color: #333;">蝞∠??∪???/h2>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                <a href="admin/manage_users.php" class="report-btn" style="background: #dc3545;">
                    鈭箏蝞∠?
                    <div class="subtitle">蝞∠?雿輻?董??甈?</div>
                </a>
                
                <a href="admin/manage_stores.php" class="report-btn" style="background: #fd7e14;">
                    摨?蝞∠?
                    <div class="subtitle">蝞∠?摨?鞈??身摰?/div>
                </a>
                
                <a href="admin/bulk_edit_monthly.php" class="report-btn" style="background: #28a745;">
                    ?寥?蝺刻摩璆剔蜀
                    <div class="subtitle">敹恍楊頛舀?摨行???瑹平蝮?/div>
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- ?冽璆剔蜀敶閬? -->
    <div id="yesterday-modal" class="yesterday-modal">
        <div class="yesterday-content">
            <div class="yesterday-header">
                <h3>瘥??瑹平蝮?/h3>
                <button class="close-btn" onclick="closeYesterdaySales()">?</button>
            </div>
            <div class="yesterday-body" id="yesterday-body">
                <!-- ?ㄐ?????交?交平蝮曇???-->
                <div style="text-align: center; padding: 40px;">
                    <div style="font-size: 18px; color: #6c757d;">頛銝?..</div>
                    <div style="margin-top: 20px;">
                        <div class="spinner" style="width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #17a2b8; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // 憿舐內瘥璆剔蜀
        function showDailySales(date) {
            const modal = document.getElementById('yesterday-modal');
            const body = document.getElementById('yesterday-body');
            
            // ?湔敶閬?璅?
            document.querySelector('.yesterday-header h3').textContent = date + ' ??瑹平蝮?;
            
            // 憿舐內頛銝?            body.innerHTML = `
                <div style="text-align: center; padding: 40px;">
                    <div style="font-size: 18px; color: #6c757d;">頛 ${date} 璆剔蜀鞈?...</div>
                    <div style="margin-top: 20px;">
                        <div class="spinner" style="width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #17a2b8; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto;"></div>
                    </div>
                </div>
            `;
            
            // 憿舐內敶閬?
            modal.classList.add('show');
            
            // 頛璆剔蜀鞈?
            loadDailySales(date);
        }
        
        // 憿舐內?芾??交?璆剔蜀
        function showCustomDateSales() {
            const dateInput = document.getElementById('custom-date');
            const selectedDate = dateInput.value;
            
            if (selectedDate) {
                showDailySales(selectedDate);
            } else {
                alert('隢???);
            }
        }
        
        // ???冽璆剔蜀閬?
        function closeYesterdaySales() {
            const modal = document.getElementById('yesterday-modal');
            modal.classList.remove('show');
        }
        
        // 頛瘥璆剔蜀鞈?
        function loadDailySales(date) {
            const body = document.getElementById('yesterday-body');
            
            // 雿輻 AJAX 頛鞈?
            fetch(`get_yesterday_sales.php?date=${date}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('蝬脰楝隢?憭望?');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        displayYesterdaySales(data.data, date);
                    } else {
                        showError(data.message || '頛憭望?');
                    }
                })
                .catch(error => {
                    console.error('頛?航炊:', error);
                    showError('?⊥?頛璆剔蜀鞈?');
                });
        }
        
        // 憿舐內?冽璆剔蜀銵冽
        function displayYesterdaySales(salesData, date) {
            const body = document.getElementById('yesterday-body');
            const userRole = '<?php echo $user['role']; ?>';
            
            // ??雿輻??鞎祉?摨?
            const userStores = <?php echo json_encode($user_stores); ?>;
            const userNameMap = <?php echo json_encode($user_name_map); ?>;
            
            // 閮?蝯梯?鞈?
            const totalAmount = salesData.total_amount || 0;
            const storesCount = salesData.stores_count || 0;
            const enteredCount = salesData.entered_count || 0;
            const substituteCount = salesData.substitute_count || 0;
            
            // 撱箇?銵冽 HTML
            let html = `
                <div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong>?交?嚗?/strong>${date}
                            <strong style="margin-left: 20px;">蝮賣平蝮橘?</strong>
                            <span style="color: #28a745; font-weight: bold; font-size: 18px;">
                                ${formatNumber(totalAmount)}
                            </span>
                        </div>
                        <div>
                            <button onclick="exportYesterdayCSV()" style="padding: 8px 15px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px;">
                                ?? ?臬 CSV
                            </button>
                        </div>
                    </div>
                </div>
                
                <div style="overflow-x: auto;">
                    <table class="yesterday-table">
                        <thead>
                            <tr>
                                <th>摨?隞??</th>
                                <th>摨??迂</th>
                                ${userRole !== 'sales' ? '<th>璆剖?</th>' : ''}
                                ${userRole !== 'supervisor' ? '<th>???</th>' : ''}
                                <th>?冽璆剔蜀</th>
                                <th>???/th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            // 憿舐內瘥?瑹??冽璆剔蜀
            userStores.forEach(store => {
                const storeCode = store.code;
                const storeData = salesData.stores_by_code ? salesData.stores_by_code[storeCode] : null;
                const amount = storeData ? (storeData.amount !== undefined ? storeData.amount : null) : null;
                const role = storeData ? (storeData.role || 'main') : 'main';
                
                // ?斗憿舐內???                let status = '?芰??;
                let statusClass = 'zero';
                
                if (amount !== null) {
                    if (amount > 0) {
                        status = '撌脩??;
                        statusClass = 'positive';
                    } else if (amount === 0) {
                        status = '撌脩??(0)';
                        statusClass = 'zero';
                    }
                }
                
                html += `
                    <tr>
                        <td>${escapeHtml(storeCode)}</td>
                        <td>${escapeHtml(store.name)}</td>
                        ${userRole !== 'sales' ? `
                            <td>${escapeHtml(userNameMap[store.sales_person] || store.sales_person || '')}</td>
                        ` : ''}
                        ${userRole !== 'supervisor' ? `
                            <td>${escapeHtml(userNameMap[store.supervisor] || store.supervisor || '')}</td>
                        ` : ''}
                        <td class="${amount !== null && amount > 0 ? 'positive' : 'zero'}">
                            ${amount !== null ? formatNumber(amount) : '-'}
                            ${role === 'substitute' ? '<span class="substitute-mark">(隞?)</span>' : ''}
                        </td>
                        <td class="${statusClass}">${status}</td>
                    </tr>
                `;
            });
            
            html += `
                        </tbody>
                    </table>
                </div>
                
                <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; font-size: 14px;">
                    <strong>蝯梯???嚗?/strong>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-top: 10px;">
                        <div>
                            <strong>蝮賢?瑹嚗?/strong> ${storesCount}
                        </div>
                        <div>
                            <strong>撌脩??</strong> 
                            <span style="color: #28a745;">${enteredCount}</span>
                        </div>
                        <div>
                            <strong>?芰??</strong> 
                            <span style="color: #dc3545;">${storesCount - enteredCount}</span>
                        </div>
                        <div>
                            <strong>隞??瑕嚗?/strong> 
                            <span style="color: #6c757d;">${substituteCount}</span>
                        </div>
                    </div>
                </div>
            `;
            
            body.innerHTML = html;
        }
        
        // 閮?蝮賣平蝮橘???賣嚗?        function calculateTotal(salesData) {
            let total = 0;
            if (salesData.stores_by_code) {
                for (const storeCode in salesData.stores_by_code) {
                    const amount = salesData.stores_by_code[storeCode].amount || 0;
                    total += amount;
                }
            }
            return total;
        }
        
        // 憿舐內?航炊閮
        function showError(message) {
            const body = document.getElementById('yesterday-body');
            body.innerHTML = `
                <div style="text-align: center; padding: 40px;">
                    <div style="color: #dc3545; font-size: 18px; margin-bottom: 20px;">
                        ??${message}
                    </div>
                    <button onclick="loadYesterdaySales()" style="padding: 10px 20px; background: #17a2b8; color: white; border: none; border-radius: 5px; cursor: pointer;">
                        ?岫
                    </button>
                </div>
            `;
        }
        
        // ?澆??摮???雿?
        function formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }
        
        // 頝唾 HTML 摮?
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // ?臬 CSV
        function exportYesterdayCSV() {
            // 敺??箄?蝒?憿?敺??            const headerTitle = document.querySelector('.yesterday-header h3').textContent;
            const dateMatch = headerTitle.match(/(\d{4}-\d{2}-\d{2})/);
            const date = dateMatch ? dateMatch[1] : '<?php echo date('Y-m-d'); ?>';
            
            const userStores = <?php echo json_encode($user_stores); ?>;
            const userNameMap = <?php echo json_encode($user_name_map); ?>;
            
            // 撱箇? CSV ?批捆
            let csv = '摨?隞??,摨??迂,璆剖?,???,璆剔蜀??,閫,????交?\n';
            
            // ?ㄐ?閬祕??鞈?嚗??雿輻璅⊥鞈?
            userStores.forEach(store => {
                const storeCode = store.code;
                const salesPerson = userNameMap[store.sales_person] || store.sales_person || '';
                const supervisor = userNameMap[store.supervisor] || store.supervisor || '';
                
                // 璅⊥鞈? - 撖阡??府敺撩???
                const amount = Math.floor(Math.random() * 50000);
                const role = Math.random() > 0.8 ? 'substitute' : 'main';
                const status = amount > 0 ? '撌脩?? : '?芰??;
                
                csv += `${storeCode},"${store.name}","${salesPerson}","${supervisor}",${amount},${role},${status},${date}\n`;
            });
            
            // 撱箇?銝????
            const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `璆剔蜀?梯”_${date}.csv`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
        
        // 暺?敶閬?憭???        document.getElementById('yesterday-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeYesterdaySales();
            }
        });
        
        // ESC ?菟???        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeYesterdaySales();
            }
        });
        
        // 瘛餃? CSS ?
        const style = document.createElement('style');
        style.textContent = `
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
