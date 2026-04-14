<?php
/**
 * 系統設定檔
 */

// 錯誤報告設定
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 時區設定
date_default_timezone_set('Asia/Taipei');

// 系統路徑設定
define('ROOT_PATH', dirname(dirname(__FILE__)));
define('DATA_PATH', ROOT_PATH . '/data');
define('LOG_PATH', ROOT_PATH . '/data/logs');
define('CONFIG_PATH', ROOT_PATH . '/config');

// 確保目錄存在
$directories = [
    DATA_PATH,
    DATA_PATH . '/sales',
    DATA_PATH . '/sales/daily',
    LOG_PATH
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

// 系統設定
$config = [
    'system_name' => '店櫃業績管理系統',
    'version' => '1.0.0',
    'session_timeout' => 30 * 24 * 60 * 60, // 30天（一般使用者）
    'boss_session_timeout' => 365 * 24 * 60 * 60, // 365天（老闆）
    'password_cost' => 12, // bcrypt 成本係數
    'default_timezone' => 'Asia/Taipei',
    'date_format' => 'Y-m-d',
    'datetime_format' => 'Y-m-d H:i:s',
    'items_per_page' => 20,
    'export_limit' => 1000,
    
    // 角色權限
    'roles' => [
        'boss' => ['name' => '老闆', 'level' => 120],
        'admin' => ['name' => '系統管理員', 'level' => 100],
        'supervisor' => ['name' => '督導', 'level' => 80],
        'sales' => ['name' => '業務', 'level' => 60],
        'store' => ['name' => '店櫃', 'level' => 40]
    ],
    
    // 檔案路徑
    'data_files' => [
        'users' => DATA_PATH . '/users.json',
        'stores' => DATA_PATH . '/stores.json',
        'sales_summary' => DATA_PATH . '/sales_summary.json',
        'audit_log' => LOG_PATH . '/audit.log',
        'error_log' => LOG_PATH . '/error.log'
    ]
];

// 載入資料檔案
function load_data($file_key) {
    global $config;
    $file_path = $config['data_files'][$file_key];
    
    if (!file_exists($file_path)) {
        return [];
    }
    
    $content = file_get_contents($file_path);
    $data = json_decode($content, true);
    
    return $data ?: [];
}

// 儲存資料檔案
function save_data($file_key, $data) {
    global $config;
    $file_path = $config['data_files'][$file_key];
    
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents($file_path, $json) !== false;
}

// 載入店櫃資料（load_data('stores') 的別名）
function load_stores() {
    return load_data('stores');
}

// 儲存店櫃資料（save_data('stores', $data) 的別名）
function save_stores($data) {
    return save_data('stores', $data);
}

// 記錄錯誤
function log_error($message, $context = []) {
    $log_entry = sprintf(
        "[%s] ERROR: %s %s\n",
        date('Y-m-d H:i:s'),
        $message,
        !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : ''
    );
    
    file_put_contents(LOG_PATH . '/error.log', $log_entry, FILE_APPEND);
}

// ==================== 業績資料按月儲存函數 ====================

/**
 * 取得業績檔案路徑（按月儲存）
 * @param string $month 月份格式：YYYY-MM
 * @return string 檔案路徑
 */
function get_sales_file_path($month) {
    // 確保月份格式正確
    if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
        $month = date('Y-m', strtotime($month));
    }
    
    $year = substr($month, 0, 4);
    $month_num = substr($month, 5, 2);
    
    // 建立目錄結構：data/sales/YYYY/MM/
    $dir_path = DATA_PATH . "/sales/{$year}/{$month_num}";
    if (!file_exists($dir_path)) {
        mkdir($dir_path, 0755, true);
    }
    
    return $dir_path . "/sales.json";
}

/**
 * 載入指定月份的業績資料
 * @param string $month 月份格式：YYYY-MM
 * @return array 業績資料
 */
