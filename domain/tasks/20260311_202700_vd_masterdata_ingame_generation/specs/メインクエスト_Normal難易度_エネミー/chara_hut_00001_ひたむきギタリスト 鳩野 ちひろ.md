# ひたむきギタリスト 鳩野 ちひろ（chara_hut_00001）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: chara_hut_00001
> mst_series_id: hut
> 作品名: ふつうの軽音部

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `chara_hut_00001` |
| mst_series_id | `hut` |
| 作品名 | ふつうの軽音部 |
| asset_key | `chara_hut_00001` |
| is_phantomized | `1` |

---

## 2. キャラクター特徴まとめ

「ひたむきギタリスト 鳩野 ちひろ」は「ふつうの軽音部」のフレンドユニットキャラクター（`c_` プレフィックス）であり、イベントおよび降臨バトルコンテンツ全般に広く使用されている。

**コンテンツフィルタについて**: 指定された「normalクエストのNormal難易度のみ」（`normal_` プレフィックスのMstInGame）には、このキャラクターを使用したステージは存在しない。以下はフィルタ適用後の参考情報として、このキャラクターの全使用実績（イベント・降臨バトル）をまとめたものである。

- **コンテンツ**: イベント（`event_hut1_*`）が主体で、降臨バトル（`raid_hut1_00001`）にも登場する
- **HP・攻撃力レンジ**: Normal種別は HP 1,000〜10,000・攻撃力 100 と比較的低め。Boss種別は HP 10,000〜100,000・攻撃力 100〜500 と幅が広く、savageシリーズでは高HPの強敵として設定される
- **character_unit_kind・role_typeの傾向**: 全バリエーションが role_type=Defense で統一されており、防衛型の役割で一貫している。Normal種別は消耗雑魚・サポート役として、Boss種別は高耐久の中核ユニットとして機能する
- **変身設定**: 全パラメータで変身なし（transformationConditionType=None）
- **コマ効果の傾向**: Poison（毒）が最多（10回）、SlipDamage（スリップダメージ）（8回）が続き、Burn（炎上）も1例確認。いずれも Player サイドへのデバフ効果で、プレイヤーを継続的に消耗させるステージ設計が多い

---

## 3. ステータス一覧

> コンテンツフィルタ「normalクエストのNormal難易度のみ」に合致するステージが存在しないため、参考として全使用パラメータを記載する。

### 3-A. フィルタ対象（normalクエストのNormal難易度）

**対象ステージなし。** `normal_` プレフィックスのMstInGameにおいて `chara_hut_00001` の使用実績は存在しない。

### 3-B. 全パラメータ一覧（参考情報）

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|----|--------|---------|---------|--------------|
| `c_hut_00001_hut1_1d1c_Normal_Colorless` | Normal | Defense | Colorless | 10,000 | 100 | 35 | 0.21 | 1 |
| `c_hut_00001_hut1_advent_Normal_Colorless` | Normal | Defense | Colorless | 1,000 | 100 | 30 | 0.15 | 0 |
| `c_hut_00001_hut1_charaget01_Normal_Colorless` | Normal | Defense | Colorless | 10,000 | 100 | 35 | 0.21 | 1 |
| `c_hut_00001_hut1_advent_Boss_Colorless` | Boss | Defense | Colorless | 10,000 | 100 | 30 | 0.15 | 0 |
| `c_hut_00001_hut1_challeng3_Boss_Red` | Boss | Defense | Red | 10,000 | 100 | 35 | 0.21 | 1 |
| `c_hut_00001_hut1_challenge1_Boss_Green` | Boss | Defense | Green | 10,000 | 100 | 35 | 0.21 | 1 |
| `c_hut_00001_hut1_challenge2_Boss_Blue` | Boss | Defense | Blue | 10,000 | 100 | 35 | 0.21 | 1 |
| `c_hut_00001_hut1_challenge4_Boss_Yellow` | Boss | Defense | Yellow | 10,000 | 100 | 35 | 0.21 | 1 |
| `c_hut_00001_hut1_charaget02_Boss_Yellow` | Boss | Defense | Yellow | 10,000 | 100 | 35 | 0.21 | 1 |
| `c_hut_00001_hut1_savage01_Boss_Colorless` | Boss | Defense | Colorless | 100,000 | 500 | 35 | 0.15 | 1 |
| `c_hut_00001_hut1_savage02_Boss_Red` | Boss | Defense | Red | 100,000 | 500 | 35 | 0.15 | 1 |
| `c_hut_00001_hut1_savage03_Boss_Yellow` | Boss | Defense | Yellow | 100,000 | 500 | 35 | 0.15 | 1 |

