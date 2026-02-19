# event_jig1_savage_00001 インゲームデータ詳細解説

> 参照リポジトリ: `projects/glow-masterdata`
> リリースキー: 202601010
> 本ファイルはMstAutoPlayerSequenceが32行のイベントクエスト（savage）全データ設定を解説する

---

## 概要

**jig1シリーズのイベントクエスト・サベッジ（上級）難度**（砦破壊型バトル）。

- 砦のHP: `100,000` でダメージ有効（砦を壊してクリアするモード）
- BGM: `SSE_SBG_003_009`（ボスBGMなし）
- グループ: **デフォルト + w1〜w5 の6グループ**（w4→w1 タイマーループ構造）
- 使用する敵の種類: 4種類（`c_jig_00501` ボス1種 + `c_jig_00401` 中ボス1種 + `e_jig_00001` 雑魚2種）
- **スピードアタックルールあり**（早くクリアすると報酬獲得）
- 各グループに `groupchange_6: FriendUnitDead(1) → w5` が共通設定されており、最初の1体撃破でw5グループが並行起動される
- `boss_mst_enemy_stage_parameter_id` に `1` が設定されているが、ボスはすべてMstAutoPlayerSequenceから召喚する設計

---

## 関連テーブル設定

### MstInGame

| カラム | 値 |
|--------|-----|
| `id` | `event_jig1_savage_00001` |
| `mst_auto_player_sequence_set_id` | `event_jig1_savage_00001` |
| `bgm_asset_key` | `SSE_SBG_003_009` |
| `boss_bgm_asset_key` | （空） |
| `loop_background_asset_key` | （空） |
| `player_outpost_asset_key` | （空） |
| `mst_page_id` | `event_jig1_savage_00001` |
| `mst_enemy_outpost_id` | `event_jig1_savage_00001` |
| `mst_defense_target_id` | （空） |
| `boss_mst_enemy_stage_parameter_id` | `1` ← ボスはシーケンスで出す |
| `boss_count` | （空） |
| `normal_enemy_hp_coef` | `1.0` |
| `normal_enemy_attack_coef` | `1.0` |
| `normal_enemy_speed_coef` | `1` |
| `boss_enemy_hp_coef` | `1.0` |
| `boss_enemy_attack_coef` | `1.0` |
| `boss_enemy_speed_coef` | `1` |
| `release_key` | `202601010` |

### MstEnemyOutpost（敵砦）

| カラム | 値 | 意味 |
|--------|-----|------|
| `id` | `event_jig1_savage_00001` | |
| `hp` | `100,000` | 10万HP（破壊可能） |
| `is_damage_invalidation` | （空） | **ダメージ有効**（砦が壊れる砦破壊モード） |
| `outpost_asset_key` | （空） | |
| `artwork_asset_key` | `event_jig_0002` | 背景アートワーク |
| `release_key` | `202601010` | |

### MstPage + MstKomaLine（コマフィールド）

4行構成。2・3段目にコマ効果あり。

```
row=1  height=0.55  layout=1.0  (1コマ: 1.0)
  koma1: jig_00002  width=1.0  bg_offset=+0.3  effect=None

row=2  height=0.55  layout=5.0  (2コマ: 0.25 / 0.75)  ← コマ効果あり
  koma1: jig_00002  width=0.25  bg_offset=+0.7  effect=Poison      ← 毒（param1=4,500, param2=3.0, target=Player）
  koma2: jig_00002  width=0.75  bg_offset=+0.7  effect=Gust        ← 突風（param1=1,000, param2=0.5, target=Player）

row=3  height=0.55  layout=1.0  (1コマ: 1.0)           ← コマ効果あり
  koma1: jig_00002  width=1.0   bg_offset=+0.3  effect=Gust        ← 突風（param1=1,000, param2=0.5, target=Player）

row=4  height=0.55  layout=12.0  (4コマ: 0.25 / 0.25 / 0.25 / 0.25)
  koma1〜4: jig_00002  width=0.25  bg_offset=+0.0  effect=None（4つとも効果なし）
```

> **コマ効果はすべて `target=Player`（自軍への影響）**。プレイヤーキャラが通過すると毒ダメージや突風（移動阻害）を受ける。MstInGameI18nでも「毒コマ、突風コマが登場するぞ!」と明記されている。

