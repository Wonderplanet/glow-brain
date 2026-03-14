# 網代 慎平（chara_sum_00001）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: chara_sum_00001
> mst_series_id: sum
> 作品名: サマータイムレンダ

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `chara_sum_00001` |
| mst_series_id | `sum` |
| 作品名 | サマータイムレンダ |
| asset_key | `chara_sum_00001` |
| is_phantomized | `1` |

---

## 2. キャラクター特徴まとめ

網代 慎平はメインクエスト Normalのみに登場するフレンドユニット（`c_` プレフィックス）であり、Normalクエストの2ステージで使用されている。

character_unit_kind はいずれも `Normal`（雑魚・中堅ポジション）、role_type は `Technical` で統一されている。カラーは `Red`（`c_sum_00001_general_Normal_Red`）と `Blue`（`c_sum_00001_general_as4_Normal_Blue`）の2バリエーションが存在する。

HPのレンジは 150,000〜245,000 と比較的高め。攻撃力は Red 版が 500、Blue 版が 900 と大きく異なる。Blue 版は索敵距離（well_distance）が 0.3 と広く、遠距離からの牽制が可能なタイプ。変身設定はなく、アビリティも未設定。

いずれのステージもコマ効果は全て `None` で、コマ効果による特殊演出は行われていない。

---

## 3. ステージ別使用実態

### normal_glo4_00002（メインクエスト Normal）

#### このステージでの役割

`c_sum_00001_general_as4_Normal_Blue` として登場。ElapsedTime（経過時間トリガー）375フレームで召喚される初期配備ユニット。攻撃力 900・索敵距離 0.3 の広射程 Technical として、ステージ序盤から前線に圧力をかける役割を担う。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_sum_00001_general_as4_Normal_Blue` | Normal | Technical | Blue | 150,000 | 900 | 40 | 0.3 | 3 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_sum_00001_general_as4_Normal_Blue` | なし | None | なし | なし |

#### 攻撃パターン

**Normal攻撃（`c_sum_00001_general_as4_Normal_Blue_Normal_00000`）**

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 70 |

MstAttackElement:

| sort_order | attack_type | range_end_parameter | max_target_count | damage_type | power_parameter_type | power_parameter | effect_type |
|-----------|------------|--------------------|-----------------|-----------|--------------------|----------------|------------|
| 1 | Direct | 0.6 | 1 | Damage | Percentage | 20.0 | None |

**Special攻撃（`c_sum_00001_general_as4_Normal_Blue_Special_00001`）**

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Special | 0 | なし | なし | 117 |

MstAttackElement:

| sort_order | attack_type | range_end_parameter | max_target_count | damage_type | power_parameter_type | power_parameter | effect_type | effective_count | effective_duration | effect_parameter | effect_value |
|-----------|------------|--------------------|-----------------|-----------|--------------------|----------------|------------|----------------|------------------|----------------|------------|
| 1 | Direct | 0.6 | 100 | Damage | Percentage | 20.0 | None | 0 | 0 | 0 | なし |
| 2 | Direct | 0.6 | 100 | Damage | Percentage | 20.0 | None | 0 | 0 | 0 | なし |
| 3 | Direct | 0.6 | 100 | Damage | Percentage | 60.0 | AttackPowerDown | -1 | 500 | 15 | なし |

Special攻撃の3段目で `AttackPowerDown`（攻撃力低下）効果を付与する。効果時間は500フレーム、効果値は15%低下（effective_count=-1で無制限スタック）。

#### シーケンス設定

```
condition_type: ElapsedTime
condition_value: 375
sequence_element_id: 1
action_type: SummonEnemy
action_value: c_sum_00001_general_as4_Normal_Blue
summon_position: （指定なし）
summon_count: 1
enemy_hp_coef: 1
sequence_group_id: （指定なし）
```

経過時間375フレームでの単体召喚。summon_position 未指定のため、デフォルト位置（敵陣後方）に召喚される。

#### コマ効果

コマ効果なし（全コマ `None`）。

---

### normal_sum_00006（メインクエスト Normal）

#### このステージでの役割

`c_sum_00001_general_Normal_Red` として登場。FriendUnitDead（味方ユニット撃破数トリガー）2体死亡時に召喚される後続ユニット。HP 245,000 の耐久型 Technical として、序盤を乗り切った後の中継ぎとして配備される。`c_sum_00101_general_Boss_Red`（ボス格ユニット）と同タイミングで召喚されるため、ボス登場とセットで登場する形になる。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_sum_00001_general_Normal_Red` | Normal | Technical | Red | 245,000 | 500 | 45 | 0.11 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_sum_00001_general_Normal_Red` | なし | None | なし | なし |

#### 攻撃パターン

**Normal攻撃（`c_sum_00001_general_Normal_Red_Normal_00000`）**

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 75 |

MstAttackElement:

| sort_order | attack_type | range_end_parameter | max_target_count | damage_type | power_parameter_type | power_parameter | effect_type |
|-----------|------------|--------------------|-----------------|-----------|--------------------|----------------|------------|
| 1 | Direct | 0.3 | 1 | Damage | Percentage | 100.0 | None |

**Special攻撃（`c_sum_00001_general_Normal_Red_Special_00001`）**

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Special | 0 | なし | なし | 117 |

MstAttackElement:

| sort_order | attack_type | range_end_parameter | max_target_count | damage_type | power_parameter_type | power_parameter | effect_type | effective_count | effective_duration | effect_parameter | effect_value |
|-----------|------------|--------------------|-----------------|-----------|--------------------|----------------|------------|----------------|------------------|----------------|------------|
| 1 | Direct | 0.3 | 1 | Damage | Percentage | 100.0 | None | 0 | 0 | 0 | なし |
| 2 | Direct | 0.3 | 1 | Damage | Percentage | 100.0 | None | 0 | 0 | 0 | なし |
| 3 | Direct | 0.3 | 1 | Damage | Percentage | 100.0 | AttackPowerDown | -1 | 1000 | 20 | なし |

Special攻撃の3段目で `AttackPowerDown` 効果を付与。効果時間は1000フレーム（Blue版の2倍）、効果値は20%低下。近距離（range_end_parameter=0.3）の1体攻撃で、Blue版と比べて接近戦向きのデザイン。

#### シーケンス設定

```
condition_type: FriendUnitDead
condition_value: 2
sequence_element_id: 3
action_type: SummonEnemy
action_value: c_sum_00001_general_Normal_Red
summon_position: 2.9
summon_count: 1
enemy_hp_coef: 1
sequence_group_id: （指定なし）
```

味方ユニット2体撃破時に召喚。summon_position=2.9 はステージ奥寄り（敵陣側）への配備を意味する。同一条件（FriendUnitDead=2）で `c_sum_00101_general_Boss_Red` も同時召喚され、2体がセットでボス登場局面を形成する。

#### コマ効果

コマ効果なし（全コマ `None`）。

---
