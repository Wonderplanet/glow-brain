# 幸山 厘（chara_hut_00101）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: chara_hut_00101
> mst_series_id: hut
> 作品名: ふつうの軽音部

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `chara_hut_00101` |
| mst_series_id | `hut` |
| 作品名 | ふつうの軽音部 |
| asset_key | `chara_hut_00101` |
| is_phantomized | `1` |

> **フィルタ適用**: 本ドキュメントは「Normal unit_kind（通常敵種別）のパラメータ」のみを対象として記載しています。なお、chara_hut_00101はメインクエストNormalには出現実績がなく、イベントと降臨バトルでのみ使用されています。

---

## 2. キャラクター特徴まとめ

幸山 厘（chara_hut_00101）は「ふつうの軽音部」シリーズのキャラクターで、Normal unit_kindとしてイベント（challengeシリーズ・charaGet02シリーズ）および降臨バトル（raid_hut1_00001）に登場する。メインクエストNormalへの出現実績はない。

Normal unit_kindのパラメータは全5種で、HPは1,000〜10,000の範囲で設定されており、中程度の雑魚〜やや強めの敵として機能する。攻撃力は100で全バリエーション統一されており、role_typeはSupportに固定されている。移動速度は35.0で統一。索敵距離は0.35〜0.42の範囲。アビリティ・変身設定はなし。

攻撃パターンはNormal攻撃（単体・近接ダメージ）とSpecial攻撃（範囲・多段ダメージ＋味方全体への攻撃力バフ）を持ち、Supportロールらしくチームに攻撃力上昇効果を付与する。charaget02バリエーションはAppearance（登場演出）攻撃も持つ。

出現パターンは主にElapsedTime（経過時間トリガー）とFriendUnitDead（敵ユニット撃破トリガー）の組み合わせで使われ、1体ずつ召喚されるケースが多い。特定ステージではFriendUnitDeadによる補充召喚も設定されており、倒しても再出現する点が特徴。

コマ効果はSlipDamage（継続ダメージ）が最も多く使用され（3回）、Burn（炎上）・Poison（毒）がそれぞれ1回使用されている。いずれもPlayer側への嫌がらせ効果であり、対策なしでは継続ダメージを受け続ける設計のステージに配置されやすい。

---

## 3. ステージ別使用実態

### event_hut1_challenge_00002（イベント）

#### このステージでの役割

challengeシリーズの第2ステージ。幸山 厘（Blue）はボスキャラ（c_hut_00001）と共に登場し、ElapsedTimeトリガーで早期召喚されるサポート型の中程度敵として機能する。複数体の汎用敵（e_glo_00001）が時間経過・撃破で波状に押し寄せる中で、HPコエフ14の補助的な強さで配置される。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_hut_00101_hut1_challenge2_Normal_Blue` | Normal | Support | Blue | 10,000 | 100 | 35.0 | 0.42 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_hut_00101_hut1_challenge2_Normal_Blue` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 79 |
| Special | 0 | なし | なし | 129 |

**Normal攻撃（c_hut_00101_hut1_challenge2_Normal_Blue_Normal_00000）**

| sort_order | attack_type | range_start | range_end | max_targets | target | damage_type | effect_type | power_parameter |
|-----------|------------|------------|----------|------------|--------|------------|------------|----------------|
| 1 | Direct | Distance 0 | Distance 0.63 | 1 | Foe/All/All/All | Damage/Normal | None | Percentage 100% |

**Special攻撃（c_hut_00101_hut1_challenge2_Normal_Blue_Special_00001）**

| sort_order | attack_type | range | max_targets | target | damage_type | effect_type | effect_parameter | effect_value |
|-----------|------------|-------|------------|--------|------------|------------|-----------------|-------------|
| 1〜5 | Direct | Distance 0〜0.63 | 100 | Foe/All/All/All | Damage/Normal | None | - | Percentage 40% |
| 6 | Direct | Distance -1〜1.0 | 100 | Friend/All/All/All | None/Normal | AttackPowerUp | duration:400 | 20 |

> Special攻撃は5回の多段ダメージ後に味方全体へ攻撃力+20、持続400フレームのバフを付与。

#### シーケンス設定

```
condition_type: ElapsedTime
condition_value: 1300
sequence_element_id: 2
action_type: SummonEnemy
action_value: c_hut_00101_hut1_challenge2_Normal_Blue
summon_position: （未指定）
summon_count: 1
enemy_hp_coef: 14
sequence_group_id: （デフォルト）
```

ElapsedTime 1300フレーム時点で1体召喚。ボス（element_id: 1）より遅いタイミングでの出現。HPコエフ14はボスの37と比べ低め。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド |
|-----------|---------|----------|
| なし（全コマNone） | - | - |

---

