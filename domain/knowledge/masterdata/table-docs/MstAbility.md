# MstAbility 詳細説明

> CSVパス: `projects/glow-masterdata/MstAbility.csv`

> i18n CSVパス: `projects/glow-masterdata/MstAbilityI18n.csv`

---

## 概要

ユニットが持つ特性（アビリティ）を管理するテーブル。アビリティとは、ユニットに付与された固有の能力・パッシブ効果のことで、バトル中の特定コマ効果への耐性や、HP条件によるバフ、根性などが含まれる。

各アビリティは `ability_type` によって効果の種類を識別し、`mst_abilities_i18n` テーブルで多言語対応の説明文とフィルタータイトルを管理する。ユニットとアビリティの紐付けは `mst_unit_abilities` テーブルが担う。

---

## 全カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---------|----|----|------|
| ENABLE | varchar | YES | 有効フラグ（`e` = 有効） |
| id | varchar(255) | YES | UUID。アビリティを一意に識別するID |
| ability_type | varchar(255) | YES | アビリティの効果タイプ（後述のenum参照） |
| release_key | bigint | YES | リリースキー。データが有効になるリリースバージョン |
| asset_key | varchar(255) | YES | アセットキー。アビリティアイコン等の表示に使用 |

### MstAbilityI18n カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---------|----|----|------|
| ENABLE | varchar | YES | 有効フラグ（`e` = 有効） |
| id | varchar(255) | YES | UUID。命名規則: `{mst_ability_id}_{language}` |
| mst_ability_id | varchar(255) | YES | 参照先の `mst_abilities.id` |
| language | enum('ja') | YES | 言語設定。現在は `ja`（日本語）のみ |
| description | varchar(255) | YES | アビリティの説明文。`{0}` などのプレースホルダーで数値を埋め込み可能 |
| filter_title | varchar(255) | YES | ユニットソート・フィルター画面に表示するタイトル |
| release_key | bigint | YES | リリースキー |

---

## ability_type（アビリティタイプ）

| ability_type 値 | 説明 |
|----------------|------|
| `AttackPowerUpKomaBoost` | 攻撃UPコマにいる間、攻撃力アップ |
| `SlipDamageKomaBlock` | ダメージコマ効果を無効化 |
| `AttackPowerDownKomaBlock` | 攻撃DOWNコマ効果を無効化 |
| `KnockBackBlock` | ノックバック効果を無効化 |
| `StunBlock` | 一定確率でスタンを無効化 |
| `FreezeBlock` | 一定確率で氷結を無効化 |
| `GustKomaBlock` | 突風コマ効果を無効化 |
| `BurnDamageCut` | 火傷ダメージを軽減 |
| `PoisonDamageCut` | 毒ダメージを軽減 |
| `Guts` | 撃破される時に一定回数だけ体力を1残して耐える |
| `AttackPowerUpByHpPercentageLess` | HPが一定割合以下の時に攻撃力アップ |
| `AttackPowerUpByHpPercentageOver` | HPが一定割合以上の時に攻撃力アップ |
| `DamageCutByHpPercentageLess` | HPが一定割合以下の時にダメージ軽減 |
| `DamageCutByHpPercentageOver` | HPが一定割合以上の時にダメージ軽減 |

---

## 命名規則 / IDの生成ルール

- メインテーブル `id`: `ability_{効果種別}_{詳細}`（例: `ability_attack_power_up_koma_boost`、`ability_StunBlock`）
- i18nテーブル `id`: `{mst_ability_id}_{language}`（例: `ability_attack_power_up_koma_boost_ja`）

---

## 他テーブルとの連携

| テーブル | 関係 | 説明 |
|---------|------|------|
| `mst_unit_abilities` | 1対多 | ユニットとアビリティの紐付け。1つのアビリティを複数ユニットが保有可能 |
| `mst_abilities_i18n` | 1対多 | 多言語テキスト管理。`mst_ability_id` で結合 |

---

## 実データ例

### パターン1: コマ無効化系アビリティ

```
ENABLE: e
id: ability_attack_power_down_koma_block
ability_type: AttackPowerDownKomaBlock
release_key: 202509010
asset_key: attackdown_koma_block
```

```
ENABLE: e
id: ability_attack_power_down_koma_block_ja
mst_ability_id: ability_attack_power_down_koma_block
language: ja
description: 攻撃DOWNコマ効果を無効化
filter_title: 攻撃DOWNコマ無効化
release_key: 202509010
```

### パターン2: 確率発動・数値プレースホルダー系アビリティ

```
ENABLE: e
id: ability_StunBlock
ability_type: StunBlock
release_key: 202509010
asset_key: stun_block
```

```
ENABLE: e
id: ability_StunBlock_ja
mst_ability_id: ability_StunBlock
language: ja
description: {0}%の確率でスタンを無効化
filter_title: スタン攻撃無効化
release_key: 202509010
```

### パターン3: 根性系アビリティ

```
ENABLE: e
id: ability_guts
ability_type: Guts
release_key: 202509010
asset_key: guts
```

```
ENABLE: e
id: ability_guts_ja
mst_ability_id: ability_guts
language: ja
description: 撃破される時に{0}回だけ体力を1残して耐える
filter_title: 根性
release_key: 202509010
```

---

## 設定時のポイント

1. **ability_type は一意**: 現状、1つの `ability_type` に対して1レコードが対応している。同じ効果タイプのアビリティを複数設定することは想定されていない。
2. **descriptionのプレースホルダー**: `{0}` は実際の数値（効果量）で置換される。数値はユニット側（`mst_unit_abilities`）で設定される。
3. **filter_title の簡潔さ**: フィルター表示領域は限られているため、`filter_title` はできるだけ短く、ユーザーに直感的な文言にする。
4. **asset_key の統一**: アセットキーはスネークケースで記述し、アビリティアイコン画像のファイル名に対応する。
5. **release_key の管理**: 新規アビリティ追加時は適切なリリースキーを設定する。既存のアビリティを更新する場合も release_key を変更して管理する。
6. **i18nとのセット作成**: アビリティを新規追加する際は、メインテーブルと同時に i18n テーブルのレコードも必ず作成する。
7. **ENABLE フラグ**: 使用しなくなったアビリティは削除ではなく `ENABLE` を無効にして管理するケースがある点に注意する。
