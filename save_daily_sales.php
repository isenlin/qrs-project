<?php
/**
 * 儲存每日業績資料 API
 * 僅限管理員和BOSS權限使用
 * 支援 JSON 和傳統表單提交
 */

// 啟動 Session
session_start();

require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/auth_simple.php';

// 檢查登入
require_login();

$user = get_current_session_user();

// 檢查權限：僅管理員和BOSS可以編輯
if (!in_array($user['role'], ['admin', 'boss'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => '權限不足'
    ]);
    exit;
}

// 檢查請求方法
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => '僅支援 POST 請求'
    ]);
    exit;
}

// 根據 Content-Type 解析輸入資料
$input = file_get_contents('php://input');
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

// 預設為表單資料
$date = $_POST['date'] ?? '';
$store_code = $_POST['store_code'] ?? '';
$amount = $_POST['amount'] ?? null;
$role = $_POST['role'] ?? 'main';
$payment_status = $_POST['payment_status'] ?? 'unpaid';

// 如果是 JSON 格式，解析 JSON
if (strpos($contentType, 'application/json') !== false) {
    $jsonData = json_decode($input, true);
    if ($jsonData) {
        $date = $jsonData['date'] ?? $date;
        $store_code = $jsonData['store_code'] ?? $store_code;
        $amount = $jsonData['amount'] ?? $amount;
        $role = $jsonData['role'] ?? $role;
        $payment_status = $jsonData['payment_status'] ?? $payment_status;
    }
}

// 驗證必要欄位
if (empty($date) || empty($store_code)) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => '缺少必要欄位：date 或 store_code'
    ]);
    exit;
}

// 驗證日期格式
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => '日期格式錯誤，應為 YYYY-MM-DD'
    ]);
    exit;
}

// 驗證金額（允許 null 或數字）
if ($amount !== null && !is_numeric($amount)) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => '金額格式錯誤，應為數字'
    ]);
    exit;
}

// 驗證角色
if (!in_array($role, ['main', 'substitute'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => '角色格式錯誤，應為 main 或 substitute'
    ]);
    exit;
}

// 驗證收款狀態
if (!in_array($payment_status, ['paid', 'unpaid', 'dayoff'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => '收款狀態格式錯誤，應為 paid、unpaid 或 dayoff'
    ]);
    exit;
}

try {
    // 取得月份
    $month = substr($date, 0, 7); // 取得月份：YYYY-MM
    
    // 載入銷售資料（按月儲存）
    $sales_data = load_monthly_sales($month);
    
    // 初始化日期資料
    if (!isset($sales_data[$date])) {
        $sales_data[$date] = [];
    }
    
    // 初始化店櫃資料
    if (!isset($sales_data[$date][$store_code])) {
        $sales_data[$date][$store_code] = [];
    }
    
    // 更新資料
    $sales_data[$date][$store_code]['amount'] = $amount !== null ? (float)$amount : null;
    $sales_data[$date][$store_code]['role'] = $role;
    $sales_data[$date][$store_code]['payment_status'] = $payment_status;
    $sales_data[$date][$store_code]['updated_at'] = date('Y-m-d H:i:s');
    $sales_data[$date][$store_code]['updated_by'] = $user['id'];
    
    // 儲存資料（按月儲存）
    save_monthly_sales($month, $sales_data);
    
    // 同時更新 sales_summary.json 以保持一致性
    $summary_data = load_data('sales_summary');
    if (!isset($summary_data[$date])) {
        $summary_data[$date] = [];
    }
    $summary_data[$date][$store_code] = [
        'amount' => $amount !== null ? (float)$amount : null,
        'role' => $role,
        'payment_status' => $payment_status,
        'updated_at' => date('Y-m-d H:i:s'),
        'updated_by' => $user['id']
    ];
    save_data('sales_summary', $summary_data);
    
    // 記錄操作（如果有 log_operation 函數）
    if (function_exists('log_operation')) {
        log_operation($user['id'], 'update_daily_sales', [
            'date' => $date,
            'store_code' => $store_code,
            'amount' => $amount,
            'role' => $role,
            'payment_status' => $payment_status
        ]);
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => '資料儲存成功',
        'data' => [
            'date' => $date,
            'store_code' => $store_code,
            'amount' => $amount,
            'role' => $role,
            'payment_status' => $payment_status
        ]
    ]);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => '儲存失敗：' . $e->getMessage()
    ]);
}
?>