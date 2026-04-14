<?php
/**
 * 取得店櫃詳細資訊 API
 * 用於編輯模式顯示店櫃基本資料
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
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => '僅支援 GET 請求'
    ]);
    exit;
}

// 取得店櫃代號
$store_code = $_GET['store_code'] ?? '';

if (empty($store_code)) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => '缺少店櫃代號參數'
    ]);
    exit;
}

try {
    // 載入店櫃資料
    $stores = load_data('stores');
    
    // 尋找指定店櫃
    $store_info = null;
    foreach ($stores as $store) {
        if ($store['code'] === $store_code) {
            $store_info = $store;
            break;
        }
    }
    
    if (!$store_info) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => '找不到指定的店櫃'
        ]);
        exit;
    }
    
    // 載入使用者資料（用於取得業務和督導名稱）
    $users = load_data('users');
    
    // 取得業務名稱
    $sales_person_name = '';
    if (!empty($store_info['sales_person'])) {
        foreach ($users as $user_data) {
            if ($user_data['id'] === $store_info['sales_person']) {
                $sales_person_name = $user_data['name'];
                break;
            }
        }
    }
    
    // 取得督導名稱
    $supervisor_name = '';
    if (!empty($store_info['supervisor'])) {
        foreach ($users as $user_data) {
            if ($user_data['id'] === $store_info['supervisor']) {
                $supervisor_name = $user_data['name'];
                break;
            }
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => [
            'store_code' => $store_info['code'],
            'store_name' => $store_info['name'],
            'sales_person' => $store_info['sales_person'] ?? '',
            'sales_person_name' => $sales_person_name,
            'supervisor' => $store_info['supervisor'] ?? '',
            'supervisor_name' => $supervisor_name,
            'area' => $store_info['area'] ?? '',
            'address' => $store_info['address'] ?? '',
            'phone' => $store_info['phone'] ?? ''
        ]
    ]);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => '取得資料失敗：' . $e->getMessage()
    ]);
}
?>