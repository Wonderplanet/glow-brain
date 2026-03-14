# 隠された英雄の姿 怪獣８号（chara_kai_00002）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: chara_kai_00002
> mst_series_id: kai
> 作品名: 怪獣８号

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `chara_kai_00002` |
| mst_series_id | `kai` |
| 作品名 | 怪獣８号 |
| asset_key | `chara_kai_00002` |
| is_phantomized | `1` |

---

## 2. キャラクター特徴まとめ

「隠された英雄の姿 怪獣８号」はメインクエスト Normalにのみ登場し、`normal_glo4_00001` と `normal_kai_00006` の2ステージで確認できる。

使われ方はステージによって大きく異なる。`normal_glo4_00001` では Normal / Attack / Green のパラメータ（HP 400,000・攻撃力 1,000）として友軍撃破後に出現する中程度の脅威として位置づけられている。一方 `normal_kai_00006` では Boss / Support / Yellow（HP 700,000・攻撃力 1,700）として友軍2体撃破後に登場する強敵・ステージの山場ボスとして機能している。

変身設定は両パラメータともなし（transformationConditionType = None）。アビリティも設定されていない。

コマ効果は両ステージともすべて None であり、特殊なコマギミックは使われていない。

---

## 3. ステージ別使用実態

### normal_glo4_00001（メインクエスト Normal）

#### このステージでの役割

友軍ユニット2体目の撃破を契機に出現する、中程度のノーマルユニット。Attackロール・Greenカラーで、前後に登場する味方敵ユニット群と波状攻撃を形成する役割を担う。単体での即死能力は低いが、複数の友軍撃破トリガーが連鎖するため、処理が遅れると次の強力ユニット（Boss）の出現を早める流れとなっている。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_kai_00002_general_as4_Normal_Green` | Normal | Attack | Green | 400,000 | 1,000 | 40 | 0.11 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_kai_00002_general_as4_Normal_Green` | なし | None | なし | なし |

#### 攻撃パターン

**通常攻撃（Normal）**

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 75 |

MstAttackElement（Normal攻撃）:

| sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|---------------------|-----------------|--------|------------|---------|----------------|------------|
| 1 | Direct | 0.3 | 1 | Foe | Damage | Normal | 100.0% | None |

**スペシャル攻撃（Special）**

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Special | 0 | なし | なし | 120 |

MstAttackElement（Special攻撃）:

| sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|---------------------|-----------------|--------|------------|---------|----------------|------------|
| 1 | Direct | 0.3 | 1 | Foe | Damage | KnockBack3 | 100.0% | None |
| 2 | Direct | 0.3 | 1 | Foe | Damage | Normal | 20.0% | None |
| 3 | Direct | 0.3 | 1 | Foe | Damage | Normal | 20.0% | None |
| 4 | Direct | 0.3 | 1 | Foe | Damage | Normal | 20.0% | None |
| 5 | Direct | 0.3 | 1 | Foe | Damage | Normal | 20.0% | None |
| 6 | Direct | 0.3 | 1 | Foe | Damage | Normal | 20.0% | None |

通常攻撃は単体ダメージ100%。スペシャルはKnockBack3で最初に叩き飛ばしたあと、100%+20%×5の連続追撃を加える多段構成。

#### シーケンス設定

```
condition_type: FriendUnitDead
condition_value: 2
sequence_element_id: 3
action_type: SummonEnemy
action_value: c_kai_00002_general_as4_Normal_Green
summon_position: （空）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 1
sequence_group_id: （空）
```

友軍2体目撃破をトリガーに単体で出現。同じ友軍2体目撃破トリガーで `c_kai_00101_general_as4_Normal_Green` も同時に召喚される（sequence_element_id: 4）ため、実質2体同時出現になる。

#### コマ効果

コマ効果なし（全コマ effect_type = None）。

---

### normal_kai_00006（メインクエスト Normal）

#### このステージでの役割

友軍2体目撃破後に出現するBossユニットで、このステージの最大の難関として機能する。Support / Yellowカラーで、登場時に出現演出（Appearance攻撃）でノックバックを与えた後、通常攻撃に攻撃力ダウン効果を付与するという独特の戦術的支援型ボスとして設計されている。HP 700,000・攻撃力 1,700と高水準のステータスを持つ。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_kai_00002_general_Boss_Yellow` | Boss | Support | Yellow | 700,000 | 1,700 | 45 | 0.2 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_kai_00002_general_Boss_Yellow` | なし | None | なし | なし |

#### 攻撃パターン

**登場演出（Appearance）**

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |

MstAttackElement（Appearance）:

| sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|---------------------|-----------------|--------|------------|---------|----------------|------------|
| 1 | Direct | 50.0 | 100 | Foe | None | ForcedKnockBack5 | 0.0% | None |

登場時に画面全体（range_end 50.0）の敵ユニット最大100体を強制ノックバック5。ダメージなしでプレイヤーの前線を一気に押し戻す演出兼妨害効果。

**通常攻撃（Normal）**

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 75 |

MstAttackElement（Normal攻撃）:

| sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type | effective_count | effective_duration | effect_parameter |
|-----------|------------|---------------------|-----------------|--------|------------|---------|----------------|------------|---------------|------------------|----------------|
| 1 | Direct | 0.3 | 1 | Foe | Damage | Normal | 100.0% | AttackPowerDown | -1（永続） | 1000フレーム | 20 |

通常攻撃は単体100%ダメージ＋攻撃力20%ダウン（永続・1000フレーム）。Supportロールらしく、攻撃力を下げてプレイヤーユニットの継続戦闘力を削ぐ。

**スペシャル攻撃（Special）**

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Special | 0 | なし | なし | 120 |

MstAttackElement（Special攻撃）:

| sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|---------------------|-----------------|--------|------------|---------|----------------|------------|
| 1 | Direct | 0.3 | 1 | Foe | Damage | KnockBack3 | 200.0% | None |
| 2 | Direct | 0.3 | 1 | Foe | Damage | Normal | 30.0% | None |
| 3 | Direct | 0.3 | 1 | Foe | Damage | Normal | 30.0% | None |
| 4 | Direct | 0.3 | 1 | Foe | Damage | Normal | 30.0% | None |
| 5 | Direct | 0.3 | 1 | Foe | Damage | Normal | 30.0% | None |
| 6 | Direct | 0.3 | 1 | Foe | Damage | Normal | 30.0% | None |

スペシャルはKnockBack3で200%の大ダメージを与えた後、30%×5の追撃（計350%）。Normal版の特殊技に比べてダメージ総量が大幅に増強されている。

#### シーケンス設定

```
condition_type: FriendUnitDead
condition_value: 2
sequence_element_id: 3
action_type: SummonEnemy
action_value: c_kai_00002_general_Boss_Yellow
summon_position: （空）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 1
sequence_group_id: （空）
```

友軍2体目撃破をトリガーに単体で出現。`normal_kai_00006_1`（InitialSummon: `c_kai_00001`）→ `sequence_element_id: 2`（FriendUnitDead 1体: `c_kai_00301`）→ `sequence_element_id: 3`（FriendUnitDead 2体: 本キャラ）という段階的なエスカレーション構成になっており、本キャラはこの波状ラッシュの最終フェーズを担う。出現後は ElapsedTime トリガーによる継続的な `e_kai_00101` 増援も加わり、長期戦を強いるステージ設計。

#### コマ効果

コマ効果なし（全コマ effect_type = None）。