**アビリティ・変身設定（全パラメータ共通）**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_hut_00001_hut1_advent_Boss_Colorless` | `enemy_ability_knockback_block` | None | なし | なし |
| `c_hut_00001_hut1_savage03_Boss_Yellow` | `enemy_ability_knockback_block` | None | なし | なし |
| 上記以外の全パラメータ | なし | None | なし | なし |

> advent系とsavage03のBoss種別のみ `enemy_ability_knockback_block`（ノックバック無効）アビリティが付与されている。

---

## 4. ステージ別使用実態（参考情報）

> 以下はコンテンツフィルタ対象外のデータであり、参考情報として記載する。

---

### event_hut1_1day_00001（イベント）

#### このステージでの役割

1日1回クエストの序盤に登場する雑魚敵。HP 10,000・Normalロールとして、プレイヤーが序盤から倒しやすい適度な難易度で配置されている。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_hut_00001_hut1_1d1c_Normal_Colorless` | Normal | Defense | Colorless | 10,000 | 100 | 35 | 0.21 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_hut_00001_hut1_1d1c_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 74 |
| Special | 0 | なし | なし | 140 |

**攻撃詳細（MstAttackElement）**

| attack_kind | attack_type | range_end_parameter | max_target_count | damage_type | hit_type | power_parameter | effect_type |
|------------|-------------|---------------------|-----------------|-------------|----------|-----------------|-------------|
| Normal | Direct | 0.32 | 1 | Damage | Normal | 100.0% | None |
| Special | Direct | 0.42 | 100 | Damage | KnockBack2 | 200.0% | None |

#### シーケンス設定

```
sequence_element_id: 1
condition_type: ElapsedTime
condition_value: 500
action_type: SummonEnemy
action_value: c_hut_00001_hut1_1d1c_Normal_Colorless
summon_position: (なし)
summon_count: 1
enemy_hp_coef: 1
sequence_group_id: (なし)
```

ElapsedTime 500（5秒後）に1体召喚。HP係数 1倍の基本設定。

#### コマ効果

コマ効果なし（該当ステージにコマ効果設定なし）

---

### event_hut1_challenge_00001（イベント）

#### このステージでの役割

チャレンジシリーズ第1弾のボスとして登場。Green色のBoss種別でHP 10,000・hp_coef 30の高耐久設定。ElapsedTime 1500で遅延出現し、プレイヤーが準備を整えた後に高い試練を与える。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_hut_00001_hut1_challenge1_Boss_Green` | Boss | Defense | Green | 10,000 | 100 | 35 | 0.21 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_hut_00001_hut1_challenge1_Boss_Green` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 74 |
| Special | 0 | なし | なし | 140 |

#### シーケンス設定

```
sequence_element_id: 1
condition_type: ElapsedTime
condition_value: 1500
action_type: SummonEnemy
action_value: c_hut_00001_hut1_challenge1_Boss_Green
summon_position: (なし)
summon_count: 1
enemy_hp_coef: 30
sequence_group_id: (なし)
```

ElapsedTime 1500（15秒後）に1体召喚。HP係数 30倍で高耐久。Appearanceアタックが設定されており登場演出あり。

#### コマ効果

コマ効果なし（該当ステージにコマ効果設定なし）

---

### event_hut1_challenge_00002（イベント）

#### このステージでの役割

