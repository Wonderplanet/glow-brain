# MstArtworkEffect 詳細説明

> CSVパス: `projects/glow-masterdata/MstArtworkEffect.csv`

> i18n CSVパス: `projects/glow-masterdata/MstArtworkEffectI18n.csv`

---

## 概要

原画が持つ効果（バフ効果）を管理するテーブル。各原画にはグレードレベル1〜5に対応した効果値が設定され、グレードアップするごとに効果が強化される仕組みになっている。

効果の種類（`effect_type`）は攻撃力アップ・HP増加・ジャンブルラッシュ強化など複数あり、効果の説明テキストは `mst_artwork_effects_i18n` テーブルで言語別に管理される。また、効果の発動条件は `mst_artwork_effect_activation_rules`、効果の対象は `mst_artwork_effect_target_rules` で定義される。

---

## 全カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---------|----|----|------|
| ENABLE | varchar | YES | 有効フラグ（`e` = 有効） |
| id | varchar(255) | YES | UUID。効果レコードを一意に識別するID（通常は `mst_artwork_id` と同値） |
| mst_artwork_id | varchar(255) | YES | 紐付く原画の `mst_artworks.id` |
| effect_type | varchar(255) | YES | 効果タイプ（後述のリスト参照） |
| grade_level1_value | double | YES | グレードレベル1時の効果値 |
| grade_level2_value | double | YES | グレードレベル2時の効果値 |
| grade_level3_value | double | YES | グレードレベル3時の効果値 |
| grade_level4_value | double | YES | グレードレベル4時の効果値 |
| grade_level5_value | double | YES | グレードレベル5時の効果値 |
| release_key | bigint | YES | リリースキー |

### MstArtworkEffectI18n カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---------|----|----|------|
| ENABLE | varchar | YES | 有効フラグ（`e` = 有効） |
| id | varchar(255) | YES | UUID。命名規則: `{mst_artwork_id}_{language}` |
| mst_artwork_id | varchar(255) | YES | 参照先の `mst_artworks.id` |
| language | enum('ja') | YES | 言語設定。現在は `ja`（日本語）のみ |
| grade_level1_effect_text | varchar(255) | YES | グレードレベル1の効果説明テキスト |
| grade_level2_effect_text | varchar(255) | YES | グレードレベル2の効果説明テキスト |
| grade_level3_effect_text | varchar(255) | YES | グレードレベル3の効果説明テキスト |
| grade_level4_effect_text | varchar(255) | YES | グレードレベル4の効果説明テキスト |
| grade_level5_effect_text | varchar(255) | YES | グレードレベル5の効果説明テキスト |
| release_key | bigint | YES | リリースキー |

---

## effect_type（効果タイプ）

| 値 | 説明 |
|----|------|
| `AttackPowerUp` | 攻撃力アップ |
| `HpUp` | HP（体力）増加 |
| `JumbleRushChargeSpeedUp` | ジャンブルラッシュのチャージスピードアップ |
| `JumbleRushDamageUp` | ジャンブルラッシュのダメージアップ |
| `SpecialAttackChargeSpeedUp` | スペシャルアタックのチャージスピードアップ |
| `ResummonSpeedUp` | 再召喚スピードアップ |
| `InitialLeaderPointUp` | 初期リーダーポイントアップ |

---

## 命名規則 / IDの生成ルール

- メインテーブル `id`: `mst_artwork_id` と同じ値（例: `artwork_spy_0001`）
- i18nテーブル `id`: `{mst_artwork_id}_{language}`（例: `artwork_spy_0001_ja`）

---

## 他テーブルとの連携

| テーブル | 関係 | 説明 |
|---------|------|------|
| `mst_artworks` | 多対1 | 親の原画テーブル。`mst_artwork_id` で参照 |
| `mst_artwork_effects_i18n` | 1対多 | 各グレードレベルの効果テキスト管理 |
| `mst_artwork_effect_activation_rules` | 1対多 | 効果の発動条件定義。`mst_artwork_effect_id` で参照 |
| `mst_artwork_effect_target_rules` | 1対多 | 効果の対象定義。`mst_artwork_effect_id` で参照 |

