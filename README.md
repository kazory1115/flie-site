# Flie Site

## 專案定位

這個 repo 是 `file-platform` 檔案平台的開發入口，根目錄負責 Docker、環境啟動與總文件，實際 Laravel 應用放在 `file-platform/`。

目前專案目標是建立一套以 Laravel 12 + Vue 3 + Inertia 為核心的檔案管理平台，先完成可用的基礎檔案瀏覽、資料夾管理、檔案上傳下載、多語系與基礎 dashboard，再逐步補齊 quota、搜尋、回收站、預覽、chunk upload 等能力。

## 文件分工

- `PROJECT_ARCHITECTURE_PLAN.md`
  說明系統架構、技術選型、資料表方向、分層原則、功能拆分、風險點與階段性規劃。
- `TODO_ROADMAP.md`
  負責待辦、優先級、Phase 排程、相依關係與驗收標準，作為日常開發追蹤入口。

## 目前 Repo 結構

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
│  ├─ database/
│  ├─ lang/
│  ├─ public/
│  ├─ resources/
│  ├─ routes/
│  ├─ composer.json
│  └─ package.json
├─ docker-compose.yml
├─ .env
├─ .env.example
├─ PROJECT_ARCHITECTURE_PLAN.md
└─ README.md
```

## 技術棧

### Backend

- PHP 8.2
- Laravel 12
- Inertia.js
- MySQL 8

### Frontend

- Vue 3
- Vite
- Tailwind CSS

### Infra

- Docker Compose
- nginx
- php-fpm
- phpMyAdmin

## 目前已落地的內容

依目前程式碼結構，已經有一版可運作的基礎骨架：

- Docker 開發環境已配置完成
- Laravel Breeze / Inertia 認證流程已存在
- `/files` 檔案瀏覽頁已存在
- 檔案上傳、下載、刪除流程已接上
- 資料夾建立、刪除流程已接上
- `en` / `zh_TW` 多語系切換已存在
- `Dashboard` 頁面已建立，但目前偏導覽型首頁，不是完整統計 dashboard

## 架構方向

依 `PROJECT_ARCHITECTURE_PLAN.md`，這個專案不是要停在單純 CRUD，而是往可擴充的檔案平台演進，核心方向如下：

- Controller 保持薄，複雜流程集中到 Service
- 資料存取邏輯集中到 Repository
- 檔案與資料夾分開建模，並保留樹狀結構能力
- UI 與 API 都以「未來會追加 quota、preview、chunk upload、回收站」為前提設計
- Docker 與應用層設定分離，根目錄管理開發環境，`file-platform/` 專注 Laravel app

## 待補功能方向

依目前架構文件與程式碼現況，後續重點大致會落在：

- Dashboard 儲存統計卡片
- 使用者 quota 機制
- rename / search / sort / pagination
- multiple upload / chunk upload
- recycle bin / restore / soft delete
- 檔案預覽與 thumbnail
- 背景工作與較大檔案處理

這些功能已經拆進 `TODO_ROADMAP.md`，後續請直接以該文件追蹤 Phase、Owner、Status、ETA。

## 本機啟動

### 1. 啟動容器

```bash
docker compose up -d --build
```

### 2. 安裝 Laravel 相依與初始化

```bash
docker compose exec php composer install
docker compose exec php php artisan key:generate
docker compose exec php php artisan migrate
```

### 3. 啟動前端 Vite

```bash
cd file-platform
npm install
npm run dev
```

## 預設入口

- Laravel: `http://localhost:8880`
- phpMyAdmin: `http://localhost:8881`

## MySQL 連線資訊

根目錄 `.env.example` 預設值如下：

- Host: `127.0.0.1`
- Port: `3400`
- Database: `file_platform`
- Username: `root`
- Password: `root`

## 建議閱讀順序

1. 先看 `README.md` 了解 repo 分工與啟動方式
2. 再看 `PROJECT_ARCHITECTURE_PLAN.md` 了解架構與功能規劃
3. `TODO_ROADMAP.md` 作為每日開發與排程追蹤入口

## 補充說明

- 根目錄是環境與文件層，不要把 Laravel 應用程式碼散放在這一層
- `file-platform/README.md` 目前還是 Laravel 預設內容，後續建議改成「子系統開發說明」
- `PROJECT_ARCHITECTURE_PLAN.md` 目前檔案可讀，但在部分環境下會出現編碼亂碼，建議統一用 UTF-8 重新存檔