### event_hut1_challenge_00003（イベント）

#### このステージでの役割

challengeシリーズの第3ステージ。幸山 厘（Red）はボス・他キャラと共に登場するサポート型中程度敵。ElapsedTimeトリガーで1体召喚され、同ステージには他のキャラ（chara_hut_00201のRed）も配置される。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_hut_00101_hut1_challeng3_Normal_Red` | Normal | Support | Red | 10,000 | 100 | 35.0 | 0.42 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_hut_00101_hut1_challeng3_Normal_Red` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 79 |
| Special | 0 | なし | なし | 129 |

**Normal攻撃（c_hut_00101_hut1_challeng3_Normal_Red_Normal_00000）**

| sort_order | attack_type | range | max_targets | target | damage_type | effect_type | power_parameter |
|-----------|------------|-------|------------|--------|------------|------------|----------------|
| 1 | Direct | Distance 0〜0.63 | 1 | Foe/All/All/All | Damage/Normal | None | Percentage 100% |

**Special攻撃（c_hut_00101_hut1_challeng3_Normal_Red_Special_00001）**

| sort_order | attack_type | range | max_targets | target | damage_type | effect_type | effect_parameter | effect_value |
|-----------|------------|-------|------------|--------|------------|------------|-----------------|-------------|
| 1〜5 | Direct | Distance 0〜0.63 | 100 | Foe/All/All/All | Damage/Normal | None | - | Percentage 40% |
| 6 | Direct | Distance -1〜1.0 | 100 | Friend/All/All/All | None/Normal | AttackPowerUp | duration:400 | 20 |

> Special攻撃は5回多段ダメージ後に味方全体へ攻撃力+20バフ付与（持続400フレーム）。

#### シーケンス設定

```
condition_type: ElapsedTime
condition_value: 1200
sequence_element_id: 2
action_type: SummonEnemy
action_value: c_hut_00101_hut1_challeng3_Normal_Red
summon_position: （未指定）
summon_count: 1
enemy_hp_coef: 12
sequence_group_id: （デフォルト）
```

ElapsedTime 1200フレームで1体召喚。HPコエフ12で設定されており、ボス（HPコエフ32）の中で比較的軽めの配置。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド |
|-----------|---------|----------|
| なし（全コマNone） | - | - |

---

### event_hut1_challenge_00004（イベント）

#### このステージでの役割

challengeシリーズの第4ステージ（最終）。幸山 厘（Yellow）はボスと他のはーとぶれいくキャラ群と共に配置され、ElapsedTimeトリガーで1体、さらにFriendUnitDeadトリガーで2回目の補充召喚が設定されている。コマ効果にSlipDamageが含まれる設計で、難易度の高い終盤ステージ。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_hut_00101_hut1_challenge4_Normal_Yellow` | Normal | Support | Yellow | 10,000 | 100 | 35.0 | 0.42 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_hut_00101_hut1_challenge4_Normal_Yellow` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 79 |
| Special | 0 | なし | なし | 129 |

**Normal攻撃（c_hut_00101_hut1_challenge4_Normal_Yellow_Normal_00000）**

| sort_order | attack_type | range | max_targets | target | damage_type | effect_type | power_parameter |
|-----------|------------|-------|------------|--------|------------|------------|----------------|
| 1 | Direct | Distance 0〜0.63 | 1 | Foe/All/All/All | Damage/Normal | None | Percentage 100% |

**Special攻撃（c_hut_00101_hut1_challenge4_Normal_Yellow_Special_00001）**

| sort_order | attack_type | range | max_targets | target | damage_type | effect_type | effect_parameter | effect_value |
|-----------|------------|-------|------------|--------|------------|------------|-----------------|-------------|
| 1〜5 | Direct | Distance 0〜0.63 | 100 | Foe/All/All/All | Damage/Normal | None | - | Percentage 40% |
| 6 | Direct | Distance -1〜1.0 | 100 | Friend/All/All/All | None/Normal | AttackPowerUp | duration:400 | 20 |

> Special攻撃は5回多段ダメージ後に味方全体へ攻撃力+20バフ付与（持続400フレーム）。

#### シーケンス設定

```
（ElapsedTimeトリガー）
condition_type: ElapsedTime
condition_value: 3000
sequence_element_id: 2
action_type: SummonEnemy
action_value: c_hut_00101_hut1_challenge4_Normal_Yellow
summon_position: （未指定）
summon_count: 1
enemy_hp_coef: 13
sequence_group_id: （デフォルト）

（FriendUnitDeadトリガー・補充召喚）
condition_type: FriendUnitDead
condition_value: 2
sequence_element_id: 7
action_type: SummonEnemy
action_value: c_hut_00101_hut1_challenge4_Normal_Yellow
summon_position: （未指定）
summon_count: 1
summon_interval: 500
enemy_hp_coef: 13
sequence_group_id: （デフォルト）
```

