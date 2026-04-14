// 純 HTML 表格固定測試的 JavaScript 部分（續）

// 方法3測試（續）
function testMethod3() {
    const gridTable = document.getElementById('gridTable');
    const header = document.getElementById('gridHeader');
    
    if (!gridTable || !header) return;
    
    gridTable.scrollLeft = 300;
    gridTable.scrollTop = 100;
    
    setTimeout(() => {
        const rect = header.getBoundingClientRect();
        const isFixed = rect.top === 0;
        
        addTestResult('方法3：CSS Grid 表頭固定', isFixed);
        
        // 檢查左欄固定
        const sidebarCell = document.querySelector('.grid-sidebar-cell');
        if (sidebarCell) {
            const cellRect = sidebarCell.getBoundingClientRect();
            const isLeftFixed = cellRect.left === 0;
            addTestResult('方法3：CSS Grid 左欄固定', isLeftFixed);
        }
        
        setTimeout(() => {
            gridTable.scrollLeft = 0;
            gridTable.scrollTop = 0;
        }, 1000);
    }, 300);
}

// 方法3滾動測試
function scrollMethod3() {
    const gridTable = document.getElementById('gridTable');
    if (gridTable) {
        gridTable.scrollLeft = 200;
        gridTable.scrollTop = 50;
        setTimeout(() => {
            gridTable.scrollLeft = 0;
            gridTable.scrollTop = 0;
        }, 2000);
    }
}

// 方法4：設定雙表格滾動同步
function setupDualScroll() {
    const container = document.getElementById('dualContainer');
    const header = document.getElementById('dualHeader');
    const content = document.getElementById('dualContent');
    
    if (!container || !header || !content) return;
    
    // 同步水平滾動
    content.addEventListener('scroll', function() {
        header.scrollLeft = this.scrollLeft;
    });
    
    // 同步垂直滾動
    container.addEventListener('scroll', function() {
        const sidebar = document.getElementById('dualSidebar');
        if (sidebar) {
            sidebar.style.top = this.scrollTop + 'px';
        }
    });
    
    addTestResult('方法4：雙表格滾動同步設定', true);
}

// 方法4測試
function testMethod4() {
    const container = document.getElementById('dualContainer');
    const header = document.getElementById('dualHeader');
    
    if (!container || !header) return;
    
    container.scrollLeft = 300;
    container.scrollTop = 100;
    
    setTimeout(() => {
        const rect = header.getBoundingClientRect();
        const isFixed = rect.top === 0;
        
        addTestResult('方法4：雙表格表頭固定', isFixed);
        
        // 檢查左欄固定
        const sidebar = document.getElementById('dualSidebar');
        if (sidebar) {
            const sidebarRect = sidebar.getBoundingClientRect();
            const isLeftFixed = sidebarRect.left === 0;
            addTestResult('方法4：雙表格左欄固定', isLeftFixed);
        }
        
        setTimeout(() => {
            container.scrollLeft = 0;
            container.scrollTop = 0;
        }, 1000);
    }, 300);
}

// 添加測試結果
function addTestResult(testName, isSuccess) {
    const results = document.getElementById('testResults');
    if (!results) return;
    
    const result = document.createElement('div');
    result.className = isSuccess ? 'test-item test-pass' : 'test-item test-fail';
    result.innerHTML = `<strong>${testName}：</strong> ${isSuccess ? '✅ 成功' : '❌ 失敗'}`;
    
    results.appendChild(result);
    
    // 更新狀態
    const status = document.getElementById('status');
    status.textContent = `狀態：${testName} ${isSuccess ? '成功' : '失敗'}`;
    status.className = `status ${isSuccess ? 'status-good' : 'status-bad'}`;
    
    // 自動滾動到最新結果
    result.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// 執行全部測試
function runAllTests() {
    const results = document.getElementById('testResults');
    results.innerHTML = '<div class="test-item"><strong>開始執行全部測試...</strong></div>';
    
    const status = document.getElementById('status');
    status.textContent = '狀態：測試中...';
    status.className = 'status';
    
    // 延遲執行測試
    setTimeout(() => testMethod1(), 500);
    setTimeout(() => {
        enableJsFix();
        setTimeout(() => testMethod2(), 500);
    }, 1500);
    setTimeout(() => testMethod3(), 3000);
    setTimeout(() => {
        setupDualScroll();
        setTimeout(() => testMethod4(), 500);
    }, 4500);
    
    // 最終狀態
    setTimeout(() => {
        status.textContent = '狀態：全部測試完成';
        status.className = 'status status-good';
    }, 6000);
}

// 重置全部
function resetAll() {
    // 重置滾動位置
    const containers = [
        document.getElementById('container1'),
        document.getElementById('container2'),
        document.getElementById('gridTable'),
        document.getElementById('dualContainer')
    ];
    
    containers.forEach(container => {
        if (container) {
            container.scrollLeft = 0;
            container.scrollTop = 0;
        }
    });
    
    // 重置測試結果
    const results = document.getElementById('testResults');
    results.innerHTML = '<div class="test-item"><strong>已重置全部測試</strong></div>';
    
    // 重置狀態
    const status = document.getElementById('status');
    status.textContent = '狀態：已重置';
    status.className = 'status';
    
    // 重新初始化表格
    initAllTables();
}

// 模擬手機
function simulateMobile() {
    const viewport = document.querySelector('meta[name="viewport"]');
    const original = viewport.content;
    
    // 切換到手機視口
    viewport.content = 'width=375, initial-scale=1.0, maximum-scale=1.0, user-scalable=no';
    
    alert('已切換到手機模擬模式（375px 寬度）\n\n請測試：\n1. 橫向滾動各個表格\n2. 觀察表頭和左欄是否固定\n3. 5秒後自動恢復');
    
    // 5秒後恢復
    setTimeout(() => {
        viewport.content = original;
        alert('已恢復正常視口');
    }, 5000);
}

// 初始化
document.addEventListener('DOMContentLoaded', function() {
    // 初始化所有表格
    initAllTables();
    
    // 自動啟用方法2的JS固定
    setTimeout(enableJsFix, 1000);
    
    // 自動設定方法4的滾動同步
    setTimeout(setupDualScroll, 1500);
    
    // 顯示歡迎訊息
    setTimeout(() => {
        const status = document.getElementById('status');
        status.textContent = '狀態：準備就緒';
        status.className = 'status status-good';
        
        console.log('✅ 四種表格固定方案測試頁面已載入');
        console.log('測試方法：');
        console.log('1. 點擊各個測試按鈕');
        console.log('2. 手動滾動表格確認固定效果');
        console.log('3. 選擇最適合的方案');
    }, 500);
});

// 導出函數供HTML使用
window.initAllTables = initAllTables;
window.testMethod1 = testMethod1;
window.scrollMethod1 = scrollMethod1;
window.enableJsFix = enableJsFix;
window.testMethod2 = testMethod2;
window.testMethod3 = testMethod3;
window.scrollMethod3 = scrollMethod3;
window.setupDualScroll = setupDualScroll;
window.testMethod4 = testMethod4;
window.runAllTests = runAllTests;
window.resetAll = resetAll;
window.simulateMobile = simulateMobile;