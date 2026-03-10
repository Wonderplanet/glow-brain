# セルポ星人 (`enemy_dan_00001`)

## 基本情報

| 項目 | 値 |
|------|-----|
| キャラクターID | `enemy_dan_00001` |
| 名前 | セルポ星人 |
| 作品 | DAN（ダンダダン） |
| `mst_series_id` | `dan` |
| 図鑑表示 | あり (`is_displayed_encyclopedia = true`) |
| 幻体 | なし (`is_phantomized = false`) |
| 変身形態 | セルポ星人（変身）`enemy_dan_00101` |

> モモを誘拐した宇宙人。クローンで個体を増やす雄のみの種。生殖機能を取り戻すため、人間の女性を狙う。

---

## 特徴サマリー

- **雑魚〜ボスまで幅広い役割**で登場する汎用敵キャラ（Normal / Boss 両形態あり）
- **変身機能を持つ**唯一のdan系通常敵。HP50%または1%で `enemy_dan_00101`（セルポ星人変身形態）に変態する
- 難易度（汎用N/H/VH）やコンテンツごとに `role_type` が **Defense** から **Attack** に変化する
- 攻撃コンボサイクルは全バリアント `1`（最短コンボ）
- `move_speed` は 25〜40 の標準域で動作する
- **出現カラー**: Colorless / Red / Blue / Green / Yellow の5色をカバー
- **量で攻める雑魚敵**：通常は複数体〜大量（最大50体）で一斉召喚される

---

## コンテンツ別パラメータ一覧

### 汎用ノーマル（`general_n`）

| ID | unit_kind | role_type | color | HP | attack | move_speed | well_distance | 変身先 | 変身条件 |
|---|---|---|---|---|---|---|---|---|---|
| `e_dan_00001_general_n_Normal_Red` | Normal | Defense | Red | 10,000 | 50 | 34 | 0.24 | — | — |
| `e_dan_00001_general_n_trans_Normal_Colorless` | Normal | Attack | Colorless | 1,000 | 50 | 34 | 0.24 | `e_dan_00101_general_n_Normal_Colorless` | HP 50% |
| `e_dan_00001_general_n_trans_Normal_Red` | Normal | Attack | Red | 1,000 | 50 | 34 | 0.24 | `e_dan_00101_general_n_Normal_Red` | HP 50% |

> 変身バリアントは HP が 1,000 と低く設定されており、実質的に変身前の見せ場として機能する。

---

### 汎用ハード（`general_h`）

| ID | unit_kind | role_type | color | HP | attack | move_speed | well_distance | 変身先 | 変身条件 |
|---|---|---|---|---|---|---|---|---|---|
| `e_dan_00001_general_h_Normal_Colorless` | Normal | Defense | Colorless | 10,000 | 100 | 34 | 0.24 | — | — |
| `e_dan_00001_general_h_Normal_Red` | Normal | Defense | Red | 10,000 | 100 | 34 | 0.24 | — | — |
| `e_dan_00001_general_h_trans_Normal_Colorless` | Normal | Attack | Colorless | 10,000 | 100 | 34 | 0.24 | `e_dan_00101_general_h_Normal_Colorless` | HP 50% |
| `e_dan_00001_general_h_trans_Normal_Red` | Normal | Attack | Red | 10,000 | 100 | 34 | 0.24 | `e_dan_00101_general_h_Normal_Red` | HP 50% |

---

### 汎用ベリーハード（`general_vh`）

