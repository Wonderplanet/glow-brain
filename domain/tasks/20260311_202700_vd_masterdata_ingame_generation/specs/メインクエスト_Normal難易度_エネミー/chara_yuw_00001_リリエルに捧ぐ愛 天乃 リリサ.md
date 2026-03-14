# リリエルに捧ぐ愛 天乃 リリサ（chara_yuw_00001）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: chara_yuw_00001
> mst_series_id: yuw
> 作品名: 2.5次元の誘惑

> **フィルタ条件**: normalクエストのNormal難易度のみ（challenge・raidステージを除外）

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `chara_yuw_00001` |
| mst_series_id | `yuw` |
| 作品名 | 2.5次元の誘惑 |
| asset_key | `chara_yuw_00001` |
| is_phantomized | `1` |

---

## 2. キャラクター特徴まとめ

リリエルに捧ぐ愛 天乃 リリサは、2.5次元の誘惑イベント（yuw1）のキャラゲットクエスト・1日クエストに登場するキャラクター。フィルタ対象（normalクエストNormal難易度）では、Normal unitとBoss unitの両バリエーションが存在する。

ステータス面では、Normal unitは HP 10,000〜50,000・攻撃力 300〜500 の幅があり、1日クエスト専用の低スタッツ版（HP 10,000・攻撃力 300）と標準的な高スタッツ版（HP 50,000・攻撃力 500）に分かれる。Boss unitは全て HP 50,000・攻撃力 500 で統一されており、出現時に全体ノックバック（ForcedKnockBack5）を発動する Appearance 攻撃を持つ。変身設定は全バリエーション共通でなし。アビリティ設定もなし。

role_type は全バリエーションで Attack。カラーは Colorless・Green・Blue の3種類が使われており、ステージごとに異なる色が設定されている。コマ効果は大半のステージで None（効果なし）で、`event_yuw1_charaget02_00006` の1コマラインのみ AttackPowerDown（Playerサイド対象）が設定されている。

シーケンスのトリガーは `ElapsedTime`（経過時間）・`EnterTargetKomaIndex`（コマ到達）・`FriendUnitDead`（味方撃破）・`InitialSummon`（初期召喚）の4種類が使われており、Normal unitは ElapsedTime や EnterTargetKomaIndex で召喚されることが多く、Boss unitは InitialSummon または FriendUnitDead（前のボスが撃破されたとき）で召喚されるパターンが見られる。

---

## 3. ステージ別使用実態

### event_yuw1_1day_00001（イベント）

#### このステージでの役割