### MstInGameI18n（バトル説明文）

**result_tips（バトルヒント）:**
> 強敵の『山田浅ェ門 桐馬』を倒すと『賊王 亜左 弔兵衛』の出現が止まるぞ!

**description（ステージ説明）:**
> 【属性情報】
> 黄属性の敵が登場するので緑属性のキャラは有利に戦うこともできるぞ!
> さらに、無属性の敵も登場するぞ!
>
> 【コマ効果情報】
> 毒コマ、突風コマが登場するぞ!
> 特性で毒ダメージ軽減や突風コマ無効化を持っているキャラを編成しよう!
>
> 【ギミック情報】
> 敵全体の攻撃UPをしてくる敵や
> 敵全体に被ダメージカットを付与する敵や
> 敵全体の体力を回復する攻撃をしてくる敵や
> 弱体化攻撃をしてくる敵や
> ノックバック無効化の特性を持つ敵が登場するぞ!
>
> 無属性の『賊王 亜左 弔兵衛』は倒しても時間経過で再度出現するぞ!
> 強敵の『山田浅ェ門 桐馬』を倒すと『賊王 亜左 弔兵衛』の出現が止まるぞ!
>
> また、このステージではスピードアタックルールがあるぞ!
> 早くクリアすると報酬ゲット!

---

## 使用する敵パラメータ（MstEnemyStageParameter）一覧

4種類の敵パラメータを使用。`c_` プレフィックスはキャラ個別ID、`e_` は汎用敵。
IDの命名規則: `{c_/e_}{キャラID}_jig1_savage_{kind}_{color}`

### カラム解説

| カラム名（略称） | DBカラム名 | 説明 |
|---------------|-----------|------|
| id | id | MstEnemyStageParameterの主キー |
| キャラID | mst_enemy_character_id | 紐付くキャラモデル・スキルの参照元 |
| kind | character_unit_kind | `Normal`（通常敵）/ `Boss`（ボス）。UIオーラ表示に影響 |
| role | role_type | 属性相性の役職（Attack/Technical/Defense/Support） |
| color | color | 属性色（Red/Yellow/Green/Blue/Colorless） |
| sort_order | sort_order | ゲーム内表示順 |
| base_hp | hp | ベースHP（`enemy_hp_coef` 乗算前の素値） |
| base_atk | attack_power | ベース攻撃力（`enemy_attack_coef` 乗算前の素値） |
| base_spd | move_speed | 移動速度（数値が大きいほど速い） |
| well_dist | well_distance | 攻撃射程（コマ単位） |
| combo | attack_combo_cycle | 攻撃コンボ数（1=単発） |
| knockback | damage_knock_back_count | 被攻撃時ノックバック回数（0=ノックバックなし） |
| ability | mst_unit_ability_id1 | 特殊アビリティID |
| drop_bp | drop_battle_point | 基本ドロップバトルポイント |

### 全4種類の詳細パラメータ

| MstEnemyStageParameter ID | 日本語名 | キャラID | kind | role | color | sort | base_hp | base_atk | base_spd | well_dist | combo | knockback | ability | drop_bp |
|--------------------------|---------|---------|------|------|-------|------|---------|---------|---------|-----------|-------|-----------|---------|---------|
| `c_jig_00501_jig1_savage_Boss_Yellow` | 山田浅ェ門 桐馬 | chara_jig_00501 | Boss | Support | Yellow | 15 | 10,000 | 100 | 35 | 0.21 | 4 | 2 | （空） | 300 |
| `c_jig_00401_jig1_savage_Normal_Colorless` | 賊王 亜左 弔兵衛 | chara_jig_00401 | Normal | Technical | Colorless | 18 | 1,000 | 100 | 30 | 0.31 | 3 | **0** | `enemy_ability_knockback_block` | 100 |
| `e_jig_00001_jig1_savage_Normal_Colorless` | 門神（無属性） | enemy_jig_00001 | Normal | Attack | Colorless | 21 | 1,000 | 100 | 27 | 0.21 | 1 | 1 | （空） | 50 |
| `e_jig_00001_jig1_savage_Normal_Yellow` | 門神（黄属性） | enemy_jig_00001 | Normal | Defense | Yellow | 22 | 1,000 | 100 | 27 | 0.21 | 1 | 3 | （空） | 50 |

