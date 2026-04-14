# 店櫃業績管理系統 - 部署檢查清單

## ✅ 已完成項目

### 1. 系統檔案
- [x] 登入系統 (index.php, logout.php)
- [x] 儀表板 (dashboard.php)
- [x] 設定檔 (config/settings.php, config/auth.php)
- [x] 業績登打 (store/input.php)
- [x] 系統測試 (test_system.php)
- [x] 資料匯入 (import_daily_sales.php)
- [x] 共用函式 (common/functions.php)
- [x] 樣式檔案 (assets/css/style.css)

### 2. 資料檔案
- [x] 使用者資料 (data/users.json) - 10個測試帳號
- [x] 店櫃資料 (data/stores.json) - 16個店櫃
- [x] 銷售資料 (data/sales_summary.json) - 4天測試資料
- [x] 日誌檔案 (data/logs/) - audit.log, error.log

### 3. 測試資料
- [x] 管理員帳號: admin / admin123
- [x] 督導帳號: supervisor1-3 / super123
- [x] 業務帳號: sales1-4 / sales123
- [x] 店櫃帳號: store1-2 / store123
- [x] 3月份業績測試資料

## 🚀 部署步驟

### 步驟 1: 上傳檔案
將整個 `sales` 資料夾複製到：
```
\\192.168.0.11\web\QRS\sales\
```

### 步驟 2: 權限設定
確保以下目錄有寫入權限：
- `sales/data/`
- `sales/data/logs/`

### 步驟 3: 環境檢查
1. **PHP 版本**: 需要 PHP 7.4+
2. **JSON 擴展**: 必須啟用
3. **檔案權限**: data/ 目錄可寫入

### 步驟 4: 測試系統
1. 訪問: `http://伺服器位址/QRS/sales/test_system.php`
2. 檢查所有測試項目是否通過
3. 如有錯誤，根據提示修正

### 步驟 5: 登入測試
使用以下帳號測試：
1. **管理員**: admin / admin123
   - 功能: 全系統管理
   - 測試: 店櫃管理、使用者管理

2. **督導**: supervisor1 / super123
   - 功能: 查看所有店櫃報表
   - 測試: 全區報表查看

3. **業務**: sales1 / sales123
   - 功能: 查看負責店櫃報表
   - 測試: 區域報表查看

4. **店櫃**: store1 / store123
   - 功能: 登打業績
   - 測試: 業績輸入功能

## 🔧 故障排除

### 常見問題 1: 無法寫入檔案
**症狀**: 系統測試顯示目錄不可寫入
**解決方案**:
```
chmod 755 data/
chmod 755 data/logs/
```

### 常見問題 2: JSON 解析錯誤
**症狀**: 資料載入失敗
**解決方案**:
1. 檢查 JSON 檔案格式
2. 確保檔案編碼為 UTF-8
3. 移除檔案中的 BOM 標記

### 常見問題 3: 登入失敗
**症狀**: 帳號密碼正確但無法登入
**解決方案**:
1. 檢查 Session 設定
2. 確認密碼雜湊函式可用
3. 檢查使用者資料格式

### 常見問題 4: 頁面空白
**症狀**: 訪問頁面顯示空白
**解決方案**:
1. 開啟 PHP 錯誤顯示
2. 檢查 PHP 錯誤日誌
3. 確認檔案權限

## 📊 資料備份

### 重要檔案
定期備份以下檔案：
```
data/users.json      # 使用者資料
data/stores.json     # 店櫃資料
data/sales_summary.json # 銷售資料
data/logs/audit.log  # 操作日誌
```

### 備份頻率
- **每日**: 銷售資料
- **每週**: 使用者與店櫃資料
- **每月**: 完整系統備份

## 🔄 系統更新

### 更新步驟
1. 備份現有資料檔案
2. 上傳新版本系統檔案
3. 保留 data/ 目錄內容
4. 測試系統功能

### 注意事項
- 不要覆蓋 data/ 目錄
- 檢查設定檔相容性
- 測試所有角色功能

## 📞 支援資訊

### 系統資訊
- **系統名稱**: 店櫃業績管理系統
- **版本**: 1.0.0
- **開發日期**: 2026-03-21
- **技術架構**: PHP + JSON 檔案系統

### 測試帳號
```
管理員: admin / admin123
督導: supervisor1 / super123
業務: sales1 / sales123
店櫃: store1 / store123
```

### 檔案結構
```
sales/
├── index.php              # 登入頁面
├── dashboard.php          # 儀表板
├── test_system.php        # 系統測試
├── config/               # 設定檔
├── data/                 # 資料檔案
├── store/                # 店櫃功能
├── admin/                # 管理功能
├── sales/                # 業務功能
├── supervisor/           # 督導功能
└── assets/               # 靜態資源
```

## ✅ 最終檢查
1. [ ] 所有檔案已上傳到正確位置
2. [ ] 目錄權限設定正確
3. [ ] PHP 環境符合要求
4. [ ] 測試系統通過所有檢查
5. [ ] 各角色功能測試完成
6. [ ] 資料備份機制已建立

---

**部署完成時間**: 2026-03-21 02:20
**部署人員**: 蝦米 (AI 助理)
**部署位置**: \\192.168.0.11\web\QRS\sales\