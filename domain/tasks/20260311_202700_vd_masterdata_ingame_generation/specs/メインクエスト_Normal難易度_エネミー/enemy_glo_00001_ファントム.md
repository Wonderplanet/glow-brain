# ファントム（enemy_glo_00001）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: enemy_glo_00001
> mst_series_id: glo
> 作品名: 不明（MstSeries/MstSeriesI18nに未登録）

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `enemy_glo_00001` |
| mst_series_id | `glo` |
| 作品名 | 不明（MstSeries/MstSeriesI18nにgloシリーズのレコードが存在しない） |
| asset_key | `enemy_glo_00001` |
| is_phantomized | `0` |

---

## 2. キャラクター特徴まとめ

ファントム（enemy_glo_00001）は、メインクエスト Normalの19ステージ（normal_aka_00001〜normal_tak_00003）に広く登場する汎用エネミーである。使用パラメータは13種類と非常に多彩で、作品横断型の汎用雑魚として各シリーズに配置されるほか、一部のステージ（normal_osh系）ではシリーズ固有のパラメータが使われる。

**ステータスレンジ感**: HPは1,000（軽量雑魚）から72,000（超高耐久）まで幅広く、攻撃力も50〜1,700と大きな差がある。通常の雑魚枠（`general_n_`系）はHP1,000・攻撃力50と最弱クラスだが、`general_rik_vh_`（HP72,000・攻撃力480）や`general_sur_vh_`（HP8,000・攻撃力1,700）はHard寄りの高難易度設定に近い強敵枠。

**character_unit_kind・role_typeの傾向**: Normal枠が大半（12/13パラメータ）で、Boss枠（`osh_n_Boss_Yellow`）が1種のみ存在する。role_typeはAttackが中心（10種）、Technicalが2種、Defenseが2種。

**変身設定**: 全パラメータで変身なし（transformationConditionType = None）。

**コマ効果の傾向**: SlipDamage（6回）が最多で、Darkness（5回）、AttackPowerDown（4回）、Burn（3回）と続く。全て対Player側への妨害効果が中心。

---

## 3. ステージ別使用実態

### normal_aka_00001（メインクエスト Normal）

#### このステージでの役割

`e_glo_00001_general_n_Normal_Colorless`（HP1,000・攻撃力50）という最軽量雑魚枠が使われている。ElapsedTime条件で計8体（5体+3体）を時間差で召喚するシンプルな構成で、序盤の流量調整役として機能する。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_glo_00001_general_n_Normal_Colorless` | Normal | Attack | Colorless | 1,000 | 50 | 40 | 0.2 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_glo_00001_general_n_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 50 |

**MstAttackElementの詳細**

| sort_order | attack_type | range_end_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter_type | power_parameter | effect_type |
|-----------|------------|---------------|---------------------|-----------------|--------|-------------|---------|---------------------|----------------|------------|
| 1 | Direct | Distance | 0.23 | 1 | Foe | Damage | Normal | Percentage | 100.0 | None |

#### シーケンス設定

```
【elem 2】
condition_type: ElapsedTime
condition_value: 350
action_type: SummonEnemy
action_value: e_glo_00001_general_n_Normal_Colorless
summon_position: （指定なし）
summon_count: 5
summon_interval: 750
enemy_hp_coef: 3.5
sequence_group_id: （なし）

【elem 3】
condition_type: ElapsedTime
condition_value: 2000
action_type: SummonEnemy
action_value: e_glo_00001_general_n_Normal_Colorless
summon_position: （指定なし）
summon_count: 3
summon_interval: 300
enemy_hp_coef: 3.5
sequence_group_id: （なし）
```

ElapsedTimeのみで制御。序盤（350フレーム）に5体、後半（2000フレーム）に3体を波状に送り込む単純なスクランブル構成。enemy_hp_coef=3.5でHPが3.5倍に補正されている。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| None（効果なし） | 全ライン | ー |

---

### normal_aka_00002（メインクエスト Normal）

#### このステージでの役割

`e_glo_00001_general_n_Normal_Colorless`（HP1,000・攻撃力50）の最軽量雑魚がメインで、3エレメント・合計26体を時間差で大量召喚する。敵の物量押しが特徴のステージ。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_glo_00001_general_n_Normal_Colorless` | Normal | Attack | Colorless | 1,000 | 50 | 40 | 0.2 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_glo_00001_general_n_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 50 |

**MstAttackElementの詳細**

| sort_order | attack_type | range_end_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter_type | power_parameter | effect_type |
|-----------|------------|---------------|---------------------|-----------------|--------|-------------|---------|---------------------|----------------|------------|
| 1 | Direct | Distance | 0.23 | 1 | Foe | Damage | Normal | Percentage | 100.0 | None |

#### シーケンス設定

```
【elem 2】
condition_type: ElapsedTime
condition_value: 250
action_type: SummonEnemy
action_value: e_glo_00001_general_n_Normal_Colorless
summon_position: （指定なし）
summon_count: 10
summon_interval: 450
enemy_hp_coef: 3.5
sequence_group_id: （なし）

【elem 3】
condition_type: ElapsedTime
condition_value: 100
action_type: SummonEnemy
action_value: e_glo_00001_general_n_Normal_Colorless
summon_position: （指定なし）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 3.5
sequence_group_id: （なし）

【elem 4】
condition_type: ElapsedTime
condition_value: 2000
action_type: SummonEnemy
action_value: e_glo_00001_general_n_Normal_Colorless
summon_position: （指定なし）
summon_count: 15
summon_interval: 550
enemy_hp_coef: 3.5
sequence_group_id: （なし）
```

3段階の時間差召喚で計26体を投入。最初から物量で押すステージ設計。enemy_hp_coef=3.5。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| AttackPowerDown | 4 | Player |

