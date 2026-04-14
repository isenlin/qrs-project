<?php
/**
 * 處理密碼變更請求
 */

// 啟動 Session
session_start();

// 載入設定和驗證函數
require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/auth_simple.php';

// 檢查是否已登入
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => '未登入']);
    exit;
}

// 只接受 POST 請求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '無效的請求方法']);
    exit;
}

// 檢查必要參數
if (!isset($_POST['action']) || $_POST['action'] !== 'change_password') {
    echo json_encode(['success' => false, 'message' => '無效的動作']);
    exit;
}

if (!isset($_POST['old_password']) || !isset($_POST['new_password'])) {
    echo json_encode(['success' => false, 'message' => '缺少必要參數']);
    exit;
}

// 取得目前使用者
$current_user = get_current_session_user();
if (!$current_user) {
    echo json_encode(['success' => false, 'message' => '使用者資訊錯誤']);
    exit;
}

// 取得輸入資料
$old_password = trim($_POST['old_password']);
$new_password = trim($_POST['new_password']);

// 變更密碼
$result = change_user_password($current_user['id'], $new_password, $old_password);

// 記錄操作日誌
if ($result['success']) {
    error_log("使用者 {$current_user['username']} 密碼變更成功");
} else {
    error_log("使用者 {$current_user['username']} 密碼變更失敗: {$result['message']}");
}

// 返回結果
header('Content-Type: application/json');
echo json_encode($result);
?>