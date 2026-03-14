# ゾンビ（enemy_chi_00101）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: enemy_chi_00101
> mst_series_id: chi
> 作品名: チェンソーマン

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `enemy_chi_00101` |
| mst_series_id | `chi` |
| 作品名 | チェンソーマン |
| asset_key | `enemy_chi_00101` |
| is_phantomized | `0` |

---

## 2. キャラクター特徴まとめ

ゾンビはチェンソーマンシリーズのメインクエスト Normal 難易度に特化した雑魚敵キャラクターである。使用されているパラメータバリエーションは Colorless（Defense/HP 5,000）と Yellow（Technical/HP 13,000）の2種で、いずれも Normal ユニット種別・雑魚枠として設計されている。

HP・攻撃力のレンジは Colorless が弱体設定（HP 5,000・攻撃力 320）、Yellow が強化設定（HP 13,000・攻撃力 720）と明確な差異があり、序盤は Colorless を大量投入してウェーブを形成し、後半から Yellow が交じることで段階的に難易度を上げる運用パターンが見られる。

変身設定は全バリエーションでなし。アビリティも設定されていない純粋な数押しキャラクターとして機能している。コマ効果は全対象ステージで None のみであり、コマ効果による支援は一切行われていない。

出現条件は ElapsedTime（経過時間）が最多で、FriendUnitDead（味方死亡数）・OutpostDamage（拠点ダメージ）との組み合わせで波状攻撃を構成する。OutpostDamage 条件発動時は summon_count=99 の大量投入が行われ、拠点への直接プレッシャーが強化される。

---

## 3. ステージ別使用実態

### normal_chi_00001（メインクエスト Normal）

#### このステージでの役割

チェンソーマン Normal クエスト第1ステージ。ゾンビが唯一の敵キャラとして全シーケンスを担い、序盤は Colorless を時間トリガーで小出しにしながらプレイヤーを慣らし、中盤以降は Yellow が混入してFriendUnitDead・OutpostDamage 条件でのプッシュアップが行われる。拠点ダメージを受けると一気に 99 体規模の大量召喚が発生するため、序盤の守りが最重要となるステージ設計。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_chi_00101_general_Normal_Colorless` | Normal | Defense | Colorless | 5,000 | 320 | 35 | 0.11 | 1 |
| `e_chi_00101_general_Normal_Yellow` | Normal | Technical | Yellow | 13,000 | 720 | 35 | 0.11 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_chi_00101_general_Normal_Colorless` | なし | None | なし | なし |
| `e_chi_00101_general_Normal_Yellow` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 95 |
| Normal | 0 | なし | なし | 95 |

> attack_delay: 33 / next_attack_interval: 45（両パラメータ共通）

#### シーケンス設定