---

### normal_aka_00003（メインクエスト Normal）

#### このステージでの役割

`e_glo_00001_general_n_Normal_Red`（HP1,000・攻撃力50・Red）がメイン敵として機能。InitialSummonで2体配置し、ElapsedTime+FriendUnitDeadで補充し続ける構成。FriendUnitDead系が4エレメント連続で設定されており、味方が倒れるたびに増援が押し寄せる設計。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_glo_00001_general_n_Normal_Red` | Normal | Attack | Red | 1,000 | 50 | 40 | 0.2 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_glo_00001_general_n_Normal_Red` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 50 |

**MstAttackElementの詳細**

| sort_order | attack_type | range_end_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter_type | power_parameter | effect_type |
|-----------|------------|---------------|---------------------|-----------------|--------|-------------|---------|---------------------|----------------|------------|
| 1 | Direct | Distance | 0.23 | 1 | Foe | Damage | Normal | Percentage | 100.0 | None |

#### シーケンス設定

```
【elem 3】
condition_type: InitialSummon
condition_value: 0
action_type: SummonEnemy
action_value: e_glo_00001_general_n_Normal_Red
summon_position: 1.4
summon_count: 1
summon_interval: 0
enemy_hp_coef: 3.5
sequence_group_id: （なし）

【elem 4】
condition_type: InitialSummon
condition_value: 0
action_type: SummonEnemy
action_value: e_glo_00001_general_n_Normal_Red
summon_position: 2.3
summon_count: 1
summon_interval: 0
enemy_hp_coef: 3.5
sequence_group_id: （なし）

【elem 5】
condition_type: ElapsedTime
condition_value: 700
action_type: SummonEnemy
action_value: e_glo_00001_general_n_Normal_Red
summon_position: （指定なし）
summon_count: 10
summon_interval: 450
enemy_hp_coef: 3.5
sequence_group_id: （なし）

【elem 6】
condition_type: FriendUnitDead
condition_value: 1
action_type: SummonEnemy
action_value: e_glo_00001_general_n_Normal_Red
summon_position: （指定なし）
summon_count: 2
summon_interval: 500
enemy_hp_coef: 3.5
sequence_group_id: （なし）

【elem 7】
condition_type: FriendUnitDead
condition_value: 2
action_type: SummonEnemy
action_value: e_glo_00001_general_n_Normal_Red
summon_position: （指定なし）
summon_count: 3
summon_interval: 750
enemy_hp_coef: 3.5
sequence_group_id: （なし）

【elem 8】
condition_type: FriendUnitDead
condition_value: 3
action_type: SummonEnemy
action_value: e_glo_00001_general_n_Normal_Red
summon_position: （指定なし）
summon_count: 3
summon_interval: 250
enemy_hp_coef: 3.5
sequence_group_id: （なし）

【elem 9】
condition_type: FriendUnitDead
condition_value: 4
action_type: SummonEnemy
action_value: e_glo_00001_general_n_Normal_Red
summon_position: （指定なし）
summon_count: 3
summon_interval: 100
enemy_hp_coef: 3.5
sequence_group_id: （なし）
```

InitialSummonで2体の位置指定配置後、ElapsedTimeで10体、FriendUnitDead（1〜4体撃破時）で段階的補充という多重条件設計。味方の消耗に連動して増援が加速する。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| AttackPowerDown | 4 | Player |

---

### normal_dan_00001（メインクエスト Normal）

#### このステージでの役割

`e_glo_00001_general_n_Normal_Colorless`（HP1,000・攻撃力50）がサブ敵として登場。ElapsedTime=500フレームで5体を比較的ゆっくりした間隔（summon_interval=1200）で送り込む。enemy_hp_coef=9.5という高倍率設定が特徴的で、HPが実質9,500相当に強化される。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_glo_00001_general_n_Normal_Colorless` | Normal | Attack | Colorless | 1,000 | 50 | 40 | 0.2 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_glo_00001_general_n_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 50 |

**MstAttackElementの詳細**

| sort_order | attack_type | range_end_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter_type | power_parameter | effect_type |
|-----------|------------|---------------|---------------------|-----------------|--------|-------------|---------|---------------------|----------------|------------|
| 1 | Direct | Distance | 0.23 | 1 | Foe | Damage | Normal | Percentage | 100.0 | None |

#### シーケンス設定

```
【elem 2】
condition_type: ElapsedTime
condition_value: 500
action_type: SummonEnemy
action_value: e_glo_00001_general_n_Normal_Colorless
summon_position: （指定なし）
summon_count: 5
summon_interval: 1200
enemy_hp_coef: 9.5
sequence_group_id: （なし）
```

単一のElapsedTimeで5体を間隔広めに召喚。enemy_hp_coef=9.5で実質高耐久な点が差別化ポイント。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| Darkness | 1 | Player |

---

### normal_dan_00002（メインクエスト Normal）

#### このステージでの役割

`e_glo_00001_general_n_Normal_Red`（HP1,000・攻撃力50・Red）がサブ敵として登場。enemy_hp_coef=9.5。ElapsedTime=500で3体。Redカラーで色指定のある敵として機能する。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_glo_00001_general_n_Normal_Red` | Normal | Attack | Red | 1,000 | 50 | 40 | 0.2 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_glo_00001_general_n_Normal_Red` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 50 |

**MstAttackElementの詳細**

| sort_order | attack_type | range_end_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter_type | power_parameter | effect_type |
|-----------|------------|---------------|---------------------|-----------------|--------|-------------|---------|---------------------|----------------|------------|
| 1 | Direct | Distance | 0.23 | 1 | Foe | Damage | Normal | Percentage | 100.0 | None |

#### シーケンス設定

```
【elem 2】
condition_type: ElapsedTime
condition_value: 500
action_type: SummonEnemy
action_value: e_glo_00001_general_n_Normal_Red
summon_position: （指定なし）
summon_count: 3
summon_interval: 500
enemy_hp_coef: 9.5
sequence_group_id: （なし）
```

シンプルなElapsedTime召喚。Redカラーで色を使った敵構成の一環として機能。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| Darkness | 4 | Player |

---

### normal_dan_00005（メインクエスト Normal）

#### このステージでの役割

`e_glo_00001_general_n_Normal_Colorless`（HP1,000・攻撃力50）が2段階で計7体召喚される。enemy_hp_coef=5.5。後半（3250フレーム）の5体をsummon_interval=50という超短間隔で投入するバースト召喚が特徴。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_glo_00001_general_n_Normal_Colorless` | Normal | Attack | Colorless | 1,000 | 50 | 40 | 0.2 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_glo_00001_general_n_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 50 |

