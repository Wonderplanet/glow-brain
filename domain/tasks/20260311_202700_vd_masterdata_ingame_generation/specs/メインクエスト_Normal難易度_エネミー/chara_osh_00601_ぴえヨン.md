# ぴえヨン（chara_osh_00601）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: chara_osh_00601
> mst_series_id: osh
> 作品名: 【推しの子】

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `chara_osh_00601` |
| mst_series_id | `osh` |
| 作品名 | 【推しの子】 |
| asset_key | `chara_osh_00601` |
| is_phantomized | `1` |

---

## 2. キャラクター特徴まとめ

ぴえヨンは【推しの子】シリーズのイベント（event_osh1_charaget02_*）専用キャラクターです。**normalクエストのNormal難易度（`normal_` プレフィックスのステージ）での使用実績はありません。**

パラメータは `Normal`（tan衣装）と `Boss`（huku衣装）の2種類が存在し、HPはどちらも10,000、攻撃力はどちらも300と同値です。role_typeはいずれも Attack で、色は両方 Colorless です。移動速度はNormalが25、Bossが45と、Boss版の方が大幅に速い設定になっています。変身設定はなし。アビリティも設定なし。

イベントステージでは主にNormal版（`c_osh_00601_osh1_boot_tan_Normal_Colorless`）が最初のフレンドユニットとして配置され、フレンドユニット撃破後にBoss版（`c_osh_00601_osh1_boot_huku_Boss_Colorless`）が出現するシーケンスパターンが使われています（event_osh1_charaget02_00003）。コマ効果は全ステージで設定なし（None）です。

---

## 3. ステータス一覧

> ※ コンテンツフィルタ「normalクエストのNormal難易度のみ」を適用した結果、該当使用実績なし。以下はキャラクターが実際に使用されているイベントステージのデータを参考として記載。

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_osh_00601_osh1_boot_tan_Normal_Colorless` | Normal | Attack | Colorless | 10,000 | 300 | 25 | 0.22 | 2 |
| `c_osh_00601_osh1_boot_huku_Boss_Colorless` | Boss | Attack | Colorless | 10,000 | 300 | 45 | 0.22 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_osh_00601_osh1_boot_tan_Normal_Colorless` | なし | None | なし | なし |
| `c_osh_00601_osh1_boot_huku_Boss_Colorless` | なし | None | なし | なし |

---

## 4. ステージ別使用実態

### event_osh1_charaget02_00001（イベント）

#### このステージでの役割

ぴえヨン（Normal版）が単独フレンドユニットとして登場するイベントステージ。InitialSummonで1体のみ召喚される最もシンプルな構成で、HP係数8倍設定により耐久力が大きく強化されている。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_osh_00601_osh1_boot_tan_Normal_Colorless` | Normal | Attack | Colorless | 10,000 | 300 | 25 | 0.22 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_osh_00601_osh1_boot_tan_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

**c_osh_00601_osh1_boot_tan_Normal_Colorless（Normal衣装）**

| attack_id | attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | attack_delay | next_attack_interval |
|-----------|------------|-----------|--------------|------------------|--------------|-------------|-------------------|
| `c_osh_00601_osh1_boot_tan_Normal_Colorless_Normal_00000` | Normal | 0 | なし | なし | 55 | 0 | 80 |

**MstAttackElement（Normal_00000）**

| sort_order | attack_type | range_start | range_end | max_target | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|-------------|-----------|-----------|--------|-------------|----------|----------------|-------------|
| 1 | Direct | Distance 0 | Distance 0.3 | 1 | Foe/All | Damage | Normal | Percentage 50.0 | None |
| 2 | Direct | Distance 0 | Distance 0.3 | 1 | Foe/All | Damage | Normal | Percentage 50.0 | None |

#### シーケンス設定

```
sequence_element_id: 1
condition_type: InitialSummon
condition_value: （なし）
action_type: SummonEnemy
action_value: c_osh_00601_osh1_boot_tan_Normal_Colorless
summon_position: 0.8
summon_count: 1
summon_interval: （なし）
enemy_hp_coef: 8
sequence_group_id: （なし）
```

InitialSummon（ステージ開始時）に位置0.8に1体だけ召喚。enemy_hp_coef=8でHP大幅強化。

#### コマ効果

コマ効果なし（全コマでNone）。

---

### event_osh1_charaget02_00002（イベント）

#### このステージでの役割

ぴえヨン（Normal版）をフレンドユニットとして配置しつつ、経過時間後にファントム（`e_glo_00001_osh1_boot_Normal_Colorless`）を大量召喚する2段構成のイベントステージ。前ステージよりHP係数が上昇しており、難易度が高い。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_osh_00601_osh1_boot_tan_Normal_Colorless` | Normal | Attack | Colorless | 10,000 | 300 | 25 | 0.22 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_osh_00601_osh1_boot_tan_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

