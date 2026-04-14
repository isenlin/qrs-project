<?php
/**
 * 編輯每日業績資料頁面
 * 彈出視窗，用於編輯單一店櫃單日業績
 */

// 啟動 Session
session_start();

require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/auth_simple.php';

// 檢查登入
require_login();

$user = get_current_session_user();

// 檢查權限：僅管理員和BOSS可以編輯
if (!in_array($user['role'], ['admin', 'boss'])) {
    echo '<script>alert("權限不足"); window.close();</script>';
    exit;
}

// 取得參數
$date = $_GET['date'] ?? '';
$store_code = $_GET['store'] ?? '';
$month = $_GET['month'] ?? date('Y-m');

// 驗證必要參數
if (empty($date) || empty($store_code)) {
    echo '<script>alert("缺少必要參數"); window.close();</script>';
    exit;
}

// 驗證日期格式
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo '<script>alert("日期格式錯誤"); window.close();</script>';
    exit;
}

// 載入店櫃資料
$stores = load_data('stores');
$users = load_data('users');

// 尋找指定店櫃
$store_info = null;
foreach ($stores as $store) {
    if ($store['code'] === $store_code) {
        $store_info = $store;
        break;
    }
}

if (!$store_info) {
    echo '<script>alert("找不到指定的店櫃"); window.close();</script>';
    exit;
}

// 清理電話號碼格式（移除非數字字符，保留+號）
function clean_phone_number($phone) {
    if (empty($phone)) return '';
    // 移除所有非數字字符，除了開頭的+號
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    return $phone;
}

// 準備清理後的電話號碼
$store_info['phone_clean'] = clean_phone_number($store_info['phone'] ?? '');
$store_info['mobile_clean'] = clean_phone_number($store_info['mobile'] ?? '');

// 載入銷售資料（按月儲存）
$month = substr($date, 0, 7); // 取得月份：YYYY-MM
$sales_data = load_monthly_sales($month);
$current_sales = $sales_data[$date][$store_code] ?? null;

// 取得業務名稱
$sales_person_name = '';
if (!empty($store_info['sales_person'])) {
    foreach ($users as $user_data) {
        if ($user_data['id'] === $store_info['sales_person']) {
            $sales_person_name = $user_data['name'];
            break;
        }
    }
}

// 取得督導名稱
$supervisor_name = '';
if (!empty($store_info['supervisor'])) {
    foreach ($users as $user_data) {
        if ($user_data['id'] === $store_info['supervisor']) {
            $supervisor_name = $user_data['name'];
            break;
        }
    }
}

// 設定預設值
$amount = $current_sales['amount'] ?? null;
$role = $current_sales['role'] ?? 'main';
$payment_status = $current_sales['payment_status'] ?? 'unpaid';