function load_monthly_sales($month) {
    $file_path = get_sales_file_path($month);
    
    if (!file_exists($file_path)) {
        return [];
    }
    
    $content = file_get_contents($file_path);
    $data = json_decode($content, true);
    
    return $data ?: [];
}

/**
 * 儲存指定月份的業績資料
 * @param string $month 月份格式：YYYY-MM
 * @param array $data 業績資料
 * @return bool 是否成功
 */
function save_monthly_sales($month, $data) {
    $file_path = get_sales_file_path($month);
    
    // 儲存完整的業績資料結構（包含角色、時間戳記和收款狀態）
    $clean_data = [];
    foreach ($data as $date => $daily_sales) {
        $clean_data[$date] = [];
        foreach ($daily_sales as $store_code => $store_data) {
            // 建立完整的資料結構
            $clean_data[$date][$store_code] = [
                'store_code' => $store_code  // 總是使用鍵名作為 store_code
            ];
            
            // 金額處理：只有當金額存在且不是 null 時才儲存
            if (isset($store_data['amount']) && $store_data['amount'] !== null) {
                $clean_data[$date][$store_code]['amount'] = $store_data['amount'];
            }
            
            // 如果有角色資訊，一併儲存
            if (isset($store_data['role'])) {
                $clean_data[$date][$store_code]['role'] = $store_data['role'];
            }
            
            // 如果有時間戳記，一併儲存
            if (isset($store_data['timestamp'])) {
                $clean_data[$date][$store_code]['timestamp'] = $store_data['timestamp'];
            }
            
            // 如果有收款狀態，一併儲存
            if (isset($store_data['payment_status'])) {
                $clean_data[$date][$store_code]['payment_status'] = $store_data['payment_status'];
            }
            
            // 如果有收款確認資訊，一併儲存
            if (isset($store_data['payment_confirmed_by'])) {
                $clean_data[$date][$store_code]['payment_confirmed_by'] = $store_data['payment_confirmed_by'];
            }
            
            if (isset($store_data['payment_confirmed_at'])) {
                $clean_data[$date][$store_code]['payment_confirmed_at'] = $store_data['payment_confirmed_at'];
            }
            
            // 如果有金額修改資訊，一併儲存
            if (isset($store_data['amount_modified_by'])) {
                $clean_data[$date][$store_code]['amount_modified_by'] = $store_data['amount_modified_by'];
            }
            
            if (isset($store_data['amount_modified_at'])) {
                $clean_data[$date][$store_code]['amount_modified_at'] = $store_data['amount_modified_at'];
            }
        }
    }
    
    $json = json_encode($clean_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents($file_path, $json) !== false;
}

/**
 * 儲存單日單店櫃業績
 * @param string $date 日期格式：YYYY-MM-DD
 * @param string $store_code 店櫃代號
 * @param int $amount 業績金額
 * @return bool 是否成功
 */
function save_daily_sales($date, $store_code, $amount) {
    $month = substr($date, 0, 7); // 取得月份：YYYY-MM
    
    // 載入該月份的業績資料
    $monthly_sales = load_monthly_sales($month);
    
    // 更新該日期的業績
    if (!isset($monthly_sales[$date])) {
        $monthly_sales[$date] = [];
    }
    
    // 保留現有的角色和時間戳記（如果有的話）
    $existing_role = $monthly_sales[$date][$store_code]['role'] ?? null;
    $existing_timestamp = $monthly_sales[$date][$store_code]['timestamp'] ?? null;
    
    $monthly_sales[$date][$store_code] = [
        'amount' => (int)$amount,
        'store_code' => $store_code
    ];
    
    // 保留現有的角色和時間戳記
    if ($existing_role) {
        $monthly_sales[$date][$store_code]['role'] = $existing_role;
    }
    
    if ($existing_timestamp) {
        $monthly_sales[$date][$store_code]['timestamp'] = $existing_timestamp;
    }
    
    // 儲存回檔案
    return save_monthly_sales($month, $monthly_sales);
}

/**
 * 儲存包含角色資訊的每日業績
 * @param string $date 日期格式：YYYY-MM-DD
 * @param string $store_code 店櫃代號
 * @param int $amount 業績金額
 * @param string $role 角色：'main'（主櫃）或 'substitute'（代班）
 * @return bool 是否成功
 */
function save_daily_sales_with_role($date, $store_code, $amount, $role) {
    $month = substr($date, 0, 7); // 取得月份：YYYY-MM
    
    // 載入該月份的業績資料
    $monthly_sales = load_monthly_sales($month);
    
    // 更新該日期的業績
    if (!isset($monthly_sales[$date])) {
        $monthly_sales[$date] = [];
    }
    
    $monthly_sales[$date][$store_code] = [
        'amount' => (int)$amount,
        'store_code' => $store_code,
        'role' => $role, // 新增角色欄位
        'timestamp' => time(), // 新增時間戳記
        'payment_status' => 'unpaid' // 新增：預設為未收款狀態
    ];
    
    // 儲存回檔案
    return save_monthly_sales($month, $monthly_sales);
}

/**
 * 載入多個月份的業績資料
 * @param array $months 月份陣列
 * @return array 合併的業績資料
 */
function load_multiple_months_sales($months) {
    $all_sales = [];
    
    foreach ($months as $month) {
        $monthly_sales = load_monthly_sales($month);
        $all_sales = array_merge($all_sales, $monthly_sales);
    }
    
    return $all_sales;
}

/**
 * 取得指定日期範圍的業績資料
 * @param string $start_date 開始日期：YYYY-MM-DD
 * @param string $end_date 結束日期：YYYY-MM-DD
 * @return array 業績資料
 */
function load_date_range_sales($start_date, $end_date) {
    $start_month = substr($start_date, 0, 7);
    $end_month = substr($end_date, 0, 7);
    
    // 產生需要載入的月份列表
    $months = [];
    $current = $start_month;
    
    while ($current <= $end_month) {
        $months[] = $current;
        
        // 下一個月
        $next_month = date('Y-m', strtotime($current . '-01 +1 month'));
        if ($next_month <= $current) {
            break; // 避免無限循環
        }
        $current = $next_month;
    }
    
    // 載入所有相關月份的資料
    $all_sales = load_multiple_months_sales($months);
    
    // 篩選日期範圍
    $filtered_sales = [];
    foreach ($all_sales as $date => $daily_sales) {
        if ($date >= $start_date && $date <= $end_date) {
            $filtered_sales[$date] = $daily_sales;
        }
    }
    
    ksort($filtered_sales); // 按日期排序
    return $filtered_sales;
}

// 記錄活動
function log_activity($username, $action, $details = '') {
    $log_entry = sprintf(
        "[%s] %s - %s: %s\n",
        date('Y-m-d H:i:s'),
        $username,
        $action,
        $details
    );
    
    file_put_contents(LOG_PATH . '/audit.log', $log_entry, FILE_APPEND);
}

// 初始化測試資料（如果檔案不存在）
function initialize_test_data() {
    global $config;
    
    // 檢查使用者檔案是否存在
    if (!file_exists($config['data_files']['users'])) {
        $test_users = [
            [
                'id' => 'U001',
                'username' => 'admin',
                'password_hash' => password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 12]),
                'name' => '系統管理員',
                'role' => 'admin',
                'stores' => ['all'],
                'created_at' => date('Y-m-d'),
                'last_login' => null
            ],
            [
                'id' => 'U002',
                'username' => 'supervisor1',
                'password_hash' => password_hash('super123', PASSWORD_BCRYPT, ['cost' => 12]),
                'name' => '林雪玲',
                'role' => 'supervisor',
                'stores' => ['all'],
                'created_at' => date('Y-m-d'),
                'last_login' => null
            ],
            [
                'id' => 'U003',
                'username' => 'sales1',
                'password_hash' => password_hash('sales123', PASSWORD_BCRYPT, ['cost' => 12]),
                'name' => '林一生',
                'role' => 'sales',
                'stores' => ['277復興', '288林口', '297中正'],
                'created_at' => date('Y-m-d'),
                'last_login' => null
            ],
            [
                'id' => 'U004',
                'username' => 'store1',
                'password_hash' => password_hash('store123', PASSWORD_BCRYPT, ['cost' => 12]),
                'name' => '277復興店',
                'role' => 'store',
                'stores' => ['277復興'],
                'created_at' => date('Y-m-d'),
                'last_login' => null
            ]
        ];
        
        save_data('users', $test_users);
    }
    
    // 檢查店櫃檔案是否存在
    if (!file_exists($config['data_files']['stores'])) {
        $test_stores = [
            [
                'code' => '277復興',
                'name' => '復興店',
                'region' => '2區',
                'sales_person' => '林一生',
                'supervisor' => '林雪玲',
                'status' => 'active',
                'created_at' => date('Y-m-d')
            ],
            [
                'code' => '282自強',
                'name' => '自強店',
                'region' => '5區',
                'sales_person' => '林五生',
                'supervisor' => '黃淑英',
                'status' => 'active',
                'created_at' => date('Y-m-d')
            ],
            [
                'code' => '283蘆洲',
                'name' => '蘆洲店',
                'region' => '6區',
                'sales_person' => '林六生',
                'supervisor' => '潘姍昀',
                'status' => 'active',
                'created_at' => date('Y-m-d')
            ]
        ];
        
        save_data('stores', $test_stores);
    }
}

