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

**解説**: 典型的な雑魚敵。単体近接攻撃のみ。HP1,000、短い攻撃間隔（50フレーム）で次々と攻撃してくる。登場演出・スペシャル攻撃は持たず、`attack_combo_cycle=1` のためSpecial発動なし。ボスに昇格する前の基本形態。

#### ステータス（MstEnemyStageParameter）

| パラメータ | 値 | 説明 |
|-----------|-----|------|
| mst_enemy_character_id | enemy_spy_00101 | MstEnemyCharacterへの参照ID。キャラクターの見た目・名前（グエン）を決定する |
| character_unit_kind | **Normal** | 戦闘での役割区分。`Normal`=雑魚敵（撃破対象として大量出現）、`Boss`=ボス敵（HP大・登場演出あり） |
| role_type | Attack | 戦闘スタイル区分。`Attack`=単体近距離攻撃の標準型、`Defense`=耐久特化・自己バフ型、`Technical`=全体攻撃・デバフ付与型 |
| color | Colorless | カラーマッチング用の色属性。プレイヤーユニットとの有利・不利関係に影響する。`Colorless`=色なし（有利色なし） |
| hp | **1,000** | 基本HP値。実際のHPは `MstInGame.normal_enemy_hp_coef × MstAutoPlayerSequence.enemy_hp_coef × この値` の積で決まる |
| damage_knock_back_count | （空）| ノックバック発生に必要な被ヒット数。空欄=ノックバック耐性なし（1ヒットでもノックバックする） |
| move_speed | 31 | 1フレームあたりの移動量（ゲーム内単位）。大きいほど高速移動する |
| well_distance | 0.2 | 攻撃を開始する最大距離（ゲーム内単位）。敵がこの距離以内に入ると攻撃モーションを開始する |
| attack_power | 50 | ダメージ計算の基礎値。`power_parameter_type=Percentage` の場合、実際のダメージ = `attack_power × power_parameter / 100` |
| attack_combo_cycle | 1 | 通常攻撃のサイクル数。この回数通常攻撃した後にSpecial攻撃を発動する。`1`=Specialなし（雑魚敵の標準）、`6`=6回ごとにSpecial（ボスの標準） |
| drop_battle_point | 200 | 撃破時にプレイヤーが獲得するバトルポイント。`MstAutoPlayerSequence.override_drop_battle_point` で上書き可能 |

#### 攻撃設定（MstAttack）

| attack_kind | action_frames | attack_delay | next_attack_interval | 説明 |
|-------------|--------------|--------------|---------------------|------|
| Normal | 60 | 25 | 50 | 通常攻撃のみ（登場演出・スペシャルなし） |

> **カラム説明**:
> - `attack_kind`: 攻撃の発動タイプ。`Normal`=通常の攻撃行動、`Appearance`=ステージ登場時に一度だけ発動（ボス限定）、`Special`=combo_cycleに達したときに発動（ボス限定）
> - `action_frames`: このアクション全体のフレーム数（モーション長）。この間は次のアクションに移れない
> - `attack_delay`: アクション開始からダメージ判定が発生するまでの遅延フレーム数。モーションの"振り"部分の長さに相当する
> - `next_attack_interval`: ダメージ判定後、次のアクションを開始するまでのインターバル（フレーム）。0の場合は即座に次の行動へ移行

#### 攻撃効果（MstAttackElement）

| 項目 | 値 | 説明 |
|------|-----|------|
| attack_type | Direct（直接攻撃） | 攻撃の物理的な方式。`Direct`=対象に直接当たる接触型攻撃 |
| range_end_type | Distance | 射程の計算方式。`Distance`=距離によって射程範囲を決定する |
| range_end_parameter | 0.21（短射程） | 攻撃が届く最大距離（ゲーム内単位）。0.21=近接のみ、50.0=全画面範囲 |
| max_target_count | 1（単体） | 一度に攻撃できる最大ターゲット数。1=単体攻撃、100=実質全体攻撃 |
| target | Foe / All | ターゲットの選択条件。`Foe/All`=全ての敵対ユニット（プレイヤー視点の味方）、`Self`=自分自身 |
| damage_type | **Damage** | ダメージを与えるかどうか。`Damage`=ダメージあり、`None`=ダメージなし（バフ・デバフのみ付与） |
| hit_type | Normal | ヒット時の追加効果。`Normal`=通常ヒット、`ForcedKnockBack5`=強制的に大きくノックバックさせる |
| power_parameter_type | Percentage | ダメージ倍率の計算方式。`Percentage`=attack_powerに対するパーセンテージで計算 |
| power_parameter | **100%** | ダメージ倍率（%）。100=attack_powerの100%分のダメージを与える |
| effect_type | None | ヒット時に付与するステータス効果。`None`=効果なし、`DamageCut`=被ダメージ軽減、`AttackPowerDown`=攻撃力ダウン |

