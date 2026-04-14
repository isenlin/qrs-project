<?php
/**
 * 權限驗證函式庫
 */

require_once __DIR__ . '/settings.php';

/**
 * 檢查使用者是否已登入
 */
function is_logged_in() {
    if (!isset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['role'])) {
        return false;
    }
    
    // 檢查 session 是否過期
    if (isset($_SESSION['login_time'])) {
        // 根據使用者角色設定不同的 timeout
        $user_role = $_SESSION['role'];
        
        if ($user_role === 'store') {
            // 店櫃人員：365天
            $timeout = 365 * 24 * 60 * 60; // 365天
        } else {
            // 其他人員（管理員、業務、督導）：30天
            $timeout = $GLOBALS['config']['session_timeout']; // 30天
        }
        
        if (time() - $_SESSION['login_time'] > $timeout) {
            logout_user();
            return false;
        }
    }
    
    return true;
}

/**
 * 驗證使用者帳號密碼
 */
function authenticate_user($username, $password) {
    $users = load_data('users');
    
    foreach ($users as $user) {
        if ($user['username'] === $username) {
            if (password_verify($password, $user['password_hash'])) {
                // 更新最後登入時間
                $user['last_login'] = date('Y-m-d H:i:s');
                save_data('users', $users);
                
                return $user;
            }
            break;
        }
    }
    
    return false;
}

/**
 * 取得目前使用者資訊
 */
function get_current_session_user() {
    if (!is_logged_in()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'role' => $_SESSION['role'],
        'name' => $_SESSION['name'] ?? '',
        'stores' => $_SESSION['stores'] ?? []
    ];
}

/**
 * 檢查使用者權限
 */
function has_permission($required_role) {
    $user = get_current_session_user();
    if (!$user) {
        return false;
    }
    
    $roles = $GLOBALS['config']['roles'];
    $user_level = $roles[$user['role']]['level'] ?? 0;
    $required_level = $roles[$required_role]['level'] ?? 0;
    
    return $user_level >= $required_level;
}

/**
 * 檢查使用者是否可以存取指定店櫃
 */
function can_access_store($store_code) {
    $user = get_current_session_user();
    if (!$user) {
        return false;
    }
    
    // 管理員和督導可以存取所有店櫃
    if (in_array($user['role'], ['admin', 'supervisor'])) {
        return true;
    }
    
    // 檢查使用者是否有權限存取該店櫃
    return in_array($store_code, $user['stores']) || in_array('all', $user['stores']);
}

/**
 * 登出使用者
 */
function logout_user() {
    $user = get_current_session_user();
    if ($user) {
        log_activity($user['username'], 'logout', '使用者登出');
    }
    
    // 清除 session
    $_SESSION = [];
    
    // 刪除 session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

/**
 * 重新導向到登入頁面
 */
function redirect_to_login() {
    header('Location: ../index.php');
    exit;
}

/**
 * 要求登入
 */
function require_login() {
    if (!is_logged_in()) {
        redirect_to_login();
    }
}

/**
 * 要求特定權限
 */
function require_permission($required_role) {
    require_login();
    
    if (!has_permission($required_role)) {
        http_response_code(403);
        echo '<h1>權限不足</h1>';
        echo '<p>您沒有權限存取此頁面。</p>';
        echo '<p><a href="dashboard.php">返回儀表板</a></p>';
        exit;
    }
}

/**
 * 產生隨機密碼
 */
function generate_random_password($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    $password = '';
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    
    return $password;
}

/**
 * 加密密碼
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => $GLOBALS['config']['password_cost']]);
}