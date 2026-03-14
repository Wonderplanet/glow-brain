# 奥村 正宗（chara_yuw_00601）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: chara_yuw_00601
> mst_series_id: yuw
> 作品名: 2.5次元の誘惑

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `chara_yuw_00601` |
| mst_series_id | `yuw` |
| 作品名 | 2.5次元の誘惑 |
| asset_key | `chara_yuw_00601` |
| is_phantomized | `1` |

---

## 2. キャラクター特徴まとめ

**対象データ**: normalクエストのNormal難易度（`normal_` で始まるインゲームID）のみ

メインクエストのNormal難易度ステージ（`normal_` IDのステージ）への使用実績は**0件**です。奥村 正宗は「2.5次元の誘惑」シリーズのイベントおよび降臨バトル専用キャラクターとして使用されており、メインクエストへの登場実績はありません。

参考として、`character_unit_kind = Normal` のパラメータが使われているステージ（フィルタ条件に最も近いデータ）を以下に記載します。これらはすべてイベント・降臨バトルステージです。

- **role_type**: 全バリエーションで `Defense` 固定
- **HP**: 10,000 〜 50,000（Normalユニットでは 10,000 または 50,000）
- **攻撃力**: 100 〜 300
- **変身設定**: 全パラメータで変身なし（`transformationConditionType = None`）
- **アビリティ**: 降臨バトル版のNormalユニット（`c_yuw_00601raid_00001_Normal_Colorless`）はアビリティなし
- **コマ効果**: `event_yuw1_charaget02_00008` で `SlipDamage`（Player側）が使用されている。`event_yuw1_1day_00001` と `raid_yuw1_00001` はコマ効果なし（全 None）

---

## 3. ステージ別使用実態

> **注意**: メインクエストNormal難易度（`normal_` IDのステージ）への使用実績はありません。
> 以下は `character_unit_kind = Normal` のパラメータが使用されているステージの参考情報です。

---

### event_yuw1_1day_00001（イベント）

#### このステージでの役割

1日1回制のデイリーイベントステージ。奥村 正宗が Colorless の Normal ユニットとして登場し、HP 10,000 / 攻撃力 300 の中程度の強さで出現する。シーケンス開始 100フレーム後に最初の召喚が行われる序盤型の配置構成。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_yuw_00601_1d1c_Normal_Colorless` | Normal | Defense | Colorless | 10,000 | 300 | 25 | 0.19 | 3 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_yuw_00601_1d1c_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 60 |
| Special | 0 | なし | なし | 92 |

**MstAttackElement 詳細**

| mst_attack_id | attack_type | target | damage_type | effect_type | effect_duration | effect_parameter |
|--------------|------------|--------|------------|------------|----------------|----------------|
| `c_yuw_00601_1d1c_Normal_Colorless_Normal_00000` | Direct | Foe / All | Damage | None | 0 | 0 |
| `c_yuw_00601_1d1c_Normal_Colorless_Special_00001` | Direct | Self / All | Damage | DamageCut | 1,000フレーム | 30 |

#### シーケンス設定

```
condition_type: ElapsedTime
condition_value: 100
sequence_element_id: 1
action_type: SummonEnemy
action_value: c_yuw_00601_1d1c_Normal_Colorless
summon_position: （指定なし）
summon_count: 1
enemy_hp_coef: 2
sequence_group_id: （なし）
```

開幕 100フレーム経過後に 1体召喚。`enemy_hp_coef = 2` により実質 HP は基本値の2倍（20,000相当）となる。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| なし（全 None） | 0 | - |

---

### event_yuw1_charaget02_00008（イベント）

#### このステージでの役割

キャラクター獲得系イベントの第2弾ステージ8番目。奥村 正宗が Blue の Normal ユニットとして登場し、HP 50,000 / 攻撃力 300 の高耐久ユニットとして配置される。`FriendUnitDead` トリガーによる再召喚パターンを持ち、味方ユニットが倒れるたびに復活する粘り強い設計。`SlipDamage` コマ効果が2コマで設定されており、プレイヤーにじわじわとダメージを与えるギミック要素も持つ。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_yuw_00601_okumuraget_Normal_Blue` | Normal | Defense | Blue | 50,000 | 300 | 25 | 0.19 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_yuw_00601_okumuraget_Normal_Blue` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 60 |
| Special | 0 | なし | なし | 92 |

**MstAttackElement 詳細**

| mst_attack_id | attack_type | target | damage_type | effect_type | effect_duration | effect_parameter |
|--------------|------------|--------|------------|------------|----------------|----------------|
| `c_yuw_00601_okumuraget_Normal_Blue_Normal_00000` | Direct | Foe / All | Damage | None | 0 | 0 |
| `c_yuw_00601_okumuraget_Normal_Blue_Special_00001` | Direct | Self / All | Damage | DamageCut | 1,000フレーム | 30 |

#### シーケンス設定

```
【sequence_element_id: 1】
condition_type: ElapsedTime
condition_value: 0
action_type: SummonEnemy
action_value: c_yuw_00601_okumuraget_Normal_Blue
summon_position: （指定なし）
summon_count: 1
enemy_hp_coef: 2
sequence_group_id: （なし）

