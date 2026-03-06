# MstShopPass 詳細説明

> CSVパス: `projects/glow-masterdata/MstShopPass.csv`
> i18n CSVパス: `projects/glow-masterdata/MstShopPassI18n.csv`

---

## 概要

`MstShopPass` は**ショップパス（サブスクリプション型の課金商品）の基本設定テーブル**。`mst_shop_passes_i18n` はパス名の多言語設定テーブル。

ショップパスはプレイヤーが課金して購入するサブスクリプション商品で、有効期間中にゲーム内効果と報酬を継続的に提供する。課金商品の実体（価格・販売情報）は `OprProduct` テーブルで管理し、ゲーム内の効果・報酬は `MstShopPassEffect` と `MstShopPassReward` で管理する。

### ゲームへの影響

- **パス有効期間**: `pass_duration_days` で有効日数を設定。購入日から起算してN日間有効。
- **有効期限表示**: `is_display_expiration` でUI上に残り日数を表示するか制御。期間限定パスは表示する。
- **バナー演出**: `shop_pass_cell_color` でショップ画面のパスカードの背景色を変更できる。
- **アセット**: `asset_key` でパスのビジュアル（キービジュアル画像等）を指定する。

### テーブル連携図

```
OprProduct（課金商品設定）
  └─ id → MstShopPass.opr_product_id（1:1）
              ├─ id → MstShopPassI18n.mst_shop_pass_id（多言語名称）
              ├─ id → MstShopPassEffect.mst_shop_pass_id（1:N、ゲーム内効果）
              └─ id → MstShopPassReward.mst_shop_pass_id（1:N、報酬設定）

UsrShopPass（ユーザーのパス購入記録）
  └─ mst_shop_pass_id → MstShopPass.id
```

---

## 全カラム一覧

### mst_shop_passes（本体テーブル）

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|---------|-----------|----|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー |
| `opr_product_id` | varchar(255) | 不可 | - | 課金商品ID（`opr_products.id`） |
| `is_display_expiration` | tinyint unsigned | 不可 | 0 | 有効期限を表示するか。`0` = 非表示、`1` = 表示 |
| `pass_duration_days` | int unsigned | 不可 | - | パスの有効日数 |
| `asset_key` | varchar(255) | 不可 | - | アセットキー |
| `shop_pass_cell_color` | varchar(255) | 不可 | `""` | パスカードの背景色識別子（例: `Gold`, `red`） |
| `release_key` | bigint | 不可 | 1 | リリースキー |

ユニーク制約: `opr_product_id` は一意（1課金商品につき1パスのみ）。

### MstShopPassI18n カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|---------|-----------|----|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー |
| `mst_shop_pass_id` | varchar(255) | 不可 | - | 対応するパスID（`mst_shop_passes.id`） |
| `language` | enum | 不可 | `ja` | 言語設定（現状 `ja` のみ） |
| `name` | varchar(255) | 不可 | - | パス名（例: `プレミアムパス`） |
| `release_key` | bigint | 不可 | 1 | リリースキー |

ユニーク制約: `(mst_shop_pass_id, language)` の組み合わせは一意。

---

## 命名規則 / IDの生成ルール

`id` は以下のパターンで命名する:

```
{パス種別}_{連番2桁}
```

例:
- `premium_pass_01` → プレミアムパス01（常設）
- `premium_pass_02` → 謹賀新年特別パス
- `test_premium_pass_01` → テスト用パス

`MstShopPassI18n.id` は `{mst_shop_pass_id}_{language}` で命名する（例: `premium_pass_01_ja`）。

---

## 実データ例

### パターン1: 常設プレミアムパス

```csv
ENABLE,id,opr_product_id,is_display_expiration,pass_duration_days,asset_key,shop_pass_cell_color,release_key
e,premium_pass_01,17,0,25,premium,Gold,202509010
```

```csv
ENABLE,release_key,id,mst_shop_pass_id,language,name
e,202509010,premium_pass_01_ja,premium_pass_01,ja,プレミアムパス
```

- `is_display_expiration = 0` で有効期限を非表示（常設パスの慣例）
- `pass_duration_days = 25` で25日間有効

### パターン2: 期間限定アニバーサリーパス

```csv
ENABLE,id,opr_product_id,is_display_expiration,pass_duration_days,asset_key,shop_pass_cell_color,release_key
e,premium_pass_03,111,1,30,halfanniv,red,202603020
```

```csv
ENABLE,release_key,id,mst_shop_pass_id,language,name
e,202603020,premium_pass_03_ja,premium_pass_03,ja,アニバ限定 特別パス
```

- `is_display_expiration = 1` で有効期限を表示（期間限定パスの慣例）
- `pass_duration_days = 30` で30日間有効

---

## 設定時のポイント

1. **`opr_product_id` は `OprProduct` テーブルに先に課金商品を登録してから設定する**。課金商品定義が先、パスマスタが後の順番で作成する。
2. **`is_display_expiration` は期間限定パスは `1`（表示）、常設パスは `0`（非表示）に設定するのが慣例**。
3. **`pass_duration_days` は購入日からの有効日数**。例えば25日間パスは購入日を含めて25日間有効。月をまたぐ場合の日数計算に注意する。
4. **`shop_pass_cell_color` はUI実装で対応している値のみ設定可能**。現在使用されている値（`Gold`, `red` 等）をクライアント実装と合わせて確認する。
5. **新パス追加時のセット作業**: `MstShopPass` → `MstShopPassEffect` → `MstShopPassReward` → `MstShopPassI18n` の全テーブルにデータを設定する必要がある。
6. **テスト用パスは `release_key = 999999999` を使用し、ID に `test_` プレフィックスを付ける**。
7. **i18nのパス名はユーザーに表示される名称**。マーケティング部門と連携して命名する。
8. **同一の `opr_product_id` を複数のパスに設定することはできない**（ユニーク制約あり）。古いパスを新しいパスに切り替える場合は `opr_product_id` を新たに払い出す。
