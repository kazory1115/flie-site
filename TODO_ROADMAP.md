# TODO Roadmap

## 文件目的

這份文件不是功能許願池，而是目前 `file-platform` 的可執行開發清單。

用途只有三個：

- 排優先級
- 控進度
- 避免功能做到一半才發現資料結構或分層先天不對

請搭配 `PROJECT_ARCHITECTURE_PLAN.md` 一起看：

- `PROJECT_ARCHITECTURE_PLAN.md` 負責講「為什麼這樣設計」
- `TODO_ROADMAP.md` 負責講「接下來先做什麼」

## 狀態定義

- `DONE`：已完成且可驗收
- `IN_PROGRESS`：正在做
- `NEXT`：下一批優先項目
- `BLOCKED`：被前置條件卡住
- `LATER`：已知要做，但不該現在做

## 目前現況摘要

依目前程式碼，已經完成的基礎能力：

- Docker 開發環境
- Laravel 認證流程
- 檔案瀏覽頁 `/files`
- 檔案上傳 / 下載 / 刪除
- 資料夾建立 / 刪除
- 中英雙語切換
- 基礎 Dashboard 頁面
- Service / Repository 初步分層

目前真正缺的不是「再多一個 CRUD」，而是把平台骨架補到能撐後續擴充。

## 執行原則

- Controller 維持薄，不直接碰複雜查詢與流程
- 新功能優先落在 Service / Repository
- 所有會影響檔案狀態的流程，先定義資料一致性與 Transaction 邊界
- 先補可支撐後續功能的底層能力，再補表面 UI
- 超過萬筆資料的查詢，一開始就要考慮 index、pagination、批次處理

## 里程碑總覽

| Phase | 目標 | 狀態 | 優先級 | 說明 |
| --- | --- | --- | --- | --- |
| Phase 0 | 文件與基礎整理 | DONE | P0 | README 已補，架構方向已明確 |
| Phase 1 | Dashboard 指標化 | DONE | P0 | 儲存統計與最近上傳已落地 |
| Phase 2 | Quota 機制 | IN_PROGRESS | P0 | quota 已接上傳阻擋與 dashboard 顯示 |
| Phase 3 | 檔案管理核心體驗 | IN_PROGRESS | P1 | pagination、search、rename、sort 已落地，接下來補 multiple upload |
| Phase 4 | 回收站與刪除策略 | LATER | P1 | 補 delete lifecycle，避免誤刪直接炸 |
| Phase 5 | 預覽與大型檔案能力 | LATER | P2 | preview / chunk upload / thumbnail / background job |
| Phase 6 | 穩定性與維運 | LATER | P1 | 測試、錯誤追蹤、效能校正 |

## Phase 1: Dashboard 指標化

### 目標

把現在偏導覽頁的 Dashboard，補成真正有資訊價值的首頁。

### Tasks

| ID | 項目 | 狀態 | 優先級 | Owner | 相依 |
| --- | --- | --- | --- | --- | --- |
| P1-01 | 建立 `StorageMetricsService` | DONE | P0 | Backend | 無 |
| P1-02 | 建立 Dashboard 資料 DTO / response shape | DONE | P0 | Backend | P1-01 |
| P1-03 | Dashboard 顯示總檔案數、總資料夾數、總容量 | DONE | P0 | Frontend | P1-02 |
| P1-04 | Dashboard 顯示最近上傳檔案清單 | DONE | P1 | Backend + Frontend | P1-02 |
| P1-05 | 補 Dashboard 查詢效能檢查 | NEXT | P1 | Backend | P1-01 |

### 交付標準

- Dashboard 不再只是導頁入口
- 使用者登入後能看到自己的儲存概況
- 統計查詢不可掃全表到 UI 卡死
- 統計欄位命名固定，後續 quota 可直接重用

### 建議實作

- Backend
  - `app/Services/DashboardService.php`
  - `app/Services/StorageMetricsService.php`
- Frontend
  - `resources/js/Pages/Dashboard.vue`

### 驗收標準

- 已登入使用者可看到：
  - 總檔案數
  - 總資料夾數
  - 已使用容量
  - 最近上傳檔案
- 無資料時 UI 不報錯
- 查詢結果限定當前登入者資料

## Phase 2: Quota 機制

### 問題本質

沒有 quota 的檔案平台，現在看起來能用，但資料一長大就一定失控。

### Tasks

