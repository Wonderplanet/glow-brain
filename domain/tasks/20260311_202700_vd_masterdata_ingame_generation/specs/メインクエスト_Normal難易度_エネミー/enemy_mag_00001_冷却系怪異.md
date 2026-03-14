# 冷却系怪異（enemy_mag_00001）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: enemy_mag_00001
> mst_series_id: mag
> 作品名: 株式会社マジルミエ

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `enemy_mag_00001` |
| mst_series_id | `mag` |
| 作品名 | 株式会社マジルミエ |
| asset_key | `enemy_mag_00001` |
| is_phantomized | `0` |

---

## 2. キャラクター特徴まとめ

冷却系怪異はメインクエスト Normal（株式会社マジルミエコンテンツ）の全3ステージ（normal_mag_00001〜00003）に登場する怪異キャラクターです。全ステージで共通して `e_mag_00001_general_Normal_Colorless`（Normalユニット）が先頭に配置され、その後にボス級パラメータが後続として召喚される構造が特徴です。

HPレンジはNormalユニット（70,000）からBossユニット（270,000〜1,000,000）まで幅広く、特にnormal_mag_00001のボス版はHP 1,000,000と高耐久です。攻撃力も1,200〜3,800と広く、すべてのパラメータで role_type=Attack が設定されており、前線攻撃型として一貫して運用されています。変身設定はなく、アビリティも未設定です。

攻撃パターンは全バリエーションで `AttackPowerDown`（攻撃力ダウン効果）を持ち、ボス版はさらに出現時に周囲の敵を5マス強制ノックバックさせる `Appearance（ForcedKnockBack5）` 攻撃を持ちます。コマ効果はすべてNone（特殊効果なし）で、ストレートなバトル難度設計です。

条件トリガーは全ステージで `InitialSummon`（試合開始時）と `FriendUnitDead`（味方ユニット撃破時）の2種類が使われており、味方ユニット撃破時に強力なボス版が召喚されるリスクのある設計です。

---

## 3. ステージ別使用実態

### normal_mag_00001（メインクエスト Normal）

#### このステージでの役割

シリーズ初ステージとして、Normalユニットを先行配置しつつ味方ユニット撃破時にHP 1,000,000の強力なBossユニット（`e_mag_00001_general2_Boss_Blue`）が後続召喚される構成です。Normalユニットで序盤の圧力をかけつつ、ボス撃破後の継続プレッシャーとして非常に高いHP・攻撃力のBossを登場させることで難度が高い設計になっています。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_mag_00001_general_Normal_Colorless` | Normal | Attack | Colorless | 70,000 | 1,200 | 35 | 0.3 | 1 |
| `e_mag_00001_general2_Boss_Blue` | Boss | Attack | Blue | 1,000,000 | 2,500 | 35 | 0.3 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_mag_00001_general_Normal_Colorless` | なし | None | なし | なし |
| `e_mag_00001_general2_Boss_Blue` | なし | None | なし | なし |

#### 攻撃パターン

**e_mag_00001_general_Normal_Colorless**

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 75 |

MstAttackElement詳細（Normal攻撃）:

| sort_order | attack_type | range_end_parameter | max_target_count | damage_type | hit_type | power_parameter | effect_type | effective_duration | effect_parameter |
|-----------|------------|--------------------|-----------------|-----------||---------|----------------|------------|-----------------|---------------|
| 1 | Direct | 0.4 | 1 | Damage | KnockBack1 | 100.0% | AttackPowerDown | 1,000 | 50 |

**e_mag_00001_general2_Boss_Blue**

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 75 |

MstAttackElement詳細（Appearance攻撃）:

| sort_order | attack_type | range_end_parameter | max_target_count | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|--------------------|-----------------|-----------||---------|----------------|------------|
| 1 | Direct | 50.0 | 100 | None | ForcedKnockBack5 | 0.0% | None |

MstAttackElement詳細（Normal攻撃）:

| sort_order | attack_type | range_end_parameter | max_target_count | damage_type | hit_type | power_parameter | effect_type | effective_duration | effect_parameter |
|-----------|------------|--------------------|-----------------|-----------||---------|----------------|------------|-----------------|---------------|
| 1 | Direct | 0.4 | 1 | Damage | KnockBack1 | 100.0% | None | 0 | 0 |
| 2 | Direct | 0.4 | 1 | Damage | Normal | 0.0% | AttackPowerDown | 1,000 | 50 |

#### シーケンス設定

