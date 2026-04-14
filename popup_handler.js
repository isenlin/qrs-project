// 彈出視窗處理器
(function() {
    'use strict';
    
    // 打開每日業績彈出視窗
    function openDailySalesPopup(url) {
        // 設定彈出視窗參數
        const width = Math.min(1200, window.innerWidth - 40);
        const height = Math.min(800, window.innerHeight - 40);
        const left = (window.innerWidth - width) / 2;
        const top = (window.innerHeight - height) / 2;
        
        // 彈出視窗設定
        const features = [
            `width=${width}`,
            `height=${height}`,
            `left=${left}`,
            `top=${top}`,
            'menubar=no',
            'toolbar=no',
            'location=no',
            'status=no',
            'resizable=yes',
            'scrollbars=yes'
        ].join(',');
        
        // 打開彈出視窗
        const popup = window.open(url, 'daily_sales_popup', features);
        
        // 如果彈出視窗被阻擋，顯示提示
        if (!popup || popup.closed || typeof popup.closed === 'undefined') {
            // 嘗試直接打開連結（可能會在新分頁）
            window.open(url, '_blank');
            
            // 顯示提示訊息
            if (window.confirm('彈出視窗被阻擋。是否要在新分頁中打開？')) {
                window.open(url, '_blank');
            }
        }
        
        return false; // 防止預設連結行為
    }
    
    // 初始化彈出視窗連結
    function initPopupLinks() {
        // 找到所有彈出視窗連結
        const popupLinks = document.querySelectorAll('a[href*="daily_sales_simple.php"]');
        
        popupLinks.forEach(link => {
            // 移除現有的 onclick 事件（如果有）
            link.onclick = null;
            
            // 添加新的 onclick 事件
            link.addEventListener('click', function(e) {
                e.preventDefault();
                openDailySalesPopup(this.href);
                return false;
            });
            
            // 確保 target="_blank"
            link.target = '_blank';
        });
        
        console.log(`已初始化 ${popupLinks.length} 個彈出視窗連結`);
    }
    
    // 等待 DOM 載入完成
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPopupLinks);
    } else {
        initPopupLinks();
    }
    
    // 導出函數供全域使用
    window.openDailySalesPopup = openDailySalesPopup;
    window.initPopupLinks = initPopupLinks;
    
    console.log('彈出視窗處理器已載入');
})();