**MstAttackElementの詳細**

| sort_order | attack_type | range_end_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter_type | power_parameter | effect_type |
|-----------|------------|---------------|---------------------|-----------------|--------|-------------|---------|---------------------|----------------|------------|
| 1 | Direct | Distance | 0.23 | 1 | Foe | Damage | Normal | Percentage | 100.0 | None |

#### シーケンス設定

```
【elem 2】
condition_type: ElapsedTime
condition_value: 1200
action_type: SummonEnemy
action_value: e_glo_00001_general_n_Normal_Colorless
summon_position: （指定なし）
summon_count: 2
summon_interval: 1000
enemy_hp_coef: 5.5
sequence_group_id: （なし）

【elem 3】
condition_type: ElapsedTime
condition_value: 3250
action_type: SummonEnemy
action_value: e_glo_00001_general_n_Normal_Colorless
summon_position: （指定なし）
summon_count: 5
summon_interval: 50
enemy_hp_coef: 5.5
sequence_group_id: （なし）
```

中盤（1200フレーム）で2体を余裕をもって展開し、後半（3250フレーム）でバースト的に5体を同時に押し込む設計。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| None（効果なし） | 全ライン | ー |

---

### normal_glo1_00003（メインクエスト Normal）

#### このステージでの役割

glo作品の第1章最終ステージ（glo1_00003）。`e_glo_00001_general_vh_Normal_Colorless`（HP10,000・攻撃力100・Defense・Colorless）と`e_glo_00001_general_h_Normal_Blue`（HP10,000・攻撃力100・Blue）の2種類が登場する。enemy_hp_coef=0.9（vh枠）/ 0.3（h枠）と低倍率設定で、HPを下げた調整が施されている。InitialSummonで位置指定配置+ElapsedTime追加召喚の複合構成。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_glo_00001_general_vh_Normal_Colorless` | Normal | Defense | Colorless | 10,000 | 100 | 40 | 0.2 | 1 |
| `e_glo_00001_general_h_Normal_Blue` | Normal | Attack | Blue | 10,000 | 100 | 40 | 0.2 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_glo_00001_general_vh_Normal_Colorless` | なし | None | なし | なし |
| `e_glo_00001_general_h_Normal_Blue` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 50 |

**MstAttackElement（vh_Normal_Colorless）**

| sort_order | attack_type | range_end_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter_type | power_parameter | effect_type |
|-----------|------------|---------------|---------------------|-----------------|--------|-------------|---------|---------------------|----------------|------------|
| 1 | Direct | Distance | 0.23 | 1 | Foe | Damage | Normal | Percentage | 100.0 | None |

**MstAttackElement（h_Normal_Blue）**

| sort_order | attack_type | range_end_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter_type | power_parameter | effect_type |
|-----------|------------|---------------|---------------------|-----------------|--------|-------------|---------|---------------------|----------------|------------|
| 1 | Direct | Distance | 0.23 | 1 | Foe | Damage | Normal | Percentage | 100.0 | None |

#### シーケンス設定

```
【elem 2】
condition_type: ElapsedTime
condition_value: 750
action_type: SummonEnemy
action_value: e_glo_00001_general_vh_Normal_Colorless
summon_position: （指定なし）
summon_count: 2
summon_interval: 50
enemy_hp_coef: 0.9
sequence_group_id: （なし）

【elem 3】
condition_type: ElapsedTime
condition_value: 1000
action_type: SummonEnemy
action_value: e_glo_00001_general_h_Normal_Blue
summon_position: （指定なし）
summon_count: 4
summon_interval: 500
enemy_hp_coef: 0.3
sequence_group_id: （なし）

【elem 4】
condition_type: InitialSummon
condition_value: 0
action_type: SummonEnemy
action_value: e_glo_00001_general_h_Normal_Blue
summon_position: 1.2
summon_count: 1
summon_interval: 0
enemy_hp_coef: 0.3
sequence_group_id: （なし）

【elem 5】
condition_type: InitialSummon
condition_value: 0
action_type: SummonEnemy
action_value: e_glo_00001_general_h_Normal_Blue
summon_position: 1.8
summon_count: 1
summon_interval: 0
enemy_hp_coef: 0.3
sequence_group_id: （なし）

【elem 6】
condition_type: ElapsedTime
condition_value: 3200
action_type: SummonEnemy
action_value: e_glo_00001_general_vh_Normal_Colorless
summon_position: （指定なし）
summon_count: 3
summon_interval: 500
enemy_hp_coef: 0.9
sequence_group_id: （なし）
```

初期配置としてBlue×2体（位置1.2、1.8）を指定し、ElapsedTimeでColorless・Blueを段階的に追加。2色使いでカラー対応の選択肢を問うステージ。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| SlipDamage | 3 | Player |

---

### normal_glo2_00002（メインクエスト Normal）

#### このステージでの役割