| ID | unit_kind | role_type | color | HP | attack | move_speed | well_distance | 変身先 | 変身条件 |
|---|---|---|---|---|---|---|---|---|---|
| `e_dan_00001_general_vh_Normal_Colorless` | Normal | Attack | Colorless | 10,000 | 100 | 34 | 0.24 | — | — |
| `e_dan_00001_general_vh_Normal_Green` | Normal | Attack | Green | 10,000 | 100 | 34 | 0.24 | — | — |
| `e_dan_00001_general_vh_Boss_Red` | Boss | Defense | Red | 10,000 | 100 | 34 | 0.24 | — | — |
| `e_dan_00001_general_vh_Boss_Colorless` | Boss | Attack | Colorless | 10,000 | 100 | 34 | 0.24 | — | — |
| `e_dan_00001_general_vh_trans_Normal_Red` | Normal | Attack | Red | 10,000 | 100 | 34 | 0.24 | `e_dan_00101_general_vh_Normal_Red` | HP **1%** |
| `e_dan_00001_general_vh_trans_Normal_Blue` | Normal | Technical | Blue | 10,000 | 100 | 34 | 0.24 | `e_dan_00101_general_vh_Normal_Blue` | HP **1%** |
| `e_dan_00001_general_vh_trans_Normal_Green` | Normal | Technical | Green | 10,000 | 100 | 34 | 0.24 | `e_dan_00101_general_vh_Normal_Green` | HP **1%** |
| `e_dan_00001_general_vh_trans_Boss_Blue` | Boss | Technical | Blue | 10,000 | 100 | **25** | 0.24 | `e_dan_00101_general_vh_Boss_Blue` | HP **1%** |

> VH の変身条件は HP **1%**。実質的に倒す直前の演出用であり、変身形態に移行する機会は少ない。
> Bossバリアントの変身形態は `move_speed` が 25 に低下している点に注意。

---

### メインクエスト GLO2（`mainquest_glo2`）

| ID | unit_kind | role_type | color | HP | attack | move_speed | well_distance | drop_bp |
|---|---|---|---|---|---|---|---|---|
| `e_dan_00001_mainquest_glo2_Normal_Blue` | Normal | Attack | Blue | 5,000 | 200 | 25 | 0.20 | 200 |

> HP は低め（5,000）だが攻撃力は 200 と高い。`well_distance` も 0.20 と最小域でウェル（ゴール）に近い位置まで来る。

---

### DAN1降臨（`dan1_advent`）

| ID | unit_kind | role_type | color | HP | attack | move_speed | well_distance | 変身先 | 変身条件 |
|---|---|---|---|---|---|---|---|---|---|
| `e_dan_00001_dan1_advent_Normal_Colorless` | Normal | Attack | Colorless | 30,000 | 200 | 25 | 0.25 | — | — |
| `e_dan_00001_dan1_advent_Boss_Colorless` | Boss | Attack | Colorless | 10,000 | 100 | 28 | 0.23 | — | — |
| `e_dan_00001_dan1_advent_trans1_Boss_Colorless` | Boss | Attack | Colorless | 10,000 | 100 | 28 | 0.23 | `e_dan_00101_dan1_advent_trans1_Normal_Colorless` | HP 50% |
| `e_dan_00001_dan1_advent_Normal_Yellow` | Normal | Technical | Yellow | 30,000 | 200 | 30 | 0.25 | — | — |

> Normal バリアントは HP 30,000 と大幅に強化。Yellow の Technical バリアントはコンテンツ難易度に合わせた特殊色。

---

### DAN1チャレンジ（`dan1challenge`）

| ID | unit_kind | role_type | color | HP | attack | move_speed | well_distance |
|---|---|---|---|---|---|---|---|
| `e_dan_00001_dan1challenge_Normal_Blue` | Normal | Attack | Blue | 10,000 | 100 | 28 | 0.23 |
| `e_dan_00001_dan1challenge_Normal_Green` | Normal | Attack | Green | 10,000 | 100 | **40** | 0.23 |

> Green バリアントの `move_speed = 40` はセルポ星人の全バリアント中**最速**。難易度アクセントとして使用される。

---

## 変身メカニズム

セルポ星人は一部バリアントで **HP閾値到達時に変身** する特殊機能を持つ。

```
変身トリガー: transformationConditionType = HpPercentage
変身先: enemy_dan_00101（セルポ星人 変身形態）
```

