# ダグ（chara_you_00201）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: chara_you_00201
> mst_series_id: you
> 作品名: 幼稚園WARS

---

> **コンテンツフィルタ適用**: 本ドキュメントは「normalクエストのNormal難易度のみ」のフィルタを適用して分析しています。
> このキャラクターはメインクエスト（`normal_%`）には登場しないため、フィルタに合致するステージデータは存在しません。
> 参考情報として、他コンテンツ（イベント・降臨バトル）での使用実態を記載します。

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `chara_you_00201` |
| mst_series_id | `you` |
| 作品名 | 幼稚園WARS |
| asset_key | `chara_you_00201` |
| is_phantomized | `1` |

**フレーバーテキスト**: "世界一安全な幼稚園"と言われる「ブラック幼稚園」たんぽぽ組の教諭で、リタの先輩。詐欺師として、誰も信用せず、独りで生きてきたが、リタに命を救われ恋に落ちる。鋭い勘と優しい性格を併せ持ち、園児たちにも好かれている。

---

## 2. キャラクター特徴まとめ

ダグは幼稚園WARSシリーズのイベントおよび降臨バトルで使用されるプレイアブルキャラクター（`c_` プレフィックス）で、**メインクエスト（normalクエスト）への登場実績はない**。

全7バリエーションのうち、Normal kindが2種・Boss kindが5種で、ボス役として登場するケースが主体。role_typeはTechnical（4種）とAttack（2種＋Boss）の混在。HPはすべて10,000で固定されており、HP係数によって実効HPが調整される設計。攻撃力は100（Technical系）または500（challenge・savage Attack系）に分かれる。移動速度は32〜35と全バリエーション共通の低速型（幼稚園WARS雑魚より遅い）。索敵距離は0.36〜0.6。

変身設定・アビリティは全バリエーションで設定なし。charaget01（Yellow/Technical）とadvent（Green/Technical）はForcedKnockBack5の登場演出を持ち、出現時に全フィールドのプレイヤーユニットを強制ノックバックする。advent系のSpecial攻撃にはAttackPowerDown（-1/300フレーム/10%効果）が付与されており、challenge Normal Greenの通常攻撃にはKnockBack1＋Poison（-1/600フレーム）が付く特殊仕様。

コマ効果の傾向としては、AttackPowerDown（8回）が最頻出で、次いでPoison（5回）がランクイン。いずれもPlayerサイド狙いで設計されており、特にcharaget01シリーズとraid降臨バトルで積極的に妨害コマが組まれている。

---

## 3. ステージ別使用実態

### event_you1_challenge_00001（イベント）

#### このステージでの役割

幼稚園WARS「challenge」イベントの1ステージ目。InitialSummonでフィールド奥（position 1.7）に配置されるメインボス役。HP係数11.9により実効HP119,000相当の強敵として登場し、1体撃破後にウェーブ切り替え（w1グループ）が発動する仕掛けになっている。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_you_00201_you1_challenge_Boss_Green` | Boss | Technical | Green | 10,000 | 500 | 32 | 0.45 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_you_00201_you1_challenge_Boss_Green` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 84 |
| Special | 0 | なし | なし | 159 |

**攻撃エレメント詳細**

| attack_kind | attack_type | range_end | max_target_count | damage_type | hit_type | power_parameter | effect_type |
|------------|-------------|-----------|-----------------|-------------|----------|-----------------|-------------|
| Appearance | Direct | Distance 50.0 | 100 | None | Normal | Percentage 100 | None |
| Normal | Direct | Distance 0.5 | 1 | Damage | Normal | Percentage 100 | None |
| Special（1〜4hit） | Direct | Distance 0.5 | 1 | Damage | Normal | Percentage 85 ×4 | None |

> Appearance は Normal（ノックバックなし）。challenge系のSpecialは4ヒット×85%の近距離多段攻撃。

#### シーケンス設定

```
condition_type: InitialSummon
condition_value: 0
sequence_element_id: 1
action_type: SummonEnemy
action_value: c_you_00201_you1_challenge_Boss_Green
summon_position: 1.7
summon_count: 1
summon_interval: 0
enemy_hp_coef: 11.9
sequence_group_id: （なし）
```

ステージ開始時に1体だけ最前線近くに配置。1体撃破でグループw1に切り替わる構成。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| コマ効果なし | - | - |

---

### event_you1_challenge_00002（イベント）

#### このステージでの役割

