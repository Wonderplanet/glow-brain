# フランキー・フランクリン（chara_spy_00401）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: chara_spy_00401
> mst_series_id: spy
> 作品名: SPY×FAMILY
> **対象フィルタ**: メインクエスト Normal 難易度のみ

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `chara_spy_00401` |
| mst_series_id | `spy` |
| 作品名 | SPY×FAMILY |
| asset_key | `chara_spy_00401` |
| is_phantomized | `1` |

---

## 2. キャラクター特徴まとめ

フランキー・フランクリンはメインクエスト Normal 難易度の 3 ステージ（`normal_glo1_00001` / `normal_spy_00005` / `normal_spy_00006`）に登場するボスユニットです。

**ステータス傾向:** HP 10,000 / 攻撃力 50 と高 HP・低攻撃力の典型的な Defense 型ボスです。移動速度は 31 と標準的で、索敵距離は 0.2 と短め。変身設定はなく、アビリティも持ちません。

**バリエーション:** Blue・Colorless の 2 色バリエーションが存在します。Blue 版は Special 攻撃（150% 威力）を持つのに対し、Colorless 版は Normal 攻撃のみの構成です。どちらも出現時に `ForcedKnockBack5`（50 距離内の全キャラクターを 5 ノックバック）を発動するため、フロントラインを一時的に崩す役割を担います。

**出現タイミング:** ElapsedTime（経過時間）で単体召喚されるパターンが主流です（2/3 ステージ）。`normal_spy_00006` ではシーケンスグループ group2 に所属し、`ElapsedTimeSinceSequenceGroupActivated` で発動する後続フェーズのボスとして登場します。

**コマ効果:** `normal_spy_00006` のみ AttackPowerUp（Player側、+30%）が設定されています。その他のステージはコマ効果なし（全 None）です。

---

## 3. ステージ別使用実態

---

### normal_glo1_00001（メインクエスト Normal）

#### このステージでの役割

SPY×FAMILY と他作品（ゴム・赤毛系キャラ）が混在するクロスオーバーステージの序盤ボスです。elapsed_time=350フレーム（約5.8秒）で早期登場し、出現時の `ForcedKnockBack5` でプレイヤーのフロントラインを崩してから後続の強敵（`c_spy_00101_general_n_Boss_Red` など）への繋ぎを作る役割です。enemy_hp_coef=9 のため実際の HP は通常値の 9 倍（90,000）です。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_spy_00401_general_n_Boss_Colorless` | Boss | Defense | Colorless | 10,000 | 50 | 31 | 0.2 | なし |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_spy_00401_general_n_Boss_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_id | attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | attack_delay | next_attack_interval |
|-----------|------------|-----------|--------------|------------------|--------------|-------------|---------------------|
| `c_spy_00401_general_n_Boss_Colorless_Appearance_00001` | Appearance | 0 | なし | 100% | 50 | 0 | 0 |
| `c_spy_00401_general_n_Boss_Colorless_Normal_00000` | Normal | 0 | なし | 100% | 60 | 25 | 100 |

**MstAttackElement 詳細**

| mst_attack_id | attack_type | range (start→end) | max_target | target | damage_type | hit_type | power_param_type | power_parameter | effect_type |
|---------------|------------|-------------------|------------|--------|-------------|----------|-----------------|-----------------|-------------|
| `…_Appearance_00001` | Direct | Distance 0 → 50.0 | 100 | Foe/Character/All | None | ForcedKnockBack5 | Percentage | 100.0 | None |
| `…_Normal_00000` | Direct | Distance 0 → 0.21 | 1 | Foe/All/All | Damage | Normal | Percentage | 100.0 | None |

> Colorless 版には Special 攻撃なし。

#### シーケンス設定

```
sequence_set_id:    normal_glo1_00001
sequence_element_id: 1
condition_type:     ElapsedTime
condition_value:    350
action_type:        SummonEnemy
action_value:       c_spy_00401_general_n_Boss_Colorless
summon_position:    （指定なし）
summon_count:       1
summon_interval:    0
enemy_hp_coef:      9
sequence_group_id:  （なし）
```

経過 350 フレーム（約 5.8 秒）でステージ開始直後に単体召喚。enemy_hp_coef=9 で HP 9 倍（実効 90,000）と非常に堅い序盤ボスとして機能。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド |
|-----------|---------|-----------|
| None（効果なし） | 全コマ | All |

> コマ効果なし（全 None）。

---

### normal_spy_00005（メインクエスト Normal）

#### このステージでの役割

SPY×FAMILY 単独ステージのメインボスです。経過 250 フレーム（約 4.2 秒）という最速クラスの登場タイミングでプレイヤーを迎え撃ちます。enemy_hp_coef=8 で実効 HP 80,000 と高耐久。撃破後（FriendUnitDead=3 の条件）に `c_spy_00201_general_n_Boss_Colorless` が後継ボスとして出現する 2 段構成のステージです。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_spy_00401_general_n_Boss_Colorless` | Boss | Defense | Colorless | 10,000 | 50 | 31 | 0.2 | なし |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_spy_00401_general_n_Boss_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_id | attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | attack_delay | next_attack_interval |
|-----------|------------|-----------|--------------|------------------|--------------|-------------|---------------------|
| `c_spy_00401_general_n_Boss_Colorless_Appearance_00001` | Appearance | 0 | なし | 100% | 50 | 0 | 0 |
| `c_spy_00401_general_n_Boss_Colorless_Normal_00000` | Normal | 0 | なし | 100% | 60 | 25 | 100 |