チャレンジシリーズ第2弾のボス。Blue色のBoss種別でHP 10,000・hp_coef 37。ElapsedTime 1000で出現。複数の雑魚敵と組み合わせてフリーUnitDead（撃破条件）トリガーも使われた難易度の高い構成。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_hut_00001_hut1_challenge2_Boss_Blue` | Boss | Defense | Blue | 10,000 | 100 | 35 | 0.21 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_hut_00001_hut1_challenge2_Boss_Blue` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 74 |
| Special | 0 | なし | なし | 140 |

#### シーケンス設定

```
sequence_element_id: 1
condition_type: ElapsedTime
condition_value: 1000
action_type: SummonEnemy
action_value: c_hut_00001_hut1_challenge2_Boss_Blue
summon_position: (なし)
summon_count: 1
enemy_hp_coef: 37
sequence_group_id: (なし)
```

ElapsedTime 1000（10秒後）に1体召喚。HP係数 37倍。FriendUnitDead型トリガーが後半に多数配置されており、撃破が次々とトリガーを発火させる難しい構成。

#### コマ効果

コマ効果なし（該当ステージにコマ効果設定なし）

---

### event_hut1_challenge_00003（イベント）

#### このステージでの役割

チャレンジシリーズ第3弾のボス。Red色のBoss種別でHP 10,000・hp_coef 32。ElapsedTime 1700で最後に出現し、他の同作品キャラと連携する総力戦の最終到達点として機能する。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_hut_00001_hut1_challeng3_Boss_Red` | Boss | Defense | Red | 10,000 | 100 | 35 | 0.21 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_hut_00001_hut1_challeng3_Boss_Red` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 74 |
| Special | 0 | なし | なし | 140 |

#### シーケンス設定

```
sequence_element_id: 1
condition_type: ElapsedTime
condition_value: 1700
action_type: SummonEnemy
action_value: c_hut_00001_hut1_challeng3_Boss_Red
summon_position: (なし)
summon_count: 1
enemy_hp_coef: 32
sequence_group_id: (なし)
```

ElapsedTime 1700（17秒後）に1体召喚。同作品の他キャラ（hut1_00101・hut1_00201）と同時進行で登場する多対多の構成。

#### コマ効果

コマ効果なし（該当ステージにコマ効果設定なし）

---

### event_hut1_challenge_00004（イベント）

#### このステージでの役割

チャレンジシリーズ第4弾のボス。Yellow色のBoss種別でHP 10,000・hp_coef 29。FriendUnitDead条件でリポップ（撃破後に再召喚）する設定が追加されており、繰り返し倒す難易度の高いステージ。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_hut_00001_hut1_challenge4_Boss_Yellow` | Boss | Defense | Yellow | 10,000 | 100 | 35 | 0.21 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_hut_00001_hut1_challenge4_Boss_Yellow` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 74 |
| Special | 0 | なし | なし | 140 |

#### シーケンス設定（初回出現）

```
sequence_element_id: 1
condition_type: ElapsedTime
condition_value: 2500
action_type: SummonEnemy
action_value: c_hut_00001_hut1_challenge4_Boss_Yellow
summon_position: (なし)
summon_count: 1
enemy_hp_coef: 29
sequence_group_id: (なし)
```

#### シーケンス設定（リポップ）

```
sequence_element_id: 6
condition_type: FriendUnitDead
condition_value: 1
action_type: SummonEnemy
action_value: c_hut_00001_hut1_challenge4_Boss_Yellow
summon_position: (なし)
summon_count: 1
summon_interval: 140
enemy_hp_coef: 29
sequence_group_id: (なし)
```

ElapsedTime 2500（25秒後）に初回召喚し、1体目撃破後（FriendUnitDead:1）に140フレーム間隔でリポップ。ボスが再登場する連続耐久戦の設計。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| SlipDamage | 1 | Player |

---

### event_hut1_charaget01_00001（イベント）

#### このステージでの役割

