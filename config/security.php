<?php
/**
 * 安全模組 - IP鎖定機制
 */

require_once __DIR__ . '/settings.php';

// 鎖定資料檔案路徑
define('LOCK_DATA_FILE', DATA_PATH . '/login_locks.json');

/**
 * 初始化鎖定資料
 */
function init_lock_data() {
    if (!file_exists(LOCK_DATA_FILE)) {
        $initial_data = [
            'ip_locks' => [],
            'last_cleanup' => time()
        ];
        file_put_contents(LOCK_DATA_FILE, json_encode($initial_data, JSON_PRETTY_PRINT));
    }
}

/**
 * 載入鎖定資料
 */
function load_lock_data() {
    init_lock_data();
    
    $content = file_get_contents(LOCK_DATA_FILE);
    if ($content === false) {
        error_log("無法讀取鎖定檔案: " . LOCK_DATA_FILE);
        return ['ip_locks' => [], 'last_cleanup' => time()];
    }
    
    $data = json_decode($content, true);
    
    if (!$data || !is_array($data)) {
        error_log("鎖定檔案JSON解析失敗: " . LOCK_DATA_FILE);
        error_log("檔案內容: " . substr($content, 0, 200));
        return ['ip_locks' => [], 'last_cleanup' => time()];
    }
    
    // 確保資料結構完整
    if (!isset($data['ip_locks'])) {
        $data['ip_locks'] = [];
    }
    if (!isset($data['last_cleanup'])) {
        $data['last_cleanup'] = time();
    }
    
    return $data;
}

/**
 * 儲存鎖定資料
 */
function save_lock_data($data) {
    $result = file_put_contents(LOCK_DATA_FILE, json_encode($data, JSON_PRETTY_PRINT));
    
    if ($result === false) {
        error_log("無法寫入鎖定檔案: " . LOCK_DATA_FILE);
        error_log("錯誤: " . error_get_last()['message'] ?? '未知錯誤');
    }
    
    return $result;
}

/**
 * 清理過期的鎖定記錄
 * 
 * 注意：不清除只是鎖定過期的記錄，只清除真正舊的記錄（24小時以上）
 * 這樣可以保留失敗次數統計，但清理長期不活動的記錄
 */
function cleanup_expired_locks(&$lock_data) {
    $current_time = time();
    $cleaned = false;
    
    foreach ($lock_data['ip_locks'] as $ip => $lock_info) {
        // 只清除24小時以上沒有活動的記錄
        $hours_since_last_failure = ($current_time - $lock_info['last_failure']) / 3600;
        
        if ($hours_since_last_failure > 24) {
            // 超過24小時沒有活動，清除記錄
            unset($lock_data['ip_locks'][$ip]);
            $cleaned = true;
            error_log("清理過期IP記錄: {$ip} (最後活動: {$hours_since_last_failure}小時前)");
        }
    }
    
    // 每小時清理一次
    if ($current_time - $lock_data['last_cleanup'] > 3600) {
        $lock_data['last_cleanup'] = $current_time;
        $cleaned = true;
    }
    
    return $cleaned;
}

/**
 * 檢查IP是否被鎖定
 * @param string $ip IP位址
 * @return array [是否鎖定, 剩餘秒數, 失敗次數]
 */
function check_ip_lock($ip) {
    $lock_data = load_lock_data();
    
    // 清理過期記錄
    cleanup_expired_locks($lock_data);
    
    if (!isset($lock_data['ip_locks'][$ip])) {
        return [false, 0, 0];
    }
    
    $lock_info = $lock_data['ip_locks'][$ip];
    $current_time = time();
    
    if ($lock_info['locked_until'] > $current_time) {
        $remaining = $lock_info['locked_until'] - $current_time;
        return [true, $remaining, $lock_info['failed_attempts']];
    } else {
        // 鎖定已過期，但保留失敗記錄（不清除）
        // 只返回未鎖定狀態，失敗次數保持不變
        return [false, 0, $lock_info['failed_attempts']];
    }
}

/**
 * 記錄登入失敗
 * @param string $ip IP位址
 * @param string $username 使用者名稱（可選）
 * @return array [是否鎖定, 鎖定時間秒數, 失敗次數]
 */
