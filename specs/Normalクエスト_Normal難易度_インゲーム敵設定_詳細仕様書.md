# NormalクエストNormal難易度 インゲーム敵設定 詳細仕様書

> 調査日: 2026-03-13
> 対象: `quest_type = normal` / `difficulty = Normal` のステージに紐づく敵インゲーム設定
> 参照データ: `release_key = 202509010`（初期リリース済みデータ）

---

## 1. データ構造・テーブル関連図

インゲームで「どの敵が、どんなステータスで、どんな行動をするか」は以下のテーブル群で管理されます。

```
MstQuest (quest_type='normal', difficulty='Normal')
    │ [id]
    ↓
MstStage [mst_quest_id → mst_in_game_id]
    │ [mst_in_game_id]
    ↓
MstInGame
  ├── mst_auto_player_sequence_set_id  ─→ MstAutoPlayerSequence（敵出現シーケンス）
  │     └── action_value               ─→ MstEnemyStageParameter.id（敵の種別・ステータス）
  ├── boss_mst_enemy_stage_parameter_id ─→ MstEnemyStageParameter（ボスHP参照用）
  └── normal/boss enemy coef           （HP・攻撃・速度の倍率調整）

MstEnemyStageParameter
  │ [id = mst_unit_id]
  ↓
MstAttack（攻撃パターン定義：Normal / Appearance / Special）
  │ [id = mst_attack_id]
  ↓
MstAttackElement（攻撃の詳細：範囲・ダメージ・効果）
```

### ポイント

- `MstEnemyStageParameter.id` が `MstAttack.mst_unit_id` と **完全に一致** する（ユーザー指摘の通り）
- 同一の `EnemyStageParameter.id` に対して `MstAttack` が複数紐づく（Normal / Appearance / Special）
- `MstAutoPlayerSequence.action_value` に `EnemyStageParameter.id` を直接記入して、「どの敵を召喚するか」を指定する

---

## 2. NormalクエストNormal難易度 ステージ一覧

| quest_id | 作品 | ステージ数 | release_key |
|----------|------|------------|-------------|
| quest_main_spy_normal_1 | SPY×FAMILY | 5 | 202509010 |
| quest_main_gom_normal_2 | ラーメン赤猫（拷問王女） | 6 | 202509010 |
| quest_main_aka_normal_3 | ラーメン赤猫 | 3 | 202509010 |
| quest_main_glo1_normal_4 〜 | オールスター系 | 3〜 | 202509010 |
| …（計17クエスト）| | | |

各クエストの各ステージに `MstInGame.id` が対応し（例: `normal_spy_00001`〜`normal_spy_00005`）、以下に詳細を解説します。

---

## 3. 具体的な設定例（7例）

---

### 例1: 通常雑魚敵 ── グエン（Colorless / Normal形態）

**作品**: SPY×FAMILY
**EnemyStageParameter ID**: `e_spy_00101_general_n_Normal_Colorless`

#### ステータス

| パラメータ | 値 | 説明 |
|-----------|-----|------|
| mst_enemy_character_id | enemy_spy_00101 | キャラ参照（グエン） |
| character_unit_kind | **Normal** | 雑魚敵 |
| role_type | Attack | 攻撃ロール |
| color | Colorless | 無色 |
| hp | **1,000** | 基本HP |
| damage_knock_back_count | （空）| ノックバック耐性なし |
| move_speed | 31 | 移動速度 |
| well_distance | 0.2 | 攻撃可能距離 |
| attack_power | 50 | 攻撃力 |
| attack_combo_cycle | 1 | コンボサイクル |
| drop_battle_point | 200 | 撃破時バトルポイント |

#### 攻撃設定（MstAttack）

| attack_kind | action_frames | attack_delay | next_attack_interval |
|-------------|--------------|--------------|---------------------|
| Normal | 60 | 25 | 50 |

> Normalのみ（登場演出・スペシャルなし）

#### 攻撃効果（MstAttackElement）

| 項目 | 値 |
|------|-----|
| attack_type | Direct（直接攻撃） |
| range_end_type | Distance |
| range_end_parameter | 0.21（短射程） |
| max_target_count | 1（単体） |
| target | Foe / All |
| damage_type | **Damage** |
| hit_type | Normal |
| power_parameter_type | Percentage |
| power_parameter | **100%** |
| effect_type | None |

