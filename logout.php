<?php
/**
 * 店櫃業績管理系統 - 登出頁面
 */

require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/auth.php';

// 登出使用者
logout_user();

// 導向到登入頁面
header('Location: index.php');
exit;