<?php
/**
 * 店櫃管理頁面 - 直接在表格中編輯
 */

// 啟動 Session
session_start();

require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../config/auth_simple.php';

// 需要管理員權限
require_permission('admin');

$user = get_current_session_user();

// 載入店櫃資料（原始為陣列格式）
$stores_raw = load_data('stores');

// 將陣列轉換為以店櫃代號為鍵的關聯陣列
$stores = [];
foreach ($stores_raw as $store) {
    $stores[$store['code']] = $store;
}

// 載入人員資料（用於選擇）
$users_raw = load_data('users');
$users = [];
foreach ($users_raw as $user_data) {
    $users[$user_data['id']] = $user_data;
}

// 取得業務和督導人員列表
$sales_persons = [];
$supervisors = [];
foreach ($users as $user_data) {
    if ($user_data['role'] === 'sales') {
        $sales_persons[$user_data['id']] = $user_data['name'];
    } elseif ($user_data['role'] === 'supervisor') {
        $supervisors[$user_data['id']] = $user_data['name'];
    }
}

// 處理新增/編輯/刪除
$message = '';
$editing_store = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save') {
        $store_data = [
            'code' => trim($_POST['code']),
            'name' => trim($_POST['name']),
            'sales_person' => trim($_POST['sales_person']),
            'supervisor' => trim($_POST['supervisor']),
            'phone' => trim($_POST['phone'] ?? ''),
            'mobile' => trim($_POST['mobile'] ?? ''),
            'status' => $_POST['status'] ?? 'active',
            'created_at' => $_POST['created_at'] ?? date('Y-m-d')
        ];
        
        // 檢查是新增還是編輯
        if (isset($_POST['original_code'])) {
            // 編輯模式
            $original_code = $_POST['original_code'];
            if ($original_code !== $store_data['code']) {
                // 如果店櫃代號改變了，需要刪除舊的，新增新的
                if (isset($stores[$original_code])) {
                    unset($stores[$original_code]);
                }
            }
            $stores[$store_data['code']] = $store_data;
            $message = '✅ 店櫃更新成功';
        } else {
            // 新增模式
            if (isset($stores[$store_data['code']])) {
                $message = '❌ 店櫃代號已存在';
            } else {
                $stores[$store_data['code']] = $store_data;
                $message = '✅ 店櫃新增成功';
            }
        }
        
        if (strpos($message, '✅') !== false) {
            // 儲存前轉換回陣列格式
            $stores_to_save = array_values($stores);
            save_data('stores', $stores_to_save);
            
            // 重新載入資料
            $stores_raw = load_data('stores');
            $stores = [];
            foreach ($stores_raw as $store) {
                $stores[$store['code']] = $store;
            }
        }
    } elseif ($action === 'delete') {
        $store_code = $_POST['store_code'];
        if (isset($stores[$store_code])) {
            unset($stores[$store_code]);
            // 儲存前轉換回陣列格式
            $stores_to_save = array_values($stores);
            save_data('stores', $stores_to_save);
            $message = '✅ 店櫃刪除成功';
            
            // 重新載入資料
            $stores_raw = load_data('stores');
            $stores = [];
            foreach ($stores_raw as $store) {
                $stores[$store['code']] = $store;
            }
        }
    } elseif ($action === 'edit') {
        $store_code = $_POST['store_code'];
        if (isset($stores[$store_code])) {
            $editing_store = $stores[$store_code];
        }
    } elseif ($action === 'cancel_edit') {
        $editing_store = null;
    }
}
?>
<!DOCTYPE html>
<html lang='zh-TW'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>店櫃管理 - 店櫃業績管理系統</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1400px;
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
        
        .header h1 {
            margin: 0;
            color: #333;
        }
        
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
        }
        
        .back-btn:hover {
            background: #5a6268;
        }
        
        .management-section {
            margin: 20px 0;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .store-form {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .store-form input, .store-form select {
            padding: 8px;
            width: 100%;
        }
        .store-table {
            width: 100%;
            border-collapse: collapse;
        }
        .store-table th, .store-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .store-table th {
            background: #f8f9fa;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .btn-edit, .btn-delete {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-edit { background: #ffc107; color: #000; }
        .btn-delete { background: #dc3545; color: white; }
        .status-active { color: #28a745; font-weight: bold; }
        .status-inactive { color: #6c757d; }
        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .editing-row {
            background: #fff3cd !important;
        }
        .editing-form {
            display: contents;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>店櫃管理</h1>
            <a href="../dashboard.php" class="back-btn">返回儀表板</a>
        </div>

        <?php if ($message): ?>
            <div class="alert <?php echo strpos($message, '✅') !== false ? 'alert-success' : 'alert-error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="management-section">
            <div class="form-header">
                <h2><?php echo $editing_store ? '編輯店櫃' : '新增店櫃'; ?></h2>
                <?php if ($editing_store): ?>
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="action" value="cancel_edit">
                        <button type="submit" class="btn">取消編輯</button>
                    </form>
                <?php endif; ?>
            </div>
            
            <form method="post" class="store-form">
                <input type="hidden" name="action" value="save">
                <?php if ($editing_store): ?>
                    <input type="hidden" name="original_code" value="<?php echo htmlspecialchars($editing_store['code']); ?>">
                <?php endif; ?>
                
                <div>
                    <label>店櫃代號 *</label>
                    <input type="text" 
                           name="code" 
                           value="<?php echo $editing_store ? htmlspecialchars($editing_store['code']) : ''; ?>" 
                           required 
                           placeholder="如：277"
                           <?php echo $editing_store ? 'readonly' : ''; ?>>
                </div>
                <div>
                    <label>店櫃名稱 *</label>
                    <input type="text" 
                           name="name" 
                           value="<?php echo $editing_store ? htmlspecialchars($editing_store['name']) : ''; ?>" 
                           required 
                           placeholder="如：277復興店">
                </div>
                <div>
                    <label>業務人員</label>
                    <select name="sales_person">
                        <option value="">請選擇業務人員</option>
                        <?php foreach ($sales_persons as $id => $name): ?>
                        <option value="<?php echo htmlspecialchars($id); ?>" 
                            <?php echo ($editing_store && $editing_store['sales_person'] === $id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($id . ' - ' . $name); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>督導人員</label>
                    <select name="supervisor">
                        <option value="">請選擇督導人員</option>
                        <?php foreach ($supervisors as $id => $name): ?>
                        <option value="<?php echo htmlspecialchars($id); ?>" 
                            <?php echo ($editing_store && $editing_store['supervisor'] === $id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($id . ' - ' . $name); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>電話</label>
                    <input type="text" 
                           name="phone" 
                           value="<?php echo $editing_store ? htmlspecialchars($editing_store['phone'] ?? '') : ''; ?>" 
                           placeholder="市話號碼">
                </div>
                <div>
                    <label>手機</label>
                    <input type="text" 
                           name="mobile" 
                           value="<?php echo $editing_store ? htmlspecialchars($editing_store['mobile'] ?? '') : ''; ?>" 
                           placeholder="手機號碼">
                </div>
                <div>
                    <label>狀態 *</label>
                    <select name="status" required>
                        <option value="active" <?php echo ($editing_store && $editing_store['status'] === 'active') ? 'selected' : ''; ?>>營業中</option>
                        <option value="inactive" <?php echo ($editing_store && $editing_store['status'] === 'inactive') ? 'selected' : ''; ?>>暫停營業</option>
                        <option value="closed" <?php echo ($editing_store && $editing_store['status'] === 'closed') ? 'selected' : ''; ?>>已結束</option>
                    </select>
                </div>
                <div style="grid-column: span 3;">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $editing_store ? '更新店櫃' : '新增店櫃'; ?>
                    </button>
                    <?php if ($editing_store): ?>
                        <button type="button" class="btn" onclick="location.href='manage_stores.php'">取消</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="management-section">
            <h2>店櫃列表</h2>
            <table class="store-table">
                <thead>
                    <tr>
                        <th>序號</th>
                        <th>店櫃代號</th>
                        <th>店櫃名稱</th>
                        <th>業務人員</th>
                        <th>督導人員</th>
                        <th>電話</th>
                        <th>手機</th>
                        <th>狀態</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $index = 1; foreach ($stores as $store_code => $store): ?>
                    <tr class="<?php echo ($editing_store && $editing_store['code'] === $store_code) ? 'editing-row' : ''; ?>">
                        <td><?php echo $index++; ?></td>
                        <td><?php echo htmlspecialchars($store_code); ?></td>
                        <td><?php echo htmlspecialchars($store['name']); ?></td>
                        <td>
                            <?php 
                            $sales_person_id = $store['sales_person'] ?? '';
                            $sales_person_name = $sales_persons[$sales_person_id] ?? $sales_person_id;
                            echo htmlspecialchars($sales_person_name);
                            ?>
                        </td>
                        <td>
                            <?php 
                            $supervisor_id = $store['supervisor'] ?? '';
                            $supervisor_name = $supervisors[$supervisor_id] ?? $supervisor_id;
                            echo htmlspecialchars($supervisor_name);
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($store['phone'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($store['mobile'] ?? ''); ?></td>
                        <td class="status-<?php echo $store['status']; ?>">
                            <?php 
                            $status_names = [
                                'active' => '營業中',
                                'inactive' => '暫停營業',
                                'closed' => '已結束'
                            ];
                            echo $status_names[$store['status']] ?? $store['status'];
                            ?>
                        </td>
                        <td class="action-buttons">
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="store_code" value="<?php echo $store_code; ?>">
                                <button type="submit" class="btn-edit">編輯</button>
                            </form>
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="store_code" value="<?php echo $store_code; ?>">
                                <button type="submit" class="btn-delete" onclick="return confirm('確定要刪除此店櫃嗎？')">刪除</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <footer>
            <p>店櫃管理系統 | 最後更新: <?php echo date('Y-m-d H:i:s'); ?></p>
        </footer>
    </div>
</body>
</html>