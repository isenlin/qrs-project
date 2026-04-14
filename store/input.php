<?php
/**
 * 店櫃業績登打頁面（修正版）
 * - 店櫃人員只能登打自己店櫃的業績
 * - 管理員可以選擇任何店櫃進行補登
 * - 改變日期時自動載入當天業績
 */

// 啟動 Session
session_start();

require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../config/auth_simple.php';

// 需要店櫃或管理員權限
if (!is_logged_in()) {
    header('Location: ../index.php');
    exit;
}

$user = get_current_session_user();

// 店櫃人員自動重定向到專用頁面
if ($user['role'] === 'store') {
    header('Location: ../store_dashboard.php');
    exit;
}

// 檢查權限：店櫃或管理員
if ($user['role'] !== 'store' && $user['role'] !== 'admin') {
    header('Location: ../dashboard.php');
    exit;
}

// 載入店櫃資料
$stores = load_data('stores');
$sales_summary = load_data('sales_summary');

// 取得查詢參數
$selected_date = $_GET['date'] ?? date('Y-m-d');
$selected_store = $_GET['store'] ?? '';

// 如果是店櫃人員，自動設定為自己的店櫃
if ($user['role'] === 'store') {
    $user_stores = $user['stores'] ?? [];
    if (!empty($user_stores)) {
        $selected_store = $user_stores[0]; // 店櫃人員只有一個店櫃
    }
}

// 如果是老闆或管理員且未選擇店櫃，預設第一個店櫃
if (in_array($user['role'], ['boss', 'admin']) && empty($selected_store) && !empty($stores)) {
    $first_store = reset($stores);
    $selected_store = $first_store['code'];
}

// 載入當天該店櫃的業績資料
$today_sales = $sales_summary[$selected_date][$selected_store] ?? [
    'amount' => 0,
    'items' => 0,
    'customers' => 0,
    'notes' => '',
    'input_time' => '',
    'input_by' => ''
];

