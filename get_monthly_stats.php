<?php
/**
 * 取得月業績區域統計數據
 * 提供督導、業務區域的即時統計數據
 */

// ✅ 修正1：最優先關閉所有錯誤輸出，避免 Warning/Notice 污染 JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

// 啟動 Session
session_start();

require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/auth_simple.php';

// 檢查登入
require_login();

$user = get_current_session_user();

// 檢查權限：業務、督導、管理員或老闆
if (!in_array($user['role'], ['sales', 'supervisor', 'admin', 'boss'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => '權限不足']);
    exit;
}

// 取得查詢月份
$month = $_GET['month'] ?? date('Y-m');

// 載入資料
try {
    $stores = load_data('stores');
    $users = load_data('users');
    $sales_summary = load_monthly_sales($month);
    
    // 除錯：寫到 error_log 而不是直接輸出
    error_log("Stores loaded: " . count($stores));
    error_log("Users loaded: " . count($users));
    error_log("Sales summary days: " . count($sales_summary));
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => '資料載入失敗',
        'debug' => $e->getMessage()
    ]);
    exit;
}

// 除錯：檢查是否有實際的業績數據
$has_sales_data = false;
foreach ($sales_summary as $date => $daily_sales) {
    if (!empty($daily_sales)) {
        $has_sales_data = true;
        break;
    }
}

