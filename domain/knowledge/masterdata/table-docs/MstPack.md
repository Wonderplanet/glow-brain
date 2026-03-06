# MstPack 詳細説明

> CSVパス: `projects/glow-masterdata/MstPack.csv`
> i18n CSVパス: `projects/glow-masterdata/MstPackI18n.csv`

---

## 概要

`MstPack` は**ショップで販売されるパック商品の基本設定テーブル**。パックの販売タイプ・コスト種別・販売条件・表示設定などを定義する。

パックの具体的な内容物（何が何個入っているか）は `MstPackContent` テーブルで管理される。`MstPackI18n` テーブルと連携してパック名を多言語対応する。

パックは課金（Cash）・ダイヤ消費・広告視聴（Ad）・無料（Free）など多様な販売形態に対応しており、販売条件（ステージクリア・ユーザーレベルなど）によるアンロック機能も持つ。

### ゲームプレイへの影響

- `pack_type` が `Daily` のパックは毎日リセットされ、`tradable_count` で購入可能回数が設定される
- `sale_condition` / `sale_condition_value` で特定の条件を達成したプレイヤーだけに表示する制限ができる
- `is_first_time_free` で初回限り無料の特別販売が実現できる
- `is_recommend` でおすすめバッジを表示できる

---

## 全カラム一覧

### mst_packs カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|---------|-----------|----|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー |
| `product_sub_id` | varchar(255) | 不可 | - | 外部製品ID（`opr_products.id`） |
| `discount_rate` | smallint unsigned | 不可 | - | 割引率（%） |
| `pack_type` | enum('Daily','Normal') | 不可 | - | パック販売タイプ |
| `sale_condition` | enum('StageClear','UserLevel') | 可 | NULL | 販売開始条件 |
| `sale_condition_value` | varchar(255) | 可 | NULL | 販売開始条件値 |
| `sale_hours` | smallint unsigned | 可 | NULL | 条件達成からの販売時間（時間単位） |
| `tradable_count` | int unsigned | 可 | NULL | 購入可能個数（NULLは無制限） |
| `cost_type` | enum | 不可 | - | 販売コスト種別（後述のenum参照） |
| `cost_amount` | int unsigned | 不可 | 0 | コスト量 |
| `is_recommend` | tinyint unsigned | 不可 | 0 | おすすめフラグ（1=おすすめ表示） |
| `is_first_time_free` | tinyint | 不可 | 0 | 初回無料フラグ（1=初回無料） |
| `is_display_expiration` | tinyint unsigned | 不可 | 0 | 表示期限があるかどうか（1=あり） |
| `asset_key` | varchar(255) | 不可 | - | バナー画像パス |
| `pack_decoration` | enum('Gold') | 可 | NULL | パックの装飾種別 |
| `release_key` | int | 不可 | 1 | リリースキー |

### MstPackI18n カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|---------|-----------|----|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー |
| `mst_pack_id` | varchar(255) | 不可 | - | 親テーブルID（`mst_packs.id`） |
| `language` | varchar(255) | 不可 | - | 言語コード |
| `name` | varchar(255) | 不可 | - | パック名（UI表示用） |
| `release_key` | int | 不可 | 1 | リリースキー |

---

## PackType（パック販売タイプ）

| 値 | 説明 |
|----|------|
| `Daily` | デイリーパック。毎日リセットされ `tradable_count` 回まで購入可能 |
| `Normal` | 通常パック。期間内に `tradable_count` 回まで購入可能（NULLは無制限） |

## CostType（販売コスト種別）

| 値 | 説明 |
|----|------|
| `Cash` | 課金通貨（実際の現金で購入） |
| `Diamond` | ダイヤ（有償・無償問わず） |
| `PaidDiamond` | 有償ダイヤのみ |
| `Ad` | 広告視聴 |
| `Free` | 無料 |

## SaleCondition（販売開始条件）

| 値 | 説明 | sale_condition_value |
|----|------|---------------------|
| `StageClear` | 指定ステージをクリアしたら表示 | クリア対象のステージID |
| `UserLevel` | 指定ユーザーレベルに達したら表示 | ユーザーレベル値 |

## PackDecoration（パック装飾）

| 値 | 説明 |
|----|------|
| `Gold` | ゴールドの特別装飾でパックを強調表示 |

---

## 命名規則 / IDの生成ルール

- `id`: パックの用途・種別を表す命名
  - スタートダッシュ系: `start_chara_pack_{連番}`、`start_item_pack_{連番}`
  - イベント系: `event_item_pack_{連番}`
  - デイリー系: `daily_item_pack_{連番}`
  - ブラックフライデー等の特別イベント: `BF_item_pack_{連番}`
- `MstPackI18n.id`: `{mst_pack_id}_{言語コード}` 形式（例: `start_chara_pack_1_ja`）

---

## 他テーブルとの連携

```
MstPack
  └─ product_sub_id → OprProduct.id（課金製品との紐付け）
  └─ id → MstPackContent.mst_pack_id（パックの内容物）
  └─ id → MstPackI18n.mst_pack_id（多言語名称）
```

---

## 実データ例

**パターン1: スタートダッシュパック（課金・回数制限あり）**

| id | product_sub_id | discount_rate | pack_type | sale_condition | tradable_count | cost_type | is_first_time_free | is_display_expiration | asset_key |
|----|---------------|---------------|-----------|---------------|----------------|-----------|-------------------|----------------------|-----------|
| start_chara_pack_1 | 13 | 0 | Normal | NULL | 0 | Cash | 0 | 0 | pack_00001 |
| start_item_pack_1 | 14 | 0 | Normal | NULL | 0 | Cash | 0 | 0 | pack_00002 |

**パターン2: デイリーパック（広告視聴・初回無料）**

| id | product_sub_id | pack_type | tradable_count | cost_type | is_first_time_free | asset_key |
|----|---------------|-----------|----------------|-----------|-------------------|-----------|
| daily_item_pack_1 | 18 | Daily | 2 | Ad | 1 | pack_00004 |

**I18nデータ例**

| id | mst_pack_id | language | name |
|----|------------|----------|------|
| start_chara_pack_1_ja | start_chara_pack_1 | ja | 【お一人様1回まで購入可】\nわくわく アーニャ パック |
| daily_item_pack_1_ja | daily_item_pack_1 | ja | 【毎日更新】\n強化パック |

---

## 設定時のポイント

1. **product_sub_idの事前確認**: 課金パックは `OprProduct` テーブルに対応する製品IDが先に登録されている必要がある
2. **tradable_countの設計**: `Daily` タイプは1日あたりの購入回数、`Normal` タイプは累計購入回数の上限。無制限にする場合は0またはNULLを設定する
3. **is_display_expirationの使い方**: 期間限定パック（イベントパックなど）は `1` に設定し、プレイヤーに期限が近いことを意識させる
4. **sale_conditionはNULLが多数**: 条件なしで常時表示する場合はNULLを設定する
5. **パック名のI18nに注意書きを含める**: 実データでは「【お一人様1回まで購入可】」「【毎日更新】」などの注意書きをパック名に含めており、プレイヤーへの案内として機能している
6. **cost_typeとcost_amountの組み合わせ**: `Cash` タイプの場合、`cost_amount` はストア側の価格設定と連動しているため、`OprProduct` テーブルとの整合性を確認する
7. **MstPackContentも同時に作成する**: パックのデータを作成したら内容物を `MstPackContent` テーブルに追加する
