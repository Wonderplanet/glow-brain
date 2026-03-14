# 溢れる母性 花園 羽々里（chara_kim_00001）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: chara_kim_00001
> mst_series_id: kim
> 作品名: 君のことが大大大大大好きな100人の彼女

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `chara_kim_00001` |
| mst_series_id | `kim` |
| 作品名 | 君のことが大大大大大好きな100人の彼女 |
| asset_key | `chara_kim_00001` |
| is_phantomized | `1` |

---

## 2. キャラクター特徴まとめ

**normalクエストのNormal難易度での使用実績はありません。**

花園 羽々里はイベント（4ステージ）および降臨バトル（1ステージ）にのみ登場するキャラクターです。全パラメータが `Boss` / `Red` に統一されており、常に強敵としての位置づけです。role_type は `Defense`（challenge・charaget02・savage 系）と `Technical`（advent 系）の2種類が存在し、コンテンツの性質によって使い分けられています。

HP は 10,000〜50,000 の幅があり、イベントの高難易度ステージや降臨バトルでは 50,000 以上の大型ボスとして登場します。移動速度は 35〜40 の範囲で、advent 系のみやや遅め（35）です。変身設定はすべてのパラメータで `None` となっており、変身なしの単純ボス運用が基本です。

コマ効果は `AttackPowerDown`（Player 対象）と `Gust`（Player 対象）が主軸となっており、プレイヤーへの攻撃力低下・風圧を組み合わせた妨害構成が特徴的です。`Poison`・`SlipDamage` を組み合わせる複合妨害構成の事例も一部存在します。

---

## 3. ステージ別使用実態

> **コンテンツフィルタ「normalクエストのNormal難易度のみ」に該当するステージはありません。**
> 以下は参考情報として、全実績ステージの詳細を記載します。

---

### event_kim1_challenge_00004（イベント）

#### このステージでの役割

challenge系イベントの最終ボスとして中盤に出現する。フレンドユニット2体撃破後に出現するため、プレイヤーが戦力を消耗したタイミングで登場する設計となっている。HP10,000のDefenseボスで、比較的抑えめな強度設定。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_kim_00001_kim1_challenge_Boss_Red` | Boss | Defense | Red | 10,000 | 100 | 40 | 0.18 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_kim_00001_kim1_challenge_Boss_Red` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 65 |
| Special | 0 | なし | なし | 125 |

**Special 攻撃詳細（MstAttackElement）:**

| sort_order | attack_type | 射程(range_end_parameter) | max_target_count | damage_type | hit_type | power_parameter | effect_type | effective_duration | effect_parameter |
|-----------|------------|--------------------------|-----------------|-------------|---------|----------------|------------|-------------------|-----------------|
| 1 | Direct | 0.52 | 100 | Damage | Normal | 2.0% | None | - | - |
| 2 | Direct | 0.52 | 100 | Damage | Normal | 5.0% | None | - | - |
| 3 | Direct | 0.52 | 100 | Damage | Normal | 8.0% | None | - | - |
| 4 | Direct | 0.52 | 100 | Damage | Normal | 25.0% | None | - | - |
| 5 | Direct | 0.52 | 100 | Damage | Stun | 0.0% | None | - | - |

> Special は5段階の連続ダメージ + 最終段スタン。通常攻撃は全敵対象・Normal 100%ダメージ。

#### シーケンス設定

```
sequence_element_id: 1
condition_type: FriendUnitDead
condition_value: 2
action_type: SummonEnemy
action_value: c_kim_00001_kim1_challenge_Boss_Red
summon_position: 3.65
summon_count: 1
summon_interval: 0
enemy_hp_coef: 10
sequence_group_id: （なし）
```

フレンドユニット2体が撃破されたタイミングで単体召喚。summon_position 3.65 と前方気味の位置に配置される。HP倍率は 10 と低め。

#### コマ効果

このステージのコマ効果に有効効果（None以外）はありません。全コマ `None` で構成されています。

| コマ効果種別 | 使用回数 | 対象サイド |
|-----------|---------|-----------|
| なし（全行 None） | - | - |

---

### event_kim1_charaget02_00005（イベント）

#### このステージでの役割

charaget02 系イベントの第1ステージ。ゲーム開始から1秒後（ElapsedTime: 1000ms）に花園 羽々里が出現し、序盤から強敵としてプレイヤーにプレッシャーを与える設計。HP20倍率のDefenseボス。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_kim_00001_kim1_charaget02_Boss_Red` | Boss | Defense | Red | 10,000 | 100 | 40 | 0.18 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_kim_00001_kim1_charaget02_Boss_Red` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 65 |
| Special | 0 | なし | なし | 125 |

**Special 攻撃詳細（MstAttackElement）:**

