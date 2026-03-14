# 不良系金髪イケメン（enemy_you_00001）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: enemy_you_00001
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
| id | `enemy_you_00001` |
| mst_series_id | `you` |
| 作品名 | 幼稚園WARS |
| asset_key | `enemy_you_00001` |
| is_phantomized | `0` |

**フレーバーテキスト**: 「ブラック幼稚園」に送られてきた暗殺者。ラーメンはチャーシューから食べる派。

---

## 2. キャラクター特徴まとめ

不良系金髪イケメンはイベントおよび降臨バトルで使用される幼稚園WARSの敵ユニットで、**メインクエスト（normalクエスト）への登場実績はない**。

全7バリエーションのうち、Normal kindが6種・Boss kindが1種。role_typeはAttack（4種）とDefense（3種）の混在であり、コンテンツや難易度に応じて使い分けられている。HPは1,000〜10,000の幅があり、イベント序盤ステージでは1,000の軽量版が、savage（強敵イベント）や降臨バトルでは10,000の高耐久版が使われる。移動速度はすべて37で固定。

変身設定・アビリティは全バリエーションで設定なし。savage系パラメータのうち `savage01_02` のみ攻撃力が80（他は100）で毒効果を付与する特殊仕様になっている。

コマ効果の傾向としては、AttackPowerDown（プレイヤー側への攻撃力低下）が最頻出（9回）、次いでPoison（毒、8回）が多く、いずれも Player サイド狙いで設計されている。これはsavageステージや降臨バトルで意図的な妨害コマとして組まれていることを示す。

---

## 3. ステージ別使用実態

### event_you1_charaget01_00001（イベント）

#### このステージでの役割

幼稚園WARS「キャラゲット01」イベントの最初のステージ。このキャラは時間経過（500ms後）から大量召喚（5体・1,500ms間隔）される主力雑魚役で、序盤の量産要員としての位置づけ。HP1,000の軽量版が使われており、難易度設計上は低難易度。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_you_00001_you1_charaget01_Normal_Colorless` | Normal | Attack | Colorless | 1,000 | 100 | 37 | 0.2 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_you_00001_you1_charaget01_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 89 |

**攻撃エレメント詳細**

| attack_type | range_start | range_end | max_target_count | damage_type | hit_type | power_parameter | effect_type |
|-------------|-------------|-----------|-----------------|-------------|----------|-----------------|-------------|
| Direct | Distance 0 | Distance 0.25 | 1 | Damage | Normal | Percentage 100 | None |

#### シーケンス設定

```
condition_type: ElapsedTime
condition_value: 500
sequence_element_id: 2
action_type: SummonEnemy
action_value: e_you_00001_you1_charaget01_Normal_Colorless
summon_position: （未指定）
summon_count: 5
summon_interval: 1500
enemy_hp_coef: 5
sequence_group_id: （なし）
```

開始500ms後から5体を1,500ms間隔で継続召喚。HP係数5は基準の5倍HPで登場するため、実効HPは5,000相当。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| None のみ | 0 | - |

> このステージはコマ効果なし（None）のみのシンプルな構成。

---

### event_you1_charaget01_00002（イベント）

#### このステージでの役割

キャラゲット01イベントの2ステージ目。前ステージと同じAttack/Colorlessパラメータを使用するが、コマにAttackPowerDownが追加されており、プレイヤーへの妨害要素が増している。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_you_00001_you1_charaget01_Normal_Colorless` | Normal | Attack | Colorless | 1,000 | 100 | 37 | 0.2 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_you_00001_you1_charaget01_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 89 |

**攻撃エレメント詳細**

| attack_type | range_start | range_end | max_target_count | damage_type | hit_type | power_parameter | effect_type |
|-------------|-------------|-----------|-----------------|-------------|----------|-----------------|-------------|
| Direct | Distance 0 | Distance 0.25 | 1 | Damage | Normal | Percentage 100 | None |

#### シーケンス設定

```
condition_type: ElapsedTime
condition_value: 500
sequence_element_id: 2
action_type: SummonEnemy
action_value: e_you_00001_you1_charaget01_Normal_Colorless
summon_count: 5
summon_interval: 1500
enemy_hp_coef: 5
sequence_group_id: （なし）
```

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| AttackPowerDown | 1 | Player |

---

### event_you1_charaget01_00003（イベント）

#### このステージでの役割

