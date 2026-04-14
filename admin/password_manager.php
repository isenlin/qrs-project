<?php
/**
 * 撖Ⅳ蝞∠?撌亙 - 甇??銝??? * 蝞∠??∪??剁??身雿輻??蝣潦??蝣潛??? */

// ?? Session
session_start();

require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../config/auth.php';

// ?閬恣?甈?
require_permission('admin');

$user = get_current_session_user();

// 頛雿輻????$users_raw = load_data('users');
$users = [];
foreach ($users_raw as $user_data) {
    $users[$user_data['id']] = $user_data;
}

// ????
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'reset_password') {
        $user_id = $_POST['user_id'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($user_id)) {
            $error = '隢?蝙?刻?;
        } elseif (empty($new_password)) {
            $error = '隢撓?交撖Ⅳ';
        } elseif (strlen($new_password) < 6) {
            $error = '?啣?蝣潸撠?閬?????;
        } elseif ($new_password !== $confirm_password) {
            $error = '?啣?蝣潸?蝣箄?撖Ⅳ銝???;
        } elseif (!isset($users[$user_id])) {
            $error = '雿輻??摮';
        } else {
            // ?湔撖Ⅳ??
            $users[$user_id]['password_hash'] = password_hash($new_password, PASSWORD_DEFAULT);
            
            // 蝘駁?Ⅳ撖Ⅳ甈?嚗????剁?
            unset($users[$user_id]['password']);
            
            // ?脣??湔敺?鞈?
            $users_to_save = array_values($users);
            if (save_data('users', $users_to_save)) {
                $message = "??雿輻??{$users[$user_id]['name']} ??蝣澆歇?身??";
                
                // 閮????亥?
                error_log("蝞∠???{$user['name']} ?身鈭蝙?刻?{$users[$user_id]['name']} ??蝣?);
            } else {
                $error = '撖Ⅳ?身憭望?嚗?蝔??岫';
            }
        }
    } elseif ($action === 'generate_password') {
        // ???冽?撖Ⅳ
        $length = 12;
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $random_password = substr(str_shuffle($chars), 0, $length);
        
        // 撠璈?蝣澆???Session嚗?潸”?桅?憛?        $_SESSION['generated_password'] = $random_password;
    }
}