キャラ獲得イベント第1弾・ステージ1の雑魚敵。HP 10,000・hp_coef 1.5という低倍率で、初心者向けの導入ステージとして機能する。InitialSummonで複数の別キャラと同時に配置される中に混在する形で登場。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_hut_00001_hut1_charaget01_Normal_Colorless` | Normal | Defense | Colorless | 10,000 | 100 | 35 | 0.21 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_hut_00001_hut1_charaget01_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 74 |
| Special | 0 | なし | なし | 140 |

**攻撃詳細（MstAttackElement）**

| attack_kind | attack_type | range_end_parameter | max_target_count | damage_type | hit_type | power_parameter | effect_type |
|------------|-------------|---------------------|-----------------|-------------|----------|-----------------|-------------|
| Normal | Direct | 0.32 | 1 | Damage | Normal | 100.0% | None |
| Special | Direct | 0.42 | 100 | Damage | KnockBack2 | 200.0% | None |

#### シーケンス設定

```
sequence_element_id: 1
condition_type: ElapsedTime
condition_value: 1000
action_type: SummonEnemy
action_value: c_hut_00001_hut1_charaget01_Normal_Colorless
summon_position: (なし)
summon_count: 1
enemy_hp_coef: 1.5
sequence_group_id: (なし)
```

ElapsedTime 1000（10秒後）に1体召喚。hp_coef 1.5と低設定で序盤雑魚として機能。

#### コマ効果

コマ効果なし（該当ステージにコマ効果設定なし）

---

### event_hut1_charaget01_00002（イベント）

#### このステージでの役割

キャラ獲得イベント第1弾・ステージ2の雑魚敵。InitialSummon条件（summon_position: 1.7）で位置指定配置される。上位ボスと同一フィールドに並立して配置される中間難易度のステージ。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_hut_00001_hut1_charaget01_Normal_Colorless` | Normal | Defense | Colorless | 10,000 | 100 | 35 | 0.21 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_hut_00001_hut1_charaget01_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

（`c_hut_00001_hut1_charaget01_Normal_Colorless` と同一。Normal: action_frames=74、Special: action_frames=140）

#### シーケンス設定

```
sequence_element_id: 2
condition_type: InitialSummon
condition_value: 0
action_type: SummonEnemy
action_value: c_hut_00001_hut1_charaget01_Normal_Colorless
summon_position: 1.7
summon_count: 1
enemy_hp_coef: 1.5
sequence_group_id: (なし)
```

InitialSummon条件で位置 1.7 に配置。ゲーム開始時に位置指定で登場する雑魚。

#### コマ効果

コマ効果なし（該当ステージにコマ効果設定なし）

---

### event_hut1_charaget01_00003（イベント）

#### このステージでの役割

キャラ獲得イベント第1弾・ステージ3の雑魚敵。InitialSummonで位置 1.9 に配置され、さらにFriendUnitDead:3（撃破後）でも再召喚される。撃破後リポップあり。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_hut_00001_hut1_charaget01_Normal_Colorless` | Normal | Defense | Colorless | 10,000 | 100 | 35 | 0.21 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_hut_00001_hut1_charaget01_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

（`c_hut_00001_hut1_charaget01_Normal_Colorless` と同一）

#### シーケンス設定（初回）

```
sequence_element_id: 3
condition_type: InitialSummon
condition_value: 0
action_type: SummonEnemy
action_value: c_hut_00001_hut1_charaget01_Normal_Colorless
summon_position: 1.9
summon_count: 1
enemy_hp_coef: 3
sequence_group_id: (なし)
```

#### シーケンス設定（リポップ）

```
sequence_element_id: 10
condition_type: FriendUnitDead
condition_value: 3
action_type: SummonEnemy
action_value: c_hut_00001_hut1_charaget01_Normal_Colorless
summon_position: (なし)
summon_count: 1
enemy_hp_coef: 3
sequence_group_id: (なし)
```

hp_coef 3。FriendUnitDead条件によりリポップする雑魚敵。

#### コマ効果

コマ効果なし（該当ステージにコマ効果設定なし）

---

### event_hut1_charaget01_00004（イベント）

#### このステージでの役割

キャラ獲得イベント第1弾・ステージ4。InitialSummon（位置 1.9）とFriendUnitDead:3（リポップ）の両方でこのキャラが使われる。hp_coef 2.5。ステージ終盤に大量のe_glo系が波状攻撃を行う中で防衛ラインを構成する役割。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_hut_00001_hut1_charaget01_Normal_Colorless` | Normal | Defense | Colorless | 10,000 | 100 | 35 | 0.21 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_hut_00001_hut1_charaget01_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

