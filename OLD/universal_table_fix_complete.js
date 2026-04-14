// 電腦與手機通用表格固定方案 - JavaScript 部分

// 生成測試資料
const stores = [];
for (let i = 1; i <= 12; i++) {
    stores.push({
        code: '27' + i.toString().padStart(2, '0'),
        name: i <= 5 ? `台北店櫃 ${i}` : 
              i <= 8 ? `台中店櫃 ${i}` : 
              `高雄店櫃 ${i}`
    });
}

const dates = [];
for (let i = 1; i <= 20; i++) {
    const date = new Date(2026, 2, i); // 2026年3月
    dates.push({
        day: i,
        weekday: ['日', '一', '二', '三', '四', '五', '六'][date.getDay()],
        isWeekend: date.getDay() === 0 || date.getDay() === 6
    });
}

// 初始化表格
function initUniversalTable() {
    const table = document.getElementById('salesTable');
    if (!table) return;
    
    // 建立表頭
    const thead = table.querySelector('thead');
    if (thead) {
        let headerHTML = '<tr><th style="min-width: 160px;">店櫃</th>';
        
        dates.forEach(date => {
            headerHTML += `<th class="${date.isWeekend ? 'weekend' : ''}">
                ${date.day}<br><small>${date.weekday}</small>
            </th>`;
        });
        
        headerHTML += '<th style="min-width: 90px;">總計</th>';
        headerHTML += '<th style="min-width: 90px;">平均</th>';
        headerHTML += '</tr>';
        
        thead.innerHTML = headerHTML;
    }
    
    // 建立表格內容
    const tbody = document.createElement('tbody');
    
    stores.forEach(store => {
        const row = document.createElement('tr');
        
        // 店櫃資訊
        const storeCell = document.createElement('td');
        storeCell.className = 'store-cell';
        storeCell.innerHTML = `
            <div class="store-code">${store.code}</div>
            <div class="store-name">${store.name}</div>
        `;
        row.appendChild(storeCell);
        
        let dayTotal = 0;
        let dayCount = 0;
        
        // 日期資料
        dates.forEach(date => {
            const amount = Math.random() > 0.25 ? Math.floor(Math.random() * 25000) + 5000 : 0;
            const isSubstitute = Math.random() > 0.8 && amount > 0;
            
            if (amount > 0) {
                dayTotal += amount;
                dayCount++;
            }
            
            const cell = document.createElement('td');
            cell.className = date.isWeekend ? 'weekend' : '';
            
            let cellHTML = `<div class="amount ${amount === 0 ? 'amount-zero' : ''}">`;
            cellHTML += amount > 0 ? amount.toLocaleString() : '-';
            cellHTML += '</div>';
            
            if (isSubstitute && amount > 0) {
                cellHTML += '<div class="substitute">代</div>';
            }
            
            cell.innerHTML = cellHTML;
            row.appendChild(cell);
        });
        
        // 總計和平均
        const totalCell = document.createElement('td');
        totalCell.className = 'total-row';
        totalCell.textContent = dayTotal.toLocaleString();
        row.appendChild(totalCell);
        
        const avgCell = document.createElement('td');
        avgCell.className = 'total-row';
        const average = dayCount > 0 ? Math.floor(dayTotal / dayCount) : 0;
        avgCell.textContent = average.toLocaleString();
        row.appendChild(avgCell);
        
        tbody.appendChild(row);
    });
    
    // 移除舊的 tbody，添加新的
    const oldTbody = table.querySelector('tbody');
    if (oldTbody) {
        table.removeChild(oldTbody);
    }
    table.appendChild(tbody);
}

