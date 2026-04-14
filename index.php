<?php
/**
 * 登入頁面
 */

// 啟動 Session
session_start();

// 載入設定和驗證函數
require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/auth_simple.php';
require_once __DIR__ . '/config/security.php';

// 如果已經登入，重定向到儀表板
if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

// 處理登入表單提交
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    // 取得客戶端IP - 使用穩定版本
    $client_ip = get_stable_client_ip();
    
    // 除錯：記錄IP資訊
    error_log("登入嘗試 - 穩定IP: {$client_ip}, REMOTE_ADDR: " . ($_SERVER['REMOTE_ADDR'] ?? '未設定') . ", SERVER_NAME: " . ($_SERVER['SERVER_NAME'] ?? '未設定'));
    
    // 檢查IP是否被鎖定
    list($is_locked, $remaining_seconds, $failed_attempts) = check_ip_lock($client_ip);
    
    // 除錯：記錄鎖定狀態
    error_log("鎖定檢查 - IP: {$client_ip}, 鎖定: " . ($is_locked ? '是' : '否') . ", 失敗次數: {$failed_attempts}");
    
    if ($is_locked) {
        $minutes = floor($remaining_seconds / 60);
        $seconds = $remaining_seconds % 60;
        $error = "此IP因多次登入失敗已被鎖定。請等待 {$minutes} 分 {$seconds} 秒後再試。";
    } elseif (empty($username) || empty($password)) {
        $error = '請輸入帳號和密碼';
    } else {
        // 驗證使用者
        $user = authenticate_user($username, $password);
        
        if ($user) {
            // 登入成功，清除IP鎖定
            clear_ip_lock($client_ip);
            // 登入成功
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();
            
            // 只有店櫃人員需要 stores 欄位
            if ($user['role'] === 'store') {
                $_SESSION['stores'] = $user['stores'] ?? [];
            } else {
                $_SESSION['stores'] = []; // 業務、督導、管理員不需要 stores 欄位
            }
            
            // 根據角色設定不同的session過期時間
            if ($user['role'] === 'boss') {
                // 老闆：365天session
                $session_lifetime = 365 * 24 * 60 * 60;
            } else {
                // 其他人員：30天session
                $session_lifetime = 30 * 24 * 60 * 60;
            }
            
            // 設定session cookie參數
            session_set_cookie_params($session_lifetime);
            
            // 重新啟動session以應用新的cookie設定
            session_regenerate_id(true);
            
            // 記錄登入日誌
            error_log("使用者 {$user['username']} 登入成功，角色: {$user['role']}，session時間: {$session_lifetime}秒");
            
            // 根據角色重定向
            if ($user['role'] === 'store') {
                // 店櫃人員：直接進入店櫃專用儀表板
                header('Location: store_dashboard.php');
            } else {
                // 其他人員：進入一般儀表板
                header('Location: dashboard.php');
            }
            exit;
        } else {
            $error = '帳號或密碼錯誤';
            error_log("登入失敗：使用者 {$username} 驗證失敗，IP: {$client_ip}");
            
            // 記錄登入失敗
            list($is_locked, $remaining_seconds, $new_failed_attempts) = record_login_failure($client_ip, $username);
            
            // 除錯：記錄失敗記錄結果
            error_log("失敗記錄 - IP: {$client_ip}, 鎖定: " . ($is_locked ? '是' : '否') . ", 新失敗次數: {$new_failed_attempts}");
            
            if ($is_locked) {
                $minutes = floor($remaining_seconds / 60);
                $seconds = $remaining_seconds % 60;
                $error = "帳號或密碼錯誤。此IP因多次登入失敗已被鎖定 {$minutes} 分 {$seconds} 秒。";
                error_log("鎖定觸發 - IP: {$client_ip} 被鎖定 {$minutes} 分 {$seconds} 秒");
            } elseif ($new_failed_attempts > 0) {
                $error = "帳號或密碼錯誤。您已有 {$new_failed_attempts} 次登入失敗，請小心操作。";
            }
            
            // 除錯：檢查檔案實際內容
            $debug_data = load_lock_data();
            $debug_attempts = $debug_data['ip_locks'][$client_ip]['failed_attempts'] ?? 0;
            error_log("檔案驗證 - IP: {$client_ip}, 檔案中的失敗次數: {$debug_attempts}");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>店櫃業績管理系統 - 登入</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }
        
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: bold;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-login:hover {
            background: #5a67d8;
        }
        
        .error {
            background: #fee;
            color: #c33;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .test-accounts {
            margin-top: 30px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .test-accounts h3 {
            margin-top: 0;
            color: #555;
        }
        
        .test-accounts ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        .test-accounts li {
            margin-bottom: 5px;
        }
        
        .store-login {
            background: #e8f4fd;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        
        .store-login h3 {
            margin-top: 0;
            color: #0366d6;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>店櫃業績管理系統</h1>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <label for="username">帳號</label>
                <input type="text" id="username" name="username" required 
                       placeholder="輸入您的帳號" autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">密碼</label>
                <input type="password" id="password" name="password" required 
                       placeholder="輸入您的密碼">
            </div>
            
            <button type="submit" class="btn-login">登入</button>
        </form>
        
        <div class="store-login">
            <h3>登入說明</h3>
            <p>帳號：代號（如：277）</p>
            <p>密碼：公司發放</p>
        </div>
        <div class="test-accounts">
            <ul>
                <li><a href="tech/tech-store.html">系統使用說明-店櫃版</a></li>
            </ul>
        </div>
    </div>
</body>
</html>