if (!$has_sales_data) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'month' => $month,
        'test_data' => true,
        'message' => '使用範例數據（資料庫中無實際業績數據）',
        'data_summary' => [
            'stores_count' => count($stores),
            'users_count' => count($users),
            'sales_days' => count($sales_summary),
            'has_actual_data' => false
        ],
        'supervisors' => [
            ['name' => '林雪玲', 'total' => 5663165, 'avg' => 235965],
            ['name' => '黃淑英', 'total' => 4558138, 'avg' => 207188],
            ['name' => '潘姍昀', 'total' => 4214944, 'avg' => 168597]
        ],
        'sales' => [
            ['name' => '蔡鐘敏', 'total' => 3103286, 'avg' => 238714],
            ['name' => '鍾金穎', 'total' => 3544608, 'avg' => 208506],
            ['name' => '張哲銘', 'total' => 2085810, 'avg' => 173817],
            ['name' => '蔡義祥', 'total' => 2834809, 'avg' => 218062],
            ['name' => '張志文', 'total' => 2867734, 'avg' => 179233]
        ],
        'totals' => [
            'supervisorTotal' => 14436247,
            'supervisorAvg' => 203327,
            'salesTotal' => 14436247,
            'salesAvg' => 203327,
            'overallStoreAvg' => 203327,
            'overallDailyAvg' => 6777
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 建立使用者映射
$user_map = [];
foreach ($users as $user_data) {
    $user_map[$user_data['id']] = $user_data;
}

// 1. 計算督導統計（排除已結束的店櫃）
$supervisor_stats = [];
$supervisor_store_counts = [];

foreach ($stores as $store) {
    // 排除已結束的店櫃
    if (($store['status'] ?? 'active') === 'closed') {
        continue;
    }
    
    // ✅ 修正2：同時相容 supervisor_id 和 supervisor 兩種欄位名稱
    $supervisor_id = $store['supervisor_id'] ?? $store['supervisor'] ?? null;
    
    if (!empty($supervisor_id)) {
        if (!isset($supervisor_stats[$supervisor_id])) {
            $supervisor_stats[$supervisor_id] = 0;
            $supervisor_store_counts[$supervisor_id] = 0;
        }
        
        // ✅ 修正3：同時相容 id 和 code 兩種店櫃識別欄位
        $store_key = $store['id'] ?? $store['code'] ?? null;
        
        $store_total = 0;
        foreach ($sales_summary as $date => $daily_sales) {
            if ($store_key !== null && isset($daily_sales[$store_key])) {
                // ✅ 修正4：相容直接數值或 ['amount'] 兩種格式
                $val = $daily_sales[$store_key];
                $store_total += is_array($val) ? ($val['amount'] ?? 0) : (int)$val;
            }
        }
        
        $supervisor_stats[$supervisor_id] += $store_total;
        $supervisor_store_counts[$supervisor_id]++;
    }
}

// 整理督導統計數據
$supervisor_results = [];
foreach ($supervisor_stats as $supervisor_id => $total) {
    if (isset($user_map[$supervisor_id])) {
        $store_count = $supervisor_store_counts[$supervisor_id];
        $average = $store_count > 0 ? round($total / $store_count) : 0;
        
        $supervisor_results[] = [
            'name' => $user_map[$supervisor_id]['name'],
            'total' => $total,
            'avg' => $average
        ];
    }
}

// 2. 計算業務統計（排除已結束的店櫃）
$sales_stats = [];
$sales_store_counts = [];

foreach ($stores as $store) {
    // 排除已結束的店櫃
    if (($store['status'] ?? 'active') === 'closed') {
        continue;
    }
    
    // ✅ 修正5：同時相容 sales_id 和 sales_person 兩種欄位名稱
    $sales_id = $store['sales_id'] ?? $store['sales_person'] ?? null;
    
    if (!empty($sales_id)) {
        if (!isset($sales_stats[$sales_id])) {
            $sales_stats[$sales_id] = 0;
            $sales_store_counts[$sales_id] = 0;
        }
        
        $store_key = $store['id'] ?? $store['code'] ?? null;
        
        $store_total = 0;
        foreach ($sales_summary as $date => $daily_sales) {
            if ($store_key !== null && isset($daily_sales[$store_key])) {
                $val = $daily_sales[$store_key];
                $store_total += is_array($val) ? ($val['amount'] ?? 0) : (int)$val;
            }
        }
        
        $sales_stats[$sales_id] += $store_total;
        $sales_store_counts[$sales_id]++;
    }
}

// 整理業務統計數據
$sales_results = [];
foreach ($sales_stats as $sales_id => $total) {
    if (isset($user_map[$sales_id])) {
        $store_count = $sales_store_counts[$sales_id];
        $average = $store_count > 0 ? round($total / $store_count) : 0;
        
        $sales_results[] = [
            'name' => $user_map[$sales_id]['name'],
            'total' => $total,
            'avg' => $average
        ];
    }
}

// 3. 計算全區總計（排除已結束的店櫃）
$overall_total = 0;
$active_stores = [];
$days_in_month = date('t', strtotime($month . '-01'));

foreach ($stores as $store) {
    // 排除已結束的店櫃
    if (($store['status'] ?? 'active') === 'closed') {
        continue;
    }
    
    $active_stores[] = $store; // 記錄 active 店櫃
    
    $store_key = $store['id'] ?? $store['code'] ?? null;
    $store_total = 0;
    foreach ($sales_summary as $date => $daily_sales) {
        if ($store_key !== null && isset($daily_sales[$store_key])) {
            $val = $daily_sales[$store_key];
            $store_total += is_array($val) ? ($val['amount'] ?? 0) : (int)$val;
        }
    }
    $overall_total += $store_total;
}

$total_active_stores = count($active_stores);

// 計算平均值（使用 active 店櫃數量）
$overall_store_avg = $total_active_stores > 0 ? round($overall_total / $total_active_stores) : 0;
$overall_daily_avg = $days_in_month > 0 ? round($overall_total / $days_in_month) : 0;

// 計算督導總計
$supervisor_total = array_sum($supervisor_stats);
// 督導總計店平均 = 督導總業績 ÷ active 店櫃數量
$supervisor_avg = $total_active_stores > 0 ? round($supervisor_total / $total_active_stores) : 0;

// 計算業務總計
$sales_total = array_sum($sales_stats);
// 業務總計店平均 = 業務總業績 ÷ active 店櫃數量
$sales_avg = $total_active_stores > 0 ? round($sales_total / $total_active_stores) : 0;

// 返回JSON數據
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'month' => $month,
    'data_summary' => [
        'stores_count' => $total_active_stores,
        'total_stores' => count($stores),
        'closed_stores' => count($stores) - $total_active_stores,
        'users_count' => count($users),
        'sales_days' => count($sales_summary),
        'overall_total' => $overall_total
    ],
    'supervisors' => $supervisor_results,
    'sales' => $sales_results,
    'totals' => [
        'supervisorTotal' => $supervisor_total,
        'supervisorAvg' => $supervisor_avg,
        'salesTotal' => $sales_total,
        'salesAvg' => $sales_avg,
        'overallStoreAvg' => $overall_store_avg,
        'overallDailyAvg' => $overall_daily_avg
    ]
], JSON_UNESCAPED_UNICODE);
?>
