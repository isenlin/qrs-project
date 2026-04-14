<?php
/**
 * ?箸?摨血銵冽???JavaScript ?箏?靽格迤
 * 
 * 雿輻?寞?嚗? * 1. 撠迨瑼?銝剔? JavaScript 蝔?蝣潸?鋆賢 monthly_report.php
 * 2. 撠迨瑼?銝剔? CSS 璅??銴ˊ??monthly_report.php
 * 3. 皜祈岫靽格迤??
 */

// ?? Session
session_start();

require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/auth.php';

echo "<!DOCTYPE html>
<html lang='zh-TW'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>?漲?梯” JavaScript ?箏?靽格迤</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background: #f8f9fa; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 3px 15px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 15px; }
        h2 { color: #444; margin-top: 30px; }
        h3 { color: #555; margin-top: 20px; }
        
        .code-block {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            margin: 15px 0;
            overflow-x: auto;
            font-family: monospace;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .step {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 8px;
            border-left: 5px solid #28a745;
            margin: 20px 0;
        }
        
        .warning {
            background: #fff3cd;
            padding: 15px;
            border-radius: 8px;
            border-left: 5px solid #ffc107;
            margin: 20px 0;
        }
        
        .success {
            background: #d4edda;
            padding: 15px;
            border-radius: 8px;
            border-left: 5px solid #28a745;
            margin: 20px 0;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 10px 5px;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn:hover {
            background: #0056b3;
        }
        
        .btn-success {
            background: #28a745;
        }
        
        .btn-success:hover {
            background: #1e7e34;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .btn-warning:hover {
            background: #e0a800;
        }
        
        .test-area {
            border: 2px dashed #007bff;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            background: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>?? ?漲?梯” JavaScript ?箏?靽格迤</h1>
        
        <div class='success'>
            <h3>? 閫?捱?寞?</h3>
            <p>?暹?銝?? CSS <code>position: sticky</code>嚗??JavaScript ???箏?銵券?椰甈?/p>
            <p><strong>?芷?嚗?/strong> 100% ?舫??楊?汗?函摰嫘扯?芸??陛?桀祕??/p>
        </div>
        
        <h2>甇仿? 1嚗溶??CSS 璅??</h2>
        <div class='step'>
            <p>??<code>monthly_report.php</code> ??CSS 璅??銝剜溶?誑銝???</p>
        </div>
        
        <div class='code-block'>
/* ==================== JavaScript ?箏?璅?? ==================== */
/* ??璅???◤ JavaScript ??? */
.monthly-table.js-fixed thead {
    position: sticky !important;
    top: 0 !important;
    z-index: 100 !important;
    background: #f8f9fa !important;
}

.monthly-table.js-fixed tbody td:first-child {
    position: sticky !important;
    left: 0 !important;
    z-index: 90 !important;
    background: #f8f9fa !important;
    box-shadow: 2px 0 5px rgba(0,0,0,0.1) !important;
}

/* 蝣箔?銵券撌行?銋摰?*/
.monthly-table.js-fixed thead th:first-child {
    position: sticky !important;
    left: 0 !important;
    z-index: 110 !important;
    background: #f8f9fa !important;
}

/* 皛曉??扯?芸? */
.table-container.js-scrolling {
    overflow-x: auto;
    overflow-y: auto;
    position: relative;
}

/* ???芸? */
@media (max-width: 768px) {
    .monthly-table.js-fixed thead {
        top: 0 !important;
    }
    
    .monthly-table.js-fixed tbody td:first-child {
        left: 0 !important;
    }
    
    .monthly-table.js-fixed thead th:first-child {
        left: 0 !important;
    }
}
        </div>
        
        <h2>甇仿? 2嚗溶??JavaScript 蝔?蝣?/h2>
        <div class='step'>
            <p>??<code>monthly_report.php</code> ??<code>&lt;/body&gt;</code> 璅惜?溶?誑銝?JavaScript嚗?/p>
        </div>
        
        <div class='code-block'>
&lt;script&gt;
// ?漲?梯”銵冽?箏?閫?捱?寞?
document.addEventListener('DOMContentLoaded', function() {
    const tableContainer = document.querySelector('.table-container');
    const monthlyTable = document.querySelector('.monthly-table');
    
    if (!tableContainer || !monthlyTable) return;
    
    // 瘛餃? JS ?箏?憿
    monthlyTable.classList.add('js-fixed');
    tableContainer.classList.add('js-scrolling');
    
    const thead = monthlyTable.querySelector('thead');
    const firstCells = monthlyTable.querySelectorAll('tbody td:first-child');
    const firstHeaderCell = monthlyTable.querySelector('thead th:first-child');
    
    if (!thead || firstCells.length === 0) return;
    
    // ??皛曉?鈭辣
    let isScrolling = false;
    let animationFrameId = null;
    
    tableContainer.addEventListener('scroll', function() {
        if (!isScrolling) {
            isScrolling = true;
            
            // 雿輻 requestAnimationFrame ?芸??扯
            animationFrameId = requestAnimationFrame(() => {
                const scrollLeft = this.scrollLeft;
                const scrollTop = this.scrollTop;
                
                // ??隤踵銵券雿蔭
                thead.style.transform = `translateY(${scrollTop}px)`;
                
                // ??隤踵撌行?雿蔭
                firstCells.forEach(cell => {
                    cell.style.transform = `translateX(${scrollLeft}px)`;
                });
                
                // ??隤踵銵券撌行?雿蔭
                if (firstHeaderCell) {
                    firstHeaderCell.style.transform = `translateX(${scrollLeft}px)`;
                }
                
                isScrolling = false;
            });
        }
    });
    
    // ??隤踵嚗孛?潔?甈⊥遝?誑??箏?嚗?    setTimeout(() => {
        tableContainer.scrollLeft = 1;
        tableContainer.scrollTop = 1;
        setTimeout(() => {
            tableContainer.scrollLeft = 0;
            tableContainer.scrollTop = 0;
        }, 50);
    }, 500);
    
    // 皜??撟
    window.addEventListener('beforeunload', function() {
        if (animationFrameId) {
            cancelAnimationFrame(animationFrameId);
        }
    });
    
    // ??閮剖??內
    if (window.innerWidth <= 768) {
        setTimeout(() => {
            if (tableContainer.scrollWidth > tableContainer.clientWidth) {
                console.log('? ??銵冽嚗撌血皛?嚗”?剖?撌行?撌脣摰?);
            }
        }, 1000);
    }
    
    console.log('???漲?梯”銵冽?箏?撌脣???);
});

// 頛?賣嚗炎?亙摰?行???function checkTableFix() {
    const thead = document.querySelector('.monthly-table.js-fixed thead');
    const firstCell = document.querySelector('.monthly-table.js-fixed tbody td:first-child');
    
    if (!thead || !firstCell) {
        console.log('???曆??啗”?澆?蝝?);
        return false;
    }
    
    const theadRect = thead.getBoundingClientRect();
    const cellRect = firstCell.getBoundingClientRect();
    
    const isHeaderFixed = theadRect.top === 0;
    const isLeftColumnFixed = cellRect.left === 0;
    
    console.log('銵券?箏?嚗?, isHeaderFixed ? '????' : '??憭望?');
    console.log('撌行??箏?嚗?, isLeftColumnFixed ? '????' : '??憭望?');
    
    return isHeaderFixed && isLeftColumnFixed;
}

// ??皜祈岫?賣
function testTableFix() {
    const tableContainer = document.querySelector('.table-container');
    if (!tableContainer) return;
    
    // 璅⊥皛曉?
    tableContainer.scrollLeft = 300;
    tableContainer.scrollTop = 100;
    
    setTimeout(() => {
        const isFixed = checkTableFix();
        
        if (isFixed) {
            alert('??皜祈岫??嚗n銵券?椰甈摰迤撣詻?);
        } else {
            alert('??皜祈岫憭望?嚗n隢炎?亦汗??Console ?亦?閰喟敦鞈???);
        }
        
        // 皛曉???雿?        tableContainer.scrollLeft = 0;
        tableContainer.scrollTop = 0;
    }, 300);
}
&lt;/script&gt;
        </div>
        
        <h2>甇仿? 3嚗葫閰虫耨甇?/h2>
        <div class='step'>
            <p>靽格摰?敺?皜祈岫靽格迤??嚗?/p>
        </div>
        
        <div class='test-area'>
            <h3>蝡皜祈岫</h3>
            <p>暺?隞乩???皜祈岫 JavaScript ?箏??寞?嚗?/p>
            
            <button class='btn btn-success' onclick='testSimpleFix()'>皜祈岫蝪∪ JS ?箏??寞?</button>
            <button class='btn btn-warning' onclick='testActualPage()'>皜祈岫撖阡??漲?梯”</button>
            
            <div id='testResult' style='margin-top: 20px;'></div>
        </div>
        
        <h2>甇仿? 4嚗?霅???/h2>
        <div class='warning'>
            <h3>?? ??瑼Ｘ?</h3>
            <ol>
                <li><strong>銵券?箏?</strong>嚗遝???交?銵券?臬?箏??券??剁?</li>
                <li><strong>撌行??箏?</strong>嚗遝??摨?鞈??臬?箏??典椰?湛?</li>
                <li><strong>隞?璅?</strong>嚗誨?剝?桃??誨??閮?行迤撣賊＊蝷綽?</li>
                <li><strong>?收璇?</strong>嚗?擐祆?蝝見撘?行迤撣賂?</li>
                <li><strong>??</strong>嚗??啣銵典??賣?行迤撣賂?</li>
                <li><strong>???詨捆</strong>嚗?璈??臬甇?虜憿舐內?遝??</li>
            </ol>
        </div>
        
        <h2>??寞?</h2>
        <div class='step'>
            <p>憒? JavaScript ?寞?隞?⊥?嚗隞乩蝙?其誑銝??冽獢?</p>
            <ul>
                <li><a href='grid_table_solution.php' target='_blank'>CSS Grid ?寞?</a> - ?曆誨雿??銵?/li>
                <li><a href='dual_table_solution.php' target='_blank'>?”?潭獢?/a> - ??喟絞雿??舫?</li>
                <li><a href='simple_js_fix.php' target='_blank'>摰 JS ?寞?</a> - ?游??渡? JavaScript 撖衣</li>
            </ul>
        </div>
        
        <div class='success'>
            <h3>??摰?嚗?/h3>
            <p>?隞乩?甇仿?靽格敺??漲?梯”?”?澆摰?憿?閰脣停?質圾瘙箔???/p>
            <p>憒?隞???嚗???琿???嚗???靘脖?甇亦????/p>
        </div>
    </div>
    
    <script>
        function testSimpleFix() {
            const resultDiv = document.getElementById('testResult');
            resultDiv.innerHTML = '<div class=\"warning\"><p>甇???皜祈岫?...</p></div>';
            
            setTimeout(() => {
                window.open('simple_js_fix.php', '_blank');
                resultDiv.innerHTML = '<div class=\"success\"><p>??皜祈岫?撌脤???隢?啗?蝒葉皜祈岫銵冽?箏?????/p></div>';
            }, 500);
        }
        
        function testActualPage() {
            const resultDiv = document.getElementById('testResult');
            resultDiv.innerHTML = '<div class=\"warning\"><p>甇???撖阡??漲?梯”...</p></div>';
            
            setTimeout(() => {
                window.open('sales/monthly_report.php', '_blank');
                resultDiv.innerHTML = '<div class=\"success\"><p>??撖阡??漲?梯”撌脤???隢葫閰西”?澆摰???/p><p><strong>瘜冽?嚗?/strong> 撖阡???航撠?靽格迤??/p></div>';
            }, 500);
        }
        
        // ?芸?瑼Ｘ
        document.addEventListener('DOMContentLoaded', function() {
            console.log('?漲?梯” JavaScript ?箏?靽格迤隤芣??撌脰???);
            console.log('皜祈岫???嚗?);
            console.log('- 蝪∪ JS ?箏?嚗imple_js_fix.php');
            console.log('- CSS Grid ?寞?嚗rid_table_solution.php');
            console.log('- ?”?潭獢?dual_table_solution.php');
        });
    </script>
</body>
</html>
