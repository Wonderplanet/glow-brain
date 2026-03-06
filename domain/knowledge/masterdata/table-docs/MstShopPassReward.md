# MstShopPassReward 詳細説明

> CSVパス: `projects/glow-masterdata/MstShopPassReward.csv`

---

## 概要

`MstShopPassReward` は**ショップパス購入者に付与される報酬を定義するテーブル**。パス購入時・毎日受け取り可能な報酬の種類・内容・数量を管理する。

1つのパスに対して複数の報酬を設定でき、受け取りタイミング（即時・毎日）ごとに報酬の内容を変えることができる。

### ゲームへの影響

- **即時報酬** (`pass_reward_type = Immediately`): パス購入直後に付与される報酬。
- **デイリー報酬** (`pass_reward_type = Daily`): パス有効期間中、毎日1回受け取れる報酬。パスが切れると受け取れなくなる。
- 報酬内容は `resource_type` と `resource_amount` で定義される。アイテムの場合は `resource_id` も指定する。

### テーブル連携図

```
MstShopPass（パス基本設定）
  └─ id → MstShopPassReward.mst_shop_pass_id（1:N）
              └─ resource_id → MstItem.id（resource_type = Itemのみ）
```

---

## 全カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|---------|-----------|----|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー |
| `mst_shop_pass_id` | varchar(255) | 不可 | - | 対象パスID（`mst_shop_passes.id`） |
| `pass_reward_type` | enum | 不可 | - | 報酬の受け取りタイミング。`Daily` / `Immediately` |
| `resource_type` | enum | 不可 | - | 報酬の種類。`Coin` / `FreeDiamond` / `Item` |
| `resource_id` | varchar(255) | 可（NULL） | - | 報酬アイテムのID（`resource_type = Item` のときのみ） |
| `resource_amount` | bigint unsigned | 不可 | - | 報酬の個数 |
| `release_key` | bigint | 不可 | 1 | リリースキー |

---

## ShopPassRewardType（enum）

| 値 | 説明 |
|----|------|
| `Daily` | 毎日受け取り可能な報酬（パス有効期間中） |
| `Immediately` | パス購入直後に付与される報酬 |

## ResourceType（enum）

| 値 | 説明 |
|----|------|
| `Coin` | コイン |
| `FreeDiamond` | 無償ダイヤ |
| `Item` | アイテム（`resource_id` で指定） |

---

## 命名規則 / IDの生成ルール

`id` は以下のパターンで命名する:

```
{mst_shop_pass_id}_reward_{連番}
```

例:
- `premium_pass_01_reward_1` → プレミアムパス01の報酬1番目
- `premium_pass_02_reward_1` → プレミアムパス02の報酬1番目（デイリーダイヤ）
- `premium_pass_02_reward_2` → プレミアムパス02の報酬2番目（デイリーコイン）

---

## 他テーブルとの連携

| 連携先テーブル | 結合キー | 用途 |
|-------------|--------|------|
| `MstShopPass` | `MstShopPassReward.mst_shop_pass_id = MstShopPass.id` | パス基本情報を取得 |
| `MstItem` | `MstShopPassReward.resource_id = MstItem.id` | アイテム詳細情報を取得（resource_type = Itemのみ） |

---

## 実データ例

### パターン1: プレミアムパス01（デイリーダイヤ報酬のみ）

```csv
ENABLE,id,mst_shop_pass_id,pass_reward_type,resource_type,resource_id,resource_amount,release_key
e,premium_pass_01_reward_1,premium_pass_01,Daily,FreeDiamond,,60,202509010
```

- 毎日60無償ダイヤを受け取れる

### パターン2: プレミアムパス02（ダイヤ+コインのデイリー報酬）

```csv
ENABLE,id,mst_shop_pass_id,pass_reward_type,resource_type,resource_id,resource_amount,release_key
e,premium_pass_02_reward_1,premium_pass_02,Daily,FreeDiamond,,80,202512020
e,premium_pass_02_reward_2,premium_pass_02,Daily,Coin,,8000,202512020
```

- 毎日80無償ダイヤと8000コインの2種類の報酬

### パターン3: プレミアムパス03（ダイヤ+コインのデイリー報酬、アニバ限定）

```csv
ENABLE,id,mst_shop_pass_id,pass_reward_type,resource_type,resource_id,resource_amount,release_key
e,premium_pass_03_reward_1,premium_pass_03,Daily,FreeDiamond,,100,202603020
e,premium_pass_03_reward_2,premium_pass_03,Daily,Coin,,1000,202603020
```

- アニバーサリー限定パスは毎日100ダイヤ+1000コイン

---

## 設定時のポイント

1. **1つのパスに複数の報酬を設定できる**。デイリーダイヤとデイリーコインをセットで設定するケースが多い。
2. **`resource_type = Item` の場合のみ `resource_id` を設定する**。`FreeDiamond` / `Coin` の場合は `resource_id` を NULL にする。
3. **`pass_reward_type = Daily` は毎日受け取り処理が実行される**。パス有効期間中は毎日受け取れるため、1日あたりの報酬量に注意して設定する。
4. **`pass_reward_type = Immediately` は購入時の1回限りの報酬**。購入特典として大量の報酬を設定できる。
5. **`mst_shop_pass_id` は `MstShopPass` に存在するIDを参照する**。`MstShopPass` レコードを先に作成してからこのテーブルを設定する。
6. **報酬量はパスの価格帯・期間と整合性を取る**。25日間パスで毎日60ダイヤなら総計1500ダイヤの価値になるため、他のパス・商品とのバランスを確認する。
7. **新パス追加時は `MstShopPass` → `MstShopPassEffect` → `MstShopPassReward` → `MstShopPassI18n` の順に設定する**。
