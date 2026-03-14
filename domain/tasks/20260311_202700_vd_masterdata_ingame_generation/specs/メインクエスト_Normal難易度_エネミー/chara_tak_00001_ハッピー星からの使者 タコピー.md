# ハッピー星からの使者 タコピー（chara_tak_00001）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: chara_tak_00001
> mst_series_id: tak
> 作品名: タコピーの原罪

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `chara_tak_00001` |
| mst_series_id | `tak` |
| 作品名 | タコピーの原罪 |
| asset_key | `chara_tak_00001` |
| is_phantomized | `1` |

---

## 2. キャラクター特徴まとめ

ハッピー星からの使者 タコピーはメインクエスト Normalにのみ登場するキャラクターで、全4ステージ（`normal_tak_00001`〜`normal_tak_00003`、`normal_glo2_00002`）で使用される。

パラメータの構成は **Boss種別（Defense/Blue・Green・Yellow）** と **Normal種別（Defense/Red）** の2系統に分かれる。Bossパラメータは3色バリエーションがあるが、HP・攻撃力・移動速度はいずれも統一されており（HP: 10,000、攻撃力: 300、移動速度: 25）、ステージごとに使用色を変えることで難易度・ステージテーマのバリエーションを表現している。Normalパラメータ（Red）はHPこそ同じ10,000だが攻撃力が100と低く、ノックバック数も2と少ないため、脇役・やや強めの雑魚的な位置づけで使われている。

攻撃パターンはBossパラメータで登場時に全体ノックバック（ForcedKnockBack5）を発動し、スペシャル攻撃でDamageCutを自身に付与する防御特化型のボスとして設計されている。変身設定はなし。

コマ効果はSlipDamage（プレイヤーサイド）が2回、Gust（プレイヤーサイド）が2回、Darkness（プレイヤーサイド）が1回使用されており、いずれもプレイヤー側への妨害効果で構成されている。

---

## 3. ステージ別使用実態

### normal_glo2_00002（メインクエスト Normal）

#### このステージでの役割

`normal_glo2_00002` はGLO2コラボステージであり、タコピーはNormal種別のDefense/Redパラメータで中盤（ElapsedTime=1600フレーム）に1体召喚される。HP10,000・攻撃力100の防衛型として機能し、他のGLO2シリーズキャラクターとともに配置される脇役的な中堅敵として機能する。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_tak_00001_mainquest_glo2_Normal_Red` | Normal | Defense | Red | 10,000 | 100 | 32 | 0.18 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_tak_00001_mainquest_glo2_Normal_Red` | なし | None | なし | なし |

#### 攻撃パターン

**Normal攻撃**

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 50 |

**MstAttackElement（Normal攻撃）**

| sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|-------------------|-----------------|--------|------------|---------|----------------|------------|
| 1 | Direct | 0.19 | 1 | Foe | Damage | Normal | 100.0% | None |

#### シーケンス設定

```
condition_type: ElapsedTime
condition_value: 1600
sequence_element_id: 6
action_type: SummonEnemy
action_value: c_tak_00001_mainquest_glo2_Normal_Red
summon_position: （空）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 3.5
sequence_group_id: （空）
```

ElapsedTime=1600フレームで1体召喚。HP係数3.5により実際のHPは35,000相当に強化されて登場する。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| Gust | 2 | Player |
| None（その他コマ） | - | - |

---

### normal_tak_00001（メインクエスト Normal）

#### このステージでの役割

タコピー専用ステージの第1弾。Boss種別Yellow/Defenseパラメータが使用され、DarknessKomaCleared（暗闇コマ消去）を条件に1体召喚されるボスとして登場する。HP15倍（hp_coef=15）の高HP設定により、強力な最終ボスとして設計されている。summon_positionが1.3に設定されており、通常よりやや後方からの出現となる。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_tak_00001_mainquest_Boss_Yellow` | Boss | Defense | Yellow | 10,000 | 300 | 25 | 0.17 | 4 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_tak_00001_mainquest_Boss_Yellow` | なし | None | なし | なし |

#### 攻撃パターン

**Appearance攻撃（登場時）**

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |

| sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|-------------------|-----------------|--------|------------|---------|----------------|------------|
| 1 | Direct | 50.0 | 100 | Foe | None | ForcedKnockBack5 | 100.0% | None |

**Normal攻撃**

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | attack_delay | next_attack_interval |
|------------|-----------|--------------|------------------|--------------|-------------|---------------------|
| Normal | 0 | なし | なし | 50 | 22 | 50 |

| sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|-------------------|-----------------|--------|------------|---------|----------------|------------|
| 1 | Direct | 0.18 | 1 | Foe | Damage | Normal | 100.0% | None |

**Special攻撃**

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | attack_delay |
|------------|-----------|--------------|------------------|--------------|-------------|
| Special | 0 | なし | なし | 167 | 38 |

| sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type | effective_count | effective_duration | effect_parameter |
|-----------|------------|-------------------|-----------------|--------|------------|---------|----------------|------------|----------------|-------------------|----------------|
| 1 | Direct | 0.18 | 1 | Self | Damage | Normal | 0.0% | DamageCut | -1 | 300 | 20 |

自身にDamageCut効果（持続300、効果値20）を付与する防御型スペシャル。

#### シーケンス設定