**解説**: 典型的な雑魚敵。単体近接攻撃のみ。HP1,000、短い攻撃間隔（50フレーム）で次々と攻撃してくる。

---

### 例2: ボス形態への変化 ── グエン（Colorless / Boss形態）

**作品**: SPY×FAMILY
**EnemyStageParameter ID**: `e_spy_00101_general_n_Boss_Colorless`

#### ステータス（例1との比較）

| パラメータ | Normal形態 | **Boss形態** |
|-----------|------------|-------------|
| character_unit_kind | Normal | **Boss** |
| hp | 1,000 | **10,000（×10）** |
| damage_knock_back_count | なし | **2（2ヒットでノックバック）** |
| move_speed | 31 | 31（変化なし） |
| attack_power | 50 | 50（変化なし） |
| drop_battle_point | 200 | **500** |

#### 攻撃設定（MstAttack）

| attack_kind | action_frames | attack_delay | next_attack_interval |
|-------------|--------------|--------------|---------------------|
| Appearance | 50 | 0 | 0 |
| Normal | 60 | 25 | 50 |

#### 攻撃効果（MstAttackElement）

**Appearance（登場時）**:

| 項目 | 値 |
|------|-----|
| hit_type | **ForcedKnockBack5**（強制ノックバック） |
| range_end_parameter | 50.0（全画面範囲） |
| max_target_count | 100（全体） |
| damage_type | None（ダメージなし） |

**Normal（通常攻撃）**:

| 項目 | 値 |
|------|-----|
| range_end_parameter | 0.21（近距離） |
| damage_type | Damage |
| power_parameter | 100% |

**解説**: ボス昇格で HP が10倍。登場時に全体強制ノックバックを発動し、プレイヤーの味方を吹き飛ばすことができる。この登場演出（Appearance）はボス限定の行動。

---

### 例3: スペシャル攻撃（自己強化型） ── 姫様

**作品**: 拷問王女（gom）
**EnemyStageParameter ID**: `c_gom_00001_general_n_Boss_Yellow`

#### ステータス

| パラメータ | 値 |
|-----------|-----|
| mst_enemy_character_id | chara_gom_00001（囚われの王女 姫様） |
| character_unit_kind | Boss |
| role_type | **Defense**（防御ロール） |
| color | **Yellow** |
| hp | 10,000 |
| damage_knock_back_count | 1 |
| move_speed | 25（低速） |
| well_distance | 0.16 |
| attack_power | 50 |
| attack_combo_cycle | **6** |
| drop_battle_point | 500 |

#### 攻撃設定（MstAttack）

| attack_kind | action_frames | attack_delay | next_attack_interval |
|-------------|--------------|--------------|---------------------|
| Appearance | 50 | 0 | 0 |
| Normal | 65 | 25 | 100 |
| **Special** | **200** | **115** | 0 |

#### 攻撃効果（MstAttackElement）

**Appearance（登場時）**:
- 全体強制ノックバック5（ボス共通）

**Normal（通常攻撃）**:
- range_end: 0.17、単体、Damage 100%

**Special（スペシャル攻撃）** ← ポイント:

| 項目 | 値 |
|------|-----|
| target | **Self（自分自身）** |
| damage_type | **None**（ダメージなし） |
| effect_type | **DamageCut** |
| effective_count | -1（回数無制限） |
| effective_duration | **500**（フレーム） |
| effect_parameter | 5 |

**解説**: `attack_combo_cycle = 6` のため、6回通常攻撃した後にスペシャルを発動。スペシャルは自身にダメージカット効果を付与する自己強化型。Defenseロールらしい耐久特化設計。

---

### 例4: スペシャル攻撃（全体デバフ型） ── トーチャー・トルチュール

**作品**: 拷問王女（gom）
**EnemyStageParameter ID**: `c_gom_00101_general_n_Boss_Yellow`

#### ステータス

