# 極楽蝶（enemy_jig_00401）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: enemy_jig_00401
> mst_series_id: jig
> 作品名: 地獄楽

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `enemy_jig_00401` |
| mst_series_id | `jig` |
| 作品名 | 地獄楽 |
| asset_key | `enemy_jig_00401` |
| is_phantomized | `0` |

> **キャラクター説明**: 蝶のような姿をしているが、人のような頭がついている人工的な生き物。鱗粉を撒き散らしたり、針のようなものを刺して攻撃する。

---

## 2. キャラクター特徴まとめ

極楽蝶はメインクエスト Normal（地獄楽）専用の雑魚敵キャラクターであり、全4ステージ（normal_jig_00003 〜 normal_jig_00006）で使用されている。

パラメータは2バリエーション存在し、いずれも character_unit_kind=Normal（雑魚）で HP 3,000・攻撃力 100 と中程度の強度を持つ。Colorless 版は role_type=Attack、Green 版は role_type=Technical となっており、使用ステージに応じて色と役割が使い分けられている。Green 版は攻撃時に毒（Poison）効果を持ち、プレイヤー側への持続ダメージを狙う設計となっている。変身設定はなく、シンプルな雑魚配置として機能する。

出現パターンは ElapsedTime（経過時間）と FriendUnitDead（友軍死亡）の2種を中心に構成されており、前半ウェーブの雑魚として大量召喚（20〜30体）されるケースが目立つ。ボス登場前の消耗役として配置されることが多い。

コマ効果としては normal_jig_00003 で koma1 に Poison（対象: Player）が設定されており、Green 版の攻撃毒と組み合わせてプレイヤーへの毒圧力を高める設計が見られる。

---

## 3. ステージ別使用実態

### normal_jig_00003（メインクエスト Normal）

#### このステージでの役割

地獄楽ノーマルクエスト序盤ステージ。経過時間トリガーでまず他の雑魚（門神×20）が前衛に登場し、その後に極楽蝶（Green×20）が大量召喚される2ウェーブ構成。極楽蝶は毒攻撃で持続ダメージを与え、コマ効果の Poison（Player対象）も相まってプレイヤーへの毒圧力を高める。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_jig_00401_mainquest_Normal_Green` | Normal | Technical | Green | 3,000 | 100 | 25 | 0.22 | 4 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_jig_00401_mainquest_Normal_Green` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | attack_delay | next_attack_interval |
|------------|-----------|--------------|------------------|--------------|-------------|---------------------|
| Normal | 0 | なし | なし | 65 | 33 | 200 |

**MstAttackElement（e_jig_00401_mainquest_Normal_Green_Normal_00000）**

| sort_order | attack_type | range_start | range_end | max_target | target | damage_type | hit_type | power_parameter | effect_type | effective_count | effective_duration | effect_parameter |
|-----------|-----------|------------|----------|-----------|--------|------------|---------|----------------|------------|----------------|-------------------|----------------|
| 1 | Direct | Distance:0 | Distance:0.23 | 1 | Foe/All/All/All | Damage | Normal | Percentage:100.0 | Poison | -1 | 300 | 1 |

> 索敵距離0.23の近接直接攻撃。ヒット時にPoison（無制限回数・持続300フレーム・効果値1）を付与する。

#### シーケンス設定

```
sequence_element_id: 2
condition_type: ElapsedTime
condition_value: 250
action_type: SummonEnemy
action_value: e_jig_00401_mainquest_Normal_Green
summon_position: （空）
summon_count: 20
summon_interval: 1100
enemy_hp_coef: 1.7
sequence_group_id: （空）
```

経過時間250フレーム後に20体を間隔1100フレームで順次召喚。enemy_hp_coef=1.7で基本HP 3,000から5,100相当に強化される。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| Poison | 1 | Player |

---

### normal_jig_00004（メインクエスト Normal）

#### このステージでの役割