> **実際のHP・ATKは `base × MstAutoPlayerSequence.enemy_hp_coef` で決まる。**
> 例: 亜左 弔兵衛（base_hp=1,000）を hp倍100 で出すと実HP = **100,000**（砦と同等）

### 敵パラメータの特性解説

#### 山田浅ェ門 桐馬（ボス）vs 賊王 亜左 弔兵衛（中ボス）比較

| 項目 | 桐馬（Boss_Yellow） | 亜左 弔兵衛（Normal_Colorless） |
|------|---------------------|-------------------------------|
| kind | **Boss**（オーラUI表示あり） | Normal（ボスオーラなし） |
| base_hp | 10,000 | 1,000 |
| role | Support（サポート型） | Technical（技巧型） |
| color | **Yellow**（緑属性キャラに弱い） | **Colorless**（属性無効） |
| base_spd | 35（速め） | 30 |
| well_dist | 0.21 | **0.31**（射程が長い） |
| combo | 4コンボ | 3コンボ |
| knockback | 2 | **0**（ノックバック不可！） |
| ability | なし | **`enemy_ability_knockback_block`** |
| drop_bp | 300 | 100 |

> **亜左 弔兵衛の最大の特徴**: `damage_knock_back_count=0` かつ `mst_unit_ability_id1=enemy_ability_knockback_block`。ノックバック攻撃を受けても後退しない。MstInGameI18nでも「ノックバック無効化の特性を持つ敵」と明記されている。

> **桐馬はデフォルトグループのみ登場**。result_tipsで「桐馬を倒すと亜左 弔兵衛の出現が止まる」とUIに表示される。実際にはデフォルトグループのみ桐馬が出現し、w1以降は亜左 弔兵衛のみが中ボスとして登場する。

#### 門神（jig雑魚）2種比較

| 項目 | 門神（無属性/Normal） | 門神（黄属性/Normal） |
|------|---------------------|---------------------|
| role | **Attack**（攻撃型） | **Defense**（防御型） |
| color | Colorless | Yellow |
| base_spd | 27（同じ） | 27（同じ） |
| combo | 1（単発） | 1（単発） |
| knockback | 1 | **3**（ノックバックされやすい） |
| drop_bp | 50 | 50 |

> 黄属性雑魚は `damage_knock_back_count=3` と高く、プレイヤーキャラが攻撃を受けると大きく弾き飛ばされやすい。緑属性キャラで挑むと属性有利で倒しやすい（description: 「黄属性の敵が登場するので緑属性のキャラは有利に戦うこともできるぞ!」）。

---

## グループ構造の全体フロー

```mermaid
flowchart LR
    START([バトル開始]) --> DEF

    DEF["デフォルトグループ<br/>elem 1〜4<br/>ボス 桐馬 + 亜左 弔兵衛 + 雑魚4体"]
    DEF -- "FriendUnitDead(1)<br/>累計1体撃破 → w5も並行起動" --> W5
    DEF -- "FriendUnitDead(2)<br/>累計2体撃破" --> W1

    W1["w1グループ<br/>elem 5〜8<br/>亜左 弔兵衛 + 雑魚群 + コマ侵入(2)"]
    W1 -- "FriendUnitDead(1)<br/>累計1体撃破 → w5も並行起動" --> W5
    W1 -- "FriendUnitDead(5)<br/>累計5体撃破" --> W2

    W2["w2グループ<br/>elem 9〜12<br/>亜左 弔兵衛 + 雑魚群（時間差）"]
    W2 -- "FriendUnitDead(1)<br/>累計1体撃破 → w5も並行起動" --> W5
    W2 -- "FriendUnitDead(9)<br/>累計9体撃破" --> W3

    W3["w3グループ<br/>elem 13〜16<br/>亜左 弔兵衛 + 雑魚群 + コマ侵入(3)"]
    W3 -- "FriendUnitDead(1)<br/>累計1体撃破 → w5も並行起動" --> W5
    W3 -- "FriendUnitDead(13)<br/>累計13体撃破" --> W4

    W4["w4グループ<br/>elem 17〜18<br/>雑魚のみ・亜左なし<br/>20秒でループ"]
    W4 -- "FriendUnitDead(1)<br/>累計1体撃破 → w5も並行起動" --> W5
    W4 -- "GroupActivated(2000)<br/>グループ起動20秒後" --> W1

    W5["w5グループ（終端・並行）<br/>elem 19〜22<br/>起動直後3体 + コマ侵入(5)<br/>砦HP70%以下で計6体追加投入"]

    style DEF fill:#6b7280,color:#fff
    style W1 fill:#3b82f6,color:#fff
    style W2 fill:#3b82f6,color:#fff
    style W3 fill:#f59e0b,color:#fff
    style W4 fill:#8b5cf6,color:#fff
    style W5 fill:#ef4444,color:#fff
```