// 處理表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 取得 POST 資料
    $amount = $_POST['amount'] !== '' ? (float)$_POST['amount'] : null;
    $role = $_POST['role'] ?? 'main';
    $payment_status = $_POST['payment_status'] ?? 'unpaid';
    
    // 驗證資料
    $errors = [];
    
    if ($amount !== null && !is_numeric($amount)) {
        $errors[] = '業績金額格式錯誤';
    }
    
    if (!in_array($role, ['main', 'substitute'])) {
        $errors[] = '角色選擇錯誤';
    }
    
    if (!in_array($payment_status, ['paid', 'unpaid', 'dayoff'])) {
        $errors[] = '收款狀態選擇錯誤';
    }
    
    if (empty($errors)) {
        // 更新銷售資料
        if (!isset($sales_data[$date])) {
            $sales_data[$date] = [];
        }
        
        $sales_data[$date][$store_code] = [
            'amount' => $amount,
            'role' => $role,
            'payment_status' => $payment_status,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $user['id']
        ];
        
        // 儲存資料
        save_data('sales', $sales_data);
        
        // 記錄操作（如果有 log_operation 函數）
        if (function_exists('log_operation')) {
            log_operation($user['id'], 'update_daily_sales', [
                'date' => $date,
                'store_code' => $store_code,
                'amount' => $amount,
                'role' => $role,
                'payment_status' => $payment_status
            ]);
        }
        
        // 回傳成功訊息（JSON）
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => '資料儲存成功'
        ]);
        exit;
    } else {
        // 回傳錯誤訊息
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => implode('<br>', $errors)
        ]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>編輯每日業績 - <?php echo htmlspecialchars($date); ?> - <?php echo htmlspecialchars($store_info['name']); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        
        body {
            background: #f5f7fa;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }
        
        .edit-container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            overflow: hidden;
        }
        
        .edit-header {
            background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);
            color: white;
            padding: 25px 30px;
            text-align: center;
        }
        
        .edit-header h1 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .edit-header .subtitle {
            font-size: 14px;
            opacity: 0.9;
            margin-top: 5px;
        }
        
        .edit-content {
            padding: 30px;
        }
        
        .info-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 12px;
            font-size: 15px;
        }
        
        .info-row:last-child {
            margin-bottom: 0;
        }
        
        .info-label {
            font-weight: 600;
            color: #495057;
            min-width: 80px;
            flex-shrink: 0;
        }
        
        .info-value {
            color: #212529;
            font-weight: 500;
        }
        
        .form-section {
            margin-bottom: 25px;
        }
        
        .form-section h3 {
            font-size: 16px;
            font-weight: 700;
            color: #495057;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #6f42c1;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #495057;
            font-size: 14px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.2s;
            background: white;
        }
        
        /* 業績金額輸入框 - 字體加大兩級（15px → 24px） */
        .amount-input {
            font-size: 24px !important;
            font-weight: 700 !important;
            text-align: center;
            color: #212529 !important;
            padding: 15px 20px !important;
            height: auto !important;
            line-height: 1.5 !important;
        }
        
        .amount-input::placeholder {
            font-size: 16px !important;
            font-weight: 400 !important;
            color: #6c757d !important;
            opacity: 0.8 !important;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #6f42c1;
            box-shadow: 0 0 0 3px rgba(111, 66, 193, 0.1);
        }
        
        .radio-group {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .radio-option {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        
        .radio-option input[type="radio"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        /* 按鈕選項樣式 */
        .button-options {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .option-btn {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            background: white;
            color: #495057;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
            min-width: 80px;
        }
        
        /* 代班狀態按鈕的懸停效果 */
        .option-btn[data-name="role"]:hover {
            border-color: #6f42c1;
            color: #6f42c1;
            background: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(111, 66, 193, 0.1);
        }
        
        /* 收款狀態按鈕的懸停效果 - 保持原有顏色 */
        .option-btn[data-name="payment_status"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        /* 代班狀態按鈕 */
        .option-btn[data-value="main"].active {
            background: #6f42c1;
            border-color: #6f42c1;
            color: white;
        }
        
        .option-btn[data-value="substitute"].active {
            background: #6f42c1;
            border-color: #6f42c1;
            color: white;
        }
        
        /* 收款狀態按鈕顏色 - 與 daily_sales_v2.php 一致 */
        .option-btn[data-value="paid"] {
            border-color: #c8e6c9;
            color: #2e7d32;
        }
        
        .option-btn[data-value="paid"].active {
            background: #e8f5e9;  /* 淺綠 */
            border-color: #2e7d32;
            color: #2e7d32;
        }
        
        .option-btn[data-value="unpaid"] {
            border-color: #ffcdd2;
            color: #c62828;
        }
        
        .option-btn[data-value="unpaid"].active {
            background: #ffebee;  /* 淺粉紅 */
            border-color: #c62828;
            color: #c62828;
        }
        
        .option-btn[data-value="dayoff"] {
            border-color: #ffe0b2;
            color: #ef6c00;
        }
        
        .option-btn[data-value="dayoff"].active {
            background: #fff3e0;  /* 淺橙 */
            border-color: #ef6c00;
            color: #ef6c00;
        }
        
        /* 所有激活按鈕的懸停效果 */
        .option-btn.active:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .radio-option span {
            font-size: 14px;
            color: #495057;
        }
        
        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 40px;
            padding-top: 25px;
            border-top: 1px solid #e9ecef;
        }
        
        .btn {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-save {
            background: linear-gradient(135deg, #28a745 0%, #218838 100%);
            color: white;
        }
        
        .btn-save:hover {
            background: linear-gradient(135deg, #218838 0%, #1e7e34 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }
        
        .btn-cancel {
            background: #6c757d;
            color: white;
        }
        
        .btn-cancel:hover {
            background: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            display: block;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            display: block;
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            display: none;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #6f42c1;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="edit-container">
        <div class="edit-header">
            <h1>編輯每日業績</h1>
            <div class="subtitle"><?php echo htmlspecialchars($date); ?> - <?php echo htmlspecialchars($store_info['code']); ?> - <?php echo htmlspecialchars($store_info['name']); ?></div>
        </div>
        
        <div class="edit-content">
            <div class="info-section">
                <div class="info-row">
                    <div class="info-label">業務：</div>
                    <div class="info-value"><?php echo htmlspecialchars($sales_person_name); ?></div>
                    <div style="margin-left: 30px; font-weight: 600; color: #495057;">督導：</div>
                    <div class="info-value" style="margin-left: 5px;"><?php echo htmlspecialchars($supervisor_name); ?></div>
                </div>
                <?php if (!empty($store_info['phone']) || !empty($store_info['mobile'])): ?>
                <div class="info-row" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e9ecef;">
                    <div class="info-label">聯絡：</div>
                    <div class="info-value" style="display: flex; flex-wrap: wrap; gap: 15px;">
                        <?php if (!empty($store_info['phone'])): ?>
                        <a href="tel:<?php echo htmlspecialchars($store_info['phone_clean']); ?>" 
                           class="phone-link" 
                           style="display: inline-flex; align-items: center; gap: 5px; background: #e3f2fd; color: #1565c0; padding: 8px 15px; border-radius: 20px; text-decoration: none; font-weight: 600; transition: all 0.2s;"
                           onmouseover="this.style.backgroundColor='#bbdefb'; this.style.transform='translateY(-2px)';"
                           onmouseout="this.style.backgroundColor='#e3f2fd'; this.style.transform='translateY(0)';"
                           title="點擊撥打市話">
                            <span style="font-size: 16px;">📞</span>
                            <span><?php echo htmlspecialchars($store_info['phone']); ?></span>
                        </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($store_info['mobile'])): ?>
                        <a href="tel:<?php echo htmlspecialchars($store_info['mobile_clean']); ?>" 
                           class="phone-link" 
                           style="display: inline-flex; align-items: center; gap: 5px; background: #e8f5e9; color: #2e7d32; padding: 8px 15px; border-radius: 20px; text-decoration: none; font-weight: 600; transition: all 0.2s;"
                           onmouseover="this.style.backgroundColor='#c8e6c9'; this.style.transform='translateY(-2px)';"
                           onmouseout="this.style.backgroundColor='#e8f5e9'; this.style.transform='translateY(0)';"
                           title="點擊撥打手機">
                            <span style="font-size: 16px;">📱</span>
                            <span><?php echo htmlspecialchars($store_info['mobile']); ?></span>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div id="message" class="message"></div>
            
            <form id="edit-form">
                <div class="form-section">
                    <h3>業績資訊</h3>
                    
                    <div class="form-group">
                        <label>代班狀態</label>
                        <div class="button-options">
                            <button type="button" class="option-btn <?php echo $role === 'main' ? 'active' : ''; ?>" data-value="main" data-name="role">
                                主櫃
                            </button>
                            <button type="button" class="option-btn <?php echo $role === 'substitute' ? 'active' : ''; ?>" data-value="substitute" data-name="role">
                                代班
                            </button>
                        </div>
                        <input type="hidden" id="role" name="role" value="<?php echo htmlspecialchars($role); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="amount">業績金額</label>
                        <input type="number" id="amount" name="amount" class="form-control amount-input" 
                               value="<?php echo $amount !== null ? htmlspecialchars($amount) : ''; ?>" 
                               placeholder="輸入業績金額（留空表示無業績）" step="1" min="0">
                        <small style="color: #6c757d; font-size: 13px; margin-top: 5px; display: block;">
                            留空表示該日無業績資料
                        </small>
                    </div><div class="form-group">
                        <label>收款狀態</label>
                        <div class="button-options">
                            <button type="button" class="option-btn <?php echo $payment_status === 'paid' ? 'active' : ''; ?>" data-value="paid" data-name="payment_status">
                                已收
                            </button>
                            <button type="button" class="option-btn <?php echo $payment_status === 'unpaid' ? 'active' : ''; ?>" data-value="unpaid" data-name="payment_status">
                                未收
                            </button>
                            <button type="button" class="option-btn <?php echo $payment_status === 'dayoff' ? 'active' : ''; ?>" data-value="dayoff" data-name="payment_status">
                                店休
                            </button>
                        </div>
                        <input type="hidden" id="payment_status" name="payment_status" value="<?php echo htmlspecialchars($payment_status); ?>">
                        <small style="color: #6c757d; font-size: 13px; margin-top: 5px; display: block;">
                            選擇「店休」時，業績金額將被忽略，顯示為「-」
                        </small>
                    </div>
                </div>
                
                <div class="button-group">
                    <button type="submit" class="btn btn-save">
                        <span>💾</span> 儲存離開
                    </button>
                    <button type="button" class="btn btn-cancel" onclick="window.close()">
                        <span>❌</span> 取消
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div id="loading" class="loading-overlay">
        <div class="spinner"></div>
        <div style="font-size: 16px; color: #6c757d;">儲存中...</div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('edit-form');
            const messageDiv = document.getElementById('message');
            const loadingOverlay = document.getElementById('loading');
            
            // 表單提交處理
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // 顯示載入中
                loadingOverlay.style.display = 'flex';
                
                // 收集表單資料
                const formData = new FormData(form);
                
                // 將 amount 轉換為適當的值（空字串轉為 null）
                let amount = formData.get('amount');
                if (amount === '') {
                    amount = null;
                } else if (amount !== null) {
                    amount = parseFloat(amount);
                }
                
                // 建立請求資料
                const data = {
                    date: '<?php echo $date; ?>',
                    store_code: '<?php echo $store_code; ?>',
                    amount: amount,
                    role: formData.get('role'),
                    payment_status: formData.get('payment_status')
                };
                
                try {
                    // 發送請求到 save_daily_sales.php
                    const response = await fetch('save_daily_sales.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(data)
                    });
                    
                    const result = await response.json();
                    
                    // 隱藏載入中
                    loadingOverlay.style.display = 'none';
                    
                    if (result.success) {
                        // 顯示成功訊息
                        showMessage('資料儲存成功！', 'success');
                        
                        // 等待 1 秒後關閉視窗並重新整理父視窗
                        setTimeout(() => {
                            if (window.opener && !window.opener.closed) {
                                window.opener.location.reload();
                            }
                            window.close();
                        }, 1000);
                    } else {
                        // 顯示錯誤訊息
                        showMessage(result.message || '儲存失敗，請重試', 'error');
                    }
                } catch (error) {
                    // 隱藏載入中
                    loadingOverlay.style.display = 'none';
                    
                    showMessage('網路錯誤，請檢查連線後重試', 'error');
                    console.error('儲存錯誤:', error);
                }
            });
            
            // 按鈕選項處理
            document.querySelectorAll('.option-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const value = this.getAttribute('data-value');
                    const name = this.getAttribute('data-name');
                    
                    // 移除同組中其他按鈕的 active 類
                    const siblingBtns = this.parentElement.querySelectorAll('.option-btn');
                    siblingBtns.forEach(sibling => {
                        sibling.classList.remove('active');
                    });
                    
                    // 為點擊的按鈕添加 active 類
                    this.classList.add('active');
                    
                    // 更新對應的隱藏輸入欄位
                    const hiddenInput = document.getElementById(name);
                    if (hiddenInput) {
                        hiddenInput.value = value;
                    }
                    
                    // 特殊處理：如果選擇「店休」，禁用金額欄位
                    if (name === 'payment_status' && value === 'dayoff') {
                        const amountInput = document.getElementById('amount');
                        amountInput.value = '';
                        amountInput.disabled = true;
                        amountInput.placeholder = '店休日不需輸入業績金額';
                    }
                    
                    // 特殊處理：如果選擇「已收」或「未收」，啟用金額欄位
                    if (name === 'payment_status' && (value === 'paid' || value === 'unpaid')) {
                        const amountInput = document.getElementById('amount');
                        amountInput.disabled = false;
                        amountInput.placeholder = '輸入業績金額（留空表示無業績）';
                    }
                });
            });
            
            // 顯示訊息函數
            function showMessage(text, type) {
                messageDiv.textContent = text;
                messageDiv.className = 'message ' + type;
                messageDiv.style.display = 'block';
                
                // 自動隱藏錯誤訊息（成功訊息會在關閉視窗前消失）
                if (type === 'error') {
                    setTimeout(() => {
                        messageDiv.style.display = 'none';
                    }, 5000);
                }
            }
            
            // 初始檢查：如果選擇「店休」，禁用金額欄位
            const paymentStatusInput = document.getElementById('payment_status');
            const amountInput = document.getElementById('amount');
            
            if (paymentStatusInput && paymentStatusInput.value === 'dayoff') {
                amountInput.disabled = true;
                amountInput.placeholder = '店休日不需輸入業績金額';
            }
        });
    </script>
</body>
</html>