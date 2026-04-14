<?php
/**
 * 簡化版使用者驗證（從 users.json 讀取）
 */

require_once __DIR__ . '/settings.php';

/**
 * 簡化版使用者驗證（從 users.json 讀取）
 */
function authenticate_user_simple($username, $password) {
    // 載入使用者資料
    $users = load_data('users');
    
    // 尋找使用者
    $found_user = null;
    foreach ($users as $user) {
        if ($user['username'] === $username) {
            $found_user = $user;
            break;
        }
    }
    
    if (!$found_user) {
        error_log("使用者 {$username} 不存在");
        return false;
    }
    
    // 密碼雙軌制驗證邏輯
    $password_correct = false;
    $needs_upgrade = false;
    
    // 檢查1: 加密密碼 (password_hash)
    if (isset($found_user['password_hash']) && password_verify($password, $found_user['password_hash'])) {
        $password_correct = true;
        error_log("使用者 {$username} 使用加密密碼驗證成功");
    }
    // 檢查2: 明碼密碼 (password) - 舊系統兼容
    elseif (isset($found_user['password']) && $found_user['password'] === $password) {
        $password_correct = true;
        $needs_upgrade = true; // 需要升級到加密格式
        error_log("使用者 {$username} 使用明碼密碼驗證成功，需要升級");
    }
    
    if ($password_correct) {
        $user_data = $found_user;
        
        // 移除密碼相關欄位
        unset($user_data['password']);
        unset($user_data['password_hash']);
        
        // 標記是否需要密碼升級
        if ($needs_upgrade) {
            $user_data['password_needs_upgrade'] = true;
        }
        
        return $user_data;
    }
    
    error_log("使用者 {$username} 密碼錯誤");
    return false;
}

/**
 * 簡化版取得目前使用者
 */
function get_current_session_user_simple() {
    if (!isset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['role'])) {
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
 * 簡化版檢查登入
 */
function is_logged_in_simple() {
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
            return false;
        }
    }
    
    return true;
}

// 為了相容性也提供原始函式別名
function is_logged_in() {
    return is_logged_in_simple();
}

function get_current_session_user() {
    return get_current_session_user_simple();
}

function authenticate_user($username, $password) {
    return authenticate_user_simple($username, $password);
}

/**
 * 要求登入（如果未登入則重定向）
 */
function require_login() {
    if (!is_logged_in()) {
        header('Location: index.php');
        exit;
    }
}

/**
 * 檢查權限（簡化版）
 */
function require_permission($required_role) {
    if (!is_logged_in()) {
        header('Location: index.php');
        exit;
    }
    
    $user = get_current_session_user();
    $roles = $GLOBALS['config']['roles'];
    
    $user_level = $roles[$user['role']]['level'] ?? 0;
    $required_level = $roles[$required_role]['level'] ?? 0;
    
    if ($user_level < $required_level) {
        header('Location: dashboard.php');
        exit;
    }
}

/**
 * 檢查使用者是否可以存取指定店櫃（以人員代號區分）
 */
function can_access_store($store_code) {
    $user = get_current_session_user();
    if (!$user) {
        return false;
    }
    
    // 老闆和管理員可以存取所有店櫃
    if (in_array($user['role'], ['boss', 'admin'])) {
        return true;
    }
    
    // 督導可以存取負責區域的店櫃
    if ($user['role'] === 'supervisor') {
        // 檢查督導是否負責此區域
        return in_array($store_code, $user['stores']) || in_array('all', $user['stores']);
    }
    
    // 業務和店櫃只能存取自己負責的店櫃
    return in_array($store_code, $user['stores']);
}