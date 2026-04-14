<?php
/**
 * 瑼Ｚ???蝣澆極?? */

// 摰瑼Ｘ嚗?迂瑼Ｚ??孵?瑼?
$allowed_files = [
    'sales/monthly_report.php',
    'dashboard.php',
    'store_dashboard.php',
    'config/settings.php'
];

$file = $_GET['file'] ?? '';

if (!in_array($file, $allowed_files)) {
    die('銝?閮望炎閬迨瑼?');
}

$file_path = __DIR__ . '/' . $file;

if (!file_exists($file_path)) {
    die('瑼?銝??? ' . htmlspecialchars($file));
}

$content = file_get_contents($file_path);
if ($content === false) {
    die('?⊥?霈??獢?);
}

// ??瑼?鞈?
$file_size = filesize($file_path);
$file_mtime = date('Y-m-d H:i:s', filemtime($file_path));
$line_count = count(file($file_path));
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>瑼Ｚ???蝣? <?php echo htmlspecialchars($file); ?></title>
    <style>
        body { font-family: monospace; margin: 0; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
        .container { max-width: 1200px; margin: 0 auto; }
        
        .header {
            background: #252526;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #007acc;
        }
        
        .header h1 {
            margin: 0 0 10px 0;
            color: #fff;
            font-size: 18px;
        }
        
        .file-info {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            font-size: 14px;
            color: #888;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .source-code {
            background: #1e1e1e;
            border-radius: 5px;
            overflow: auto;
            max-height: 80vh;
            border: 1px solid #333;
        }
        
        .line-numbers {
            background: #252526;
            color: #858585;
            text-align: right;
            padding-right: 10px;
            border-right: 1px solid #333;
            user-select: none;
        }
        
        .code-content {
            padding: 10px;
            white-space: pre;
            tab-size: 4;
        }
        
        .highlight {
            background: rgba(255, 255, 0, 0.1);
            border-left: 3px solid #ffd700;
        }
        
        .keyword { color: #569cd6; }
        .string { color: #ce9178; }
        .comment { color: #6a9955; }
        .function { color: #dcdcaa; }
        .variable { color: #9cdcfe; }
        .number { color: #b5cea8; }
        
        .actions {
            margin-top: 20px;
            text-align: center;
        }
        
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background: #007acc;
            color: white;
            text-decoration: none;
            border-radius: 3px;
            margin: 0 5px;
            border: none;
            cursor: pointer;
            font-family: sans-serif;
            font-size: 14px;
        }
        
        .btn:hover {
            background: #005a9e;
        }
        
        .btn-close {
            background: #555;
        }
        
        .btn-close:hover {
            background: #333;
        }
        
        /* ??擃漁 */
        .search-highlight {
            background: rgba(255, 255, 0, 0.3);
            border-radius: 2px;
            padding: 0 2px;
        }
        
        /* ?箏?靽格迤?賊?璅?? */
        .fix-css { background: rgba(0, 122, 204, 0.1); border-left: 3px solid #007acc; }
        .fix-js { background: rgba(86, 156, 214, 0.1); border-left: 3px solid #569cd6; }
        .fix-html { background: rgba(206, 145, 120, 0.1); border-left: 3px solid #ce9178; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>?? 瑼Ｚ???蝣? <?php echo htmlspecialchars($file); ?></h1>
            <div class="file-info">
                <div class="info-item">?? 憭批?: <?php echo number_format($file_size); ?> 雿?蝯?/div>
                <div class="info-item">?? 靽格??: <?php echo htmlspecialchars($file_mtime); ?></div>
                <div class="info-item">?? 銵: <?php echo number_format($line_count); ?> 銵?/div>
                <div class="info-item">? 頝臬?: <?php echo htmlspecialchars($file_path); ?></div>
            </div>
        </div>
        
        <div class="source-code">
            <table width="100%" cellspacing="0" cellpadding="0">
                <tr>
                    <td width="50" valign="top" class="line-numbers">
                        <?php
                        $lines = explode("\n", $content);
                        for ($i = 1; $i <= count($lines); $i++) {
                            echo '<div id="L' . $i . '">' . $i . '</div>';
                        }
                        ?>
                    </td>
                    <td valign="top" class="code-content">
                        <?php
                        // 蝪∪??瘜?鈭?                        foreach ($lines as $i => $line) {
                            $line_num = $i + 1;
                            $line_class = '';
                            
                            // 瑼Ｘ?臬?箏摰耨甇???撘Ⅳ
                            if (preg_match('/\.monthly-table\.js-fixed/', $line)) {
                                $line_class = 'fix-css';
                            } elseif (preg_match('/?餉??璈銵冽?箏??寞?/', $line)) {
                                $line_class = 'fix-js';
                            } elseif (preg_match('/monthlyTable\.classList\.add\(\'js-fixed\'\)/', $line)) {
                                $line_class = 'fix-js';
                            } elseif (preg_match('/tableContainer\.addEventListener\(\'scroll\'/', $line)) {
                                $line_class = 'fix-js';
                            } elseif (preg_match('/if \(window\.innerWidth <= 768\)/', $line)) {
                                $line_class = 'fix-js';
                            }
                            
                            // ?箸隤?擃漁
                            $highlighted = htmlspecialchars($line);
                            
                            // PHP 擃漁
                            $highlighted = preg_replace('/(&lt;\?php|&lt;\?|\?&gt;)/', '<span class="keyword">$1</span>', $highlighted);
                            $highlighted = preg_replace('/(function|class|if|else|foreach|echo|return|true|false|null)/', '<span class="keyword">$1</span>', $highlighted);
                            
                            // 摮葡擃漁
                            $highlighted = preg_replace('/(".*?"|\'.*?\')/', '<span class="string">$1</span>', $highlighted);
                            
                            // 閮餉圾擃漁
                            $highlighted = preg_replace('/(\/\/.*?$|#.*?$)/', '<span class="comment">$1</span>', $highlighted);
                            $highlighted = preg_replace('/(\/\*.*?\*\/)/s', '<span class="comment">$1</span>', $highlighted);
                            
                            // ?賣擃漁
                            $highlighted = preg_replace('/(\w+)\s*\(/', '<span class="function">$1</span>(', $highlighted);
                            
                            // 霈擃漁
                            $highlighted = preg_replace('/(\$[a-zA-Z_][a-zA-Z0-9_]*)/', '<span class="variable">$1</span>', $highlighted);
                            
                            // ?詨?擃漁
                            $highlighted = preg_replace('/(\b\d+\b)/', '<span class="number">$1</span>', $highlighted);
                            
                            echo '<div id="C' . $line_num . '" class="' . $line_class . '">' . $highlighted . '</div>';
                        }
                        ?>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="actions">
            <button class="btn" onclick="window.print()">?</button>
            <button class="btn" onclick="copySource()">銴ˊ</button>
            <button class="btn" onclick="searchCode()">??</button>
            <button class="btn btn-close" onclick="window.close()">??</button>
        </div>
    </div>
    
    <script>
        // 銴ˊ??蝣?        function copySource() {
            const code = `<?php echo addslashes($content); ?>`;
            navigator.clipboard.writeText(code).then(() => {
                alert('??蝣澆歇銴ˊ?啣鞎潛倏');
            }).catch(err => {
                console.error('銴ˊ憭望?:', err);
                alert('銴ˊ憭望?嚗???銴ˊ');
            });
        }
        
        // ??蝔?蝣?        function searchCode() {
            const query = prompt('隢撓?交?撠??萄?:');
            if (!query) return;
            
            // 蝘駁銋???鈭?            document.querySelectorAll('.search-highlight').forEach(el => {
                const parent = el.parentNode;
                parent.replaceChild(document.createTextNode(el.textContent), el);
                parent.normalize();
            });
            
            // ??銝阡?鈭?            const codeDivs = document.querySelectorAll('.code-content div');
            let found = false;
            
            codeDivs.forEach(div => {
                const text = div.textContent;
                if (text.includes(query)) {
                    found = true;
                    const highlighted = text.replace(
                        new RegExp(query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'gi'),
                        match => `<span class="search-highlight">${match}</span>`
                    );
                    div.innerHTML = highlighted;
                    
                    // 皛曉??啁洵銝????                    if (!window.scrolledToFirst) {
                        div.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        window.scrolledToFirst = true;
                    }
                }
            });
            
            if (!found) {
                alert('?芣?啁泵???批捆');
            }
        }
        
        // ?芸?皛曉??啣摰耨甇???撘Ⅳ
        document.addEventListener('DOMContentLoaded', function() {
            // 撠蝚砌??摰耨甇???銵?            const fixElements = document.querySelectorAll('.fix-css, .fix-js');
            if (fixElements.length > 0) {
                setTimeout(() => {
                    fixElements[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 500);
            }
        });
    </script>
</body>
</html>
