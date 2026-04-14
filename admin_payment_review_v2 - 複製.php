<?php
/**
 * 管理員收款審核系統 - 優化版
 * 1. 文字調整：已收/未收
 * 2. 位置交換：店休按鈕在前，收款按鈕在後
 * 3. 標題：營業狀態/收款
 */

session_start();
require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/auth_simple.php';

require_login();
$user = get_current_session_user();

if (!in_array($user['role'], ['boss', 'admin'])) {
    header('Location: ../dashboard.php');
    exit;
}

// 取得前十天日期
$date_range = [];
for ($i = 0; $i < 10; $i++) {
    $date_range[] = date('Y-m-d', strtotime("-$i days"));
}

$stores = load_data('stores');
$users = load_data('users');

$sales_persons = [];
foreach ($users as $u) {
    if ($u['role'] === 'sales') $sales_persons[] = ['id' => $u['id'], 'name' => $u['name']];
}

$sales_stores_map = [];
$store_info_map = [];
foreach ($stores as $store) {
    $sid = $store['sales_person'] ?? '';
    if ($sid) {
        if (!isset($sales_stores_map[$sid])) $sales_stores_map[$sid] = [];
        $sales_stores_map[$sid][] = ['code' => $store['code'], 'name' => $store['name']];
    }
    $s_name = '';
    foreach($users as $u) { if($u['id'] == $sid) { $s_name = $u['name']; break; } }
    $store_info_map[$store['code']] = [
        'name' => $store['name'],
        'sales_person' => $sid,
        'sales_person_name' => $s_name
    ];
}

