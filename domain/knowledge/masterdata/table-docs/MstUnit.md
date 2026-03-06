# MstUnit 詳細説明

> CSVパス: `projects/glow-masterdata/MstUnit.csv`
> i18n CSVパス: `projects/glow-masterdata/MstUnitI18n.csv`

---

## 概要

ゲームに登場するキャラ（ユニット）のゲームプレイに関わる全パラメータを管理するテーブル。属性・ロール・レアリティ・ステータス値・召喚コスト・クールタイム・アビリティなど、インゲームで使用されるキャラの基本設定をすべて定義する。多言語テキスト（名前・説明）は `mst_units_i18n` で管理する。

- `mst_units_i18n` テーブルと `id` → `mst_unit_id` で1対多の関係
- アビリティは最大3枠（`mst_unit_ability_id1`〜`3`）設定でき、各アビリティに開放ランクを指定する
- `has_specific_rank_up` が 1 の場合、通常のランクアップテーブルではなくキャラ個別のランクアップ設定を参照する

---

## 全カラム一覧

### MstUnit カラム一覧

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---|---|---|---|---|
| id | varchar(255) | 不可 | - | キャラID（例: `chara_dan_00001`） |
| fragment_mst_item_id | varchar(255) | 不可 | - | このキャラのかけらアイテムID（`mst_items.id`） |
| color | enum | 不可 | `Colorless` | 属性（`CharacterColor` enum参照） |
| role_type | enum | 不可 | - | ロールタイプ（`CharacterUnitRoleType` enum参照） |
| attack_range_type | varchar(255) | 不可 | - | 攻撃射程タイプ（`CharacterAttackRangeType` enum参照） |
| unit_label | enum | 不可 | - | ユニットラベル（`UnitLabel` enum参照） |
| has_specific_rank_up | tinyint | 不可 | `0` | 個別ランクアップ設定を使うか（0: 共通設定、1: 個別設定） |
| mst_series_id | varchar(255) | 不可 | `` | 作品ID（`mst_series.id`） |
| asset_key | varchar(255) | 不可 | `` | アセットキー（Addressables） |
| rarity | enum | 不可 | - | レアリティ（`Rarity` enum参照） |
| sort_order | int unsigned | 不可 | - | ソート順序 |
| summon_cost | int unsigned | 不可 | - | インゲームでの召喚コスト |
| summon_cool_time | int unsigned | 不可 | - | 召喚後のクールタイム（ミリ秒） |
| special_attack_initial_cool_time | int unsigned | 不可 | - | 召喚時の必殺ワザ初期クールタイム（ミリ秒） |
| special_attack_cool_time | int unsigned | 不可 | - | 必殺ワザ使用後のクールタイム（ミリ秒） |
| min_hp | int unsigned | 不可 | - | 基礎HP最小値（レベル1相当） |
| max_hp | int unsigned | 不可 | - | 基礎HP最大値（最大レベル相当） |
| damage_knock_back_count | int unsigned | 不可 | - | HP減少によるノックバック回数 |
| move_speed | decimal(10,2) | 不可 | - | 移動速度 |
| well_distance | double(8,2) | 不可 | - | 索敵距離 |
| min_attack_power | int unsigned | 不可 | - | 攻撃力最小値（レベル1相当） |
| max_attack_power | int unsigned | 不可 | - | 攻撃力最大値（最大レベル相当） |
| mst_unit_ability_id1 | varchar(255) | 不可 | - | アビリティスロット1のID（`mst_unit_abilities.id`） |
| ability_unlock_rank1 | int | 不可 | - | アビリティスロット1の開放ランク |
| mst_unit_ability_id2 | varchar(255) | 不可 | `` | アビリティスロット2のID（空: なし） |
| ability_unlock_rank2 | int | 不可 | - | アビリティスロット2の開放ランク |
| mst_unit_ability_id3 | varchar(255) | 不可 | `` | アビリティスロット3のID（空: なし） |
| ability_unlock_rank3 | int | 不可 | - | アビリティスロット3の開放ランク |
| is_encyclopedia_special_attack_position_right | tinyint unsigned | 不可 | `0` | 図鑑画面で必殺ワザ再生時にキャラを右寄りにするか |
| release_key | int | 不可 | `1` | リリースキー |

### MstUnitI18n カラム一覧

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---|---|---|---|---|
| id | varchar(255) | 不可 | - | UUID（例: `chara_dan_00001_ja`） |
| mst_unit_id | varchar(255) | 不可 | - | 対象キャラID（`mst_units.id`） |
| language | enum | 不可 | `ja` | 言語設定（`ja`・`en`・`zh-Hant`） |
| name | varchar(255) | 不可 | - | キャラ名 |
| description | varchar(255) | 不可 | - | キャラ説明文 |
| detail | varchar(255) | 不可 | `` | キャラの情報詳細（図鑑説明などに使用） |
| release_key | int | 不可 | `1` | リリースキー |

---

## CharacterColor（属性）

| 値 | 説明 |
|---|---|
| `Colorless` | 無属性 |
| `Red` | 赤属性 |
| `Blue` | 青属性 |
| `Yellow` | 黄属性 |
| `Green` | 緑属性 |