challengeイベントの2ステージ目。ElapsedTime 250ms後に1体登場するシンプルなボス出現構成。同時にc_you_00301も250ms後に登場する2ボス同時出現設計で、OutpostDamage 5,000ごとに雑魚が増援として送られる持久戦型。HP係数7（実効HP70,000相当）。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_you_00201_you1_challenge_Boss_Green` | Boss | Technical | Green | 10,000 | 500 | 32 | 0.45 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_you_00201_you1_challenge_Boss_Green` | なし | None | なし | なし |

#### 攻撃パターン

前ステージ（event_you1_challenge_00001）と同一パラメータのため攻撃パターンも同一。Appearance（Normal/50F）・Normal（単体/84F）・Special（4hit×85%/159F）の3段階構成。

#### シーケンス設定

```
condition_type: ElapsedTime
condition_value: 250
sequence_element_id: 1
action_type: SummonEnemy
action_value: c_you_00201_you1_challenge_Boss_Green
summon_count: 1
summon_interval: 0
enemy_hp_coef: 7
sequence_group_id: （なし）
```

開始250ms後に1体出現（HP係数7、実効HP70,000）。c_you_00301と同時刻に登場する2ボス構成。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| コマ効果なし | - | - |

---

### event_you1_challenge_00004（イベント）

#### このステージでの役割

challengeイベントの4ステージ目（グループ切り替え方式の多ウェーブ構成）。ダグはw2グループ内のElapsedTimeSinceSequenceGroupActivated 75秒後に出現するNormal kindバリエーション（HP係数34、実効HP340,000）。c_you_00001のBoss Greenとほぼ同時（5秒後）に登場し、同じグループ内で連携する。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_you_00201_you1_challenge_Normal_Green` | Normal | Technical | Green | 10,000 | 500 | 32 | 0.45 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_you_00201_you1_challenge_Normal_Green` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 84 |

**攻撃エレメント詳細**

| attack_type | range_end | max_target_count | damage_type | hit_type | power_parameter | effect_type | effective_duration | effect_parameter |
|-------------|-----------|-----------------|-------------|----------|-----------------|-------------|------------------|-----------------|
| Direct | Distance 0.5 | 1 | Damage | KnockBack1 | Percentage 100 | Poison | 600フレーム | 10 |

> challenge Normal Greenの通常攻撃はKnockBack1＋Poison（600フレーム/値10）付き。攻撃ごとに毒を付与する特殊仕様。Appearance・Specialなし（Normal kindのみ）。

#### シーケンス設定

```
condition_type: ElapsedTimeSinceSequenceGroupActivated
condition_value: 75
sequence_element_id: 21
action_type: SummonEnemy
action_value: c_you_00201_you1_challenge_Normal_Green
summon_count: 1
summon_interval: 0
enemy_hp_coef: 34
sequence_group_id: w2
```

w2グループ起動から75秒後に出現（HP係数34、実効HP340,000）。グループ内では最後尾の出現タイミングにあたる。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| コマ効果なし | - | - |

---

### event_you1_charaget01_00001（イベント）

#### このステージでの役割

幼稚園WARS「キャラゲット01」イベントの1ステージ目。ForcedKnockBack5（登場演出）を持つBoss Yellowパラメータが初登場するステージ。InitialSummonで位置1.7に配置され、登場時に全フィールドへの強制ノックバックが発動する。HP係数1.5（実効HP15,000）と序盤は控えめな強さ。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_you_00201_you1_charaget01_Boss_Yellow` | Boss | Technical | Yellow | 10,000 | 100 | 35 | 0.36 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_you_00201_you1_charaget01_Boss_Yellow` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 83 |
| Special | 0 | なし | なし | 158 |

**攻撃エレメント詳細**

| attack_kind | attack_type | range_end | max_target_count | damage_type | hit_type | power_parameter | effect_type |
|------------|-------------|-----------|-----------------|-------------|----------|-----------------|-------------|
| Appearance | Direct | Distance 50.0 | 100 | None | ForcedKnockBack5 | Percentage 100 | None |
| Normal | Direct | Distance 0.39 | 1 | Damage | Normal | Percentage 100 | None |
| Special（1〜3hit） | Direct | Distance 0.47 | 1 | Damage | Normal | Percentage 50 | None |
| Special（4hit目） | Direct | Distance 0.47 | 1 | Damage | Stun | Percentage 50 | None |

> Appearance時にForcedKnockBack5を全フィールドに発動。Specialの4hit目はStun（スタン）付き。

