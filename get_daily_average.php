<?php
/**
 * 取得日均業績資料 API
 * 支援區域範圍和全區範圍
 */

session_start();
require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/auth_simple.php';

// 檢查登入
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => '未登入']);
    exit;
}

$user = get_current_session_user();
$month = $_GET['month'] ?? date('Y-m');
$scope = $_GET['scope'] ?? 'region'; // region 或 all

// 檢查權限：只有老闆、管理員、業務、督導可以使用此功能
if (!in_array($user['role'], ['boss', 'admin', 'sales', 'supervisor'])) {
    echo json_encode(['success' => false, 'message' => '權限不足']);
    exit;
}

// 載入店櫃資料
$stores = load_data('stores');

// 根據範圍篩選店櫃
if ($scope === 'region' && in_array($user['role'], ['sales', 'supervisor'])) {
    // 業務/督導：只顯示負責店櫃
    $filtered_stores = [];
    foreach ($stores as $store) {
        if ($user['role'] === 'sales' && $store['sales_person'] === $user['id']) {
            $filtered_stores[] = $store;
        } elseif ($user['role'] === 'supervisor' && $store['supervisor'] === $user['id']) {
            $filtered_stores[] = $store;
        }
    }
} else {
    // 老闆/管理員或全區範圍：顯示所有店櫃
    $filtered_stores = $stores;
}

// 載入該月的銷售資料
$sales_summary = load_monthly_sales($month);

// 計算本月已過天數
$current_day = date('j');
$days_in_month = date('t', strtotime($month . '-01'));

// 計算每個店櫃的日均業績
$result = [
    'success' => true,
    'month' => $month,
    'scope' => $scope,
    'days_counted' => $current_day,
    'stores_by_code' => [],
    'summary' => [
        'total_stores' => count($filtered_stores),
        'total_daily_avg' => 0,
        'stores_with_data' => 0
    ]
];

foreach ($filtered_stores as $store) {
    $store_code = $store['code'];
    $month_total = 0;
    $days_with_data = 0;
    
    // 計算該店櫃在該月的總業績和有資料的天數
    foreach ($sales_summary as $date => $daily_sales) {
        if (strpos($date, $month) === 0) { // 確保是該月的資料
            if (isset($daily_sales[$store_code])) {
                $amount = $daily_sales[$store_code]['amount'] ?? 0;
                $month_total += $amount;
                $days_with_data++;
            }
        }
    }
    
    // 計算日均（如果有資料）
    $daily_avg = 0;
    if ($days_with_data > 0) {
        $daily_avg = round($month_total / $days_with_data);
    } elseif ($current_day > 0) {
        // 如果本月還沒有資料，使用0作為日均
        $daily_avg = 0;
    }
    
    $result['stores_by_code'][$store_code] = [
        'amount' => $daily_avg,
        'store_name' => $store['name'],
        'month_total' => $month_total,
        'days_with_data' => $days_with_data
    ];
    
    if ($daily_avg > 0) {
        $result['summary']['total_daily_avg'] += $daily_avg;
        $result['summary']['stores_with_data']++;
    }
}

header('Content-Type: application/json');
echo json_encode($result);
?>