function record_login_failure($ip, $username = '') {
    $lock_data = load_lock_data();
    $current_time = time();
    
    // 清理過期記錄
    cleanup_expired_locks($lock_data);
    
    if (!isset($lock_data['ip_locks'][$ip])) {
        // 第一次失敗
        $lock_data['ip_locks'][$ip] = [
            'failed_attempts' => 1,
            'first_failure' => $current_time,
            'last_failure' => $current_time,
            'locked_until' => 0, // 尚未鎖定
            'usernames' => $username ? [$username] : []
        ];
    } else {
        // 增加失敗次數
        $lock_info = &$lock_data['ip_locks'][$ip];
        $lock_info['failed_attempts']++;
        $lock_info['last_failure'] = $current_time;
        
        if ($username && !in_array($username, $lock_info['usernames'])) {
            $lock_info['usernames'][] = $username;
        }
        
        // 檢查是否需要鎖定
        // 規則：每5次失敗增加鎖定時間
        // 5次 → 5分鐘, 10次 → 10分鐘, 15次 → 15分鐘...
        $failure_count = $lock_info['failed_attempts'];
        if ($failure_count % 5 === 0) {
            $lock_minutes = ($failure_count / 5) * 5; // 5, 10, 15...分鐘
            $lock_info['locked_until'] = $current_time + ($lock_minutes * 60);
            
            // 記錄鎖定事件
            error_log("IP {$ip} 因 {$failure_count} 次登入失敗被鎖定 {$lock_minutes} 分鐘");
        }
    }
    
    save_lock_data($lock_data);
    
    // 返回當前狀態
    return check_ip_lock($ip);
}

/**
 * 清除IP鎖定（成功登入時呼叫）
 * @param string $ip IP位址
 */
function clear_ip_lock($ip) {
    $lock_data = load_lock_data();
    
    if (isset($lock_data['ip_locks'][$ip])) {
        // 重置失敗次數，但保留記錄（用於監控）
        $lock_data['ip_locks'][$ip]['failed_attempts'] = 0;
        $lock_data['ip_locks'][$ip]['locked_until'] = 0;
        
        // 如果之前有鎖定，現在解鎖
        error_log("IP {$ip} 因成功登入而解鎖");
        
        save_lock_data($lock_data);
    }
}

/**
 * 取得IP位址
 * 
 * 注意：在開發環境中，IP可能不穩定（localhost可能使用127.0.0.1或::1）
 * 這會導致IP鎖定機制失效，因為每次都被視為新IP
 */
function get_client_ip() {
    // 優先使用REMOTE_ADDR（最穩定）
    if (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
        
        // 如果是IPv6的localhost，轉為IPv4
        if ($ip === '::1') {
            return '127.0.0.1'; // 將IPv6 localhost轉為IPv4
        }
        
        // 如果是IPv4的localhost，直接使用
        if ($ip === '127.0.0.1') {
            return $ip;
        }
        
        // 驗證IP格式
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }
    
    // 備用方案：檢查其他IP標頭
    $ip_keys = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED'
    ];
    
    foreach ($ip_keys as $key) {
        if (isset($_SERVER[$key])) {
            $ip_list = explode(',', $_SERVER[$key]);
            foreach ($ip_list as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    // IPv6 localhost轉IPv4
                    if ($ip === '::1') {
                        return '127.0.0.1';
                    }
                    return $ip;
                }
            }
        }
    }
    
    // 最終備用
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

/**
 * 簡化版IP獲取（開發環境專用）
 * 強制使用穩定IP，解決localhost IP不穩定問題
 */
function get_stable_client_ip() {
    // 如果是開發環境，使用固定IP
    if (isset($_SERVER['SERVER_NAME']) && 
        ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1')) {
        return 'DEV_LOCALHOST_IP'; // 開發環境固定IP
    }
    
    // 生產環境使用正常IP獲取
    return get_client_ip();
}

/**
 * 取得鎖定狀態訊息
 */
function get_lock_message($locked, $remaining_seconds, $failed_attempts) {
    if (!$locked) {
        if ($failed_attempts > 0) {
            return "您已有 {$failed_attempts} 次登入失敗，請小心操作。";
        }
        return '';
    }
    
    $minutes = floor($remaining_seconds / 60);
    $seconds = $remaining_seconds % 60;
    
    return "此IP因多次登入失敗已被鎖定。請等待 {$minutes} 分 {$seconds} 秒後再試。";
}

// 初始化
init_lock_data();
?>