| コンテンツ | 変身条件 HP | 備考 |
|---|---|---|
| 汎用N（trans系） | 50% | 変身前HP=1,000と低く早期変身 |
| 汎用H（trans系） | 50% | 変身前後で差が出やすい |
| 汎用VH（trans系） | **1%** | 実質演出のみ（ほぼ変身しない） |
| DAN1降臨 trans1 | 50% | Boss形態のみ変身 |

### 変身連鎖トリガー（`FriendUnitTransform`）

VHの特定ステージでは、変身直後に大量の変身後セルポが援軍として召喚される連鎖構造が存在する。

```
FriendUnitTransform = 1 → e_dan_00101_general_vh_Normal_Blue を3体
FriendUnitTransform = 1 → e_dan_00101_general_vh_Normal_Blue を50体（大量投入）
FriendUnitTransform = 1 → e_dan_00101_general_vh_Normal_Blue を30体（Fall4演出）
```

> trans（変身あり）のセルポ星人が HP1% に到達すると変身し、その瞬間に変身後セルポ（enemy_dan_00101）が最大50体・30体と連続召喚される強力な連鎖構造。

---

## MstAutoPlayerSequence 召喚パターン分析

### 登場ステージ一覧

| ステージ種別 | sequence_set_id | 出現回数（エントリ数） | 合計召喚数 |
|---|---|---|---|
| Normal | normal_dan_00002 | 6 | 15 |
| Normal | normal_dan_00003 | 1 | 1 |
| Normal | normal_dan_00006 | 2 | 2 |
| Hard | hard_dan_00001 | 3 | 13 |
| Hard | hard_dan_00002 | 10 | 28 |
| Hard | hard_dan_00003 | 1 | 1 |
| Hard | hard_dan_00004 | 4 | 4 |
| Hard | hard_dan_00005 | 4 | 5 |
| Hard | hard_dan_00006 | 3 | 5 |
| VeryHard | veryhard_dan_00001 | 25 | 44 |
| VeryHard | veryhard_dan_00002 | 6 | 11 |
| VeryHard | veryhard_dan_00003 | 8 | 24 |
| VeryHard | veryhard_dan_00004 | 7 | 11 |
| VeryHard | veryhard_dan_00005 | 4 | 4 |
| VeryHard | veryhard_dan_00006 | 6 | 11 |
| イベント（チャレンジ）| event_dan1_challenge01_00001 | 6 | 61 |
| イベント（チャレンジ）| event_dan1_challenge01_00002 | 4 | 73 |
| イベント（チャレンジ）| event_dan1_challenge01_00004 | 2 | 2 |
| イベント（キャラゲット）| event_dan1_charaget01_00001〜00007 | 複数 | 複数 |
| レイド | raid_dan1_00001 | 10 | 49 |
| メインクエスト（glo2）| hard_glo2_00001, normal_glo2_00001 他 | 複数 | 複数 |

---

### Normal難度（normal_dan_00002）のシーケンス構造

```
[フェーズ1]
・DarknessKomaCleared = 3 → e_dan_00001_general_n_Normal_Red を3体（位置1.6/1.7/1.8）同時召喚
・ElapsedTime = 500 → glo_00001系を3体補助召喚

[フェーズ2（グループ切替: FriendUnitDead=1）]
・ElapsedTimeSinceSequenceGroupActivated = 50   → 4体召喚
・ElapsedTimeSinceSequenceGroupActivated = 500  → 4体召喚
・ElapsedTimeSinceSequenceGroupActivated = 1500 → 4体召喚
```

**特徴**: 闇コマ3個クリアをトリガーに少数から呼び出し、最初の仲間が倒されるとグループ移行して波状攻撃。

- トリガー: `DarknessKomaCleared`（闇コマ消し）
- グループ移行: `FriendUnitDead`

---

### Hard難度（hard_dan_00002）のシーケンス構造