`e_glo_00001_general_Normal_Colorless`（HP5,000・攻撃力100・Attack・Colorless）が中量級の雑魚として登場。ElapsedTime=400フレームで30体という大量召喚（summon_interval=750）。enemy_hp_coef=1.6。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_glo_00001_general_Normal_Colorless` | Normal | Attack | Colorless | 5,000 | 100 | 34 | 0.22 | 3 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_glo_00001_general_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 50 |

**MstAttackElementの詳細**

| sort_order | attack_type | range_end_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter_type | power_parameter | effect_type |
|-----------|------------|---------------|---------------------|-----------------|--------|-------------|---------|---------------------|----------------|------------|
| 1 | Direct | Distance | 0.23 | 1 | Foe | Damage | Normal | Percentage | 100.0 | None |

#### シーケンス設定

```
【elem 7】
condition_type: ElapsedTime
condition_value: 400
action_type: SummonEnemy
action_value: e_glo_00001_general_Normal_Colorless
summon_position: （指定なし）
summon_count: 30
summon_interval: 750
enemy_hp_coef: 1.6
sequence_group_id: （なし）
```

単一エレメントで30体という大量物量召喚。Colorlessで色を問わないシンプルな壁役。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| Gust | 1 | Player |
| AttackPowerUp | 2 | （AttackPowerUp系） |

---

### normal_glo2_00003（メインクエスト Normal）

#### このステージでの役割

`e_glo_00001_mainquest_glo2_Normal_Green`（HP3,000・攻撃力100・Defense・Green）というステージ専用パラメータが使われる。enemy_hp_coef=2で実質6,000相当。ElapsedTimeで計53体（3体+50体）を召喚する物量重視の設計。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_glo_00001_mainquest_glo2_Normal_Green` | Normal | Defense | Green | 3,000 | 100 | 34 | 0.22 | 3 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_glo_00001_mainquest_glo2_Normal_Green` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 50 |

**MstAttackElementの詳細**

| sort_order | attack_type | range_end_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter_type | power_parameter | effect_type |
|-----------|------------|---------------|---------------------|-----------------|--------|-------------|---------|---------------------|----------------|------------|
| 1 | Direct | Distance | 0.23 | 1 | Foe | Damage | Normal | Percentage | 100.0 | None |

#### シーケンス設定

```
【elem 1】
condition_type: ElapsedTime
condition_value: 350
action_type: SummonEnemy
action_value: e_glo_00001_mainquest_glo2_Normal_Green
summon_position: （指定なし）
summon_count: 3
summon_interval: 150
enemy_hp_coef: 2
sequence_group_id: （なし）

【elem 2】
condition_type: ElapsedTime
condition_value: 1000
action_type: SummonEnemy
action_value: e_glo_00001_mainquest_glo2_Normal_Green
summon_position: （指定なし）
summon_count: 50
summon_interval: 600
enemy_hp_coef: 2
sequence_group_id: （なし）
```

ステージ専用Greenパラメータを大量（最大53体）投入する物量ステージ。Defense roleで粘り強い。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| AttackPowerUp | 2 | Player（ステージ内の他ラインで付与） |

---

### normal_glo3_00001（メインクエスト Normal）

#### このステージでの役割

`e_glo_00001_general_rik_vh_Normal_Yellow`（HP72,000・攻撃力480・Normal・Yellow）という超高耐久パラメータが登場。enemy_hp_coef=0.1で実質7,200相当に調整されている。ElapsedTimeで99体+FriendUnitDeadで99体の大量召喚設定だが、hp_coefで耐久を絞っている。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_glo_00001_general_rik_vh_Normal_Yellow` | Normal | Attack | Yellow | 72,000 | 480 | 45 | 0.11 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_glo_00001_general_rik_vh_Normal_Yellow` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 50 |

**MstAttackElementの詳細**

| sort_order | attack_type | range_end_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter_type | power_parameter | effect_type |
|-----------|------------|---------------|---------------------|-----------------|--------|-------------|---------|---------------------|----------------|------------|
| 1 | Direct | Distance | 0.2 | 1 | Foe | Damage | Normal | Percentage | 100.0 | None |

#### シーケンス設定

```
【elem 5】
condition_type: ElapsedTime
condition_value: 200
action_type: SummonEnemy
action_value: e_glo_00001_general_rik_vh_Normal_Yellow
summon_position: （指定なし）
summon_count: 99
summon_interval: 700
enemy_hp_coef: 0.1
sequence_group_id: （なし）

【elem 6】
condition_type: FriendUnitDead
condition_value: 4
action_type: SummonEnemy
action_value: e_glo_00001_general_rik_vh_Normal_Yellow
summon_position: （指定なし）
summon_count: 99
summon_interval: 1200
enemy_hp_coef: 0.1
sequence_group_id: （なし）
```

vh（very hard）パラメータをhp_coef=0.1で使うことで適切なHPに調整。Yellowカラーで色対応を問う設計。ElapsedTime+FriendUnitDeadの2条件で大量召喚。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| None（効果なし） | 全ライン | ー |

---

### normal_glo3_00002（メインクエスト Normal）

#### このステージでの役割

`e_glo_00001_general_sur_vh_Normal_Blue`（HP8,000・攻撃力1,700・Technical・Blue）という超高攻撃力パラメータが使用される。enemy_hp_coef=0.1で実質HP800に絞られるが、攻撃力1,700は非常に高く、被弾するとダメージが大きい。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_glo_00001_general_sur_vh_Normal_Blue` | Normal | Technical | Blue | 8,000 | 1,700 | 65 | 0.11 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_glo_00001_general_sur_vh_Normal_Blue` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 50 |

**MstAttackElementの詳細**

| sort_order | attack_type | range_end_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter_type | power_parameter | effect_type |
|-----------|------------|---------------|---------------------|-----------------|--------|-------------|---------|---------------------|----------------|------------|
| 1 | Direct | Distance | 0.2 | 1 | Foe | Damage | Normal | Percentage | 100.0 | None |

#### シーケンス設定

```
【elem 6】
condition_type: ElapsedTime
condition_value: 150
action_type: SummonEnemy
action_value: e_glo_00001_general_sur_vh_Normal_Blue
summon_position: （指定なし）
summon_count: 99
summon_interval: 700
enemy_hp_coef: 0.1
sequence_group_id: （なし）