// 憒?????撖Ⅳ嚗?潸”?桅?憛?$generated_password = $_SESSION['generated_password'] ?? '';
if ($generated_password && $_SERVER['REQUEST_METHOD'] === 'GET') {
    unset($_SESSION['generated_password']);
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>撖Ⅳ蝞∠?撌亙 - 摨?璆剔蜀蝞∠?蝟餌絞</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: "Microsoft JhengHei", sans-serif; 
            background: #f8f9fa;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #007bff;
            padding: 20px;
        }
        
        .header h1 {
            margin: 0;
            color: #333;
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
            padding: 0 20px 20px;
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
        
        .message-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .management-section {
            margin: 20px 0;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        
        .user-form {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .user-form input, .user-form select {
            padding: 10px;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .user-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .user-table th, .user-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        
        .user-table th {
            background: #f8f9fa;
        }
        
        .password-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-hash { background: #28a745; color: white; }
        .status-plain { background: #dc3545; color: white; }
        .status-none { background: #6c757d; color: white; }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary { background: #4a6fa5; color: white; }
        .btn-primary:hover { background: #3a5f95; }
        
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; }
        
        .btn-warning { background: #ffc107; color: #333; }
        .btn-warning:hover { background: #e0a800; }
        
        .password-generator {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
        }
        
        .generated-password {
            font-family: monospace;
            font-size: 18px;
            background: white;
            padding: 10px;
            border: 2px dashed #28a745;
            border-radius: 4px;
            margin: 10px 0;
            text-align: center;
            letter-spacing: 1px;
        }
        
        .security-note {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-top: 20px;
            border-radius: 4px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .full-width {
            grid-column: span 2;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>?? 撖Ⅳ蝞∠?撌亙</h1>
            <a href="../dashboard.php" class="back-btn">餈??銵冽</a>
        </div>
        
        <div class="main-content">
            <?php if ($message): ?>
                <div class="message message-success">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="message message-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <div class="management-section">
                <h2>?身雿輻??蝣?/h2>
                
                <form method="POST" action="" class="user-form">
                    <input type="hidden" name="action" value="reset_password">
                    
                    <div class="form-group">
                        <label>?豢?雿輻??*</label>
                        <select name="user_id" required>
                            <option value="">隢?蝙?刻?/option>
                            <?php foreach ($users as $id => $user_data): ?>
                            <option value="<?php echo htmlspecialchars($id); ?>">
                                <?php echo htmlspecialchars($user_data['name']); ?> 
                                (<?php echo htmlspecialchars($user_data['username']); ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>?啣?蝣?*</label>
                        <input type="text" 
                               name="new_password" 
                               value="<?php echo htmlspecialchars($generated_password); ?>"
                               required 
                               placeholder="?喳?6????
                               minlength="6">
                    </div>
                    
                    <div class="form-group">
                        <label>蝣箄??啣?蝣?*</label>
                        <input type="text" 
                               name="confirm_password" 
                               value="<?php echo htmlspecialchars($generated_password); ?>"
                               required 
                               placeholder="?活頛詨?啣?蝣?
                               minlength="6">
                    </div>
                    
                    <div class="form-group full-width">
                        <button type="submit" class="btn btn-primary">?身撖Ⅳ</button>
                        <button type="submit" name="action" value="generate_password" class="btn btn-warning">?Ｙ??冽?撖Ⅳ</button>
                    </div>
                </form>
                
                <div class="password-generator">
                    <h3>撖Ⅳ?Ｙ???/h3>
                    <p>暺???璈?蝣潦?????銝???函??冽?撖Ⅳ??/p>
                    
                    <?php if ($generated_password): ?>
                    <div class="generated-password">
                        <?php echo htmlspecialchars($generated_password); ?>
                    </div>
                    <p>隢?甇文?蝣潭?靘策雿輻??銝行??蝙?刻?亙?蝡霈撖Ⅳ??/p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="management-section">
                <h2>雿輻??蝣潛???/h2>
                
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>鈭箏隞??</th>
                            <th>?餃撣唾?</th>
                            <th>憪?</th>
                            <th>閫</th>
                            <th>撖Ⅳ???/th>
                            <th>?敺??/th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $id => $user_data): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($id); ?></td>
                            <td><?php echo htmlspecialchars($user_data['username']); ?></td>
                            <td><?php echo htmlspecialchars($user_data['name']); ?></td>
                            <td>
                                <?php 
                                $role_names = [
                                    'admin' => '蝞∠???,
                                    'supervisor' => '???', 
                                    'sales' => '璆剖?',
                                    'store' => '摨?'
                                ];
                                echo $role_names[$user_data['role']] ?? $user_data['role'];
                                ?>
                            </td>
                            <td>
                                <?php if (isset($user_data['password_hash'])): ?>
                                    <span class="password-status status-hash">雿輻撖Ⅳ??</span>
                                <?php elseif (isset($user_data['password'])): ?>
                                    <span class="password-status status-plain">?Ⅳ撖Ⅳ嚗??湔嚗?/span>
                                <?php else: ?>
                                    <span class="password-status status-none">?芾身摰?蝣?/span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($user_data['last_login'] ?? '敺?餃'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="security-note">
                <h3>?? 摰瘜冽?鈭?</h3>
                <ul>
                    <li>???蝣潮?府雿輻撖Ⅳ???脣?嚗??摮?蝣澆?蝣?/li>
                    <li>撱箄降雿輻?????游?蝣潘?瘥?-6??嚗?/li>
                    <li>撖Ⅳ?瑕漲?喳?6????撱箄降雿輻憭批?撖怠?瘥摮??寞?蝚西?蝯?</li>
                    <li>?身撖Ⅳ敺?隢雿輻???喟?乩蒂霈撖Ⅳ</li>
                    <li>?踹?雿輻摰寞????蝣潘?憒??乓閰晞陛?格摮???嚗?/li>
                    <li>蝟餌絞??????蝣潮?閮剜?雿?隢戎??蝞∠恣?撣唾?</li>
                </ul>
            </div>
        </div>
    </div>
    
    <script>
        // ?芸??豢?銵典銝剔?雿輻??憒??ET?嚗?        const urlParams = new URLSearchParams(window.location.search);
        const userId = urlParams.get('user_id');
        if (userId) {
            const select = document.querySelector('select[name="user_id"]');
            if (select) {
                select.value = userId;
            }
        }
        
        // 撖Ⅳ撘瑕漲瑼Ｘ
        document.querySelectorAll('input[name="new_password"], input[name="confirm_password"]').forEach(input => {
            input.addEventListener('input', function() {
                checkPasswordStrength(this.value);
            });
        });
        
        function checkPasswordStrength(password) {
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            // ?臭誑?冽迨瘛餃?撖Ⅳ撘瑕漲閬死??            return strength;
        }
    </script>
</body>
</html>
