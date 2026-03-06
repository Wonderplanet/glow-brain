# MstShopPassEffect 詳細説明

> CSVパス: `projects/glow-masterdata/MstShopPassEffect.csv`

---

## 概要

`MstShopPassEffect` は**ショップパス（サブスクリプション型課金）が提供するゲーム内効果を定義するテーブル**。パス1件に対して複数の効果を設定でき、それぞれの効果タイプと効果量を管理する。

### ゲームへの影響

- パス購入者はパス有効期間中、設定された効果が全て適用される。
- **広告スキップ** (`AdSkip`): インゲームやリザルト画面の広告をスキップできる。
- **放置報酬倍増** (`IdleIncentiveAddReward`): 放置コイン・アイテム等の獲得量が増加する。
- **バトルスピード変更** (`ChangeBattleSpeed`): バトル中の速度変更が解放される。
- **スタミナ回復上限増加** (`StaminaAddRecoveryLimit`): 自動回復するスタミナの上限が増加する。

### テーブル連携図

```
MstShopPass（パス基本設定）
  └─ id → MstShopPassEffect.mst_shop_pass_id（1:N）
              （パス1件に複数の効果を設定）
```

---

## 全カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|---------|-----------|----|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー |
| `mst_shop_pass_id` | varchar(255) | 不可 | - | 対象パスID（`mst_shop_passes.id`） |
| `effect_type` | enum | 不可 | - | 効果タイプ |
| `effect_value` | bigint unsigned | 不可 | 0 | 効果の設定値 |
| `release_key` | bigint | 不可 | 1 | リリースキー |

---

## ShopPassEffectType（enum）

| 値 | 説明 | `effect_value` の意味 |
|----|------|----------------------|
| `IdleIncentiveAddReward` | 放置報酬の上乗せ倍率 | 倍率値（例: `2` = 2倍） |
| `IdleIncentiveMaxQuickReceiveByDiamond` | ダイヤでの即時受け取り最大回数増加 | 増加回数 |
| `IdleIncentiveMaxQuickReceiveByAd` | 広告での即時受け取り最大回数増加 | 増加回数 |
| `StaminaAddRecoveryLimit` | スタミナ自動回復上限の増加量 | 増加するスタミナ量 |
| `AdSkip` | 広告スキップの可否 | `1` = 有効 |
| `ChangeBattleSpeed` | バトルスピード変更の解放 | `1` = 有効 |

---

## 命名規則 / IDの生成ルール

`id` は以下のパターンで命名する:

```
{mst_shop_pass_id}_effect_{連番}
```

例:
- `premium_pass_01_effect_1` → プレミアムパス01の効果1番目
- `premium_pass_01_effect_2` → プレミアムパス01の効果2番目

---

## 他テーブルとの連携

| 連携先テーブル | 結合キー | 用途 |
|-------------|--------|------|
| `MstShopPass` | `MstShopPassEffect.mst_shop_pass_id = MstShopPass.id` | パス基本情報を取得 |

---

## 実データ例

### パターン1: プレミアムパス01の効果設定

```csv
ENABLE,id,mst_shop_pass_id,effect_type,effect_value,release_key
e,premium_pass_01_effect_1,premium_pass_01,AdSkip,1,202509010
e,premium_pass_01_effect_2,premium_pass_01,IdleIncentiveAddReward,2,202509010
```

- `AdSkip` (`effect_value = 1`): 広告スキップ有効
- `IdleIncentiveAddReward` (`effect_value = 2`): 放置報酬2倍

### パターン2: テスト用パスの効果設定

```csv
ENABLE,id,mst_shop_pass_id,effect_type,effect_value,release_key
e,test_premium_pass_01_effect_1,test_premium_pass_01,AdSkip,1,999999999
e,test_premium_pass_01_effect_2,test_premium_pass_01,IdleIncentiveAddReward,2,999999999
```

- `release_key = 999999999` は開発・テスト専用データ

---

## 設定時のポイント

1. **1つのパスに対して複数の効果を設定できる**。現在のプレミアムパスは `AdSkip` と `IdleIncentiveAddReward` の2つが基本セット。
2. **`AdSkip` の `effect_value` は `1`（有効）を設定する**。`0` を設定するとスキップが無効になる。
3. **`effect_value` の意味はeffect_typeごとに異なる**。`IdleIncentiveAddReward` では倍率を、`StaminaAddRecoveryLimit` では増加量を設定するなど、枚挙に留意する。
4. **`mst_shop_pass_id` は `MstShopPass` に存在するIDを参照する**。存在しないIDを参照するとパス購入時に効果が取得できない。
5. **新しいパスを追加する際は `MstShopPass` → `MstShopPassEffect` → `MstShopPassReward` → `MstShopPassI18n` の順に設定する**。各テーブルが連鎖的に依存しているため、親テーブルから順番に作成する。
6. **テスト用パスは `release_key = 999999999` を使用する**。本番環境に誤って配信されないように管理する。
