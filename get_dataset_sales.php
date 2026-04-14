<?php
/**
 * 取得每日業績資料 API
 * 支援查詢任意日期的業績資料
 */

// 啟動 Session
session_start();

require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/auth_simple.php';

// 檢查登入
require_login();

// 取得目前使用者
$user = get_current_session_user();

// 只有老闆、管理員、業務、督導可以使用此功能
if (!in_array($user['role'], ['boss', 'admin', 'sales', 'supervisor'])) {
    echo json_encode([
        'success' => false,
        'message' => '權限不足'
    ]);
    exit;
}

// 取得請求的日期（預設為今天）
$request_date = $_GET['date'] ?? date('Y-m-d');

// 驗證日期格式
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $request_date)) {
    echo json_encode([
        'success' => false,
        'message' => '日期格式錯誤'
    ]);
    exit;
}

// 載入店櫃資料
$stores = load_data('stores');

// 根據角色和範圍篩選店櫃（排除已結束的店櫃）
$scope = $_GET['scope'] ?? 'region'; // region 或 all
$user_stores = [];

if ($scope === 'all' || in_array($user['role'], ['boss', 'admin'])) {
    // 全區範圍或老闆/管理員：所有狀態為 active 的店櫃
    foreach ($stores as $store) {
        if (($store['status'] ?? 'active') === 'active') {
            $user_stores[] = $store;
        }
    }
} else {
    // 業務/督導的區域範圍：從 stores.json 讀取負責店櫃（只包含 active 狀態）
    foreach ($stores as $store) {
        if (($store['status'] ?? 'active') === 'active') {
            if ($user['role'] === 'sales' && $store['sales_person'] === $user['id']) {
                $user_stores[] = $store;
            } elseif ($user['role'] === 'supervisor' && $store['supervisor'] === $user['id']) {
                $user_stores[] = $store;
            }
        }
    }
}

// 載入該日期的業績資料
$month = substr($request_date, 0, 7);
$sales_summary = load_monthly_sales($month);
$yesterday_sales = $sales_summary[$request_date] ?? [];

// 只保留使用者負責的店櫃資料
$filtered_sales = [];
foreach ($user_stores as $store) {
    $store_code = $store['code'];
    if (isset($yesterday_sales[$store_code])) {
        $filtered_sales[$store_code] = $yesterday_sales[$store_code];
    } else {
        // 即使沒有業績資料，也應該包含店櫃資訊（顯示為未登打）
        $filtered_sales[$store_code] = null;
    }
}

// 載入使用者資料（用於顯示姓名）
$users = load_data('users');
$user_name_map = [];
foreach ($users as $user_data) {
    $user_name_map[$user_data['id']] = $user_data['name'];
}

// 準備回傳資料
$response_data = [
    'date' => $request_date,
    'stores_count' => count($user_stores),
    'entered_count' => 0, // 將在後面計算
    'total_amount' => 0,
    'substitute_count' => 0,
    'stores_by_code' => []  // 先初始化為陣列，最後再轉為物件
];

// 計算統計資料並建立以店櫃代號為鍵的資料結構
foreach ($user_stores as $store) {
    $store_code = $store['code'];
    $sales_data = $filtered_sales[$store_code] ?? null;
    
    if ($sales_data !== null) {
        $amount = $sales_data['amount'] ?? 0;
        $role = $sales_data['role'] ?? 'main';
        $payment_status = $sales_data['payment_status'] ?? 'unpaid';
        
        // 只計算非店休的業績
        if ($payment_status !== 'dayoff') {
            $response_data['total_amount'] += $amount;
            $response_data['entered_count']++;
        }
        
        if ($role === 'substitute') {
            $response_data['substitute_count']++;
        }
    } else {
        $amount = null;
        $role = 'main';
        $payment_status = 'unpaid';
    }
    
    $response_data['stores_by_code'][$store_code] = [
        'amount' => $amount,
        'role' => $role,
        'payment_status' => $payment_status,
        'store_code' => $store_code,
        'name' => $store['name'],
        'sales_person' => $store['sales_person'] ?? '',
        'sales_person_name' => $user_name_map[$store['sales_person'] ?? ''] ?? '',
        'supervisor' => $store['supervisor'] ?? '',
        'supervisor_name' => $user_name_map[$store['supervisor'] ?? ''] ?? '',
        'formatted_amount' => $amount !== null ? number_format($amount) : '-'
    ];
}

// 為了相容性，先建立 stores 陣列格式
$response_data['stores'] = array_values($response_data['stores_by_code']);

// 將 stores_by_code 陣列轉為物件，確保 JSON 編碼為 {}
if (!empty($response_data['stores_by_code'])) {
    $response_data['stores_by_code'] = (object)$response_data['stores_by_code'];
} else {
    $response_data['stores_by_code'] = (object)[];
}

// 回傳 JSON 格式資料
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'success' => true,
    'message' => '取得昨日業績成功',
    'data' => $response_data,
    'user_role' => $user['role']
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);