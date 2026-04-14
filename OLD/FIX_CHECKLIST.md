# 修正檢查清單

## ✅ 已修正的檔案

### 1. dashboard.php
- ✅ 添加 `session_start()`
- ✅ 使用 `auth_simple.php`
- ✅ 使用 `get_current_session_user()`（不是 `get_current_session_user_simple()`）

### 2. auth_simple.php
- ✅ 包含 `can_access_store()` 函式
- ✅ 包含所有必要的函式別名

### 3. store/input.php
- ✅ 添加 `session_start()`
- ✅ 使用 `auth_simple.php`

### 4. sales/reports.php
- ✅ 添加 `session_start()`
- ✅ 使用 `auth_simple.php`

### 5. import_daily_sales.php
- ✅ 添加 `session_start()`
- ✅ 使用 `auth_simple.php`

### 6. index.php
- ✅ 已有 `session_start()`
- ✅ 使用 `auth_simple.php`
- ✅ 使用 `is_logged_in()` 和 `authenticate_user()`（不是 `_simple` 版本）

## 🔍 修正內容摘要

### 問題原因
1. **缺少 session_start()** - 多個檔案沒有啟動 Session
2. **函式名稱不一致** - 有些用 `_simple` 版本，有些用原始版本
3. **使用舊的 auth.php** - 應該使用 `auth_simple.php`

### 無限重定向循環流程
```
index.php → 檢查已登入？是 → 重定向到 dashboard.php
dashboard.php → 沒有 session_start() → 無法讀取 Session → 視為未登入
require_login() → 重定向回 index.php
index.php → 檢查已登入？是 → 重定向到 dashboard.php
...無限循環...
```

### 修正後流程
```
index.php → 檢查已登入？是 → 重定向到 dashboard.php
dashboard.php → 有 session_start() → 成功讀取 Session → 顯示儀表板
```

## 🚀 部署步驟

1. **複製整個 sales 資料夾**到：
   ```
   \\192.168.0.11\web\QRS\sales\
   ```

2. **設定權限**（如果需要）：
   - `data/` 目錄可寫入
   - `data/logs/` 目錄可寫入

3. **測試系統**：
   - 訪問：`https://qrs.civmeei.com.tw/sales/index.php`
   - 使用測試帳號登入
   - 檢查是否正常進入儀表板

## 🧪 測試帳號

```
管理員:   admin / admin123
督導:    supervisor1 / super123
業務:    sales1 / sales123
店櫃:    store1 / store123
```

## 📋 最終檢查

- [ ] 所有 PHP 檔案都有 `session_start()`（如果需要 Session）
- [ ] 所有檔案都使用 `auth_simple.php`
- [ ] 函式名稱統一（不使用 `_simple` 後綴）
- [ ] 資料目錄有寫入權限
- [ ] 瀏覽器快取已清除

## 🔧 如果還有問題

1. **檢查 Session**：訪問 `minimal_test.php`（如果已部署）
2. **檢查錯誤日誌**：查看伺服器錯誤訊息
3. **使用無痕模式**：避免瀏覽器快取問題
4. **檢查 Cookie 設定**：確保瀏覽器接受 Cookie

---

**修正完成時間**：2026-03-21 09:50  
**修正人員**：蝦米 (AI 助理)