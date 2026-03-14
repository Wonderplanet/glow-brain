# コスプレに託す乙女心 橘 美花莉（chara_yuw_00101）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: chara_yuw_00101
> mst_series_id: yuw
> 作品名: 2.5次元の誘惑

> **フィルタ条件**: normalクエストのNormal難易度のみ（challenge・raidステージを除外）

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `chara_yuw_00101` |
| mst_series_id | `yuw` |
| 作品名 | 2.5次元の誘惑 |
| asset_key | `chara_yuw_00101` |
| is_phantomized | `1` |

---

## 2. キャラクター特徴まとめ

コスプレに託す乙女心 橘 美花莉は、2.5次元の誘惑イベント（yuw1）のキャラゲットクエストに登場するキャラクター。フィルタ対象（normalクエストNormal難易度）ではキャラゲットクエスト01の第3ステージ（通常敵）とキャラゲットクエスト02の第6ステージ（ボス敵）の2ステージに使用されている。

ステータス面では、Normal・Bossいずれのバリエーションも HP 50,000・攻撃力 300 で統一されており、移動速度29・索敵距離0.25・ノックバック数2と設定されている。role_type は両バリエーションで Technical。アビリティ設定・変身設定はなし。

Normal unit（c_yuw_00101_753get_Normal_Green）は EnterTargetKomaIndex をトリガーに登場し、HP係数1.5のもとキャラゲット01の最終ステージで通常敵として機能する。Boss unit（c_yuw_00101_okumuraget_Boss_Blue）はキャラゲット02の最終ステージで、先行ボス（c_yuw_00001）が撃破された後に FriendUnitDead トリガーで登場する連続ボスの2番手として機能し、Appearance攻撃による射程50の全体ノックバック（ForcedKnockBack5）を持つ。

通常攻撃は6段ヒット（各16〜17%ダメージ）の単体攻撃、スペシャル攻撃は全体250%ダメージ＋AttackPowerDown（効果時間500フレーム、効果量20）付与という、攻撃力デバフを伴う強力なスペシャルを持つ点が特徴的。

コマ効果はキャラゲット01_00003では全ラインNone、キャラゲット02_00006では一部ラインにAttackPowerDown（Playerサイド対象）が設定されており、スペシャル攻撃のデバフと合わせてプレイヤーの攻撃力を削る設計になっている。

---

## 3. ステージ別使用実態

### event_yuw1_charaget01_00003（イベント）

#### このステージでの役割

キャラゲットクエスト01の第3ステージ（最終ステージ）に登場する通常敵。コマ到達（EnterTargetKomaIndex=0）をトリガーに、後続のボス（c_yuw_00001_753get_Boss_Green）と同一トリガーで連続召喚される先鋒として機能する。HP係数1.5のノーマル枠として、ボス登場前のウォームアップ的な役割を担う。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_yuw_00101_753get_Normal_Green` | Normal | Technical | Green | 50,000 | 300 | 29 | 0.25 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_yuw_00101_753get_Normal_Green` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 75 |
| Special | 0 | なし | なし | 175 |

**MstAttackElement 詳細**

| attack_id | sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type | effective_duration | effect_parameter |
|-----------|-----------|------------|-------------------|-----------------|--------|------------|---------|----------------|------------|-------------------|-----------------|
| `c_yuw_00101_753get_Normal_Green_Normal_00000` | 1 | Direct | 0.35 | 1 | Foe/All | Damage | Normal | 16.0% | None | - | - |
| `c_yuw_00101_753get_Normal_Green_Normal_00000` | 2 | Direct | 0.35 | 1 | Foe/All | Damage | Normal | 16.0% | None | - | - |
| `c_yuw_00101_753get_Normal_Green_Normal_00000` | 3 | Direct | 0.35 | 1 | Foe/All | Damage | Normal | 16.0% | None | - | - |
| `c_yuw_00101_753get_Normal_Green_Normal_00000` | 4 | Direct | 0.35 | 1 | Foe/All | Damage | Normal | 16.0% | None | - | - |
| `c_yuw_00101_753get_Normal_Green_Normal_00000` | 5 | Direct | 0.35 | 1 | Foe/All | Damage | Normal | 16.0% | None | - | - |
| `c_yuw_00101_753get_Normal_Green_Normal_00000` | 6 | Direct | 0.35 | 1 | Foe/All | Damage | Normal | 17.0% | None | - | - |
| `c_yuw_00101_753get_Normal_Green_Special_00001` | 1 | Direct | 0.35 | 100 | Foe/All | Damage | Normal | 250.0% | AttackPowerDown | 500 | 20 |