#### シーケンス設定

```
condition_type: InitialSummon
condition_value: 0
sequence_element_id: 1
action_type: SummonEnemy
action_value: c_you_00201_you1_charaget01_Boss_Yellow
summon_position: 1.7
summon_count: 1
summon_interval: 0
enemy_hp_coef: 1.5
sequence_group_id: （なし）
```

開始時にposition 1.7に配置（HP係数1.5、実効HP15,000）。序盤ステージのため控えめな設定。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| コマ効果なし | - | - |

---

### event_you1_charaget01_00002（イベント）

#### このステージでの役割

キャラゲット01の2ステージ目。OutpostHpPercentage 99%トリガー（ほぼ即時）でBoss Yellowが出現。HP係数2（実効HP20,000）で前ステージより強化。AttackPowerDownコマが加わりプレイヤー側への妨害圧力が増している。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_you_00201_you1_charaget01_Boss_Yellow` | Boss | Technical | Yellow | 10,000 | 100 | 35 | 0.36 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_you_00201_you1_charaget01_Boss_Yellow` | なし | None | なし | なし |

#### 攻撃パターン

前ステージと同一パラメータのため攻撃パターンも同一。Appearance（ForcedKnockBack5）・Normal（50F/100%）・Special（4hit、3×50%通常＋1Stun）の3段階構成。

#### シーケンス設定

```
condition_type: OutpostHpPercentage
condition_value: 99
sequence_element_id: 1
action_type: SummonEnemy
action_value: c_you_00201_you1_charaget01_Boss_Yellow
summon_count: 1
summon_interval: 1
enemy_hp_coef: 2
sequence_group_id: （なし）
```

アウトポストHP99%（ほぼ開始直後）のトリガーで1体出現。HP係数2（実効HP20,000）。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| AttackPowerDown | 1 | Player |

---

### event_you1_charaget01_00003（イベント）

#### このステージでの役割

キャラゲット01の3ステージ目。引き続きOutpostHpPercentage 99%トリガー（interval 50ms）でBoss Yellowが出現。HP係数4（実効HP40,000）とさらに強化。コマ2にAttackPowerDownが追加。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_you_00201_you1_charaget01_Boss_Yellow` | Boss | Technical | Yellow | 10,000 | 100 | 35 | 0.36 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_you_00201_you1_charaget01_Boss_Yellow` | なし | None | なし | なし |

#### 攻撃パターン

前ステージと同一パラメータのため攻撃パターンも同一。

#### シーケンス設定

```
condition_type: OutpostHpPercentage
condition_value: 99
sequence_element_id: 1
action_type: SummonEnemy
action_value: c_you_00201_you1_charaget01_Boss_Yellow
summon_count: 1
summon_interval: 50
enemy_hp_coef: 4
sequence_group_id: （なし）
```

HP係数4（実効HP40,000）。OutpostHpPercentage 99%トリガーの50ms間隔出現。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| AttackPowerDown | 1 | Player |

---

### event_you1_charaget01_00005（イベント）

#### このステージでの役割

キャラゲット01の5ステージ目。ElapsedTime 3,000ms後（3秒）にBoss Yellowが1体出現。HP係数8.5（実効HP85,000）で中盤の強敵として機能。コマ1・コマ2の両方にAttackPowerDownが組まれており、強い妨害圧力を持つ構成。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_you_00201_you1_charaget01_Boss_Yellow` | Boss | Technical | Yellow | 10,000 | 100 | 35 | 0.36 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_you_00201_you1_charaget01_Boss_Yellow` | なし | None | なし | なし |

#### 攻撃パターン

前ステージと同一パラメータのため攻撃パターンも同一。

#### シーケンス設定

```
condition_type: ElapsedTime
condition_value: 3000
sequence_element_id: 2
action_type: SummonEnemy
action_value: c_you_00201_you1_charaget01_Boss_Yellow
summon_count: 1
summon_interval: 0
enemy_hp_coef: 8.5
sequence_group_id: （なし）
```

開始3,000ms後に出現（HP係数8.5、実効HP85,000）。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| AttackPowerDown | 2 | Player |

---

### event_you1_charaget01_00006（イベント）

#### このステージでの役割