【sequence_element_id: 2】
condition_type: FriendUnitDead
condition_value: 1
action_type: SummonEnemy
action_value: c_yuw_00601_okumuraget_Normal_Blue
summon_position: （指定なし）
summon_count: 1
enemy_hp_coef: 3
sequence_group_id: （なし）
```

開幕 0フレームで即召喚（`enemy_hp_coef = 2` で HP 100,000相当）、さらに味方1体撃破ごとに再召喚（`enemy_hp_coef = 3` で HP 150,000相当）。ノーマル枠ながら高い耐久力を持つ粘り型配置。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| SlipDamage | 3 | Player |

---

### raid_yuw1_00001（降臨バトル）

#### このステージでの役割

「2.5次元の誘惑」シリーズの降臨バトルステージ。多段ウェーブ構成（w1〜w7）の中で奥村 正宗は Colorless の Normal ユニットとして複数回登場する。序盤の単体配置（seq_id: 1）のほか、ウェーブ1（w1: seq_id 13）・ウェーブ4（w4: seq_id 28）・ウェーブ1への再移行（seq_id 16）と異なるフェーズで繰り返し登場する。`summon_position` が明示されているケースでは前線寄り（2.2〜2.5）に配置される。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_yuw_00601raid_00001_Normal_Colorless` | Normal | Defense | Colorless | 10,000 | 100 | 25 | 0.19 | 3 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_yuw_00601raid_00001_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 60 |
| Special | 0 | なし | なし | 92 |

**MstAttackElement 詳細**

| mst_attack_id | attack_type | target | damage_type | effect_type | effect_duration | effect_parameter |
|--------------|------------|--------|------------|------------|----------------|----------------|
| `c_yuw_00601raid_00001_Normal_Colorless_Normal_00000` | Direct | Foe / All | Damage | None | 0 | 0 |
| `c_yuw_00601raid_00001_Normal_Colorless_Special_00001` | Direct | Self / All | Damage | DamageCut | 500フレーム | 10 |

#### シーケンス設定（対象キャラ登場行のみ）

```
【seq_id: 1 / 序盤・グループなし】
condition_type: ElapsedTime
condition_value: 50
action_type: SummonEnemy
action_value: c_yuw_00601raid_00001_Normal_Colorless
summon_position: 2.5
summon_count: 1
enemy_hp_coef: 1
sequence_group_id: （なし）

【seq_id: 13 / ウェーブ1（w1）】
condition_type: ElapsedTimeSinceSequenceGroupActivated
condition_value: 300
action_type: SummonEnemy
action_value: c_yuw_00601raid_00001_Normal_Colorless
summon_position: 2.2
summon_count: 1
enemy_hp_coef: 1
sequence_group_id: w1

【seq_id: 16 / ウェーブ1（w1）・FriendUnitDead】
condition_type: FriendUnitDead
condition_value: 15
action_type: SummonEnemy
action_value: c_yuw_00601raid_00001_Normal_Colorless
summon_position: （指定なし）
summon_count: 1
enemy_hp_coef: 1
sequence_group_id: w1

【seq_id: 28 / ウェーブ4（w4）】
condition_type: ElapsedTimeSinceSequenceGroupActivated
condition_value: 50
action_type: SummonEnemy
action_value: c_yuw_00601raid_00001_Normal_Colorless
summon_position: （指定なし）
summon_count: 1
enemy_hp_coef: 30
sequence_group_id: w4
```

ウェーブ4（w4）での `enemy_hp_coef = 30` により終盤では HP が基本値の30倍（300,000相当）になり、同一パラメータでも終盤では強敵として機能する設計。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| なし（全 None） | 0 | - |

---
