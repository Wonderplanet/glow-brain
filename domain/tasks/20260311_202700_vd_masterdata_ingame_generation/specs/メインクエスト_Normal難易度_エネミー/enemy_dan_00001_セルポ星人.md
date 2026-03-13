# セルポ星人（enemy_dan_00001）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: enemy_dan_00001
> mst_series_id: dan
> 作品名: ダンダダン

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `enemy_dan_00001` |
| mst_series_id | `dan` |
| 作品名 | ダンダダン |
| asset_key | `enemy_dan_00001` |
| is_phantomized | `0` |

---

## 2. キャラクター特徴まとめ

セルポ星人はメインクエストNormal難易度においてダンダダン作品の3ステージ（normal_dan_00002〜normal_dan_00006）とコラボクエスト1ステージ（normal_glo2_00001）の計4ステージで使用される。

パラメータは用途によって大きく2種類に分かれる。**Defenseロール（HP 10,000、赤色）**は高HPの壁役として登場し、Darknessコマ解除のトリガーで召喚される特殊なパターンが特徴的。**Attackロール（HP 1,000〜5,000）**は低HP・変身持ち、または大量召喚用の構成で、赤色・無色・青色の3色バリエーションがある。

変身設定を持つパラメータ（`_trans_` 系）はHP 50%で別パラメータへ変身し、より強力な次のフェーズへ移行する設計。normal_dan_00003 では変身後パラメータへの切り替えをトリガーにシーケンスグループを切り替える連動構造が使われている。

コラボクエスト（normal_glo2_00001）では専用の青色パラメータ（HP 5,000, 攻撃力 200）が使われ、30体の大量同時召喚が設定されている。攻撃パターンは全パラメータで共通して近接単体攻撃（効果なし）。

コマ効果はDarknessがnormal_dan_00002で集中して使われ、Poison（Player対象）がnormal_glo2_00001に1件設定されている。

---

## 3. ステージ別使用実態

### normal_dan_00002（メインクエスト Normal）

#### このステージでの役割

ステージの中盤以降に登場するDefenseロールの壁役。DarknessコマがPlayerサイドで解除されるたびに召喚されるトリガー連動型の配置で、プレイヤーのコマ活用を妨害する役割を担う。さらに友軍ユニット撃破後にはシーケンスグループが切り替わり、継続的に複数体が召喚され続ける持久型の構成。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_dan_00001_general_n_Normal_Red` | Normal | Defense | Red | 10,000 | 50 | 34 | 0.24 | （なし） |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_dan_00001_general_n_Normal_Red` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | attack_delay | next_attack_interval |
|------------|-----------|--------------|------------------|--------------|-------------|---------------------|
| Normal | 0 | なし | なし | 58 | 25 | 100 |

**攻撃要素（MstAttackElement）**

| attack_type | range_start | range_end | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|------------|-------------|-----------|------------------|--------|-------------|----------|----------------|-------------|
| Direct | Distance 0 | Distance 0.25 | 1 | Foe/All/All/All | Damage | Normal | Percentage 100% | None |

#### シーケンス設定

```
[elem 1 × 3件] DarknessKomaCleared
  condition_type: DarknessKomaCleared
  condition_value: 3
  action_type: SummonEnemy
  action_value: e_dan_00001_general_n_Normal_Red
  summon_position: 1.6 / 1.7 / 1.8（3件）
  summon_count: 1
  summon_interval: 0
  enemy_hp_coef: 0.55
  sequence_group_id: （なし）

[elem 3] ElapsedTimeSinceSequenceGroupActivated（group1起動後50ms）
  condition_type: ElapsedTimeSinceSequenceGroupActivated
  condition_value: 50
  action_type: SummonEnemy
  action_value: e_dan_00001_general_n_Normal_Red
  summon_position: （なし）
  summon_count: 4
  summon_interval: 100
  enemy_hp_coef: 0.55
  sequence_group_id: group1

[elem 4] ElapsedTimeSinceSequenceGroupActivated（group1起動後500ms）
  condition_type: ElapsedTimeSinceSequenceGroupActivated
  condition_value: 500
  action_type: SummonEnemy
  action_value: e_dan_00001_general_n_Normal_Red
  summon_position: （なし）
  summon_count: 4
  summon_interval: 500
  enemy_hp_coef: 0.55
  sequence_group_id: group1

[elem 5] ElapsedTimeSinceSequenceGroupActivated（group1起動後1500ms）
  condition_type: ElapsedTimeSinceSequenceGroupActivated
  condition_value: 1500
  action_type: SummonEnemy
  action_value: e_dan_00001_general_n_Normal_Red
  summon_position: （なし）
  summon_count: 4
  summon_interval: 750
  enemy_hp_coef: 0.55
  sequence_group_id: group1

[elem 6] FriendUnitDead → SwitchSequenceGroup group1
```

Darknessコマ解除を3回検知するたびに単体召喚（hp_coef 0.55 で弱体化版）、友軍撃破後はgroup1に切り替わり一定間隔で4体ずつ連続召喚される設計。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド |
|-----------|---------|----------|
| Darkness | 3 | Player |

---

### normal_dan_00003（メインクエスト Normal）

#### このステージでの役割