キャラゲット01の最終（6ステージ）ステージ。ElapsedTime 3,000ms後にBoss Yellowが出現。HP係数7（実効HP70,000）。コマ1に2行のAttackPowerDownが設定された妨害重視の最終ステージ。コマ4スロットにはNone以外のeffect_typeは含まれないが3行のコマラインを持つ構成。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_you_00201_you1_charaget01_Boss_Yellow` | Boss | Technical | Yellow | 10,000 | 100 | 35 | 0.36 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_you_00201_you1_charaget01_Boss_Yellow` | なし | None | なし | なし |

#### 攻撃パターン

前ステージと同一パラメータのため攻撃パターンも同一。

#### シーケンス設定

```
condition_type: ElapsedTime
condition_value: 3000
sequence_element_id: 2
action_type: SummonEnemy
action_value: c_you_00201_you1_charaget01_Boss_Yellow
summon_count: 1
summon_interval: 0
enemy_hp_coef: 7
sequence_group_id: （なし）
```

HP係数7（実効HP70,000）。最終ステージとして強めの妨害コマとの組み合わせで難度を担保。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| AttackPowerDown | 2 | Player |

---

### event_you1_charaget02_00006（イベント）

#### このステージでの役割

幼稚園WARS「キャラゲット02」イベントの6ステージ目（最終ステージ）。w1グループのElapsedTimeSinceSequenceGroupActivated 800ms後にBoss Red（Attack/HP10,000）が出現。HP係数13（実効HP130,000）の高耐久ボスとして登場し、3〜4ターゲット対象のSpecial攻撃を持つ。コマ効果はなし。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_you_00201_you1_charaget02_Boss_Red` | Boss | Attack | Red | 10,000 | 500 | 32 | 0.6 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_you_00201_you1_charaget02_Boss_Red` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 84 |
| Special | 0 | なし | なし | 159 |

**攻撃エレメント詳細**

| attack_kind | attack_type | range_end | max_target_count | damage_type | hit_type | power_parameter | effect_type |
|------------|-------------|-----------|-----------------|-------------|----------|-----------------|-------------|
| Appearance | Direct | Distance 50.0 | 100 | None | ForcedKnockBack5 | Percentage 100 | None |
| Normal | Direct | Distance 0.62 | 1 | Damage | Normal | Percentage 100 | None |
| Special（1〜4hit） | Direct | Distance 0.62 | 3 | Damage | Normal | Percentage 100 ×4 | None |

> Redバリエーションはindexed距離0.62と長リーチ。SpecialはMax3ターゲット×4hit（各100%）の多体攻撃で、Technicalバリエーションより大幅に高火力。Appearance時にはForcedKnockBack5。

#### シーケンス設定

```
condition_type: ElapsedTimeSinceSequenceGroupActivated
condition_value: 800
sequence_element_id: w1_2
action_type: SummonEnemy
action_value: c_you_00201_you1_charaget02_Boss_Red
summon_count: 1
summon_interval: 0
enemy_hp_coef: 13
sequence_group_id: w1
```

w1グループ起動から800ms後に出現（HP係数13、実効HP130,000）。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| コマ効果なし | - | - |

---

### event_you1_savage_00001（イベント）

#### このステージでの役割

幼稚園WARS「savage（強敵）」イベントの1ステージ目。Boss Green（Attack）が2回出現する構成。InitialSummon 1でposition 1.45に配置（HP係数3.8、実効HP38,000）され、4体撃破後（FriendUnitDead 4）に2体目（HP係数4.7、実効HP47,000）が出現する。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_you_00201_you1_savage01_Boss_Green` | Boss | Attack | Green | 50,000 | 500 | 32 | 0.45 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_you_00201_you1_savage01_Boss_Green` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 83 |
| Special | 0 | なし | なし | 158 |

**攻撃エレメント詳細**

| attack_kind | attack_type | range_end | max_target_count | damage_type | hit_type | power_parameter | effect_type |
|------------|-------------|-----------|-----------------|-------------|----------|-----------------|-------------|
| Appearance | Direct | Distance 50.0 | 100 | Damage | Normal | Percentage 100 | None |
| Normal | Direct | Distance 0.5 | 1 | Damage | Normal | Percentage 100 | None |
| Special（1〜4hit） | Direct | Distance 0.5 | 1 | Damage | Normal | Percentage 85 ×4 | None |

> savage Boss GreenのAppearanceはDamage（Normal）付き（他のBossとは異なる）。BaseHP50,000と他バリエーションの5倍のHP設定で、savage専用の高耐久設計。Specialは challenge系と同様の4hit×85%。

#### シーケンス設定