```
--- [elem 1] ---
condition_type: ElapsedTime
condition_value: 250
action_type: SummonEnemy
action_value: e_chi_00101_general_Normal_Colorless
summon_position: (デフォルト)
summon_count: 2
summon_interval: 350
enemy_hp_coef: 1
sequence_group_id: (なし)

--- [elem 2] ---
condition_type: ElapsedTime
condition_value: 700
action_type: SummonEnemy
action_value: e_chi_00101_general_Normal_Colorless
summon_position: (デフォルト)
summon_count: 2
summon_interval: 350
enemy_hp_coef: 1
sequence_group_id: (なし)

--- [elem 3] ---
condition_type: ElapsedTime
condition_value: 1200
action_type: SummonEnemy
action_value: e_chi_00101_general_Normal_Colorless
summon_position: (デフォルト)
summon_count: 1
summon_interval: 0
enemy_hp_coef: 1
sequence_group_id: (なし)

--- [elem 4] ---
condition_type: FriendUnitDead
condition_value: 3
action_type: SummonEnemy
action_value: e_chi_00101_general_Normal_Colorless
summon_position: (デフォルト)
summon_count: 10
summon_interval: 500
enemy_hp_coef: 1
sequence_group_id: (なし)

--- [elem 5] ---
condition_type: ElapsedTime
condition_value: 1500
action_type: SummonEnemy
action_value: e_chi_00101_general_Normal_Yellow
summon_position: (デフォルト)
summon_count: 1
summon_interval: 0
enemy_hp_coef: 1
sequence_group_id: (なし)

--- [elem 6] ---
condition_type: ElapsedTime
condition_value: 1700
action_type: SummonEnemy
action_value: e_chi_00101_general_Normal_Yellow
summon_position: (デフォルト)
summon_count: 2
summon_interval: 500
enemy_hp_coef: 1
sequence_group_id: (なし)

--- [elem 7] ---
condition_type: FriendUnitDead
condition_value: 5
action_type: SummonEnemy
action_value: e_chi_00101_general_Normal_Yellow
summon_position: (デフォルト)
summon_count: 2
summon_interval: 500
enemy_hp_coef: 1
sequence_group_id: (なし)

--- [elem 8] ---
condition_type: ElapsedTime
condition_value: 2300
action_type: SummonEnemy
action_value: e_chi_00101_general_Normal_Yellow
summon_position: (デフォルト)
summon_count: 1
summon_interval: 0
enemy_hp_coef: 1
sequence_group_id: (なし)

--- [elem 9] ---
condition_type: FriendUnitDead
condition_value: 8
action_type: SummonEnemy
action_value: e_chi_00101_general_Normal_Yellow
summon_position: 1.9
summon_count: 3
summon_interval: 350
enemy_hp_coef: 1
sequence_group_id: (なし)

--- [elem 10] ---
condition_type: FriendUnitDead
condition_value: 8
action_type: SummonEnemy
action_value: e_chi_00101_general_Normal_Yellow
summon_position: 1.8
summon_count: 3
summon_interval: 350
enemy_hp_coef: 1
sequence_group_id: (なし)

--- [elem 11] ---
condition_type: FriendUnitDead
condition_value: 8
action_type: SummonEnemy
action_value: e_chi_00101_general_Normal_Colorless
summon_position: (デフォルト)
summon_count: 20
summon_interval: 500
enemy_hp_coef: 1
sequence_group_id: (なし)

--- [elem 12] ---
condition_type: ElapsedTime
condition_value: 3000
action_type: SummonEnemy
action_value: e_chi_00101_general_Normal_Yellow
summon_position: (デフォルト)
summon_count: 1
summon_interval: 0
enemy_hp_coef: 1
sequence_group_id: (なし)

--- [elem 13] ---
condition_type: FriendUnitDead
condition_value: 12
action_type: SummonEnemy
action_value: e_chi_00101_general_Normal_Yellow
summon_position: 1.83
summon_count: 3
summon_interval: 750
enemy_hp_coef: 1
sequence_group_id: (なし)

--- [elem 14] ---
condition_type: FriendUnitDead
condition_value: 12
action_type: SummonEnemy
action_value: e_chi_00101_general_Normal_Yellow
summon_position: 1.86
summon_count: 3
summon_interval: 750
enemy_hp_coef: 1
sequence_group_id: (なし)

--- [elem 15] ---
condition_type: FriendUnitDead
condition_value: 12
action_type: SummonEnemy
action_value: e_chi_00101_general_Normal_Yellow
summon_position: 1.88
summon_count: 3
summon_interval: 750
enemy_hp_coef: 1
sequence_group_id: (なし)

--- [elem 16] ---
condition_type: ElapsedTime
condition_value: 4500
action_type: SummonEnemy
action_value: e_chi_00101_general_Normal_Yellow
summon_position: 1.83
summon_count: 3
summon_interval: 750
enemy_hp_coef: 1
sequence_group_id: (なし)

--- [elem 17] ---
condition_type: ElapsedTime
condition_value: 4600
action_type: SummonEnemy
action_value: e_chi_00101_general_Normal_Yellow
summon_position: 1.86
summon_count: 3
summon_interval: 750
enemy_hp_coef: 1
sequence_group_id: (なし)

--- [elem 18] ---
condition_type: ElapsedTime
condition_value: 4700
action_type: SummonEnemy
action_value: e_chi_00101_general_Normal_Yellow
summon_position: 1.88
summon_count: 3
summon_interval: 750
enemy_hp_coef: 1
sequence_group_id: (なし)

--- [elem 19] ---
condition_type: OutpostDamage
condition_value: 1
action_type: SummonEnemy
action_value: e_chi_00101_general_Normal_Yellow
summon_position: 1.9
summon_count: 99
summon_interval: 250
enemy_hp_coef: 1
sequence_group_id: (なし)

--- [elem 20] ---
condition_type: OutpostDamage
condition_value: 1
action_type: SummonEnemy
action_value: e_chi_00101_general_Normal_Yellow
summon_position: 1.8
summon_count: 99
summon_interval: 250
enemy_hp_coef: 1
sequence_group_id: (なし)
```