> **Mermaid スタイルカラー規則**:
> - デフォルトグループ: `#6b7280`（グレー）
> - w1〜w2: `#3b82f6`（青）
> - w3: `#f59e0b`（橙）
> - w4: `#8b5cf6`（紫 = ループ起点直前グループ）
> - w5: `#ef4444`（赤 = 終端グループ）

> **w4→w1 のループ**: グループ起動から20,000ms（20秒）後に自動でw1へ戻る。以降はw1→w2→w3→w4→w1を繰り返す。撃破数条件ではなくタイマー条件によるループが特徴。

> **w5 は各グループから並行起動**: 最初の1体撃破時に `groupchange_6` でw5が起動。w5では `OutpostHpPercentage(70)` （砦HP70%以下）で追加敵を投入する。w5はgroupchangeを持たない終端グループ。

---

## 全32行の詳細データ（グループ単位）

### デフォルトグループ（elem 1〜4, groupchange_1, groupchange_6）

バトル開始と同時に雑魚を流し、4.5秒後に亜左 弔兵衛、6秒後にボス 桐馬が登場。**累計2体倒すとw1へ切り替わる**。

| id | elem | 条件 | アクション | 召喚数 | interval(ms) | aura | hp倍 | atk倍 | override_bp | 説明 |
|----|------|------|-----------|--------|-------------|------|------|------|------------|------|
| `_1` | 1 | ElapsedTime(600) | `c_jig_00501_...Boss_Yellow` | 1 | — | Default | 80 | 12 | 300 | バトル開始6,000ms後に桐馬（Boss_Yellow）1体出現。実HP=80,000 |
| `_2` | 2 | ElapsedTime(450) | `c_jig_00401_...Normal_Colorless` | 1 | — | Default | 70 | 7 | 100 | バトル開始4,500ms後に亜左 弔兵衛1体出現。実HP=70,000 |
| `_3` | 3 | ElapsedTime(100) | `e_jig_00001_...Normal_Yellow` | 2 | 750 | Default | 50 | 6 | 100 | バトル開始1,000ms後に門神（黄/Normal）を750ms間隔で2体召喚。実HP=50,000 |
| `_4` | 4 | ElapsedTime(0) | `e_jig_00001_...Normal_Colorless` | 2 | 1,000 | Default | 100 | 20 | 100 | バトル開始直後に門神（無属性/Normal）を1,000ms間隔で2体召喚。実HP=100,000 |
| `_5` | groupchange_6 | **FriendUnitDead(1)** | SwitchSequenceGroup(**w5**) | — | — | — | — | — | — | 累計1体撃破でw5グループを並行起動（action_delay=50ms） |
| `_6` | groupchange_1 | **FriendUnitDead(2)** | SwitchSequenceGroup(w1) | — | — | — | — | — | — | 累計2体撃破でw1グループへ切り替え（action_delay=50ms） |

**ポイント:**
- elem4（門神_無属性）がバトル開始直後に最初に出てくる。実HP=100,000 と高く、倒すのに時間がかかる設計
- elem1（桐馬ボス）は6秒遅れで登場。hp倍80で実HP=800,000（MstEnemyStageParameter base_hp=10,000 × hp_coef=80）の超強敵
- groupchange_6 と groupchange_1 は並行評価されるため、「1体撃破→w5起動」と「2体撃破→w1へ」が同時に設定されている

---

### w1グループ（elem 5〜8, groupchange_2, groupchange_6）

