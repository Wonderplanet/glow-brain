# MstDungeonCard 詳細説明

> CSVパス: `projects/glow-masterdata/MstDungeonCard.csv`（未作成・将来追加予定）
> i18n CSVパス: `projects/glow-masterdata/MstDungeonCardI18n.csv`（未作成・将来追加予定）

---

## 概要

`MstDungeonCard` は**限界チャレンジ内で入手・使用できるカードの定義テーブル**。

限界チャレンジでは、敵を倒すとルーレットが回転してカードを獲得できる。このカードはユニットに特殊な効果（攻撃バフ等）を付与するアイテムで、同一ダンジョン内での有効なパワーアップ手段となる。カードのレアリティ・アセット・適用効果（`MstAttack` 参照）を管理する。

`mst_dungeon_cards_i18n` テーブルでカード名称・説明文の多言語対応を行う。

2026年3月時点でCSVファイルは未作成（`MstDungeonCard.csv`, `MstDungeonCardI18n.csv` ともに存在しない）。

### ゲームプレイへの影響

- **`rarity`**: カードのレアリティ（N/R/SR/SSR/UR）。レアリティが高いほど強力な効果を持つ
- **`mst_attack_id`**: カードが付与する攻撃効果。`MstAttack` テーブルのエントリを参照し、ルーレットで取得後にユニットへ適用される
- **アセット**: `icon_asset_key`（カードアイコン）と `background_asset_key`（カード背景）でカードのビジュアルを制御

### 関連テーブルとの構造図

```
MstDungeonCard（カード定義）
  └─ id → MstDungeonCardI18n.mst_dungeon_card_id（多言語名称・説明文）
  └─ mst_attack_id → MstAttack.id（カード効果の攻撃定義）
  └─ id → MstDungeonCardGroup.mst_dungeon_card_ids（カード候補グループ内IDリスト）

MstDungeonCardGroup（深度帯別カード候補グループ）
  └─ mst_dungeon_card_ids（カンマ区切りのカードIDリスト）
```

---

## 全カラム一覧

### mst_dungeon_cards カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー。カードID |
| `release_key` | bigint | 不可 | 1 | リリースキー。マスタデータのバージョン管理に使用 |
| `icon_asset_key` | varchar(255) | 不可 | - | カードアイコンのアセットキー |
| `background_asset_key` | varchar(255) | 不可 | - | カード背景のアセットキー |
| `rarity` | enum | 不可 | - | レアリティ。`N` / `R` / `SR` / `SSR` / `UR` の5段階 |
| `mst_attack_id` | varchar(255) | 不可 | - | 適用する攻撃効果のID（`mst_attacks.id`） |

### MstDungeonCardI18n カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `mst_dungeon_card_id` | varchar(255) | 不可 | - | 参照先カードID（`mst_dungeon_cards.id`） |
| `language` | enum | 不可 | - | 言語コード。`ja` のみ対応 |
| `name` | varchar(255) | 不可 | - | カード名称 |
| `description` | varchar(255) | 不可 | - | カード説明文（効果の説明） |

**ユニーク制約**: `(mst_dungeon_card_id, language)` の組み合わせが重複不可

---

## Rarity（レアリティ）

| 値 | 意味 | 特徴 |
|----|------|------|
| `N` | ノーマル | 最も一般的。基本的な効果のカード |
| `R` | レア | やや入手しにくい。強化された効果のカード |
| `SR` | スーパーレア | 希少。さらに強化された効果のカード |
| `SSR` | スーパースーパーレア | 非常に希少。高性能なカード |
| `UR` | ウルトラレア | 最高レアリティ。最強性能のカード |

---

## 他テーブルとの連携

| テーブル | 参照方向 | 用途 |
|---------|---------|------|
| `mst_dungeon_cards_i18n` | `id` ← `mst_dungeon_card_id` | カード名称・説明文の多言語テキスト |
| `mst_attacks` | `mst_attack_id` → `id` | カードが付与する攻撃効果の定義 |
| `mst_dungeon_card_groups` | `id` ← `mst_dungeon_card_ids`（カンマ区切り文字列内） | カードを使用する深度帯別グループ |

---

## 実データ例

> 2026年3月現在、`MstDungeonCard.csv` / `MstDungeonCardI18n.csv` は未作成のため実データは存在しない。
> 以下は想定されるデータ形式の例。

### パターン1: 通常レアリティのカード

```
[MstDungeonCard.csv]
ENABLE: e
id: dungeon_card_normal_attack_01
release_key: 202601010
icon_asset_key: dungeon_card_icon_normal_attack_01
background_asset_key: dungeon_card_bg_r_01
rarity: R
mst_attack_id: attack_dungeon_power_up_01

[MstDungeonCardI18n.csv]
ENABLE: e
id: dungeon_card_normal_attack_01_ja
release_key: 202601010
mst_dungeon_card_id: dungeon_card_normal_attack_01
language: ja
name: 剛力のカード
description: ユニットの攻撃力を一時的に増強する
```

### パターン2: 高レアリティのカード（SSR）

```
[MstDungeonCard.csv]
ENABLE: e
id: dungeon_card_super_attack_01
release_key: 202601010
icon_asset_key: dungeon_card_icon_super_attack_01
background_asset_key: dungeon_card_bg_ssr_01
rarity: SSR
mst_attack_id: attack_dungeon_mega_power_01

[MstDungeonCardI18n.csv]
ENABLE: e
id: dungeon_card_super_attack_01_ja
release_key: 202601010
mst_dungeon_card_id: dungeon_card_super_attack_01
language: ja
name: 覇王のカード
description: 圧倒的な攻撃力でフィールドを制圧する
```

---

## 設定時のポイント

1. **`mst_attack_id` は `mst_attacks` テーブルに存在する有効なIDを設定する**。ダンジョン専用の攻撃効果を新規作成する場合は `MstAttack` への追加が先に必要。

2. **レアリティ別にアセットを分けることを推奨**。背景アセット（`background_asset_key`）にレアリティに応じたビジュアルを設定することで、プレイヤーがカードの価値を視覚的に判断できる。

3. **`MstDungeonCardGroup` にカードIDを登録して初めてゲーム内に出現する**。カードデータを追加しただけではルーレットに出現しない。必ず `MstDungeonCardGroup.mst_dungeon_card_ids` に追加すること。

4. **i18nレコードはカードごとに必ず1レコード（language=ja）を作成する**。i18nが存在しないカードはクライアントで名称表示ができなくなる。

5. **`description` にはプレイヤーが理解できる効果の説明を記述する**。参照先の `MstAttack` の技術的な仕様ではなく、ゲーム内でのわかりやすい説明文を設定すること。

6. **`id` は役割がわかる名前をつけることを推奨**。`dungeon_card_{効果種別}_{連番}` の形式で命名すると管理しやすい。