キャラゲット01の3ステージ目。コマ2にAttackPowerDownが設定されており、引き続きプレイヤーへの攻撃力低下妨害が組み込まれている。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_you_00001_you1_charaget01_Normal_Colorless` | Normal | Attack | Colorless | 1,000 | 100 | 37 | 0.2 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_you_00001_you1_charaget01_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 89 |

**攻撃エレメント詳細**

| attack_type | range_start | range_end | max_target_count | damage_type | hit_type | power_parameter | effect_type |
|-------------|-------------|-----------|-----------------|-------------|----------|-----------------|-------------|
| Direct | Distance 0 | Distance 0.25 | 1 | Damage | Normal | Percentage 100 | None |

#### シーケンス設定

前ステージと同様のElapsedTime起動パターン（詳細は略）。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| AttackPowerDown | 1 | Player |

---

### event_you1_charaget01_00004（イベント）

#### このステージでの役割

キャラゲット01の4ステージ目。コマ1にAttackPowerDownが追加。同時出現数が増加し難易度が上がる。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_you_00001_you1_charaget01_Normal_Colorless` | Normal | Attack | Colorless | 1,000 | 100 | 37 | 0.2 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_you_00001_you1_charaget01_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 89 |

**攻撃エレメント詳細**

| attack_type | range_start | range_end | max_target_count | damage_type | hit_type | power_parameter | effect_type |
|-------------|-------------|-----------|-----------------|-------------|----------|-----------------|-------------|
| Direct | Distance 0 | Distance 0.25 | 1 | Damage | Normal | Percentage 100 | None |

#### シーケンス設定

FriendUnitDeadトリガー（前キャラ撃破後）を含む複合トリガー構成。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| AttackPowerDown | 1 | Player |

---

### event_you1_charaget01_00005（イベント）

#### このステージでの役割

キャラゲット01の5ステージ目。コマ1・コマ2の両方にAttackPowerDownが入る強めの妨害構成。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_you_00001_you1_charaget01_Normal_Colorless` | Normal | Attack | Colorless | 1,000 | 100 | 37 | 0.2 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_you_00001_you1_charaget01_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 89 |

**攻撃エレメント詳細**

| attack_type | range_start | range_end | max_target_count | damage_type | hit_type | power_parameter | effect_type |
|-------------|-------------|-----------|-----------------|-------------|----------|-----------------|-------------|
| Direct | Distance 0 | Distance 0.25 | 1 | Damage | Normal | Percentage 100 | None |

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| AttackPowerDown | 2 | Player |

---

### event_you1_charaget01_00006（イベント）

#### このステージでの役割

キャラゲット01の最終（6ステージ）ステージ。コマ1にAttackPowerDownが設定された最も難易度が高い構成。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_you_00001_you1_charaget01_Normal_Colorless` | Normal | Attack | Colorless | 1,000 | 100 | 37 | 0.2 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_you_00001_you1_charaget01_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 89 |

**攻撃エレメント詳細**

| attack_type | range_start | range_end | max_target_count | damage_type | hit_type | power_parameter | effect_type |
|-------------|-------------|-----------|-----------------|-------------|----------|-----------------|-------------|
| Direct | Distance 0 | Distance 0.25 | 1 | Damage | Normal | Percentage 100 | None |

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| AttackPowerDown | 1 | Player |

---

### event_you1_charaget02_00003（イベント）

#### このステージでの役割

幼稚園WARS「キャラゲット02」イベントの3ステージ目から登場する赤色Defenseパラメータを持つ別バリエーション。HP1,000の雑魚役だが、role_typeがDefenseのため前線で粘り強く戦う性質を持つ。コマ効果はなし。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_you_00001_you1_charaget02_Normal_Red` | Normal | Defense | Red | 1,000 | 100 | 37 | 0.2 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_you_00001_you1_charaget02_Normal_Red` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 64 |

**攻撃エレメント詳細**

| attack_type | range_start | range_end | max_target_count | damage_type | hit_type | power_parameter | effect_type |
|-------------|-------------|-----------|-----------------|-------------|----------|-----------------|-------------|
| Direct | Distance 0 | Distance 0.22 | 1 | Damage | Normal | Percentage 100 | None |

> charaget01（action_frames=89）と比べてaction_framesが64と短く、攻撃間隔が短め（next_attack_interval=50）の速攻型仕様。

#### シーケンス設定

```
condition_type: ElapsedTime
condition_value: 350
sequence_element_id: 1
action_type: SummonEnemy
action_value: e_you_00001_you1_charaget02_Normal_Red
summon_count: 99（無制限）
summon_interval: 4700
enemy_hp_coef: 26
sequence_group_id: （なし）
```

