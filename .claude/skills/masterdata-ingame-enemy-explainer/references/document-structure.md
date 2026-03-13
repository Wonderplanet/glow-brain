# 出力MDフォーマットテンプレート

このファイルは `masterdata-ingame-enemy-explainer` スキルが生成するMarkdownの詳細構造を定義します。

---

## ファイル名規則

```
{MstEnemyCharacter.id}_{MstEnemyCharacterI18n.name}.md
```

例: `enemy_kai_00001_怪獣 本獣.md`

---

## テンプレート

````markdown
# {キャラ名}（{mst_enemy_character_id}）詳細解説

> 作成日: {YYYY-MM-DD}
> mst_enemy_character_id: {id}
> mst_series_id: {mst_series_id}

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `{id}` |
| mst_series_id | `{mst_series_id}` |
| asset_key | `{asset_key}` |
| is_phantomized | `{is_phantomized}` |

---

## 2. ステータスバリエーション

### バリエーション一覧

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `{id}` | {character_unit_kind} | {role_type} | {color} | {hp:,} | {attack_power:,} | {move_speed} | {well_distance} | {damage_knock_back_count} |
| ... | ... | ... | ... | ... | ... | ... | ... | ... |

> 数値は3桁区切りカンマ表示。全行省略なし。

### アビリティ・変身設定

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `{id}` | {mst_unit_ability_id1} | {transformationConditionType} | {transformationConditionValue} | `{mstTransformationEnemyStageParameterId}` |

> アビリティなし・変身なしの場合は「なし」または空文字と記載。

### 設定傾向分析

**character_unit_kind別傾向**
- {unit_kind_A}: {傾向の説明}
- {unit_kind_B}: {傾向の説明}

**role_type別傾向**
- {role_A}: {傾向の説明}

**ステータス範囲**
- HP: {min:,} 〜 {max:,}（平均 {avg:,}）
- 攻撃力(attack_power): {min:,} 〜 {max:,}（平均 {avg:,}）
- 移動速度(move_speed): {min} 〜 {max}（平均 {avg}）

**変身設定**
- {変身設定の有無と条件の説明。変身ありの場合は変身後パラメータとの差分も記載。}

---

## 3. 攻撃パターン

### 攻撃一覧（MstAttack）

| パラメータID | attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|------------|-----------|--------------|------------------|--------------|
| `{mst_unit_id}` | {attack_kind} | {unit_grade} | {killer_colors} | {killer_percentage} | {action_frames} |

> 対応するパラメータIDが存在しない場合（VD専用パラメータ等）は「データなし」と記載。

### 攻撃要素詳細（MstAttackElement）

| mst_attack_id | sort_order | attack_type | target | damage_type | power_parameter | effect_type |
|--------------|-----------|------------|--------|------------|----------------|------------|
| `{mst_attack_id}` | {sort_order} | {attack_type} | {target} | {damage_type} | {power_parameter} | {effect_type} |

---

## 4. インゲーム使用実績

### コンテンツ別使用状況

| コンテンツ種別 | ステージ数 |
|-------------|---------|
| VD Normal | {count} |
| VD Boss | {count} |
| メインクエスト Normal | {count} |
| メインクエスト Hard | {count} |
| 降臨バトル | {count} |
| イベント | {count} |
| その他 | {count} |
| **合計** | **{total}** |

### 使用ステージ一覧

| インゲームID | コンテンツ種別 | 使用パラメータID | kind | role | color |
|------------|-------------|--------------|------|------|-------|
| `{ingame_id}` | {content_type} | `{param_id}` | {character_unit_kind} | {role_type} | {color} |
| ... | ... | ... | ... | ... | ... |

---

## 5. 出現シーケンスパターン

### 出現タイミングパターン

| condition_type | 使用回数 | 典型的なパターン |
|--------------|---------|--------------|
| {condition_type} | {count} | {pattern_desc} |
| ... | ... | ... |

### 代表的なシーケンス設定例

以下に、よく使われるパターンを 2〜3 例示します。

**例1: {インゲームID}（{コンテンツ種別}）**

```
condition_type: {condition_type}
condition_value: {condition_value}
sequence_element_id: {sequence_element_id}
action_type: SummonEnemy
action_value: {param_id}
summon_position: {summon_position}
summon_count: {summon_count}
enemy_hp_coef: {enemy_hp_coef}
sequence_group_id: {sequence_group_id}
```

{この設定の特徴を1〜2文で説明}

**例2: {インゲームID}（{コンテンツ種別}）**

（同様の形式で記載）

---

## 6. よく使われるコマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) | 代表ステージ例 |
|-----------|---------|------------------------------|-------------|
| {koma_effect_type} | {count} | {koma_effect_target_side} | `{ingame_id}` |
| ... | ... | ... | ... |

> コマ効果なし（None）は集計から除外。

---

## 7. インゲーム設計ガイド

### このキャラの特徴

{ステータス・行動・使用実績から読み取れる設計特性を散文形式で記載。
以下の観点を含める:
- どのコンテンツで主に使われているか
- HP・攻撃力のレンジ感（強敵/中程度/雑魚等）
- character_unit_kind・role_typeの傾向（Normal/Boss・Attack/Defense等）
- 変身設定があるかどうか・あればどのような設定か}

### 推奨設定パターン

**よく使われる組み合わせ:**

| character_unit_kind | role_type | color | 使用回数 |
|--------------------|----------|-------|---------|
| {unit_kind} | {role_type} | {color} | {count} |

**代表的なシーケンス設定:**

- {シーケンス設定パターンA}: {説明}
- {シーケンス設定パターンB}: {説明}

### 注意点・特記事項

- {変身設定がある場合}: 変身条件（transformationConditionType={condition}）に注意。変身後パラメータ `{mstTransformationEnemyStageParameterId}` のシーケンス登録も必要。
- {VD専用の場合}: `_vd_` を含むパラメータIDに対応する MstAttack レコードは存在しない（設計上の制約）。
- {特殊アビリティがある場合}: mst_unit_ability_id1 `{ability}` の効果に注意。
- {使用実績が少ない場合}: 過去の使用例が少なく、バランス参考値が限定的。
````

---

## 記載ルール

- **数値は3桁区切りカンマ**: 例 `12,000`（HP・攻撃力のみ）
- **全行省略なし**: バリエーション一覧は省略せず全行記載
- **コードブロック**: IDは `` ` `` で囲む（例: `` `enemy_kai_00001` ``）
- **該当なしの場合**: 「なし」または「データなし」と記載（セルを空にしない）
- **コンテンツ種別分類** は以下のルールで判定:

| IDパターン | コンテンツ種別 |
|----------|-------------|
| `vd_%normal%` | VD Normal |
| `vd_%boss%` | VD Boss |
| `normal_%` | メインクエスト Normal |
| `hard_%` | メインクエスト Hard |
| `raid_%` | 降臨バトル |
| `event_%` | イベント |
| その他 | その他 |
