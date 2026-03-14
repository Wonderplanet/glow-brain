# 花奏 すず（chara_aya_00101）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: chara_aya_00101
> mst_series_id: aya
> 作品名: あやかしトライアングル

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `chara_aya_00101` |
| mst_series_id | `aya` |
| 作品名 | あやかしトライアングル |
| asset_key | `chara_aya_00101` |
| is_phantomized | `1` |

---

## 2. キャラクター特徴まとめ

花奏 すずはイベントステージのみで使用実績がある「cキャラ（フレンドユニット）」です。normalクエストのNormal難易度での使用実績はありません。

Normal kindパラメータは3種類存在し、HPは1,000〜10,000と幅があります（イベント序盤ステージでは1,000、後半ステージでは10,000）。攻撃力はすべて100で統一されており、移動速度35・索敵距離0.3・ノックバック数1という控えめなスペックです。role_typeはすべてDefenseで、前線での壁役として設計されています。変身設定・アビリティは一切なく、シンプルな雑魚敵として機能します。

コマ効果はほぼ使用されておらず（Noneが大半）、唯一 `event_aya1_challenge_00002` で Poison（Player側）が1コマ配置されるのみです。

シーケンスでは ElapsedTime（経過時間）条件での召喚が主体で、単体召喚（summon_count=1）のみ使われています。後半ステージ（event_aya1_charaget02_00006）では EnterTargetKomaIndex（コマ到達）条件で召喚される構成になっています。

---

## 3. ステージ別使用実態

### event_aya1_challenge_00002（イベント）

#### このステージでの役割

チャレンジ系イベントの序盤ステージ（00002）で、経過時間350フレームを条件に1体が召喚される。HP1,000の軽量な壁役として、プレイヤーの初期押し込みを担う役割。同ステージにはBoss kindの同キャラも登場するが（FriendUnitDead条件）、Normalはあくまで序盤の消耗役として機能する。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_aya_00101_aya1_challenge01_Normal_Green` | Normal | Defense | Green | 1,000 | 100 | 35 | 0.3 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_aya_00101_aya1_challenge01_Normal_Green` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 83 |
| Special | 0 | なし | なし | 155 |

MstAttackElement:
- Normal攻撃: Direct / Distance(0)〜Distance(0.31) / 対象:Foe, All / max_target_count:1 / Damage / Normal / Percentage 100.0 / effect_type:None
- Special攻撃: Direct / Distance(0)〜Distance(0.33) / 対象:Foe, All / max_target_count:1 / Damage / Normal / Percentage 100.0 / effect_type:None

#### シーケンス設定

```
condition_type: ElapsedTime
condition_value: 350
sequence_element_id: 1
action_type: SummonEnemy
action_value: c_aya_00101_aya1_challenge01_Normal_Green
summon_position: （未設定）
summon_count: 1
enemy_hp_coef: 50
sequence_group_id: （未設定）
```

経過時間350フレームで1体召喚。enemy_hp_coef=50のため実効HPは500（1,000 × 0.5）。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| Poison | 1 | Player |

---

### event_aya1_charaget01_00003（イベント）

#### このステージでの役割

キャラゲット系イベント（charaget01）の第3ステージで、ElapsedTime=500フレームを条件に1体召喚される。他のエネミー（OutpostHpPercentage・FriendUnitDead条件）が先行して登場しており、花奏 すずはステージ中盤以降の追加戦力として配置されている。HP1,000でenemy_hp_coef=30なので実効HPは300と非常に低く、早期撃破される想定の雑魚役。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_aya_00101_aya1_charaget01_Normal_Green` | Normal | Defense | Green | 1,000 | 100 | 35 | 0.3 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_aya_00101_aya1_charaget01_Normal_Green` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 83 |
| Special | 0 | なし | なし | 155 |

MstAttackElement:
- Normal攻撃: Direct / Distance(0)〜Distance(0.31) / 対象:Foe, All / max_target_count:1 / Damage / Normal / Percentage 100.0 / effect_type:None
- Special攻撃: Direct / Distance(0)〜Distance(0.33) / 対象:Foe, All / max_target_count:1 / Damage / Normal / Percentage 100.0 / effect_type:None

