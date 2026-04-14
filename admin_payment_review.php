<?php
/**
 * 管理員收款審核系統
 * 功能：審核店櫃是否已寄回款項，確認收款狀態（支援批量處理）
 */

// 啟動 Session
session_start();

require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/auth_simple.php';

// 檢查登入
require_login();

$user = get_current_session_user();

// 檢查權限：只有老闆和管理員可以訪問
if (!in_array($user['role'], ['boss', 'admin'])) {
    header('Location: ../dashboard.php');
    exit;
}

// 取得今天日期
$today = date('Y-m-d');

// 計算前十天日期（含今天，由小到大：最早到最新）
$date_range = [];
for ($i = 9; $i >= 0; $i--) {
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
$is_postback = false; // 標記是否為提交後頁面

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $is_postback = true;
    
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
                // 查詢該店櫃前十天的未收款業績
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
                
                if (empty($unpaid_sales)) {
                    $message = "✅ 店櫃 {$store_code} ({$store_name}) 前十天無未收款業績";
                }
            } else {
                $message = "❌ 店櫃代號 {$store_code} 不存在";
            }
        }
    }
    
    // 處理批量儲存
    if (isset($_POST['save_payments'])) {
        $store_code_to_save = $_POST['store_code'] ?? '';
        
        if (!empty($store_code_to_save)) {
            $saved_count = 0;
            $error_count = 0;
            
            if (isset($_POST['selected_payments']) && is_array($_POST['selected_payments'])) {
                foreach ($_POST['selected_payments'] as $payment_date) {
                    $month = date('Y-m', strtotime($payment_date));
                    $sales_data = load_monthly_sales($month);
                    
                    if (isset($sales_data[$payment_date][$store_code_to_save])) {
                        $sales_data[$payment_date][$store_code_to_save]['payment_status'] = 'paid';
                        $sales_data[$payment_date][$store_code_to_save]['payment_confirmed_by'] = $user['id'];
                        $sales_data[$payment_date][$store_code_to_save]['payment_confirmed_at'] = date('Y-m-d H:i:s');
                        
                        $amount_key = 'amount_' . str_replace('-', '_', $payment_date);
                        if (isset($_POST[$amount_key]) && is_numeric($_POST[$amount_key])) {
                            $new_amount = (int)$_POST[$amount_key];
                            $sales_data[$payment_date][$store_code_to_save]['amount'] = $new_amount;
                        }
                        
                        if (save_monthly_sales($month, $sales_data)) {
                            $saved_count++;
                        } else {
                            $error_count++;
                        }
                    }
                }
                
                if ($saved_count > 0) {
                    $message = "✅ 已成功儲存 {$saved_count} 筆收款確認";
                    // 儲存成功後才清空 store_code
                    $store_code = ''; 
                    $store_name = '';
                    $unpaid_sales = [];
                }
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
    <title>店櫃收款審核系統 - 最終版</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: "Microsoft JhengHei", sans-serif; background: #f8f9fa; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; background: white; border-radius: 10px; box-shadow: 0 3px 15px rgba(0,0,0,0.1); overflow: hidden; }
        .header { 
            background: #4a6fa5; 
            color: white; 
            padding: 20px; 
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .header-content {
            flex: 1;
            text-align: left;
        }
        .header h1 { font-size: 22px; margin: 0 0 10px 0; }
        .header .subtitle { font-size: 14px; opacity: 0.9; }
        .date-range { background: rgba(255,255,255,0.1); padding: 8px 12px; border-radius: 6px; font-size: 12px; margin-top: 10px; }
        
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
            white-space: nowrap;
        }
        
        .back-btn:hover {
            background: #5a6268;
        }
        .main-content { padding: 25px; }
        .search-section { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e9ecef; }
        .search-title { font-size: 16px; font-weight: 700; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
        .search-title::before { content: '🔍'; }
        .input-group { display: flex; gap: 10px; margin-bottom: 15px; justify-content: center; align-items: center; }
        .store-input { width: 230px; padding: 10px 12px; border: 2px solid #ced4da; border-radius: 6px; font-size: 24px; text-align: center; font-weight: 600; }
        .store-input:focus { outline: none; border-color: #4a6fa5; }
        
        /* 電腦端：加大字體以補償像素密度差異 */
        @media (min-width: 768px) {
            .store-input {
                font-size: 28px;  /* 在電腦上加大4px */
            }
            .amount-input {
                font-size: 22px;  /* 在電腦上加大2px */
            }
        }
        .btn { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s; }
        .btn-primary { background: #4a6fa5; color: white; }
        .message { padding: 12px; border-radius: 6px; margin: 12px 0; font-weight: 500; }
        .message-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .message-error { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .store-info { background: #e9ecef; padding: 12px; border-radius: 6px; margin-bottom: 15px; }
        .payment-table { width: 100%; border-collapse: collapse; background: white; border-radius: 6px; overflow: hidden; margin-top: 20px; }
        .payment-table th { background: #f8f9fa; padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6; }
        .payment-table td { padding: 12px; border-bottom: 1px solid #e9ecef; }
        .amount-input { width: 98px; padding: 8px 10px; border: 1px solid #ced4da; border-radius: 4px; text-align: right; font-size: 20px; font-weight: 600; }
        .confirm-btn { padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 13px; transition: all 0.2s; border: 1px solid #ced4da; }
        .confirm-btn.selected { background: #28a745; color: white; border-color: #28a745; }
        .batch-actions { margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; }
        .count { font-weight: bold; color: #28a745; }
        .no-data { text-align: center; padding: 30px; color: #6c757d; }
        .control-section { margin-top: 30px; padding-top: 15px; border-top: 1px solid #e9ecef; text-align: center; font-size: 13px; color: #666; }
        
        /* 響應式設計 */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .header-content {
                text-align: center;
            }
            
            .back-btn {
                width: 100%;
                max-width: 200px;
                margin: 0 auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <h1>💰 店櫃收款審核系統</h1>
                <div class="subtitle">管理員專用 - 批量審核店櫃是否已寄回款項</div>
                <div class="date-range">查詢範圍：<?php echo $date_range[0]; ?> 至 <?php echo $date_range[9]; ?></div>
            </div>
            <a href="dashboard.php" class="back-btn">返回儀表板</a>
        </div>
        
        <div class="main-content">
            <?php if (!empty($message)): ?>
                <div class="message <?php echo strpos($message, '✅') !== false ? 'message-success' : 'message-error'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <div class="search-section">
                <form method="POST" action="" id="searchForm">
                    <div class="input-group">
                        <input type="text" 
                               name="store_code" 
                               id="store_code_input"
                               class="store-input" 
                               placeholder="輸入店櫃代號" 
                               value="<?php echo htmlspecialchars($store_code); ?>"
                               required
                               autofocus
                               autocomplete="off">
                        <button type="submit" class="btn btn-primary">🔍 查詢</button>
                    </div>
                </form>
                
                <?php if (!empty($store_name)): ?>
                <div class="store-info">
                    <h3>店櫃：<?php echo htmlspecialchars($store_code); ?> - <?php echo htmlspecialchars($store_name); ?></h3>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($unpaid_sales)): ?>
            <form method="POST" action="" id="paymentForm">
                <input type="hidden" name="store_code" value="<?php echo htmlspecialchars($store_code); ?>">
                <table class="payment-table">
                    <thead>
                        <tr>
                            <th>日期</th><th>金額</th><th>角色</th><th>確認</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($unpaid_sales as $sale): ?>
                        <tr>
                            <td><?php echo $sale['date']; ?></td>
                            <td>
                                <input type="number" name="amount_<?php echo str_replace('-', '_', $sale['date']); ?>" 
                                       class="amount-input" value="<?php echo $sale['amount']; ?>">
                            </td>
                            <td><?php echo $sale['role'] === 'substitute' ? '代班' : '正職'; ?></td>
                            <td>
                                <button type="button" class="confirm-btn unselected" onclick="toggleConfirm(this, '<?php echo $sale['date']; ?>')">🔘 未選</button>
                                <input type="hidden" name="selected_payments[]" value="<?php echo $sale['date']; ?>" disabled>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="batch-actions">
                    <div class="batch-info">已選取 <span class="count">0</span> 筆</div>
                    <button type="submit" name="save_payments" class="btn btn-primary" style="background: #28a745;">💾 儲存</button>
                </div>
            </form>
            <?php else: ?>
                <div class="no-data">請輸入店櫃代號開始查詢</div>
            <?php endif; ?>

            <div class="control-section">
                <p>小技巧：輸入代號後直接按 Enter 即可查詢。儲存後系統會自動選中文字，方便直接輸入下一筆。</p>
            </div>
        </div>
    </div>

    <script>
        // 核心功能：全選輸入框文字
        function selectStoreInput() {
            const input = document.getElementById('store_code_input');
            if (input) {
                input.focus();
                // 針對所有瀏覽器的全選方案
                setTimeout(() => {
                    input.select();
                    input.setSelectionRange(0, input.value.length);
                }, 10);
            }
        }

        // 頁面載入時執行全選
        window.addEventListener('load', selectStoreInput);
        
        // 額外保險：如果 PHP 判斷是 POST 回來的頁面，加強執行
        <?php if ($is_postback): ?>
        document.addEventListener('DOMContentLoaded', selectStoreInput);
        setTimeout(selectStoreInput, 100); 
        <?php endif; ?>

        function toggleConfirm(btn, date) {
            const hiddenInput = btn.nextElementSibling;
            if (btn.classList.contains('selected')) {
                btn.classList.remove('selected');
                btn.innerHTML = '🔘 未選';
                hiddenInput.disabled = true;
            } else {
                btn.classList.add('selected');
                btn.innerHTML = '✅ 已選';
                hiddenInput.disabled = false;
            }
            document.querySelector('.count').textContent = document.querySelectorAll('.confirm-btn.selected').length;
        }

        // 監聽 Enter 鍵防止重複提交或異常
        document.getElementById('store_code_input').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                // 允許原生提交，但可以在此處加入 loading 效果
            }
        });
    </script>
</body>
</html>