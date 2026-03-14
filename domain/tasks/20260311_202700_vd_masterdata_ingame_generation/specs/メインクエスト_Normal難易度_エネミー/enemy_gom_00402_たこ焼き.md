# たこ焼き（enemy_gom_00402）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: enemy_gom_00402
> mst_series_id: gom
> 作品名: "姫様"拷問"の時間です"

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `enemy_gom_00402` |
| mst_series_id | `gom` |
| 作品名 | "姫様"拷問"の時間です" |
| asset_key | `enemy_gom_00402` |
| is_phantomized | `0` |

**キャラクター説明**

たこ焼き。目の前の鉄板で焼かれるライブ調理で、できたてを提供。

---

## 2. キャラクター特徴まとめ

たこ焼きはメインクエスト Normal難易度の「"姫様"拷問"の時間です"」クエストで使用される雑魚敵キャラクターである。ColorlessとYellowの2色バリエーションが存在するが、ステータスは完全に同一（HP: 1,000 / 攻撃力: 50）で、雑魚敵として運用される低スペックユニットである。

character_unit_kind はすべて Normal、role_type はすべて Attack であり、ボス用途には使われない。アビリティ・変身設定はなく、シンプルな構成の敵として設計されている。

使用ステージ（normal_gom_00002 / normal_gom_00006）では同キャラクターが大量召喚されるパターンが目立ち、数の圧力でプレイヤーを圧迫する役割を担う。特に normal_gom_00002 では FriendUnitDead トリガー発動後に 99体連続召喚という極端な数の出現パターンが組まれており、このキャラ固有の使われ方となっている。normal_glo1_00002 ではゲスト出演的に少数が挿入されるパターンとなっている。

コマ効果の傾向として、出現するステージでは AttackPowerDown（Player 側への攻撃力ダウン）と SlipDamage が設定されており、プレイヤーへの持続的な不利状況を作る設計が見られる。

---

## 3. ステージ別使用実態

### normal_glo1_00002（メインクエスト Normal）

#### このステージでの役割

このステージでは Yellow カラーのたこ焼きが中盤・後半に少数〜中数（1〜3体）召喚される。ボス敵や他の強敵を補佐する雑魚ウェーブとして機能しており、前半の主役ではない脇役的配置である。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_gom_00402_general_n_Normal_Yellow` | Normal | Attack | Yellow | 1,000 | 50 | 34 | 0.11 | （空） |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_gom_00402_general_n_Normal_Yellow` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 50 |

**MstAttackElement 詳細**

| sort_order | attack_type | range_start | range_end | max_target_count | target | damage_type | hit_type | power_parameter_type | power_parameter | effect_type |
|-----------|------------|------------|----------|-----------------|--------|------------|---------|---------------------|----------------|------------|
| 1 | Direct | Distance: 0 | Distance: 0.12 | 1 | Foe / All | Damage | Normal | Percentage | 100.0 | None |

近接1体攻撃（射程距離 0〜0.12）、効果なし、100%ダメージのシンプルな通常攻撃。

#### シーケンス設定

```
--- シーケンス要素 #10 ---
condition_type: ElapsedTime
condition_value: 400
action_type: SummonEnemy
action_value: e_gom_00402_general_n_Normal_Yellow
summon_position: （空）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 4
sequence_group_id: （空）

--- シーケンス要素 #11 ---
condition_type: ElapsedTime
condition_value: 1000
action_type: SummonEnemy
action_value: e_gom_00402_general_n_Normal_Yellow
summon_position: （空）
summon_count: 3
summon_interval: 500
enemy_hp_coef: 4
sequence_group_id: （空）
```

開始から400フレーム後に1体、1000フレーム後に3体（500フレーム間隔）が召喚される。enemy_hp_coef=4 と設定されており、HP は基礎値の4倍（4,000相当）で登場する。ElapsedTime ベースの定時召喚パターンである。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| AttackPowerDown | 2 | Player |

---

### normal_gom_00002（メインクエスト Normal）

#### このステージでの役割

このステージはたこ焼きが主役の出現パターンを持つステージである。序盤に1体で先行登場した後、FriendUnitDead トリガーによる phase 移行後に大量（99体×複数ウェーブ）が立て続けに召喚される。たこ焼きを大量出現させてプレイヤーのリソースを削る、いわゆる「物量攻め」設計のステージである。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_gom_00402_general_n_Normal_Colorless` | Normal | Attack | Colorless | 1,000 | 50 | 34 | 0.11 | （空） |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_gom_00402_general_n_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 50 |

**MstAttackElement 詳細**

| sort_order | attack_type | range_start | range_end | max_target_count | target | damage_type | hit_type | power_parameter_type | power_parameter | effect_type |
|-----------|------------|------------|----------|-----------------|--------|------------|---------|---------------------|----------------|------------|
| 1 | Direct | Distance: 0 | Distance: 0.12 | 1 | Foe / All | Damage | Normal | Percentage | 100.0 | None |

Colorless 版も Yellow 版と同一の近接1体通常攻撃。効果なし。

#### シーケンス設定

```
--- シーケンス要素 #1（フェーズ1） ---
condition_type: ElapsedTime
condition_value: 250
action_type: SummonEnemy
action_value: e_gom_00402_general_n_Normal_Colorless
summon_position: （空）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 2
sequence_group_id: （空）

--- フェーズ移行 ---
condition_type: FriendUnitDead
condition_value: 1
action_type: SwitchSequenceGroup → group1

