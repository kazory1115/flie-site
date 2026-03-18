# 檔案平台專案架構規劃

## 1. 專案定位

本專案是一個以 Laravel 12 + Vue 3 + Inertia 為核心的簡易雲端檔案平台，目標是提供：

- 使用者登入與帳號管理
- 資料夾管理
- 檔案上傳 / 下載 / 刪除
- 多語系介面
- 可逐步擴充為共享、配額、預覽、回收桶、系統監控的檔案平台

目前策略不是一次做成完整雲端硬碟，而是先把「核心檔案操作平台」打穩，再往配額、統計、分享權限擴充。

---

## 2. 技術選型

### 後端

- Laravel 12
- PHP 8.2
- MySQL 8.0
- Inertia.js

### 前端

- Vue 3
- Vite
- Tailwind CSS

### 開發環境

- Docker Compose
- nginx
- php-fpm
- mysql
- phpMyAdmin

---

## 3. 目前目錄結構

```txt
flie-site/
├─ docker/
│  ├─ nginx/
│  │  └─ default.conf
│  └─ php/
│     ├─ Dockerfile
│     └─ uploads.ini
├─ file-platform/
│  ├─ app/
│  │  ├─ Http/
│  │  │  ├─ Controllers/
│  │  │  ├─ Middleware/
│  │  │  └─ Requests/
│  │  ├─ Models/
│  │  ├─ Repositories/
│  │  └─ Services/
│  ├─ database/
│  │  └─ migrations/
│  ├─ lang/
│  │  ├─ en/
│  │  └─ zh_TW/
│  ├─ resources/
│  │  └─ js/
│  │     ├─ Components/
│  │     ├─ Layouts/
│  │     ├─ Pages/
│  │     └─ lib/
│  └─ routes/
├─ docker-compose.yml
├─ .env
├─ .env.example
└─ PROJECT_ARCHITECTURE_PLAN.md
```

---

## 4. 目前已完成內容

### 4.1 Docker 基礎環境

已完成：

- `docker-compose.yml`
- `nginx + php-fpm + mysql + phpMyAdmin`
- Docker 參數拆到專案根目錄 `.env`
- PHP upload 限制調整

目前用途：

- 本機開發可直接啟動 Laravel 環境
- phpMyAdmin 可輔助查看資料

---

### 4.2 Laravel 基礎站台

已完成：

- Breeze / Inertia 基本登入註冊流程
- 首頁 `/` 導向登入或 dashboard
- `/dashboard` 已改為真正的儀表板頁
- `/files` 為檔案操作主畫面

---

### 4.3 檔案平台核心資料結構

已完成資料表：

- `users`
- `folders`
- `files`

目前 `folders` 用途：

- 管理巢狀資料夾
- 支援 `parent_id`
- 保留 `path` 供 UI 與快速顯示使用

目前 `files` 用途：

- 儲存檔案 metadata
- 儲存檔案大小、mime type、hash、disk、path
- 實體檔案放在 Laravel storage

---

### 4.4 分層架構

目前已拆分：

- Controller
- Request
- Service
- Repository
- Model

已存在模組：

- `FileBrowserController`
- `FileController`
- `FolderController`
- `LocaleController`

- `FileBrowserService`
- `FileService`
- `FolderService`

- `FolderRepository`
- `UserFileRepository`

目前原則：

- Controller 不直接碰 DB
- 商業流程在 Service
- 查詢與資料操作集中在 Repository

---

### 4.5 檔案操作功能

目前已完成：

- 建立資料夾
- 瀏覽資料夾
- 進入資料夾內容
- 上傳檔案
- 下載檔案
- 刪除檔案
- 刪除空資料夾

目前限制：

- 刪除資料夾僅支援空資料夾
- 單檔上傳限制 100 MB
- 單檔上傳為主，尚未支援 chunk upload

---

### 4.6 Upload 區塊 UX

目前已完成：

- 卡片式拖拉上傳區
- 虛線框拖放狀態
- 檔案 icon
- 已選檔名與大小顯示
- 目標資料夾選擇器
- 最近使用資料夾置頂
- 樹狀縮排資料夾選單
- 上方同步顯示目前上傳位置

目前最近使用資料夾記錄方式：

- 使用 session 記錄最近 5 次上傳目標

---

### 4.7 多語系

目前已完成：

- 語系：`en` / `zh_TW`
- Laravel locale middleware
- Inertia 共享翻譯資料
- Vue `trans()` helper
- 登入前 / 登入後語系切換 UI
- auth / profile / files / dashboard 頁面翻譯

目前語系檔：

- `lang/en/ui.php`
- `lang/zh_TW/ui.php`
- `lang/en/auth.php`
- `lang/zh_TW/auth.php`
- `lang/en/passwords.php`
- `lang/zh_TW/passwords.php`
- `lang/en/validation.php`
- `lang/zh_TW/validation.php`