#### シーケンス設定

```
condition_type: ElapsedTime
condition_value: 500
sequence_element_id: 4
action_type: SummonEnemy
action_value: c_aya_00101_aya1_charaget01_Normal_Green
summon_position: （未設定）
summon_count: 1
enemy_hp_coef: 30
sequence_group_id: （未設定）
```

経過時間500フレームで1体召喚。enemy_hp_coef=30のため実効HPは300（1,000 × 0.3）と軽量。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| なし（全コマNone） | - | - |

---

### event_aya1_charaget01_00004（イベント）

#### このステージでの役割

キャラゲット系イベント（charaget01）の第4ステージで、ElapsedTime=600フレームを条件に1体召喚される。第3ステージと同じパラメータを使用しており、00003より若干遅い召喚タイミングで登場。enemy_hp_coef=50で実効HP500と00003より耐久性が増しており、難易度が一段上がっている。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_aya_00101_aya1_charaget01_Normal_Green` | Normal | Defense | Green | 1,000 | 100 | 35 | 0.3 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_aya_00101_aya1_charaget01_Normal_Green` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 83 |
| Special | 0 | なし | なし | 155 |

MstAttackElement:
- Normal攻撃: Direct / Distance(0)〜Distance(0.31) / 対象:Foe, All / max_target_count:1 / Damage / Normal / Percentage 100.0 / effect_type:None
- Special攻撃: Direct / Distance(0)〜Distance(0.33) / 対象:Foe, All / max_target_count:1 / Damage / Normal / Percentage 100.0 / effect_type:None

#### シーケンス設定

```
condition_type: ElapsedTime
condition_value: 600
sequence_element_id: 4
action_type: SummonEnemy
action_value: c_aya_00101_aya1_charaget01_Normal_Green
summon_position: （未設定）
summon_count: 1
enemy_hp_coef: 50
sequence_group_id: （未設定）
```

経過時間600フレームで1体召喚。enemy_hp_coef=50のため実効HPは500（1,000 × 0.5）。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| なし（全コマNone） | - | - |

---

### event_aya1_charaget02_00006（イベント）

#### このステージでの役割

キャラゲット系イベント（charaget02）の第6ステージで、EnterTargetKomaIndex=3（コマインデックス3到達）を条件に1体召喚される。本ステージでは複数のcキャラが同じEnterTargetKomaIndex=3条件で同時に召喚されるグループ配置設計になっており、花奏 すずはその一体として登場。HP10,000・enemy_hp_coef=15の実効HP1,500で、他ステージより高耐久の設定。summon_position=2.7でやや後方気味の召喚位置。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_aya_00101_aya1_charaget02_Normal_Green` | Normal | Defense | Green | 10,000 | 100 | 35 | 0.3 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_aya_00101_aya1_charaget02_Normal_Green` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 83 |
| Special | 0 | なし | なし | 155 |

MstAttackElement:
- Normal攻撃: Direct / Distance(0)〜Distance(0.31) / 対象:Foe, All / max_target_count:1 / Damage / Normal / Percentage 100.0 / effect_type:None
- Special攻撃: Direct / Distance(0)〜Distance(0.33) / 対象:Foe, All / max_target_count:1 / Damage / Normal / Percentage 100.0 / effect_type:None

#### シーケンス設定

```
condition_type: EnterTargetKomaIndex
condition_value: 3
sequence_element_id: 3
action_type: SummonEnemy
action_value: c_aya_00101_aya1_charaget02_Normal_Green
summon_position: 2.7
summon_count: 1
enemy_hp_coef: 15
sequence_group_id: （未設定）
```

コマインデックス3到達で召喚。summon_position=2.7でやや後方。enemy_hp_coef=15のため実効HPは1,500（10,000 × 0.15）。同条件で他キャラ（c_aya_00001、c_aya_00201、c_aya_00301）も同時召喚されるグループ戦。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| なし（全コマNone） | - | - |

---
