# Docker 啟動方式

1. 啟動容器

```bash
docker compose up -d --build
```

2. 進入 PHP 容器安裝相依

```bash
docker compose exec php composer install
docker compose exec php php artisan migrate
```

3. 啟動前端 Vite

```bash
cd file-platform
npm install
npm run dev
```

4. 入口

- Laravel: http://localhost:8080
- phpMyAdmin: http://localhost:8081

5. MySQL 連線資訊

- Host: `127.0.0.1`
- Port: `3307`
- Database: `file_platform`
- Username: `root`
- Password: `root`

6. Docker 參數集中管理

- Docker Compose 預設讀取專案根目錄的 `.env`
- `.env.example` 是範本
- `.env.docker` 保留給你做額外版本管理或備份，不是 Compose 預設讀取檔
- Laravel 應用程式設定維持在 `file-platform/.env`