開始350ms後から最大99体を4,700ms間隔で連続召喚。HP係数26により実効HPは26,000相当の強敵仕様。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| コマ効果なし | - | - |

---

### event_you1_charaget02_00004（イベント）

#### このステージでの役割

キャラゲット02の4ステージ目。redパラメータを継続使用し、複数のElapsedTimeトリガーで召喚波が増加する。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_you_00001_you1_charaget02_Normal_Red` | Normal | Defense | Red | 1,000 | 100 | 37 | 0.2 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_you_00001_you1_charaget02_Normal_Red` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 64 |

**攻撃エレメント詳細**

| attack_type | range_start | range_end | max_target_count | damage_type | hit_type | power_parameter | effect_type |
|-------------|-------------|-----------|-----------------|-------------|----------|-----------------|-------------|
| Direct | Distance 0 | Distance 0.22 | 1 | Damage | Normal | Percentage 100 | None |

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| コマ効果なし | - | - |

---

### event_you1_charaget02_00005（イベント）

#### このステージでの役割

キャラゲット02の5ステージ目。redパラメータを継続使用。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_you_00001_you1_charaget02_Normal_Red` | Normal | Defense | Red | 1,000 | 100 | 37 | 0.2 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_you_00001_you1_charaget02_Normal_Red` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 64 |

**攻撃エレメント詳細**

| attack_type | range_start | range_end | max_target_count | damage_type | hit_type | power_parameter | effect_type |
|-------------|-------------|-----------|-----------------|-------------|----------|-----------------|-------------|
| Direct | Distance 0 | Distance 0.22 | 1 | Damage | Normal | Percentage 100 | None |

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| コマ効果なし | - | - |

---

### event_you1_charaget02_00006（イベント）

#### このステージでの役割

キャラゲット02の最終（6ステージ）ステージ。OutpostHpPercentage70%でボス3体が登場するトリガー切り替え構成の難関ステージ。Redパラメータが大量（99体上限・複数波）召喚される。ボス3体（c_you 系）が登場した後もOutpostHpPercentage30%で再び大量召喚されるため、終盤まで継続的にこのキャラが圧力をかける。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_you_00001_you1_charaget02_Normal_Red` | Normal | Defense | Red | 1,000 | 100 | 37 | 0.2 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_you_00001_you1_charaget02_Normal_Red` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 64 |

**攻撃エレメント詳細**

| attack_type | range_start | range_end | max_target_count | damage_type | hit_type | power_parameter | effect_type |
|-------------|-------------|-----------|-----------------|-------------|----------|-----------------|-------------|
| Direct | Distance 0 | Distance 0.22 | 1 | Damage | Normal | Percentage 100 | None |

#### シーケンス設定（主要エントリ）

```
condition_type: ElapsedTime
condition_value: 350
sequence_element_id: 1
action_type: SummonEnemy
action_value: e_you_00001_you1_charaget02_Normal_Red
summon_count: 99（無制限）
summon_interval: 4700
enemy_hp_coef: 26
sequence_group_id: （なし）
```

```
condition_type: OutpostHpPercentage（HP30%以下でのトリガー）
sequence_element_id: w1_4
action_value: e_you_00001_you1_charaget02_Normal_Red
summon_count: 4
summon_interval: 2000
enemy_hp_coef: 26
sequence_group_id: w1
```

ステージ後半（アウトポストHP30%以下）でも再召喚されるダブルフェーズ構成。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| コマ効果なし | - | - |

---

### event_you1_savage_00001（イベント）

#### このステージでの役割

幼稚園WARS「savage（強敵）」イベントの1ステージ目。HP10,000の高耐久バリエーション（Colorless/Green）が初登場する強敵ステージ。InitialSummonで複数体が初期配置され、ElapsedTimeとFriendUnitDeadで追加召喚が行われる複合構成。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_you_00001_you1_savage01_Normal_Colorless` | Normal | Attack | Colorless | 10,000 | 100 | 37 | 0.2 | 2 |
| `e_you_00001_you1_savage01_Normal_Green` | Normal | Attack | Green | 10,000 | 100 | 37 | 0.2 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_you_00001_you1_savage01_Normal_Colorless` | なし | None | なし | なし |
| `e_you_00001_you1_savage01_Normal_Green` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 89 |

**攻撃エレメント詳細**（Colorless / Green 共通）

