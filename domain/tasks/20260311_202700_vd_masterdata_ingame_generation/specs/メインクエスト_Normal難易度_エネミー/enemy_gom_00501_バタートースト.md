# バタートースト（enemy_gom_00501）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: enemy_gom_00501
> mst_series_id: gom
> 作品名: "姫様"拷問"の時間です"

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `enemy_gom_00501` |
| mst_series_id | `gom` |
| 作品名 | "姫様"拷問"の時間です" |
| asset_key | `enemy_gom_00501` |
| is_phantomized | `0` |

---

## 2. キャラクター特徴まとめ

バタートーストはメインクエスト Normal難易度の2ステージ（normal_gom_00001・normal_gom_00006）に登場する雑魚敵です。いずれも character_unit_kind=Normal・role_type=Defense の防御型ユニットとして配置されており、HP 1,000・攻撃力 50 と控えめなステータスで、典型的な序盤〜中盤の消耗役を担います。

Colorless と Yellow の2バリエーションが存在し、ステージの作品カラーに合わせて使い分けられています。変身設定・アビリティは一切なく、シンプルな壁役として機能します。攻撃は近接単体直撃（Direct / Distance 0〜0.15 / max_target_count=1）で、スペシャル技や範囲攻撃は持ちません。

召喚パターンは ElapsedTime を基本とし、まとまった数（4〜10体）を一定間隔で繰り返し投入するウェーブ型が主流です。normal_gom_00006 ではグループトリガー（FriendUnitDead でグループ切替）を活用した複合シーケンスの一部に組み込まれており、ボス戦フェーズでの補助雑魚として機能します。コマ効果は normal_gom_00006 に SlipDamage（Player サイド）が1コマ存在し、持続ダメージによる圧力を加える設計となっています。

---

## 3. ステージ別使用実態

### normal_gom_00001（メインクエスト Normal）

#### このステージでの役割

バタートースト（Colorless）が主要雑魚として大量投入されるシンプルなウェーブステージです。同じくColorlessのもう1体（e_gom_00502）と交互に投入される形で、防御型ユニットを盾にしながら敵陣が前進してくる設計となっています。コマ効果はなく、純粋な数の圧力で攻略側の処理能力を問うステージです。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_gom_00501_general_n_Normal_Colorless` | Normal | Defense | Colorless | 1,000 | 50 | 34 | 0.14 | （なし） |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_gom_00501_general_n_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 50 |

**MstAttackElement（e_gom_00501_general_n_Normal_Colorless_Normal_00000）**

| attack_type | range_start | range_end | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-------------|------------|-----------|-----------------|--------|------------|----------|----------------|-------------|
| Direct | Distance 0 | Distance 0.15 | 1 | Foe / All | Damage | Normal | Percentage 100.0 | None |

#### シーケンス設定

```
[sequence_element_id: 1]
condition_type: ElapsedTime
condition_value: 800
action_type: SummonEnemy
action_value: e_gom_00501_general_n_Normal_Colorless
summon_position: （空）
summon_count: 5
summon_interval: 25
enemy_hp_coef: 9
sequence_group_id: （なし）
```

ゲーム開始800フレーム経過時点で5体を連続召喚（間隔25フレーム）するウェーブ。enemy_hp_coef=9 と高い係数が設定されており、実効HP は基礎値の9倍にスケールされます。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| なし（全コマ None） | 0 | - |

---

### normal_gom_00006（メインクエスト Normal）

#### このステージでの役割

Yellow カラーのバタートーストが、フレンドユニット生存をトリガーとした複合シーケンスの中に組み込まれた大規模ステージです。通常フェーズで4体、グループ切替後のボスフェーズで10体と、フェーズが進むにつれて投入数が増加します。コマに SlipDamage（Player サイド）が設定されており、持続ダメージによる追加圧力も加わります。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_gom_00501_general_n_Normal_Yellow` | Normal | Defense | Yellow | 1,000 | 50 | 34 | 0.14 | （なし） |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_gom_00501_general_n_Normal_Yellow` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 50 |

**MstAttackElement（e_gom_00501_general_n_Normal_Yellow_Normal_00000）**

| attack_type | range_start | range_end | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-------------|------------|-----------|-----------------|--------|------------|----------|----------------|-------------|
| Direct | Distance 0 | Distance 0.15 | 1 | Foe / All | Damage | Normal | Percentage 100.0 | None |

#### シーケンス設定

**通常フェーズ（ElapsedTime）**

```
[sequence_element_id: 5]
condition_type: ElapsedTime
condition_value: 50
action_type: SummonEnemy
action_value: e_gom_00501_general_n_Normal_Yellow
summon_position: （空）
summon_count: 4
summon_interval: 300
enemy_hp_coef: 9
sequence_group_id: （なし）
```

ゲーム開始直後50フレームで4体を投入する先行ウェーブ。他の雑魚・フレンドユニット・ボスと並列してシーケンスが組まれており、序盤から大量の敵が押し寄せる設計です。

**グループ切替後フェーズ（FriendUnitDead → group1）**

```
[sequence_element_id: 17]
condition_type: ElapsedTimeSinceSequenceGroupActivated
condition_value: 600
action_type: SummonEnemy
action_value: e_gom_00501_general_n_Normal_Yellow
summon_position: （空）
summon_count: 10
summon_interval: 300
enemy_hp_coef: 9
sequence_group_id: group1
```

フレンドユニット2体目が撃破されて group1 が起動してから600フレーム経過後に10体を一斉投入。ボス戦フェーズの佳境で大量の防御型雑魚がボス陣営を補強する役割を担います。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| SlipDamage | 1 | Player |

> SlipDamage は koma1 に設定されており、プレイヤーサイドに持続ダメージを与えるコマ効果です。

---