切り替え直後に亜左 弔兵衛が1体入場し、時間差で雑魚群が続く。累計5体でw2へ。

| id | elem | 条件 | アクション | 召喚数 | interval(ms) | summon_pos | aura | hp倍 | atk倍 | override_bp | defeated_score | 説明 |
|----|------|------|-----------|--------|-------------|------|------|------|------|------------|-------|------|
| `_7` | 5 | GroupActivated(0) | `c_jig_00401_...Normal_Colorless` | 1 | — | — | Default | 80 | 7 | 100 | 0 | グループ開始直後に亜左 弔兵衛1体出現。実HP=80,000 |
| `_8` | 6 | GroupActivated(300) | `e_jig_00001_...Normal_Yellow` | 3 | 750 | — | Default | 110 | 6 | 75 | 0 | グループ開始3,000ms後に門神（黄/Normal）を750ms間隔で3体召喚。実HP=110,000 |
| `_9` | 7 | GroupActivated(500) | `e_jig_00001_...Normal_Colorless` | 3 | 750 | — | Default | 60 | 20 | 75 | 0 | グループ開始5,000ms後に門神（無属性/Normal）を750ms間隔で3体召喚。実HP=60,000 |
| `_10` | 8 | **EnterTargetKomaIndex(2)** | `e_jig_00001_...Normal_Colorless` | 2 | 50 | **0.8** | Default | 60 | 20 | 75 | **50** | プレイヤーが**コマ2番目に到達**したとき、門神（無属性/Normal）をpos=0.8にFall0で2体追加 |
| `_11` | groupchange_6 | **FriendUnitDead(1)** | SwitchSequenceGroup(**w5**) | — | — | — | — | — | — | — | — | 累計1体撃破でw5グループ起動（action_delay=50ms） |
| `_12` | groupchange_2 | **FriendUnitDead(5)** | SwitchSequenceGroup(w2) | — | — | — | — | — | — | — | — | 累計5体撃破でw2グループへ切り替え（action_delay=50ms） |

**ポイント:**
- elem8（`EnterTargetKomaIndex(2)`）: プレイヤーが前進してコマ2番目に入ったとき仕掛けが発動。後方（pos=0.8）からFall0（落下演出）で2体追加。`defeated_score=50` が設定されており、これらを倒すとスコア加点される
- 黄雑魚のhp倍がデフォルト(50)から110に大幅上昇。ウェーブ進行とともに圧力が増す設計

---

### w2グループ（elem 9〜12, groupchange_3, groupchange_6）

時間差で亜左 弔兵衛 + 黄雑魚 + 無属性雑魚を順次投入。累計9体でw3へ。elem11の15秒遅延が特徴的。

| id | elem | 条件 | アクション | 召喚数 | interval(ms) | hp倍 | atk倍 | override_bp | 説明 |
|----|------|------|-----------|--------|-------------|------|------|------------|------|
| `_13` | 9 | GroupActivated(0) | `c_jig_00401_...Normal_Colorless` | 1 | — | 90 | 7 | 100 | グループ開始直後に亜左 弔兵衛1体。実HP=90,000 |
| `_14` | 10 | GroupActivated(300) | `e_jig_00001_...Normal_Yellow` | 2 | 50 | 120 | 6 | 50 | グループ開始3,000ms後に門神（黄/Normal）を50ms間隔で2体召喚。実HP=120,000 |
| `_15` | 11 | GroupActivated(1500) | `e_jig_00001_...Normal_Yellow` | 3 | 1,200 | 120 | 6 | 50 | グループ開始15,000ms後に門神（黄/Normal）を1,200ms間隔で3体召喚（大幅遅延・長間隔） |
| `_16` | 12 | GroupActivated(600) | `e_jig_00001_...Normal_Colorless` | 3 | 1,800 | 65 | 20 | 50 | グループ開始6,000ms後に門神（無属性/Normal）を1,800ms間隔で3体召喚（かなり長い間隔） |
| `_17` | groupchange_6 | **FriendUnitDead(1)** | SwitchSequenceGroup(**w5**) | — | — | — | — | — | w5グループ起動（action_delay=50ms） |
| `_18` | groupchange_3 | **FriendUnitDead(9)** | SwitchSequenceGroup(w3) | — | — | — | — | — | 累計9体撃破でw3へ（action_delay=500ms） |