序盤 (ElapsedTime 250〜1200) は Colorless 2〜1体ずつの緩やかな投入。FriendUnitDead=3 で Colorless 10体、ElapsedTime 1500 以降は Yellow が増加し始め、FriendUnitDead=8 でマルチポジション（1.8〜1.9）の同時出現に移行する。終盤 FriendUnitDead=12・ElapsedTime 4500〜4700 では前線に複数位置から Yellow が集中投下される設計。拠点ダメージが1回でも発生すると 1.8・1.9 両位置から Yellow 99体×2=198体の無限ウェーブが始まり、事実上の敗北演出となる。

#### コマ効果

全行コマ効果なし（全コマ effect_type = None）

---

### normal_chi_00002（メインクエスト Normal）

#### このステージでの役割

チェンソーマン Normal クエスト第2ステージ。序盤は Colorless の密度の高い波状投入（elem 1〜4）からスタートし、FriendUnitDead=4 でYellow へシフト。後半 ElapsedTime 2500 で Yellow 99体の大量召喚と同時に別キャラ（`e_chi_00001_general_Boss_Yellow`）が出現する複合構成となっており、ゾンビは前座として大量の壁役を担う。OutpostDamage 条件でも Yellow 99体が2ポジションから展開される。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_chi_00101_general_Normal_Colorless` | Normal | Defense | Colorless | 5,000 | 320 | 35 | 0.11 | 1 |
| `e_chi_00101_general_Normal_Yellow` | Normal | Technical | Yellow | 13,000 | 720 | 35 | 0.11 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_chi_00101_general_Normal_Colorless` | なし | None | なし | なし |
| `e_chi_00101_general_Normal_Yellow` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 95 |
| Normal | 0 | なし | なし | 95 |

> attack_delay: 33 / next_attack_interval: 45（両パラメータ共通）

#### シーケンス設定