| ID | 項目 | 狀態 | 優先級 | Owner | 相依 |
| --- | --- | --- | --- | --- | --- |
| P2-01 | 決定 quota 存放位置：`users.quota_bytes` 或獨立 quota table | DONE | P0 | Backend | P1-01 |
| P2-02 | 建立 `QuotaService` | DONE | P0 | Backend | P2-01 |
| P2-03 | 上傳前檢查 quota | DONE | P0 | Backend | P2-02 |
| P2-04 | Dashboard 顯示已用 / 上限 / 使用率 | DONE | P0 | Backend + Frontend | P2-02 |
| P2-05 | quota 超限錯誤訊息與前端提示 | DONE | P0 | Frontend | P2-03 |
| P2-06 | 預留 admin 調整 quota 的資料結構 | LATER | P1 | Backend | P2-01 |

### 交付標準

- 上傳前先判斷 quota，不是寫進 storage 後才報錯
- quota 計算以 bytes 為準，不要混 MB 字串
- Dashboard 能直接看使用率
- 後續若加方案等級，不需要大改既有上傳流程

### 風險提醒

- ❌ 不建議把 quota 判斷寫死在 Controller
- ❌ 不建議只靠前端限制檔案大小
- ⚠️ 若未來支援多檔同傳，quota 必須考慮總和，不是單檔判斷

### 驗收標準

- 超過 quota 的檔案無法上傳
- 未超過 quota 的檔案可正常完成流程
- 已使用容量與 Dashboard 顯示一致

## Phase 3: 檔案管理核心體驗

### 目標

把目前能用但偏陽春的檔案管理頁，補成日常可操作版本。

### Tasks

| ID | 項目 | 狀態 | 優先級 | Owner | 相依 |
| --- | --- | --- | --- | --- | --- |
| P3-01 | 檔案 / 資料夾 rename | DONE | P0 | Backend + Frontend | 無 |
| P3-02 | 關鍵字 search | DONE | P0 | Backend + Frontend | 無 |
| P3-03 | sort by name / size / created_at | DONE | P1 | Backend + Frontend | P3-02 |
| P3-04 | pagination | DONE | P0 | Backend + Frontend | P3-02 |
| P3-05 | multiple upload | NEXT | P1 | Backend + Frontend | P2-03 |
| P3-06 | tree folder selector component | NEXT | P1 | Frontend | 無 |
| P3-07 | move file / move folder | LATER | P1 | Backend + Frontend | P3-01, P3-06 |
| P3-08 | rename UI 美化，改掉目前 `prompt` 互動 | NEXT | P2 | Frontend | P3-01 |

### 先做順序

1. `pagination`
2. `search`
3. `rename`
4. `sort`
5. `multiple upload`
6. `move`

### 為什麼這樣排

- 先補 pagination，避免資料量一上來整頁全撈
- search / sort 要一起設計 query 介面，不然之後會重拆
- rename 會碰到 `folders.path` 一致性問題，要先單獨處理
- move 依賴 tree selector 與 path 更新策略，太早做會炸

### 重點技術事項

- `folders.path` 若繼續存在，rename / move 時要整串更新子節點
- search 要先定義只搜目前資料夾，還是全域搜尋
- pagination 要與 breadcrumb、filter、sort 參數共存
- multiple upload 要考慮 quota 與部分失敗回報格式

### 驗收標準

- 10,000 筆以上資料時列表仍可分頁操作
- rename 不會造成 path 壞掉
- search / sort / pagination 可以共同使用
- multiple upload 至少能正確處理成功 / 失敗結果

## Phase 4: 回收站與刪除策略

### 問題本質

現在的刪除若是 hard delete，短期很乾脆，長期一定有人誤刪資料來找你。

### Tasks

| ID | 項目 | 狀態 | 優先級 | Owner | 相依 |
| --- | --- | --- | --- | --- | --- |
| P4-01 | 設計 file / folder 刪除生命週期 | LATER | P1 | Backend | P3-01 |
| P4-02 | 導入 soft delete 或 recycle bin table | LATER | P1 | Backend | P4-01 |
| P4-03 | 還原流程 restore | LATER | P1 | Backend + Frontend | P4-02 |
| P4-04 | 永久刪除流程 purge | LATER | P1 | Backend | P4-02 |
| P4-05 | 回收站列表 UI | LATER | P2 | Frontend | P4-03 |

### 建議

- ✅ 推薦：明確設計 recycle bin 流程，再決定是否用 soft delete
- ⚠️ 勉強可：只對 file soft delete，folder 先不做
- ❌ 不建議：維持現在直接刪實體檔案與 DB 記錄