| attack_type | range_start | range_end | max_target_count | damage_type | hit_type | power_parameter | effect_type |
|-------------|-------------|-----------|-----------------|-------------|----------|-----------------|-------------|
| Direct | Distance 0 | Distance 0.25 | 1 | Damage | Normal | Percentage 100 | None |

#### シーケンス設定（主要エントリ）

```
condition_type: InitialSummon
condition_value: 1
sequence_element_id: 11
action_type: SummonEnemy
action_value: e_you_00001_you1_savage01_Normal_Green
summon_position: 0.8
summon_count: 1
enemy_hp_coef: 13
sequence_group_id: （なし）
```

```
condition_type: FriendUnitDead
condition_value: 1
sequence_element_id: 5
action_type: SummonEnemy
action_value: e_you_00001_you1_savage01_Normal_Colorless
summon_count: 1
enemy_hp_coef: 10
sequence_group_id: （なし）
```

InitialSummonで2体（Green）が初期配置、FriendUnitDead（1体撃破）でColorlessが召喚される複合設計。HP係数が9〜14と高めで実効HP90,000〜140,000相当の強敵。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| コマ効果なし | - | - |

---

### event_you1_savage_00002（イベント）

#### このステージでの役割

savageイベント2ステージ目。Colorless・Green両バリエーションが引き続き使用される。コマに毒（Poison）が3つ設定されており、プレイヤーへの持続ダメージ妨害が強化されている。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_you_00001_you1_savage01_Normal_Colorless` | Normal | Attack | Colorless | 10,000 | 100 | 37 | 0.2 | 2 |
| `e_you_00001_you1_savage01_Normal_Green` | Normal | Attack | Green | 10,000 | 100 | 37 | 0.2 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_you_00001_you1_savage01_Normal_Colorless` | なし | None | なし | なし |
| `e_you_00001_you1_savage01_Normal_Green` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 89 |

**攻撃エレメント詳細**

| attack_type | range_start | range_end | max_target_count | damage_type | hit_type | power_parameter | effect_type |
|-------------|-------------|-----------|-----------------|-------------|----------|-----------------|-------------|
| Direct | Distance 0 | Distance 0.25 | 1 | Damage | Normal | Percentage 100 | None |

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| Poison | 3 | Player |

---

### event_you1_savage_00003（イベント）

#### このステージでの役割

savageイベント最終（3ステージ）。Colorlessの毒付き特殊バリエーション（`savage01_02`）が初登場する高難度ステージ。攻撃力80・毒効果300フレーム・継続ダメージ3というデバフ付きパラメータと通常Greenパラメータが混在し、コマにもPoisonが4つ設定されている。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_you_00001_you1_savage01_Normal_Green` | Normal | Attack | Green | 10,000 | 100 | 37 | 0.2 | 2 |
| `e_you_00001_you1_savage01_02_Normal_Colorless` | Normal | Attack | Colorless | 10,000 | 80 | 37 | 0.2 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_you_00001_you1_savage01_Normal_Green` | なし | None | なし | なし |
| `e_you_00001_you1_savage01_02_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal（Green）| 0 | なし | なし | 89 |
| Normal（savage01_02）| 0 | なし | なし | 89 |

**攻撃エレメント詳細**

| mst_attack_id | attack_type | range_end | damage_type | hit_type | power_parameter | effect_type | effective_duration | effect_parameter |
|--------------|-------------|-----------|-------------|----------|-----------------|-------------|------------------|-----------------|
| savage01_Normal_Green | Direct | Distance 0.25 | Damage | Normal | Percentage 100 | None | - | - |
| savage01_02_Normal_Colorless | Direct | Distance 0.25 | Damage | Normal | Percentage 80 | Poison | 300フレーム | 3 |

> `savage01_02` は攻撃力80%・毒（Poison / 持続300フレーム / 値3）付きの特殊仕様。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| Poison | 4 | Player |

---

### raid_you1_00001（降臨バトル）

#### このステージでの役割

幼稚園WARS降臨バトル。降臨バトル専用の`advent`パラメータ（Normal/Green・Boss/Colorless）が使用される唯一のコンテンツ。Normal Greenが大量の雑魚として各ウェーブに配置され、ウェーブ6ではBoss Colorlessがボスとして登場する。7ウェーブのグループ切り替え（FriendUnitDead / ElapsedTimeSinceSequenceGroupActivated）で構成された長尺の降臨バトル。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_you_00001_you1_advent_Normal_Green` | Normal | Defense | Green | 1,000 | 100 | 37 | 0.2 | 2 |
| `e_you_00001_you1_advent_Boss_Colorless` | Boss | Defense | Colorless | 10,000 | 100 | 37 | 0.2 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_you_00001_you1_advent_Normal_Green` | なし | None | なし | なし |
| `e_you_00001_you1_advent_Boss_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal（Normal Green）| 0 | なし | なし | 89 |
| Appearance（Boss Colorless）| 0 | なし | なし | 50 |
| Normal（Boss Colorless）| 0 | なし | なし | 89 |