1日クエスト（1周回の消化型ステージ）の後半に登場する通常敵。HP 10,000・攻撃力 300 と低スタッツで、経過時間600フレームで1体召喚される。ステージ序盤には別キャラ（c_yuw_00601）が先に登場し、リリサは後続として登場する構成。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_yuw_00001_1d1c_Normal_Colorless` | Normal | Attack | Colorless | 10,000 | 300 | 30 | 0.24 | 3 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_yuw_00001_1d1c_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 77 |
| Special | 0 | なし | なし | 184 |

**MstAttackElement 詳細**

| attack_id | sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|-----------|------------|-------------------|-----------------|--------|------------|---------|----------------|------------|
| `c_yuw_00001_1d1c_Normal_Colorless_Normal_00000` | 1 | Direct | 0.34 | 1 | Foe/All | Damage | Normal | 100.0% | None |
| `c_yuw_00001_1d1c_Normal_Colorless_Special_00001` | 1 | Direct | 0.34 | 100 | Foe/All | Damage | Normal | 200.0% | None |

#### シーケンス設定

```
sequence_set_id: event_yuw1_1day_00001
sequence_element_id: 2
condition_type: ElapsedTime
condition_value: 600
action_type: SummonEnemy
action_value: c_yuw_00001_1d1c_Normal_Colorless
summon_position: （指定なし）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 1.8
sequence_group_id: （なし）
```

経過時間600フレームを条件に1体召喚。HP係数1.8が設定されており、実際のHPはパラメータ基本値（10,000）の1.8倍となる。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| なし（全スロットNone） | - | - |

---

### event_yuw1_charaget01_00002（イベント）

#### このステージでの役割

キャラゲットクエスト01の第2ステージ（中間難易度）に登場する通常敵。コマ到達（EnterTargetKomaIndex）をトリガーに1体召喚され、HP 50,000・攻撃力 500 とスタッツは高めでメインの戦闘対象となる。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_yuw_00001_753get_Normal_Green` | Normal | Attack | Green | 50,000 | 500 | 34 | 0.24 | 3 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_yuw_00001_753get_Normal_Green` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 77 |
| Special | 0 | なし | なし | 184 |

**MstAttackElement 詳細**

| attack_id | sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|-----------|------------|-------------------|-----------------|--------|------------|---------|----------------|------------|
| `c_yuw_00001_753get_Normal_Green_Normal_00000` | 1 | Direct | 0.34 | 1 | Foe/All | Damage | Normal | 100.0% | None |
| `c_yuw_00001_753get_Normal_Green_Special_00001` | 1 | Direct | 0.34 | 100 | Foe/All | Damage | Normal | 200.0% | None |

#### シーケンス設定

```
sequence_set_id: event_yuw1_charaget01_00002
sequence_element_id: 1
condition_type: EnterTargetKomaIndex
condition_value: 0
action_type: SummonEnemy
action_value: c_yuw_00001_753get_Normal_Green
summon_position: （指定なし）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 2.0
sequence_group_id: （なし）
```

コマ到達（EnterTargetKomaIndex=0）をトリガーに即時1体召喚。HP係数2.0が設定されている。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| なし（全スロットNone） | - | - |

---

### event_yuw1_charaget01_00003（イベント）

#### このステージでの役割

キャラゲットクエスト01の第3ステージ（最終ステージ）に登場するボス敵。別の通常敵（c_yuw_00101）と同じコマ到達トリガーで連続召喚される2番手のボスとして登場。Appearance攻撃による全体ノックバックを発動する強力な敵。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_yuw_00001_753get_Boss_Green` | Boss | Attack | Green | 50,000 | 500 | 34 | 0.24 | 3 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_yuw_00001_753get_Boss_Green` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 77 |
| Special | 0 | なし | なし | 184 |

**MstAttackElement 詳細**

| attack_id | sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|-----------|------------|-------------------|-----------------|--------|------------|---------|----------------|------------|
| `c_yuw_00001_753get_Boss_Green_Appearance_00001` | 1 | Direct | 50.0 | 100 | Foe/All | None | ForcedKnockBack5 | 100.0% | None |
| `c_yuw_00001_753get_Boss_Green_Normal_00000` | 1 | Direct | 0.34 | 1 | Foe/All | Damage | Normal | 100.0% | None |
| `c_yuw_00001_753get_Boss_Green_Special_00002` | 1 | Direct | 0.34 | 100 | Foe/All | Damage | Normal | 200.0% | None |

登場時に射程50の全体ForcedKnockBack5を発動。通常攻撃は単体・スペシャルは全体攻撃（100体対象）。

#### シーケンス設定

```
sequence_set_id: event_yuw1_charaget01_00003
sequence_element_id: 2
condition_type: EnterTargetKomaIndex
condition_value: 0
action_type: SummonEnemy
action_value: c_yuw_00001_753get_Boss_Green
summon_position: （指定なし）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 2.4
sequence_group_id: （なし）
```

コマ到達（EnterTargetKomaIndex=0）をトリガーに、先行する通常敵（sequence_element_id=1）と同条件で召喚される2番手ボス。HP係数2.4と高めで耐久力がある。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| なし（全スロットNone） | - | - |

---

### event_yuw1_charaget02_00003（イベント）

#### このステージでの役割

キャラゲットクエスト02の第3ステージに登場する通常敵（序盤）。開幕即時召喚（ElapsedTime=0）で登場し、その後時間経過で別ボス（c_yuw_00301）が登場する2段構成ステージの先鋒。Blue色。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_yuw_00001_okumuraget_Normal_Blue` | Normal | Attack | Blue | 50,000 | 500 | 34 | 0.24 | 3 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_yuw_00001_okumuraget_Normal_Blue` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 77 |
| Special | 0 | なし | なし | 184 |

**MstAttackElement 詳細**

| attack_id | sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|-----------|------------|-------------------|-----------------|--------|------------|---------|----------------|------------|
| `c_yuw_00001_okumuraget_Normal_Blue_Normal_00000` | 1 | Direct | 0.34 | 1 | Foe/All | Damage | Normal | 100.0% | None |
| `c_yuw_00001_okumuraget_Normal_Blue_Special_00001` | 1 | Direct | 0.34 | 100 | Foe/All | Damage | Normal | 200.0% | None |

#### シーケンス設定

```
sequence_set_id: event_yuw1_charaget02_00003
sequence_element_id: 1
condition_type: ElapsedTime
condition_value: 0
action_type: SummonEnemy
action_value: c_yuw_00001_okumuraget_Normal_Blue
summon_position: （指定なし）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 1.5
sequence_group_id: （なし）
```

開幕ElapsedTime=0で即時召喚。HP係数1.5。続いてElapsedTime=1900フレームで別ボスが登場する。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| なし（全スロットNone） | - | - |

---

### event_yuw1_charaget02_00005（イベント）

#### このステージでの役割

キャラゲットクエスト02の第5ステージに登場する通常敵。開幕召喚後、味方ユニット撃破（FriendUnitDead）をトリガーに追加召喚される補充型の敵。同一パラメータが複数回召喚される繰り返し敵として機能し、その後別ボス（c_yuw_00401）が登場する構成。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_yuw_00001_okumuraget_Normal_Blue` | Normal | Attack | Blue | 50,000 | 500 | 34 | 0.24 | 3 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_yuw_00001_okumuraget_Normal_Blue` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 77 |
| Special | 0 | なし | なし | 184 |

**MstAttackElement 詳細**

| attack_id | sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|-----------|------------|-------------------|-----------------|--------|------------|---------|----------------|------------|
| `c_yuw_00001_okumuraget_Normal_Blue_Normal_00000` | 1 | Direct | 0.34 | 1 | Foe/All | Damage | Normal | 100.0% | None |
| `c_yuw_00001_okumuraget_Normal_Blue_Special_00001` | 1 | Direct | 0.34 | 100 | Foe/All | Damage | Normal | 200.0% | None |

#### シーケンス設定

```
sequence_set_id: event_yuw1_charaget02_00005
sequence_element_id: 1
condition_type: ElapsedTime
condition_value: 0
action_type: SummonEnemy
action_value: c_yuw_00001_okumuraget_Normal_Blue
summon_position: （指定なし）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 2.0
sequence_group_id: （なし）
```

```
sequence_set_id: event_yuw1_charaget02_00005
sequence_element_id: 2
condition_type: FriendUnitDead
condition_value: 1
action_type: SummonEnemy
action_value: c_yuw_00001_okumuraget_Normal_Blue
summon_position: （指定なし）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 3.0
sequence_group_id: （なし）
```

開幕召喚（HP係数2.0）後、味方ユニットが1体撃破されるたびに追加召喚（HP係数3.0）。さらに別条件でボスが登場する構成。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| なし（全スロットNone） | - | - |

---

### event_yuw1_charaget02_00006（イベント）

#### このステージでの役割

キャラゲットクエスト02の第6ステージ（最終ステージ）に登場するボス敵。InitialSummon（初期配置）で0.75の位置に最初から配置される主要ボス。Appearance攻撃による全体ノックバックを発動し、撃破されると後続のボス（c_yuw_00101, c_yuw_00301, c_yuw_00401）が次々と登場する連続ボスステージの先鋒。一部コマラインにAttackPowerDown（プレイヤー対象）が設定された難度の高いステージ。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_yuw_00001_okumuraget_Boss_Blue` | Boss | Attack | Blue | 50,000 | 500 | 34 | 0.24 | 3 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_yuw_00001_okumuraget_Boss_Blue` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 77 |
| Special | 0 | なし | なし | 184 |

**MstAttackElement 詳細**

| attack_id | sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|-----------|------------|-------------------|-----------------|--------|------------|---------|----------------|------------|
| `c_yuw_00001_okumuraget_Boss_Blue_Appearance_00001` | 1 | Direct | 50.0 | 100 | Foe/All | None | ForcedKnockBack5 | 100.0% | None |
| `c_yuw_00001_okumuraget_Boss_Blue_Normal_00000` | 1 | Direct | 0.34 | 1 | Foe/All | Damage | Normal | 100.0% | None |
| `c_yuw_00001_okumuraget_Boss_Blue_Special_00002` | 1 | Direct | 0.34 | 100 | Foe/All | Damage | Normal | 200.0% | None |

登場時に射程50の全体ForcedKnockBack5を発動。

#### シーケンス設定

```
sequence_set_id: event_yuw1_charaget02_00006
sequence_element_id: 1
condition_type: InitialSummon
condition_value: 0
action_type: SummonEnemy
action_value: c_yuw_00001_okumuraget_Boss_Blue
summon_position: 0.75
summon_count: 1
summon_interval: 0
enemy_hp_coef: 2.0
sequence_group_id: （なし）
```

InitialSummonで位置0.75に初期配置。HP係数2.0。撃破後はFriendUnitDeadトリガーで後続ボスが3体連続登場する。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| AttackPowerDown | 1 | Player |
| なし（その他ラインはNone） | - | - |

---