---

## 実データ例

### パターン1: 攻撃力アップ効果（シリーズ縛りなし）

```
ENABLE: e
id: artwork_spy_0001
mst_artwork_id: artwork_spy_0001
effect_type: AttackPowerUp
grade_level1_value: 0.28
grade_level2_value: 0.55
grade_level3_value: 0.83
grade_level4_value: 1.1
grade_level5_value: 1.65
release_key: 202603020
```

```
ENABLE: e
id: artwork_spy_0001
mst_artwork_id: artwork_spy_0001
language: ja
grade_level1_effect_text: バトル中永続で、ランダムに味方1体の攻撃を0.28%UP
grade_level2_effect_text: バトル中永続で、ランダムに味方1体の攻撃を0.55%UP
grade_level3_effect_text: バトル中永続で、ランダムに味方1体の攻撃を0.83%UP
grade_level4_effect_text: バトル中永続で、ランダムに味方1体の攻撃を1.10%UP
grade_level5_effect_text: バトル中永続で、ランダムに味方1体の攻撃を1.65%UP
release_key: 202603020
```

### パターン2: 攻撃力アップ効果（シリーズキャラ編成条件あり・高効果量）

```
ENABLE: e
id: artwork_spy_0003
mst_artwork_id: artwork_spy_0003
effect_type: AttackPowerUp
grade_level1_value: 2.37
grade_level2_value: 2.67
grade_level3_value: 2.97
grade_level4_value: 3.26
grade_level5_value: 3.56
release_key: 202603020
```

```
ENABLE: e
id: artwork_spy_0003
mst_artwork_id: artwork_spy_0003
language: ja
grade_level1_effect_text: バトル中永続で、『SPY×FAMILY』作品キャラを1体以上編成している場合、ランダムに味方3体の攻撃を2.37%UP
grade_level2_effect_text: バトル中永続で、『SPY×FAMILY』作品キャラを1体以上編成している場合、ランダムに味方3体の攻撃を2.67%UP
grade_level3_effect_text: バトル中永続で、『SPY×FAMILY』作品キャラを1体以上編成している場合、ランダムに味方3体の攻撃を2.97%UP
grade_level4_effect_text: バトル中永続で、『SPY×FAMILY』作品キャラを1体以上編成している場合、ランダムに味方3体の攻撃を3.26%UP
grade_level5_effect_text: バトル中永続で、『SPY×FAMILY』作品キャラを1体以上編成している場合、ランダムに味方3体の攻撃を3.56%UP
release_key: 202603020
```

---

## 設定時のポイント

1. **グレードレベルの段階的増加**: `grade_level1_value` から `grade_level5_value` は必ず昇順で設定する。グレードアップのモチベーションを保つため、各レベル間の増加幅を一定に保つことが望ましい。
2. **効果値の単位**: `effect_type` によって単位が異なる。`AttackPowerUp` や `HpUp` は通常パーセント（%）で表現される。
3. **発動条件との整合性**: 効果テキストには発動条件（シリーズ縛り等）を明記する必要がある。`mst_artwork_effect_activation_rules` に設定した条件と説明テキストの内容が一致していることを確認する。
4. **id と mst_artwork_id の一致**: 通常、`id` と `mst_artwork_id` は同じ値を使用する。これにより原画と効果の1対1対応が直感的になる。
5. **i18nとのセット作成**: 効果を新規追加する際は、メインテーブルと同時に i18n テーブルのレコードも必ず作成する。各グレードレベルのテキストをすべて設定する。
6. **SSR・URの効果量**: 高レアリティの原画（特にシリーズ縛り条件付き）は効果量が大きい傾向がある。バランス調整時は既存データを参照して適切な値を設定する。
