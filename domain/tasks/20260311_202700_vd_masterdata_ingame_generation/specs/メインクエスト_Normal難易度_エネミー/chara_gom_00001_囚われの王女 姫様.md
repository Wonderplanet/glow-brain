# 囚われの王女 姫様（chara_gom_00001）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: chara_gom_00001
> mst_series_id: gom
> 作品名: "姫様"拷問"の時間です

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `chara_gom_00001` |
| mst_series_id | `gom` |
| 作品名 | "姫様"拷問"の時間です |
| asset_key | `chara_gom_00001` |
| is_phantomized | `1` |

---

## 2. キャラクター特徴まとめ

「囚われの王女 姫様」は、normalクエストのNormal難易度において `c_gom_00001_general_n_Boss_Yellow`（Boss/Defense/Yellow）の1種類のパラメータのみで使用される。HP=10,000・攻撃力=50と攻撃力は控えめだが、Bossユニットとしてステージの軸を担う。移動速度=25はBossキャラとしては標準的な速度。

登場パターンは2ステージで確認されており、`normal_glo1_00002` では初期召喚（InitialSummon）によりステージ開始直後に登場し、hp_coef=14の高倍率で実際の耐久力が大幅に引き上げられる。`normal_gom_00006` では特定グループ（group1）が発動してから即時（ElapsedTimeSinceSequenceGroupActivated=0）に出現し、hp_coef=10の設定でステージ後半の難度を支える役割を担う。

攻撃パターンとしては、登場時に広範囲（0〜50）のForcedKnockBack5を持つAppearanceアタックを持ち、通常攻撃は近接単体ダメージ（Percentage 100%）を与える。Specialでは自身に対してDamageCut（500フレーム持続）を付与し、場持ちの良さが特徴。アビリティ・変身設定はなし。

コマ効果の傾向としては、登場するステージではAttackPowerDown（Player側）が主に使用され、一部のステージではSlipDamageも採用されている。プレイヤー側ユニットの攻撃力を削ぐことで、Bossユニットである姫様の耐久を間接的に補強する設計と見られる。

---

## 3. ステージ別使用実態

### normal_glo1_00002（メインクエスト Normal）

#### このステージでの役割

`normal_glo1_00002` において姫様は初期召喚（InitialSummon）で登場する核心的なBossユニットである。hp_coef=14 という高倍率設定により実際のHPは 140,000 相当となり、ステージの主要障壁として機能する。ステージ中盤には別グループ（group1）が起動し、多数の雑魚を含む第2波が押し寄せる複合構成となっている。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_gom_00001_general_n_Boss_Yellow` | Boss | Defense | Yellow | 10,000 | 50 | 25 | 0.16 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_gom_00001_general_n_Boss_Yellow` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 65 |
| Special | 0 | なし | なし | 200 |

**MstAttackElement 詳細**

| mst_attack_id | sort_order | attack_type | range_start | range_end | max_target | target | damage_type | hit_type | power_parameter | effect_type | effective_count | effective_duration |
|--------------|-----------|------------|-------------|-----------|-----------|--------|------------|---------|----------------|------------|----------------|------------------|
| `c_gom_00001_general_n_Boss_Yellow_Appearance_00001` | 1 | Direct | Distance/0 | Distance/50.0 | 100 | Foe/Character | None | ForcedKnockBack5 | Percentage/100.0 | None | 0 | 0 |
| `c_gom_00001_general_n_Boss_Yellow_Normal_00000` | 1 | Direct | Distance/0 | Distance/0.17 | 1 | Foe/All | Damage | Normal | Percentage/100.0 | None | 0 | 0 |
| `c_gom_00001_general_n_Boss_Yellow_Special_00002` | 1 | Direct | Distance/0 | Distance/0.17 | 1 | Self/All | None | Normal | Percentage/100.0 | DamageCut | -1 | 500 |

#### シーケンス設定

```
sequence_set_id: normal_glo1_00002
sequence_element_id: 1
condition_type: InitialSummon
condition_value: 0
action_type: SummonEnemy
action_value: c_gom_00001_general_n_Boss_Yellow
summon_position: 1.3
summon_count: 1
summon_interval: 0
enemy_hp_coef: 14
sequence_group_id: （なし）
```

ステージ開始直後（InitialSummon）に位置 1.3（コマ列付近）に1体出現。hp_coef=14 によりHPが基本値の14倍（実質 140,000 相当）まで引き上げられており、このステージ最大の耐久力を持つボスとして機能する。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| AttackPowerDown | 2 | Player |

---

### normal_gom_00006（メインクエスト Normal）

#### このステージでの役割

`normal_gom_00006` において姫様は、2体目のフレンドユニット（FriendUnitDead=2）が倒れた際に起動するグループ（group1）の中で、即時（ElapsedTimeSinceSequenceGroupActivated=0）に出現するBossユニットである。hp_coef=10 により実質HP 100,000 相当となる。グループ起動後に短い時間差で多数の雑魚も追加召喚される大規模な第2波の中核を担う。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_gom_00001_general_n_Boss_Yellow` | Boss | Defense | Yellow | 10,000 | 50 | 25 | 0.16 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_gom_00001_general_n_Boss_Yellow` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 65 |
| Special | 0 | なし | なし | 200 |

**MstAttackElement 詳細**

| mst_attack_id | sort_order | attack_type | range_start | range_end | max_target | target | damage_type | hit_type | power_parameter | effect_type | effective_count | effective_duration |
|--------------|-----------|------------|-------------|-----------|-----------|--------|------------|---------|----------------|------------|----------------|------------------|
| `c_gom_00001_general_n_Boss_Yellow_Appearance_00001` | 1 | Direct | Distance/0 | Distance/50.0 | 100 | Foe/Character | None | ForcedKnockBack5 | Percentage/100.0 | None | 0 | 0 |
| `c_gom_00001_general_n_Boss_Yellow_Normal_00000` | 1 | Direct | Distance/0 | Distance/0.17 | 1 | Foe/All | Damage | Normal | Percentage/100.0 | None | 0 | 0 |
| `c_gom_00001_general_n_Boss_Yellow_Special_00002` | 1 | Direct | Distance/0 | Distance/0.17 | 1 | Self/All | None | Normal | Percentage/100.0 | DamageCut | -1 | 500 |

#### シーケンス設定

```
sequence_set_id: normal_gom_00006
sequence_element_id: 12
condition_type: ElapsedTimeSinceSequenceGroupActivated
condition_value: 0
action_type: SummonEnemy
action_value: c_gom_00001_general_n_Boss_Yellow
summon_position: （なし）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 10
sequence_group_id: group1
```

2体目のフレンドユニットが倒れたことを契機に group1 が発動し、その起動直後（elapsed=0）に出現する。summon_position が未指定のため、デフォルト位置に1体のみ召喚。hp_coef=10 で基本HPの10倍（実質 100,000 相当）となり、group1 内の追加雑魚群とともにステージ後半の大波を形成する。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| SlipDamage | 2 | Player |