（event_osh1_charaget02_00001と同様のため省略）

Normal版: Normal攻撃のみ、Direc t近接2ヒット、Percentage 50.0×2。

#### シーケンス設定

```
sequence_element_id: 1
condition_type: InitialSummon
condition_value: （なし）
action_type: SummonEnemy
action_value: c_osh_00601_osh1_boot_tan_Normal_Colorless
summon_position: 0.8
summon_count: 1
summon_interval: （なし）
enemy_hp_coef: 10
sequence_group_id: （なし）

sequence_element_id: 2
condition_type: ElapsedTime
condition_value: 400
action_type: SummonEnemy
action_value: e_glo_00001_osh1_boot_Normal_Colorless
summon_position: （なし）
summon_count: 10
summon_interval: 800
enemy_hp_coef: 0.6
sequence_group_id: （なし）
```

InitialSummonでぴえヨン（HP係数10倍）、ElapsedTime 400フレーム後にファントム10体を間隔800フレームで追加召喚。

#### コマ効果

コマ効果なし（全コマでNone）。

---

### event_osh1_charaget02_00003（イベント）

#### このステージでの役割

最も複雑な構成のイベントステージ。Normal版ぴえヨンをフレンドユニットとして配置し、フレンドユニット撃破（FriendUnitDead）をトリガーにBoss版ぴえヨン（huku衣装）が出現するシーケンスグループ切り替えパターンを採用。Boss版はHP係数14倍で登場し、ファントムの大量波状召喚も組み合わさった高難度構成。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_osh_00601_osh1_boot_tan_Normal_Colorless` | Normal | Attack | Colorless | 10,000 | 300 | 25 | 0.22 | 2 |
| `c_osh_00601_osh1_boot_huku_Boss_Colorless` | Boss | Attack | Colorless | 10,000 | 300 | 45 | 0.22 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_osh_00601_osh1_boot_tan_Normal_Colorless` | なし | None | なし | なし |
| `c_osh_00601_osh1_boot_huku_Boss_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

**c_osh_00601_osh1_boot_huku_Boss_Colorless（Boss衣装）**

| attack_id | attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | attack_delay | next_attack_interval |
|-----------|------------|-----------|--------------|------------------|--------------|-------------|-------------------|
| `c_osh_00601_osh1_boot_huku_Boss_Colorless_Appearance_00002` | Appearance | 0 | なし | なし | 50 | 0 | 0 |
| `c_osh_00601_osh1_boot_huku_Boss_Colorless_Normal_00000` | Normal | 0 | なし | なし | 55 | 0 | 60 |
| `c_osh_00601_osh1_boot_huku_Boss_Colorless_Special_00001` | Special | 0 | なし | なし | 95 | 20 | 0 |

**MstAttackElement（Appearance_00002）**

| sort_order | attack_type | range_start | range_end | max_target | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|-------------|-----------|-----------|--------|-------------|----------|----------------|-------------|
| 1 | Direct | Distance 0 | Distance 50.0 | 100 | Foe/All | None | ForcedKnockBack5 | Percentage 100.0 | None |

> 出現時に全範囲（Distance 50.0）の敵最大100体に強制ノックバック5を付与する登場演出攻撃。

**MstAttackElement（Normal_00000）**

| sort_order | attack_type | range_start | range_end | max_target | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|-------------|-----------|-----------|--------|-------------|----------|----------------|-------------|
| 1 | Direct | Distance 0 | Distance 0.3 | 1 | Foe/All | Damage | Normal | Percentage 50.0 | None |
| 2 | Direct | Distance 0 | Distance 0.3 | 1 | Foe/All | Damage | Normal | Percentage 50.0 | None |

**MstAttackElement（Special_00001）**

| sort_order | attack_type | range_start | range_end | max_target | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|-------------|-----------|-----------|--------|-------------|----------|----------------|-------------|
| 1 | Direct | Distance 0 | Distance 0.5 | 1 | Foe/All | Damage | Normal | Percentage 85.0 | None |
| 2 | Direct | Distance 0 | Distance 0.5 | 1 | Foe/All | Damage | Normal | Percentage 85.0 | None |
| 3 | Direct | Distance 0 | Distance 0.5 | 1 | Foe/All | Damage | Normal | Percentage 85.0 | None |
| 4 | Direct | Distance 0 | Distance 0.5 | 1 | Foe/All | Damage | Normal | Percentage 85.0 | None |

#### シーケンス設定

**デフォルトグループ（sequence_group_id: なし）**

```
sequence_element_id: 1
condition_type: InitialSummon
condition_value: （なし）
action_type: SummonEnemy
action_value: c_osh_00601_osh1_boot_tan_Normal_Colorless
summon_position: 0.8
summon_count: 1
summon_interval: （なし）
enemy_hp_coef: 11
sequence_group_id: （なし）