| パラメータ | 値 |
|-----------|-----|
| mst_enemy_character_id | chara_gom_00101（トーチャー・トルチュール） |
| character_unit_kind | Boss |
| role_type | **Technical** |
| color | Yellow |
| hp | 10,000 |
| move_speed | 25 |
| well_distance | 0.39 |
| attack_power | 50 |
| attack_combo_cycle | **6** |
| drop_battle_point | 500 |

#### 攻撃設定（MstAttack）

| attack_kind | action_frames | attack_delay | next_attack_interval |
|-------------|--------------|--------------|---------------------|
| Appearance | 50 | 0 | 0 |
| Normal | **145** | **75** | 100 |
| Special | 230 | 150 | 0 |

#### 攻撃効果（MstAttackElement）

**Normal（通常攻撃）**:
- range_end: 0.4（やや長射程）、単体、Damage 100%

**Special（スペシャル攻撃）** ← ポイント:

| 項目 | 値 |
|------|-----|
| range_end_parameter | 0.4 |
| max_target_count | **100（全体）** |
| target | Foe / All |
| damage_type | Damage |
| power_parameter | 100% |
| effect_type | **AttackPowerDown** |
| effective_count | -1 |
| effective_duration | **1000（フレーム）** |
| effect_parameter | **20** |

**解説**: スペシャルが全体攻撃 + 攻撃力ダウン20%（1000フレーム継続）。Technicalロールらしい「対複数＋デバフ」型。通常攻撃のモーション（145フレーム）も他と比べて長く、動きの遅さが設計に反映されている。

---

### 例5: 多段連続攻撃型 ── クロル

**作品**: 拷問王女（gom）
**EnemyStageParameter ID**: `c_gom_00201_general_n_Boss_Yellow`

#### ステータス

| パラメータ | 値 |
|-----------|-----|
| mst_enemy_character_id | chara_gom_00201（クロル） |
| character_unit_kind | Boss |
| role_type | **Attack** |
| color | Yellow |
| hp | 10,000 |
| damage_knock_back_count | **2** |
| move_speed | **50（高速）** |
| well_distance | **0.6（長射程）** |
| attack_power | 50 |
| attack_combo_cycle | **6** |
| drop_battle_point | 500 |

#### 攻撃設定（MstAttack）

| attack_kind | action_frames | attack_delay | next_attack_interval |
|-------------|--------------|--------------|---------------------|
| Appearance | 50 | 0 | 0 |
| Normal | 65 | 0 | 100 |
| Special | **250** | 0 | 0 |

#### 攻撃効果（MstAttackElement）

**Normal（通常攻撃） ── 3段ヒット**:

| sort_order | attack_delay | range_end | power_parameter |
|-----------|-------------|-----------|----------------|
| 1 | 20 | 0.62 | 100% |
| 2 | 30 | 0.62 | 25% |
| 3 | 40 | 0.62 | 25% |

**Special（スペシャル攻撃） ── 9段ヒット**:

| sort_order | attack_delay | range_end | power_parameter |
|-----------|-------------|-----------|----------------|
| 1 | 105 | 0.65 | 15% |
| 2 | 115 | 0.65 | 15% |
| 3 | 125 | 0.65 | 15% |
| 4 | 135 | 0.65 | 15% |
| 5 | 145 | 0.65 | 15% |
| 6 | 160 | 0.65 | 15% |
| 7 | 170 | 0.65 | 15% |
| 8 | 180 | 0.65 | 15% |
| 9 | 190 | 0.65 | **30%** |

総ダメージ（スペシャル）: `15% × 8 + 30% = 150%`
総ダメージ（ノーマル）: `100% + 25% + 25% = 150%`

**解説**: move_speed=50（他ボスの約2倍）で高速移動し、well_distance=0.6の長射程で攻撃する。通常攻撃も3段ヒット、スペシャルは9段連続攻撃（間隔10フレームごと）。スペシャルが最後の1ヒットが2倍ダメージになるフィニッシュブロー設計。

---

### 例6: ステージシーケンス ── 単純出現 `normal_spy_00001`（SPY Stage 1）

**MstInGame設定**:

| パラメータ | 値 |
|-----------|-----|
| id | normal_spy_00001 |
| mst_auto_player_sequence_set_id | normal_spy_00001 |
| boss_mst_enemy_stage_parameter_id | 1（ダミー） |
| normal_enemy_hp_coef | 1.0 |
| normal_enemy_attack_coef | 1.0 |
| boss_enemy_hp_coef | 1.0 |
| boss_enemy_attack_coef | 1.0 |

