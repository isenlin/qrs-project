<?php
/**
 * Session Helper - 用於修復Session Cookie問題
 * 
 * 功能：
 * 1. 在每個頁面啟動時更新session cookie的過期時間
 * 2. 確保session cookie在瀏覽器關閉後仍然存在
 * 3. 提供統一的session管理函數
 */

/**
 * 啟動session並確保cookie設定正確
 * 
 * @param bool $require_login 是否要求登入
 * @return array|null 使用者資訊或null
 */
function start_session_with_fix($require_login = true) {
    // 啟動session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // 檢查是否已登入
    if (!isset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['role'])) {
        if ($require_login) {
            // 重定向到登入頁面
            header('Location: ../index.php');
            exit;
        }
        return null;
    }
    
    // 更新session cookie的過期時間（如果session中有設定lifetime）
    if (isset($_SESSION['session_lifetime'])) {
        update_session_cookie($_SESSION['session_lifetime']);
    }
    
    // 檢查session是否過期
    if (isset($_SESSION['login_time'])) {
        $user_role = $_SESSION['role'];
        
        // 根據角色設定不同的timeout
        if ($user_role === 'boss') {
            $timeout = 365 * 24 * 60 * 60; // 365天
        } elseif ($user_role === 'store') {
            $timeout = 365 * 24 * 60 * 60; // 365天
        } else {
            $timeout = 30 * 24 * 60 * 60; // 30天
        }
        
        // 檢查是否過期
        if (time() - $_SESSION['login_time'] > $timeout) {
            // session過期，清除session
            clear_session();
            
            if ($require_login) {
                header('Location: ../index.php');
                exit;
            }
            return null;
        }
    }
    
    // 返回使用者資訊
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'role' => $_SESSION['role'],
        'name' => $_SESSION['name'] ?? '',
        'stores' => $_SESSION['stores'] ?? []
    ];
}

/**
 * 更新session cookie的過期時間
 * 
 * @param int $lifetime 過期時間（秒）
 */
function update_session_cookie($lifetime) {
    // 取得session cookie參數
    $params = session_get_cookie_params();
    
    // 使用setcookie()更新session cookie的過期時間
    setcookie(
        session_name(),      // session cookie名稱
        session_id(),        // session ID
        time() + $lifetime,  // 新的過期時間
        $params['path'],     // cookie路徑
        $params['domain'],   // cookie網域
        $params['secure'],   // 是否僅限HTTPS
        $params['httponly']  // 是否僅限HTTP存取
    );
}

/**
 * 清除session（登出）
 */
function clear_session() {
    // 清除session資料
    $_SESSION = [];
    
    // 刪除session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    
    // 銷毀session
    session_destroy();
}

/**
 * 檢查使用者是否已登入（修復版）
 * 
 * @return bool 是否已登入
 */
function is_logged_in_fixed() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['role'])) {
        return false;
    }
    
    // 更新session cookie（如果session中有設定lifetime）
    if (isset($_SESSION['session_lifetime'])) {
        update_session_cookie($_SESSION['session_lifetime']);
    }
    
    // 檢查session是否過期
    if (isset($_SESSION['login_time'])) {
        $user_role = $_SESSION['role'];
        
        // 根據角色設定不同的timeout
        if ($user_role === 'boss') {
            $timeout = 365 * 24 * 60 * 60; // 365天
        } elseif ($user_role === 'store') {
            $timeout = 365 * 24 * 60 * 60; // 365天
        } else {
            $timeout = 30 * 24 * 60 * 60; // 30天
        }
        
        if (time() - $_SESSION['login_time'] > $timeout) {
            clear_session();
            return false;
        }
    }
    
    return true;
}

/**
 * 取得目前使用者資訊（修復版）
 * 
 * @return array|null 使用者資訊或null
 */
function get_current_user_fixed() {
    if (!is_logged_in_fixed()) {
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
 * 要求登入（修復版）
 */
function require_login_fixed() {
    if (!is_logged_in_fixed()) {
        header('Location: index.php');
        exit;
    }
}

/**
 * 測試session cookie設定
 */
function test_session_cookie() {
    echo "<h2>Session Cookie 測試</h2>";
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>項目</th><th>值</th></tr>";
    
    // Session狀態
    echo "<tr><td>Session狀態</td><td>" . session_status() . "</td></tr>";
    
    // Session ID
    echo "<tr><td>Session ID</td><td>" . session_id() . "</td></tr>";
    
    // Session名稱
    echo "<tr><td>Session名稱</td><td>" . session_name() . "</td></tr>";
    
    // Cookie參數
    $params = session_get_cookie_params();
    echo "<tr><td>Cookie Lifetime</td><td>" . $params['lifetime'] . " 秒</td></tr>";
    echo "<tr><td>Cookie Path</td><td>" . $params['path'] . "</td></tr>";
    echo "<tr><td>Cookie Domain</td><td>" . $params['domain'] . "</td></tr>";
    
    // 檢查是否有session cookie
    $session_name = session_name();
    if (isset($_COOKIE[$session_name])) {
        echo "<tr><td>Session Cookie存在</td><td>是</td></tr>";
        echo "<tr><td>Session Cookie值</td><td>" . substr($_COOKIE[$session_name], 0, 20) . "...</td></tr>";
        
        // 檢查cookie的過期時間
        if (isset($_SESSION['session_lifetime'])) {
            $expires = time() + $_SESSION['session_lifetime'];
            echo "<tr><td>Cookie過期時間</td><td>" . date('Y-m-d H:i:s', $expires) . "</td></tr>";
            echo "<tr><td>剩餘時間</td><td>" . ($_SESSION['session_lifetime'] / (24 * 60 * 60)) . " 天</td></tr>";
        }
    } else {
        echo "<tr><td>Session Cookie存在</td><td>否</td></tr>";
    }
    
    // 顯示session資料
    if (isset($_SESSION['user_id'])) {
        echo "<tr><td>使用者ID</td><td>" . $_SESSION['user_id'] . "</td></tr>";
        echo "<tr><td>使用者名稱</td><td>" . $_SESSION['username'] . "</td></tr>";
        echo "<tr><td>使用者角色</td><td>" . $_SESSION['role'] . "</td></tr>";
        echo "<tr><td>登入時間</td><td>" . date('Y-m-d H:i:s', $_SESSION['login_time']) . "</td></tr>";
        
        if (isset($_SESSION['session_lifetime'])) {
            echo "<tr><td>Session Lifetime</td><td>" . $_SESSION['session_lifetime'] . " 秒 (" . ($_SESSION['session_lifetime'] / (24 * 60 * 60)) . " 天)</td></tr>";
        }
    }
    
    echo "</table>";
}

// 為了相容性，也提供原始函式的別名
if (!function_exists('is_logged_in')) {
    function is_logged_in() {
        return is_logged_in_fixed();
    }
}

if (!function_exists('get_current_session_user')) {
    function get_current_session_user() {
        return get_current_user_fixed();
    }
}

if (!function_exists('require_login')) {
    function require_login() {
        require_login_fixed();
    }
}