---

### 例2: ボス形態への変化 ── グエン（Colorless / Boss形態）

**作品**: SPY×FAMILY
**EnemyStageParameter ID**: `e_spy_00101_general_n_Boss_Colorless`

**解説**: ボス昇格で HP が10倍（1,000→10,000）になり、ノックバック耐性（2ヒット必要）も追加される。登場時に全体強制ノックバックを発動し、プレイヤーの味方を吹き飛ばすことができる。この登場演出（Appearance）はボス限定の行動。同一キャラの `Normal形態`（例1）との差分を把握することで、ステージ難易度調整のパターンを理解できる。

#### ステータス（例1との比較）

| パラメータ | Normal形態 | **Boss形態** | 変更点の意味 |
|-----------|------------|-------------|------------|
| character_unit_kind | Normal | **Boss** | ボス判定になることで登場演出（Appearance）が使えるようになり、HPバーUIも表示される |
| hp | 1,000 | **10,000（×10）** | ステージのクリア難易度を大きく左右するメインパラメータ |
| damage_knock_back_count | なし | **2（2ヒットでノックバック）** | N回ヒットしないとノックバックしない耐性。数値が大きいほど吹き飛ばしにくく、ボスらしい重厚感が出る |
| move_speed | 31 | 31（変化なし） | ボスでも移動速度は据え置き |
| attack_power | 50 | 50（変化なし） | 攻撃力は変化なし（coef倍率で実質的な強さは変わる） |
| drop_battle_point | 200 | **500** | ボスは撃破報酬が高い |

#### 攻撃設定（MstAttack）

| attack_kind | action_frames | attack_delay | next_attack_interval |
|-------------|--------------|--------------|---------------------|
| Appearance | 50 | 0 | 0 |
| Normal | 60 | 25 | 50 |

#### 攻撃効果（MstAttackElement）

**Appearance（登場時）**:

| 項目 | 値 | 説明 |
|------|-----|------|
| hit_type | **ForcedKnockBack5** | `attack_delay=0` で登場と同時に全プレイヤーユニットを強制ノックバック。数値5は吹き飛ばし距離の強さを示す |
| range_end_parameter | 50.0（全画面範囲） | 実質画面全体をカバーする射程。登場した瞬間に全ユニットへ影響する |
| max_target_count | 100（全体） | 全プレイヤーユニットが対象 |
| damage_type | None（ダメージなし） | ノックバックのみで実ダメージは与えない。プレイヤーの陣形を崩す効果に特化 |

**Normal（通常攻撃）**:

| 項目 | 値 | 説明 |
|------|-----|------|
| range_end_parameter | 0.21（近距離） | Normal形態と同じ近接射程 |
| damage_type | Damage | 通常ダメージを与える |
| power_parameter | 100% | attack_powerの100%分のダメージ |

---

### 例3: スペシャル攻撃（自己強化型） ── 姫様

**作品**: 拷問王女（gom）
**EnemyStageParameter ID**: `c_gom_00001_general_n_Boss_Yellow`

**解説**: `attack_combo_cycle=6` のため、6回通常攻撃した後にスペシャルを発動する設計。スペシャルは自身にダメージカット効果を付与する自己強化型で、`role_type=Defense` らしい耐久特化設計。IDプレフィックスが `c_`（characterの略）であり、作品の登場キャラクターを敵として使用するパターン。low_speed（25）とDamageCutの組み合わせで、高耐久・鈍足型ボスの典型例。

#### ステータス