**攻撃エレメント詳細**

| mst_attack_id | attack_type | range_end | max_target_count | damage_type | hit_type | power_parameter | effect_type |
|--------------|-------------|-----------|-----------------|-------------|----------|-----------------|-------------|
| advent_Normal_Green_Normal | Direct | Distance 0.25 | 1 | Damage | Normal | Percentage 100 | None |
| advent_Boss_Colorless_Appearance | Direct | Distance 50.0 | 100 | None | ForcedKnockBack5 | Percentage 100 | None |
| advent_Boss_Colorless_Normal | Direct | Distance 0.25 | 1 | Damage | KnockBack1 | Percentage 100 | None |

> Boss Colorlessは登場時（Appearance）に全フィールドへのForcedKnockBack5（強制ノックバック）を発動する特殊アビリティを持つ。通常攻撃はKnockBack1。

#### シーケンス設定（代表エントリ）

```
condition_type: InitialSummon
condition_value: 0
sequence_element_id: 1, 2
action_type: SummonEnemy
action_value: e_you_00001_you1_advent_Normal_Green
summon_position: 1.2, 1.5
summon_count: 各1
enemy_hp_coef: 10
sequence_group_id: （なし）
```

```
condition_type: ElapsedTimeSinceSequenceGroupActivated
condition_value: 500
sequence_element_id: 34（w6ウェーブ）
action_type: SummonEnemy
action_value: e_you_00001_you1_advent_Boss_Colorless
summon_position: 2.5
summon_count: 1
enemy_hp_coef: 80
sequence_group_id: w6
```

ウェーブ1ではInitialSummonで2体（HP係数10）が初期配置。ウェーブ6の500ms後にBoss Colorless（HP係数80）が登場。ウェーブ6以降もNormal Greenが継続召喚されてプレッシャーをかけ続ける。7ウェーブ構成（w1〜w6＋ループ）で長時間戦闘を要求する降臨バトル設計。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| AttackPowerDown | 1 | Player |
| Poison | 1 | Player |

---

## 4. パラメータバリエーション一覧（全件）

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 | 主な使用コンテンツ |
|------------|------|------|-------|-----|--------|---------|---------|--------------|--------------|
| `e_you_00001_you1_advent_Boss_Colorless` | Boss | Defense | Colorless | 10,000 | 100 | 37 | 0.2 | 2 | 降臨バトル |
| `e_you_00001_you1_advent_Normal_Green` | Normal | Defense | Green | 1,000 | 100 | 37 | 0.2 | 2 | 降臨バトル |
| `e_you_00001_you1_charaget01_Normal_Colorless` | Normal | Attack | Colorless | 1,000 | 100 | 37 | 0.2 | 2 | イベント（charaget01） |
| `e_you_00001_you1_charaget02_Normal_Red` | Normal | Defense | Red | 1,000 | 100 | 37 | 0.2 | 1 | イベント（charaget02） |
| `e_you_00001_you1_savage01_02_Normal_Colorless` | Normal | Attack | Colorless | 10,000 | 80 | 37 | 0.2 | 2 | イベント（savage03） |
| `e_you_00001_you1_savage01_Normal_Colorless` | Normal | Attack | Colorless | 10,000 | 100 | 37 | 0.2 | 2 | イベント（savage01-02） |
| `e_you_00001_you1_savage01_Normal_Green` | Normal | Attack | Green | 10,000 | 100 | 37 | 0.2 | 2 | イベント（savage01-03） |

---

## 5. コンテンツ別登場実績サマリー

| コンテンツ種別 | ステージ数 | 主な使用パラメータ |
|--------------|---------|----------------|
| イベント | 13ステージ | charaget01_Colorless / charaget02_Red / savage01_Colorless,Green / savage01_02_Colorless |
| 降臨バトル | 1ステージ | advent_Normal_Green / advent_Boss_Colorless |
| メインクエスト Normal | **0ステージ（登場なし）** | - |

> **備考**: このキャラクターはメインクエスト（`normal_%`）には一切登場していません。指定されたフィルタ（normalクエストのNormal難易度）に合致するデータは存在しません。
