// 雙表格解決方案的 JavaScript 部分

// 滾動同步
function setupScrollSync() {
    const container = document.getElementById('tableContainer');
    const header = document.getElementById('fixedHeader');
    const content = document.getElementById('scrollableContent');
    
    if (!container || !header || !content) return;
    
    // 同步水平滾動
    content.addEventListener('scroll', function() {
        header.scrollLeft = this.scrollLeft;
    });
    
    // 同步垂直滾動
    container.addEventListener('scroll', function() {
        const sidebar = document.getElementById('fixedSidebar');
        if (sidebar) {
            sidebar.style.top = this.scrollTop + 'px';
        }
    });
}

// 測試表頭固定
function testFixedHeader() {
    const container = document.getElementById('tableContainer');
    const header = document.getElementById('fixedHeader');
    const status = document.getElementById('statusBox');
    
    if (!container || !header) return;
    
    // 模擬滾動
    container.scrollLeft = 300;
    container.scrollTop = 100;
    
    setTimeout(() => {
        const headerRect = header.getBoundingClientRect();
        const isFixed = headerRect.top === 0;
        
        const result = document.createElement('div');
        result.className = isFixed ? 'test-item test-pass' : 'test-item test-fail';
        result.innerHTML = `<strong>表頭固定測試：</strong> ${isFixed ? '✅ 成功 - 表頭固定在頂部' : '❌ 失敗'}`;
        
        document.getElementById('testResults').appendChild(result);
        
        status.textContent = `狀態：表頭固定 ${isFixed ? '成功' : '失敗'}`;
        status.className = `status-box ${isFixed ? 'status-good' : 'status-bad'}`;
        
    }, 300);
}

// 測試左欄固定
function testFixedSidebar() {
    const container = document.getElementById('tableContainer');
    const sidebar = document.getElementById('fixedSidebar');
    const status = document.getElementById('statusBox');
    
    if (!container || !sidebar) return;
    
    // 模擬滾動
    container.scrollLeft = 400;
    container.scrollTop = 150;
    
    setTimeout(() => {
        const sidebarRect = sidebar.getBoundingClientRect();
        const isFixed = sidebarRect.left === 0;
        
        const result = document.createElement('div');
        result.className = isFixed ? 'test-item test-pass' : 'test-item test-fail';
        result.innerHTML = `<strong>左欄固定測試：</strong> ${isFixed ? '✅ 成功 - 左欄固定在左側' : '❌ 失敗'}`;
        
        document.getElementById('testResults').appendChild(result);
        
        status.textContent = `狀態：左欄固定 ${isFixed ? '成功' : '失敗'}`;
        status.className = `status-box ${isFixed ? 'status-good' : 'status-bad'}`;
        
    }, 300);
}

// 測試滾動同步
function testScrollSync() {
    const content = document.getElementById('scrollableContent');
    const header = document.getElementById('fixedHeader');
    const status = document.getElementById('statusBox');
    
    if (!content || !header) return;
    
    // 滾動內容區域
    content.scrollLeft = 200;
    
    setTimeout(() => {
        const isSynced = Math.abs(content.scrollLeft - header.scrollLeft) < 5;
        
        const result = document.createElement('div');
        result.className = isSynced ? 'test-item test-pass' : 'test-item test-fail';
        result.innerHTML = `<strong>滾動同步測試：</strong> ${isSynced ? '✅ 成功 - 表頭和內容同步滾動' : '❌ 失敗'}`;
        
        document.getElementById('testResults').appendChild(result);
        
        status.textContent = `狀態：滾動同步 ${isSynced ? '成功' : '失敗'}`;
        status.className = `status-box ${isSynced ? 'status-good' : 'status-bad'}`;
        
    }, 100);
}

// 重置滾動
function resetScroll() {
    const container = document.getElementById('tableContainer');
    const content = document.getElementById('scrollableContent');
    
    if (container) {
        container.scrollLeft = 0;
        container.scrollTop = 0;
    }
    
    if (content) {
        content.scrollLeft = 0;
    }
    
    const status = document.getElementById('statusBox');
    status.textContent = '狀態：已重置滾動';
    status.className = 'status-box';
}

// 自動測試
function runAutoTest() {
    document.getElementById('testResults').innerHTML = '';
    
    setTimeout(() => testFixedHeader(), 500);
    setTimeout(() => testFixedSidebar(), 1500);
    setTimeout(() => testScrollSync(), 2500);
    
    const status = document.getElementById('statusBox');
    status.textContent = '狀態：自動測試中...';
    status.className = 'status-box';
}

// 初始化
document.addEventListener('DOMContentLoaded', function() {
    setupScrollSync();
    
    // 自動執行測試
    setTimeout(runAutoTest, 1000);
    
    // 滾動提示
    setTimeout(() => {
        const container = document.getElementById('tableContainer');
        if (container && container.scrollWidth > container.clientWidth) {
            alert('📱 提示：可左右滑動查看完整表格\n\n表頭和左欄應該保持固定！');
        }
    }, 2000);
});