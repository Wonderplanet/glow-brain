# MstInGameSpecialRuleUnitStatus 詳細説明

> CSVパス: `projects/glow-masterdata/MstInGameSpecialRuleUnitStatus.csv`

---

## 概要

`MstInGameSpecialRuleUnitStatus` は**インゲーム特殊ルールの「UnitStatus（ユニットステータス変更）」ルールの詳細設定テーブル**。`MstInGameSpecialRule` で `rule_type = UnitStatus` と設定されたルールに対して、どのユニット対象にどのステータスをどれだけ変更するかを定義する。

`group_id` でグループ化され、`MstInGameSpecialRule.rule_value` にこの `group_id` を設定することで参照される。

### ゲームプレイへの影響

- **target_type** でバフ/デバフの対象範囲を指定する（全ユニット / 特定ユニット / ロール / 属性 / シリーズなど）。
- **target_value** で `target_type` に応じた対象IDや名称を指定する（`All` の場合はNULL）。
- **status_parameter_type** でどのステータスを変更するかを指定する（HP / 攻撃力 / スペシャルクールタイム / 召喚クールタイム）。
- **effect_value** でステータスの変更量（加算値）を指定する。正の値でバフ、負の値でデバフになる。

### 関連テーブルとの構造図

```
MstInGameSpecialRule（rule_type = UnitStatus）
  └─ rule_value → MstInGameSpecialRuleUnitStatus.group_id（1:N）
         └─ target_type = Series → MstSeries（target_value でシリーズ識別）
         └─ target_type = Unit → MstUnit（target_value でユニットID指定）
```

---

## 全カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `id` | varchar(255) | 不可 | - | 主キー（整数連番） |
| `group_id` | varchar(255) | 不可 | - | グループID。`MstInGameSpecialRule.rule_value` と対応する |
| `target_type` | varchar(255) | 不可 | - | 効果を適用するユニットのターゲット種別（`InGameSpecialRuleUnitStatusTargetType` enum値） |
| `target_value` | varchar(255) | 不可 | - | ターゲット種別に応じた対象識別値。`All` の場合はNULL |
| `status_parameter_type` | varchar(255) | 不可 | - | 変更するステータスの種類（`InGameSpecialRuleUnitStatusParameterType` enum値） |
| `effect_value` | int | 不可 | - | ステータスの変更量（正: バフ、負: デバフ） |

---

## InGameSpecialRuleUnitStatusTargetType（ターゲット種別）

| 値 | 説明 | target_value |
|----|------|--------------|
| `All` | 全ユニットに適用 | NULL |
| `Unit` | 特定のユニットに適用 | `mst_units.id` |
| `CharacterUnitRoleType` | 特定ロールタイプのユニットに適用 | ロールタイプ名 |
| `CharacterColor` | 特定属性（カラー）のユニットに適用 | 属性名 |
| `Series` | 特定シリーズのユニットに適用 | シリーズID文字列 |

---

## InGameSpecialRuleUnitStatusParameterType（ステータス種別）

| 値 | 説明 |
|----|------|
| `Hp` | HPの変更（加算値） |
| `AttackPower` | 攻撃力の変更（加算値） |
| `SpecialAttackCoolTime` | スペシャルアタックのクールタイム変更 |
| `SummonCoolTime` | 召喚クールタイムの変更 |

---

## 命名規則 / IDの生成ルール

| 種類 | 命名パターン | 例 |
|------|------------|-----|
| id | 整数連番（全テーブルで通し番号） | `1`, `2`, `53` |
| group_id（PvP） | `pvp_{シーズン識別子}_specialrule_{連番}_{グループ番号}` | `pvp_yuw_specialrule_001_1` |
| group_id（Stage/Event） | `quest_{コンテンツ識別子}_{ステータス名}` | `quest_event_hut1_Hp` |

---

## 他テーブルとの連携

| 連携先テーブル | カラム | 関係 |
|-------------|-------|------|
| `mst_in_game_special_rules` | `group_id` → `rule_value` | 参照元の特殊ルール（N:1） |
| `mst_units` | `target_value` → `id` | Unit指定時のユニット参照（N:1） |

---

## 実データ例

**パターン1: PvP全ユニットHPバフ**
```
ENABLE: e
release_key: 202511020
id: 1
group_id: pvp_yuw_specialrule_001_1
target_type: All
target_value: NULL
status_parameter_type: Hp
effect_value: 100
```
- PvP「yuw」シーズンの特殊ルール
- 全ユニットのHPが+100
- `MstInGameSpecialRule.rule_value = 'pvp_yuw_specialrule_001_1'` で参照される

**パターン2: シリーズ別HP・攻撃力バフ**
```
ENABLE: e
release_key: 202603010
id: 7
group_id: quest_event_hut1_Hp
target_type: Series
target_value: hut
status_parameter_type: Hp
effect_value: 20
```
```
ENABLE: e
release_key: 202603010
id: 8
group_id: quest_event_hut1_AttackPower
target_type: Series
target_value: hut
status_parameter_type: AttackPower
effect_value: 20
```
- イベント「hut」シリーズのユニット限定バフ
- HP+20、攻撃力+20の複数ステータスバフを別グループIDで管理

---

## 設定時のポイント

1. **MstInGameSpecialRule との対応**: このテーブルの `group_id` が `MstInGameSpecialRule.rule_value` として参照される。`MstInGameSpecialRule` に `rule_type = UnitStatus` のレコードを先に作成し、`rule_value` に `group_id` を設定する。
2. **target_type = All のとき target_value はNULL**: `All` を指定するときは `target_value` をNULL（CSVでは空欄）にする。
3. **複数ステータスのバフは別グループIDで管理**: HP と AttackPower の両方にバフを設定したい場合、同一グループIDに複数レコードを持つか、ステータス別に別グループIDを作成するかは設計次第。現状は別グループIDで管理するパターンが多い。
4. **effect_value の符号**: 正の値（+100）はバフ（強化）、負の値（-100）はデバフ（弱化）になる。PvPでは全ユニットHPを底上げするためにバフが使われる。
5. **group_id の命名**: PvPは `pvp_{シーズン}_{連番}_{グループ番号}` 形式、ステージ/イベントは `quest_{識別子}_{ステータス名}` 形式で命名する慣習。
6. **id の整数連番**: 全レコードを通して連番で採番する。新規追加は最大IDの次を使用する。
7. **シリーズIDの確認**: `target_type = Series` の場合、`target_value` に設定するシリーズIDが `mst_series.id` と一致することを確認する。