**ポイント:**
- elem11: 15秒後に3体を1.2秒間隔で召喚（合計約17.4秒かかる）。最後まで撃破数が溜まりにくい設計
- elem12: 無属性雑魚を1.8秒間隔（3体で計約3.6秒）と長い間隔で送り込む。ノックバックで押せない囮として機能する
- groupchange_3 の action_delay=500ms: 500ms遅れてからw3に移行（演出的な猶予）

---

### w3グループ（elem 13〜16, groupchange_4, groupchange_6）

コマ侵入トリガーが追加される。累計13体でw4へ。亜左 弔兵衛が実HP=100,000に達し砦と同等の強さになる。

| id | elem | 条件 | アクション | 召喚数 | interval(ms) | hp倍 | atk倍 | override_bp | 説明 |
|----|------|------|-----------|--------|-------------|------|------|------------|------|
| `_19` | 13 | GroupActivated(0) | `c_jig_00401_...Normal_Colorless` | 1 | — | 100 | 7 | **200** | グループ開始直後に亜左 弔兵衛1体。実HP=100,000（砦と同HP）。bp=200に倍増 |
| `_20` | 14 | GroupActivated(300) | `e_jig_00001_...Normal_Yellow` | 3 | 750 | 90 | 6 | 50 | グループ開始3,000ms後に門神（黄/Normal）を750ms間隔で3体召喚。実HP=90,000 |
| `_21` | 15 | GroupActivated(500) | `e_jig_00001_...Normal_Colorless` | 3 | 750 | 130 | 20 | 50 | グループ開始5,000ms後に門神（無属性/Normal）を750ms間隔で3体召喚。実HP=130,000 |
| `_22` | 16 | **EnterTargetKomaIndex(3)** | `e_jig_00001_...Normal_Colorless` | 2 | 50 | 130 | 20 | 50 | プレイヤーが**コマ3番目に到達**したとき、門神（無属性/Normal）2体追加 |
| `_23` | groupchange_6 | **FriendUnitDead(1)** | SwitchSequenceGroup(**w5**) | — | — | — | — | — | w5グループ起動（action_delay=50ms） |
| `_24` | groupchange_4 | **FriendUnitDead(13)** | SwitchSequenceGroup(w4) | — | — | — | — | — | 累計13体撃破でw4へ（action_delay=50ms） |

**ポイント:**
- elem13: 亜左 弔兵衛のhp倍が100に達し、実HP=100,000（砦と同等）。さらにbp=200に増加している（w2以前の倍）
- elem16（`EnterTargetKomaIndex(3)`）: コマ3番目到達トリガー。w1のコマ2番目より前進した位置で追加敵が発動。プレイヤーが砦に迫るほど敵が増える設計

---

### w4グループ（elem 17〜18, groupchange_5, groupchange_6）← ループ起点フェーズ

**亜左 弔兵衛が出現しない唯一のグループ（雑魚のみ）**。20秒経過するとw1へループ。

| id | elem | 条件 | アクション | 召喚数 | interval(ms) | hp倍 | atk倍 | override_bp | 説明 |
|----|------|------|-----------|--------|-------------|------|------|------------|------|
| `_25` | 17 | GroupActivated(500) | `e_jig_00001_...Normal_Yellow` | 2 | 150 | 90 | 6 | 50 | グループ開始5,000ms後に門神（黄/Normal）を150ms間隔で2体召喚 |
| `_26` | 18 | GroupActivated(0) | `e_jig_00001_...Normal_Colorless` | 2 | 50 | 140 | 20 | 50 | グループ開始直後に門神（無属性/Normal）を50ms間隔で2体召喚。実HP=140,000 |
| `_27` | groupchange_6 | **FriendUnitDead(1)** | SwitchSequenceGroup(**w5**) | — | — | — | — | — | w5グループ起動（action_delay=50ms） |
| `_28` | groupchange_5 | **GroupActivated(2000)** | SwitchSequenceGroup(**w1**) | — | — | — | — | — | グループ起動20,000ms（20秒）後にw1へ**ループ** |