| パラメータ | 値 | 説明 |
|-----------|-----|------|
| mst_enemy_character_id | chara_gom_00001 | 囚われの王女 姫様 |
| character_unit_kind | Boss | ボス敵 |
| role_type | **Defense** | 防御ロール。低速・高耐久・自己バフが設計思想 |
| color | **Yellow** | Yellow色敵。プレイヤー側の有利色ユニットと組み合わせてゲームプレイに影響する |
| hp | 10,000 | ボス標準HP |
| damage_knock_back_count | 1 | 1ヒットでノックバックする（ノックバック耐性あり・最小値） |
| move_speed | 25（低速） | 他ボスの平均より低速。Defenseロールの鈍足設計 |
| well_distance | 0.16 | 近距離でないと攻撃しない |
| attack_power | 50 | 標準攻撃力 |
| attack_combo_cycle | **6** | 6回通常攻撃した後にSpecialを発動するサイクル数 |
| drop_battle_point | 500 | ボス標準BP |

#### 攻撃設定（MstAttack）

| attack_kind | action_frames | attack_delay | next_attack_interval |
|-------------|--------------|--------------|---------------------|
| Appearance | 50 | 0 | 0 |
| Normal | 65 | 25 | 100 |
| **Special** | **200** | **115** | 0 |

> `Special` の `action_frames=200` はモーション全体が長く、発動中プレイヤーが対処する猶予がある。`attack_delay=115` でダメージ/効果判定が遅れて発生するため、モーション前半が長い"ため"演出になっている。

#### 攻撃効果（MstAttackElement）

**Appearance（登場時）**:
- 全体強制ノックバック5（ボス共通演出）

**Normal（通常攻撃）**:
- range_end: 0.17（近距離単体攻撃）、Damage 100%

**Special（スペシャル攻撃）** ← ポイント:

| 項目 | 値 | 説明 |
|------|-----|------|
| target | **Self（自分自身）** | 自身を対象とするため、プレイヤーユニットへのダメージは発生しない |
| damage_type | **None**（ダメージなし） | 攻撃ではなく自己強化専用アクション |
| effect_type | **DamageCut** | 被ダメージを軽減するバフ効果。Defenseロールらしい生存特化設計 |
| effective_count | -1（回数無制限） | 持続時間が尽きるまで何度被弾してもDamageCutが有効 |
| effective_duration | **500**（フレーム） | 効果の持続フレーム数。約8.3秒間ダメージカットが有効 |
| effect_parameter | 5 | DamageCutの軽減率（%）。被ダメージを5%カットする |

---

### 例4: スペシャル攻撃（全体デバフ型） ── トーチャー・トルチュール

**作品**: 拷問王女（gom）
**EnemyStageParameter ID**: `c_gom_00101_general_n_Boss_Yellow`

**解説**: `role_type=Technical` らしい「対複数＋デバフ」型の設計。スペシャルが全体攻撃 + 攻撃力ダウン20%（1000フレーム継続）で、プレイヤー全体のDPSを長時間低下させる。通常攻撃のモーション（`action_frames=145`）も他キャラと比べて長く、のっそりした動きが設計に反映されている。`well_distance=0.39` とやや長めの射程で、プレイヤーが近寄る前に攻撃できる。

#### ステータス

| パラメータ | 値 | 説明 |
|-----------|-----|------|
| mst_enemy_character_id | chara_gom_00101 | トーチャー・トルチュール |
| character_unit_kind | Boss | ボス敵 |
| role_type | **Technical** | 技巧ロール。全体攻撃・デバフ付与が設計思想 |
| color | Yellow | Yellow色敵 |
| hp | 10,000 | ボス標準HP |
| move_speed | 25 | 低速（姫様と同じ） |
| well_distance | 0.39 | やや長射程。近距離以外からも攻撃できる |
| attack_power | 50 | 標準攻撃力 |
| attack_combo_cycle | **6** | 6回通常攻撃後にSpecialを発動 |
| drop_battle_point | 500 | ボス標準BP |

#### 攻撃設定（MstAttack）

| attack_kind | action_frames | attack_delay | next_attack_interval |
|-------------|--------------|--------------|---------------------|
| Appearance | 50 | 0 | 0 |
| Normal | **145** | **75** | 100 |
| Special | 230 | 150 | 0 |