【elem 7】
condition_type: FriendUnitDead
condition_value: 4
action_type: SummonEnemy
action_value: e_glo_00001_general_sur_vh_Normal_Blue
summon_position: （指定なし）
summon_count: 99
summon_interval: 1200
enemy_hp_coef: 0.1
sequence_group_id: （なし）
```

超高攻撃力（1,700）・高移動速度（65）のTechnical Blueとして、前のめりに突撃してくる構成。hp_coef=0.1でHP調整済み。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| None（効果なし） | 全ライン | ー |

---

### normal_glo3_00003（メインクエスト Normal）

#### このステージでの役割

`e_glo_00001_general_as_Normal_Red`（HP30,000・攻撃力500・Attack・Red）が主力敵として大量展開される。このパラメータは攻撃時にAttackPowerUpを自己付与（sort_order=2）するため、攻撃を許すたびに強くなる危険な敵。enemy_hp_coef=0.7。全10エレメントで多段的に召喚される。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_glo_00001_general_as_Normal_Red` | Normal | Attack | Red | 30,000 | 500 | 40 | 0.28 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_glo_00001_general_as_Normal_Red` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | next_attack_interval |
|------------|-----------|--------------|------------------|--------------|----------------------|
| Normal | 0 | なし | なし | 50 | 75 |

**MstAttackElementの詳細**

| sort_order | attack_type | range_end_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter_type | power_parameter | effect_type | effective_count | effective_duration | effect_parameter |
|-----------|------------|---------------|---------------------|-----------------|--------|-------------|---------|---------------------|----------------|------------|----------------|-------------------|----------------|
| 1 | Direct | Distance | 0.3 | 1 | Foe | Damage | Normal | Percentage | 100.0 | None | 0 | 0 | 0 |
| 2 | Direct | Page | 50.0 | 100 | Self | None | Normal | Percentage | 0.0 | AttackPowerUp | -1 | 99999 | 5 |

攻撃時にSelf対象・無限回数（effective_count=-1）・永続（effective_duration=99999）のAttackPowerUpを付与する特殊な攻撃パターン。攻撃するたびに攻撃力が5累積強化される。

#### シーケンス設定

```
【elem 1】ElapsedTime / 50 / count:5 / interval:500 / hp_coef:0.7
【elem 2】ElapsedTime / 1100 / count:99 / interval:500 / hp_coef:0.7
【elem 3】ElapsedTime / 1500 / count:5 / interval:500 / hp_coef:0.7
【elem 4】ElapsedTime / 2500 / count:5 / interval:300 / hp_coef:0.7
【elem 5】ElapsedTime / 4200 / count:99 / interval:600 / hp_coef:0.7
【elem 6】ElapsedTime / 4500 / count:99 / interval:700 / hp_coef:0.7
【elem 7】ElapsedTime / 4600 / count:10 / interval:500 / hp_coef:0.7
【elem 8】ElapsedTime / 4300 / count:1 / interval:0 / hp_coef:0.7
【elem 10】ElapsedTime / 5500 / count:99 / interval:400 / hp_coef:0.7
【elem 11】OutpostDamage / 1 / count:99 / interval:1200 / hp_coef:0.7
【elem 12】OutpostDamage / 1 / count:99 / interval:1200 / hp_coef:0.7
【elem 13】OutpostDamage / 1 / count:2 / interval:1200 / hp_coef:0.7
【elem 14】OutpostDamage / 1 / count:2 / interval:1200 / hp_coef:0.7
```

全14エレメント（elem番号飛びあり）でElapsedTime（10エレメント）とOutpostDamage（4エレメント）を組み合わせた複合設計。アウトポストへの攻撃が更なる増援を呼ぶ。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| None（効果なし） | 全ライン | ー |

---

### normal_glo4_00003（メインクエスト Normal）

#### このステージでの役割

`e_glo_00001_general_as_Normal_Yellow`（HP25,000・攻撃力500・Technical・Yellow）が主力として展開される。このパラメータは攻撃時に自己Heal（MaxHP10%）を行うため、削り切れないと回復し続ける。enemy_hp_coef=1.0（等倍）。大量召喚+OutpostDamageトリガーの複合構成。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_glo_00001_general_as_Normal_Yellow` | Normal | Technical | Yellow | 25,000 | 500 | 40 | 0.2 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_glo_00001_general_as_Normal_Yellow` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | next_attack_interval |
|------------|-----------|--------------|------------------|--------------|----------------------|
| Normal | 0 | なし | なし | 50 | 50 |

**MstAttackElementの詳細**

| sort_order | attack_type | range_end_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter_type | power_parameter | effect_type | effective_count | effective_duration | effect_parameter |
|-----------|------------|---------------|---------------------|-----------------|--------|-------------|---------|---------------------|----------------|------------|----------------|-------------------|----------------|
| 1 | Direct | Distance | 0.25 | 1 | Foe | Damage | Normal | Percentage | 100.0 | None | 0 | 0 | 0 |
| 2 | Direct | Page | 50.0 | 100 | Self | None | Normal | MaxHpPercentage | 10.0 | None | 0 | 0 | 0 |

攻撃時に自己をHeal（MaxHP10%回復）する特殊攻撃パターン。sort_order=2の効果でSelf対象・MaxHpPercentage10.0のHealが実行される。

#### シーケンス設定

```
【elem 1】ElapsedTime / 100 / count:3 / interval:500 / hp_coef:1
【elem 2】ElapsedTime / 1500 / count:5 / interval:500 / hp_coef:1
【elem 3】ElapsedTime / 2650 / count:2 / interval:600 / hp_coef:1
【elem 4】ElapsedTime / 2800 / count:99 / interval:600 / hp_coef:1
【elem 5】ElapsedTime / 3500 / count:3 / interval:700 / hp_coef:1
【elem 6】ElapsedTime / 3600 / count:3 / interval:700 / hp_coef:1
【elem 7】ElapsedTime / 4000 / count:99 / interval:700 / hp_coef:1
【elem 8】ElapsedTime / 6000 / count:1 / interval:0 / hp_coef:1
【elem 10】ElapsedTime / 5500 / count:3 / interval:500 / hp_coef:1
【elem 11】ElapsedTime / 6500 / count:99 / interval:800 / hp_coef:1
【elem 12】OutpostDamage / 1 / count:99 / interval:800 / hp_coef:1
【elem 13】OutpostDamage / 1 / count:99 / interval:800 / hp_coef:1
```

ElapsedTime10エレメント+OutpostDamage2エレメントの複合。自己回復持ちYellowが継続的に押し寄せる。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| None（効果なし） | 全ライン | ー |

---

### normal_gom_00003（メインクエスト Normal）

#### このステージでの役割

`e_glo_00001_general_n_Normal_Colorless`（HP1,000・攻撃力50）が最軽量雑魚として登場。enemy_hp_coef=12という非常に高い倍率設定で、実質HP12,000相当。ElapsedTimeで3体ずつ（3200、3250、3300フレーム時）と後半（6000〜6100フレーム時）に3体ずつ、計6体を間隔なし（summon_interval=0）でほぼ同時に配置。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_glo_00001_general_n_Normal_Colorless` | Normal | Attack | Colorless | 1,000 | 50 | 40 | 0.2 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_glo_00001_general_n_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 50 |