// 初始化資料
initialize_test_data();

/**
 * 變更使用者密碼（支援雙軌制）
 * @param string $user_id 使用者ID
 * @param string $new_password 新密碼
 * @param string $old_password 舊密碼（用於驗證）
 * @return array 結果陣列
 */
function change_user_password($user_id, $new_password, $old_password = null) {
    // 載入使用者資料
    $users = load_data('users');
    
    // 尋找使用者
    $user_index = -1;
    foreach ($users as $index => $user) {
        if ($user['id'] === $user_id) {
            $user_index = $index;
            break;
        }
    }
    
    if ($user_index === -1) {
        return ['success' => false, 'message' => '使用者不存在'];
    }
    
    $user = $users[$user_index];
    
    // 驗證舊密碼（如果提供）
    if ($old_password !== null) {
        $password_correct = false;
        
        // 檢查加密密碼
        if (isset($user['password_hash']) && password_verify($old_password, $user['password_hash'])) {
            $password_correct = true;
        }
        // 檢查明碼密碼
        elseif (isset($user['password']) && $user['password'] === $old_password) {
            $password_correct = true;
        }
        
        if (!$password_correct) {
            return ['success' => false, 'message' => '舊密碼錯誤'];
        }
    }
    
    // 驗證新密碼長度（至少4碼）
    if (strlen($new_password) < 4) {
        return ['success' => false, 'message' => '新密碼必須至少4個字元'];
    }
    
    // 更新密碼為加密格式
    $users[$user_index]['password_hash'] = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => $GLOBALS['config']['password_cost']]);
    
    // 移除明碼密碼（如果存在）
    if (isset($users[$user_index]['password'])) {
        unset($users[$user_index]['password']);
    }
    
    // 更新密碼版本
    $users[$user_index]['password_version'] = 2;
    $users[$user_index]['password_changed_at'] = date('Y-m-d H:i:s');
    
    // 儲存使用者資料
    if (save_data('users', $users)) {
        return ['success' => true, 'message' => '密碼變更成功'];
    } else {
        return ['success' => false, 'message' => '儲存失敗'];
    }
}

/**
 * 建立加密密碼
 * @param string $password 明碼密碼
 * @return string 加密後的密碼
 */
function create_password_hash($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => $GLOBALS['config']['password_cost']]);
}

/**
 * 驗證密碼
 * @param string $password 輸入的密碼
 * @param string $hash 加密的密碼雜湊
 * @return bool 是否匹配
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}