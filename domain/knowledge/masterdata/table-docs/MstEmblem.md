# MstEmblem 詳細説明

> CSVパス: `projects/glow-masterdata/MstEmblem.csv`
> i18n CSVパス: `projects/glow-masterdata/MstEmblemI18n.csv`

---

## 概要

`MstEmblem` は**ゲーム内のエンブレム（称号・バッジ）の定義テーブル**。

エンブレムはプレイヤーのプロフィール画面に表示される実績的なバッジ。特定の条件（作品のメインクエスト全クリア・イベントへの参加など）を達成したプレイヤーに付与される。エンブレムタイプ（イベント系 / 作品系）と表示アセット・関連作品IDを管理する。

`mst_emblems_i18n` テーブルでエンブレム名・フレーバーテキストの多言語対応を行う。

CSVは合計30件程度（2026年3月現在）。

### ゲームプレイへの影響

- プレイヤーは獲得したエンブレムをプロフィールに表示して他のプレイヤーに見せることができる
- **`emblem_type = Series`**: 作品のメインクエストを全クリアした際に付与。各作品に1つずつ存在
- **`emblem_type = Event`**: イベント参加・達成時に付与。イベント開催ごとに作成される
- **`mst_series_id`**: 作品系エンブレムはどの作品に関連するかを示す

### 関連テーブルとの構造図

```
MstEmblem（エンブレム定義）
  └─ id → MstEmblemI18n.mst_emblem_id（多言語名称・フレーバーテキスト）
  └─ mst_series_id → MstSeries.id（作品マスタ）
  └─ id → MstDummyUser.mst_emblem_id（ダミーユーザーのプロフィール表示用）
```

---

## 全カラム一覧

### mst_emblems カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー。UUID形式 |
| `emblem_type` | enum | 不可 | - | エンブレムのタイプ。`Event`（イベント系）/ `Series`（作品系）の2種 |
| `mst_series_id` | varchar(255) | 許可 | - | 関連する作品ID（`mst_series.id`）。`Event` タイプの場合はNULLになることがある |
| `asset_key` | varchar(255) | 不可 | - | エンブレム画像のアセットキー |
| `release_key` | bigint | 不可 | 1 | リリースキー。マスタデータのバージョン管理に使用 |

### MstEmblemI18n カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `mst_emblem_id` | varchar(255) | 不可 | - | 参照先エンブレムID（`mst_emblems.id`） |
| `language` | enum | 不可 | - | 言語コード。`ja` のみ対応 |
| `name` | varchar(255) | 不可 | - | エンブレム名称（作品名やイベント名） |
| `description` | varchar(255) | 不可 | - | フレーバーテキスト（獲得条件などの説明） |

---

## EmblemType（エンブレムタイプ）

| 値 | 意味 | `mst_series_id` | 用途 |
|----|------|-----------------|------|
| `Series` | 作品系エンブレム | 作品IDを設定 | 各作品のメインクエストを全クリアした際に付与 |
| `Event` | イベント系エンブレム | NULL可（イベント特有の場合） | イベント参加・達成報酬として付与 |

---

## 命名規則 / IDの生成ルール

| 項目 | 規則 | 例 |
|------|------|----|
| `id` | `emblem_{タイプ}_{作品略称}_{4桁連番}` | `emblem_normal_spy_00001`, `emblem_event_kai_00001` |
| `asset_key` | `{タイプ}_{作品略称}_{4桁連番}` | `normal_spy_00001`, `event_kai_00001` |
| i18n の `id` | `{emblem_id}_ja` | `emblem_normal_spy_00001_ja` |

---

## 他テーブルとの連携

| テーブル | 参照方向 | 用途 |
|---------|---------|------|
| `mst_emblems_i18n` | `id` ← `mst_emblem_id` | エンブレムの名称・フレーバーテキスト |
| `mst_series` | `mst_series_id` → `id` | 関連作品マスタ |
| `mst_dummy_users` | `id` ← `mst_emblem_id` | ダミーユーザーのプロフィール表示 |

---

## 実データ例

### パターン1: 作品系エンブレム（SPY×FAMILY）

```
[MstEmblem.csv]
ENABLE: e
id: emblem_normal_spy_00001
emblem_type: Series
mst_series_id: spy
asset_key: normal_spy_00001
release_key: 202509010

[MstEmblemI18n.csv]
ENABLE: e
release_key: 202509010
id: emblem_normal_spy_00001_ja
mst_emblem_id: emblem_normal_spy_00001
language: ja
name: SPY×FAMILY
description: メインクエスト『SPY×FAMILY』のステージを全てクリアした証
```

メインクエストの全クリア実績として付与されるエンブレム。作品名をそのまま名称として使用し、フレーバーテキストで獲得条件を説明している。

### パターン2: 作品系エンブレム（ラーメン赤猫）

```
[MstEmblem.csv]
ENABLE: e
id: emblem_normal_aka_00001
emblem_type: Series
mst_series_id: aka
asset_key: normal_aka_00001
release_key: 202509010

[MstEmblemI18n.csv]
ENABLE: e
release_key: 202509010
id: emblem_normal_aka_00001_ja
mst_emblem_id: emblem_normal_aka_00001
language: ja
name: ラーメン赤猫
description: メインクエスト『ラーメン赤猫』のステージを全てクリアした証
```

---

## 設定時のポイント

1. **新作品を追加する際は `Series` タイプのエンブレムも同時に作成する**。メインクエストが存在する作品には必ず1つの作品系エンブレムが対応する設計になっている。

2. **`id` の命名は `emblem_{タイプ}_{作品略称}_{4桁連番}` 形式を厳守する**。既存データで確立されたパターンであり、外部参照（`MstDummyUser.mst_emblem_id` など）との整合性のために一貫した命名が重要。

3. **`asset_key` は `emblem_` プレフィックスを含まない**。実データでは `normal_spy_00001` のように `emblem_` を除いた形式で設定されているため注意。

4. **i18nレコードはエンブレムごとに必ず1レコード（language=ja）を作成する**。i18nが存在しない場合、クライアントでエンブレム名とフレーバーテキストが表示されない。

5. **`description`（フレーバーテキスト）にはプレイヤーが理解できる獲得条件を記述する**。既存データは「メインクエスト『{作品名}』のステージを全てクリアした証」という統一フォーマットを使用している。イベント系でも同様に獲得条件をわかりやすく記述することを推奨。

6. **`mst_series_id` は `Event` タイプでもNULLにしない方が望ましい**。イベントが特定作品に関連する場合は作品IDを設定することで、関連付けを明確にできる。

7. **既存作品のエンブレムIDと `mst_series_id` の整合性を確認する**。`mst_series_id = 'spy'` なら `id = 'emblem_normal_spy_...'` という対応を崩さないこと。
