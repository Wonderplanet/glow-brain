# つらら（enemy_mag_00101）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: enemy_mag_00101
> mst_series_id: mag
> 作品名: 株式会社マジルミエ

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `enemy_mag_00101` |
| mst_series_id | `mag` |
| 作品名 | 株式会社マジルミエ |
| asset_key | `enemy_mag_00101` |
| is_phantomized | `0` |

> 説明: 冷却系怪異が放ったつらら。

---

## 2. キャラクター特徴まとめ

つららは株式会社マジルミエシリーズのメインクエスト Normal 難易度専用の敵キャラクターで、全ステージにわたって `character_unit_kind=Normal` / `role_type=Attack` または `Technical` のバリエーションで登場する。

**HP・攻撃力のレンジ感:**
- `general` バリアント（初期難易度向け）: HP 10,000〜20,000、攻撃力 800〜1,500 の雑魚〜中程度の強さ
- `general2` バリアント（中盤向け）: HP 20,000〜30,000、攻撃力 400〜700 とHP高めだが攻撃力は低め
- `general_as4` バリアント（高難易度向け）: HP 200,000、攻撃力 1,500 と突出して高いHP を持つ強敵。`role_type=Technical` で動作が異なる

**変身設定:** 全バリアントで変身設定なし（`transformationConditionType=None`）

**出現パターンの特徴:** 全ステージで `condition_type=FriendUnitDead`（味方ユニット撃破数トリガー）での召喚が基本。大量召喚（`summon_count=99`、`summon_interval=300〜780`）でウェーブ形式に継続出現するケースが多い。

**コマ効果:** 対象全ステージでコマ効果は全て `None`（コマ効果なし）。

---

## 3. ステージ別使用実態

---

### normal_glo4_00002（メインクエスト Normal）

#### このステージでの役割