**MstAutoPlayerSequence**:

| id | condition_type | condition_value | action_type | action_value | summon_count | enemy_hp_coef | enemy_attack_coef |
|----|----------------|----------------|-------------|--------------|--------------|---------------|-------------------|
| normal_spy_00001_1 | ElapsedTime | **650** | SummonEnemy | e_spy_00101_general_n_Normal_Colorless | 1 | **1.5** | **2** |

**解説**: 最もシンプルな構造。ゲーム開始から650フレーム後（約10.8秒）にグエン（雑魚）を1体召喚。召喚時の個別倍率として HP×1.5、攻撃力×2 が適用される（MstInGameの基本coef×シーケンスcoefの積）。

---

### 例7: ステージシーケンス ── グループ遷移とボス出現 `normal_gom_00002`（GOM Stage 2）

**MstAutoPlayerSequence（全シーケンス）**:

**フェーズ0（初期グループなし）**:

| id | condition_type | condition_value | action_type | action_value | summon_count | override_drop | enemy_hp_coef | enemy_attack_coef |
|----|----------------|----------------|-------------|--------------|--------------|---------------|---------------|-------------------|
| _1 | ElapsedTime | 250 | SummonEnemy | e_gom_00402_general_n_Normal_Colorless（たこ焼き） | 1 | **500** | 2.0 | 2.4 |
| _9 | FriendUnitDead | **1** | **SwitchSequenceGroup** | **group1** | - | - | - | - |

**フェーズ1（group1 - ボス出現後）**:

| id | condition_type | condition_value | action_type | action_value | summon_count | summon_interval |
|----|----------------|----------------|-------------|--------------|--------------|----------------|
| _2 | ElapsedTimeSinceGroupActivated | 0 | SummonEnemy | e_gom_00401_general_n_Boss_Colorless | 1 | - |
| _3 | ElapsedTimeSinceGroupActivated | 0 | SummonEnemy | e_gom_00402_general_n_Normal_Colorless | 99 | 300 |
| _4 | ElapsedTimeSinceGroupActivated | 150 | SummonEnemy | e_gom_00402_general_n_Normal_Colorless | 99 | 750 |
| _5 | ElapsedTimeSinceGroupActivated | 25 | SummonEnemy | e_gom_00402_general_n_Normal_Colorless | 99 | 500 |
| _6 | ElapsedTimeSinceGroupActivated | 50 | SummonEnemy | e_gom_00402_general_n_Normal_Colorless | 1 | - |
| _7 | ElapsedTimeSinceGroupActivated | 75 | SummonEnemy | e_gom_00402_general_n_Normal_Colorless | 1 | - |
| _8 | ElapsedTimeSinceGroupActivated | 200 | SummonEnemy | e_gom_00402_general_n_Normal_Colorless | 1 | - |

**解説**:
- 最初にたこ焼き（通常敵）を1体だけ出現させる（`override_drop_battle_point=500` で高倍率報酬）
- たこ焼きを撃破（`FriendUnitDead=1`）すると `SwitchSequenceGroup` で **group1 に遷移**
- group1 ではボス（ラーメン）が即座に登場し、同時にたこ焼きが大量召喚される（最大99体を複数ウェーブ）
- `SummonEnemy count=99, interval=300` は「300フレーム間隔で上限99体まで連続召喚」を意味する

---

## 4. 設定パターンまとめ

### 敵のロール別設計思想

| role_type | 代表例 | 特徴 |
|-----------|--------|------|
| Attack | グエン、密輸残党 | 単体近距離攻撃、Standard設計 |
| Defense | 姫様 | 低速・高耐久、Special=自己バフ（DamageCut） |
| Technical | トーチャー | 全体攻撃、Special=デバフ付与 |

### attack_combo_cycle の意味

`attack_combo_cycle = 6` は「6回通常攻撃した後にSpecialを発動する」を示す。
Normal敵（雑魚）は多くが `cycle=1`（Specialなし）。ボス敵は `cycle=6` が多い。

### シーケンス出現パターン類型

