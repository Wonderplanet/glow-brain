# 門神（enemy_jig_00001）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: enemy_jig_00001
> mst_series_id: jig
> 作品名: 地獄楽

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `enemy_jig_00001` |
| mst_series_id | `jig` |
| 作品名 | 地獄楽 |
| asset_key | `enemy_jig_00001` |
| is_phantomized | `0` |

---

## 2. キャラクター特徴まとめ

門神はメインクエスト Normal難易度において地獄楽ステージ群（normal_jig_00003〜00005）および他作品ステージ（normal_glo2_00001）で広く使用される汎用雑魚敵である。character_unit_kind は Normal、role_type は Defense で統一されており、HP 3,500・攻撃力 50 と低ステータスの典型的な序盤雑魚として配置される。カラーは Colorless のみで、killer 設定・アビリティ・変身設定は一切持たない。

出現条件は ElapsedTime（経過時間トリガー）が主流で4回使用されており、FriendUnitDead（味方ユニット全滅）による出現も1回確認されている。複数体まとめて召喚される場面（summon_count=20〜30）と1体単独で召喚される場面の両方が存在し、ステージ序盤〜中盤の継続的な敵圧力を担っている。

コマ効果は Poison（対象: Player）が2ステージで確認されており、プレイヤーキャラへのデバフ環境下での戦闘が設計されている。

---

## 3. ステージ別使用実態

### normal_glo2_00001（メインクエスト Normal）

#### このステージでの役割

他作品（glo2系）のノーマルステージに門神が1体だけ最初に出現する役割を担っている。ElapsedTime=0（開幕即座）かつ summon_count=1 で単独召喚され、序盤のファーストウェーブとして機能する。enemy_hp_coef=9 と高い倍率が設定されており、実質的なHPは大幅に増強されている。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_jig_00001_mainquest_Normal_Colorless` | Normal | Defense | Colorless | 3,500 | 50 | 31 | 0.21 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_jig_00001_mainquest_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 92 |

**MstAttackElement 詳細**

| mst_attack_id | sort_order | attack_type | range_start | range_end | max_target | target | damage_type | hit_type | power_parameter | effect_type |
|--------------|-----------|------------|------------|---------|-----------|--------|------------|---------|----------------|------------|
| e_jig_00001_mainquest_Normal_Colorless_Normal_00000 | 1 | Direct | Distance: 0 | Distance: 0.26 | 100 | Foe / All | Damage | Normal | Percentage: 100.0 | None |

#### シーケンス設定

```
condition_type: ElapsedTime
condition_value: 0
sequence_element_id: 5
action_type: SummonEnemy
action_value: e_jig_00001_mainquest_Normal_Colorless
summon_position: （空）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 9
sequence_group_id: （空）
```

開幕0秒で1体だけ召喚される。enemy_hp_coef=9 により基礎HP 3,500 の9倍相当のHPを持ち、見た目に反して耐久力の高い個体として登場する。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| Poison | 1 | Player |

---

### normal_jig_00003（メインクエスト Normal）

#### このステージでの役割

地獄楽ステージ序盤の第1クエストに相当し、門神が ElapsedTime=100（ゲーム開始から100フレーム後）に20体まとめて登場する。序盤に大量の雑魚が押し寄せる波状攻撃として設計されており、引き続き別パラメータ（jig_00401系）の敵も並行して出現する。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_jig_00001_mainquest_Normal_Colorless` | Normal | Defense | Colorless | 3,500 | 50 | 31 | 0.21 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_jig_00001_mainquest_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 92 |

**MstAttackElement 詳細**

| mst_attack_id | sort_order | attack_type | range_start | range_end | max_target | target | damage_type | hit_type | power_parameter | effect_type |
|--------------|-----------|------------|------------|---------|-----------|--------|------------|---------|----------------|------------|
| e_jig_00001_mainquest_Normal_Colorless_Normal_00000 | 1 | Direct | Distance: 0 | Distance: 0.26 | 100 | Foe / All | Damage | Normal | Percentage: 100.0 | None |

#### シーケンス設定