--- シーケンス要素 #3（group1） ---
condition_type: ElapsedTimeSinceSequenceGroupActivated
condition_value: 0
action_type: SummonEnemy
action_value: e_gom_00402_general_n_Normal_Colorless
summon_position: （空）
summon_count: 99
summon_interval: 300
enemy_hp_coef: 2
sequence_group_id: group1

--- シーケンス要素 #4（group1） ---
condition_type: ElapsedTimeSinceSequenceGroupActivated
condition_value: 150
action_type: SummonEnemy
action_value: e_gom_00402_general_n_Normal_Colorless
summon_position: （空）
summon_count: 99
summon_interval: 750
enemy_hp_coef: 2
sequence_group_id: group1

--- シーケンス要素 #5（group1） ---
condition_type: ElapsedTimeSinceSequenceGroupActivated
condition_value: 25
action_type: SummonEnemy
action_value: e_gom_00402_general_n_Normal_Colorless
summon_position: （空）
summon_count: 99
summon_interval: 500
enemy_hp_coef: 2
sequence_group_id: group1

--- シーケンス要素 #6（group1） ---
condition_type: ElapsedTimeSinceSequenceGroupActivated
condition_value: 50
action_type: SummonEnemy
action_value: e_gom_00402_general_n_Normal_Colorless
summon_position: （空）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 2
sequence_group_id: group1

--- シーケンス要素 #7（group1） ---
condition_type: ElapsedTimeSinceSequenceGroupActivated
condition_value: 75
action_type: SummonEnemy
action_value: e_gom_00402_general_n_Normal_Colorless
summon_position: （空）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 2
sequence_group_id: group1

--- シーケンス要素 #8（group1） ---
condition_type: ElapsedTimeSinceSequenceGroupActivated
condition_value: 200
action_type: SummonEnemy
action_value: e_gom_00402_general_n_Normal_Colorless
summon_position: （空）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 2
sequence_group_id: group1
```

序盤は250フレーム後に1体（enemy_hp_coef=2）で先行。フレンドユニットが1体倒れると group1 に移行し、直後から大量召喚ウェーブが開始される。group1 では25・50・75・150・200フレームのほぼ同時並行で99体（インターバル300〜750フレーム）が複数スタックで押し寄せるという極端な物量設計となっている。

#### コマ効果

コマ効果なし（全コマ効果が None）

---

### normal_gom_00006（メインクエスト Normal）

#### このステージでの役割

多彩な敵が登場する長編ステージにおいて、たこ焼きは Yellow カラーでフェーズ1とフェーズ2（group1）の両方に登場する。フェーズ1の後半で5体が出現し、フェーズ移行後（group1）では3体→5体→10体と段階的に増加する波状攻撃を担う。多種の敵と組み合わせた複合構成の中でも持続的に出現する雑魚として機能する。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_gom_00402_general_n_Normal_Yellow` | Normal | Attack | Yellow | 1,000 | 50 | 34 | 0.11 | （空） |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_gom_00402_general_n_Normal_Yellow` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 50 |

**MstAttackElement 詳細**

| sort_order | attack_type | range_start | range_end | max_target_count | target | damage_type | hit_type | power_parameter_type | power_parameter | effect_type |
|-----------|------------|------------|----------|-----------------|--------|------------|---------|---------------------|----------------|------------|
| 1 | Direct | Distance: 0 | Distance: 0.12 | 1 | Foe / All | Damage | Normal | Percentage | 100.0 | None |

Yellow 版と同一の近接1体通常攻撃。

#### シーケンス設定

```
--- シーケンス要素 #9（フェーズ1） ---
condition_type: ElapsedTime
condition_value: 3000
action_type: SummonEnemy
action_value: e_gom_00402_general_n_Normal_Yellow
summon_position: （空）
summon_count: 5
summon_interval: 1000
enemy_hp_coef: 2
sequence_group_id: （空）

--- フェーズ移行 ---
condition_type: FriendUnitDead
condition_value: 2
action_type: SwitchSequenceGroup → group1

--- シーケンス要素 #14（group1） ---
condition_type: ElapsedTimeSinceSequenceGroupActivated
condition_value: 100
action_type: SummonEnemy
action_value: e_gom_00402_general_n_Normal_Yellow
summon_position: （空）
summon_count: 3
summon_interval: 100
enemy_hp_coef: 2
sequence_group_id: group1

--- シーケンス要素 #15（group1） ---
condition_type: ElapsedTimeSinceSequenceGroupActivated
condition_value: 150
action_type: SummonEnemy
action_value: e_gom_00402_general_n_Normal_Yellow
summon_position: （空）
summon_count: 5
summon_interval: 150
enemy_hp_coef: 2
sequence_group_id: group1

--- シーケンス要素 #16（group1） ---
condition_type: ElapsedTimeSinceSequenceGroupActivated
condition_value: 800
action_type: SummonEnemy
action_value: e_gom_00402_general_n_Normal_Yellow
summon_position: （空）
summon_count: 10
summon_interval: 400
enemy_hp_coef: 2
sequence_group_id: group1
```

フェーズ1では3000フレーム後に5体（間隔1000フレーム）が出現。フレンドユニット2体撃破後に group1 に移行し、100フレームで3体・150フレームで5体・800フレームで10体と段階的に数が増える波状召喚パターン。全て enemy_hp_coef=2 でHPは基礎値の2倍（2,000相当）。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| SlipDamage | 1 | Player |

---