```
[フェーズ1]
・DarknessKomaCleared = 1 → 3体を位置指定（0.7/1.7/1.8）で個別召喚
  + FoeEnterSameKoma → 追加1体

[グループ切替: FriendUnitDead=1]
[フェーズ2]
・ElapsedTimeSinceSequenceGroupActivated = 0    → ボス登場（c_dan_00101 系）
・ElapsedTimeSinceSequenceGroupActivated = 500  → 3体召喚
・ElapsedTimeSinceSequenceGroupActivated = 750  → 3体召喚
・FriendUnitDead = 2 → 6体召喚
・FriendUnitDead = 3 → 5体召喚
・FriendUnitDead = 4 → 6体召喚
```

**特徴**: 闇コマ1個で起動し、最初の仲間が倒れると即ボス登場。以降は仲間が倒されるたびに増援を大量展開。

- トリガー: `DarknessKomaCleared` → `FriendUnitDead`（仲間の死亡）

---

### VeryHard難度（veryhard_dan_00001）のシーケンス構造

```
[フェーズ1]
・InitialSummon    → e_dan_00001_general_vh_Normal_Colorless を1体（初期配置）
・ElapsedTime = 0  → e_dan_00001_general_h_Normal_Red を3体召喚

[グループ切替: OutpostDamage=1（拠点にダメージ）]
[フェーズ2（group1）]
・element_id=3: ElapsedTimeSinceSequenceGroupActivated=0
    → Boss_Colorless (HP係数35) を1体
    → Boss_Red (HP係数65) を2体（連続）
・element_id=4: Normal_Colorless を2体 + Red を1体（Fall演出）
・element_id=5: 3体以上を分散Fall
・element_id=6〜7: 継続召喚
・element_id=8: OutpostHpPercentage=60 → 追加強化召喚
・element_id=9〜12: FriendUnitDead = 4〜6 ごとに3〜6体の増援
```

**特徴**: 拠点に初めてダメージが入ることでグループ移行し、一気にボス2体が出現。以降は仲間の撃破数に応じて増援が切れ目なく続く長期持久戦の構造。

- トリガー: `InitialSummon` + `OutpostDamage` → `FriendUnitDead` 多段トリガー

---

### VeryHard難度（veryhard_dan_00002）- 変身形態特化ステージ

```
・ElapsedTime = 300    → e_dan_00001_general_vh_trans_Boss_Blue (Fall4演出) 1体 ← 変身ボスとして登場
・ElapsedTime = 1000   → e_dan_00001_general_vh_trans_Normal_Blue を4体
・ElapsedTime = 4000   → e_dan_00001_general_vh_trans_Normal_Blue を3体
・EnterTargetKomaIndex = 3/5 → 指定マス進入で各1体（Fall4/Fall演出）
・OutpostDamage = 1    → 拠点ダメージで1体

・FriendUnitTransform = 1 → e_dan_00101_general_vh_Normal_Blue を3体 ← 変身時に援軍
・FriendUnitTransform = 1 → e_dan_00101_general_vh_Normal_Blue を50体（大量投入）
・FriendUnitTransform = 1 → e_dan_00101_general_vh_Normal_Blue を30体（Fall4演出）
```

**特徴**: 変身前後の関係が核心。trans（変身あり）のセルポ星人が倒されるとHP1%で変身し、さらに `FriendUnitTransform` トリガーで変身した瞬間に変身後セルポ（enemy_dan_00101）が50体・30体と大量召喚される強力な連鎖構造。

---

### イベント（event_dan1_challenge01_00001）のシーケンス

```
・ElapsedTime = 200   → e_dan_00001_dan1challenge_Normal_Blue を3体
・ElapsedTime = 1000  → e_dan_00001_dan1challenge_Normal_Blue を50体
・OutpostHpPercentage = 99 → 5体 + ターボババア(e_dan_00201)を1体
・InitialSummon = 1/2  → 初期配置 各1体
```

**特徴**: チャレンジステージでは大量（50体）の召喚が特徴的。ターボババアと共演する場面も多い。

---