（`c_hut_00001_hut1_charaget01_Normal_Colorless` と同一）

#### シーケンス設定（初回）

```
sequence_element_id: 3
condition_type: InitialSummon
condition_value: 0
action_type: SummonEnemy
action_value: c_hut_00001_hut1_charaget01_Normal_Colorless
summon_position: 1.9
summon_count: 1
enemy_hp_coef: 2.5
sequence_group_id: (なし)
```

#### シーケンス設定（リポップ）

```
sequence_element_id: 10
condition_type: FriendUnitDead
condition_value: 3
action_type: SummonEnemy
action_value: c_hut_00001_hut1_charaget01_Normal_Colorless
summon_position: (なし)
summon_count: 1
enemy_hp_coef: 2.5
sequence_group_id: (なし)
```

#### コマ効果

コマ効果なし（該当ステージにコマ効果設定なし）

---

### event_hut1_charaget02_00002（イベント）

#### このステージでの役割

キャラ獲得イベント第2弾・ステージ2のボス。Yellow色のBoss種別。OutpostHpPercentage:99（拠点HP 99%以下）という即時トリガーで開幕登場するボスとして機能。hp_coef 15。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_hut_00001_hut1_charaget02_Boss_Yellow` | Boss | Defense | Yellow | 10,000 | 100 | 35 | 0.21 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_hut_00001_hut1_charaget02_Boss_Yellow` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 74 |
| Special | 0 | なし | なし | 140 |

#### シーケンス設定

```
sequence_element_id: 1
condition_type: OutpostHpPercentage
condition_value: 99
action_type: SummonEnemy
action_value: c_hut_00001_hut1_charaget02_Boss_Yellow
summon_position: (なし)
summon_count: 1
enemy_hp_coef: 15
sequence_group_id: (なし)
```

OutpostHpPercentage:99で実質ゲーム開始直後に登場。Appearanceアタックで演出あり。hp_coef 15。

#### コマ効果

コマ効果なし（該当ステージにコマ効果設定なし）

---

### event_hut1_charaget02_00004（イベント）

#### このステージでの役割

キャラ獲得イベント第2弾・ステージ4のボス。Yellow色・Boss種別でhp_coef 17。OutpostHpPercentage:99で開幕登場し、自身撃破後のFriendUnitDead:2により別のサポートキャラが繰り出される構成。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_hut_00001_hut1_charaget02_Boss_Yellow` | Boss | Defense | Yellow | 10,000 | 100 | 35 | 0.21 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_hut_00001_hut1_charaget02_Boss_Yellow` | なし | None | なし | なし |

#### 攻撃パターン

（`c_hut_00001_hut1_charaget02_Boss_Yellow` と同一。Appearance / Normal / Special）

#### シーケンス設定

```
sequence_element_id: 1
condition_type: OutpostHpPercentage
condition_value: 99
action_type: SummonEnemy
action_value: c_hut_00001_hut1_charaget02_Boss_Yellow
summon_position: (なし)
summon_count: 1
enemy_hp_coef: 17
sequence_group_id: (なし)
```

hp_coef 17で序盤から高HPボスとして圧力をかける。撃破後はFriendUnitDead:2が発火しサポートキャラが展開。

#### コマ効果

コマ効果なし（該当ステージにコマ効果設定なし）

---

### event_hut1_charaget02_00006（イベント）

#### このステージでの役割

キャラ獲得イベント第2弾・ステージ6のボス。hp_coef 24と高耐久。OutpostHpPercentage:99で開幕登場し、EnterTargetKomaIndex:3という特定コマ到達トリガーで大量の雑魚が展開する複合トリガーのボスステージ。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_hut_00001_hut1_charaget02_Boss_Yellow` | Boss | Defense | Yellow | 10,000 | 100 | 35 | 0.21 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_hut_00001_hut1_charaget02_Boss_Yellow` | なし | None | なし | なし |

