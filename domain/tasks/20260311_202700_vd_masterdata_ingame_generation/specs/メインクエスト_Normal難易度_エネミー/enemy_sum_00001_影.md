# 影（enemy_sum_00001）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: enemy_sum_00001
> mst_series_id: sum
> 作品名: サマータイムレンダ

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `enemy_sum_00001` |
| mst_series_id | `sum` |
| 作品名 | サマータイムレンダ |
| asset_key | `enemy_sum_00001` |
| is_phantomized | `0` |

---

## 2. キャラクター特徴まとめ

影はサマータイムレンダシリーズのメインクエスト Normal 難易度において、3ステージ（normal_sum_00002 / normal_sum_00004 / normal_sum_00005）に登場する敵キャラクターである。コンテンツ全体で中核となる雑魚敵として設計されており、全パラメータが Defense ロールで統一されている。

ステータスレンジはNormalユニットの HP が 15,000（Colorless）〜 80,000（Red）、攻撃力が 200（Colorless）〜 500（Red）と幅広い。Bossユニットは normal_sum_00005 にのみ登場し、HP 350,000・攻撃力 600 と大幅に強化される。移動速度はすべての色・グレードで 40 に統一され、機動力は固定。アビリティ・変身設定は全パラメータで設定なし。

出現条件は ElapsedTime（経過時間）が最多で、序盤の刺客役として用いられる。FriendUnitDead（フレンド撃破数連動）や OutpostDamage（アウトポスト攻撃回数連動）も積極的に使われており、プレイヤーの動向に応じた増援として機能する。normal_sum_00005 では InitialSummon（ステージ開始時即時出現）も設定されており、開幕から敵が配置されるデザインになっている。

コマ効果は3ステージ全てで None のみが設定されており、コマ効果を持つステージは存在しない。

---

## 3. ステージ別使用実態

### normal_sum_00002（メインクエスト Normal）

#### このステージでの役割

サマータイムレンダシリーズのメインクエスト Normal 2ステージ目。影は Colorless・Yellow・Red の3色で登場し、序盤の継続的な雑魚波として耐久を削る役割を担う。終盤（seq ID: 10）では OutpostDamage をトリガーに大量の Yellow が増援として出現し、プレイヤーへの圧力を高める構成になっている。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_sum_00001_general_Normal_Colorless` | Normal | Defense | Colorless | 15,000 | 200 | 40 | 0.2 | 1 |
| `e_sum_00001_general_Normal_Red` | Normal | Defense | Red | 80,000 | 500 | 40 | 0.3 | 1 |
| `e_sum_00001_general_Normal_Yellow` | Normal | Defense | Yellow | 26,000 | 300 | 40 | 0.2 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_sum_00001_general_Normal_Colorless` | なし | None | なし | なし |
| `e_sum_00001_general_Normal_Red` | なし | None | なし | なし |
| `e_sum_00001_general_Normal_Yellow` | なし | None | なし | なし |

#### 攻撃パターン

**Normal_00000 (Normal種 / Colorless・Red・Yellow 共通)**

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 75 |

MstAttackElement（Normal_00000 共通）:

| attack_type | range_start | range_end | max_target | target | damage_type | hit_type | power_parameter | effect_type |
|------------|------------|----------|-----------|--------|------------|---------|----------------|------------|
| Direct | Distance: 0 | Distance: 0.3 | 1 | Foe / All | Damage | Normal | Percentage: 100% | None |

#### シーケンス設定

```
[seq:1] condition_type: ElapsedTime / condition_value: 200 / action_value: e_sum_00001_general_Normal_Colorless / summon_count: 1 / summon_interval: 0
[seq:2] condition_type: ElapsedTime / condition_value: 500 / action_value: e_sum_00001_general_Normal_Colorless / summon_count: 1 / summon_interval: 0
[seq:3] condition_type: ElapsedTime / condition_value: 1000 / action_value: e_sum_00001_general_Normal_Colorless / summon_count: 1 / summon_interval: 0
[seq:4] condition_type: FriendUnitDead / condition_value: 3 / action_value: e_sum_00001_general_Normal_Yellow / summon_count: 3 / summon_interval: 400
[seq:5] condition_type: ElapsedTime / condition_value: 2200 / action_value: e_sum_00001_general_Normal_Yellow / summon_count: 2 / summon_interval: 600
[seq:6] condition_type: ElapsedTime / condition_value: 2600 / action_value: e_sum_00001_general_Normal_Colorless / summon_count: 99 / summon_interval: 700
[seq:8] condition_type: ElapsedTime / condition_value: 3700 / action_value: e_sum_00001_general_Normal_Red / summon_count: 2 / summon_interval: 50
[seq:9] condition_type: ElapsedTime / condition_value: 5000 / action_value: e_sum_00001_general_Normal_Yellow / summon_count: 99 / summon_interval: 1400
[seq:10] condition_type: OutpostDamage / condition_value: 1 / action_value: e_sum_00001_general_Normal_Yellow / summon_count: 99 / summon_interval: 1000
```