// 裝置檢測
function detectDevice() {
    const deviceInfo = document.getElementById('deviceInfo');
    const deviceSpecs = document.getElementById('deviceSpecs');
    
    if (!deviceInfo || !deviceSpecs) return;
    
    const isMobile = window.innerWidth <= 768;
    const isTablet = window.innerWidth > 768 && window.innerWidth <= 1024;
    const isDesktop = window.innerWidth > 1024;
    
    let deviceType = '💻 桌面電腦';
    let deviceIcon = '💻';
    
    if (isMobile) {
        deviceType = '📱 手機';
        deviceIcon = '📱';
    } else if (isTablet) {
        deviceType = '🖥️ 平板';
        deviceIcon = '🖥️';
    }
    
    deviceInfo.innerHTML = `
        <div class="device-icon">${deviceIcon}</div>
        <div class="device-text">${deviceType}</div>
    `;
    
    deviceSpecs.innerHTML = `
        <div class="spec-item">
            <span>📏 螢幕:</span>
            <span>${window.innerWidth} × ${window.innerHeight}px</span>
        </div>
        <div class="spec-item">
            <span>🌐 瀏覽器:</span>
            <span>${navigator.userAgent.split(' ')[0]}</span>
        </div>
        <div class="spec-item">
            <span>⚡ 性能:</span>
            <span>${navigator.hardwareConcurrency || 4}核心</span>
        </div>
    `;
}

// 啟用通用固定
let isUniversalFixEnabled = false;
let animationFrameId = null;

function enableUniversalFix() {
    if (isUniversalFixEnabled) {
        showResult('通用固定', '已啟用', 'success');
        return;
    }
    
    const tableWrapper = document.getElementById('tableWrapper');
    const table = document.getElementById('salesTable');
    
    if (!tableWrapper || !table) {
        showResult('通用固定', '找不到表格元素', 'error');
        return;
    }
    
    const thead = table.querySelector('thead');
    const firstCells = table.querySelectorAll('tbody td:first-child');
    
    if (!thead || firstCells.length === 0) {
        showResult('通用固定', '表格結構不完整', 'error');
        return;
    }
    
    // 添加固定類別
    table.classList.add('js-fixed');
    
    // 為表頭添加固定樣式
    thead.style.position = 'sticky';
    thead.style.top = '0';
    thead.style.zIndex = '100';
    thead.style.backgroundColor = '#f8fafc';
    thead.style.backdropFilter = 'blur(10px)';
    thead.style.webkitBackdropFilter = 'blur(10px)';
    
    // 為左欄添加固定樣式
    firstCells.forEach(cell => {
        cell.style.position = 'sticky';
        cell.style.left = '0';
        cell.style.zIndex = '90';
        cell.style.backgroundColor = '#f8fafc';
        cell.style.backdropFilter = 'blur(10px)';
        cell.style.webkitBackdropFilter = 'blur(10px)';
        cell.style.boxShadow = '2px 0 8px rgba(0,0,0,0.1)';
    });
    
    // 監聽滾動事件
    let isScrolling = false;
    
    tableWrapper.addEventListener('scroll', function() {
        if (!isScrolling) {
            isScrolling = true;
            
            // 使用 requestAnimationFrame 優化性能
            animationFrameId = requestAnimationFrame(() => {
                const scrollLeft = this.scrollLeft;
                const scrollTop = this.scrollTop;
                
                // 動態調整表頭位置
                thead.style.transform = `translateY(${scrollTop}px)`;
                
                // 動態調整左欄位置
                firstCells.forEach(cell => {
                    cell.style.transform = `translateX(${scrollLeft}px)`;
                });
                
                // 更新滾動提示
                updateScrollHint(scrollLeft, scrollTop);
                
                isScrolling = false;
            });
        }
    });
    
    isUniversalFixEnabled = true;
    
    // 更新狀態
    const status = document.getElementById('universalStatus');
    status.textContent = '狀態：通用固定已啟用';
    status.className = 'universal-status status-active';
    
    showResult('通用固定', '已成功啟用', 'success');
    
    // 觸發一次滾動以啟用固定
    setTimeout(() => {
        tableWrapper.scrollLeft = 1;
        tableWrapper.scrollTop = 1;
        setTimeout(() => {
            tableWrapper.scrollLeft = 0;
            tableWrapper.scrollTop = 0;
        }, 50);
    }, 100);
    
    // 手機專用優化
    if (window.innerWidth <= 768) {
        optimizeForMobile();
    }
}