| 種別 | condition_type | 主な用途 |
|------|----------------|---------|
| 時間トリガー | ElapsedTime | ゲーム開始X秒後に出現 |
| 初期召喚 | InitialSummon | ゲーム開始時に特定位置に配置 |
| 敵撃破トリガー | FriendUnitDead | N体撃破でボスや増援が出現 |
| フェーズ遷移 | SwitchSequenceGroup | グループを切り替えて次フェーズへ |
| グループ時間 | ElapsedTimeSinceSequenceGroupActivated | フェーズ移行からX秒後に追加出現 |

### 敵種別の命名規則

```
{prefix}_{作品コード}_{キャラ番号}_{クエスト種別}_{出現ロール}_{BoS or Normal}_{カラー}
例: e_spy_00101_general_n_Boss_Colorless
    │  │      │         │      │         │
    │  │      │         │      │         └─ カラー（色制限なし=Colorless）
    │  │      │         │      └─────────── キャラ種別（Boss/Normal）
    │  │      │         └────────────────── クエスト種別 n=Normal, h=Hard, vh=VeryHard
    │  │      └──────────────────────────── キャラ番号（0番台=主要キャラ）
    │  └─────────────────────────────────── 作品コード
    └────────────────────────────────────── e=enemy（ゲームオリジナル敵）/ c=character（作品キャラ）
```

---

## 5. ID連鎖の実例（フルトレース）

`normal_spy_00002` ステージ2の「ボス出現」フローを追う：

```
MstQuest.id = quest_main_spy_normal_1 (quest_type=normal, difficulty=Normal)
    ↓
MstStage.id = normal_spy_00002 (mst_quest_id = quest_main_spy_normal_1)
    mst_in_game_id = normal_spy_00002
    ↓
MstInGame.id = normal_spy_00002
    mst_auto_player_sequence_set_id = normal_spy_00002
    boss_enemy_hp_coef = 1.0, boss_enemy_attack_coef = 1.0
    ↓
MstAutoPlayerSequence (sequence_set_id = normal_spy_00002)
    id=_2: condition=FriendUnitDead(1) → action=SummonEnemy
           action_value = e_spy_00101_general_n_Boss_Colorless
           enemy_hp_coef = 1.5, enemy_attack_coef = 4
    ↓
MstEnemyStageParameter.id = e_spy_00101_general_n_Boss_Colorless
    mst_enemy_character_id = enemy_spy_00101 (グエン)
    character_unit_kind = Boss
    hp = 10000, attack_power = 50
    ↓ (id = mst_unit_id)
MstAttack (mst_unit_id = e_spy_00101_general_n_Boss_Colorless)
    - id=...Appearance_00001: action_frames=50
    - id=...Normal_00000:     action_frames=60, attack_delay=25, interval=50
    ↓ (id = mst_attack_id)
MstAttackElement
    Appearance: ForcedKnockBack5, range=50.0（全体）
    Normal:     Damage 100%, range=0.21（近距離単体）

【実際の出現時ステータス】
HP = 10000 × MstAutoPlayerSequence.enemy_hp_coef(1.5) × MstInGame.boss_enemy_hp_coef(1.0)
   = 10000 × 1.5 = 15,000 HP
攻撃力 = 50 × 4.0 × 1.0 = 実質 4倍攻撃力
```

---

## 6. 参照テーブル・CSVパス

| テーブル | CSVパス |
|---------|---------|
| MstQuest | `projects/glow-masterdata/MstQuest.csv` |
| MstStage | `projects/glow-masterdata/MstStage.csv` |
| MstInGame | `projects/glow-masterdata/MstInGame.csv` |
| MstAutoPlayerSequence | `projects/glow-masterdata/MstAutoPlayerSequence.csv` |
| MstEnemyStageParameter | `projects/glow-masterdata/MstEnemyStageParameter.csv` |
| MstAttack | `projects/glow-masterdata/MstAttack.csv` |
| MstAttackElement | `projects/glow-masterdata/MstAttackElement.csv` |
| MstEnemyCharacter | `projects/glow-masterdata/MstEnemyCharacter.csv` |
| MstEnemyCharacterI18n | `projects/glow-masterdata/MstEnemyCharacterI18n.csv` |