序盤は ElapsedTime で Colorless を1体ずつ散らしながら侵攻させ、フレンド撃破 3 体到達時に Yellow 3体を一気に追加する。時間 2600 以降は Colorless・Yellow を大量召喚（summon_count: 99）に切り替えて物量攻勢をかけ、時間 3700 には Red 2体で突破口を作る構成。OutpostDamage: 1 のトリガーも設定されており、アウトポストへの攻撃を受けた直後にさらに Yellow の大量増援が来る。

#### コマ効果

コマ効果なし（全コマ None）

---

### normal_sum_00004（メインクエスト Normal）

#### このステージでの役割

サマータイムレンダシリーズのメインクエスト Normal 4ステージ目。影は Colorless・Blue・Red の3色で構成される。normal_sum_00002 と同じシーケンス構造を踏襲しており、カラーが Yellow → Blue に差し替えられたバリエーションとして位置づけられる。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_sum_00001_general_Normal_Blue` | Normal | Defense | Blue | 18,000 | 400 | 40 | 0.2 | 1 |
| `e_sum_00001_general_Normal_Colorless` | Normal | Defense | Colorless | 15,000 | 200 | 40 | 0.2 | 1 |
| `e_sum_00001_general_Normal_Red` | Normal | Defense | Red | 80,000 | 500 | 40 | 0.3 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_sum_00001_general_Normal_Blue` | なし | None | なし | なし |
| `e_sum_00001_general_Normal_Colorless` | なし | None | なし | なし |
| `e_sum_00001_general_Normal_Red` | なし | None | なし | なし |

#### 攻撃パターン

**Normal_00000 (Normal種 / Blue・Colorless・Red 共通)**

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 75 |

MstAttackElement（Normal_00000 共通）:

| attack_type | range_start | range_end | max_target | target | damage_type | hit_type | power_parameter | effect_type |
|------------|------------|----------|-----------|--------|------------|---------|----------------|------------|
| Direct | Distance: 0 | Distance: 0.3 | 1 | Foe / All | Damage | Normal | Percentage: 100% | None |

#### シーケンス設定

```
[seq:1] condition_type: ElapsedTime / condition_value: 200 / action_value: e_sum_00001_general_Normal_Colorless / summon_count: 1 / summon_interval: 0
[seq:2] condition_type: ElapsedTime / condition_value: 500 / action_value: e_sum_00001_general_Normal_Colorless / summon_count: 1 / summon_interval: 0
[seq:3] condition_type: ElapsedTime / condition_value: 1000 / action_value: e_sum_00001_general_Normal_Colorless / summon_count: 1 / summon_interval: 0
[seq:4] condition_type: FriendUnitDead / condition_value: 3 / action_value: e_sum_00001_general_Normal_Blue / summon_count: 3 / summon_interval: 400
[seq:5] condition_type: ElapsedTime / condition_value: 2200 / action_value: e_sum_00001_general_Normal_Blue / summon_count: 2 / summon_interval: 400
[seq:6] condition_type: ElapsedTime / condition_value: 2600 / action_value: e_sum_00001_general_Normal_Colorless / summon_count: 99 / summon_interval: 700
[seq:8] condition_type: ElapsedTime / condition_value: 3900 / action_value: e_sum_00001_general_Normal_Red / summon_count: 2 / summon_interval: 50
[seq:9] condition_type: ElapsedTime / condition_value: 5000 / action_value: e_sum_00001_general_Normal_Blue / summon_count: 99 / summon_interval: 1400
[seq:10] condition_type: OutpostDamage / condition_value: 1 / action_value: e_sum_00001_general_Normal_Blue / summon_count: 99 / summon_interval: 1000
```

normal_sum_00002 と同じ骨格を持ち、Yellowパラメータを Blueパラメータに置き換えた構成。Blue は Yellow より HP が低い（26,000 → 18,000）が攻撃力が高く（300 → 400）、より攻撃的な雑魚として設計されている。Red 投入タイミングが 3700 → 3900 と若干遅くなっている。

#### コマ効果

コマ効果なし（全コマ None）

---

### normal_sum_00005（メインクエスト Normal）

#### このステージでの役割

サマータイムレンダシリーズのメインクエスト Normal 5ステージ目。影の全5パラメータ（Colorless・Blue・Red・Yellow・Boss Red）が出揃う集大成ステージ。BoSSユニット（e_sum_00001_general_Boss_Red: HP 350,000）が初めて登場し、フレンド撃破 9 体という高難度トリガーで呼び出される。InitialSummon が設定されており、ステージ開始直後から Colorless 2体が前線に配置される点が序盤の特徴。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_sum_00001_general_Normal_Colorless` | Normal | Defense | Colorless | 15,000 | 200 | 40 | 0.2 | 1 |
| `e_sum_00001_general_Normal_Blue` | Normal | Defense | Blue | 18,000 | 400 | 40 | 0.2 | 1 |
| `e_sum_00001_general_Normal_Red` | Normal | Defense | Red | 80,000 | 500 | 40 | 0.3 | 1 |
| `e_sum_00001_general_Boss_Red` | Boss | Defense | Red | 350,000 | 600 | 40 | 0.2 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_sum_00001_general_Normal_Colorless` | なし | None | なし | なし |
| `e_sum_00001_general_Normal_Blue` | なし | None | なし | なし |
| `e_sum_00001_general_Normal_Red` | なし | None | なし | なし |
| `e_sum_00001_general_Boss_Red` | なし | None | なし | なし |