序盤に変身前パラメータ（Colorless, HP 1,000）を単体召喚し、フレンドユニット変身をトリガーとしてシーケンスグループを切り替える構成。変身条件（HP 50%）によりHalf体力を超えたタイミングで `e_dan_00101_general_n_Normal_Colorless` へと変身し、以後のシーケンスでは変身後のキャラが大量召喚される。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_dan_00001_general_n_trans_Normal_Colorless` | Normal | Attack | Colorless | 1,000 | 50 | 34 | 0.24 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_dan_00001_general_n_trans_Normal_Colorless` | なし | HpPercentage | 50 | `e_dan_00101_general_n_Normal_Colorless` |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | attack_delay | next_attack_interval |
|------------|-----------|--------------|------------------|--------------|-------------|---------------------|
| Normal | 0 | なし | なし | 58 | 25 | 100 |

**攻撃要素（MstAttackElement）**

| attack_type | range_start | range_end | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|------------|-------------|-----------|------------------|--------|-------------|----------|----------------|-------------|
| Direct | Distance 0 | Distance 0.25 | 1 | Foe/All/All/All | Damage | Normal | Percentage 100% | None |

#### シーケンス設定

```
[elem 1] ElapsedTime（250ms経過）
  condition_type: ElapsedTime
  condition_value: 250
  action_type: SummonEnemy
  action_value: e_dan_00001_general_n_trans_Normal_Colorless
  summon_position: （なし）
  summon_count: 1
  summon_interval: 0
  enemy_hp_coef: 3
  sequence_group_id: （なし）

[elem 5] FriendUnitTransform → SwitchSequenceGroup group1
（group1ではe_dan_00101変身後キャラが召喚）
```

ゲーム開始から250ms後に1体召喚（hp_coef 3 で大幅強化版）。フレンドユニットが変身したタイミングでシーケンスグループがgroup1に切り替わる。

#### コマ効果

コマ効果なし（全ライン None）。

---

### normal_dan_00006（メインクエスト Normal）

#### このステージでの役割

ステージ開始直後（InitialSummon）に赤色変身型パラメータを1体配置する役割。HP 50%で `e_dan_00101_general_n_Normal_Red` へ変身し、フレンドユニット変身後には `e_dan_00101` が大量召喚される展開となる。このステージでは複数の友軍ボスキャラ（`c_dan_*`、`e_dan_00201`）と共存する大規模編成。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_dan_00001_general_n_trans_Normal_Red` | Normal | Attack | Red | 1,000 | 50 | 34 | 0.24 | （なし） |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_dan_00001_general_n_trans_Normal_Red` | なし | HpPercentage | 50 | `e_dan_00101_general_n_Normal_Red` |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | attack_delay | next_attack_interval |
|------------|-----------|--------------|------------------|--------------|-------------|---------------------|
| Normal | 0 | なし | なし | 58 | 25 | 100 |

**攻撃要素（MstAttackElement）**

| attack_type | range_start | range_end | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|------------|-------------|-----------|------------------|--------|-------------|----------|----------------|-------------|
| Direct | Distance 0 | Distance 0.25 | 1 | Foe/All/All/All | Damage | Normal | Percentage 100% | None |

#### シーケンス設定

```
[elem 1] InitialSummon
  condition_type: InitialSummon
  condition_value: 1
  action_type: SummonEnemy
  action_value: e_dan_00001_general_n_trans_Normal_Red
  summon_position: 1.5
  summon_count: 1
  summon_interval: 0
  enemy_hp_coef: 1.5
  sequence_group_id: （なし）
```

ステージ開始時に位置1.5で単体召喚（hp_coef 1.5）。その後は他キャラが主体となり、フレンドユニット変身後は `e_dan_00101` の3連続召喚に移行する。

#### コマ効果

コマ効果なし（全ライン None）。

---

### normal_glo2_00001（メインクエスト Normal）

#### このステージでの役割

コラボクエスト専用の青色パラメータを使用する大量召喚ステージ。1,300ms経過後に30体を間隔850msで連続召喚するシーンが中心で、通常の general_n 系より攻撃力が高い（200）。他のダンダダンキャラや `e_jig_*`（別作品）キャラとの混成編成で、フレンドユニット撃破5回目に別の `e_jig` ボスが追加召喚される。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_dan_00001_mainquest_glo2_Normal_Blue` | Normal | Attack | Blue | 5,000 | 200 | 25 | 0.2 | 3 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_dan_00001_mainquest_glo2_Normal_Blue` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | attack_delay | next_attack_interval |
|------------|-----------|--------------|------------------|--------------|-------------|---------------------|
| Normal | 0 | なし | なし | 58 | 25 | 110 |

**攻撃要素（MstAttackElement）**

| attack_type | range_start | range_end | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|------------|-------------|-----------|------------------|--------|-------------|----------|----------------|-------------|
| Direct | Distance 0 | Distance 0.21 | 1 | Foe/All/All/All | Damage | Normal | Percentage 100% | None |

#### シーケンス設定

```
[elem 1] ElapsedTime（1300ms経過）
  condition_type: ElapsedTime
  condition_value: 1300
  action_type: SummonEnemy
  action_value: e_dan_00001_mainquest_glo2_Normal_Blue
  summon_position: （なし）
  summon_count: 30
  summon_interval: 850
  enemy_hp_coef: 1.6
  sequence_group_id: （なし）
```

ゲーム開始1,300ms後から30体を850msおきに連続召喚（hp_coef 1.6）。この大量召喚が本ステージの主要な脅威となっている。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド |
|-----------|---------|----------|
| Poison | 1 | Player |