ElapsedTime 3000フレームで初期召喚。ボス（2500フレーム）より後から登場。さらにFriendUnitDead 2（2体目の敵撃破時）に補充召喚される設計で、倒しても戻ってくる。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド |
|-----------|---------|----------|
| SlipDamage | 1 | Player |

---

### event_hut1_charaget02_00001（イベント）

#### このステージでの役割

キャラゲット（charaget02）シリーズの第1ステージ。幸山 厘（Yellow）が主要敵として登場し、ElapsedTimeとFriendUnitDeadの2パターンで召喚される。HPコエフ8とやや低めの設定で、汎用敵（e_glo_00001）が大量に押し寄せる中での補助的な強敵として機能する。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_hut_00101_hut1_charaget02_Normal_Yellow` | Normal | Support | Yellow | 1,000 | 100 | 35.0 | 0.42 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_hut_00101_hut1_charaget02_Normal_Yellow` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 79 |
| Special | 0 | なし | なし | 129 |

**Appearance攻撃（c_hut_00101_hut1_charaget02_Normal_Yellow_Appearance_00002）**

登場演出専用。ダメージ・効果なし（action_framesのみ50フレーム）。

**Normal攻撃（c_hut_00101_hut1_charaget02_Normal_Yellow_Normal_00000）**

| sort_order | attack_type | range | max_targets | target | damage_type | effect_type | power_parameter |
|-----------|------------|-------|------------|--------|------------|------------|----------------|
| 1 | Direct | Distance 0〜0.63 | 1 | Foe/All/All/All | Damage/Normal | None | Percentage 100% |

**Special攻撃（c_hut_00101_hut1_charaget02_Normal_Yellow_Special_00001）**

| sort_order | attack_type | range | max_targets | target | damage_type | effect_type | effect_parameter | effect_value |
|-----------|------------|-------|------------|--------|------------|------------|-----------------|-------------|
| 1〜5 | Direct | Distance 0〜0.63 | 100 | Foe/All/All/All | Damage/Normal | None | - | Percentage 40% |
| 6 | Direct | Distance -1〜1.0 | 100 | Friend/All/All/All | None/Normal | AttackPowerUp | duration:400 | 10 |

> charaget02バリエーションはSpecial攻撃の攻撃力バフ値が10（challengeシリーズの20より低い）。

#### シーケンス設定

```
（ElapsedTimeトリガー）
condition_type: ElapsedTime
condition_value: 1200
sequence_element_id: 1
action_type: SummonEnemy
action_value: c_hut_00101_hut1_charaget02_Normal_Yellow
summon_position: （未指定）
summon_count: 1
summon_interval: （未設定）
enemy_hp_coef: 8
sequence_group_id: （デフォルト）

（FriendUnitDeadトリガー・補充召喚）
condition_type: FriendUnitDead
condition_value: 1
sequence_element_id: 4
action_type: SummonEnemy
action_value: c_hut_00101_hut1_charaget02_Normal_Yellow
summon_position: （未指定）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 8
sequence_group_id: （デフォルト）
```

ElapsedTime 1200フレームで初期召喚し、FriendUnitDead 1（1体目撃破）でも補充される。このステージでは汎用敵15体の大量投入との組み合わせで難易度を設計している。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド |
|-----------|---------|----------|
| なし（全コマNone） | - | - |

---

### event_hut1_charaget02_00002（イベント）

#### このステージでの役割

キャラゲット（charaget02）シリーズの第2ステージ。幸山 厘（Yellow）はHPコエフ1と非常に低い設定で配置され、このステージでは幸山 厘自体が主軸ではなくボス（chara_hut_00001）がメインの強敵。幸山 厘はElapsedTimeとFriendUnitDeadで1体ずつ召喚される補助的な役割。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_hut_00101_hut1_charaget02_Normal_Yellow` | Normal | Support | Yellow | 1,000 | 100 | 35.0 | 0.42 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_hut_00101_hut1_charaget02_Normal_Yellow` | なし | None | なし | なし |

#### 攻撃パターン

（event_hut1_charaget02_00001と同一パラメータIDを使用のため同じ攻撃パターン）

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 79 |
| Special | 0 | なし | なし | 129 |

> Appearance（登場演出）・Normal（単体ダメージ100%）・Special（5回多段40%＋攻撃力+10バフ）の3種。詳細はevent_hut1_charaget02_00001の攻撃パターン欄を参照。

#### シーケンス設定