| sort_order | attack_type | 射程(range_end_parameter) | max_target_count | damage_type | hit_type | power_parameter | effect_type | effective_duration | effect_parameter |
|-----------|------------|--------------------------|-----------------|-------------|---------|----------------|------------|-------------------|-----------------|
| 1 | Direct | 0.52 | 100 | Damage | Normal | 2.0% | None | - | - |
| 2 | Direct | 0.52 | 100 | Damage | Normal | 5.0% | None | - | - |
| 3 | Direct | 0.52 | 100 | Damage | Normal | 8.0% | None | - | - |
| 4 | Direct | 0.52 | 100 | Damage | Normal | 25.0% | None | - | - |
| 5 | Direct | 0.52 | 100 | Damage | KnockBack1 | 60.0% | AttackPowerDown | 1000ms | 20 |

> charaget02 系 Special の最終段は KnockBack1 + 攻撃力ダウン付き（challenge 系の Stun とは異なる）。

#### シーケンス設定

```
sequence_element_id: 1
condition_type: ElapsedTime
condition_value: 1000
action_type: SummonEnemy
action_value: c_kim_00001_kim1_charaget02_Boss_Red
summon_position: （なし）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 20
sequence_group_id: （なし）
```

ゲーム開始から1秒後に出現。出現位置の指定なし（デフォルト位置）。HP倍率は 20。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド |
|-----------|---------|-----------|
| Gust | 1 | Player |

---

### event_kim1_charaget02_00006（イベント）

#### このステージでの役割

charaget02 系イベントの第2ステージ。ゲーム開始から3秒後（ElapsedTime: 3000ms）に出現し、前ステージより遅延した登場タイミングでバランスが取られている。HP22倍率とやや強化。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_kim_00001_kim1_charaget02_Boss_Red` | Boss | Defense | Red | 10,000 | 100 | 40 | 0.18 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_kim_00001_kim1_charaget02_Boss_Red` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 65 |
| Special | 0 | なし | なし | 125 |

> event_kim1_charaget02_00005 と同パラメータIDのため攻撃パターンは同一。

#### シーケンス設定

```
sequence_element_id: 1
condition_type: ElapsedTime
condition_value: 3000
action_type: SummonEnemy
action_value: c_kim_00001_kim1_charaget02_Boss_Red
summon_position: （なし）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 22
sequence_group_id: （なし）
```

ゲーム開始から3秒後に出現。HP倍率 22 とわずかに前ステージより強化。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド |
|-----------|---------|-----------|
| Gust | 2 | Player |

---

### event_kim1_savage_00003（イベント）

#### このステージでの役割

savage 系イベントの高難易度ステージ。HP50,000・攻撃力300の最高強度パラメータが使用されており、ゲーム開始1.6秒後の初期登場とフレンドユニット6体撃破後の再出現（ウェーブ切替）の2段構えで登場する。AttackPowerDown を多用するコマ構成と組み合わさった、圧倒的な難易度設計が特徴。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_kim_00001_kim1_savage03_Boss_Red` | Boss | Defense | Red | 50,000 | 300 | 40 | 0.18 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_kim_00001_kim1_savage03_Boss_Red` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 65 |
| Special | 0 | なし | なし | 125 |

**Normal 攻撃詳細（savage03 専用）:**

| sort_order | attack_type | 射程(range_end_parameter) | max_target_count | damage_type | hit_type | power_parameter |
|-----------|------------|--------------------------|-----------------|-------------|---------|----------------|
| 1 | Direct | 0.19 | 1 | Damage | Normal | 100.0% |

> savage03 の Normal 攻撃は max_target_count=1（単体攻撃）と他バリアントと異なる設計。

**Special 攻撃詳細（MstAttackElement）:**

| sort_order | attack_type | 射程(range_end_parameter) | max_target_count | damage_type | hit_type | power_parameter | effect_type | effective_duration | effect_parameter |
|-----------|------------|--------------------------|-----------------|-------------|---------|----------------|------------|-------------------|-----------------|
| 1 | Direct | 0.3 | 100 | Damage | Normal | 20.0% | None | - | - |
| 2 | Direct | 0.3 | 100 | Damage | Normal | 20.0% | None | - | - |
| 3 | Direct | 0.3 | 100 | Damage | Normal | 20.0% | None | - | - |
| 4 | Direct | 0.3 | 100 | Damage | Normal | 20.0% | None | - | - |
| 5 | Direct | 0.3 | 100 | Damage | Normal | 20.0% | None | - | - |
| 6 | Direct | 0.3 | 100 | Damage（Self） | Normal | 0.0% | DamageCut | 1000ms | 30 |

> Special は5連続ダメージ（各20%）＋最終段でDamageCut（自己）30%付与。他バリアントとは異なるセルフバフ型の設計。

#### シーケンス設定

**初期登場（ElapsedTime）:**
```
sequence_element_id: 3
condition_type: ElapsedTime
condition_value: 1600
action_type: SummonEnemy
action_value: c_kim_00001_kim1_savage03_Boss_Red
summon_position: （なし）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 10
sequence_group_id: （なし）
```

