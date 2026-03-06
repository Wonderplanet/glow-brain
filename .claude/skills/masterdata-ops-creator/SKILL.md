---
name: masterdata-ops-creator
description: 運営設計書スプシのローカルコピーからリリースキー別マスタデータCSVを一括生成するスキル。
  スプシフォルダを指定するだけで、設計書CSVを読んでイベント・ガチャ・ミッション・降臨バトル・
  ショップ・交換所・UIバナーのマスタデータを手順書に沿って生成します。
  「運営設計書からマスタデータ」「一括生成」「リリースキー生成」「ops-creator」「運営マスタ生成」
  「スプシからマスタデータ」「ガチャマスタ」「ミッションマスタ」などのキーワードで使用します。
---

# 運営マスタデータ一括生成スキル

## 概要

運営設計書スプシのローカルコピー（CSVフォルダ）をインプットとして、リリースキー単位で運営系マスタデータCSVを一括生成します。

各機能（イベント・ガチャ・ミッション・降臨バトル・ショップ・交換所・UIバナー）に対応する手順書（`setup-guides/`）を参照しながら、依存関係を考慮した順序でCSVを生成します。

> **インゲームデータ（バトル系）は対象外**: インゲーム・バトル設定（MstInGame 系）は `masterdata-ingame-creator` スキルが担当します。

---

## 出力先

```
domain/tasks/20260305_111309_ops-design-sheet-improvement/outputs/generated/{リリースキー}/tables/{テーブル名}.csv
```

**例:**
```
domain/tasks/20260305_111309_ops-design-sheet-improvement/outputs/generated/202602015/tables/
  ├── MstSeries.csv
  ├── MstEvent.csv
  ├── OprGacha.csv
  ├── OprGachaPrize.csv
  ├── MstMissionEvent.csv
  ├── MstAdventBattle.csv
  ├── MstPack.csv
  ├── MstHomeBanner.csv
  └── ...
```

---

## インプット（ユーザーに確認すること）

スキル開始時に以下3点を確認する。不明な場合はまとめて1回だけ質問する。

| # | 項目 | 例 | 必須 |
|---|------|-----|------|
| 1 | **スプシフォルダパス** | `domain/raw-data/google-drive/spread-sheet/GLOW/080_運営/いいジャン祭（施策）/運営_仕様書/20260202_幼稚園WARS いいジャン祭_仕様書/` | 必須 |
| 2 | **リリースキー** | `202602015` | 必須 |
| 3 | **出力先** | デフォルト: `domain/tasks/20260305_111309_ops-design-sheet-improvement/outputs/generated/{リリースキー}/tables/` | 任意（デフォルト可） |

---

## ワークフロー

### Step 1: スプシCSVの識別と読み込み

指定フォルダ内の CSV ファイルを列挙し、「設計書CSV」と「ノイズCSV」に分類する。
詳細は [`references/csv-identification-rules.md`](references/csv-identification-rules.md) を参照。

**作業手順:**
1. フォルダ内のファイル一覧を取得（`ls {フォルダパス}`）
2. 識別ルールに従って各CSVを分類
3. 設計書CSVの一覧をユーザーに提示して確認を取る
4. 設計書CSVのみを読み込む

**提示フォーマット:**
```
【設計書CSV（読み込み対象）】
- @幼稚園WARS いいジャン祭ピックアップガシャ_設計書.csv
- 01_概要.csv
- 04_ミッション.csv

【ノイズCSV（スキップ）】
- 案_旧設計書.csv
- バナー作成依頼.csv
- memo用計算シート.csv
```

---

### Step 2: 含まれる機能の判定

設計書CSVの内容から、今回のリリースに含まれる機能を特定する。
詳細は [`references/sheet-to-guide-mapping.md`](references/sheet-to-guide-mapping.md) を参照。

| 設計書ファイル | 機能 | 手順書 |
|-------------|------|--------|
| `01_概要.csv`, `02_施策.csv` | イベント基本情報 | `01_event.md` |
| `@*ガシャ_設計書.csv`, `06_ガシャ基本仕様.csv` | ガチャ | `03_gacha.md` |
| `03_降臨バトル.csv` | 降臨バトル | `06_advent-battle.md` |
| `04_ミッション.csv`, `05_報酬一覧.csv` | ミッション | `05_mission.md` |
| `07_*設計書.csv`, `07_ショップ_要件書.csv` | ショップ・パック | `08_shop-pack.md` |
| 概要CSVに「交換所」の記載あり | 交換所 | `10_exchange.md` |
| バナー関連の記載あり | UIバナー | `11_ui-banner.md` |

**判定結果をユーザーに提示して承認を取ってから Step 3 へ進む。**

---

### Step 3: 依存順序で機能別CSV生成

`setup-guides/README.md` の推奨設定順序に従い、依存関係を考慮して生成する。

**生成順序:**
1. **MstSeries → MstEvent**（`01_event.md` 参照）
   - イベント名・期間・シリーズ定義
2. **OprGacha 系**（`03_gacha.md` 参照）
   - ガチャ本体・景品・天井・消費リソース
