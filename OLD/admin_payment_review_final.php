<?php
/**
 * 蝞∠??⊥甈曉祟?貊頂蝯?- ?蝯?
 * ?嚗祟?詨?瑹?血歇撖?甈暸?嚗Ⅱ隤甈曄????舀?寥???嚗? */

// ?? Session
session_start();

require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/auth.php';

// 瑼Ｘ?餃
require_login();

$user = get_current_session_user();

// 瑼Ｘ甈?嚗?恣??臭誑閮芸?
if ($user['role'] !== 'admin') {
    header('Location: ../dashboard.php');
    exit;
}

// ??隞予?交?
$today = date('Y-m-d');

// 閮???憭拇???思?憭抬?
$date_range = [];
for ($i = 0; $i < 10; $i++) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $date_range[] = $date;
}

// 頛摨?鞈?
$stores = load_data('stores');

// ??銵典?漱
$store_code = '';
$store_name = '';
$unpaid_sales = [];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ??摨?隞???亥岷
    if (isset($_POST['store_code'])) {
        $store_code = trim($_POST['store_code']);
        
        if (!empty($store_code)) {
            // 瑼Ｘ摨??臬摮
            $store_exists = false;
            foreach ($stores as $store) {
                if ($store['code'] === $store_code) {
                    $store_exists = true;
                    $store_name = $store['name'];
                    break;
                }
            }
            
            if ($store_exists) {
                // ?亥岷閰脣?瑹?鈭予??嗆狡璆剔蜀
                $unpaid_sales = [];
                
                foreach ($date_range as $date) {
                    // 頛閰脫???瑕鞈?
                    $month = date('Y-m', strtotime($date));
                    $sales_data = load_monthly_sales($month);
                    
                    if (isset($sales_data[$date][$store_code])) {
                        $sale_data = $sales_data[$date][$store_code];
                        $amount = $sale_data['amount'] ?? 0;
                        
                        // 瑼Ｘ?嗆狡????身??'unpaid' ?芣甈橘?
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
                    $message = "??摨? {$store_code} ({$store_name}) ??憭拍?芣甈暹平蝮?;
                }
            } else {
                $message = "??摨?隞?? {$store_code} 銝???;
            }
        }
    }
    
    // ???寥??脣?
    if (isset($_POST['save_payments'])) {
        $store_code = $_POST['store_code'] ?? '';
        
        if (!empty($store_code)) {
            $saved_count = 0;
            $error_count = 0;
            
            // 瑼Ｘ?臬????嗆狡?
            if (isset($_POST['selected_payments']) && is_array($_POST['selected_payments'])) {
                foreach ($_POST['selected_payments'] as $payment_date) {
                    // 頛閰脫?隞賜??瑕鞈?
                    $month = date('Y-m', strtotime($payment_date));
                    $sales_data = load_monthly_sales($month);
                    
                    if (isset($sales_data[$payment_date][$store_code])) {
                        // ?湔?嗆狡???                        $sales_data[$payment_date][$store_code]['payment_status'] = 'paid';
                        $sales_data[$payment_date][$store_code]['payment_confirmed_by'] = $user['id'];
                        $sales_data[$payment_date][$store_code]['payment_confirmed_at'] = date('Y-m-d H:i:s');
                        
                        // 憒??耨?寥?憿??湔??
                        $amount_key = 'amount_' . str_replace('-', '_', $payment_date);
                        if (isset($_POST[$amount_key]) && is_numeric($_POST[$amount_key])) {
                            $new_amount = (int)$_POST[$amount_key];
                            if ($new_amount >= 0) {
                                $sales_data[$payment_date][$store_code]['amount'] = $new_amount;
                                $sales_data[$payment_date][$store_code]['amount_modified_by'] = $user['id'];
                                $sales_data[$payment_date][$store_code]['amount_modified_at'] = date('Y-m-d H:i:s');
                            }
                        }
                        
                        // ?脣??湔敺?鞈?
                        if (save_monthly_sales($month, $sales_data)) {
                            $saved_count++;
                        } else {
                            $error_count++;
                        }
                    }
                }
                
                if ($saved_count > 0) {
                    $message = "??撌脫??摮?{$saved_count} 蝑甈曄Ⅱ隤?;
                    if ($error_count > 0) {
                        $message .= "嚗$error_count} 蝑仃??;
                    }
                    
                    // ??亥岷?芣甈暹平蝮?                    $unpaid_sales = [];
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
                    $message = "??瘝??詨?隞颱??嗆狡?";
                }
            } else {
                $message = "??隢??詨?閬Ⅱ隤甈曄??";
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
    <title>摨??嗆狡撖拇蝟餌絞 - ?蝯?</title>
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
        .search-title::before { content: '??'; }
        
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
        
        .amount-input { width: 140px; padding: 8px 10px; border: 1px solid #ced4da; border-radius: 4px; font-size: 16px; font-weight: 600; text-align: right; color: #333; }
        .amount-input:focus { outline: none; border-color: #4a6fa5; box-shadow: 0 0 0 3px rgba(74, 111, 165, 0.2); }
        
        /* 瘚株?/????璅?? */
        .confirm-btn { 
            padding: 8px 16px; 
            border: none; 
            border-radius: 6px; 
            cursor: pointer; 
            font-weight: 600; 
            font-size: 13px; 
            display: inline-flex; 
            align-items: center; 
            gap: 4px; 
            transition: all 0.2s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .confirm-btn.unselected { 
            background: #f8f9fa; 
            color: #495057;
            border: 1px solid #ced4da;
        }
        
        .confirm-btn.selected { 
            background: #28a745; 
            color: white;
            border: 1px solid #28a745;
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
        }
        
        .confirm-btn.unselected:hover { 
            background: #e9ecef; 
            border-color: #adb5bd;
        }
        
        .confirm-btn.selected:hover { 
            background: #218838; 
            border-color: #1e7e34;
        }
        
        /* ?寥???? */
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
        
        /* ???踵?撘?*/
        @media (max-width: 768px) {
            body { padding: 10px; }
            .input-group { flex-direction: column; }
            .payment-table { display: block; overflow-x: auto; }
            .action-buttons { flex-direction: column; gap: 5px; }
            .btn { width: 100%; justify-content: center; }
            .batch-actions { flex-direction: column; gap: 15px; }
            .confirm-btn { padding: 6px 12px; font-size: 12px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>? 摨??嗆狡撖拇蝟餌絞 - ?蝯?</h1>
            <div class="subtitle">蝞∠??∪???- ?寥?撖拇摨??臬撌脣??狡??/div>
            <div class="date-range">
                ?亥岷?交?蝭?嚗??php echo $date_range[9]; ?> ??<?php echo $date_range[0]; ?>嚗隞予??憭抬?
            </div>
        </div>
        
        <div class="main-content">
            <?php if (!empty($message)): ?>
                <div class="message <?php echo strpos($message, '??) !== false ? 'message-success' : (strpos($message, '??) !== false ? 'message-error' : 'message-info'); ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <div class="search-section">
                <div class="search-title">?亥岷摨??芣甈暹平蝮?/div>
                
                <form method="POST" action="" id="searchForm">
                    <div class="input-group">
                        <input type="text" 
                               name="store_code" 
                               class="store-input" 
                               placeholder="隢撓?亙?瑹誨??靘?嚗001嚗? 
                               value="<?php echo htmlspecialchars($store_code); ?>"
                               required
                               autofocus
                               autocomplete="off">
                        <button type="submit" class="btn btn-primary">
                            <span>??</span>
                            <span>?亥岷</span>
                        </button>
                    </div>
                </form>
                
                <?php if (!empty($store_code) && !empty($store_name)): ?>
                <div class="store-info">
                    <h3>摨?鞈?嚗??php echo htmlspecialchars($store_code); ?> - <?php echo htmlspecialchars($store_name); ?></h3>
                    <p>?亥岷?交?蝭?嚗??php echo $date_range[9]; ?> ??<?php echo $date_range[0]; ?></p>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($unpaid_sales)): ?>
            <form method="POST" action="" id="paymentForm">
                <input type="hidden" name="store_code" value="<?php echo htmlspecialchars($store_code); ?>">
                
                <div class="unpaid-list">
                    <div class="search-title">?芣甈暹平蝮曉?銵?/div>
                    
                    <table class="payment-table">
                        <thead>
                            <tr>
                                <th style="width: 100px;">?交?</th>
                                <th style="width: 120px;">??</th>
                                <th style="width: 80px;">閫</th>
                                <th style="width: 100px;">蝣箄??嗆狡</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($unpaid_sales as $index => $sale): ?>
                            <tr>
                                <td><?php echo $sale['date']; ?></td>
                                <td>
                                    <input type="number" 
                                           name="amount_<?php echo str_replace('-', '_', $sale['date']); ?>"
                                           class="amount-input"
                                           value="<?php echo $sale['amount']; ?>"
                                           min="0"
                                           step="1">
                                </td>
                                <td><?php echo $sale['role'] === 'substitute' ? '隞?' : '甇?'; ?></td>
                                <td>
                                    <button type="button" 
                                            class="confirm-btn unselected"
                                            data-date="<?php echo $sale['date']; ?>"
                                            onclick="toggleConfirm(this, '<?php echo $sale['date']; ?>')">
                                        <span class="btn-icon">??</span>
                                        <span class="btn-text">?芷</span>
                                    </button>
                                    <input type="hidden" name="selected_payments[]" value="<?php echo $sale['date']; ?>" disabled>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="batch-actions">
                        <div class="batch-info">
                            撌脤??<span class="count">0</span> 蝑甈暸???                        </div>
                        <div class="action-buttons">
                            <button type="submit" name="save_payments" class="btn btn-success">
                                ? ?脣?
                            </button>
                            <button type="button" onclick="window.close()" class="btn btn-secondary">
                                ????
                            </button>
                        </div>
                    </div>
                </div>
            </form>
            
            <script>
                // 蝣箄??嗆狡??暺?鈭辣
                function toggleConfirm(btn, date) {
                    const isSelected = btn.classList.contains('selected');
                    const hiddenInput = btn.nextElementSibling;
                    
                    if (isSelected) {
                        // ???豢?
                        btn.classList.remove('selected');
                        btn.classList.add('unselected');
                        btn.querySelector('.btn-icon').textContent = '??';
                        btn.querySelector('.btn-text').textContent = '?芷';
                        hiddenInput.disabled = true;
                    } else {
                        // ?豢?
                        btn.classList.remove('unselected');
                        btn.classList.add('selected');
                        btn.querySelector('.btn-icon').textContent = '??;
                        btn.querySelector('.btn-text').textContent = '撌脤';
                        hiddenInput.disabled = false;
                    }
                    
                    // ?湔撌脤?閮
                    updateSelectedCount();
                }
                
                // ?湔撌脤?閮
                function updateSelectedCount() {
                    const selectedCount = document.querySelectorAll('.confirm-btn.selected').length;
                    document.querySelector('.batch-info .count').textContent = selectedCount;
                }
                
                // ?脣?敺???血頛詨獢?                document.addEventListener('DOMContentLoaded', function() {
                    const storeInput = document.querySelector('.store-input');
                    if (storeInput) {
                        storeInput.focus();
                    }
                    
                    // ??銵典?漱
                    const paymentForm = document.getElementById('paymentForm');
                    if (paymentForm) {
                        paymentForm.addEventListener('submit', function() {
                            // ?脣?敺辣?脖?銝??
                            setTimeout(function() {
                                const storeInput = document.querySelector('.store-input');
                                if (storeInput) {
                                    storeInput.focus();
                                    storeInput.select();
                                }
                            }, 100);
                        });
                    }
                });
                
                // ??Enter ?菜?鈭斗閰Ｚ”??                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && e.target.classList.contains('store-input')) {
                        e.preventDefault();
                        document.getElementById('searchForm').submit();
                    }
                });
            </script>
            <?php else: ?>
            <div class="no-data">
                <p>隢?頛詨摨?隞???亥岷?芣甈暹平蝮?/p>
                <p>?府摨???憭拍?芣甈暹平蝮?/p>
            </div>
            <?php endif; ?>
            
            <div class="control-section">
                <p>雿輻隤芣?嚗撓?亙?瑹誨?????亥岷?芣甈暹平蝮???暺??Ⅱ隤甈整????????靽格??嚗?賂????脣? ????</p>
                <p>?脣?敺虜璅??芸?頝喳?頛詨獢??嫣噶蝜潛?銝?蝑?璆?/p>
            </div>
        </div>
    </div>
</body>
</html>