> Normal攻撃の `action_frames=145`（他ボスの60〜65に対して2倍以上）は、のっそりした重い動作を表現している。`attack_delay=75` でダメージ判定も遅く、攻撃モーション開始から約1.25秒後にやっとダメージが発生する。

#### 攻撃効果（MstAttackElement）

**Normal（通常攻撃）**:
- range_end: 0.4（やや長射程）、単体、Damage 100%

**Special（スペシャル攻撃）** ← ポイント:

| 項目 | 値 | 説明 |
|------|-----|------|
| range_end_parameter | 0.4 | Normal攻撃と同じ射程でSpecialも届く |
| max_target_count | **100（全体）** | 全プレイヤーユニットを対象にする全体攻撃 |
| target | Foe / All | 全ての敵対ユニット（プレイヤー側全員）が対象 |
| damage_type | Damage | 全体ダメージを与えつつデバフも付与する |
| power_parameter | 100% | attack_powerの100%分のダメージ |
| effect_type | **AttackPowerDown** | 攻撃力ダウンのデバフ効果。ヒットした全ユニットに付与する |
| effective_count | -1 | 持続時間が尽きるまで効果が有効（回数制限なし） |
| effective_duration | **1000（フレーム）** | 約16.7秒間の長時間デバフ。combo_cycle=6のため次のSpecialまで約1000フレーム相当の時間があり、実質的に常時デバフが入り続ける設計 |
| effect_parameter | **20** | 攻撃力ダウンの低下率（%）。プレイヤーユニット全員の攻撃力を20%ダウンさせる |

---

### 例5: 多段連続攻撃型 ── クロル

**作品**: 拷問王女（gom）
**EnemyStageParameter ID**: `c_gom_00201_general_n_Boss_Yellow`

**解説**: `move_speed=50`（他ボスの約2倍の高速移動）で `well_distance=0.6`（長射程）を持ち、素早く距離を詰めて攻撃するアタッカー型ボス。通常攻撃が3段ヒット（総ダメージ150%）、スペシャルは9段連続攻撃（10フレーム間隔、最終ヒットが2倍ダメージのフィニッシュブロー設計）。攻撃力は姫様・トーチャーと同じ `attack_power=50` だが、coef倍率が大きめに設定されることが多く、実質最高ダメージを出すボス。

#### ステータス

| パラメータ | 値 | 説明 |
|-----------|-----|------|
| mst_enemy_character_id | chara_gom_00201 | クロル |
| character_unit_kind | Boss | ボス敵 |
| role_type | **Attack** | 攻撃ロール。高速・長射程・多段ヒットが設計思想 |
| color | Yellow | Yellow色敵 |
| hp | 10,000 | ボス標準HP |
| damage_knock_back_count | **2** | 2ヒット必要なノックバック耐性。高速移動中にノックバックされにくい設計 |
| move_speed | **50（高速）** | 他ボスの約2倍。プレイヤー陣地に素早く到達し、長射程攻撃を仕掛ける |
| well_distance | **0.6（長射程）** | 他ボス（0.16〜0.39）と比べて格段に長い攻撃射程。遠距離からでも攻撃開始できる |
| attack_power | 50 | 標準攻撃力（多段ヒットにより実質ダメージは高い） |
| attack_combo_cycle | **6** | 6回通常攻撃後にSpecialを発動 |
| drop_battle_point | 500 | ボス標準BP |

#### 攻撃設定（MstAttack）

| attack_kind | action_frames | attack_delay | next_attack_interval |
|-------------|--------------|--------------|---------------------|
| Appearance | 50 | 0 | 0 |
| Normal | 65 | 0 | 100 |
| Special | **250** | 0 | 0 |

> Special の `action_frames=250` はこのキャラ最長のモーション。`attack_delay=0` から開始し、MstAttackElement の `sort_order` ごとに異なる `attack_delay` を設定することで9段連続ヒットを実現している。

#### 攻撃効果（MstAttackElement）

**Normal（通常攻撃） ── 3段ヒット**:

| sort_order | attack_delay | range_end | power_parameter | 説明 |
|-----------|-------------|-----------|-----------------|------|
| 1 | 20 | 0.62 | 100% | 1段目：メインヒット（attack_powerの100%） |
| 2 | 30 | 0.62 | 25% | 2段目：追加ヒット（10フレーム後） |
| 3 | 40 | 0.62 | 25% | 3段目：追加ヒット（さらに10フレーム後） |

> `sort_order` は同一攻撃内でのヒット順序。`attack_delay` の差分（10フレーム間隔）で連続ヒットを表現する。全段ヒット総ダメージ: `100% + 25% + 25% = 150%`

**Special（スペシャル攻撃） ── 9段ヒット**:

| sort_order | attack_delay | range_end | power_parameter | 説明 |
|-----------|-------------|-----------|-----------------|------|
| 1 | 105 | 0.65 | 15% | 1段目（attack開始から105フレーム後） |
| 2 | 115 | 0.65 | 15% | 2段目（10フレーム間隔） |
| 3 | 125 | 0.65 | 15% | 3段目 |
| 4 | 135 | 0.65 | 15% | 4段目 |
| 5 | 145 | 0.65 | 15% | 5段目 |
| 6 | 160 | 0.65 | 15% | 6段目（15フレーム間隔に変化） |
| 7 | 170 | 0.65 | 15% | 7段目 |
| 8 | 180 | 0.65 | 15% | 8段目 |
| 9 | 190 | 0.65 | **30%** | 9段目（フィニッシュブロー：他の2倍ダメージ） |

> 総ダメージ（スペシャル）: `15% × 8 + 30% = 150%`
> 総ダメージ（ノーマル）: `100% + 25% + 25% = 150%`
>
> 通常・スペシャルともに総ダメージ150%で統一されており、スペシャルは「ため演出（105フレーム）＋高速9連打＋フィニッシュ」の見栄えに特化した設計。

---

### 例6: ステージシーケンス ── 単純出現 `normal_spy_00001`（SPY Stage 1）

**解説**: 最もシンプルなシーケンス構造。ゲーム開始から650フレーム後（約10.8秒）にグエン（雑魚）を1体召喚。召喚時の個別倍率として HP×1.5、攻撃力×2 が適用される（MstInGameの基本coef×シーケンスcoefの積）。シーケンスグループ遷移なし、ウェーブなし、ボスなしのチュートリアル的ステージ。

**MstInGame設定**:

| パラメータ | 値 | 説明 |
|-----------|-----|------|
| id | normal_spy_00001 | ステージID（MstStage.mst_in_game_idと一致） |
| mst_auto_player_sequence_set_id | normal_spy_00001 | 紐づくシーケンスのset_id（通常MstInGame.idと同値） |
| boss_mst_enemy_stage_parameter_id | 1（ダミー） | ボスHP表示バー用の参照ID。このステージはボスなしのためダミー値を設定 |
| normal_enemy_hp_coef | 1.0 | 通常敵のHP全体倍率。MstAutoPlayerSequenceのenemy_hp_coefと積になる |
| normal_enemy_attack_coef | 1.0 | 通常敵の攻撃力全体倍率 |
| boss_enemy_hp_coef | 1.0 | ボス敵のHP全体倍率 |
| boss_enemy_attack_coef | 1.0 | ボス敵の攻撃力全体倍率 |

**MstAutoPlayerSequence**:

| id | condition_type | condition_value | action_type | action_value | summon_count | enemy_hp_coef | enemy_attack_coef |
|----|----------------|----------------|-------------|--------------|--------------|---------------|-------------------|
| normal_spy_00001_1 | ElapsedTime | **650** | SummonEnemy | e_spy_00101_general_n_Normal_Colorless | 1 | **1.5** | **2** |