---

### 4.8 Dashboard

目前已完成：

- 不再自動 redirect 到 `/files`
- 有基本儀表板介面
- 可進入檔案空間
- 可進入個人資料
- 有快速開始說明

目前 dashboard 還不是管理儀表板，只是平台入口頁。

---

## 5. 目前待處理項目

### 5.1 空間統計

這是下一階段優先項目。

#### 平台空間統計

- 每個使用者已用空間
- 全站檔案總量
- 可搭配 quota

#### 主機儲存空間摘要

- 只顯示目前掛載磁碟總量 / 已用 / 剩餘
- 不掃任意目錄
- 不暴露過多 OS 細節

---

### 5.2 資料夾與檔案管理補強

- 資料夾重新命名
- 檔案重新命名
- 搜尋
- 排序
- 分頁
- 批次操作

---

### 5.3 刪除策略

目前狀態：

- 檔案直接刪除
- 資料夾只能刪空資料夾

待做：

- 回收桶
- 軟刪除
- 背景清理機制

---

### 5.4 權限與共享

待做：

- 使用者層級 quota
- 共享資料夾 / 分享連結
- 權限控管
- Policy 整理

---

### 5.5 檔案體驗

待做：

- chunk upload
- 多檔上傳
- 圖片 / PDF 預覽
- 上傳進度
- 上傳失敗重試

---

## 6. 目前設計上的已知限制

### 6.1 `folders.path` 為顯示方便欄位

目前 `path` 是為了：

- UI 顯示
- 上傳目標下拉快速顯示
- 減少前端自行組字串

風險：

- 若未來支援 rename / move，要同步更新整棵子樹 path

建議：

- 未來資料夾 rename / move 要統一進 Service 處理

---

### 6.2 最近使用資料夾目前存在 session

優點：

- 實作快
- 無需新表

缺點：

- 換瀏覽器或 session 重置即消失
- 不是真正持久化使用者偏好

建議：

- 若此功能成為常用操作，再改存 DB

---

### 6.3 原生 select 的樹狀表現有限

目前已做縮排，但本質還是原生 `<select>`。

限制：

- 視覺層級有限
- 無法做展開收合
- 無法顯示 folder icon / 最近使用 pin icon

建議：

- 未來改成自訂 tree dropdown component

---

## 7. 下一階段推薦實作順序

### Phase 1：空間統計

優先度最高。

- Dashboard 顯示平台空間統計
- Dashboard 顯示主機儲存空間摘要
- 統一成 `StorageMetricsService`

建議拆法：

- `StorageMetricsService`
- `DashboardService`
- dashboard cards UI

---

### Phase 2：Quota 與限制

- `users` 或獨立 quota table
- 每位使用者已用容量計算
- 上傳前檢查 quota
- 超量禁止上傳

---

### Phase 3：檔案管理補齊

- rename
- search
- sort
- pagination
- multiple upload

---

### Phase 4：刪除策略升級

- recycle bin
- soft delete
- restore
- 定期清理機制

---

### Phase 5：體驗升級

- preview
- chunk upload
- background job
- thumbnail

---

## 8. 建議新增的後續模組

### Backend

- `DashboardService`
- `StorageMetricsService`
- `QuotaService`
- `FolderTreeBuilder`
- `FileQueryService`

### Frontend

- 自訂 tree folder selector
- upload progress component
- dashboard stats cards
- file preview drawer

---

## 9. 建議資料指標

後續 dashboard 建議顯示：

- 我的已用空間
- 我的檔案總數
- 我的資料夾總數
- 全站檔案總數
- 全站已用容量
- 主機 storage 已用 / 剩餘 / 使用率

---

## 10. 目前開發歷程濃縮

### 已完成的實際開發軌跡

1. 建立 Docker 開發環境
2. 修正 MySQL 版本與初始化問題
3. 建立 Laravel 檔案平台基本骨架
4. 完成資料夾 / 檔案資料結構
5. 完成上傳 / 下載 / 刪除 / 瀏覽流程
6. 把上傳區塊升級成拖拉卡片
7. 完成雙語系切換
8. 補上 dashboard 與平台入口頁
9. 補上目標資料夾選擇、樹狀結構、最近使用資料夾

### 目前專案成熟度

目前屬於：

- 可操作的 MVP
- 架構已可繼續擴充
- 尚未進入「營運等級」

---

## 11. 結論

目前這個專案已經不是純骨架，而是進入「可實際操作的第一版檔案平台」階段。

現在最合理的下一步不是再補零碎 UI，而是開始做：

- 空間統計
- quota
- dashboard 真正數據化

這三個做完，整個平台就會從「會上傳檔案的站」升級成「真正有管理能力的檔案平台」。