#### 攻撃パターン

（`c_hut_00001_hut1_charaget02_Boss_Yellow` と同一）

#### シーケンス設定

```
sequence_element_id: 1
condition_type: OutpostHpPercentage
condition_value: 99
action_type: SummonEnemy
action_value: c_hut_00001_hut1_charaget02_Boss_Yellow
summon_position: (なし)
summon_count: 1
enemy_hp_coef: 24
sequence_group_id: (なし)
```

hp_coef 24。EnterTargetKomaIndex:3との連動でコマ進行が特定段階に達すると雑魚が一斉展開する、プレッシャーが高い構成。

#### コマ効果

コマ効果なし（該当ステージにコマ効果設定なし）

---

### event_hut1_savage_00001（イベント）

#### このステージでの役割

savageシリーズ第1弾のボス。HP 100,000・攻撃力 500・hp_coef 8という高ステータス設定。EnterTargetKomaIndex:4というコマ到達条件で登場し、ゲーム後半の大波として機能する高難易度ボス。`enemy_ability_knockback_block` によりノックバック無効。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_hut_00001_hut1_savage01_Boss_Colorless` | Boss | Defense | Colorless | 100,000 | 500 | 35 | 0.15 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_hut_00001_hut1_savage01_Boss_Colorless` | `enemy_ability_knockback_block` | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | attack_delay | next_attack_interval |
|------------|-----------|--------------|------------------|--------------|-------------|---------------------|
| Appearance | 0 | なし | なし | 50 | 0 | 0 |
| Normal | 0 | なし | なし | 74 | 8 | 50 |
| Special | 0 | なし | なし | 140 | 44 | 0 |

#### シーケンス設定

```
sequence_element_id: 6
condition_type: EnterTargetKomaIndex
condition_value: 4
action_type: SummonEnemy
action_value: c_hut_00001_hut1_savage01_Boss_Colorless
summon_position: (なし)
summon_count: 1
enemy_hp_coef: 8
sequence_group_id: (なし)
```

EnterTargetKomaIndex:4（コマ4到達時）に登場。ノックバック無効で強力な防衛壁として機能。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| SlipDamage | 4 | Player |

---

### event_hut1_savage_00002（イベント）

#### このステージでの役割

savageシリーズ第2弾のボス。HP 100,000・攻撃力 500。EnterTargetKomaIndex:3で登場し、同じく高HP・高攻撃力の他Bossと連携する最高難度クラスのステージボス。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_hut_00001_hut1_savage02_Boss_Red` | Boss | Defense | Red | 100,000 | 500 | 35 | 0.15 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_hut_00001_hut1_savage02_Boss_Red` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | attack_delay | next_attack_interval |
|------------|-----------|--------------|------------------|--------------|-------------|---------------------|
| Appearance | 0 | なし | なし | 50 | 0 | 0 |
| Normal | 0 | なし | なし | 74 | 8 | 50 |
| Special | 0 | なし | なし | 140 | 44 | 0 |

#### シーケンス設定

```
sequence_element_id: 4
condition_type: EnterTargetKomaIndex
condition_value: 3
action_type: SummonEnemy
action_value: c_hut_00001_hut1_savage02_Boss_Red
summon_position: (なし)
summon_count: 1
enemy_hp_coef: 8
sequence_group_id: (なし)
```

EnterTargetKomaIndex:3（コマ3到達時）に登場。他のBoss（hut1_00101・hut1_00201）と同時展開する3体ボス構成の一角。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| Poison | 3 | Player |

---

### event_hut1_savage_00003（イベント）

#### このステージでの役割