```
--- シーケンス要素 1 ---
condition_type: InitialSummon
condition_value: 2
action_type: SummonEnemy
action_value: e_mag_00001_general_Normal_Colorless
summon_position: 1.5
summon_count: 1
summon_interval: 0
enemy_hp_coef: 1
sequence_group_id: なし

--- シーケンス要素 2 ---
condition_type: FriendUnitDead
condition_value: 1
action_type: SummonEnemy
action_value: e_mag_00001_general2_Boss_Blue
summon_position: 1.5
summon_count: 1
summon_interval: 0
enemy_hp_coef: 1
sequence_group_id: なし
```

試合開始後2秒でNormalユニットを召喚し、味方ユニット1体撃破時にHP 100万のBossユニットを召喚する構成。Bossは出現時に半径50の範囲内の敵を5マス強制ノックバックさせる。

#### コマ効果

全コマラインにおいてコマ効果なし（すべてNone）。

---

### normal_mag_00002（メインクエスト Normal）

#### このステージでの役割

Normalユニットを先行配置した後、味方ユニット撃破時にBossユニット（`e_mag_00001_general_Boss_Blue`、HP 270,000）を召喚し、さらに追加で別のキャラクター（`e_mag_00101_general_Normal_Blue`）を大量召喚するステージです。冷却系怪異のBossは強力な攻撃力ダウンデバフを持ち、他キャラクターとの合わせ技で継続的な圧力をかけます。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_mag_00001_general_Normal_Colorless` | Normal | Attack | Colorless | 70,000 | 1,200 | 35 | 0.3 | 1 |
| `e_mag_00001_general_Boss_Blue` | Boss | Attack | Blue | 270,000 | 3,800 | 35 | 0.3 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_mag_00001_general_Normal_Colorless` | なし | None | なし | なし |
| `e_mag_00001_general_Boss_Blue` | なし | None | なし | なし |

#### 攻撃パターン

**e_mag_00001_general_Normal_Colorless**

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 75 |

MstAttackElement詳細（Normal攻撃）:

| sort_order | attack_type | range_end_parameter | max_target_count | damage_type | hit_type | power_parameter | effect_type | effective_duration | effect_parameter |
|-----------|------------|--------------------|-----------------|-----------||---------|----------------|------------|-----------------|---------------|
| 1 | Direct | 0.4 | 1 | Damage | KnockBack1 | 100.0% | AttackPowerDown | 1,000 | 50 |

**e_mag_00001_general_Boss_Blue**

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 75 |

MstAttackElement詳細（Appearance攻撃）:

| sort_order | attack_type | range_end_parameter | max_target_count | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|--------------------|-----------------|-----------||---------|----------------|------------|
| 1 | Direct | 50.0 | 100 | None | ForcedKnockBack5 | 0.0% | None |

MstAttackElement詳細（Normal攻撃）:

| sort_order | attack_type | range_end_parameter | max_target_count | damage_type | hit_type | power_parameter | effect_type | effective_duration | effect_parameter |
|-----------|------------|--------------------|-----------------|-----------||---------|----------------|------------|-----------------|---------------|
| 1 | Direct | 0.4 | 1 | Damage | KnockBack1 | 100.0% | None | 0 | 0 |
| 2 | Direct | 0.3 | 1 | Damage | Normal | 0.0% | AttackPowerDown | 1,000 | 50 |

#### シーケンス設定

```
--- シーケンス要素 1 ---
condition_type: InitialSummon
condition_value: 2
action_type: SummonEnemy
action_value: e_mag_00001_general_Normal_Colorless
summon_position: 1.5
summon_count: 1
summon_interval: 0
enemy_hp_coef: 1
sequence_group_id: なし

--- シーケンス要素 2 ---
condition_type: FriendUnitDead
condition_value: 1
action_type: SummonEnemy
action_value: e_mag_00001_general_Boss_Blue
summon_position: なし（空文字）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 1
sequence_group_id: なし

--- シーケンス要素 3（別キャラ: e_mag_00101） ---
condition_type: FriendUnitDead
condition_value: 1
action_type: SummonEnemy
action_value: e_mag_00101_general_Normal_Blue
summon_position: なし（空文字）
summon_count: 99
summon_interval: 750
enemy_hp_coef: 1
sequence_group_id: なし
```

味方ユニット1体撃破時にBossと別キャラを同時にトリガーする構成。Bossは単体召喚、別キャラは750フレーム間隔で最大99体の大量召喚が発動する。

#### コマ効果

全コマラインにおいてコマ効果なし（すべてNone）。