**ポイント:**
- w4では亜左 弔兵衛が出現しない唯一のグループ。雑魚のみで構成されるため撃破数が稼ぎやすい局面
- groupchange_5 の条件が `ElapsedTimeSinceSequenceGroupActivated(2000)` = 20,000ms = 20秒後に自動でw1へ戻る。**撃破数に関係なくタイマーでループ**する
- 無属性雑魚のhp倍が140に達しており、実HP=140,000（砦を超えるHP）

---

### w5グループ（elem 19〜22）← 砦ダメージ強化・終端フェーズ

最初の1体撃破で各グループから並行起動されるグループ。**砦HP70%以下になると追加敵を大量投入**。groupchangeなし（終端グループ）。

| id | elem | 条件 | アクション | 召喚数 | interval(ms) | summon_pos | hp倍 | atk倍 | override_bp | defeated_score | 説明 |
|----|------|------|-----------|--------|-------------|------|------|------|------------|-------|------|
| `_29` | 19 | GroupActivated(0) | `e_jig_00001_...Normal_Colorless` | 3 | 500 | — | 90 | 20 | 50 | 0 | グループ起動直後に門神（無属性/Normal）を500ms間隔で3体召喚 |
| `_30` | 20 | **EnterTargetKomaIndex(5)** | `e_jig_00001_...Normal_Colorless` | 3 | 50 | **0.8** | 90 | 20 | 50 | **50** | プレイヤーが**コマ5番目に到達**したとき、Fall0でpos=0.8から3体追加 |
| `_31` | 21 | **OutpostHpPercentage(70)** | `e_jig_00001_...Normal_Yellow` | 3 | 50 | — | **150** | 6 | 50 | 0 | 砦HP70%以下になったとき門神（黄/Normal）を50ms間隔で3体。実HP=150,000 |
| `_32` | 22 | **OutpostHpPercentage(70)** | `e_jig_00001_...Normal_Colorless` | 3 | 50 | — | 90 | 20 | 50 | 0 | 砦HP70%以下になったとき門神（無属性/Normal）を50ms間隔で3体 |

**ポイント:**
- elem19: グループ起動後すぐに雑魚3体を流す（グループ起動直後の即時攻勢）
- elem20（`EnterTargetKomaIndex(5)`）: コマ5番目（最前線に近い）到達で後方からFall0で3体追加。`defeated_score=50` が設定
- elem21,22（`OutpostHpPercentage(70)`）: 砦が70%以下になると同時に黄＋無属性雑魚を計6体投入。砦が削られるほど守りが固くなる逆比例設計
- **w5にはgroupchangeがない**（終端グループ）。一度起動されたら次のグループへの遷移はない

---

## グループ切り替えまとめ表

| 切り替え | 条件 | 遷移先 | action_delay |
|---------|------|--------|-------------|
| デフォルト → w5（並行） | **FriendUnitDead(1)** | w5（並行起動） | 50ms |
| デフォルト → w1 | **FriendUnitDead(2)** | w1 | 50ms |
| w1 → w5（並行） | **FriendUnitDead(1)** | w5（並行起動） | 50ms |
| w1 → w2 | **FriendUnitDead(5)** | w2 | 50ms |
| w2 → w5（並行） | **FriendUnitDead(1)** | w5（並行起動） | 50ms |
| w2 → w3 | **FriendUnitDead(9)** | w3 | 500ms |
| w3 → w5（並行） | **FriendUnitDead(1)** | w5（並行起動） | 50ms |
| w3 → w4 | **FriendUnitDead(13)** | w4 | 50ms |
| w4 → w5（並行） | **FriendUnitDead(1)** | w5（並行起動） | 50ms |
| w4 → w1（ループ） | **GroupActivated(2000)** = 20秒経過 | w1 | — |

各ウェーブで倒すべき目安:
- デフォルト: 2体（すぐw1へ）
- w1: 5-2=3体（でw2へ）
- w2: 9-5=4体（でw3へ）
- w3: 13-9=4体（でw4へ）
- w4: 撃破数条件なし（20秒タイマーでw1ループ）

---

## スコア体系

バトルポイントは `override_drop_battle_point`（MstAutoPlayerSequence設定値）が優先される。

