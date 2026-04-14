<?php
/**
 * 取得月累計業績資料 API
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

// 計算每個店櫃的月累計
$result = [
    'success' => true,
    'month' => $month,
    'scope' => $scope,
    'stores_by_code' => [],
    'summary' => [
        'total_stores' => count($filtered_stores),
        'total_amount' => 0,
        'stores_with_data' => 0
    ]
];

foreach ($filtered_stores as $store) {
    $store_code = $store['code'];
    $month_total = 0;
    
    // 計算該店櫃在該月的總業績
    foreach ($sales_summary as $date => $daily_sales) {
        if (isset($daily_sales[$store_code])) {
            $amount = $daily_sales[$store_code]['amount'] ?? 0;
            $month_total += $amount;
        }
    }
    
    $result['stores_by_code'][$store_code] = [
        'amount' => $month_total,
        'store_name' => $store['name']
    ];
    
    if ($month_total > 0) {
        $result['summary']['total_amount'] += $month_total;
        $result['summary']['stores_with_data']++;
    }
}

header('Content-Type: application/json');
echo json_encode($result);
?>