> **カラム説明**:
> - `condition_type`: 出現トリガーの種別。`ElapsedTime`=ゲーム開始からの経過フレーム数、`FriendUnitDead`=味方ユニット（敵側から見た友軍）の撃破数、`ElapsedTimeSinceGroupActivated`=グループ遷移後の経過フレーム数
> - `condition_value`: トリガー発火の閾値。`ElapsedTime`の場合はフレーム数（60fps換算：650フレーム ≒ 10.8秒）
> - `action_type`: トリガー発火時に実行するアクション。`SummonEnemy`=指定した敵を召喚、`SwitchSequenceGroup`=次のフェーズへ遷移
> - `action_value`: アクションの対象ID。`SummonEnemy`の場合はMstEnemyStageParameter.id、`SwitchSequenceGroup`の場合はグループ名（例: `group1`）
> - `summon_count`: 召喚する敵の最大体数。`summon_interval`が設定されている場合は間隔ごとに1体ずつ召喚してこの数に達したら停止
> - `enemy_hp_coef`: この召喚エントリ個別のHP倍率。**MstInGame.normal/boss_enemy_hp_coefと積**になる（例: MstInGame側1.0 × シーケンス側1.5 = 実際のHP×1.5）
> - `enemy_attack_coef`: この召喚エントリ個別の攻撃力倍率

**HP計算例**:
```
実際のHP = MstEnemyStageParameter.hp × MstInGame.normal_enemy_hp_coef × MstAutoPlayerSequence.enemy_hp_coef
         = 1,000 × 1.0 × 1.5 = 1,500 HP
```

---

### 例7: ステージシーケンス ── グループ遷移とボス出現 `normal_gom_00002`（GOM Stage 2）

**解説**:
- 最初にたこ焼き（通常敵）を1体だけ出現させる（`override_drop_battle_point=500` で高倍率報酬）
- たこ焼きを撃破（`FriendUnitDead=1`）すると `SwitchSequenceGroup` で **group1 に遷移**
- group1 ではボス（ラーメン）が即座に登場し、同時にたこ焼きが大量召喚される（最大99体を複数ウェーブ）
- `SummonEnemy count=99, interval=300` は「300フレーム間隔で上限99体まで連続召喚」を意味する
- 「弱い雑魚を撃破 → ボス出現 + 大量増援」という二段階構成で、プレイヤーに油断と緊張感の変化を与える設計

**MstAutoPlayerSequence（全シーケンス）**:

**フェーズ0（初期グループなし）**:

| id | condition_type | condition_value | action_type | action_value | summon_count | override_drop | enemy_hp_coef | enemy_attack_coef |
|----|----------------|----------------|-------------|--------------|--------------|---------------|---------------|-------------------|
| _1 | ElapsedTime | 250 | SummonEnemy | e_gom_00402_general_n_Normal_Colorless（たこ焼き） | 1 | **500** | 2.0 | 2.4 |
| _9 | FriendUnitDead | **1** | **SwitchSequenceGroup** | **group1** | - | - | - | - |

> - `override_drop_battle_point`: この敵が撃破されたときのBP上書き値。MstEnemyStageParameterの `drop_battle_point` を無視してこの値（500）を使用する。最初の1体を撃破することへのインセンティブ設計
> - `FriendUnitDead=1`: 1体撃破したら発火するトリガー。このたこ焼きを倒すことがボス出現条件になっている
> - `SwitchSequenceGroup`: フェーズ遷移アクション。`group1` という名前のシーケンスグループを有効化する

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

> - `ElapsedTimeSinceGroupActivated`: グループ遷移（SwitchSequenceGroup）からの経過フレーム数。`condition_value=0` はグループ遷移と同時に即座に発火する
> - `summon_count=99, summon_interval=300`: 300フレーム（5秒）間隔で1体ずつ召喚し、最大99体まで出し続けるウェーブ設定。複数のウェーブエントリを重ねることで「ランダムに見える」不規則な大量召喚を実現している
> - `_2〜_8` の複数エントリが `condition_value=0`（即時）から `200`（約3.3秒後）まで分散しており、グループ切り替え直後に様々なタイミングで敵が押し寄せる設計

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

## 6. 全カラム設定例とデフォルト値

各テーブルの **1レコード全体** を示す。「仕様書の各例で説明したカラム以外」がどんな値になっているかを確認できる。

### 凡例
- **固定値**: 全レコードで常に同じ値が入る
- **NULL / デフォルト**: 未使用時に設定する値（空欄 or 規定値）
- **個別設定**: ステージ・キャラによって変わる値

---

### MstEnemyStageParameter（1レコード例: グエン Normal形態）