---

### normal_mag_00003（メインクエスト Normal）

#### このステージでの役割

normal_mag_00002の発展形で、味方ユニット撃破時に冷却系怪異のBoss召喚に加え、複数の別キャラクターが300フレーム間隔で大量召喚される複合構成ステージです。キャラクター数の多さと攻撃力ダウンデバフの組み合わせが難度を高める設計になっています。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_mag_00001_general_Normal_Colorless` | Normal | Attack | Colorless | 70,000 | 1,200 | 35 | 0.3 | 1 |
| `e_mag_00001_general_Boss_Blue` | Boss | Attack | Blue | 270,000 | 3,800 | 35 | 0.3 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_mag_00001_general_Normal_Colorless` | なし | None | なし | なし |
| `e_mag_00001_general_Boss_Blue` | なし | None | なし | なし |

#### 攻撃パターン

**e_mag_00001_general_Normal_Colorless**

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 75 |

MstAttackElement詳細（Normal攻撃）:

| sort_order | attack_type | range_end_parameter | max_target_count | damage_type | hit_type | power_parameter | effect_type | effective_duration | effect_parameter |
|-----------|------------|--------------------|-----------------|-----------||---------|----------------|------------|-----------------|---------------|
| 1 | Direct | 0.4 | 1 | Damage | KnockBack1 | 100.0% | AttackPowerDown | 1,000 | 50 |

**e_mag_00001_general_Boss_Blue**

（normal_mag_00002 と同パラメータにつき同様の攻撃パターン）

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 75 |

MstAttackElement詳細（Appearance攻撃）:

| sort_order | attack_type | range_end_parameter | max_target_count | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|--------------------|-----------------|-----------||---------|----------------|------------|
| 1 | Direct | 50.0 | 100 | None | ForcedKnockBack5 | 0.0% | None |

MstAttackElement詳細（Normal攻撃）:

| sort_order | attack_type | range_end_parameter | max_target_count | damage_type | hit_type | power_parameter | effect_type | effective_duration | effect_parameter |
|-----------|------------|--------------------|-----------------|-----------||---------|----------------|------------|-----------------|---------------|
| 1 | Direct | 0.4 | 1 | Damage | KnockBack1 | 100.0% | None | 0 | 0 |
| 2 | Direct | 0.3 | 1 | Damage | Normal | 0.0% | AttackPowerDown | 1,000 | 50 |

#### シーケンス設定

```
--- シーケンス要素 1 ---
condition_type: InitialSummon
condition_value: 2
action_type: SummonEnemy
action_value: e_mag_00001_general_Normal_Colorless
summon_position: 1.5
summon_count: 1
summon_interval: 0
enemy_hp_coef: 1
sequence_group_id: なし

--- シーケンス要素 2 ---
condition_type: FriendUnitDead
condition_value: 1
action_type: SummonEnemy
action_value: e_mag_00001_general_Boss_Blue
summon_position: なし（空文字）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 1
sequence_group_id: なし

--- シーケンス要素 3（別キャラ: e_mag_00101_Normal_Blue） ---
condition_type: FriendUnitDead
condition_value: 1
action_type: SummonEnemy
action_value: e_mag_00101_general_Normal_Blue
summon_position: なし（空文字）
summon_count: 99
summon_interval: 300
enemy_hp_coef: 1
sequence_group_id: なし

--- シーケンス要素 4（別キャラ: e_mag_00101_Normal_Colorless） ---
condition_type: FriendUnitDead
condition_value: 1
action_type: SummonEnemy
action_value: e_mag_00101_general_Normal_Colorless
summon_position: なし（空文字）
summon_count: 99
summon_interval: 300
enemy_hp_coef: 1
sequence_group_id: なし

--- シーケンス要素 5（別キャラ: e_mag_00101_Normal_Colorless 2本目） ---
condition_type: FriendUnitDead
condition_value: 1
action_type: SummonEnemy
action_value: e_mag_00101_general_Normal_Colorless
summon_position: なし（空文字）
summon_count: 99
summon_interval: 300
enemy_hp_coef: 1
sequence_group_id: なし
```

味方ユニット1体撃破時に冷却系怪異Bossと合わせて3系統の別キャラが同時にトリガーされ、それぞれ300フレーム間隔で最大99体ずつ大量出現する。normal_mag_00002より別キャラの種類・本数が増えており、最も複雑な構成のステージ。

#### コマ効果

全コマラインにおいてコマ効果なし（すべてNone）。