```
condition_type: ElapsedTime
condition_value: 100
sequence_element_id: 1
action_type: SummonEnemy
action_value: e_jig_00001_mainquest_Normal_Colorless
summon_position: （空）
summon_count: 20
summon_interval: 750
enemy_hp_coef: 4
sequence_group_id: （空）
```

開始100フレーム後に20体を750フレーム間隔で逐次召喚。enemy_hp_coef=4 で基礎HPの4倍。大量出現による数的優位を活かした設計。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| Poison | 1 | Player |

---

### normal_jig_00004（メインクエスト Normal）

#### このステージでの役割

地獄楽ステージ中盤の第2クエストに相当し、門神が2つのシーケンス要素で登場する。1回目は単体で早期召喚（ElapsedTime=300）、2回目は集団として後続波（ElapsedTime=650 に30体）という二段階の出現設計がされている。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_jig_00001_mainquest_Normal_Colorless` | Normal | Defense | Colorless | 3,500 | 50 | 31 | 0.21 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_jig_00001_mainquest_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 92 |

**MstAttackElement 詳細**

| mst_attack_id | sort_order | attack_type | range_start | range_end | max_target | target | damage_type | hit_type | power_parameter | effect_type |
|--------------|-----------|------------|------------|---------|-----------|--------|------------|---------|----------------|------------|
| e_jig_00001_mainquest_Normal_Colorless_Normal_00000 | 1 | Direct | Distance: 0 | Distance: 0.26 | 100 | Foe / All | Damage | Normal | Percentage: 100.0 | None |

#### シーケンス設定（1回目: 単体早期出現）

```
condition_type: ElapsedTime
condition_value: 300
sequence_element_id: 2
action_type: SummonEnemy
action_value: e_jig_00001_mainquest_Normal_Colorless
summon_position: （空）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 4
sequence_group_id: （空）
```

#### シーケンス設定（2回目: 集団後続波）

```
condition_type: ElapsedTime
condition_value: 650
sequence_element_id: 3
action_type: SummonEnemy
action_value: e_jig_00001_mainquest_Normal_Colorless
summon_position: （空）
summon_count: 30
summon_interval: 800
enemy_hp_coef: 4
sequence_group_id: （空）
```

300フレーム時点で先行として1体召喚し、650フレーム時点からさらに30体を800フレーム間隔で追加投入する二段波設計。いずれも enemy_hp_coef=4。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| なし | - | - |

---

### normal_jig_00005（メインクエスト Normal）

#### このステージでの役割

地獄楽ステージの後半クエストに相当し、FriendUnitDead=5（味方ユニット5体撃破後）という条件で30体まとめて出現する。比較的難易度が高く、ボス（jig_00301・jig_00201系）も同一シーケンス内に登場するため、門神は中盤の数的プレッシャーを担う役割。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_jig_00001_mainquest_Normal_Colorless` | Normal | Defense | Colorless | 3,500 | 50 | 31 | 0.21 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_jig_00001_mainquest_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 92 |

**MstAttackElement 詳細**

| mst_attack_id | sort_order | attack_type | range_start | range_end | max_target | target | damage_type | hit_type | power_parameter | effect_type |
|--------------|-----------|------------|------------|---------|-----------|--------|------------|---------|----------------|------------|
| e_jig_00001_mainquest_Normal_Colorless_Normal_00000 | 1 | Direct | Distance: 0 | Distance: 0.26 | 100 | Foe / All | Damage | Normal | Percentage: 100.0 | None |

#### シーケンス設定

```
condition_type: FriendUnitDead
condition_value: 5
sequence_element_id: 2
action_type: SummonEnemy
action_value: e_jig_00001_mainquest_Normal_Colorless
summon_position: （空）
summon_count: 30
summon_interval: 650
enemy_hp_coef: 4
sequence_group_id: （空）
```

味方ユニット5体撃破という条件で発動するリアクティブな召喚設計。30体を650フレーム間隔で投入し、ステージ中盤の集団プレッシャーを形成する。enemy_hp_coef=4。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| なし | - | - |