**ウェーブ再登場（FriendUnitDead・ウェーブ w1）:**
```
sequence_element_id: 10
condition_type: FriendUnitDead
condition_value: 6
action_type: SummonEnemy
action_value: c_kim_00001_kim1_savage03_Boss_Red
summon_position: （なし）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 18
sequence_group_id: w1
```

ゲーム開始1.6秒後とウェーブ切替後の2段構え。2回目は HP倍率 18 に強化。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド |
|-----------|---------|-----------|
| AttackPowerDown | 5 | Player |

---

### raid_kim1_00001（降臨バトル）

#### このステージでの役割

降臨バトル（raid）の主要ボスとして、マルチウェーブ構成（w1〜w5）の中に組み込まれている。HP50,000・Technical ロールで登場し、複数ウェーブにわたって繰り返し出現するエンドコンテンツ向けの高強度設計。HP倍率 20〜30 と非常に高い。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_kim_00001_kim1_advent_Boss_Red` | Boss | Technical | Red | 10,000 | 100 | 35 | 0.21 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_kim_00001_kim1_advent_Boss_Red` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 65 |
| Special | 0 | なし | なし | 125 |

**Special 攻撃詳細（MstAttackElement）:**

| sort_order | attack_type | 射程(range_end_parameter) | max_target_count | damage_type | hit_type | power_parameter | effect_type | effective_duration | effect_parameter |
|-----------|------------|--------------------------|-----------------|-------------|---------|----------------|------------|-------------------|-----------------|
| 1 | Direct | 0.52 | 100 | Damage | Normal | 2.0% | None | - | - |
| 2 | Direct | 0.52 | 100 | Damage | Normal | 5.0% | None | - | - |
| 3 | Direct | 0.52 | 100 | Damage | Normal | 8.0% | None | - | - |
| 4 | Direct | 0.52 | 100 | Damage | Normal | 25.0% | None | - | - |
| 5 | Direct | 0.52 | 100 | Damage | KnockBack1 | 60.0% | AttackPowerDown | 1000ms | 20 |

> advent 系 Special は challenge 系と同じく最終段が KnockBack1 + AttackPowerDown。

#### シーケンス設定

**ウェーブ w2 での登場（ElapsedTimeSinceSequenceGroupActivated）:**
```
sequence_element_id: 12
condition_type: ElapsedTimeSinceSequenceGroupActivated
condition_value: 50
action_type: SummonEnemy
action_value: c_kim_00001_kim1_advent_Boss_Red
summon_position: （なし）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 20
sequence_group_id: w2
```

**ウェーブ w3 での登場（ElapsedTimeSinceSequenceGroupActivated）:**
```
sequence_element_id: 19
condition_type: ElapsedTimeSinceSequenceGroupActivated
condition_value: 0
action_type: SummonEnemy
action_value: c_kim_00001_kim1_advent_Boss_Red
summon_position: （なし）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 30
sequence_group_id: w3
```

マルチウェーブ構成（w1〜w5）で、w2（50ms後）と w3（即時）に登場。HP倍率はそれぞれ 20・30 と高い。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド |
|-----------|---------|-----------|
| Gust | 2 | Player |
| Poison | 1 | Player |
| SlipDamage | 1 | Player |

---

## 4. パラメータバリエーション一覧（全件）

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 | 変身条件 |
|------------|------|------|-------|-----|--------|---------|---------|------------|--------|
| `c_kim_00001_kim1_advent_Boss_Red` | Boss | Technical | Red | 10,000 | 100 | 35 | 0.21 | 1 | None |
| `c_kim_00001_kim1_challenge_Boss_Red` | Boss | Defense | Red | 50,000 | 100 | 40 | 0.18 | 2 | None |
| `c_kim_00001_kim1_charaget02_Boss_Red` | Boss | Defense | Red | 10,000 | 100 | 40 | 0.18 | 1 | None |
| `c_kim_00001_kim1_savage03_Boss_Red` | Boss | Defense | Red | 50,000 | 300 | 40 | 0.18 | 2 | None |

**ステータス統計（Boss のみ）:**

| 指標 | HP | 攻撃力 | 移動速度 |
|-----|-----|--------|---------|
| 最小 | 10,000 | 100 | 35 |
| 最大 | 50,000 | 300 | 40 |
| 平均 | 30,000 | 150 | 38.75 |

---

## 5. インゲーム使用実績サマリー

| コンテンツ種別 | ステージ数 |
|-------------|---------|
| イベント | 4 |
| 降臨バトル | 1 |
| **normalクエスト Normal（フィルタ対象）** | **0（実績なし）** |

---

## 6. コマ効果使用実績ランキング（全ステージ集計）

| 順位 | コマ効果種別 | 使用回数 | 主な対象サイド |
|-----|-----------|---------|--------------|
| 1 | Gust | 6 | Player |
| 2 | AttackPowerDown | 5 | Player |
| 3 | Poison | 1 | Player |
| 4 | SlipDamage | 1 | Player |