| 敵の種類 | override_bp（獲得バトルポイント） | 備考 |
|---------|----------------------------------|------|
| ボス 桐馬（Boss_Yellow） | 300 | デフォルトグループのみ |
| 中ボス 亜左 弔兵衛（Normal_Colorless） | 100〜200 | ウェーブが進むほど増加（w3で200） |
| 門神（両種）デフォルト | 100 | デフォルトグループのみ |
| 門神（両種）w1以降 | 50〜75 | w1は75、w2以降は50 |

| コマ侵入トリガー敵 | `defeated_score` | 特記 |
|-------------------|-----------------|------|
| w1 コマ2到達追加敵 | 50 | EnterTargetKomaIndex(2)、Fall0で後方から落下 |
| w5 コマ5到達追加敵 | 50 | EnterTargetKomaIndex(5)、Fall0で後方から落下 |

> **スピードアタックルール**: クリアまでの時間が早いほど追加報酬がある（`result_tips` および description 記載）。砦HP 100,000 を早期に削ることが高報酬の鍵。

---

## この設定から読み取れる設計パターン

### 1. 砦破壊型 + スピードアタックの二重目標

`is_damage_invalidation` が空（通常ダメージ有効）で砦HP=100,000。プレイヤーは砦を**破壊することが目的**。さらにスピードアタックルールで「速さ」も評価される二重の目標設計。砦HPを早く削るほど高評価だが、`OutpostHpPercentage(70)` で砦が30%削られると大量援軍が来るため、スピードと防御のトレードオフが生じる。

### 2. w5 の並行グループ設計

各ウェーブで最初の1体撃破と同時に `w5` グループが起動する。w5は `OutpostHpPercentage(70)` トリガーを持ち、砦が30%削られると大量の援軍を送り込む。砦を攻め続けるほど防衛が強化されていく逆比例設計。これによりプレイヤーは「素早く砦を削りつつも、増援をさばく」という高度な戦略が求められる。

### 3. 中ボス（亜左 弔兵衛）のhp倍率段階的スケーリング

亜左 弔兵衛の `enemy_hp_coef` がウェーブごとに70→80→90→100と増加し、砦HP（100,000）に近づいていく。プレイヤーへの圧力がウェーブ経過で線形に上昇する設計。

```
デフォルト: 亜左 弔兵衛 hp倍70（実HP=70,000）  桐馬 hp倍80（実HP=800,000）
w1:         亜左 弔兵衛 hp倍80（実HP=80,000）  雑魚 hp倍60〜110
w2:         亜左 弔兵衛 hp倍90（実HP=90,000）  雑魚 hp倍65〜120
w3:         亜左 弔兵衛 hp倍100（実HP=100,000 = 砦と同等）  雑魚 hp倍90〜130
w4:         亜左 弔兵衛 なし  雑魚 hp倍90〜140
w5（砦70%以下）: 雑魚 hp倍90〜150
```

### 4. ノックバック無効の中ボス設計

亜左 弔兵衛は `damage_knock_back_count=0` + `mst_unit_ability_id1=enemy_ability_knockback_block` を持ち、いかなるノックバック攻撃も無効。倒しても時間経過で再出現する仕様（description: 「無属性の『賊王 亜左 弔兵衛』は倒しても時間経過で再度出現するぞ!」）。射程0.31（桐馬の0.21より長い）と合わせて、砦前の「壁役」として機能する設計。

### 5. コマ侵入トリガーによる進撃抑制

`EnterTargetKomaIndex` 条件（w1のコマ2番目・w3のコマ3番目・w5のコマ5番目）でプレイヤーが前進するたびに後方から援軍が落下してくる（Fall0アニメーション）。砦に近づくほど敵が増える仕組みで、無計画な突撃を抑制する設計。w5のコマ5番目トリガーは最前線（砦直前）に相当し、最後の壁として機能する。

### 6. w4のタイマーループによる強制リセット

w4のみ撃破数ではなく**20秒タイマー**（`ElapsedTimeSinceSequenceGroupActivated(2000)` = 20,000ms）でw1に戻る。w4では亜左 弔兵衛が出現しないため倒せる敵が少なく、意図的にグループ進行が停滞しやすく設計されている。タイマーによって無限ループを防ぎ、ウェーブが強制的にリセットされる。このw4が「ループ起点直前グループ」として機能しており、長期戦でのゲーム継続性を担保している。