3. **MstAdventBattle 系**（`06_advent-battle.md` 参照）
   - 降臨バトル・エンブレム・報酬
4. **MstMissionEvent 系**（`05_mission.md` 参照）
   - ミッション本体・報酬・解放順序
5. **MstPack / OprProduct 系**（`08_shop-pack.md` 参照）
   - パック・ストア商品・販売情報
6. **MstExchange 系**（`10_exchange.md` 参照、含まれる場合のみ）
   - 交換所・交換ラインナップ・コスト
7. **MstHomeBanner・MstMangaAnimation**（`11_ui-banner.md` 参照）
   - ホーム画面バナー・漫画アニメーション

各機能の生成時は、対応する手順書のカラム定義・命名規則・設定値ルールを必ず参照すること。

**手順書パス**: `domain/tasks/20260305_111309_ops-design-sheet-improvement/outputs/setup-guides/XX_*.md`

---

### Step 4: 過去データ参照（DuckDB）

各テーブル生成前に、類似リリースデータを参照してデータ構造・値の傾向を把握する。

```sql
-- 例: OprGacha の過去データ確認
SELECT * FROM read_csv(
  'domain/raw-data/masterdata/released/202602015/tables/OprGacha.csv'
) LIMIT 5;
```

**参照先リリースキー:**
- `202602015` — 標準的なイベント構成（ガチャ・ミッション・降臨バトルあり）
- `202512020` — 交換所ありのリリース
- `202601010` — 別パターン参照

---

### Step 5: CSV保存

各CSVを出力先ディレクトリに保存する。

```
domain/tasks/20260305_111309_ops-design-sheet-improvement/outputs/generated/{リリースキー}/tables/{テーブル名}.csv
```

**保存時の注意:**
- ヘッダー行はDBスキーマのカラム名と完全一致させる（`projects/glow-server/api/database/schema/exports/master_tables_schema.json` 参照）
- リリースキーカラムが存在する場合は正しい値を設定する
- ID 採番は `domain/knowledge/masterdata/ID割り振りルール.csv` を参照する（`masterdata-id-numbering` スキル活用可）

---

### Step 6: 検証

生成完了後、`masterdata-csv-validator` スキルで検証を実行する。

```
/masterdata-csv-validator
```

検証対象: `domain/tasks/20260305_111309_ops-design-sheet-improvement/outputs/generated/{リリースキー}/tables/`

**検証観点:**
- カラム名がDBスキーマと一致しているか
- 必須カラムに値が入っているか
- 参照IDが実在するか（外部キー整合性）
- リリースキーの値が正しいか

---

## 生成スコープ外

以下は本スキルの対象外：

| 機能 | 対応スキル |
|------|-----------|
| インゲーム・バトル設定（MstInGame 系） | `masterdata-ingame-creator` |
| キャラクター・ユニット追加（MstUnit 系） | 別途手動で実施（`02_unit.md` 参照） |
| アートワーク・フラグメント（MstArtwork 系） | 別途手動で実施（`07_artwork.md` 参照） |
| イベントクエスト・ステージ（MstQuest / MstStage 系） | 別途手動で実施（`04_quest-stage.md` 参照） |

---

## 参照リソース

| リソース | パス |
|---------|------|
| **手順書インデックス** | `domain/tasks/20260305_111309_ops-design-sheet-improvement/outputs/setup-guides/README.md` |
| **イベント手順書** | `domain/tasks/20260305_111309_ops-design-sheet-improvement/outputs/setup-guides/01_event.md` |
| **ガチャ手順書** | `domain/tasks/20260305_111309_ops-design-sheet-improvement/outputs/setup-guides/03_gacha.md` |
| **ミッション手順書** | `domain/tasks/20260305_111309_ops-design-sheet-improvement/outputs/setup-guides/05_mission.md` |
| **降臨バトル手順書** | `domain/tasks/20260305_111309_ops-design-sheet-improvement/outputs/setup-guides/06_advent-battle.md` |
| **ショップ手順書** | `domain/tasks/20260305_111309_ops-design-sheet-improvement/outputs/setup-guides/08_shop-pack.md` |
| **交換所手順書** | `domain/tasks/20260305_111309_ops-design-sheet-improvement/outputs/setup-guides/10_exchange.md` |
| **UIバナー手順書** | `domain/tasks/20260305_111309_ops-design-sheet-improvement/outputs/setup-guides/11_ui-banner.md` |
| **DBスキーマ** | `projects/glow-server/api/database/schema/exports/master_tables_schema.json` |
| **過去リリースデータ（標準）** | `domain/raw-data/masterdata/released/202602015/tables/` |
| **過去リリースデータ（交換所あり）** | `domain/raw-data/masterdata/released/202512020/tables/` |
| **ID採番ルール** | `domain/knowledge/masterdata/ID割り振りルール.csv` |
| **インゲーム生成スキル（委譲先）** | `.claude/skills/masterdata-ingame-creator/SKILL.md` |
| **CSVファイル識別ルール** | [`references/csv-identification-rules.md`](references/csv-identification-rules.md) |
| **シート→手順書マッピング** | [`references/sheet-to-guide-mapping.md`](references/sheet-to-guide-mapping.md) |
