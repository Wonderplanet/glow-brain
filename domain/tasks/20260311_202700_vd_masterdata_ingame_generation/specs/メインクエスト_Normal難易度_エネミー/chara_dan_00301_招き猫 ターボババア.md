# 招き猫 ターボババア（chara_dan_00301）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: chara_dan_00301
> mst_series_id: dan
> 作品名: ダンダダン
> ※ 対象コンテンツ: event_dan1_* イベントのみ（メインクエストNormal出場なし）

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `chara_dan_00301` |
| mst_series_id | `dan` |
| 作品名 | ダンダダン |
| asset_key | `chara_dan_00301` |
| is_phantomized | `1` |

---

## 2. キャラクター特徴まとめ

招き猫 ターボババアは `chara_` プレフィックスかつ `is_phantomized=1` のキャラクターであり、フレンドユニット（味方）として実装された敵ユニット。メインクエストNormalへの出場実績はなく、ダンダダン作品の `event_dan1_*` イベント限定で使用される。

パラメータは3種類あり、全て HP 50,000・攻撃力 300・移動速度 30 と高スペックで統一されている。違いはロールタイプと攻撃パターンにあり、**Attack型（bbaget_atk）**・**Technical型（bbaget_tec）**・**Defense型（dan1challenge）**の3つが存在する。変身設定は全パラメータなし。

攻撃パターンは全種共通でNormal攻撃（3段：25%+25%+50%）とSpecial攻撃（200%）を持ち、bbaget系は登場時にForcedKnockBack5（広範囲強制ノックバック）を発動する。Technical型のみSpecial攻撃にFreeze判定が付き、Defense型（dan1challenge）はSpecial攻撃後にDamageCutの自己バフを発動する点が特徴的。

登場条件は InitialSummon・ElapsedTime・EnterTargetKomaIndex・FriendUnitDead と多様で、キャラゲットイベント（charaget01）では繰り返し登場設計が多く、チャレンジイベント（challenge01）では友軍撃破後に強化版が再登場する段階的な構成となっている。

コマ効果は event_dan1_challenge01_00003 のみ AttackPowerDown（Player対象）を使用。

---

## 3. ステージ別使用実態

### event_dan1_challenge01_00003（イベント）

#### このステージでの役割

ステージ開始直後（InitialSummon）に Normal/Defense/Yellow パラメータで登場するイベントチャレンジの主役。友軍撃破2回目に hp_coef 1.8 の強化版が再登場し、難易度を段階的に引き上げる設計。中間には `c_dan_00002`（別キャラ）がボス級で挟み込まれる複合構成。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_dan_00301_dan1challenge_Normal_Yellow` | Normal | Defense | Yellow | 50,000 | 300 | 30 | 0.21 | 3 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_dan_00301_dan1challenge_Normal_Yellow` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | attack_delay | next_attack_interval |
|------------|-----------|--------------|------------------|--------------|-------------|---------------------|
| Normal | 0 | なし | なし | 80 | 0 | 80 |
| Special | 0 | なし | なし | 92 | 15 | 0 |

**攻撃要素（MstAttackElement）**

Normal攻撃（3段）:

| sort_order | attack_type | range_end | max_target | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|-----------|-----------|--------|-------------|----------|----------------|-------------|
| 1 | Direct | Distance 0.22 | 1 | Foe/All/All/All | Damage | Normal | Percentage 25% | None |
| 2 | Direct | Distance 0.22 | 1 | Foe/All/All/All | Damage | Normal | Percentage 25% | None |
| 3 | Direct | Distance 0.22 | 1 | Foe/All/All/All | Damage | Normal | Percentage 50% | None |

Special攻撃（2段）:

| sort_order | attack_type | range_end | max_target | target | damage_type | hit_type | power_parameter | effect_type | effective_count | effective_duration |
|-----------|------------|-----------|-----------|--------|-------------|----------|----------------|-------------|---------------|-----------------|
| 1 | Direct | Distance 0.22 | 1 | Foe/All/All/All | Damage | Normal | Percentage 200% | None | 0 | 0 |
| 2 | Direct | Distance 0.22 | 1 | Self/All/All/All | None | Normal | Percentage 0% | DamageCut | -1 | 1,000ms |

> Special攻撃後に自己DamageCutバフ（1000ms持続）を発動。

#### シーケンス設定