```
（ElapsedTimeトリガー）
condition_type: ElapsedTime
condition_value: 2000
sequence_element_id: 2
action_type: SummonEnemy
action_value: c_hut_00101_hut1_charaget02_Normal_Yellow
summon_position: （未指定）
summon_count: 1
summon_interval: 1
enemy_hp_coef: 1
sequence_group_id: （デフォルト）

（FriendUnitDeadトリガー・補充召喚）
condition_type: FriendUnitDead
condition_value: 1
sequence_element_id: 4
action_type: SummonEnemy
action_value: c_hut_00101_hut1_charaget02_Normal_Yellow
summon_position: （未指定）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 1
sequence_group_id: （デフォルト）
```

HPコエフ1は非常に低い設定。このステージではボス（chara_hut_00001、OutpostHpPercentage 99%でゲーム開始直後に即召喚）が主役であり、幸山 厘は補助的な撃破ターゲットとして機能する。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド |
|-----------|---------|----------|
| なし（全コマNone） | - | - |

---

### raid_hut1_00001（降臨バトル）

#### このステージでの役割

降臨バトル（raid）で唯一登場するステージ。幸山 厘（Colorless）はウェーブ5（w5）に配置されており、ゲーム終盤の高難易度フェーズで登場する。HPコエフ300と全配置の中で最も高く設定されており、降臨バトルにおける要の敵として機能する。FriendUnitDead補充召喚も設定されており、倒すと再登場する。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_hut_00101_hut1_advent_Normal_Colorless` | Normal | Support | Colorless | 1,000 | 100 | 35.0 | 0.35 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_hut_00101_hut1_advent_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 79 |
| Special | 0 | なし | なし | 129 |

**Normal攻撃（c_hut_00101_hut1_advent_Normal_Colorless_Normal_00000）**

| sort_order | attack_type | range | max_targets | target | damage_type | effect_type | power_parameter |
|-----------|------------|-------|------------|--------|------------|------------|----------------|
| 1 | Direct | Distance 0〜0.36 | 1 | Foe/All/All/All | Damage/Normal | None | Percentage 100% |

> adventバリエーションはrange_end_parameterが0.36と短く、近接型の攻撃範囲。

**Special攻撃（c_hut_00101_hut1_advent_Normal_Colorless_Special_00001）**

| sort_order | attack_type | range | max_targets | target | damage_type | effect_type | effect_parameter | effect_value |
|-----------|------------|-------|------------|--------|------------|------------|-----------------|-------------|
| 1 | Direct | Distance 0〜0.46 | 100 | Foe/All/All/All | Damage/Normal | None | - | Percentage 10% |
| 2 | Direct | Distance 0〜0.46 | 100 | Foe/All/All/All | Damage/Normal | None | - | Percentage 10% |
| 3 | Direct | Distance 0〜0.46 | 100 | Foe/All/All/All | Damage/Normal | None | - | Percentage 10% |
| 4 | Direct | Distance 0〜0.46 | 100 | Foe/All/All/All | Damage/Normal | None | - | Percentage 20% |
| 5 | Direct | Distance 0〜0.46 | 100 | Foe/All/All/All | Damage/Normal | None | - | Percentage 50% |
| 6 | Direct | Distance -50〜50.0 | 100 | Friend/All/All/All | None/Normal | AttackPowerUp | duration:500 | 10 |

> adventバリエーションのSpecial攻撃は10%×3＋20%＋50%の変動多段ダメージ（計100%）後に味方全体へ攻撃力+10バフ（持続500フレーム）。challengeシリーズとは多段構成と持続フレームが異なる。

#### シーケンス設定

```
（ElapsedTimeSinceSequenceGroupActivatedトリガー）
condition_type: ElapsedTimeSinceSequenceGroupActivated
condition_value: 300
sequence_element_id: 27
action_type: SummonEnemy
action_value: c_hut_00101_hut1_advent_Normal_Colorless
summon_position: （未指定）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 300
sequence_group_id: w5

（FriendUnitDeadトリガー・補充召喚）
condition_type: FriendUnitDead
condition_value: 27
sequence_element_id: 28
action_type: SummonEnemy
action_value: c_hut_00101_hut1_advent_Normal_Colorless
summon_position: （未指定）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 300
sequence_group_id: w5
```

ウェーブ5（w5）はSwitchSequenceGroupにより20体目の敵撃破時に切り替わるフェーズ。幸山 厘はw5開始300フレーム後に登場し、撃破されても即補充召喚される。HPコエフ300は降臨バトル全体でも高水準であり、耐久力の高い強敵として機能する。ウェーブ構造（w1〜w6のSwitchSequenceGroup）を持つ多フェーズバトルの一部。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド |
|-----------|---------|----------|
| SlipDamage | 2 | Player |
| Burn | 1 | Player |
| Poison | 1 | Player |

> 降臨バトルでは複数のコマラインにSlipDamage・Burn・Poisonが設定されており、継続ダメージ系の嫌がらせ効果が強いステージ設計。