```
ENABLE,release_key,id,mst_enemy_character_id,character_unit_kind,role_type,color,sort_order,hp,damage_knock_back_count,move_speed,well_distance,attack_power,attack_combo_cycle,mst_unit_ability_id1,drop_battle_point,mstTransformationEnemyStageParameterId,transformationConditionType,transformationConditionValue
e,202509010,e_spy_00101_general_n_Normal_Colorless,enemy_spy_00101,Normal,Attack,Colorless,1,1000,,31,0.2,50,1,,200,,None,
```

| カテゴリ | カラム | 値 | 備考 |
|---------|--------|-----|------|
| 固定値 | ENABLE | `e` | 有効フラグ。`e`=enabled（常にe） |
| 固定値 | release_key | `202509010` | リリースキー |
| 固定値 | transformationConditionType | `None` | 変身条件。基本的に`None`（変身なし） |
| 個別設定 | sort_order | `1`, `2`… | 同一キャラのNormal/Boss間でのソート順 |
| NULL | damage_knock_back_count | （空） | Normalキャラはノックバック耐性なし。Bossは数値を設定 |
| NULL | mst_unit_ability_id1 | （空） | 特殊アビリティなしの場合は空欄 |
| NULL | mstTransformationEnemyStageParameterId | （空） | 変身先敵ID。変身なしなら空欄 |
| NULL | transformationConditionValue | （空） | 変身条件値。変身なしなら空欄 |

---

### MstAttack（1レコード例: グエン Normal攻撃）

```
ENABLE,release_key,id,mst_unit_id,unit_grade,attack_kind,killer_colors,killer_percentage,action_frames,attack_delay,next_attack_interval,asset_key
e,202509010,e_spy_00101_general_n_Normal_Colorless_Normal_00000,e_spy_00101_general_n_Normal_Colorless,0,Normal,,,60,25,50,
```

| カテゴリ | カラム | 値 | 備考 |
|---------|--------|-----|------|
| 固定値 | ENABLE | `e` | 有効フラグ |
| 固定値 | unit_grade | `0` | ユニットグレード（強化段階）。現状は常に`0` |
| NULL | killer_colors | （空） | 特定色へのキラー倍率対象色。設定なしなら空欄 |
| NULL | killer_percentage | （空） | キラー倍率（%）。`killer_colors`と対で設定 |
| NULL | asset_key | （空） | 攻撃モーションの差し替えアセットキー。基本空欄 |

> IDルール: `{mst_unit_id}_{attack_kind}_{連番5桁}` 例: `..._Normal_00000`, `..._Appearance_00001`

---

### MstAttackElement（1レコード例: グエン Normal攻撃 単体ヒット）

```
ENABLE,release_key,id,mst_attack_id,sort_order,attack_delay,attack_type,range_start_type,range_start_parameter,range_end_type,range_end_parameter,max_target_count,target,target_type,target_colors,target_roles,damage_type,hit_type,hit_parameter1,hit_parameter2,hit_effect_id,is_hit_stop,probability,power_parameter_type,power_parameter,effect_type,effective_count,effective_duration,effect_parameter,effect_value,effect_trigger_roles,effect_trigger_colors
e,202509010,e_spy_00101_general_n_Normal_Colorless_Normal_00000_1,..._Normal_00000,1,0,Direct,Distance,0,Distance,0.21,1,Foe,All,All,All,Damage,Normal,0,0,dageki_1,,100,Percentage,100.0,None,0,0,0.0,,,
```

| カテゴリ | カラム | 値 | 備考 |
|---------|--------|-----|------|
| 固定値 | ENABLE | `e` | 有効フラグ |
| 固定値 | range_start_type | `Distance` | 射程の開始計算方式。常に`Distance` |
| 固定値 | range_start_parameter | `0` | 射程の開始距離。通常0（自分の位置から） |
| 固定値 | target_type | `All` | ターゲット選択方式。基本`All` |
| 固定値 | target_colors | `All` | 対象カラー。基本`All`（全色対象） |
| 固定値 | target_roles | `All` | 対象ロール。基本`All`（全ロール対象） |
| 固定値 | probability | `100` | 発動確率（%）。基本100（必ず発動） |
| 個別設定 | hit_effect_id | `dageki_1` 等 | ヒット時のエフェクトID。攻撃種別ごとに設定 |
| 固定値(0) | hit_parameter1/2 | `0` | ヒット時の追加パラメータ。通常使わない |
| NULL | is_hit_stop | （空） | ヒットストップ有無。空欄=なし |
| NULL | effect_value / effect_trigger_* | （空） | 効果トリガー条件の詳細設定。基本未使用 |
| 0扱い | effective_count / effective_duration / effect_parameter | `0` | `effect_type=None` のときは全て`0`。効果あり時のみ設定 |