**MstAttackElementの詳細**

| sort_order | attack_type | range_end_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter_type | power_parameter | effect_type |
|-----------|------------|---------------|---------------------|-----------------|--------|-------------|---------|---------------------|----------------|------------|
| 1 | Direct | Distance | 0.23 | 1 | Foe | Damage | Normal | Percentage | 100.0 | None |

#### シーケンス設定

```
【elem 2】
condition_type: ElapsedTime
condition_value: 3200
action_type: SummonEnemy
action_value: e_glo_00001_general_n_Normal_Colorless
summon_position: （指定なし）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 12
sequence_group_id: （なし）

【elem 3】
condition_type: ElapsedTime
condition_value: 3250
action_type: SummonEnemy
action_value: e_glo_00001_general_n_Normal_Colorless
summon_position: （指定なし）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 12
sequence_group_id: （なし）

【elem 4】
condition_type: ElapsedTime
condition_value: 3300
action_type: SummonEnemy
action_value: e_glo_00001_general_n_Normal_Colorless
summon_position: （指定なし）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 12
sequence_group_id: （なし）

【elem 5】
condition_type: ElapsedTime
condition_value: 6000
action_type: SummonEnemy
action_value: e_glo_00001_general_n_Normal_Colorless
summon_position: （指定なし）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 12
sequence_group_id: （なし）

【elem 6】
condition_type: ElapsedTime
condition_value: 6050
action_type: SummonEnemy
action_value: e_glo_00001_general_n_Normal_Colorless
summon_position: （指定なし）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 12
sequence_group_id: （なし）

【elem 7】
condition_type: ElapsedTime
condition_value: 6100
action_type: SummonEnemy
action_value: e_glo_00001_general_n_Normal_Colorless
summon_position: （指定なし）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 12
sequence_group_id: （なし）
```

1体ずつ50フレーム間隔で3体を同時配置するのを2回繰り返す特殊構成。enemy_hp_coef=12で見た目の軽さに反して高耐久。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| SlipDamage | 3 | Player |

---

### normal_osh_00001（メインクエスト Normal）

#### このステージでの役割

`e_glo_00001_general_osh_n_Normal_Yellow`（HP1,000・攻撃力100・Attack・Yellow）を中心に、ボス枠の`e_glo_00001_general_osh_n_Boss_Yellow`（HP1,000・攻撃力100・Boss・Yellow）も登場する多条件複合ステージ。ボス出現時にAppearanceアタック（ForcedKnockBack5・全体）が発動する。enemy_hp_coef: Normal=40、Boss=600。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_glo_00001_general_osh_n_Normal_Yellow` | Normal | Attack | Yellow | 1,000 | 100 | 34 | 0.22 | 3 |
| `e_glo_00001_general_osh_n_Boss_Yellow` | Boss | Attack | Yellow | 1,000 | 100 | 34 | 0.22 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_glo_00001_general_osh_n_Normal_Yellow` | なし | None | なし | なし |
| `e_glo_00001_general_osh_n_Boss_Yellow` | なし | None | なし | なし |

#### 攻撃パターン

**Normal_Yellow（通常攻撃）**

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | next_attack_interval |
|------------|-----------|--------------|------------------|--------------|----------------------|
| Normal | 0 | なし | なし | 50 | 100 |

MstAttackElement:

| sort_order | attack_type | range_end_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter_type | power_parameter | effect_type |
|-----------|------------|---------------|---------------------|-----------------|--------|-------------|---------|---------------------|----------------|------------|
| 1 | Direct | Distance | 0.23 | 1 | Foe | Damage | Normal | Percentage | 100.0 | None |