// 手機專用優化
function optimizeForMobile() {
    const table = document.getElementById('salesTable');
    if (!table) return;
    
    // 調整字體大小
    table.style.fontSize = '14px';
    
    // 調整內邊距
    const cells = table.querySelectorAll('th, td');
    cells.forEach(cell => {
        cell.style.padding = '10px 6px';
    });
    
    // 調整第一欄寬度
    const firstCells = table.querySelectorAll('td:first-child');
    firstCells.forEach(cell => {
        cell.style.minWidth = '140px';
    });
    
    showResult('手機優化', '已套用手機專用樣式', 'success');
}

// 更新滾動提示
function updateScrollHint(scrollLeft, scrollTop) {
    const hint = document.getElementById('scrollHint');
    if (!hint) return;
    
    if (scrollLeft > 50 || scrollTop > 50) {
        hint.style.opacity = '0.7';
        hint.style.transition = 'opacity 0.3s';
    } else {
        hint.style.opacity = '1';
    }
}

// 測試固定功能
function testUniversalFix() {
    if (!isUniversalFixEnabled) {
        alert('請先啟用通用固定功能');
        return;
    }
    
    const tableWrapper = document.getElementById('tableWrapper');
    const thead = document.querySelector('#salesTable thead');
    const firstCell = document.querySelector('#salesTable tbody td:first-child');
    
    if (!tableWrapper || !thead || !firstCell) {
        showResult('固定測試', '找不到測試元素', 'error');
        return;
    }
    
    // 模擬滾動
    tableWrapper.scrollLeft = 400;
    tableWrapper.scrollTop = 150;
    
    setTimeout(() => {
        const theadRect = thead.getBoundingClientRect();
        const cellRect = firstCell.getBoundingClientRect();
        
        const isHeaderFixed = Math.abs(theadRect.top) < 5;
        const isLeftColumnFixed = Math.abs(cellRect.left) < 5;
        
        if (isHeaderFixed && isLeftColumnFixed) {
            showResult('固定測試', '表頭和左欄固定正常', 'success');
        } else {
            showResult('固定測試', 
                `表頭: ${isHeaderFixed ? '正常' : '異常'}, 左欄: ${isLeftColumnFixed ? '正常' : '異常'}`, 
                'warning');
        }
        
        // 滾動回原位
        setTimeout(() => {
            tableWrapper.scrollLeft = 0;
            tableWrapper.scrollTop = 0;
        }, 1000);
    }, 300);
}

// 模擬手機測試
function simulateMobileTest() {
    const viewport = document.querySelector('meta[name="viewport"]');
    const originalContent = viewport.content;
    
    // 儲存原始滾動位置
    const tableWrapper = document.getElementById('tableWrapper');
    const originalScrollLeft = tableWrapper ? tableWrapper.scrollLeft : 0;
    const originalScrollTop = tableWrapper ? tableWrapper.scrollTop : 0;
    
    // 切換到手機視口
    viewport.content = 'width=375, initial-scale=1.0, maximum-scale=1.0, user-scalable=no';
    
    // 強制重排
    document.body.style.width = '375px';
    window.dispatchEvent(new Event('resize'));
    
    // 更新裝置檢測
    detectDevice();
    
    // 啟用手機優化
    optimizeForMobile();
    
    showResult('手機模擬', '已切換到手機模式 (375px)', 'success');
    
    alert('📱 已切換到手機模擬模式\n\n請測試：\n1. 橫向滾動表格\n2. 觀察表頭和左欄是否固定\n3. 測試觸摸滾動體驗\n\n10秒後自動恢復');
    
    // 10秒後恢復
    setTimeout(() => {
        viewport.content = originalContent;
        document.body.style.width = '';
        window.dispatchEvent(new Event('resize'));
        detectDevice();
        
        // 恢復滾動位置
        if (tableWrapper) {
            tableWrapper.scrollLeft = originalScrollLeft;
            tableWrapper.scrollTop = originalScrollTop;
        }
        
        showResult('手機模擬', '已恢復正常模式', 'success');
    }, 10000);
}