> IDルール: `{mst_attack_id}_{sort_order}` 例: `..._Normal_00000_1`, `..._Normal_00000_2`（多段ヒット時）

---

### MstInGame（1レコード例: normal_spy_00001）

```
ENABLE,id,mst_auto_player_sequence_id,mst_auto_player_sequence_set_id,bgm_asset_key,boss_bgm_asset_key,loop_background_asset_key,player_outpost_asset_key,mst_page_id,mst_enemy_outpost_id,mst_defense_target_id,boss_mst_enemy_stage_parameter_id,boss_count,normal_enemy_hp_coef,normal_enemy_attack_coef,normal_enemy_speed_coef,boss_enemy_hp_coef,boss_enemy_attack_coef,boss_enemy_speed_coef,release_key
e,normal_spy_00001,normal_spy_00001,normal_spy_00001,SSE_SBG_003_002,,spy_00005,,normal_spy_00001,normal_spy_00001,,1,,1.0,1.0,1,1.0,1.0,1,202509010
```

| カテゴリ | カラム | 値 | 備考 |
|---------|--------|-----|------|
| 固定値 | ENABLE | `e` | 有効フラグ |
| 固定値 | normal/boss_enemy_speed_coef | `1` | 速度倍率。現状は常に`1`（変更しない） |
| 同値 | mst_auto_player_sequence_id | MstInGame.idと同値 | sequence_set_idと同じ値を入れる |
| 個別設定 | bgm_asset_key | `SSE_SBG_003_002` 等 | ステージBGMのアセットキー |
| 個別設定 | loop_background_asset_key | `spy_00005` 等 | 背景スクロール画像のアセットキー |
| NULL | boss_bgm_asset_key | （空） | ボス出現時のBGM変化。変化なしなら空欄 |
| NULL | player_outpost_asset_key | （空） | プレイヤー拠点のカスタムアセット。基本空欄 |
| NULL | mst_defense_target_id | （空） | 防衛対象ID。防衛ステージ以外は空欄 |
| NULL | boss_count | （空） | ボス出現数の上限。空欄=制限なし |
| ダミー | boss_mst_enemy_stage_parameter_id | `1` | ボスなしステージはダミー値`1`を設定 |

---

### MstAutoPlayerSequence（1レコード例: normal_spy_00001_1）

```
ENABLE,id,sequence_set_id,sequence_element_id,condition_type,condition_value,action_type,action_value,summon_count,summon_interval,summon_animation_type,move_start_condition_type,move_stop_condition_type,move_restart_condition_type,aura_type,death_type,enemy_hp_coef,enemy_attack_coef,enemy_speed_coef,deactivation_condition_type,release_key
e,normal_spy_00001_1,normal_spy_00001,1,ElapsedTime,650,SummonEnemy,e_spy_00101_general_n_Normal_Colorless,1,0,None,None,None,None,Default,Normal,1.5,2.0,1.0,None,202509010
```

| カテゴリ | カラム | 値 | 備考 |
|---------|--------|-----|------|
| 固定値 | ENABLE | `e` | 有効フラグ |
| 固定値 | summon_animation_type | `None` | 召喚演出。基本`None` |
| 固定値 | move_start/stop/restart_condition_type | `None` | 召喚後の移動制御。基本全て`None`（通常移動） |
| 固定値 | aura_type | `Default` | オーラ表示設定。基本`Default` |
| 固定値 | death_type | `Normal` | 撃破演出。基本`Normal` |
| 固定値 | enemy_speed_coef | `1.0` | 速度倍率。現状は常に`1.0` |
| 固定値 | deactivation_condition_type | `None` | シーケンス無効化条件。基本`None` |
| 個別設定 | sequence_element_id | `1`, `2`… | 同一sequence_set内での順序番号 |

---

## 7. 参照テーブル・CSVパス

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