**Boss_Yellow（Appearance攻撃 + 通常攻撃）**

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 50 |

MstAttackElement（Appearance）:

| sort_order | attack_type | range_end_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter_type | power_parameter | effect_type |
|-----------|------------|---------------|---------------------|-----------------|--------|-------------|---------|---------------------|----------------|------------|
| 1 | Direct | Distance | 50.0 | 100 | Foe | None | ForcedKnockBack5 | Percentage | 100.0 | None |

Appearanceアタックで全体ノックバック（ForcedKnockBack5）。ボス登場演出と同時に強制ノックバック効果が発動。

MstAttackElement（Normal）:

| sort_order | attack_type | range_end_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter_type | power_parameter | effect_type |
|-----------|------------|---------------|---------------------|-----------------|--------|-------------|---------|---------------------|----------------|------------|
| 1 | Direct | Distance | 0.23 | 1 | Foe | Damage | Normal | Percentage | 100.0 | None |

#### シーケンス設定

```
【elem 2】
condition_type: EnterTargetKomaIndex
condition_value: 3
action_value: e_glo_00001_general_osh_n_Normal_Yellow
summon_position: 1.8 / count:1 / interval:0 / hp_coef:40

【elem 3 (Normal_Yellow)】
condition_type: FriendUnitDead
condition_value: 2
action_value: e_glo_00001_general_osh_n_Normal_Yellow
summon_position: （なし） / count:2 / interval:250 / hp_coef:40

【elem 3 (Boss_Yellow)】
condition_type: FriendUnitDead
condition_value: 2
action_value: e_glo_00001_general_osh_n_Boss_Yellow
summon_position: 2.7 / count:1 / interval:0 / hp_coef:600

【elem 4】
condition_type: OutpostHpPercentage
condition_value: 99
action_value: e_glo_00001_general_osh_n_Normal_Yellow
count:20 / interval:50 / hp_coef:40

【elem 5】
condition_type: OutpostHpPercentage
condition_value: 99
action_value: e_glo_00001_general_osh_n_Normal_Yellow
count:2 / interval:250 / hp_coef:40

【elem 6〜10】（sequence_group_id: w1）
condition_type: ElapsedTimeSinceSequenceGroupActivated
action_value: e_glo_00001_general_osh_n_Normal_Yellow
各エレメント: count:3〜10 / 様々な位置指定・間隔 / hp_coef:40
```

5種類の異なるcondition_typeを使用した複合設計。グループ（w1）を使いElapsedTimeSinceSequenceGroupActivatedで時間管理する高度な構成。ボス登場時は強制全体ノックバックが発動。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| None（効果なし） | 全ライン | ー |

---

### normal_osh_00003（メインクエスト Normal）

#### このステージでの役割

`e_glo_00001_general_osh_n_Normal_Green`（HP1,000・攻撃力100・Attack・Green）がメイン敵として使用される。EnterTargetKomaIndex（コマ進入）をメインのトリガーとし、全6コマ（index 0〜5）に対応する精密な位置指定召喚が特徴。FriendUnitDead条件での大量補充（15体×2回）も設定されている。enemy_hp_coef=30と高倍率。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_glo_00001_general_osh_n_Normal_Green` | Normal | Attack | Green | 1,000 | 100 | 34 | 0.22 | 3 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_glo_00001_general_osh_n_Normal_Green` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | next_attack_interval |
|------------|-----------|--------------|------------------|--------------|----------------------|
| Normal | 0 | なし | なし | 50 | 100 |

**MstAttackElementの詳細**

| sort_order | attack_type | range_end_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter_type | power_parameter | effect_type |
|-----------|------------|---------------|---------------------|-----------------|--------|-------------|---------|---------------------|----------------|------------|
| 1 | Direct | Distance | 0.23 | 1 | Foe | Damage | Normal | Percentage | 100.0 | None |

#### シーケンス設定

```
【elem 2】EnterTargetKomaIndex / 0 / pos:1.1 / count:2 / interval:100 / hp_coef:30
【elem 3】EnterTargetKomaIndex / 1 / pos:1.5 / count:3 / interval:150 / hp_coef:30
【elem 4】EnterTargetKomaIndex / 2 / pos:3.3 / count:2 / interval:250 / hp_coef:30
【elem 5】EnterTargetKomaIndex / 3 / pos:3.5 / count:3 / interval:50 / hp_coef:30
【elem 6】EnterTargetKomaIndex / 4 / pos:3.7 / count:3 / interval:500 / hp_coef:30
【elem 7】EnterTargetKomaIndex / 5 / pos:なし / count:5 / interval:150 / hp_coef:30
【elem 8】EnterTargetKomaIndex / 5 / pos:0.8 / count:3 / interval:50 / hp_coef:30
【elem 10】FriendUnitDead / 9 / pos:0.3 / count:15 / interval:50 / hp_coef:30
【elem 11】FriendUnitDead / 1 / pos:なし / count:4 / interval:250 / hp_coef:30
【elem 12】FriendUnitDead / 1 / pos:なし / count:4 / interval:50 / hp_coef:30
【elem 13】FriendUnitDead / 1 / pos:2.8 / count:2 / interval:100 / hp_coef:30
【elem 14】FriendUnitDead / 1 / pos:なし / count:3 / interval:50 / hp_coef:30
【elem 15】FriendUnitDead / 9 / pos:なし / count:15 / interval:50 / hp_coef:30
【elem 16】ElapsedTime / 4000 / pos:なし / count:3 / interval:500 / hp_coef:30
```

コマ進入ごとに精密な位置・数・間隔で召喚するリアクティブ設計。敵9体撃破で15体バースト（elem 10, 15）という大規模な補充も。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| Burn | 3 | Player |

---

### normal_tak_00001（メインクエスト Normal）