`general_as4` バリアントが使用される高難易度ステージ。HP 200,000・`role_type=Technical` のつららが Blue/Colorless の2色で複数体配置される強敵枠として機能する。味方ユニット2〜3体撃破をトリガーに複数波に分けて召喚され、中盤以降の防衛を担うキーエネミー。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_mag_00101_general_as4_Normal_Blue` | Normal | Technical | Blue | 200,000 | 1,500 | 75 | 0.11 | 1 |
| `e_mag_00101_general_as4_Normal_Colorless` | Normal | Technical | Colorless | 200,000 | 1,500 | 75 | 0.11 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_mag_00101_general_as4_Normal_Blue` | なし | None | なし | なし |
| `e_mag_00101_general_as4_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 19 |

**MstAttackElement（e_mag_00101_general_as4_Normal_Blue_Normal_00000）:**

| sort_order | attack_type | range_start_type | range_end_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|-------------|-----------------|---------------|---------------------|-----------------|--------|-------------|----------|----------------|-------------|
| 1 | Direct | Distance(0) | Distance | 0.3 | 1 | Foe | Damage | Freeze | 100.0% | None |
| 2 | Direct | Distance(0) | Distance | 0.3 | 1 | Self | Damage | Normal | 99,999% | None |

**MstAttackElement（e_mag_00101_general_as4_Normal_Colorless_Normal_00000）:**

| sort_order | attack_type | range_start_type | range_end_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|-------------|-----------------|---------------|---------------------|-----------------|--------|-------------|----------|----------------|-------------|
| 1 | Direct | Distance(0) | Distance | 0.3 | 1 | Foe | Damage | Normal | 100.0% | None |
| 2 | Direct | Distance(0) | Distance | 0.3 | 1 | Self | Damage | Normal | 99,999% | None |

> Blueバリアントは hit_type=Freeze（凍結攻撃）で Colorless バリアントとは攻撃性質が異なる。

#### シーケンス設定

```
condition_type: FriendUnitDead
condition_value: 2
sequence_element_id: 4
action_type: SummonEnemy
action_value: e_mag_00101_general_as4_Normal_Blue
summon_position: 2.9
summon_count: 1
enemy_hp_coef: 1
sequence_group_id: なし
```

```
condition_type: FriendUnitDead
condition_value: 2
sequence_element_id: 5
action_type: SummonEnemy
action_value: e_mag_00101_general_as4_Normal_Colorless
summon_position: 2.8
summon_count: 1
enemy_hp_coef: 1
sequence_group_id: なし
```

```
condition_type: FriendUnitDead
condition_value: 2
sequence_element_id: 6
action_type: SummonEnemy
action_value: e_mag_00101_general_as4_Normal_Colorless
summon_position: 2.75
summon_count: 1
enemy_hp_coef: 1
sequence_group_id: なし
```

```
condition_type: FriendUnitDead
condition_value: 3
sequence_element_id: 7
action_type: SummonEnemy
action_value: e_mag_00101_general_as4_Normal_Blue
summon_position: 2.9
summon_count: 1
enemy_hp_coef: 1
sequence_group_id: なし
```

```
condition_type: FriendUnitDead
condition_value: 3
sequence_element_id: 8
action_type: SummonEnemy
action_value: e_mag_00101_general_as4_Normal_Colorless
summon_position: 2.8
summon_count: 1
enemy_hp_coef: 1
sequence_group_id: なし
```

```
condition_type: FriendUnitDead
condition_value: 3
sequence_element_id: 9
action_type: SummonEnemy
action_value: e_mag_00101_general_as4_Normal_Colorless
summon_position: 2.75
summon_count: 1
enemy_hp_coef: 1
sequence_group_id: なし
```

味方ユニット2体撃破時に Blue×1 + Colorless×2 の3体セットで召喚、続いて3体撃破時にも同じ3体セットで召喚される2ウェーブ構成。各ユニットは細かく異なる summon_position（2.75〜2.9）で配置され、整然と敵ラインを形成する。

#### コマ効果

全コマ効果が `None`。このステージではコマ効果は使用されていない。

---

### normal_mag_00002（メインクエスト Normal）

#### このステージでの役割

`general` バリアント（Blue）が使用される序〜中盤向けステージ。HP 10,000・`role_type=Attack` の軽量なつらら Blue が、最初の強敵（e_mag_00001_general_Boss_Blue）撃破後に大量継続召喚される雑魚ウェーブ役として登場する。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_mag_00101_general_Normal_Blue` | Normal | Attack | Blue | 10,000 | 1,500 | 100 | 0.11 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_mag_00101_general_Normal_Blue` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 18 |

**MstAttackElement（e_mag_00101_general_Normal_Blue_Normal_00000）:**

| sort_order | attack_type | range_start_type | range_end_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|-------------|-----------------|---------------|---------------------|-----------------|--------|-------------|----------|----------------|-------------|
| 1 | Direct | Distance(0) | Distance | 0.3 | 1 | Foe | Damage | Normal | 100.0% | None |
| 2 | Direct | Distance(0) | Distance | 0.3 | 1 | Self | Damage | Normal | 999,999% | None |
| 3 | Direct | Distance(0) | Distance | 0.3 | 1 | Foe | Damage | Freeze | 0.0% | None |

> sort_order=3 の Freeze は power_parameter=0.0% のため実質ダメージなし。凍結エフェクト表現目的と思われる。

#### シーケンス設定

```
condition_type: FriendUnitDead
condition_value: 1
sequence_element_id: 3
action_type: SummonEnemy
action_value: e_mag_00101_general_Normal_Blue
summon_position: なし
summon_count: 99
summon_interval: 750
enemy_hp_coef: 1
sequence_group_id: なし
```

1体目の強敵（FriendUnitDead=1）を倒した後、すぐに99体（実質無限）のつらら Blue が750フレーム間隔で継続召喚されるウェーブ設計。summon_position 指定なし（デフォルト位置）。

#### コマ効果

全コマ効果が `None`。このステージではコマ効果は使用されていない。

---

### normal_mag_00003（メインクエスト Normal）

#### このステージでの役割

`general` バリアント Blue/Colorless の両色が登場する中盤ステージ。最初の強敵撃破後に Blue と Colorless のつらら2色が並行してウェーブ召喚される。Colorless バリアントは HP 20,000 とやや高く、色混在の難易度調整を担う。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_mag_00101_general_Normal_Blue` | Normal | Attack | Blue | 10,000 | 1,500 | 100 | 0.11 | 1 |
| `e_mag_00101_general_Normal_Colorless` | Normal | Attack | Colorless | 20,000 | 800 | 100 | 0.11 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_mag_00101_general_Normal_Blue` | なし | None | なし | なし |
| `e_mag_00101_general_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 18 |

**MstAttackElement（e_mag_00101_general_Normal_Blue_Normal_00000）:**

| sort_order | attack_type | range_start_type | range_end_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|-------------|-----------------|---------------|---------------------|-----------------|--------|-------------|----------|----------------|-------------|
| 1 | Direct | Distance(0) | Distance | 0.3 | 1 | Foe | Damage | Normal | 100.0% | None |
| 2 | Direct | Distance(0) | Distance | 0.3 | 1 | Self | Damage | Normal | 999,999% | None |
| 3 | Direct | Distance(0) | Distance | 0.3 | 1 | Foe | Damage | Freeze | 0.0% | None |

**MstAttackElement（e_mag_00101_general_Normal_Colorless_Normal_00000）:**

| sort_order | attack_type | range_start_type | range_end_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|-------------|-----------------|---------------|---------------------|-----------------|--------|-------------|----------|----------------|-------------|
| 1 | Direct | Distance(0) | Distance | 0.3 | 1 | Foe | Damage | Normal | 100.0% | None |
| 1 | Direct | Distance(0) | Distance | 0.3 | 1 | Self | Damage | Normal | 999,999% | None |