```
--- [elem 1] ---
condition_type: ElapsedTime
condition_value: 100
action_type: SummonEnemy
action_value: e_chi_00101_general_Normal_Colorless
summon_position: (デフォルト)
summon_count: 3
summon_interval: 200
enemy_hp_coef: 1
sequence_group_id: (なし)

--- [elem 2] ---
condition_type: ElapsedTime
condition_value: 800
action_type: SummonEnemy
action_value: e_chi_00101_general_Normal_Colorless
summon_position: (デフォルト)
summon_count: 3
summon_interval: 400
enemy_hp_coef: 1
sequence_group_id: (なし)

--- [elem 3] ---
condition_type: ElapsedTime
condition_value: 900
action_type: SummonEnemy
action_value: e_chi_00101_general_Normal_Colorless
summon_position: (デフォルト)
summon_count: 3
summon_interval: 400
enemy_hp_coef: 1
sequence_group_id: (なし)

--- [elem 4] ---
condition_type: ElapsedTime
condition_value: 1500
action_type: SummonEnemy
action_value: e_chi_00101_general_Normal_Colorless
summon_position: (デフォルト)
summon_count: 1
summon_interval: 0
enemy_hp_coef: 1
sequence_group_id: (なし)

--- [elem 5] ---
condition_type: FriendUnitDead
condition_value: 4
action_type: SummonEnemy
action_value: e_chi_00101_general_Normal_Yellow
summon_position: 1.9
summon_count: 3
summon_interval: 400
enemy_hp_coef: 1
sequence_group_id: (なし)

--- [elem 6] ---
condition_type: FriendUnitDead
condition_value: 4
action_type: SummonEnemy
action_value: e_chi_00101_general_Normal_Yellow
summon_position: 1.8
summon_count: 3
summon_interval: 400
enemy_hp_coef: 1
sequence_group_id: (なし)

--- [elem 7] ---
condition_type: FriendUnitDead
condition_value: 4
action_type: SummonEnemy
action_value: e_chi_00101_general_Normal_Colorless
summon_position: (デフォルト)
summon_count: 10
summon_interval: 400
enemy_hp_coef: 1
sequence_group_id: (なし)

--- [elem 8] ---
condition_type: ElapsedTime
condition_value: 2500
action_type: SummonEnemy
action_value: e_chi_00101_general_Normal_Yellow
summon_position: (デフォルト)
summon_count: 99
summon_interval: 500
enemy_hp_coef: 1
sequence_group_id: (なし)

--- [elem 10] ---
condition_type: OutpostDamage
condition_value: 1
action_type: SummonEnemy
action_value: e_chi_00101_general_Normal_Yellow
summon_position: 1.9
summon_count: 99
summon_interval: 500
enemy_hp_coef: 1
sequence_group_id: (なし)

--- [elem 11] ---
condition_type: OutpostDamage
condition_value: 1
action_type: SummonEnemy
action_value: e_chi_00101_general_Normal_Yellow
summon_position: 1.85
summon_count: 99
summon_interval: 250
enemy_hp_coef: 1
sequence_group_id: (なし)
```

序盤は ElapsedTime 100（開幕直後）から Colorless 3体×3ウェーブと密度が高い。FriendUnitDead=4 で Yellow が前線2ポジション（1.8・1.9）から展開し、同時に Colorless 10体が補充される。ElapsedTime 2500 では Yellow 99体の同時大量投入が発生しボス戦への移行を示す。拠点ダメージ後は 99体×2ポジションの Yellow 無限ウェーブが即座に展開される。

#### コマ効果

全行コマ効果なし（全コマ effect_type = None）

---

### normal_chi_00006（メインクエスト Normal）

#### このステージでの役割

チェンソーマン Normal クエスト第6ステージ。フレンドユニット（`c_chi_00201`・`c_chi_00301`・`c_chi_00002`）が味方死亡を契機に順次参戦するバトル構成で、ゾンビ（Colorless）は ElapsedTime トリガーで大量出現（99体×2ウェーブ）する純粋な壁役。フレンドユニット登場タイミングの調整に連動して、ゾンビが数の圧力を担う設計となっている。このステージでは Yellow バリアントは使用されず、Colorless のみが登場する。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_chi_00101_general_Normal_Colorless` | Normal | Defense | Colorless | 5,000 | 320 | 35 | 0.11 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_chi_00101_general_Normal_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 95 |

> attack_delay: 33 / next_attack_interval: 45

#### シーケンス設定

```
--- [elem 4] ---
condition_type: ElapsedTime
condition_value: 500
action_type: SummonEnemy
action_value: e_chi_00101_general_Normal_Colorless
summon_position: (デフォルト)
summon_count: 99
summon_interval: 800
enemy_hp_coef: 1
sequence_group_id: (なし)

--- [elem 5] ---
condition_type: ElapsedTime
condition_value: 3000
action_type: SummonEnemy
action_value: e_chi_00101_general_Normal_Colorless
summon_position: (デフォルト)
summon_count: 99
summon_interval: 1200
enemy_hp_coef: 1
sequence_group_id: (なし)
```

elem 1〜3 はフレンドユニット系のシーケンスのため省略（ゾンビ関連のみ記載）。開幕 ElapsedTime 500 で Colorless 99体（間隔 800ms）が大量投入され、ElapsedTime 3000 で再度 99体（間隔 1200ms）と2段階の大波がくる。いずれもデフォルト位置への出現で、前線のフレンドユニットとの連携を前提とした数的優位を形成する設計。

#### コマ効果

全行コマ効果なし（全コマ effect_type = None）