#### このステージでの役割

`e_glo_00001_general_Normal_Colorless`（HP5,000・攻撃力100・Attack・Colorless）を序盤から大量（30体+5体）に召喚する物量ステージ。enemy_hp_coef=1.3。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_glo_00001_general_Normal_Colorless` | Normal | Attack | Colorless | 5,000 | 100 | 34 | 0.22 | 3 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_glo_00001_general_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | next_attack_interval |
|------------|-----------|--------------|------------------|--------------|----------------------|
| Normal | 0 | なし | なし | 50 | 50 |

**MstAttackElementの詳細**

| sort_order | attack_type | range_end_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter_type | power_parameter | effect_type |
|-----------|------------|---------------|---------------------|-----------------|--------|-------------|---------|---------------------|----------------|------------|
| 1 | Direct | Distance | 0.23 | 1 | Foe | Damage | Normal | Percentage | 100.0 | None |

#### シーケンス設定

```
【elem 2】
condition_type: ElapsedTime
condition_value: 350
action_type: SummonEnemy
action_value: e_glo_00001_general_Normal_Colorless
summon_position: （指定なし）
summon_count: 30
summon_interval: 700
enemy_hp_coef: 1.3
sequence_group_id: （なし）

【elem 3】
condition_type: ElapsedTime
condition_value: 0
action_type: SummonEnemy
action_value: e_glo_00001_general_Normal_Colorless
summon_position: （指定なし）
summon_count: 5
summon_interval: 700
enemy_hp_coef: 1.3
sequence_group_id: （なし）
```

elem 3がElapsedTime=0で5体を即時召喚、elem 2が350フレームで30体を波状に送り込む大量物量設計。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| Darkness | 1 | Player |

---

### normal_tak_00002（メインクエスト Normal）

#### このステージでの役割

`e_glo_00001_general_Normal_Colorless`（HP5,000・攻撃力100・Colorless）がElapsedTime即時召喚（4体）とFriendUnitDead補充（4体撃破時に10体×2回）で登場。敵を倒すほど増援が来るリズムを強調したステージ。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_glo_00001_general_Normal_Colorless` | Normal | Attack | Colorless | 5,000 | 100 | 34 | 0.22 | 3 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_glo_00001_general_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | next_attack_interval |
|------------|-----------|--------------|------------------|--------------|----------------------|
| Normal | 0 | なし | なし | 50 | 50 |

**MstAttackElementの詳細**

| sort_order | attack_type | range_end_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter_type | power_parameter | effect_type |
|-----------|------------|---------------|---------------------|-----------------|--------|-------------|---------|---------------------|----------------|------------|
| 1 | Direct | Distance | 0.23 | 1 | Foe | Damage | Normal | Percentage | 100.0 | None |

#### シーケンス設定

```
【elem 1】
condition_type: ElapsedTime
condition_value: 0
action_type: SummonEnemy
action_value: e_glo_00001_general_Normal_Colorless
summon_position: （指定なし）
summon_count: 4
summon_interval: 500
enemy_hp_coef: 1.3
sequence_group_id: （なし）

【elem 2】
condition_type: FriendUnitDead
condition_value: 4
action_type: SummonEnemy
action_value: e_glo_00001_general_Normal_Colorless
summon_position: （指定なし）
summon_count: 10
summon_interval: 500
enemy_hp_coef: 1.3
sequence_group_id: （なし）

【elem 3】
condition_type: FriendUnitDead
condition_value: 4
action_type: SummonEnemy
action_value: e_glo_00001_general_Normal_Colorless
summon_position: （指定なし）
summon_count: 10
summon_interval: 800
enemy_hp_coef: 1.3
sequence_group_id: （なし）
```

ElapsedTime=0で即時4体、FriendUnitDead=4（4体撃破ごと）で10体×2のダブル補充。4体撃破で最大20体の増援が来る。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| None（効果なし） | 全ライン | ー |

---

### normal_tak_00003（メインクエスト Normal）

#### このステージでの役割

`e_glo_00001_general_Normal_Colorless`（HP5,000・攻撃力100・Colorless）を即時30体（ElapsedTime=0）+500フレーム後に2体で大量物量展開するステージ。enemy_hp_coef=1.3。tak系列の締めとして大規模召喚が特徴。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_glo_00001_general_Normal_Colorless` | Normal | Attack | Colorless | 5,000 | 100 | 34 | 0.22 | 3 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_glo_00001_general_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | next_attack_interval |
|------------|-----------|--------------|------------------|--------------|----------------------|
| Normal | 0 | なし | なし | 50 | 950 |

**MstAttackElementの詳細**

| sort_order | attack_type | range_end_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter_type | power_parameter | effect_type |
|-----------|------------|---------------|---------------------|-----------------|--------|-------------|---------|---------------------|----------------|------------|
| 1 | Direct | Distance | 0.23 | 1 | Foe | Damage | Normal | Percentage | 100.0 | None |

#### シーケンス設定

```
【elem 1】
condition_type: ElapsedTime
condition_value: 0
action_type: SummonEnemy
action_value: e_glo_00001_general_Normal_Colorless
summon_position: （指定なし）
summon_count: 30
summon_interval: 950
enemy_hp_coef: 1.3
sequence_group_id: （なし）

【elem 2】
condition_type: ElapsedTime
condition_value: 500
action_type: SummonEnemy
action_value: e_glo_00001_general_Normal_Colorless
summon_position: （指定なし）
summon_count: 2
summon_interval: 300
enemy_hp_coef: 1.3
sequence_group_id: （なし）
```

即時30体（interval=950と余裕あり）+追加2体の物量設計。next_attack_intervalもelem 1のsummon_intervalと合わせて950フレームに設定されている。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| SlipDamage | 3 | Player |