savageシリーズ第3弾のボス。HP 100,000・攻撃力 500・`enemy_ability_knockback_block`（ノックバック無効）。EnterTargetKomaIndex:3で登場し、4体のBoss（hut1_00001〜00301）が連携する最大規模の高難易度ステージ。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_hut_00001_hut1_savage03_Boss_Yellow` | Boss | Defense | Yellow | 100,000 | 500 | 35 | 0.15 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_hut_00001_hut1_savage03_Boss_Yellow` | `enemy_ability_knockback_block` | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | attack_delay | next_attack_interval |
|------------|-----------|--------------|------------------|--------------|-------------|---------------------|
| Appearance | 0 | なし | なし | 50 | 0 | 0 |
| Normal | 0 | なし | なし | 74 | 8 | 50 |
| Special | 0 | なし | なし | 140 | 44 | 0 |

#### シーケンス設定

```
sequence_element_id: 3
condition_type: EnterTargetKomaIndex
condition_value: 3
action_type: SummonEnemy
action_value: c_hut_00001_hut1_savage03_Boss_Yellow
summon_position: (なし)
summon_count: 1
enemy_hp_coef: 12
sequence_group_id: (なし)
```

EnterTargetKomaIndex:3でhp_coef 12という高倍率で登場。ノックバック無効かつ4体Boss同時展開の最難関設定。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| Poison | 4 | Player |

---

### raid_hut1_00001（降臨バトル）

#### このステージでの役割

降臨バトルステージにて、ウェーブ式（sequence_group_id: w1〜w6）の複数フェーズにわたって登場する。Normal種別（Colorless）とBoss種別（Colorless）の2つのパラメータが使用され、低HP雑魚（advent_Normal）と高HP中核ボス（advent_Boss）の2役を担う。6ウェーブ構成の長期戦ステージ。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_hut_00001_hut1_advent_Normal_Colorless` | Normal | Defense | Colorless | 1,000 | 100 | 30 | 0.15 | 0 |
| `c_hut_00001_hut1_advent_Boss_Colorless` | Boss | Defense | Colorless | 10,000 | 100 | 30 | 0.15 | 0 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_hut_00001_hut1_advent_Normal_Colorless` | なし | None | なし | なし |
| `c_hut_00001_hut1_advent_Boss_Colorless` | `enemy_ability_knockback_block` | None | なし | なし |

#### 攻撃パターン（Normal種別）

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | attack_delay | next_attack_interval |
|------------|-----------|--------------|------------------|--------------|-------------|---------------------|
| Normal | 0 | なし | なし | 74 | 8 | 50 |
| Special | 0 | なし | なし | 140 | 44 | 0 |

#### 攻撃パターン（Boss種別）

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | attack_delay | next_attack_interval |
|------------|-----------|--------------|------------------|--------------|-------------|---------------------|
| Appearance | 0 | なし | なし | 50 | 0 | 0 |
| Normal | 0 | なし | なし | 74 | 8 | 50 |
| Special | 0 | なし | なし | 140 | 44 | 0 |

#### シーケンス設定（Normal種別 - w1ウェーブ）

```
sequence_element_id: 5
condition_type: ElapsedTimeSinceSequenceGroupActivated
condition_value: 0
action_type: SummonEnemy
action_value: c_hut_00001_hut1_advent_Normal_Colorless
summon_position: (なし)
summon_count: 1
enemy_hp_coef: 30
sequence_group_id: w1
```

#### シーケンス設定（Boss種別 - w5ウェーブ）

```
sequence_element_id: 26
condition_type: ElapsedTimeSinceSequenceGroupActivated
condition_value: 0
action_type: SummonEnemy
action_value: c_hut_00001_hut1_advent_Boss_Colorless
summon_position: (なし)
summon_count: 1
enemy_hp_coef: 100
sequence_group_id: w5
```

ウェーブ式6段階構成（w1〜w6）で、FriendUnitDeadによるウェーブ移行が実装されている。Normal種別はw1・w2で複数回登場し雑魚ラッシュを演出、Boss種別はw5・w6の終盤フェーズでhp_coef 100〜120という極めて高い耐久ボスとして登場。ノックバック無効アビリティにより、ボスフェーズでは押し返しが不可能な最終試練となっている。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| SlipDamage | 2 | Player |
| Burn | 1 | Player |
| Poison | 1 | Player |