### 驗收標準

- 使用者刪除資料後可在回收站找到
- restore 後關聯與路徑仍正確
- purge 後才真正刪除 storage 實體檔案

## Phase 5: 預覽與大型檔案能力

### 目標

讓平台不只會存檔，也能處理比較像產品的檔案體驗。

### Tasks

| ID | 項目 | 狀態 | 優先級 | Owner | 相依 |
| --- | --- | --- | --- | --- | --- |
| P5-01 | 圖片預覽 | LATER | P2 | Backend + Frontend | P3-04 |
| P5-02 | PDF 預覽 | LATER | P2 | Backend + Frontend | P5-01 |
| P5-03 | thumbnail 產生策略 | LATER | P2 | Backend | P5-01 |
| P5-04 | chunk upload | LATER | P2 | Backend + Frontend | P2-03 |
| P5-05 | 背景工作處理大檔案任務 | LATER | P2 | Backend | P5-03, P5-04 |

### 技術提醒

- chunk upload 不是前端切檔就算完成，後端要有合併、驗證、重試策略
- preview 會牽涉權限、暫時 URL、檔案類型白名單
- thumbnail 生成建議走 queue，不要同步卡請求

## Phase 6: 穩定性與維運

### Tasks

| ID | 項目 | 狀態 | 優先級 | Owner | 相依 |
| --- | --- | --- | --- | --- | --- |
| P6-01 | 補 Feature tests：upload / delete / folder create / quota | NEXT | P0 | Backend | P2-03 |
| P6-02 | 補 rename / search / pagination tests | LATER | P1 | Backend | P3-04 |
| P6-03 | 建立檔案操作錯誤碼 / 訊息規格 | NEXT | P1 | Backend | 無 |
| P6-04 | 檔案操作 audit log 規格 | LATER | P2 | Backend | P4-01 |
| P6-05 | 查詢 index 檢查與 SQL 校正 | NEXT | P1 | Backend | P3-04 |

### 驗收標準

- 核心檔案流程至少有 Feature test 保護
- 主要列表查詢有基本 index 支撐
- 大部分使用者錯誤訊息可以定位原因，不是只有 500

## 本週建議開工順序

如果你要按工程效率往下做，不要平均分配火力，直接這樣排：

1. `P1-01` 建立 `StorageMetricsService`
2. `P1-02` 定義 Dashboard metrics response
3. `P1-03` 補 Dashboard 統計卡片
4. `P2-01` 決定 quota 欄位設計
5. `P2-02` 建立 `QuotaService`
6. `P2-03` 接上傳前 quota 檢查
7. `P6-01` 補 upload / quota Feature tests
8. `P3-04` 補 pagination
9. `P3-02` 補 search
10. `P3-01` 補 rename

## 建議新增的類別清單

這份清單是依目前 codebase 分層直接推下去，不是重做架構：

- `app/Services/DashboardService.php`
- `app/Services/StorageMetricsService.php`
- `app/Services/QuotaService.php`
- `app/Services/FileQueryService.php`
- `app/Support/DTO/DashboardMetricsData.php`

如果後續 move / tree selector 要做深一點，再考慮：

- `app/Services/FolderTreeBuilder.php`
- `app/Support/DTO/FolderTreeNodeData.php`

## 已知風險清單

### R1. `folders.path` 會在 rename / move 爆炸

如果 `path` 是快取欄位，那 rename / move 時一定要同步更新所有子節點，不然 UI breadcrumb 會開始不可信。

### R2. quota 若只靠目前檔案總和即時計算，未來可能吃效能

初期可以接受，但資料量大之後可能要改成彙總欄位或事件驅動更新。

### R3. 刪除流程目前偏單純

如果現在是直接刪 DB + storage，之後導入回收站會有遷移成本，越晚做越痛。

### R4. multiple upload 與 chunk upload 不能混成一題做

這兩個看起來像同類型，實際上複雜度差很多。先做 multi-upload，chunk upload 留到大型檔案階段。

## 文件維護規則

之後更新這份文件時，請遵守：

- 新增功能先放到對應 Phase，不要直接亂加到最底下
- 每個 Task 至少要有 `狀態`、`優先級`、`相依`
- 做完就把 `狀態` 改掉，不要讓 roadmap 永遠停在 NEXT
- 若實作結果改變架構方向，要同步回寫 `PROJECT_ARCHITECTURE_PLAN.md`