// 重置所有
function resetUniversal() {
    const tableWrapper = document.getElementById('tableWrapper');
    if (tableWrapper) {
        tableWrapper.scrollLeft = 0;
        tableWrapper.scrollTop = 0;
    }
    
    // 清除動畫幀
    if (animationFrameId) {
        cancelAnimationFrame(animationFrameId);
        animationFrameId = null;
    }
    
    // 重置狀態
    const status = document.getElementById('universalStatus');
    status.textContent = '狀態：已重置';
    status.className = 'universal-status status-inactive';
    
    // 清除測試結果
    const resultsContainer = document.getElementById('resultsContainer');
    if (resultsContainer) {
        resultsContainer.innerHTML = '';
    }
    
    showResult('重置', '已重置所有設定', 'success');
}

// 顯示測試結果
function showResult(title, message, type) {
    const resultsContainer = document.getElementById('resultsContainer');
    if (!resultsContainer) return;
    
    const resultCard = document.createElement('div');
    resultCard.className = `result-card fade-in ${type === 'success' ? 'result-success' : 
                          type === 'warning' ? 'result-warning' : 'result-error'}`;
    
    let icon = '✅';
    if (type === 'warning') icon = '⚠️';
    if (type === 'error') icon = '❌';
    
    resultCard.innerHTML = `
        <div class="result-header">
            <div class="result-icon">${icon}</div>
            <div class="result-title">${title}</div>
        </div>
        <div class="result-content">${message}</div>
    `;
    
    resultsContainer.appendChild(resultCard);
    
    // 自動滾動到最新結果
    resultCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// 執行完整測試
function runCompleteTest() {
    const resultsContainer = document.getElementById('resultsContainer');
    if (resultsContainer) {
        resultsContainer.innerHTML = '';
    }
    
    showResult('開始測試', '正在執行完整測試套件...', 'success');
    
    // 測試順序
    setTimeout(() => detectDevice(), 500);
    setTimeout(() => enableUniversalFix(), 1000);
    setTimeout(() => testUniversalFix(), 2500);
    
    // 如果是手機，測試手機優化
    if (window.innerWidth <= 768) {
        setTimeout(() => optimizeForMobile(), 3500);
    }
    
    // 最終結果
    setTimeout(() => {
        showResult('測試完成', '所有測試項目已執行完畢', 'success');
        
        const status = document.getElementById('universalStatus');
        status.textContent = '狀態：測試完成';
        status.className = 'universal-status status-active';
    }, 4500);
}

// 初始化
document.addEventListener('DOMContentLoaded', function() {
    // 初始化表格
    initUniversalTable();
    
    // 檢測裝置
    detectDevice();
    
    // 自動啟用通用固定
    setTimeout(() => {
        enableUniversalFix();
    }, 800);
    
    // 監聽視窗大小變化
    window.addEventListener('resize', function() {
        detectDevice();
        
        // 如果是手機且固定已啟用，重新優化
        if (isUniversalFixEnabled && window.innerWidth <= 768) {
            optimizeForMobile();
        }
    });
    
    // 顯示滾動提示
    setTimeout(() => {
        const tableWrapper = document.getElementById('tableWrapper');
        if (tableWrapper && tableWrapper.scrollWidth > tableWrapper.clientWidth) {
            const hint = document.getElementById('scrollHint');
            if (hint) {
                hint.style.display = 'flex';
                
                // 5秒後淡出
                setTimeout(() => {
                    hint.style.opacity = '0.5';
                    setTimeout(() => {
                        hint.style.display = 'none';
                    }, 1000);
                }, 5000);
            }
        }
    }, 1500);
    
    console.log('💻📱 電腦與手機通用表格固定方案已載入');
});

// 導出函數供HTML使用
window.initUniversalTable = initUniversalTable;
window.detectDevice = detectDevice;
window.enableUniversalFix = enableUniversalFix;
window.testUniversalFix = testUniversalFix;
window.simulateMobileTest = simulateMobileTest;
window.resetUniversal = resetUniversal;
window.runCompleteTest = runCompleteTest;