---

## CharacterUnitRoleType（ロールタイプ）

| 値 | 説明 |
|---|---|
| `None` | なし |
| `Attack` | アタック（攻撃特化） |
| `Balance` | バランス |
| `Defense` | ディフェンス（耐久特化） |
| `Support` | サポート |
| `Unique` | ユニーク |
| `Technical` | テクニカル |
| `Special` | スペシャル |

---

## Rarity（レアリティ）

| 値 | 説明 |
|---|---|
| `N` | ノーマル |
| `R` | レア |
| `SR` | スーパーレア |
| `SSR` | スーパースーパーレア |
| `UR` | ウルトラレア |

---

## UnitLabel（ユニットラベル）

| 値 | 説明 |
|---|---|
| `DropR` | ドロップ排出 R |
| `DropSR` | ドロップ排出 SR |
| `DropSSR` | ドロップ排出 SSR |
| `DropUR` | ドロップ排出 UR |
| `PremiumR` | プレミアム排出 R |
| `PremiumSR` | プレミアム排出 SR |
| `PremiumSSR` | プレミアム排出 SSR |
| `PremiumUR` | プレミアム排出 UR |
| `FestivalUR` | フェスティバル排出 UR |

---

## 命名規則 / IDの生成ルール

キャラIDは `chara_{シリーズコード}_{5桁連番}` の形式で構成される。

例:
- `chara_dan_00001` → ダンダダン（dan）シリーズ、001番目のキャラ
- `chara_gom_00101` → ゴミ箱（gom）シリーズ、101番目のキャラ

i18n レコードの ID は `{キャラID}_{言語コード}` 形式（例: `chara_dan_00001_ja`）。

---

## 他テーブルとの連携

| 関連テーブル | カラム | 説明 |
|---|---|---|
| `mst_units_i18n` | `id` → `mst_units_i18n.mst_unit_id` | キャラ名・説明の多言語テキスト |
| `mst_items` | `fragment_mst_item_id` → `mst_items.id` | キャラのかけらアイテム |
| `mst_unit_abilities` | `mst_unit_ability_id1/2/3` → `mst_unit_abilities.id` | キャラのアビリティ設定 |
| `mst_series` | `mst_series_id` → `mst_series.id` | 作品シリーズ |
| `mst_unit_rank_ups` / `mst_unit_specific_rank_ups` | `id` → 参照元 | ランクアップ設定 |
| `mst_unit_encyclopedia_effects` | 図鑑ランクごとの効果 | キャラ図鑑報酬のインゲーム効果 |

---

## 実データ例

### 例1: UR キャラ（アタックロール、Middle射程）

```
id              | color | role_type | rarity | unit_label | summon_cost | summon_cool_time | min_hp | max_hp | min_attack_power | max_attack_power | release_key
chara_dan_00002 | Blue  | Attack    | UR     | PremiumUR  | 985         | 1010             | 2760   | 27600  | 4800             | 48000            | 202509010
```

UR のアタックキャラ。召喚コスト985、HP 2760〜27600、攻撃力 4800〜48000。

### 例2: R キャラ（ディフェンスロール）

```
id              | color | role_type | rarity | unit_label | summon_cost | summon_cool_time | min_hp | max_hp | min_attack_power | max_attack_power | release_key
chara_dan_00001 | Red   | Defense   | R      | PremiumR   | 160         | 335              | 910    | 9100   | 320              | 3200             | 202509010
```

R のディフェンスキャラ。UR と比べてステータスが低め。

### 例3: MstUnitI18n（キャラ名・説明）

```
id                  | mst_unit_id     | language | name      | description                        | detail
chara_dan_00001_ja  | chara_dan_00001 | ja       | オカルン  | 幽霊は信じていないが、宇宙人は信じている... | 必殺ワザで自身が受けるダメージをカットできるディフェンスキャラ!
```

---

## 設定時のポイント

1. **min_hp / max_hp・min_attack_power / max_attack_power の比率**: 実データでは `max = min * 10` の関係が成立している（グレード/レベルアップによる成長の基準値）
2. **アビリティスロットが空の場合は空文字**: `mst_unit_ability_id2/3` にアビリティがない場合は NULL ではなく空文字を設定する
3. **ability_unlock_rank は 0 でスタート**: アビリティ開放ランクが 0 の場合はキャラ取得直後から使用可能
4. **fragment_mst_item_id は必須**: キャラのかけらアイテムは必ず `mst_items` テーブルに存在するものを指定する
5. **i18nは全対応言語分設定**: 日本語（`ja`）・英語（`en`）・繁体字中国語（`zh-Hant`）のレコードを全言語分設定する
6. **クライアントクラス（本体）**: `MstUnitData`（`GLOW.Core.Data.Data`名前空間）。全パラメータ（id・roleType・color・rarity・summonCostなど）が配信される
7. **クライアントクラス（i18n）**: `MstUnitI18nData`（`GLOW.Core.Data.Data`名前空間）。`id`・`mstUnitId`・`language`・`name`・`description`・`detail`・`releaseKey` が配信される
8. **sort_order は同シリーズ内での表示順**: 同シリーズのキャラが図鑑などで並ぶ順序を定義する