## 難易度別トリガー設計の差異

| 難易度 | 主要トリガー | 設計思想 |
|---|---|---|
| Normal | `DarknessKomaCleared` | 闇コマ消しで起動。少数→波状攻撃のシンプル構造 |
| Hard | `DarknessKomaCleared` → `FriendUnitDead` | ボスが即登場する緊張感。仲間の死亡で増援が連鎖 |
| VeryHard | `InitialSummon` + `OutpostDamage` → `FriendUnitDead` | 拠点ダメージ起点のボス2体即出現。長期持久戦 |

---

## 設計上のポイント

- **汎用N/H では Defense ロール** を担い、壁役として配置されやすい
- **変身バリアント (`trans_`)** は、単純な高HP配置の代替として変化に富んだ戦闘体験を提供
- **VH での HP1% 変身** は「変身する演出は出すが実際には変身させない」設計意図と読める
- `attack_combo_cycle = 1` 固定のため攻撃間隔は全て最短。攻撃力の数値差で難易度を調整している
- `drop_battle_point` は一律 100（ただしメインクエストGLO2のみ 200）
- **拠点ダメージ起点の大攻勢**（VeryHard以上）: 拠点に初めてダメージが入った瞬間にボス2体が即出現するデザイン
- **多様なカラー・役割タイプ**: Colorless/Red/Blue/Green/Yellow の5色、Defense/Attack/Technical の3ロールをカバーし、ステージ・難易度ごとに使い分けられる

---

## 統計的特徴

> 集計対象: `is_phantomized = 0` の通常敵キャラのみ。バリアント数ベースの平均値を使用。

### danシリーズ内での位置づけ（4体中）

danシリーズの通常敵は **セルポ星人・セルポ星人（変身）・ターボババア・アクロバティックさらさら** の4体。

| 指標 | セルポ星人の平均値 | シリーズ内順位 | 最小〜最大（シリーズ） |
|------|---:|:---:|---|
| move_speed | 32.0 | **4位 / 4体**（最低） | 32.0 〜 62.5 |
| HP | 10,773 | **4位 / 4体**（最低） | 10,773 〜 55,000 |
| attack_power | 107 | **4位 / 4体**（最低） | 107 〜 358 |
| drop_battle_point | 105 | **4位 / 4体**（最低） | 105 〜 367 |

> **全項目でシリーズ最低値**。セルポ星人は「ザコ敵の基準点」として機能しており、他3体がそれぞれどの程度強いかを測る比較軸になっている。

---

### 全作品内での位置づけ（全47体比較）

全作品の通常敵キャラ（47体）の平均値を元に四分位で評価。

| 指標 | セルポ星人の平均値 | 全体中央値（p50） | 全体評価 |
|------|---:|---:|---|
| move_speed | **32.0** | 36.0 | ▼ 下位25%水準（p25=32.0 と同等） |
| HP | **10,773** | 10,773 | ◆ ちょうど中央値 |
| attack_power | **107** | 167 | ▽ やや低め（p25=92〜p50=167 の間） |

```
全体の四分位分布（参考）
move_speed:   p25=32  ─────●（セルポ）─ p50=36  ── p75=40
HP:                    p25=5,472 ──── p50=10,773●（セルポ）── p75=55,271
attack:       p25=92 ── 107●（セルポ）─────── p50=167 ──────── p75=534
```

- **move_speed**: 全体の下位25%に位置する遅い敵。機動力で圧力をかけるタイプではない
- **HP**: 全体の中央値にあたる10,773。「普通のザコ」として標準的な耐久を持つ
- **attack_power**: 中央値（167）を大きく下回る107。攻撃力は低め寄りで、長時間生存させても脅威は小さい

---

## 関連データ

| 関連先 | ID | 概要 |
|---|---|---|
| 変身形態 | `enemy_dan_00101` | セルポ星人（変身） — HP50%/1%到達で変身先となるキャラ |
| 所属シリーズ | `dan` | ダンダダン |