通常攻撃は6段ヒット（累計97%ダメージ）の単体近距離攻撃。スペシャル攻撃は全体250%ダメージ＋AttackPowerDown（永続／効果時間500フレーム、効果量20）付与。

#### シーケンス設定

```
sequence_set_id: event_yuw1_charaget01_00003
sequence_element_id: 1
condition_type: EnterTargetKomaIndex
condition_value: 0
action_type: SummonEnemy
action_value: c_yuw_00101_753get_Normal_Green
summon_position: （指定なし）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 1.5
sequence_group_id: （なし）
```

コマ到達（EnterTargetKomaIndex=0）をトリガーに1体召喚。HP係数1.5。直後の sequence_element_id=2 で後続ボス（c_yuw_00001_753get_Boss_Green, HP係数2.4）が同条件で召喚される2連構成の先鋒として機能する。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| なし（全スロットNone） | - | - |

---

### event_yuw1_charaget02_00006（イベント）

#### このステージでの役割

キャラゲットクエスト02の第6ステージ（最終ステージ）に登場するボス敵。先行ボスである c_yuw_00001_okumuraget_Boss_Blue が撃破されると FriendUnitDead トリガーで召喚される連続ボスの2番手。Appearance攻撃による全体ノックバックを発動し、スペシャル攻撃でもAttackPowerDownを付与する強力な役割を担う。撃破後もさらに c_yuw_00301・c_yuw_00401 が連続登場するボスラッシュ構成の中間ボス。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_yuw_00101_okumuraget_Boss_Blue` | Boss | Technical | Blue | 50,000 | 300 | 29 | 0.25 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_yuw_00101_okumuraget_Boss_Blue` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 75 |
| Special | 0 | なし | なし | 175 |

**MstAttackElement 詳細**

| attack_id | sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type | effective_duration | effect_parameter |
|-----------|-----------|------------|-------------------|-----------------|--------|------------|---------|----------------|------------|-------------------|-----------------|
| `c_yuw_00101_okumuraget_Boss_Blue_Appearance_00001` | 1 | Direct | 50.0 | 100 | Foe/All | None | ForcedKnockBack5 | 100.0% | None | - | - |
| `c_yuw_00101_okumuraget_Boss_Blue_Normal_00000` | 1 | Direct | 0.35 | 1 | Foe/All | Damage | Normal | 16.0% | None | - | - |
| `c_yuw_00101_okumuraget_Boss_Blue_Normal_00000` | 2 | Direct | 0.35 | 1 | Foe/All | Damage | Normal | 16.0% | None | - | - |
| `c_yuw_00101_okumuraget_Boss_Blue_Normal_00000` | 3 | Direct | 0.35 | 1 | Foe/All | Damage | Normal | 16.0% | None | - | - |
| `c_yuw_00101_okumuraget_Boss_Blue_Normal_00000` | 4 | Direct | 0.35 | 1 | Foe/All | Damage | Normal | 16.0% | None | - | - |
| `c_yuw_00101_okumuraget_Boss_Blue_Normal_00000` | 5 | Direct | 0.35 | 1 | Foe/All | Damage | Normal | 16.0% | None | - | - |
| `c_yuw_00101_okumuraget_Boss_Blue_Normal_00000` | 6 | Direct | 0.35 | 1 | Foe/All | Damage | Normal | 17.0% | None | - | - |
| `c_yuw_00101_okumuraget_Boss_Blue_Special_00002` | 1 | Direct | 0.35 | 100 | Foe/All | Damage | Normal | 250.0% | AttackPowerDown | 500 | 20 |

登場時に射程50の全体ForcedKnockBack5を発動。通常攻撃は6段ヒット単体攻撃、スペシャル攻撃は全体250%ダメージ＋AttackPowerDown付与（効果時間500フレーム、効果量20）。

#### シーケンス設定

```
sequence_set_id: event_yuw1_charaget02_00006
sequence_element_id: 2
condition_type: FriendUnitDead
condition_value: 1
action_type: SummonEnemy
action_value: c_yuw_00101_okumuraget_Boss_Blue
summon_position: （指定なし）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 2
sequence_group_id: （なし）
```

FriendUnitDead（味方ユニット1体撃破）をトリガーとして召喚。先行ボス（c_yuw_00001, sequence_element_id=1）が倒れた後に登場する2番手。HP係数2.0。撃破後はさらに c_yuw_00301（HP係数2.5）、c_yuw_00401（HP係数2.5）が同条件で連続登場する。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| AttackPowerDown | 1（コマライン2のkoma1スロット） | Player |
| なし（その他ラインはNone） | - | - |

---
