---
name: vd-masterdata-ingame-enemy-action-designer
description: VDインゲーム設計書（design.md）の「敵キャラ行動パターン設計」セクションを生成・更新するスキル。引数で渡されたキャラIDをもとにMstAttack/MstAttackElementの攻撃種別・効果・対象・ダメージ種別を設計します。「VD行動パターン設計」「敵キャラ攻撃設計」「enemy-action」などのキーワードで使用します。
---

# VD敵キャラ行動パターン設計スキル

## 概要

VDインゲーム設計書（design.md）の **`#### 行動パターン（MstAttack / MstAttackElement）`** セクションを生成・更新する専門スキル。

- **担当セクション**: `## レベルデザイン > ### 敵キャラ設計 > #### 行動パターン`
- **キャラ選定は行わない**: 引数で渡されたキャラIDを前提とする

---

## 入力引数

| 引数 | 必須 | 説明 |
|------|------|------|
| `作品ID` | ✓ | kai / dan / spy 等 |
| `キャラリスト` | ✓ | キャラIDのリスト（前ステップ・enemy-stats-designerの結果） |
| `[対抗キャラID]` | 任意 | 対抗キャラの能力に合わせたダメージ種別選択のため |
| `[--batch]` | 任意 | 確認ループをスキップ |

---

## 3ステップワークフロー

### Step 0: 準備・ドキュメント読み込み

以下を確認・読み込む。

**テーブル詳細ドキュメント（必須）**:
- `domain/knowledge/masterdata/table-docs/MstEnemyStageParameter.md`
- `domain/knowledge/masterdata/table-docs/MstAttack.md`
- `domain/knowledge/masterdata/table-docs/MstAttackElement.md`

**メインクエスト攻撃パターン実績（キャラが存在すれば）**:
- `domain/tasks/20260311_202700_vd_masterdata_ingame_generation/specs/メインクエスト_Normal難易度_エネミー/` 配下のMDファイルを確認
  - ファイル命名: `{MstEnemyCharacter.id}_{MstEnemyCharacterI18n.name}.md`
  - 対象キャラIDのファイルが存在すれば Read tool で読み込み、攻撃パターン実績（attack_kind・damage_type・effect_type 等）を参考にする

### Step 1: 行動パターン設計

引数のキャラIDごとに攻撃パターンを設計する。

#### 主要な設計項目

| 項目 | 選択肢 | 設計方針 |
|------|--------|---------|
| `attack_kind` | `Normal` / `Special` / `Ultimate` | 基本は `Normal`、特殊攻撃は `Special` |
| `damage_type` | `None` / `Poison` / `Burn` / `Slip` 等 | 対抗キャラの軽減システムと連動させる |
| `effect_type` | `None` / `AttackPowerDown` / `MoveSpeedDown` 等 | 難易度調整に使用 |
| `target_type` | `Single` / `All` / `Random` 等 | 攻撃対象の選び方 |
| `range` | 攻撃射程 | 通常は `1.0`〜`3.0` |

#### 対抗キャラ弱点との連動

対抗キャラIDが渡された場合:
1. `domain/tasks/20260311_202700_vd_masterdata_ingame_generation/specs/MstUnitAbility_特性とインゲームコマ効果_網羅解説.md` を参照
2. 対抗キャラの `ability_type` を確認
3. 以下の対応で `damage_type` を選択（対抗キャラが軽減できるダメージを敵に持たせる）:
   - `PoisonDamageCut` → `damage_type=Poison`
   - `BurnDamageCut` → `damage_type=Burn`
   - `SlipDamageKomaBlock` → `damage_type=Slip`

### Step 2: 設計テーブル生成

以下のMarkdownテーブルを生成する。

```markdown
#### 行動パターン（MstAttack / MstAttackElement）

| 敵キャラID | 攻撃名 | 攻撃種別 | ダメージ種別 | 効果 | 対象 | 射程 | 備考 |
|-----------|-------|---------|-----------|------|------|------|------|
| {キャラID} | {攻撃名} | {attack_kind} | {damage_type} | {effect_type} | {target_type} | {range} | {備考} |
```

### Step 3: 確認・更新

`--batch` フラグがない場合:
```
敵キャラ行動パターンを設計しました。内容をご確認ください。

修正がなければ「OK」または「承認」とお伝えください。
修正がある場合は具体的にご指示ください。
```

承認後（または `--batch` 時）、design.md の該当セクションを更新する。

---

## ガードレール

1. **キャラ選定は引数のみ**: 引数に含まれないキャラIDの攻撃パターンを追加しない
2. **既存攻撃パターンの実績を優先**: メインクエスト実績がある場合は参考値として使用する
3. **対抗キャラとの連動は任意**: 対抗キャラIDが渡されない場合はスキップ
4. **attack_kind・damage_typeはenum値のみ**: MstAttack.md / MstAttackElement.md のenum値以外を使用しない

---

## リファレンス

- `domain/knowledge/masterdata/table-docs/MstEnemyStageParameter.md` — テーブル定義
- `domain/knowledge/masterdata/table-docs/MstAttack.md` — 攻撃テーブル定義
- `domain/knowledge/masterdata/table-docs/MstAttackElement.md` — 攻撃要素テーブル定義
- `domain/tasks/20260311_202700_vd_masterdata_ingame_generation/specs/メインクエスト_Normal難易度_エネミー/` — 攻撃パターン実績参照
- `domain/tasks/20260311_202700_vd_masterdata_ingame_generation/specs/MstUnitAbility_特性とインゲームコマ効果_網羅解説.md` — 対抗キャラ能力対応一覧