```
[elem 1] InitialSummon（ステージ開始時）
  condition_type: InitialSummon
  condition_value: 0
  action_type: SummonEnemy
  action_value: c_dan_00301_dan1challenge_Normal_Yellow
  summon_position: 1.5
  summon_count: 1
  summon_interval: 0
  enemy_hp_coef: 1.2
  sequence_group_id: （なし）

[elem 3] FriendUnitDead（友軍2体撃破後）
  condition_type: FriendUnitDead
  condition_value: 2
  action_type: SummonEnemy
  action_value: c_dan_00301_dan1challenge_Normal_Yellow
  summon_position: （なし）
  summon_count: 1
  summon_interval: 0
  enemy_hp_coef: 1.8
  sequence_group_id: （なし）
```

ステージ開始時に単体登場（hp_coef 1.2）、友軍2体撃破後に hp_coef 1.8 の強化版が再召喚される段階的な難易度設計。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド |
|-----------|---------|----------|
| AttackPowerDown | 2 | Player |

---

### event_dan1_charaget01_00005（イベント）

#### このステージでの役割

キャラゲットイベントの1ステージとして Attack/Boss/Blue パラメータで登場。300ms後に単体召喚されたのち、友軍撃破1回目に hp_coef 1.5 のより強化された版が再登場する2フェーズ構成。登場時のForcedKnockBack5が特徴。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_dan_00301_bbaget_atk_Boss_Blue` | Boss | Attack | Blue | 50,000 | 300 | 30 | 0.25 | 3 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_dan_00301_bbaget_atk_Boss_Blue` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | attack_delay | next_attack_interval |
|------------|-----------|--------------|------------------|--------------|-------------|---------------------|
| Appearance | 0 | なし | なし | 50 | 0 | 0 |
| Normal | 0 | なし | なし | 80 | 0 | 75 |
| Special | 0 | なし | なし | 92 | 15 | 0 |

**攻撃要素（MstAttackElement）**

Appearance（登場演出時・広範囲強制ノックバック）:

| sort_order | attack_type | range_end | max_target | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|-----------|-----------|--------|-------------|----------|----------------|-------------|
| 1 | Direct | Distance 50.0 | 100 | Foe/All/All/All | None | ForcedKnockBack5 | Percentage 100% | None |

Normal攻撃（3段）:

| sort_order | attack_type | range_end | max_target | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|-----------|-----------|--------|-------------|----------|----------------|-------------|
| 1 | Direct | Distance 0.26 | 1 | Foe/All/All/All | Damage | Normal | Percentage 25% | None |
| 2 | Direct | Distance 0.26 | 1 | Foe/All/All/All | Damage | Normal | Percentage 25% | None |
| 3 | Direct | Distance 0.26 | 1 | Foe/All/All/All | Damage | Normal | Percentage 50% | None |

Special攻撃:

| sort_order | attack_type | range_end | max_target | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|-----------|-----------|--------|-------------|----------|----------------|-------------|
| 1 | Direct | Distance 0.26 | 1 | Foe/All/All/All | Damage | Normal | Percentage 200% | None |

#### シーケンス設定

```
[elem 1] ElapsedTime（300ms経過）
  condition_type: ElapsedTime
  condition_value: 300
  action_type: SummonEnemy
  action_value: c_dan_00301_bbaget_atk_Boss_Blue
  summon_position: （なし）
  summon_count: 1
  summon_interval: 0
  enemy_hp_coef: 0.8
  sequence_group_id: （なし）

[elem 2] FriendUnitDead（友軍1体撃破後）
  condition_type: FriendUnitDead
  condition_value: 1
  action_type: SummonEnemy
  action_value: c_dan_00301_bbaget_atk_Boss_Blue
  summon_position: （なし）
  summon_count: 1
  summon_interval: 0
  enemy_hp_coef: 1.5
  sequence_group_id: （なし）
```

300ms後に弱体版（hp_coef 0.8）で登場し、倒すと hp_coef 1.5 の強化版が再召喚される2段階構成。

#### コマ効果

コマ効果なし（全ライン None）。

---

### event_dan1_charaget01_00006（イベント）

#### このステージでの役割

`EnterTargetKomaIndex` という特殊なトリガー（コマインデックスへの突入）で Technical/Boss/Blue パラメータが召喚される、コマシステムと連動した設計のステージ。高hp_coef（2.5）の強敵として登場し、Freeze判定付きSpecial攻撃が特徴。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_dan_00301_bbaget_tec_Boss_Blue` | Boss | Technical | Blue | 50,000 | 300 | 30 | 0.25 | 3 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_dan_00301_bbaget_tec_Boss_Blue` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | attack_delay | next_attack_interval |
|------------|-----------|--------------|------------------|--------------|-------------|---------------------|
| Appearance | 0 | なし | なし | 50 | 0 | 0 |
| Normal | 0 | なし | なし | 80 | 0 | 90 |
| Special | 0 | なし | なし | 92 | 15 | 0 |

**攻撃要素（MstAttackElement）**

Appearance（登場演出時・広範囲強制ノックバック）:

| sort_order | attack_type | range_end | max_target | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|-----------|-----------|--------|-------------|----------|----------------|-------------|
| 1 | Direct | Distance 50.0 | 100 | Foe/All/All/All | None | ForcedKnockBack5 | Percentage 100% | None |

Normal攻撃（3段）:

| sort_order | attack_type | range_end | max_target | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|-----------|-----------|--------|-------------|----------|----------------|-------------|
| 1 | Direct | Distance 0.26 | 1 | Foe/All/All/All | Damage | Normal | Percentage 25% | None |
| 2 | Direct | Distance 0.26 | 1 | Foe/All/All/All | Damage | Normal | Percentage 25% | None |
| 3 | Direct | Distance 0.26 | 1 | Foe/All/All/All | Damage | Normal | Percentage 50% | None |

Special攻撃（**Freeze判定**）:

| sort_order | attack_type | range_end | max_target | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|-----------|-----------|--------|-------------|----------|----------------|-------------|
| 1 | Direct | Distance 0.26 | 1 | Foe/All/All/All | Damage | **Freeze** | Percentage 200% | None |

> bbaget_atk と異なり、Special攻撃の hit_type が `Freeze` となっており、命中時に凍結効果が発生する。

#### シーケンス設定

```
[elem 1] EnterTargetKomaIndex（コマインデックス0に突入）
  condition_type: EnterTargetKomaIndex
  condition_value: 0
  action_type: SummonEnemy
  action_value: c_dan_00301_bbaget_tec_Boss_Blue
  summon_position: （なし）
  summon_count: 1
  summon_interval: 0
  enemy_hp_coef: 2.5
  sequence_group_id: （なし）
```

プレイヤーがコマインデックス0を踏んだ瞬間に召喚されるコマ連動型トリガー。hp_coef 2.5 は全登場パターン中最も高い強化倍率。

#### コマ効果

コマ効果なし（全ライン None）。

---

### event_dan1_charaget01_00007（イベント）

#### このステージでの役割

キャラゲットイベントの上位ステージ。前半は他キャラ（`c_dan_00001`・`c_dan_00101`）が主役で、友軍2体撃破後に Technical/Boss/Blue パラメータが高hp_coef（3.0）で登場する後半戦型の構成。本キャラが登場するまでの前フェーズが設計上の特徴。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_dan_00301_bbaget_tec_Boss_Blue` | Boss | Technical | Blue | 50,000 | 300 | 30 | 0.25 | 3 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_dan_00301_bbaget_tec_Boss_Blue` | なし | None | なし | なし |

#### 攻撃パターン

event_dan1_charaget01_00006 と同一パラメータ・同一攻撃設定。

#### シーケンス設定

```
[elem 5] FriendUnitDead（友軍2体撃破後）
  condition_type: FriendUnitDead
  condition_value: 2
  action_type: SummonEnemy
  action_value: c_dan_00301_bbaget_tec_Boss_Blue
  summon_position: （なし）
  summon_count: 1
  summon_interval: 0
  enemy_hp_coef: 3.0
  sequence_group_id: （なし）
```

hp_coef 3.0 は全登場パターン中で最大級の強化倍率。友軍2体を倒して初めて登場する「ラストボス的な」位置づけ。

#### コマ効果

コマ効果なし（全ライン None）。

---

### event_dan1_charaget01_00008（イベント）

#### このステージでの役割

キャラゲットイベントの別ステージ。200ms後に Technical/Boss/Blue パラメータを単体召喚し、友軍撃破1回目以降は `c_dan_00002`（別キャラ）が引き継ぐ1回限り型の登場設計。ステージ序盤の圧力として機能する。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_dan_00301_bbaget_tec_Boss_Blue` | Boss | Technical | Blue | 50,000 | 300 | 30 | 0.25 | 3 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_dan_00301_bbaget_tec_Boss_Blue` | なし | None | なし | なし |

#### 攻撃パターン

event_dan1_charaget01_00006 と同一パラメータ・同一攻撃設定。

#### シーケンス設定

```
[elem 1] ElapsedTime（200ms経過）
  condition_type: ElapsedTime
  condition_value: 200
  action_type: SummonEnemy
  action_value: c_dan_00301_bbaget_tec_Boss_Blue
  summon_position: （なし）
  summon_count: 1
  summon_interval: 0
  enemy_hp_coef: 2.0
  sequence_group_id: （なし）
```

200ms後に hp_coef 2.0 で単体登場。友軍撃破後は別キャラ（`c_dan_00002_bbaget_Boss_Blue`）が続くため、本キャラは序盤フェーズの担当。

#### コマ効果

コマ効果なし（全ライン None）。
