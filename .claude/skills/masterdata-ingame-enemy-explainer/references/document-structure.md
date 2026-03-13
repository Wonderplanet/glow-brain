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
> 作品名: {MstSeriesI18n.name (language='ja')}

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `{id}` |
| mst_series_id | `{mst_series_id}` |
| 作品名 | {MstSeriesI18n.name (language='ja')} |
| asset_key | `{asset_key}` |
| is_phantomized | `{is_phantomized}` |

---

## 2. キャラクター特徴まとめ

{複数インゲームの使用実態を横断比較して読み取れる傾向を散文形式で記載。
以下の観点を含める:
- どのコンテンツで主に使われているか
- HP・攻撃力のレンジ感（強敵/中程度/雑魚等）
- character_unit_kind・role_typeの傾向（Normal/Boss・Attack/Defense等）
- 変身設定があるかどうか・あればどのような設定か
- よく使われるコマ効果の傾向}

---

## 3. ステージ別使用実態

### {インゲームID}（{コンテンツ種別}）

#### このステージでの役割

{このインゲームにおけるキャラクターの役割を1〜3文で説明。ボス/雑魚/強化版等の位置づけ、難易度への寄与、ステージ特有の意図を記載。}

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `{id}` | {character_unit_kind} | {role_type} | {color} | {hp:,} | {attack_power:,} | {move_speed} | {well_distance} | {damage_knock_back_count} |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `{id}` | {mst_unit_ability_id1} | {transformationConditionType} | {transformationConditionValue} | `{mstTransformationEnemyStageParameterId}` |

> アビリティなし・変身なしの場合は「なし」または空文字と記載。

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| {attack_kind} | {unit_grade} | {killer_colors} | {killer_percentage} | {action_frames} |

> VD専用パラメータ（`_vd_` を含む id）は MstAttack レコードが存在しないため「データなし」と記載。

#### シーケンス設定

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

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| {koma_effect_type} | {count} | {koma_effect_target_side} |

> コマ効果なし（None）は集計から除外。

---

### {インゲームID_2}（{コンテンツ種別}）

（同様の形式で繰り返す）

---

（以降、全ステージ分繰り返す）
````

---

## 記載ルール

- **数値は3桁区切りカンマ**: 例 `12,000`（HP・攻撃力のみ）
- **全行省略なし**: バリエーション一覧は省略せず全行記載
- **コードブロック**: IDは `` ` `` で囲む（例: `` `enemy_kai_00001` ``）
- **該当なしの場合**: 「なし」または「データなし」と記載（セルを空にしない）
- **「このステージでの役割」は必ず冒頭に配置**: 各インゲームサブセクションの最初のセクションは必ず「このステージでの役割」にする
- **作品名はMstSeriesI18n.nameを使う**: 必ず `SELECT name FROM MstSeriesI18n WHERE mst_series_id='{id}' AND language='ja'` で正式名称を引く。通称・略称・カタカナ読みは禁止
- **フィルタが指定された場合**: 全セクションでフィルタ対象データのみ記載する（フィルタ外のパラメータはステータス一覧にも含めない）
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