// 處理表單提交
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save') {
        $store_code = $_POST['store_code'];
        $date = $_POST['date'];
        $amount = floatval($_POST['amount']);
        $items = intval($_POST['items'] ?? 0);
        $customers = intval($_POST['customers'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');
        
        // 驗證必填欄位
        if (empty($store_code) || empty($date) || $amount <= 0) {
            $message = '❌ 請填寫必填欄位（店櫃、日期、銷售額）';
        } else {
            // 檢查權限
            $can_edit = false;
            if ($user['role'] === 'admin') {
                $can_edit = true; // 管理員可以編輯任何店櫃
            } elseif ($user['role'] === 'store') {
                // 店櫃人員只能編輯自己的店櫃
                $can_edit = in_array($store_code, $user['stores']);
            }
            
            if (!$can_edit) {
                $message = '❌ 您沒有權限編輯此店櫃的業績';
            } else {
                // 儲存業績資料
                if (!isset($sales_summary[$date])) {
                    $sales_summary[$date] = [];
                }
                
                $sales_summary[$date][$store_code] = [
                    'amount' => $amount,
                    'items' => $items,
                    'customers' => $customers,
                    'notes' => $notes,
                    'input_time' => date('Y-m-d H:i:s'),
                    'input_by' => $user['username']
                ];
                
                save_data('sales_summary', $sales_summary);
                
                // 記錄操作日誌
                log_activity($user['username'], 'sales_input', "登打業績: $store_code - $date - NT$ $amount");
                
                $message = '✅ 業績資料已儲存！';
                
                // 重新載入資料
                $today_sales = $sales_summary[$date][$store_code] ?? $today_sales;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang='zh-TW'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>業績登打 - 店櫃業績管理系統</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .input-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.25);
        }
        .required::after {
            content: ' *';
            color: #dc3545;
        }
        .btn-submit {
            width: 100%;
            padding: 15px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-submit:hover {
            background: #218838;
        }
        .date-selector {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .store-info {
            padding: 15px;
            background: #e8f4fd;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .permission-note {
            padding: 10px;
            background: #fff3cd;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
    </style>
    <script>
        // 當日期改變時，自動重新載入頁面
        function changeDate(newDate) {
            const url = new URL(window.location.href);
            url.searchParams.set('date', newDate);
            window.location.href = url.toString();
        }
        
        // 當店櫃改變時（管理員），自動重新載入頁面
        function changeStore(newStore) {
            const url = new URL(window.location.href);
            url.searchParams.set('store', newStore);
            window.location.href = url.toString();
        }
        
        // 自動計算相關欄位
        function calculateFields() {
            const amount = parseFloat(document.getElementById('amount').value) || 0;
            const items = parseInt(document.getElementById('items').value) || 0;
            const customers = parseInt(document.getElementById('customers').value) || 0;
            
            // 計算平均客單價
            if (customers > 0) {
                const avg = amount / customers;
                document.getElementById('avg_per_customer').textContent = 'NT$ ' + avg.toFixed(2);
            } else {
                document.getElementById('avg_per_customer').textContent = 'NT$ 0';
            }
            
            // 計算平均單品價
            if (items > 0) {
                const avg = amount / items;
                document.getElementById('avg_per_item').textContent = 'NT$ ' + avg.toFixed(2);
            } else {
                document.getElementById('avg_per_item').textContent = 'NT$ 0';
            }
        }
        
        // 頁面載入時自動計算
        document.addEventListener('DOMContentLoaded', function() {
            calculateFields();
            
            // 綁定輸入事件
            document.getElementById('amount').addEventListener('input', calculateFields);
            document.getElementById('items').addEventListener('input', calculateFields);
            document.getElementById('customers').addEventListener('input', calculateFields);
        });
    </script>
</head>
<body>
    <div class="container">
        <header>
            <h1>業績登打系統</h1>
            <div class="user-info">
                <span><?php echo htmlspecialchars($user['name']); ?> (<?php echo $GLOBALS['config']['roles'][$user['role']]['name'] ?? $user['role']; ?>)</span>
                <a href="../dashboard.php" class="btn">返回儀表板</a>
                <a href="../logout.php" class="btn btn-logout">登出</a>
            </div>
        </header>

        <div class="input-container">
            <?php if ($message): ?>
                <div class="alert <?php echo strpos($message, '✅') !== false ? 'alert-success' : 'alert-error'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <div class="permission-note">
                <?php if ($user['role'] === 'store'): ?>
                    <strong>店櫃人員權限：</strong> 您只能登打自己店櫃的業績資料。
                <?php else: ?>
                    <strong>管理員權限：</strong> 您可以選擇任何店櫃進行業績登打或補登。
                <?php endif; ?>
            </div>
            
            <div class="date-selector">
                <div style="flex: 1;">
                    <label>選擇日期</label>
                    <input type="date" id="date_picker" value="<?php echo $selected_date; ?>" 
                           onchange="changeDate(this.value)" style="width: 200px;">
                </div>
                
                <?php if ($user['role'] === 'admin'): ?>
                <div style="flex: 2;">
                    <label>選擇店櫃</label>
                    <select onchange="changeStore(this.value)" style="width: 300px;">
                        <option value="">請選擇店櫃</option>
                        <?php foreach ($stores as $store): ?>
                        <option value="<?php echo $store['code']; ?>" 
                                <?php echo $selected_store === $store['code'] ? 'selected' : ''; ?>>
                            <?php echo $store['code']; ?> - <?php echo $store['name']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php else: ?>
                <div style="flex: 2;">
                    <div class="store-info">
                        <strong>負責店櫃：</strong>
                        <?php 
                        if (!empty($selected_store) && isset($stores[$selected_store])) {
                            $store = $stores[$selected_store];
                            echo $store['code'] . ' - ' . $store['name'] . ' (' . $store['region'] . ')';
                        } else {
                            echo '❌ 未設定負責店櫃';
                        }
                        ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($selected_store)): ?>
            <form method="post">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="store_code" value="<?php echo $selected_store; ?>">
                <input type="hidden" name="date" value="<?php echo $selected_date; ?>">
                
                <div class="form-group">
                    <label class="required">銷售額 (NT$)</label>
                    <input type="number" id="amount" name="amount" 
                           value="<?php echo $today_sales['amount']; ?>" 
                           step="0.01" min="0" required placeholder="輸入銷售金額">
                </div>
                
                <div class="form-group">
                    <label>銷售件數</label>
                    <input type="number" id="items" name="items" 
                           value="<?php echo $today_sales['items']; ?>" 
                           min="0" placeholder="輸入銷售件數（選填）">
                </div>
                
                <div class="form-group">
                    <label>來客數</label>
                    <input type="number" id="customers" name="customers" 
                           value="<?php echo $today_sales['customers']; ?>" 
                           min="0" placeholder="輸入來客數（選填）">
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div style="padding: 15px; background: #f8f9fa; border-radius: 5px;">
                        <h4>平均客單價</h4>
                        <p id="avg_per_customer" style="font-size: 24px; font-weight: bold; color: #007bff;">NT$ 0</p>
                    </div>
                    <div style="padding: 15px; background: #f8f9fa; border-radius: 5px;">
                        <h4>平均單品價</h4>
                        <p id="avg_per_item" style="font-size: 24px; font-weight: bold; color: #28a745;">NT$ 0</p>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>備註說明</label>
                    <textarea name="notes" rows="3" placeholder="輸入備註說明（選填）"><?php echo htmlspecialchars($today_sales['notes']); ?></textarea>
                </div>
                
                <div style="margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                    <h4>資料資訊</h4>
                    <p><strong>登打人員：</strong> <?php echo $today_sales['input_by'] ?: '尚未登打'; ?></p>
                    <p><strong>登打時間：</strong> <?php echo $today_sales['input_time'] ?: '尚未登打'; ?></p>
                    <p><strong>最後更新：</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
                </div>
                
                <button type="submit" class="btn-submit">
                    <?php echo $today_sales['amount'] > 0 ? '更新業績資料' : '儲存業績資料'; ?>
                </button>
            </form>
            <?php else: ?>
            <div style="padding: 40px; text-align: center; background: #f8f9fa; border-radius: 5px;">
                <h3>請選擇店櫃</h3>
                <p>請從上方選擇要登打業績的店櫃</p>
            </div>
            <?php endif; ?>
        </div>
        
        <footer>
            <p>業績登打系統 | 最後更新: <?php echo date('Y-m-d H:i:s'); ?></p>
        </footer>
    </div>
</body>
</html>