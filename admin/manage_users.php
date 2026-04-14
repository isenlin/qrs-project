<?php
/**
 * 人員管理頁面 - 合併新增/編輯表單
 */

// 啟動 Session
session_start();

require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../config/auth_simple.php';

// 需要管理員權限
require_permission('admin');

$user = get_current_session_user();

// 載入人員資料（原始為陣列格式）
$users_raw = load_data('users');

// 將陣列轉換為以人員代號為鍵的關聯陣列
$users = [];
foreach ($users_raw as $user_data) {
    $users[$user_data['id']] = $user_data;
}

// 載入店櫃資料用於自動計算
$stores_raw = load_data('stores');
$stores = [];
foreach ($stores_raw as $store) {
    $stores[$store['code']] = $store;
}

// 處理新增/編輯/刪除
$message = '';
// 從URL參數獲取訊息（用於驗證錯誤）
if (isset($_GET['message'])) {
    $message = urldecode($_GET['message']);
}
$edit_mode = false;
$editing_user = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save') {
        $user_data = [
            'id' => trim($_POST['id']),
            'username' => trim($_POST['username']),
            'name' => trim($_POST['name']),
            'role' => $_POST['role'],
            'password' => $_POST['password'] // 注意：這裡應該加密
        ];
        
        // 根據角色設定負責店櫃
        // 注意：業務和督導的負責店櫃現在從 stores.json 讀取，不儲存在 users.json
        if ($_POST['role'] === 'store') {
            // 店櫃：店櫃代號就是人員代號
            $user_data['stores'] = [$user_data['id']];
        } else {
            // 管理員、業務、督導：不需要儲存負責店櫃
            // 注意：不設定 stores 欄位，而不是設為空陣列
            unset($user_data['stores']);
        }
        
        // 密碼驗證：必須至少4碼
        $is_edit_mode = isset($_POST['original_id']);
        
        if (!empty($user_data['password'])) {
            // 如果有輸入密碼，檢查長度
            if (strlen($user_data['password']) < 4) {
                $message = '❌ 密碼必須至少4碼！';
                // 重新載入頁面但不儲存
                header('Location: manage_users.php?message=' . urlencode($message));
                exit;
            }
            // 密碼處理：使用加密格式
            $user_data['password_hash'] = password_hash($user_data['password'], PASSWORD_BCRYPT, ['cost' => $GLOBALS['config']['password_cost']]);
            $user_data['password_version'] = 2;
            $user_data['password_changed_at'] = date('Y-m-d H:i:s');
            // 移除明碼密碼欄位
            unset($user_data['password']);
        } elseif ($is_edit_mode && isset($_POST['original_id']) && isset($users[$_POST['original_id']])) {
            // 編輯時保留原密碼（支援雙軌制）
            $existing_user = $users[$_POST['original_id']];
            
            // 保留加密密碼
            if (isset($existing_user['password_hash'])) {
                $user_data['password_hash'] = $existing_user['password_hash'];
                $user_data['password_version'] = $existing_user['password_version'] ?? 1;
            }
            
            // 保留明碼密碼（如果存在，用於舊系統兼容）
            if (isset($existing_user['password'])) {
                $user_data['password'] = $existing_user['password'];
            }
            
            // 保留密碼變更時間
            if (isset($existing_user['password_changed_at'])) {
                $user_data['password_changed_at'] = $existing_user['password_changed_at'];
            }
        } elseif (!$is_edit_mode) {
            // 新增模式但沒有輸入密碼
            $message = '❌ 新增人員必須輸入密碼（至少4碼）！';
            header('Location: manage_users.php?message=' . urlencode($message));
            exit;
        }
        
        // 檢查是新增還是編輯
        if (isset($_POST['original_id'])) {
            // 編輯模式
            $original_id = $_POST['original_id'];
            if ($original_id !== $user_data['id']) {
                // 如果人員代號改變了，需要刪除舊的，新增新的
                if (isset($users[$original_id])) {
                    unset($users[$original_id]);
                }
            }
            $users[$user_data['id']] = $user_data;
            $message = '✅ 人員更新成功';
        } else {
            // 新增模式
            if (isset($users[$user_data['id']])) {
                $message = '❌ 使用者ID已存在';
            } else {
                $users[$user_data['id']] = $user_data;
                $message = '✅ 人員新增成功';
            }
        }
        
        if (strpos($message, '✅') !== false) {
            // 儲存前轉換回陣列格式
            $users_to_save = array_values($users);
            save_data('users', $users_to_save);
            
            // 重新載入資料
            $users_raw = load_data('users');
            $users = [];
            foreach ($users_raw as $user_data) {
                $users[$user_data['id']] = $user_data;
            }
        }
    } elseif ($action === 'delete') {
        $user_id = $_POST['user_id'];
        if (isset($users[$user_id])) {
            unset($users[$user_id]);
            // 儲存前轉換回陣列格式
            $users_to_save = array_values($users);
            save_data('users', $users_to_save);
            $message = '✅ 人員刪除成功';
            
            // 重新載入資料
            $users_raw = load_data('users');
            $users = [];
            foreach ($users_raw as $user_data) {
                $users[$user_data['id']] = $user_data;
            }
        }
    } elseif ($action === 'edit') {
        $user_id = $_POST['user_id'];
        if (isset($users[$user_id])) {
            $edit_mode = true;
            $editing_user = $users[$user_id];
        }
    } elseif ($action === 'cancel_edit') {
        $edit_mode = false;
        $editing_user = null;
    }
}
?>
<!DOCTYPE html>
<html lang='zh-TW'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>人員管理 - 店櫃業績管理系統</title>
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
        .user-form {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .user-form input, .user-form select {
            padding: 8px;
            width: 100%;
        }
        .user-table {
            width: 100%;
            border-collapse: collapse;
        }
        .user-table th, .user-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .user-table th {
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
        .role-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        .role-admin { background: #dc3545; color: white; }
        .role-supervisor { background: #fd7e14; color: white; }
        .role-sales { background: #007bff; color: white; }
        .role-store { background: #28a745; color: white; }
        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>人員管理</h1>
            <a href="../dashboard.php" class="back-btn">返回儀表板</a>
        </div>

        <?php if ($message): ?>
            <div class="alert <?php echo strpos($message, '✅') !== false ? 'alert-success' : 'alert-error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="management-section">
            <div class="form-header">
                <h2><?php echo $edit_mode ? '編輯人員' : '新增人員'; ?></h2>
                <?php if ($edit_mode): ?>
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="action" value="cancel_edit">
                        <button type="submit" class="btn">取消編輯</button>
                    </form>
                <?php endif; ?>
            </div>
            
            <form method="post" class="user-form">
                <input type="hidden" name="action" value="save">
                <?php if ($edit_mode && $editing_user): ?>
                    <input type="hidden" name="original_id" value="<?php echo htmlspecialchars($editing_user['id']); ?>">
                <?php endif; ?>
                
                <div>
                    <label>人員代號 *</label>
                    <input type="text" 
                           name="id" 
                           value="<?php echo $edit_mode ? htmlspecialchars($editing_user['id']) : ''; ?>" 
                           required 
                           placeholder="如：U001"
                           <?php echo $edit_mode ? 'readonly' : ''; ?>>
                </div>
                <div>
                    <label>登入帳號 *</label>
                    <input type="text" 
                           name="username" 
                           value="<?php echo $edit_mode ? htmlspecialchars($editing_user['username']) : ''; ?>" 
                           required 
                           placeholder="如：admin">
                </div>
                <div>
                    <label>姓名 *</label>
                    <input type="text" 
                           name="name" 
                           value="<?php echo $edit_mode ? htmlspecialchars($editing_user['name']) : ''; ?>" 
                           required 
                           placeholder="如：張三">
                </div>
                <div>
                    <label>密碼 <?php echo $edit_mode ? '（留空不變更）' : '*'; ?></label>
                    <input type="password" 
                           name="password" 
                           <?php echo $edit_mode ? '' : 'required'; ?> 
                           minlength="4"
                           placeholder="<?php echo $edit_mode ? '留空則不變更密碼，輸入則需至少4碼' : '輸入密碼（至少4碼）'; ?>">
                </div>
                <div>
                    <label>角色 *</label>
                    <select name="role" required>
                        <option value="">選擇角色</option>
                        <option value="boss" <?php echo ($edit_mode && $editing_user['role'] === 'boss') ? 'selected' : ''; ?>>老闆</option>
                        <option value="admin" <?php echo ($edit_mode && $editing_user['role'] === 'admin') ? 'selected' : ''; ?>>系統管理員</option>
                        <option value="supervisor" <?php echo ($edit_mode && $editing_user['role'] === 'supervisor') ? 'selected' : ''; ?>>督導</option>
                        <option value="sales" <?php echo ($edit_mode && $editing_user['role'] === 'sales') ? 'selected' : ''; ?>>業務</option>
                        <option value="store" <?php echo ($edit_mode && $editing_user['role'] === 'store') ? 'selected' : ''; ?>>店櫃</option>
                    </select>
                </div>
                <div style="grid-column: span 2;">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $edit_mode ? '更新人員' : '新增人員'; ?>
                    </button>
                    <?php if ($edit_mode): ?>
                        <button type="button" class="btn" onclick="location.href='manage_users.php'">取消</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="management-section">
            <h2>人員列表</h2>
            <table class="user-table">
                <thead>
                    <tr>
                        <th>人員代號</th>
                        <th>登入帳號</th>
                        <th>姓名</th>
                        <th>角色</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $id => $user_data): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($id); ?></td>
                        <td><?php echo htmlspecialchars($user_data['username']); ?></td>
                        <td><?php echo htmlspecialchars($user_data['name']); ?></td>
                        <td>
                            <span class="role-badge role-<?php echo $user_data['role']; ?>">
                                <?php 
                                $role_names = [
                                    'admin' => '管理員',
                                    'supervisor' => '督導', 
                                    'sales' => '業務',
                                    'store' => '店櫃'
                                ];
                                echo $role_names[$user_data['role']] ?? $user_data['role'];
                                ?>
                            </span>
                        </td>
                        <td class="action-buttons">
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="user_id" value="<?php echo $id; ?>">
                                <button type="submit" class="btn-edit">編輯</button>
                            </form>
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="user_id" value="<?php echo $id; ?>">
                                <button type="submit" class="btn-delete" onclick="return confirm('確定要刪除此人員嗎？')">刪除</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <footer>
            <p>人員管理系統 | 最後更新: <?php echo date('Y-m-d H:i:s'); ?></p>
        </footer>
    </div>
    
    <script>
        // 表單提交驗證
        document.querySelector('.user-form').addEventListener('submit', function(event) {
            const passwordInput = document.querySelector('input[name="password"]');
            const passwordValue = passwordInput.value.trim();
            const isEditMode = <?php echo $edit_mode ? 'true' : 'false'; ?>;
            
            // 編輯模式下，如果輸入密碼則必須至少4碼
            if (isEditMode && passwordValue !== '' && passwordValue.length < 4) {
                event.preventDefault();
                alert('密碼必須至少4碼！');
                passwordInput.focus();
                return false;
            }
            
            // 新增模式下，密碼必須至少4碼
            if (!isEditMode && passwordValue.length < 4) {
                event.preventDefault();
                alert('密碼必須至少4碼！');
                passwordInput.focus();
                return false;
            }
            
            return true;
        });
    </script>
</body>
</html>