Colorless版の極楽蝶が先頭ウェーブとして30体一斉召喚される。その後に門神が別タイミングで大量召喚され、最後にFriendUnitDead条件でボス（Green・Boss）が登場する3ウェーブ構成。極楽蝶はプレイヤーへの消耗役として機能する。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_jig_00401_mainquest_Normal_Colorless` | Normal | Attack | Colorless | 3,000 | 100 | 32 | 0.24 | 4 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_jig_00401_mainquest_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | attack_delay | next_attack_interval |
|------------|-----------|--------------|------------------|--------------|-------------|---------------------|
| Normal | 0 | なし | なし | 65 | 33 | 125 |

**MstAttackElement（e_jig_00401_mainquest_Normal_Colorless_Normal_00000）**

| sort_order | attack_type | range_start | range_end | max_target | target | damage_type | hit_type | power_parameter | effect_type | effective_count | effective_duration | effect_parameter |
|-----------|-----------|------------|----------|-----------|--------|------------|---------|----------------|------------|----------------|-------------------|----------------|
| 1 | Direct | Distance:0 | Distance:0.25 | 1 | Foe/All/All/All | Damage | Normal | Percentage:100.0 | None | 0 | 0 | 0 |

> 索敵距離0.25の近接直接攻撃。特殊効果なし。next_attack_interval=125とGreen版（200）より短く、攻撃頻度が高い。

#### シーケンス設定

```
sequence_element_id: 1
condition_type: ElapsedTime
condition_value: 250
action_type: SummonEnemy
action_value: e_jig_00401_mainquest_Normal_Colorless
summon_position: （空）
summon_count: 30
summon_interval: 600
enemy_hp_coef: 1.7
sequence_group_id: （空）
```

経過時間250フレーム後に30体を間隔600フレームで召喚。enemy_hp_coef=1.7で基本HP 3,000から5,100相当に強化される。30体召喚のため間隔600フレームで連続的に前線へ押し寄せる構成。

#### コマ効果

コマ効果なし（このステージでは Poison 等の効果コマは設定されていない）

---

### normal_jig_00005（メインクエスト Normal）

#### このステージでの役割

FriendUnitDead（友軍5体死亡）をトリガーとして複数キャラが同時召喚される一斉ウェーブステージ。極楽蝶（Green）は門神・別キャラと同条件で30体召喚され、さらにボス（Colorless・Boss）も同トリガーで登場する。序盤にはElapsedTimeでボス（Green・Boss）も1体登場しており、全体的に難易度が高い構成となっている。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_jig_00401_mainquest_Normal_Green` | Normal | Technical | Green | 3,000 | 100 | 25 | 0.22 | 4 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_jig_00401_mainquest_Normal_Green` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | attack_delay | next_attack_interval |
|------------|-----------|--------------|------------------|--------------|-------------|---------------------|
| Normal | 0 | なし | なし | 65 | 33 | 200 |

**MstAttackElement（e_jig_00401_mainquest_Normal_Green_Normal_00000）**

| sort_order | attack_type | range_start | range_end | max_target | target | damage_type | hit_type | power_parameter | effect_type | effective_count | effective_duration | effect_parameter |
|-----------|-----------|------------|----------|-----------|--------|------------|---------|----------------|------------|----------------|-------------------|----------------|
| 1 | Direct | Distance:0 | Distance:0.23 | 1 | Foe/All/All/All | Damage | Normal | Percentage:100.0 | Poison | -1 | 300 | 1 |

> 索敵距離0.23の近接直接攻撃。ヒット時にPoison（無制限回数・持続300フレーム・効果値1）を付与する。

#### シーケンス設定

```
sequence_element_id: 1
condition_type: FriendUnitDead
condition_value: 5
action_type: SummonEnemy
action_value: e_jig_00401_mainquest_Normal_Green
summon_position: （空）
summon_count: 30
summon_interval: 500
enemy_hp_coef: 1.7
sequence_group_id: （空）
```

友軍5体死亡をトリガーに30体を間隔500フレームで召喚。同トリガーで門神・別種雑魚・ボスも同時に召喚される混戦構成。enemy_hp_coef=1.7で5,100相当のHPとなる。

#### コマ効果

コマ効果なし（このステージでは Poison 等の効果コマは設定されていない）

---

### normal_jig_00006（メインクエスト Normal）

#### このステージでの役割

Colorless版の極楽蝶が1体のみ経過時間トリガーで召喚されるステージ。その後は別種雑魚（e_jig_00402）の大量召喚やFriendUnitDeadによるフレンドユニット・ボスの出現と続く。極楽蝶単体の役割は限定的で、序盤の単体召喚による牽制程度の位置づけとなっている。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_jig_00401_mainquest_Normal_Colorless` | Normal | Attack | Colorless | 3,000 | 100 | 32 | 0.24 | 4 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_jig_00401_mainquest_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | attack_delay | next_attack_interval |
|------------|-----------|--------------|------------------|--------------|-------------|---------------------|
| Normal | 0 | なし | なし | 65 | 33 | 125 |

**MstAttackElement（e_jig_00401_mainquest_Normal_Colorless_Normal_00000）**

| sort_order | attack_type | range_start | range_end | max_target | target | damage_type | hit_type | power_parameter | effect_type | effective_count | effective_duration | effect_parameter |
|-----------|-----------|------------|----------|-----------|--------|------------|---------|----------------|------------|----------------|-------------------|----------------|
| 1 | Direct | Distance:0 | Distance:0.25 | 1 | Foe/All/All/All | Damage | Normal | Percentage:100.0 | None | 0 | 0 | 0 |

> 索敵距離0.25の近接直接攻撃。特殊効果なし。

#### シーケンス設定

```
sequence_element_id: 1
condition_type: ElapsedTime
condition_value: 250
action_type: SummonEnemy
action_value: e_jig_00401_mainquest_Normal_Colorless
summon_position: （空）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 1.7
sequence_group_id: （空）
```

経過時間250フレーム後に1体のみ召喚。summon_count=1・summon_interval=0のため単体かつ即時召喚。このステージでの極楽蝶はシンボル的な単体登場にとどまり、主力はその後に続く大量召喚ウェーブ（e_jig_00402×30体）となっている。

#### コマ効果

コマ効果なし（このステージでは Poison 等の効果コマは設定されていない）