**MstAttackElement 詳細**

| mst_attack_id | attack_type | range (start→end) | max_target | target | damage_type | hit_type | power_param_type | power_parameter | effect_type |
|---------------|------------|-------------------|------------|--------|-------------|----------|-----------------|-----------------|-------------|
| `…_Appearance_00001` | Direct | Distance 0 → 50.0 | 100 | Foe/Character/All | None | ForcedKnockBack5 | Percentage | 100.0 | None |
| `…_Normal_00000` | Direct | Distance 0 → 0.21 | 1 | Foe/All/All | Damage | Normal | Percentage | 100.0 | None |

> Colorless 版には Special 攻撃なし。

#### シーケンス設定

```
sequence_set_id:    normal_spy_00005
sequence_element_id: 1
condition_type:     ElapsedTime
condition_value:    250
action_type:        SummonEnemy
action_value:       c_spy_00401_general_n_Boss_Colorless
summon_position:    （指定なし）
summon_count:       1
summon_interval:    0
enemy_hp_coef:      8
sequence_group_id:  （なし）
```

経過 250 フレーム（約 4.2 秒）でステージ最序盤に単体召喚。enemy_hp_coef=8 で実効 HP 80,000。倒した後に後続ボスが出現する 2 段構成のステージ。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド |
|-----------|---------|-----------|
| None（効果なし） | 全コマ | All |

> コマ効果なし（全 None）。

---

### normal_spy_00006（メインクエスト Normal）

#### このステージでの役割

グループ切り替え（SwitchSequenceGroup）を使った 2 フェーズ構成の SPY×FAMILY ステージです。フランキー・フランクリンは group2 に所属し、`ElapsedTimeSinceSequenceGroupActivated=0`（group2 開始直後）に即登場する第 2 フェーズのボスです。Blue カラーかつ Special 攻撃（150%威力）を持つため、第 1 フェーズ（`c_spy_00101_general_n_Boss_Blue` + `c_spy_00201_general_n_Boss_Blue`）より難易度が高い局面での登場となります。コマ効果 AttackPowerUp（Player側 +30%）が設定されており、プレイヤー側のダメージ増加でバランスが調整されています。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_spy_00401_general_n_Boss_Blue` | Boss | Defense | Blue | 10,000 | 50 | 31 | 0.2 | なし |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_spy_00401_general_n_Boss_Blue` | なし | None | なし | なし |

#### 攻撃パターン

| attack_id | attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | attack_delay | next_attack_interval |
|-----------|------------|-----------|--------------|------------------|--------------|-------------|---------------------|
| `c_spy_00401_general_n_Boss_Blue_Appearance_00001` | Appearance | 0 | なし | 100% | 50 | 0 | 0 |
| `c_spy_00401_general_n_Boss_Blue_Normal_00000` | Normal | 0 | なし | 100% | 60 | 25 | 100 |
| `c_spy_00401_general_n_Boss_Blue_Special_00002` | Special | 0 | なし | 150% | 138 | 67 | 0 |

**MstAttackElement 詳細**

| mst_attack_id | attack_type | range (start→end) | max_target | target | damage_type | hit_type | power_param_type | power_parameter | effect_type |
|---------------|------------|-------------------|------------|--------|-------------|----------|-----------------|-----------------|-------------|
| `…_Appearance_00001` | Direct | Distance 0 → 50.0 | 100 | Foe/Character/All | None | ForcedKnockBack5 | Percentage | 100.0 | None |
| `…_Normal_00000` | Direct | Distance 0 → 0.21 | 1 | Foe/All/All | Damage | Normal | Percentage | 100.0 | None |
| `…_Special_00002` | Direct | Distance 0 → 0.21 | 1 | Foe/All/All | Damage | Normal | Percentage | 150.0 | None |

> Blue 版は Special 攻撃（150% 威力、138 フレーム、67 フレーム遅延）を持つ。

#### シーケンス設定

```
sequence_set_id:    normal_spy_00006
sequence_element_id: 6
condition_type:     ElapsedTimeSinceSequenceGroupActivated
condition_value:    0
action_type:        SummonEnemy
action_value:       c_spy_00401_general_n_Boss_Blue
summon_position:    （指定なし）
summon_count:       1
summon_interval:    0
enemy_hp_coef:      8
sequence_group_id:  group2
```

group2 開始（condition_value=0）直後に即召喚される第 2 フェーズのボス。group2 は FriendUnitDead=3 で group1 から切り替わる。enemy_hp_coef=8 で実効 HP 80,000。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| AttackPowerUp | 2 | Player |

> `normal_spy_00006_2`（koma2）と `normal_spy_00006_3`（koma1）に AttackPowerUp (Player側, effect_parameter1=30) が設定。プレイヤーの攻撃力を 30% 増加させることでステージの難度調整が行われている。
