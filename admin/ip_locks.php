<?php
/**
 * IP鎖定管理頁面
 */

// 啟動 Session
session_start();

require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../config/auth_simple.php';
require_once __DIR__ . '/../config/security.php';

// 需要管理員或BOSS權限
$user = get_current_session_user();
if (!$user || !in_array($user['role'], ['admin', 'boss'])) {
    header('Location: ../dashboard.php');
    exit;
}

// 處理動作
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'clear_ip' && isset($_POST['ip'])) {
        $ip = trim($_POST['ip']);
        $lock_data = load_lock_data();
        
        if (isset($lock_data['ip_locks'][$ip])) {
            unset($lock_data['ip_locks'][$ip]);
            save_lock_data($lock_data);
            $message = "✅ IP {$ip} 鎖定已清除";
            error_log("管理員 {$user['username']} 清除了 IP {$ip} 的鎖定");
        }
    } elseif ($action === 'clear_all') {
        $lock_data = load_lock_data();
        $count = count($lock_data['ip_locks']);
        $lock_data['ip_locks'] = [];
        save_lock_data($lock_data);
        $message = "✅ 已清除所有 {$count} 個IP鎖定";
        error_log("管理員 {$user['username']} 清除了所有IP鎖定");
    }
}

// 載入鎖定資料
$lock_data = load_lock_data();
$ip_locks = $lock_data['ip_locks'];
$current_time = time();

// 排序：最近失敗的在前
uasort($ip_locks, function($a, $b) {
    return $b['last_failure'] - $a['last_failure'];
});
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IP鎖定管理 - 店櫃業績管理系統</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: "Microsoft JhengHei", sans-serif; 
            background: #f8f9fa; 
            padding: 20px;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: #4a6fa5;
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .header h1 {
            font-size: 22px;
            margin: 0;
        }
        
        .back-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
        }
        
        .back-btn:hover {
            background: #5a6268;
        }
        
        .main-content {
            padding: 25px;
        }
        
        .message {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .message-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .stats {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .stats span {
            font-weight: bold;
            color: #28a745;
        }
        
        .clear-all-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .clear-all-btn:hover {
            background: #c82333;
        }
        
        .ip-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 6px;
            overflow: hidden;
            margin-top: 20px;
        }
        
        .ip-table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
            font-weight: 700;
        }
        
        .ip-table td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .ip-table tr:hover {
            background: #f8f9fa;
        }
        
        .locked {
            color: #dc3545;
            font-weight: bold;
        }
        
        .unlocked {
            color: #28a745;
        }
        
        .action-btn {
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            border: 1px solid #ced4da;
            background: white;
        }
        
        .action-btn:hover {
            background: #f8f9fa;
        }
        
        .action-btn.clear {
            color: #dc3545;
            border-color: #dc3545;
        }
        
        .action-btn.clear:hover {
            background: #dc3545;
            color: white;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            font-size: 16px;
        }
        
        .usernames {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .container {
                border-radius: 0;
            }
            
            .header {
                flex-direction: column;
                text-align: center;
            }
            
            .controls {
                flex-direction: column;
                align-items: stretch;
            }
            
            .ip-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔒 IP鎖定管理</h1>
            <a href="manage_users.php" class="back-btn">返回人員管理</a>
        </div>
        
        <div class="main-content">
            <?php if ($message): ?>
                <div class="message message-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <div class="controls">
                <div class="stats">
                    目前有 <span><?php echo count($ip_locks); ?></span> 個IP記錄，
                    其中 <span><?php echo count(array_filter($ip_locks, function($lock) use ($current_time) { 
                        return $lock['locked_until'] > $current_time; 
                    })); ?></span> 個被鎖定中
                </div>
                
                <?php if (!empty($ip_locks)): ?>
                <form method="POST" onsubmit="return confirm('確定要清除所有IP鎖定記錄嗎？');">
                    <input type="hidden" name="action" value="clear_all">
                    <button type="submit" class="clear-all-btn">清除所有鎖定</button>
                </form>
                <?php endif; ?>
            </div>
            
            <?php if (empty($ip_locks)): ?>
                <div class="no-data">
                    📭 目前沒有任何IP鎖定記錄
                </div>
            <?php else: ?>
                <table class="ip-table">
                    <thead>
                        <tr>
                            <th>IP位址</th>
                            <th>失敗次數</th>
                            <th>第一次失敗</th>
                            <th>最後失敗</th>
                            <th>鎖定狀態</th>
                            <th>嘗試的使用者</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ip_locks as $ip => $lock): ?>
                            <?php 
                            $is_locked = $lock['locked_until'] > $current_time;
                            $remaining = $is_locked ? $lock['locked_until'] - $current_time : 0;
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($ip); ?></strong></td>
                                <td><?php echo $lock['failed_attempts']; ?></td>
                                <td><?php echo date('Y-m-d H:i:s', $lock['first_failure']); ?></td>
                                <td><?php echo date('Y-m-d H:i:s', $lock['last_failure']); ?></td>
                                <td>
                                    <?php if ($is_locked): ?>
                                        <span class="locked">
                                            🔒 鎖定中 (剩餘 <?php echo floor($remaining / 60); ?>分<?php echo $remaining % 60; ?>秒)
                                        </span>
                                    <?php else: ?>
                                        <span class="unlocked">✅ 未鎖定</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($lock['usernames'])): ?>
                                        <?php echo implode(', ', array_map('htmlspecialchars', $lock['usernames'])); ?>
                                    <?php else: ?>
                                        <span style="color: #999;">未知</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('確定要清除此IP的鎖定記錄嗎？');">
                                        <input type="hidden" name="action" value="clear_ip">
                                        <input type="hidden" name="ip" value="<?php echo htmlspecialchars($ip); ?>">
                                        <button type="submit" class="action-btn clear">清除</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            
            <div style="margin-top: 30px; padding-top: 15px; border-top: 1px solid #e9ecef; font-size: 13px; color: #666;">
                <h4>鎖定規則說明：</h4>
                <ul style="margin-top: 10px; padding-left: 20px;">
                    <li>每5次登入失敗觸發鎖定</li>
                    <li>鎖定時間：5次失敗 → 5分鐘，10次失敗 → 10分鐘，依此類推</li>
                    <li>成功登入後，該IP的失敗次數會重置</li>
                    <li>鎖定時間過期後自動解除</li>
                    <li>系統每小時自動清理過期記錄</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>