```
condition_type: DarknessKomaCleared
condition_value: 2
sequence_element_id: 1
action_type: SummonEnemy
action_value: c_tak_00001_mainquest_Boss_Yellow
summon_position: 1.3
summon_count: 1
summon_interval: 0
enemy_hp_coef: 15
sequence_group_id: （空）
```

暗闇コマを2個消去するとボスが出現する仕様。HP係数15により実際のHPは150,000相当。summon_position=1.3でやや後退した位置に登場する。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| Darkness | 1 | Player |

---

### normal_tak_00002（メインクエスト Normal）

#### このステージでの役割

タコピー専用ステージの第2弾。Boss種別Blue/Defenseパラメータが使用され、初回はElapsedTime=700フレームで1体、その後FriendUnitDead=4（味方4体死亡）の条件でも1体追加召喚される2段構成のボスステージ。hp_coefはそれぞれ5・12と異なり、後から登場するほど強力な設定になっている。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_tak_00001_mainquest_Boss_Blue` | Boss | Defense | Blue | 10,000 | 300 | 25 | 0.17 | 4 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_tak_00001_mainquest_Boss_Blue` | なし | None | なし | なし |

#### 攻撃パターン

**Appearance攻撃（登場時）**

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |

| sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|-------------------|-----------------|--------|------------|---------|----------------|------------|
| 1 | Direct | 50.0 | 100 | Foe | None | ForcedKnockBack5 | 100.0% | None |

**Normal攻撃**

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | attack_delay | next_attack_interval |
|------------|-----------|--------------|------------------|--------------|-------------|---------------------|
| Normal | 0 | なし | なし | 50 | 22 | 50 |

| sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|-------------------|-----------------|--------|------------|---------|----------------|------------|
| 1 | Direct | 0.18 | 1 | Foe | Damage | Normal | 100.0% | None |

**Special攻撃**

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | attack_delay |
|------------|-----------|--------------|------------------|--------------|-------------|
| Special | 0 | なし | なし | 167 | 38 |

| sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type | effective_count | effective_duration | effect_parameter |
|-----------|------------|-------------------|-----------------|--------|------------|---------|----------------|------------|----------------|-------------------|----------------|
| 1 | Direct | 0.18 | 1 | Self | Damage | Normal | 0.0% | DamageCut | -1 | 300 | 20 |

#### シーケンス設定

**初回召喚（elem_id: 4）**

```
condition_type: ElapsedTime
condition_value: 700
sequence_element_id: 4
action_type: SummonEnemy
action_value: c_tak_00001_mainquest_Boss_Blue
summon_position: （空）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 5
sequence_group_id: （空）
```

**追加召喚（elem_id: 5）**

```
condition_type: FriendUnitDead
condition_value: 4
sequence_element_id: 5
action_type: SummonEnemy
action_value: c_tak_00001_mainquest_Boss_Blue
summon_position: （空）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 12
sequence_group_id: （空）
```

ElapsedTime=700フレームで最初のボス（hp_coef=5、実HP50,000）が登場。その後味方ユニット4体を撃破すると2体目（hp_coef=12、実HP120,000）が出現する2段階構成。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| なし（全コマNone） | - | - |

---

### normal_tak_00003（メインクエスト Normal）

#### このステージでの役割

タコピー専用ステージの第3弾。Boss種別Green/Defenseパラメータが使用され、ElapsedTime=1650フレームで1体召喚される。hp_coef=15という最高水準の設定で登場し、シリーズ最終ステージとして高難度のボス戦を演出する。SlipDamageコマ効果によりプレイヤーが継続的にダメージを受ける仕様が特徴的。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_tak_00001_mainquest_Boss_Green` | Boss | Defense | Green | 10,000 | 300 | 25 | 0.17 | 4 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_tak_00001_mainquest_Boss_Green` | なし | None | なし | なし |

#### 攻撃パターン

**Appearance攻撃（登場時）**

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |

| sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|-------------------|-----------------|--------|------------|---------|----------------|------------|
| 1 | Direct | 50.0 | 100 | Foe | None | ForcedKnockBack5 | 100.0% | None |

**Normal攻撃**

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | attack_delay | next_attack_interval |
|------------|-----------|--------------|------------------|--------------|-------------|---------------------|
| Normal | 0 | なし | なし | 50 | 22 | 50 |

| sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|-------------------|-----------------|--------|------------|---------|----------------|------------|
| 1 | Direct | 0.18 | 1 | Foe | Damage | Normal | 100.0% | None |

**Special攻撃**

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | attack_delay |
|------------|-----------|--------------|------------------|--------------|-------------|
| Special | 0 | なし | なし | 167 | 38 |

| sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type | effective_count | effective_duration | effect_parameter |
|-----------|------------|-------------------|-----------------|--------|------------|---------|----------------|------------|----------------|-------------------|----------------|
| 1 | Direct | 0.18 | 1 | Self | Damage | Normal | 0.0% | DamageCut | -1 | 300 | 20 |

#### シーケンス設定

```
condition_type: ElapsedTime
condition_value: 1650
sequence_element_id: 3
action_type: SummonEnemy
action_value: c_tak_00001_mainquest_Boss_Green
summon_position: （空）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 15
sequence_group_id: （空）
```

ElapsedTime=1650フレームで1体召喚。hp_coef=15により実HP150,000相当という最高難度の設定。序盤・中盤の雑魚（e_glo_00001系）をクリアした後に出現するクライマックスボスとして位置づけられている。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| SlipDamage | 2 | Player |
