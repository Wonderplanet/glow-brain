# MstInGameSpecialRule 詳細説明

> CSVパス: `projects/glow-masterdata/MstInGameSpecialRule.csv`

---

## 概要

`MstInGameSpecialRule` は**インゲーム（ステージ・PvP・アドベントバトル）に設定される特殊ルールの定義テーブル**。「このステージはスピードアタックが有効」「このステージはコンティニュー不可」「このPvP期間はHPが+200」といったゲームルールの変更・制約を管理する。

1レコードが1つのルール条件を定義し、同一コンテンツに複数のルールを設定する場合は複数レコードを作成する。

### ゲームプレイへの影響

- **content_type** でルールを適用するコンテンツ種別（Stage / AdventBattle / Pvp）を指定する。
- **target_id** で具体的なステージIDやPvPシーズンIDなど対象を特定する。
- **rule_type** でルールの種類を指定する。
  - `SpeedAttack`: スピードアタックルール（攻撃速度変更）
  - `PartySeries`: 特定シリーズのユニットをパーティに含む条件
  - `NoContinue`: コンティニュー禁止
  - `PartyRoleType`: 特定ロールタイプの条件
  - `UnitStatus`: ユニットのステータス変更（HPや攻撃力のバフ/デバフ）
  - `OutpostHp`: 拠点HPの変更
  - `PartyRarity`: パーティのレアリティ条件
  - `PartyUnitNum`: パーティのユニット数条件
  - `PartyColor`: パーティの属性条件
- **rule_value** でルールの条件値を設定する（ロールタイプ名・属性名・数値など）。
- **start_at / end_at** でルールの有効期間を設定する。

### 関連テーブルとの構造図

```
MstInGameSpecialRule（インゲーム特殊ルール）
  ├─ content_type = Stage → MstStage.id（target_id）
  ├─ content_type = AdventBattle → MstAdventBattle.id（target_id）
  └─ content_type = Pvp → PvPシーズンID（target_id）

MstInGameSpecialRule（rule_type = UnitStatus）
  └─ group_id的な役割 → MstInGameSpecialRuleUnitStatus.group_id（ユニットへの効果詳細）
```

---

## 全カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー。コンテンツとルールを識別する文字列ID |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `content_type` | enum | 不可 | - | インゲームコンテンツ種別（`InGameContentType` enum） |
| `target_id` | varchar(255) | 不可 | - | 対象コンテンツのID（ステージID、PvPシーズンIDなど） |
| `rule_type` | varchar(255) | 不可 | - | ルール種別（`RuleType` enum値の文字列） |
| `rule_value` | varchar(255) | 可 | - | ルール条件値。ルールタイプによりNULL可 |
| `start_at` | timestamp | 不可 | - | ルール有効開始日時 |
| `end_at` | timestamp | 不可 | - | ルール有効終了日時 |

---

## InGameContentType（コンテンツ種別）

| 値 | 説明 |
|----|------|
| `Stage` | 通常ステージ（スパイ・ハンター系等） |
| `AdventBattle` | アドベントバトル |
| `Pvp` | PvP（プレイヤー対戦） |
| `Dungeon` | ダンジョン（スキーマ定義あり、現在CSVでは未使用） |

---

## RuleType（ルール種別）

| 値 | 説明 | rule_value の例 |
|----|------|----------------|
| `SpeedAttack` | スピードアタックルール有効 | NULL（フラグ的な使用） |
| `NoContinue` | コンティニュー禁止 | NULL |
| `PartyRoleType` | パーティにこのロールタイプのユニットを含む条件 | `Attack`, `Defense`, `Support`, `Special` |
| `PartySeries` | パーティにこのシリーズのユニットを含む条件 | シリーズID |
| `PartyRarity` | パーティユニットのレアリティ条件 | `SSR`, `UR` など |
| `PartyUnitNum` | パーティに含めるユニット数の条件 | 数値文字列 |
| `PartyColor` | パーティユニットの属性条件 | 属性名 |
| `OutpostHp` | 拠点のHP設定変更 | HP値（整数文字列） |
| `UnitStatus` | ユニットのステータス変更 | UnitStatusグループID |

---

## 命名規則 / IDの生成ルール

| 種類 | 命名パターン | 例 |
|------|------------|-----|
| id（Stage） | `{stage_id}_{連番2桁}` | `veryhard_spy_00001_01`, `veryhard_spy_00001_02` |
| id（Pvp） | `pvp_{識別子}_{連番}` | `pvp_test`, `pvp_yuw_specialrule_001_1` |

---

## 他テーブルとの連携

| 連携先テーブル | カラム | 関係 |
|-------------|-------|------|
| `mst_stages` | `target_id` → `id` | 対象ステージ（N:1） |
| `mst_in_game_special_rule_unit_statuses` | `rule_value` → `group_id` | UnitStatusルール時のユニット効果詳細（1:N） |

---

## 実データ例

**パターン1: コンティニュー禁止ルール（ステージ）**
```
ENABLE: e
id: veryhard_spy_00001_01
release_key: 202509010
content_type: Stage
target_id: veryhard_spy_00001
rule_type: NoContinue
rule_value: NULL
start_at: 2025-05-01 12:00:00
end_at: 2037-12-31 23:59:59
```
- `veryhard_spy_00001` ステージにコンティニュー禁止を設定
- `rule_value` はNULL（フラグ的な設定）

**パターン2: ロールタイプ条件（ステージ、複数ルール）**
```
ENABLE: e
id: veryhard_spy_00001_02
release_key: 202509010
content_type: Stage
target_id: veryhard_spy_00001
rule_type: PartyRoleType
rule_value: Attack
start_at: 2025-05-01 12:00:00
end_at: 2037-12-31 23:59:59
```
- 同じステージにアタックロールのユニットが必要な条件を追加
- 同一ステージに複数のルールを付与する場合は複数レコードで管理

---

## 設定時のポイント

1. **同一コンテンツへの複数ルール**: 1つのステージに複数のルール（NoContinue + PartyRoleType など）を設定する場合は、同じ `target_id` で複数レコードを作成し、`id` の末尾連番を変える（`_01`, `_02`...）。
2. **rule_value の型変換**: `rule_value` はvarchar型だがサーバー側でルールタイプに応じた型に変換される。`OutpostHp` の場合は整数、`PartySeries` の場合はシリーズIDとして扱われる。
3. **UnitStatus ルールの扱い**: `rule_type = UnitStatus` の場合、`rule_value` に `MstInGameSpecialRuleUnitStatus.group_id` を設定し、そちらに効果の詳細を定義する。
4. **end_at の設定**: 常設ステージのルールは `end_at` を遠い未来（`2037-12-31 23:59:59`）に設定する。期間限定コンテンツは終了日時と合わせる。
5. **release_key**: テスト用レコード（`pvp_test`）は `release_key: 999999999` のような特殊値が設定されている。本番リリースと区別するための慣習。
6. **target_id の存在確認**: `target_id` が対応するコンテンツの実際のIDと一致していることを確認する。不一致の場合、ルールが適用されない。
7. **1139件の大量データ**: ステージ数 × ルール種類の組み合わせで大量レコードになる。新ステージ追加時は同時にこのテーブルも更新が必要なことを意識する。