#### 攻撃パターン

**Normal種（Colorless・Blue・Red 共通）**

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 75 |

MstAttackElement:

| attack_type | range_start | range_end | max_target | target | damage_type | hit_type | power_parameter | effect_type |
|------------|------------|----------|-----------|--------|------------|---------|----------------|------------|
| Direct | Distance: 0 | Distance: 0.3 | 1 | Foe / All | Damage | Normal | Percentage: 100% | None |

**Boss Red（e_sum_00001_general_Boss_Red）**

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 75 |

MstAttackElement（Boss Red）:

| attack_id | attack_type | range_end | max_target | damage_type | hit_type | power_parameter | effect_type |
|----------|------------|----------|-----------|------------|---------|----------------|------------|
| Appearance_00001 | Direct | Distance: 50.0 | 100 | None | ForcedKnockBack5 | Percentage: 0% | None |
| Normal_00000 | Direct | Distance: 0.3 | 1 | Damage | Normal | Percentage: 100% | None |

ボスは出現時（Appearance）に範囲 50 の強制ノックバック5を全員に与える登場演出アタックを持つ。

#### シーケンス設定

```
[seq:1]  condition_type: InitialSummon / condition_value: 2 / action_value: e_sum_00001_general_Normal_Colorless / summon_position: 2.8 / summon_count: 1 / summon_interval: 0
[seq:2]  condition_type: InitialSummon / condition_value: 2 / action_value: e_sum_00001_general_Normal_Colorless / summon_position: 2.7 / summon_count: 1 / summon_interval: 0
[seq:3]  condition_type: FriendUnitDead / condition_value: 1 / action_value: e_sum_00001_general_Normal_Colorless / summon_count: 3 / summon_interval: 700
[seq:4]  condition_type: FriendUnitDead / condition_value: 2 / action_value: e_sum_00001_general_Normal_Colorless / summon_count: 3 / summon_interval: 700
[seq:5]  condition_type: ElapsedTime / condition_value: 2600 / action_value: e_sum_00001_general_Normal_Colorless / summon_count: 1 / summon_interval: 0
[seq:6]  condition_type: ElapsedTime / condition_value: 2700 / action_value: e_sum_00001_general_Normal_Colorless / summon_count: 1 / summon_interval: 0
[seq:7]  condition_type: ElapsedTime / condition_value: 2800 / action_value: e_sum_00001_general_Normal_Blue / summon_count: 99 / summon_interval: 1500
[seq:8]  condition_type: FriendUnitDead / condition_value: 5 / action_value: e_sum_00001_general_Normal_Blue / summon_position: 2.8 / summon_count: 1 / summon_interval: 0
[seq:9]  condition_type: FriendUnitDead / condition_value: 5 / action_value: e_sum_00001_general_Normal_Blue / summon_position: 2.7 / summon_count: 1 / summon_interval: 0
[seq:10] condition_type: FriendUnitDead / condition_value: 5 / action_value: e_sum_00001_general_Normal_Colorless / summon_position: 2.9 / summon_count: 99 / summon_interval: 600
[seq:11] condition_type: FriendUnitDead / condition_value: 8 / action_value: e_sum_00001_general_Normal_Red / summon_position: 2.8 / summon_count: 1 / summon_interval: 0
[seq:12] condition_type: FriendUnitDead / condition_value: 8 / action_value: e_sum_00001_general_Normal_Red / summon_position: 2.7 / summon_count: 1 / summon_interval: 0
[seq:13] condition_type: FriendUnitDead / condition_value: 9 / action_value: e_sum_00001_general_Boss_Red / summon_position: 2.9 / summon_count: 1 / summon_interval: 0
[seq:14] condition_type: OutpostDamage / condition_value: 1 / action_value: e_sum_00001_general_Normal_Blue / summon_count: 99 / summon_interval: 700
```

ステージ開始時から Colorless 2体を前線に配置（InitialSummon）し、フレンド撃破数に応じて段階的に強化版が出現する設計。フレンド撃破 5 体で Blue が summon_position 指定付きで2体＋Colorless 大量召喚、撃破 8 体で Red 2体、9 体到達でボス（Boss Red）が登場する。OutpostDamage でも Blue の大量増援が来る。summon_position が指定されているレコードは前線寄り（2.7〜2.9）への位置指定で、押し込みプレッシャーを意図した配置。

#### コマ効果

コマ効果なし（全コマ None）