sequence_element_id: 2
condition_type: ElapsedTime
condition_value: 400
action_type: SummonEnemy
action_value: e_glo_00001_osh1_boot_Normal_Colorless
summon_position: 0.65
summon_count: 3
summon_interval: 10
enemy_hp_coef: 1.5
sequence_group_id: （なし）

sequence_element_id: 3
condition_type: ElapsedTime
condition_value: 1500
action_type: SummonEnemy
action_value: e_glo_00001_osh1_boot_Normal_Colorless
summon_position: （なし）
summon_count: 50
summon_interval: 700
enemy_hp_coef: 1.5
sequence_group_id: （なし）

sequence_element_id: groupchange_1
condition_type: FriendUnitDead
condition_value: 1
action_type: SwitchSequenceGroup
action_value: w1
sequence_group_id: （なし）
```

**グループw1（FriendUnitDead後に切り替え）**

```
sequence_element_id: 101
condition_type: ElapsedTimeSinceSequenceGroupActivated
condition_value: 350
action_type: SummonEnemy
action_value: c_osh_00601_osh1_boot_huku_Boss_Colorless
summon_position: （なし）
summon_count: 1
summon_interval: （なし）
enemy_hp_coef: 14
sequence_group_id: w1

sequence_element_id: 102
condition_type: ElapsedTimeSinceSequenceGroupActivated
condition_value: 1
action_type: SummonEnemy
action_value: e_glo_00001_osh1_boot_Normal_Colorless
summon_position: （なし）
summon_count: 1
summon_interval: （なし）
enemy_hp_coef: 1.5
sequence_group_id: w1

sequence_element_id: 103
condition_type: ElapsedTimeSinceSequenceGroupActivated
condition_value: 25
action_type: SummonEnemy
action_value: e_glo_00001_osh1_boot_Normal_Colorless
summon_position: （なし）
summon_count: 1
summon_interval: （なし）
enemy_hp_coef: 1.5
sequence_group_id: w1

sequence_element_id: 104
condition_type: ElapsedTimeSinceSequenceGroupActivated
condition_value: 50
action_type: SummonEnemy
action_value: e_glo_00001_osh1_boot_Normal_Colorless
summon_position: （なし）
summon_count: 1
summon_interval: （なし）
enemy_hp_coef: 1.5
sequence_group_id: w1

sequence_element_id: 105
condition_type: ElapsedTimeSinceSequenceGroupActivated
condition_value: 801
action_type: SummonEnemy
action_value: e_glo_00001_osh1_boot_Normal_Colorless
summon_position: （なし）
summon_count: 20
summon_interval: 600
enemy_hp_coef: 1.5
sequence_group_id: w1

sequence_element_id: 106
condition_type: ElapsedTimeSinceSequenceGroupActivated
condition_value: 826
action_type: SummonEnemy
action_value: e_glo_00001_osh1_boot_Normal_Colorless
summon_position: （なし）
summon_count: 20
summon_interval: 600
enemy_hp_coef: 1.5
sequence_group_id: w1

sequence_element_id: 107
condition_type: ElapsedTimeSinceSequenceGroupActivated
condition_value: 851
action_type: SummonEnemy
action_value: e_glo_00001_osh1_boot_Normal_Colorless
summon_position: （なし）
summon_count: 20
summon_interval: 600
enemy_hp_coef: 1.5
sequence_group_id: w1
```

フレンドユニット（Normal版ぴえヨン）が撃破されるとシーケンスグループがw1に切り替わり、350フレーム後にBoss版ぴえヨン（HP係数14倍）が1体出現。同時にファントムを波状で大量召喚（最大60体超）する高圧力構成。

#### コマ効果

コマ効果なし（全コマでNone）。

---

## 5. 補足情報

### normalクエストでの使用実績

コンテンツフィルタ「normalクエストのNormal難易度のみ」を適用した結果、chara_osh_00601（ぴえヨン）は **`normal_` プレフィックスのメインクエストステージでは一切使用されていません。** 全使用実績はイベントステージ（event_osh1_charaget02_00001〜00003）のみです。

### コンテンツ別使用実績サマリー

| コンテンツ種別 | ステージ数 |
|-------------|---------|
| イベント | 3 |
| メインクエスト Normal | 0 |
| メインクエスト Hard | 0 |
| VD Normal | 0 |
| VD Boss | 0 |
| 降臨バトル | 0 |