$store_code = '';
$store_display_name = '';
$sales_display_name = '';
$selected_sales_person = '';
$display_sales = [];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['sales_person'])) $selected_sales_person = trim($_POST['sales_person']);
    
    if (isset($_POST['store_code'])) {
        $store_code = strtoupper(trim($_POST['store_code']));
        if (!empty($store_code) && isset($store_info_map[$store_code])) {
            $store_display_name = $store_info_map[$store_code]['name'];
            $sales_display_name = $store_info_map[$store_code]['sales_person_name'];
            $selected_sales_person = $store_info_map[$store_code]['sales_person'];
            
            foreach ($date_range as $date) {
                $month = date('Y-m', strtotime($date));
                $sales_data = load_monthly_sales($month);
                $sale = $sales_data[$date][$store_code] ?? null;
                
                // 重要修正：無論是否有資料，都顯示該日期
                // 如果有資料，使用資料中的值
                // 如果沒有資料，建立空白記錄
                if ($sale) {
                    // 有資料的情況
                    if (!in_array($sale['payment_status'] ?? '', ['paid', 'dayoff'])) {
                        $display_sales[] = [
                            'date' => $date,
                            'amount' => $sale['amount'],
                            'role' => $sale['role'] ?? 'main',
                            'has_data' => true,
                            'payment_status' => $sale['payment_status'] ?? 'unpaid'
                        ];
                    }
                } else {
                    // 沒有資料的情況（店櫃休息或未登打）
                    $display_sales[] = [
                        'date' => $date,
                        'amount' => null,  // 預設為null，顯示空白
                        'role' => 'main',
                        'has_data' => false,
                        'payment_status' => 'unpaid'
                    ];
                }
            }
        }
    }

    if (isset($_POST['save_payments']) && !empty($_POST['store_code'])) {
        $sc = $_POST['store_code'];
        $count = 0;
        
        // 處理收款確認
        if (isset($_POST['selected_payments'])) {
            foreach ($_POST['selected_payments'] as $p_date) {
                $m = date('Y-m', strtotime($p_date));
                $s_data = load_monthly_sales($m);
                
                // 確保該日期的資料存在
                if (!isset($s_data[$p_date])) {
                    $s_data[$p_date] = [];
                }
                if (!isset($s_data[$p_date][$sc])) {
                    $s_data[$p_date][$sc] = [
                        'role' => 'main',
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                }
                
                $amt_key = 'amount_' . str_replace('-', '_', $p_date);
                // 正確處理金額：空白輸入視為 0，但保留原值如果存在
                $amount = 0;
                if (isset($_POST[$amt_key]) && $_POST[$amt_key] !== '') {
                    $amount = (int)$_POST[$amt_key];
                }
                
                $s_data[$p_date][$sc]['payment_status'] = 'paid';
                $s_data[$p_date][$sc]['amount'] = $amount;
                $s_data[$p_date][$sc]['payment_confirmed_by'] = $user['id'];
                $s_data[$p_date][$sc]['payment_confirmed_at'] = date('Y-m-d H:i:s');
                
                if (save_monthly_sales($m, $s_data)) $count++;
            }
        }
        
        // 處理店休確認
        if (isset($_POST['dayoff_payments'])) {
            foreach ($_POST['dayoff_payments'] as $d_date) {
                $m = date('Y-m', strtotime($d_date));
                $s_data = load_monthly_sales($m);
                
                // 確保該日期的資料存在
                if (!isset($s_data[$d_date])) {
                    $s_data[$d_date] = [];
                }
                if (!isset($s_data[$d_date][$sc])) {
                    $s_data[$d_date][$sc] = [
                        'role' => 'main',
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                }
                
                $s_data[$d_date][$sc]['payment_status'] = 'dayoff';
                $s_data[$d_date][$sc]['amount'] = null;  // 店休時金額為空
                $s_data[$d_date][$sc]['payment_confirmed_by'] = $user['id'];
                $s_data[$d_date][$sc]['payment_confirmed_at'] = date('Y-m-d H:i:s');
                
                if (save_monthly_sales($m, $s_data)) $count++;
            }
        }
        
        if ($count > 0) {
            $message = "✅ 已成功更新 {$count} 筆狀態";
            // 重要：儲存後只清空店櫃相關選擇，保留業務選擇
            // 這樣管理員可以繼續選擇同一業務的其他店櫃
            $store_code = ''; 
            $store_display_name = ''; 
            $sales_display_name = '';
            // 注意：不清空 $selected_sales_person，保持業務選擇
            $display_sales = [];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>店櫃收款審核系統</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: "Microsoft JhengHei", sans-serif; background: #f8f9fa; padding: 20px; }
        .container { max-width: 950px; margin: 0 auto; background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: #4a6fa5; color: white; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        .main-content { padding: 25px; }
        .search-section { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e9ecef; }
        .search-section label { display: block; font-size: 20px; font-weight: 600; margin-bottom: 8px; color: #333; }
        select { width: 100%; padding: 15px; border: 2px solid #ced4da; border-radius: 8px; font-size: 18px; }
        .store-input { width: 220px; padding: 10px; border: 2px solid #ced4da; border-radius: 6px; font-size: 26px; text-align: center; font-weight: bold; text-transform: uppercase; }
        .current-store-info { background: #e7f3ff; border: 1px solid #b3d7ff; padding: 15px; border-radius: 8px; margin-top: 15px; display: flex; justify-content: center; align-items: center; gap: 25px; }
        .info-value { font-size: 24px; font-weight: bold; color: #0056b3; }
        input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        input[type=number] { -moz-appearance: textfield; }
        .payment-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .payment-table th, .payment-table td { padding: 15px; border-bottom: 1px solid #e9ecef; font-size: 19px; text-align: center; }
        .amount-input { width: 130px; padding: 10px; font-size: 22px; text-align: right; border: 1px solid #ccc; font-weight: 600; border-radius: 4px; }
        .amount-input:disabled { background-color: #eee; color: #999; cursor: not-allowed; opacity: 0.6; }
        .action-btn { padding: 10px 20px; border-radius: 6px; cursor: pointer; border: 1px solid #ccc; background: #fff; font-size: 17px; transition: 0.2s; min-width: 90px; }
        .confirm-btn.selected { background: #28a745; color: white; border-color: #28a745; }
        .dayoff-btn.is-off { background: #ffc0cb; color: #d00000; border-color: #ffb1bc; font-weight: bold; }
        .btn-primary { background: #4a6fa5; color: white; border: none; padding: 12px 30px; border-radius: 6px; cursor: pointer; font-size: 19px; font-weight: bold; }
        .batch-actions { margin-top: 25px; padding: 20px; background: #f1f3f5; display: flex; justify-content: space-between; align-items: center; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💰 店櫃收款審核</h1>
            <a href="dashboard.php" style="color:white; text-decoration:none; background:rgba(0,0,0,0.2); padding:8px 15px; border-radius:5px;">返回儀表板</a>
        </div>
        <div class="main-content">
            <?php if (!empty($message)): ?>
                <div style="padding:15px; background:#d4edda; color:#155724; border-radius:8px; margin-bottom:15px; font-size:18px; font-weight:bold;"><?= $message ?></div>
            <?php endif; ?>
            <div class="search-section">
                <form method="POST" action="" id="searchForm" onsubmit="showLoading()">
                    <div style="display: flex; gap: 15px; margin-bottom: 25px;">
                        <div style="flex: 1;"><label>👤 選擇業務人員：</label>
                            <select name="sales_person" id="sales_person_select" onchange="updateStoreList(false, null)">
                                <option value="">-- 請選擇業務 --</option>
                                <?php foreach ($sales_persons as $s): ?>
                                    <option value="<?= $s['id'] ?>" <?= $selected_sales_person == $s['id'] ? 'selected' : '' ?>><?= $s['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="flex: 1;"><label>🏪 選擇店櫃名稱：</label>
                            <select name="store_select" id="store_select" onchange="selectStoreFromDropdown()"><option value="">-- 請先選擇業務 --</option></select>
                        </div>
                    </div>
                    <div style="display: flex; gap: 15px; justify-content: center; align-items: center;">
                        <span style="font-size: 20px; color:#666;"></span>
                        <input type="text" name="store_code" id="store_code_input" class="store-input" value="<?= htmlspecialchars($store_code) ?>" autocomplete="off" oninput="updateDropdownsFromInput()" onkeypress="handleEnterKey(event)">
                        <button type="submit" id="submitBtn" class="btn-primary">🔍 查詢</button>
                    </div>
                    <?php if (!empty($store_display_name)): ?>
                    <div class="current-store-info">
                        <div><span style="color:#555;">店櫃：</span><span class="info-value"><?= $store_code ?> - <?= $store_display_name ?></span></div>
                        <div style="border-left:1px solid #ccc; height:30px;"></div>
                        <div><span style="color:#555;">業務：</span><span class="info-value"><?= $sales_display_name ?></span></div>
                    </div>
                    <?php endif; ?>
                </form>
            </div>
            <?php if (!empty($display_sales)): ?>
            <form method="POST" action="">
                <input type="hidden" name="store_code" value="<?= htmlspecialchars($store_code) ?>">
                <table class="payment-table">
                    <thead><tr><th>日期</th><th>金額</th><th>身份</th><th>營業狀態/收款</th></tr></thead>
                    <tbody>
                        <?php foreach ($display_sales as $sale): 
                            $did = str_replace('-', '_', $sale['date']);
                            $has_data = $sale['has_data'] ?? false;
                            $payment_status = $sale['payment_status'] ?? 'unpaid';
                            // 正確處理金額顯示：
                            // 1. 有資料且金額不是 null 時顯示實際金額（包括 0）
                            // 2. 無資料或金額為 null 時顯示空白
                            $amount_value = '';
                            if ($has_data && isset($sale['amount']) && $sale['amount'] !== null) {
                                $amount_value = $sale['amount'];  // 包括 0
                            }
                            $is_dayoff = $payment_status === 'dayoff';
                            $is_paid = $payment_status === 'paid';
                        ?>
                        <tr>
                            <td><?= $sale['date'] ?></td>
                            <td>
                                <input type="number" 
                                       name="amount_<?= $did ?>" 
                                       id="amt_<?= $did ?>" 
                                       class="amount-input" 
                                       value="<?= $amount_value ?>" 
                                       <?= $is_dayoff ? 'disabled' : '' ?>
                                       placeholder="<?= $has_data ? '' : '無資料' ?>">
                            </td>
                            <td><?= $sale['role'] === 'substitute' ? '代班' : '正職' ?></td>
                            <td style="display: flex; gap: 10px; justify-content: center;">
                                <!-- 店休按鈕 -->
                                <button type="button" 
                                        class="action-btn dayoff-btn <?= $is_dayoff ? 'is-off' : '' ?>" 
                                        id="btn_off_<?= $did ?>" 
                                        onclick="toggleDayOff(this, '<?= $sale['date'] ?>')">
                                    <?= $is_dayoff ? '店休' : '營業' ?>
                                </button>
                                <input type="hidden" 
                                       name="dayoff_payments[]" 
                                       id="hid_off_<?= $did ?>" 
                                       value="<?= $sale['date'] ?>" 
                                       <?= $is_dayoff ? '' : 'disabled' ?>>

                                <!-- 收款按鈕 -->
                                <button type="button" 
                                        class="action-btn confirm-btn <?= $is_paid ? 'selected' : '' ?>" 
                                        id="btn_conf_<?= $did ?>" 
                                        onclick="toggleConfirm(this, '<?= $sale['date'] ?>')">
                                    <?= $is_paid ? '✅ 已收' : '🔘 未收' ?>
                                </button>
                                <input type="hidden" 
                                       name="selected_payments[]" 
                                       id="hid_conf_<?= $did ?>" 
                                       value="<?= $sale['date'] ?>" 
                                       <?= $is_paid ? '' : 'disabled' ?>>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="batch-actions">
                    <div style="font-size: 22px;">已處理：<span id="sel_count" style="font-weight:bold; color:#28a745;">0</span> 筆</div>
                    <button type="submit" name="save_payments" class="btn-primary" style="background:#28a745; padding: 15px 50px;">💾 儲存收款與店休</button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const salesStoresMap = <?= json_encode($sales_stores_map) ?>;
        const storeInfoMap = <?= json_encode($store_info_map) ?>;
        let isUpdating = false;

        function showLoading() { const b = document.getElementById('submitBtn'); b.disabled = true; b.innerHTML = '⌛...'; }

        function toggleConfirm(btn, date) {
            const id = date.replace(/-/g, '_');
            const hConf = document.getElementById('hid_conf_' + id);
            const bOff = document.getElementById('btn_off_' + id);
            const hOff = document.getElementById('hid_off_' + id);
            const amt = document.getElementById('amt_' + id);

            // 如果店休已選，先取消店休
            if (!hOff.disabled) {
                bOff.classList.remove('is-off'); 
                bOff.innerHTML = '營業';
                hOff.disabled = true; 
                if(amt) amt.disabled = false;
            }
            
            // 切換收款狀態
            btn.classList.toggle('selected');
            const isSel = btn.classList.contains('selected');
            btn.innerHTML = isSel ? '✅ 已收' : '🔘 未收';
            hConf.disabled = !isSel;
            
            // 如果選擇收款，確保金額輸入框可用
            if (isSel && amt) {
                amt.disabled = false;
            }
            
            updateCount();
        }

        function toggleDayOff(btn, date) {
            const id = date.replace(/-/g, '_');
            const hOff = document.getElementById('hid_off_' + id);
            const bConf = document.getElementById('btn_conf_' + id);
            const hConf = document.getElementById('hid_conf_' + id);
            const amt = document.getElementById('amt_' + id);

            // 如果收款已選，先取消收款
            if (!hConf.disabled) {
                bConf.classList.remove('selected'); 
                bConf.innerHTML = '🔘 未收';
                hConf.disabled = true;
            }
            
            // 切換店休狀態
            btn.classList.toggle('is-off');
            const isOff = btn.classList.contains('is-off');
            
            if (isOff) {
                // 設定為店休
                btn.innerHTML = '店休'; 
                hOff.disabled = false;
                if (amt) { 
                    amt.value = '';  // 清空金額
                    amt.disabled = true;  // 禁用輸入框
                }
            } else {
                // 取消店休
                btn.innerHTML = '營業'; 
                hOff.disabled = true;
                if (amt) amt.disabled = false;  // 啟用輸入框
            }
            
            updateCount();
        }

        function updateCount() {
            const c = document.querySelectorAll('.confirm-btn.selected').length;
            const o = document.querySelectorAll('.dayoff-btn.is-off').length;
            document.getElementById('sel_count').textContent = c + o;
        }

        function updateStoreList(preserve, target) {
            const sp = document.getElementById('sales_person_select').value;
            const ss = document.getElementById('store_select');
            ss.innerHTML = '<option value="">-- 選擇店櫃 --</option>';
            if (sp && salesStoresMap[sp]) {
                salesStoresMap[sp].forEach(s => { ss.add(new Option(`${s.code} - ${s.name}`, s.code)); });
                ss.disabled = false; if (target) ss.value = target;
            } else ss.disabled = true;
        }

        function selectStoreFromDropdown() {
            const v = document.getElementById('store_select').value;
            if (v) { document.getElementById('store_code_input').value = v; showLoading(); document.getElementById('searchForm').submit(); }
        }

        function updateDropdownsFromInput() {
            if (isUpdating) return; isUpdating = true;
            const code = document.getElementById('store_code_input').value.trim().toUpperCase();
            if (code && storeInfoMap[code]) {
                document.getElementById('sales_person_select').value = storeInfoMap[code].sales_person;
                updateStoreList(true, code);
            }
            setTimeout(() => isUpdating = false, 100);
        }

        function handleEnterKey(e) { if (e.key === 'Enter') { e.preventDefault(); if (e.target.value.trim()) { showLoading(); document.getElementById('searchForm').submit(); } } }

        window.onload = () => {
            const input = document.getElementById('store_code_input');
            input.focus(); 
            input.select();
            
            <?php if ($selected_sales_person): ?>
                // 重要：只要業務已選擇，就自動載入該業務的店櫃列表
                updateStoreList(false, null);
            <?php endif; ?>
            
            // 初始化已處理筆數統計
            updateCount();
        };
    </script>
</body>
</html>