```
condition_type: InitialSummon
condition_value: 1
sequence_element_id: 1
action_type: SummonEnemy
action_value: c_you_00201_you1_savage01_Boss_Green
summon_position: 1.45
summon_count: 1
summon_interval: 0
enemy_hp_coef: 3.8
sequence_group_id: （なし）
```

```
condition_type: FriendUnitDead
condition_value: 4
sequence_element_id: 8
action_type: SummonEnemy
action_value: c_you_00201_you1_savage01_Boss_Green
summon_count: 1
summon_interval: 0
enemy_hp_coef: 4.7
sequence_group_id: （なし）
```

InitialSummon（HP係数3.8）と4体撃破後のFriendUnitDead（HP係数4.7）の2波出現構成。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| コマ効果なし | - | - |

---

### event_you1_savage_00003（イベント）

#### このステージでの役割

savageイベントの3ステージ目（最終）。EnterTargetKomaIndex 4（コマ4到達時）のトリガーでposition 1.55にBoss Greenが登場（HP係数10、実効HP500,000）。コマ1・コマ2にPoisonが各2行組まれた強毒ステージで、このキャラクターはコマ4到達という特殊トリガーで出現する点が特徴。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_you_00201_you1_savage01_Boss_Green` | Boss | Attack | Green | 50,000 | 500 | 32 | 0.45 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_you_00201_you1_savage01_Boss_Green` | なし | None | なし | なし |

#### 攻撃パターン

前ステージと同一パラメータのため攻撃パターンも同一。Appearance（Damage/Normal）・Normal（単体/83F）・Special（4hit×85%/158F）の3段階構成。

#### シーケンス設定

```
condition_type: EnterTargetKomaIndex
condition_value: 4
sequence_element_id: 4
action_type: SummonEnemy
action_value: c_you_00201_you1_savage01_Boss_Green
summon_position: 1.55
summon_count: 1
summon_interval: 0
enemy_hp_coef: 10
sequence_group_id: （なし）
```

コマ4到達をトリガーに出現（HP係数10、実効HP500,000）。EnterTargetKomaIndexを使った珍しい条件付き出現設計。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| Poison | 4 | Player |

---

### raid_you1_00001（降臨バトル）

#### このステージでの役割

幼稚園WARS降臨バトル（7ウェーブ構成）。ダグは2つのバリエーションで登場する。w1グループの150ms後にBoss Green（HP係数8、実効HP80,000）が出現し、w5グループでは250ms後にNormal Green（HP係数400、実効HP400万）が2回（FriendUnitDead引き継ぎで合計2体）出現するウェーブ5のキー敵として機能。コマにはAttackPowerDownとPoisonが混在する高難度降臨バトル。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_you_00201_you1_advent_Boss_Green` | Boss | Technical | Green | 10,000 | 100 | 32 | 0.36 | 1 |
| `c_you_00201_you1_advent_Normal_Green` | Normal | Technical | Green | 1,000 | 100 | 32 | 0.36 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_you_00201_you1_advent_Boss_Green` | なし | None | なし | なし |
| `c_you_00201_you1_advent_Normal_Green` | なし | None | なし | なし |

#### 攻撃パターン

| mst_unit_id（略） | attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|----------------|------------|-----------|--------------|------------------|--------------|
| advent_Boss_Green | Appearance | 0 | なし | なし | 50 |
| advent_Boss_Green | Normal | 0 | なし | なし | 84 |
| advent_Boss_Green | Special | 0 | なし | なし | 159 |
| advent_Normal_Green | Normal | 0 | なし | なし | 84 |
| advent_Normal_Green | Special | 0 | なし | なし | 159 |

**攻撃エレメント詳細**

| mst_unit_id（略） | attack_kind | attack_type | range_end | max_target_count | damage_type | hit_type | power_parameter | effect_type | effective_duration | effect_parameter |
|----------------|------------|-------------|-----------|-----------------|-------------|----------|-----------------|-------------|------------------|-----------------|
| advent_Boss_Green | Appearance | Direct | Distance 50.0 | 100 | None | ForcedKnockBack5 | Percentage 100 | None | - | - |
| advent_Boss_Green | Normal | Direct | Distance 0.37 | 1 | Damage | Normal | Percentage 100 | None | - | - |
| advent_Boss_Green | Special（1〜3hit） | Direct | Distance 0.47 | 100 | Damage | Normal | Percentage 23 | AttackPowerDown（1hit目のみ） | 300フレーム | 10 |
| advent_Boss_Green | Special（4hit目） | Direct | Distance 0.47 | 100 | Damage | Normal | Percentage 25 | None | - | - |
| advent_Normal_Green | Normal | Direct | Distance 0.37 | 1 | Damage | Normal | Percentage 100 | None | - | - |
| advent_Normal_Green | Special（1〜3hit） | Direct | Distance 0.47 | 100 | Damage | Normal | Percentage 23 | AttackPowerDown（1hit目のみ） | 300フレーム | 10 |
| advent_Normal_Green | Special（4hit目） | Direct | Distance 0.47 | 100 | Damage | Normal | Percentage 25 | None | - | - |

