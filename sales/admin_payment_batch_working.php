<?php
/**
 * 管理員收款審核系統 - 批量處理工作版
 * 功能：審核店櫃是否已寄回款項，確認收款狀態（支援批量處理）
 */

// 啟動 Session
session_start();

require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/auth_simple.php';

// 檢查登入
require_login();

$user = get_current_session_user();

// 檢查權限：只有管理員可以訪問
if ($user['role'] !== 'admin') {
    header('Location: ../dashboard.php');
    exit;
}

// 取得今天日期
$today = date('Y-m-d');

// 計算前五天日期（含今天）
$date_range = [];
for ($i = 0; $i < 5; $i++) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $date_range[] = $date;
}

// 載入店櫃資料
$stores = load_data('stores');

// 處理表單提交
$store_code = '';
$store_name = '';
$unpaid_sales = [];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 處理店櫃代號查詢
    if (isset($_POST['store_code'])) {
        $store_code = trim($_POST['store_code']);
        
        if (!empty($store_code)) {
            // 檢查店櫃是否存在
            $store_exists = false;
            foreach ($stores as $store) {
                if ($store['code'] === $store_code) {
                    $store_exists = true;
                    $store_name = $store['name'];
                    break;
                }
            }
            
            if ($store_exists) {
                // 查詢該店櫃前五天的未收款業績
                $unpaid_sales = [];
                
                foreach ($date_range as $date) {
                    // 載入該日期的銷售資料
                    $month = date('Y-m', strtotime($date));
                    $sales_data = load_monthly_sales($month);
                    
                    if (isset($sales_data[$date][$store_code])) {
                        $sale_data = $sales_data[$date][$store_code];
                        $amount = $sale_data['amount'] ?? 0;
                        
                        // 檢查收款狀態（預設為 'unpaid' 未收款）
                        $payment_status = $sale_data['payment_status'] ?? 'unpaid';
                        
                        if ($amount > 0 && $payment_status === 'unpaid') {
                            $unpaid_sales[] = [
                                'date' => $date,
                                'amount' => $amount,
                                'role' => $sale_data['role'] ?? 'main',
                                'payment_status' => $payment_status
                            ];
                        }
                    }
                }
                
                if (empty($unpaid_sales)) {
                    $message = "✅ 店櫃 {$store_code} ({$store_name}) 前五天無未收款業績";
                }
            } else {
                $message = "❌ 店櫃代號 {$store_code} 不存在";
            }
        }
    }
    
    // 處理批量儲存
    if (isset($_POST['save_payments'])) {
        $store_code = $_POST['store_code'] ?? '';
        
        if (!empty($store_code)) {
            $saved_count = 0;
            $error_count = 0;
            
            // 檢查是否有選取的收款項目
            if (isset($_POST['selected_payments']) && is_array($_POST['selected_payments'])) {
                foreach ($_POST['selected_payments'] as $payment_date) {
                    // 載入該月份的銷售資料
                    $month = date('Y-m', strtotime($payment_date));
                    $sales_data = load_monthly_sales($month);
                    
                    if (isset($sales_data[$payment_date][$store_code])) {
                        // 更新收款狀態
                        $sales_data[$payment_date][$store_code]['payment_status'] = 'paid';
                        $sales_data[$payment_date][$store_code]['payment_confirmed_by'] = $user['id'];
                        $sales_data[$payment_date][$store_code]['payment_confirmed_at'] = date('Y-m-d H:i:s');
                        
                        // 如果有修改金額，更新金額
                        $amount_key = 'amount_' . str_replace('-', '_', $payment_date);
                        if (isset($_POST[$amount_key]) && is_numeric($_POST[$amount_key])) {
                            $new_amount = (int)$_POST[$amount_key];
                            if ($new_amount >= 0) {
                                $sales_data[$payment_date][$store_code]['amount'] = $new_amount;
                                $sales_data[$payment_date][$store_code]['amount_modified_by'] = $user['id'];
                                $sales_data[$payment_date][$store_code]['amount_modified_at'] = date('Y-m-d H:i:s');
                            }
                        }
                        
                        // 儲存更新後的資料
                        if (save_monthly_sales($month, $sales_data)) {
                            $saved_count++;
                        } else {
                            $error_count++;
                        }
                    }
                }
                
                if ($saved_count > 0) {
                    $message = "✅ 已成功儲存 {$saved_count} 筆收款確認";
                    if ($error_count > 0) {
                        $message .= "，{$error_count} 筆失敗";
                    }
                    
                    // 重新查詢未收款業績
                    $unpaid_sales = [];
                    foreach ($date_range as $date) {
                        $month = date('Y-m', strtotime($date));
                        $sales_data = load_monthly_sales($month);
                        
                        if (isset($sales_data[$date][$store_code])) {
                            $sale_data = $sales_data[$date][$store_code];
                            $amount = $sale_data['amount'] ?? 0;
                            $payment_status = $sale_data['payment_status'] ?? 'unpaid';
                            
                            if ($amount > 0 && $payment_status === 'unpaid') {
                                $unpaid_sales[] = [
                                    'date' => $date,
                                    'amount' => $amount,
                                    'role' => $sale_data['role'] ?? 'main',
                                    'payment_status' => $payment_status
                                ];
                            }
                        }
                    }
                } else {
                    $message = "❌ 沒有選取任何收款項目";
                }
            } else {
                $message = "❌ 請先選取要確認收款的項目";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>店櫃收款審核系統 - 批量處理工作版</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: "Microsoft JhengHei", sans-serif; background: #f8f9fa; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; background: white; border-radius: 10px; box-shadow: 0 3px 15px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: #4a6fa5; color: white; padding: 20px; text-align: center; }
        .header h1 { font-size: 22px; margin: 0 0 10px 0; }
        .header .subtitle { font-size: 14px; opacity: 0.9; }
        .date-range { background: rgba(255,255,255,0.1); padding: 8px 12px; border-radius: 6px; font-size: 12px; margin-top: 10px; }
        .main-content { padding: 25px; }
        
        .search-section { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e9ecef; }
        .search-title { font-size: 16px; font-weight: 700; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
        .search-title::before { content: '🔍'; }
        
        .input-group { display: flex; gap: 10px; margin-bottom: 15px; }
        .store-input { flex: 1; padding: 10px 12px; border: 2px solid #ced4da; border-radius: 6px; font-size: 15px; }
        .store-input:focus { outline: none; border-color: #4a6fa5; }
        
        .btn { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s; }
        .btn-primary { background: #4a6fa5; color: white; }
        .btn-primary:hover { background: #3a5f95; }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; }
        .btn-warning { background: #ffc107; color: #333; }
        .btn-warning:hover { background: #e0a800; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #5a6268; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-danger:hover { background: #c82333; }
        
        .message { padding: 12px; border-radius: 6px; margin: 12px 0; font-weight: 500; }
        .message-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .message-error { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .message-info { background: #d1ecf1; color: #0c5460; border-left: 4px solid #17a2b8; }
        
        .store-info { background: #e9ecef; padding: 12px; border-radius: 6px; margin-bottom: 15px; }
        .store-info h3 { margin: 0 0 8px 0; font-size: 16px; }
        
        .payment-table { width: 100%; border-collapse: collapse; background: white; border-radius: 6px; overflow: hidden; margin-top: 20px; }
        .payment-table th { background: #f8f9fa; padding: 12px; text-align: left; font-weight: 700; border-bottom: 2px solid #dee2e6; }
        .payment-table td { padding: 12px; border-bottom: 1px solid #e9ecef; vertical-align: middle; }
        .payment-table tr:hover { background: #f8fbfe; }
        
        .amount-input { width: 120px; padding: 6px 8px; border: 1px solid #ced4da; border-radius: 4px; font-size: 13px; text-align: right; }
        .amount-input:focus { outline: none; border-color: #4a6fa5; box-shadow: 0 0 0 2px rgba(74, 111, 165, 0.1); }
        
        /* 開關按鈕樣式 */
        .toggle-btn { 
            display: inline-block; 
            width: 60px; 
            height: 30px; 
            border-radius: 15px; 
            position: relative; 
            cursor: pointer; 
            transition: all 0.3s;
            border: 2px solid #ccc;
        }
        
        .toggle-btn.off { 
            background: #f8f9fa; 
            border-color: #6c757d;
        }
        
        .toggle-btn.on { 
            background: #28a745; 
            border-color: #28a745;
        }
        
        .toggle-btn .toggle-handle {
            position: absolute;
            top: 2px;
            left: 2px;
            width: 22px;
            height: 22px;
            background: white;
            border-radius: 50%;
            transition: all 0.3s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .toggle-btn.on .toggle-handle {
            left: 32px;
        }
        
        .toggle-btn .toggle-label {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            font-size: 11px;
            font-weight: bold;
            color: white;
            pointer-events: none;
        }
        
        .toggle-btn.off .toggle-label {
            left: 35px;
            color: #6c757d;
        }
        
        .toggle-btn.on .toggle-label {
            left: 10px;
        }
        
        .toggle-btn.off .toggle-label::after {
            content: '關';
        }
        
        .toggle-btn.on .toggle-label::after {
            content: '開';
        }
        
        /* 批量操作區 */
        .batch-actions { 
            margin-top: 20px; 
            padding: 15px; 
            background: #f8f9fa; 
            border-radius: 8px; 
            border: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .batch-info {
            font-size: 14px;
            color: #495057;
        }
        
        .batch-info .count {
            font-weight: bold;
            color: #28a745;
        }
        
        .action-buttons { display: flex; gap: 10px; }
        
        .no-data { text-align: center; padding: 30px; color: #6c757d; }
        .control-section { margin-top: 30px; padding-top: 15px; border-top: 1px solid #e9ecef; text-align: center; }
        
        /* 手機響應式 */
        @media (max-width: 768px) {
            body { padding: 10px; }
            .input-group { flex-direction: column; }
            .payment-table { display: block; overflow-x: auto; }
            .action-buttons { flex-direction: column; gap: 5px; }
            .btn { width: 100%; justify-content: center; }
            .batch-actions { flex-direction: column; gap: 15px; }
            .toggle-btn { width: 50px; height: 26px; }
            .toggle-btn.on .toggle-handle { left: 24px; }
            .toggle-btn .toggle-handle { width: 18px; height: 18px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💰 店櫃收款審核系統 - 批量處理工作版</h1>
            <div class="subtitle">管理員專用 - 批量審核店櫃是否已寄回款項</div>
            <div class="date-range">
                查詢日期範圍：<?php echo $date_range[4]; ?> 至 <?php echo $date_range[0]; ?>（含今天前五天）
            </div>
        </div>
        
        <div class="main-content">
            <?php if (!empty($message)): ?>
                <div class="message <?php echo strpos($message, '✅') !== false ? 'message-success' : (strpos($message, '❌') !== false ? 'message-error' : 'message-info'); ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <div class="search-section">
                <div class="search-title">查詢店櫃未收款業績</div>
                
                <form method="POST" action="" id="searchForm">
                    <div class="input-group">
                        <input type="text" 
                               name="store_code" 
                               class="store-input" 
                               placeholder="請輸入店櫃代號（例如：A001）" 
                               value="<?php echo htmlspecialchars($store_code); ?>"
                               required
                               autofocus
                               autocomplete="off">
                        <button type="submit" class="btn btn-primary">
                            <span>🔍</span>
                            <span>查詢</span>
                        </button>
                    </div>
                </form>
                
                <?php if (!empty($store_code) && !empty($store_name)): ?>
                <div class="store-info">
                    <h3>店櫃資訊：<?php echo htmlspecialchars($store_code); ?> - <?php echo htmlspecialchars($store_name); ?></h3>
                    <p>查詢日期範圍：<?php echo $date_range[4]; ?> 至 <?php echo $date_range[0]; ?></p>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($unpaid_sales)): ?>
            <form method="POST" action="" id="paymentForm">
                <input type="hidden" name="store_code" value="<?php echo htmlspecialchars($store_code); ?>">
                
                <div class="unpaid-list">
                    <div class="search-title">未收款業績列表</div>
                    
                    <table class="payment-table">
                        <thead>
                            <tr>
                                <th style="width: 80px;">選擇</th>
                                <th style="width: 100px;">日期</th>
                                <th style="width: 120px;">金額</