> Colorless バリアントは Freeze エフェクトなし（Normal 攻撃のみ）。Blue バリアントと異なる攻撃パターン。

#### シーケンス設定

```
condition_type: FriendUnitDead
condition_value: 1
sequence_element_id: 3
action_type: SummonEnemy
action_value: e_mag_00101_general_Normal_Blue
summon_position: なし
summon_count: 99
summon_interval: 300
enemy_hp_coef: 1
sequence_group_id: なし
```

```
condition_type: FriendUnitDead
condition_value: 1
sequence_element_id: 4
action_type: SummonEnemy
action_value: e_mag_00101_general_Normal_Colorless
summon_position: なし
summon_count: 99
summon_interval: 300
enemy_hp_coef: 1
sequence_group_id: なし
```

```
condition_type: FriendUnitDead
condition_value: 1
sequence_element_id: 5
action_type: SummonEnemy
action_value: e_mag_00101_general_Normal_Colorless
summon_position: なし
summon_count: 99
summon_interval: 300
enemy_hp_coef: 1
sequence_group_id: なし
```

1体目の強敵撃破後、Blue×1 + Colorless×2 の3本の無限ウェーブが同時に起動する。各ウェーブの summon_interval は300フレームで、normal_mag_00002（750フレーム）より召喚頻度が高くより激しいウェーブになる。

#### コマ効果

全コマ効果が `None`。このステージではコマ効果は使用されていない。

---

### normal_mag_00006（メインクエスト Normal）

#### このステージでの役割

`general2` バリアント（HP 20,000〜30,000）が登場する後半ステージ。最初の強敵撃破後、Blue×1 + Colorless×2 の無限ウェーブで継続出現する。`general2` は `general` より高HP だが攻撃力が低く（400〜700）、耐久型の雑魚群として位置づけられている。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_mag_00101_general2_Normal_Blue` | Normal | Attack | Blue | 20,000 | 400 | 100 | 0.11 | 1 |
| `e_mag_00101_general2_Normal_Colorless` | Normal | Attack | Colorless | 30,000 | 700 | 100 | 0.11 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_mag_00101_general2_Normal_Blue` | なし | None | なし | なし |
| `e_mag_00101_general2_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 18 |

**MstAttackElement（e_mag_00101_general2_Normal_Blue_Normal_00000）:**

| sort_order | attack_type | range_start_type | range_end_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|-------------|-----------------|---------------|---------------------|-----------------|--------|-------------|----------|----------------|-------------|
| 1 | Direct | Distance(0) | Distance | 0.3 | 1 | Foe | Damage | Normal | 100.0% | None |
| 2 | Direct | Distance(0) | Distance | 0.3 | 1 | Self | Damage | Normal | 999,999% | None |
| 3 | Direct | Distance(0) | Distance | 0.3 | 1 | Foe | Damage | Freeze | 0.0% | None |

**MstAttackElement（e_mag_00101_general2_Normal_Colorless_Normal_00000）:**

| sort_order | attack_type | range_start_type | range_end_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|-------------|-----------------|---------------|---------------------|-----------------|--------|-------------|----------|----------------|-------------|
| 1 | Direct | Distance(0) | Distance | 0.3 | 1 | Foe | Damage | Normal | 100.0% | None |
| 2 | Direct | Distance(0) | Distance | 0.3 | 1 | Self | Damage | Normal | 999,999% | None |

> general2 も Blue は Freeze エフェクト付き、Colorless は Normal 攻撃のみという general と同じパターン。

#### シーケンス設定

```
condition_type: FriendUnitDead
condition_value: 1
sequence_element_id: 3
action_type: SummonEnemy
action_value: e_mag_00101_general2_Normal_Blue
summon_position: なし
summon_count: 99
summon_interval: 780
enemy_hp_coef: 1
sequence_group_id: なし
```

```
condition_type: FriendUnitDead
condition_value: 1
sequence_element_id: 4
action_type: SummonEnemy
action_value: e_mag_00101_general2_Normal_Colorless
summon_position: なし
summon_count: 99
summon_interval: 780
enemy_hp_coef: 1
sequence_group_id: なし
```

```
condition_type: FriendUnitDead
condition_value: 1
sequence_element_id: 5
action_type: SummonEnemy
action_value: e_mag_00101_general2_Normal_Colorless
summon_position: なし
summon_count: 99
summon_interval: 780
enemy_hp_coef: 1
sequence_group_id: なし
```

1体目の強敵撃破後、Blue×1 + Colorless×2 の3本の無限ウェーブが780フレーム間隔で起動。normal_mag_00003（300フレーム）より間隔が長く落ち着いたペース感だが、general2 はHPが高いため総合的な難易度は維持される。

#### コマ効果

全コマ効果が `None`。このステージではコマ効果は使用されていない。