> Boss GreenのAppearanceはForcedKnockBack5。SpecialはMax100ターゲット対象の広域4hit攻撃で、1hit目にAttackPowerDown（300F/10%低下）を付与。Normal GreenはAppearanceなしだがSpecialは同仕様（広域攻撃＋攻撃力低下）。

#### シーケンス設定（ダグ登場分のみ）

```
condition_type: ElapsedTimeSinceSequenceGroupActivated
condition_value: 150
sequence_element_id: 6
action_type: SummonEnemy
action_value: c_you_00201_you1_advent_Boss_Green
summon_count: 1
summon_interval: 0
enemy_hp_coef: 8
sequence_group_id: w1
```

```
condition_type: ElapsedTimeSinceSequenceGroupActivated
condition_value: 250
sequence_element_id: 27
action_type: SummonEnemy
action_value: c_you_00201_you1_advent_Normal_Green
summon_count: 1
summon_interval: 0
enemy_hp_coef: 400
sequence_group_id: w5
```

```
condition_type: FriendUnitDead
condition_value: 27
sequence_element_id: 28
action_type: SummonEnemy
action_value: c_you_00201_you1_advent_Normal_Green
summon_count: 1
summon_interval: 0
enemy_hp_coef: 400
sequence_group_id: w5
```

w1ではBoss Green（HP係数8、実効HP80,000）が150ms後に出現。w5ではNormal Green（HP係数400、実効HP400万）が2回出現する超高耐久ウェーブ設計（降臨バトル後半の難関ウェーブ）。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| AttackPowerDown | 2 | Player |
| Poison | 1 | Player |

---

## 4. パラメータバリエーション一覧（全件）

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 | 主な使用コンテンツ |
|------------|------|------|-------|-----|--------|---------|---------|--------------|--------------|
| `c_you_00201_you1_advent_Boss_Green` | Boss | Technical | Green | 10,000 | 100 | 32 | 0.36 | 1 | 降臨バトル（w1） |
| `c_you_00201_you1_advent_Normal_Green` | Normal | Technical | Green | 1,000 | 100 | 32 | 0.36 | 1 | 降臨バトル（w5） |
| `c_you_00201_you1_challenge_Boss_Green` | Boss | Technical | Green | 10,000 | 500 | 32 | 0.45 | 2 | イベント（challenge 1〜2） |
| `c_you_00201_you1_challenge_Normal_Green` | Normal | Technical | Green | 10,000 | 500 | 32 | 0.45 | 2 | イベント（challenge 4） |
| `c_you_00201_you1_charaget01_Boss_Yellow` | Boss | Technical | Yellow | 10,000 | 100 | 35 | 0.36 | 2 | イベント（charaget01 1〜6） |
| `c_you_00201_you1_charaget02_Boss_Red` | Boss | Attack | Red | 10,000 | 500 | 32 | 0.6 | 2 | イベント（charaget02 6） |
| `c_you_00201_you1_savage01_Boss_Green` | Boss | Attack | Green | 50,000 | 500 | 32 | 0.45 | 2 | イベント（savage 1・3） |

---

## 5. コンテンツ別登場実績サマリー

| コンテンツ種別 | ステージ数 | 主な使用パラメータ |
|--------------|---------|----------------|
| イベント | 12ステージ | challenge_Boss_Green / challenge_Normal_Green / charaget01_Boss_Yellow / charaget02_Boss_Red / savage01_Boss_Green |
| 降臨バトル | 1ステージ | advent_Boss_Green / advent_Normal_Green |
| メインクエスト Normal | **0ステージ（登場なし）** | - |

> **備考**: このキャラクターはメインクエスト（`normal_%`）には一切登場していません。指定されたフィルタ（normalクエストのNormal難易度）に合致するデータは存在しません。
