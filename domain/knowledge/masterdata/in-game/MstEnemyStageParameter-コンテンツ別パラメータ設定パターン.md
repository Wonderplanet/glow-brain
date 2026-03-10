# MstEnemyStageParameter コンテンツ別パラメータ設定パターン

作成日: 2026-03-10

---

## 概要

`MstEnemyStageParameter` は、同一キャラクター（`mst_enemy_character_id`）に対して、
使用するコンテンツや属性・難易度・ギミックの違いごとに **複数のデータが作成される**。

本ドキュメントでは、どのような軸でデータが分かれているか、
および glo 以外の作品キャラ5体のコンテンツ別設定差を記録する。

---

## 1. データの区別軸（全体）

### 1-1. IDの命名パターン

```
{e/c}_{キャラ略称}_{コンテンツキーワード}[_{修飾語}]_{Normal/Boss}_{color}

例:
e_dan_00001_general_vh_trans_Normal_Blue
└─────────┘ └─────┘ └─┘ └───┘  └──────┘ └──┘
  敵キャラ   汎用  VH 変身前  Normal種  Blue属性

c_aka_00001_general_vh_Boss_Red
└─────────┘ └──────────┘ └──┘ └──┘
 プレイアブル  汎用VH難易度  Boss Red
```

- `e_` プレフィックス: 敵専用キャラ（567件）
- `c_` プレフィックス: プレイアブルキャラがNPCとして登場（498件）

---

### 1-2. 7つの区別軸

| 軸 | カラム / IDキーワード | 値の例 |
|---|---|---|
| **① コンテンツ種別** | ID中間部 | general / savage / advent / challenge / charaget / mainquest / raid / tutorial |
| **② 難易度** | IDキーワード | `_n_`（ノーマル）/ `_h_`（ハード）/ `_vh_`（ベリーハード） |
| **③ 属性** | `color` / ID末尾 | Colorless / Blue / Red / Yellow / Green |
| **④ ユニット種別** | `character_unit_kind` | Normal / Boss / AdventBattleBoss |
| **⑤ サイズ** | IDキーワード `_big_` | 通常サイズ vs 大サイズ |
| **⑥ 変身前後** | IDキーワード `_trans_` | 変身前（`mstTransformationEnemyStageParameterId` に変身後IDを参照）|
| **⑦ キャラ固有コンテキスト** | ID中間部 | `_osh_`, `_kai_`, `_sur_` 等 特定施策・対戦相手を識別 |

---

### 1-3. コンテンツ種別ごとのレコード数（全体）

| コンテンツキーワード | 件数 | 説明 |
|---|---|---|
| `general_vh` | 224件 | 汎用コンテンツ・ベリーハード難易度 |
| `advent` | 124件 | アドベントバトル |
| `challenge` | 117件 | チャレンジコンテンツ |
| `general`（その他） | 95件 | 汎用・特定コンテキスト付き |
| `savage` | 90件 | サヴェージコンテンツ |
| `charaget` | 76件 | キャラゲットコンテンツ |
| `general_n` | 57件 | 汎用・ノーマル難易度 |
| `general_h` | 50件 | 汎用・ハード難易度 |
| `mainquest` | 33件 | メインクエスト専用 |
| `1d1c` | 25件 | 1day1coin系コンテンツ |
| `general_ori` | 21件 | 汎用オリジナル（独自難易度番号付き） |
| `raid` | 12件 | レイドコンテンツ |
| `tutorial` | 10件 | チュートリアル |
| `l05anniv` | 34件 | 5周年記念イベント |
| `event` | 4件 | その他イベント |

---

### 1-4. コンテンツ別の共通パラメータ傾向

| コンテンツ種別 | HP傾向 | ATK傾向 | 速度傾向 | 備考 |
|---|---|---|---|---|
| `general_n` | 中（〜10,000） | 低 | 標準 | ベース難易度 |
| `general_h` | 中 | 中 | 標準 | well_distance が増えることがある |
| `general_vh` | 高 | 高 | 高め | Boss種が登場することが多い |
| `general_ori / as4 / ori4` | 極大（100万〜） | 高 | やや低下 | 特殊高難易度コンテンツ専用 |
| `advent` | 低（1,000〜10,000） | 低〜中 | 標準 | AdventBattleBoss 種が出現することあり |
| `challenge` | 中〜高 | 中 | 標準〜高 | combo増加・well_distance調整で難易度付加 |
| `savage` | 中 | 中 | 標準 | combo=0 の場合あり |
| `mainquest` | 低め | 低め | 低め | ストーリー向けに弱め設定 |
| `l05anniv` | 低（1,000） | 最低（100） | 標準 | combo=0（演出専用、実質戦闘しない） |

---

## 2. キャラ別 コンテンツ設定の詳細（glo以外5体）

対象: glo 以外の作品IDを持つキャラ上位5体（2026-03-10 時点）

| キャラID | 件数 | 特徴 |
|---|---|---|
| `enemy_dan_00001` | 22件 | 変身ギミックが最多 |
| `enemy_sur_00101` | 20件 | 高速移動・コンボ変動 |
| `enemy_mag_00101` | 18件 | 超高速（speed=100）・高HP系列 |
| `enemy_kai_00101` | 17件 | 属性によって速度が異なる |
| `enemy_kai_00001` | 16件 | 高HP Bossと超低速の対比 |

---

### 2-1. enemy_dan_00001（dan作品）— 変身ギミック特化

**全22件中9件が変身（trans）データ。** 変身前は `mstTransformationEnemyStageParameterId` で変身後（`enemy_dan_00101`）を参照し、HP残量%で変身トリガー（`transformationConditionType = HpPercentage`）。

| コンテンツ | kind | role | color | HP | ATK | 速度 | combo | trans |
|---|---|---|---|---|---|---|---|---|
| `general_n` | Normal | Defense | Colorless/Red | 10,000 | 50 | 34 | 1 | - |
| `general_n`（trans前） | Normal | Attack | Colorless/Red | **1,000** | 50 | 34 | 1 | → dan_00101 |
| `general_h` | Normal | Defense | Colorless/Red | 10,000 | 100 | 34 | 1 | - |
| `general_h`（trans前） | Normal | Attack | Colorless/Red | 10,000 | 100 | 34 | 1 | → dan_00101 |
| `general_vh`（Normal） | Normal | Attack/Technical | Colorless/Green/Blue/Red | 10,000 | 100 | 34 | 1 | -/→ dan_00101 |
| `general_vh`（Boss） | Boss | Attack/Defense/Technical | Colorless/Red/Blue | 10,000 | 100 | 25〜34 | 1 | -/→ dan_00101 |
| `advent`（Normal） | Normal | Attack/Technical | Colorless/Yellow | 30,000 | 200 | 25〜30 | 1 | - |
| `advent`（Boss） | Boss | Attack | Colorless | 10,000 | 100 | 28 | 1 | → dan_00101 |
| `challenge` | Normal | Attack | Blue/Green | 10,000 | 100 | 28〜40 | 1 | - |
| `mainquest_glo2` | Normal | Attack | Blue | **5,000** | 200 | **25** | 1 | - |

**ポイント:**
- trans前のHP を意図的に低く設定（1,000）→ すぐ変身させる演出
- `general_n / h / vh` の3段階難易度体系がすべて揃っている
- `mainquest` は HP・速度ともに全コンテンツ中最低（ストーリー向け弱め設定）

---

### 2-2. enemy_sur_00101（sur作品）— 高速移動・コンボ変動

**move_speed が全体で最大65と高速。** challenge のみ `attack_combo_cycle=4`、glo2_savage01 は `attack_combo_cycle=0` と、コンテンツによってコンボ設定が大きく異なる。

| コンテンツ | kind | role | color | HP | ATK | 速度 | combo |
|---|---|---|---|---|---|---|---|
| `aobaget` | Normal | Technical | Blue/Colorless/Red | **1,000** | **100** | 42 | 1 |
| `sur1_advent` | Normal | Defense | Red | **1,000** | **100** | 40 | 1 |
| `general`（Normal） | Normal | Attack/Defense | Blue/Colorless/Green | 3,000〜22,000 | 100〜300 | 35〜50 | 1 |
| `general`（Boss） | Boss | Attack | Green | **400,000** | **850** | 45 | 1 |
| `general_sur_vh` | Normal | Defense/Attack | Blue/Green/Red | 10,000〜110,000 | 700〜**2,000** | 45〜**65** | 1 |
| `savege` | Normal | Technical/Defense | Blue/Colorless/Yellow | 10,000 | 300 | 30〜32 | 1 |
| `1d1c` | Normal | Attack | Colorless | 10,000 | 300 | 30 | 1 |
| `challenge` | Normal | Attack | Red | 50,000 | 300 | 30 | **4** |
| `glo2_advent` | Normal | Defense | Red | 10,000 | 300 | 30 | 1 |
| `glo2_savage01` | Normal | Defense | Colorless/Red | 10,000 | 300 | 30 | **0** |

**ポイント:**
- `challenge` のみ `attack_combo_cycle=4`（連続攻撃ギミック）
- `glo2_savage01` の `attack_combo_cycle=0`（攻撃しない待機ユニット）
- `aobaget` / `sur1_advent` は HP=1,000・ATK=100（キャラ紹介用の超弱設定）
- `general` Boss版が HP:400,000 と突出（同コンテンツ内で最大）

---

### 2-3. enemy_mag_00101（mag作品）— 超高速移動・高HP系列

**move_speed=100 が基本値**（全データ中断トツ最速）。`general_as4` / `general_ori4` は HP が桁違いに高い高難易度専用エリート版。

| コンテンツ | kind | role | color | HP | ATK | 速度 | well_dist |
|---|---|---|---|---|---|---|---|
| `challange` | Normal | Technical | Blue | 1,000 | 400 | 80 | 0.11 |
| `mag1_advent`（全5色） | Normal | Technical | 全属性 | 1,000 | 400 | 80 | 0.11 |
| `general` | Normal | Attack | Blue/Colorless | 10,000〜20,000 | 800〜1,500 | **100** | 0.11 |
| `general_h` | Normal | Attack | Blue | 8,000 | 500 | **100** | **0.30** |
| `general_vh` | Normal | Attack | Blue | 25,000 | 2,000 | **100** | 0.11 |
| `general2` | Normal | Attack | Blue/Colorless | 20,000〜30,000 | 400〜700 | **100** | 0.11 |
| `general2_h` | Normal | Attack | Blue | 8,000 | 500 | **100** | 0.30 |
| `general_as4` | Normal | Technical | Blue/Colorless | **200,000** | 1,500 | 75 | 0.11 |
| `general_ori4` | Normal | Technical | Blue/Colorless | **700,000** | 1,000 | 80 | 0.11 |

**ポイント:**
- `general_h` / `general2_h` だけ `well_distance=0.30`（遠距離攻撃設定）
- `general_as4`（HP 200,000）→ `general_ori4`（HP 700,000）の段階的なエリート強化系列
- `mag1_advent` のみ全5属性が揃っている（他コンテンツは青・白のみ）
- Boss種が1件も存在しない（Normal専用キャラ）

---

### 2-4. enemy_kai_00101（kai作品）— 属性ごとに速度が異なる

**`eventchallenge01` は属性によって `move_speed` が大きく異なる**（Blue:23 / Colorless:21 / Green:35 / Red:20 / Yellow:31）。

| コンテンツ | kind | role | color | HP | ATK | 速度 | combo |
|---|---|---|---|---|---|---|---|
| `l05anniv_advent` | Normal | Attack | Colorless | **1,000** | **100** | 28 | **0** |
| `glo2_advent` | Normal | Attack | Red | 10,000 | **100** | 21 | 1 |
| `kai1_advent`（Normal） | Normal | Defense | Colorless | 10,000 | 500 | 21 | 1 |
| `kai1_advent`（Boss） | **AdventBattleBoss** | Attack | Blue/Red | 10,000 | 500 | 25〜28 | 1 |
| `eventchallenge01`（全5色） | Normal | Attack | Blue/Colorless/Green/Red/Yellow | 10,000 | 500 | **20〜35** | 1 |
| `general` | Normal | Defense/Attack | Colorless/Green/Yellow | 25,000〜220,000 | 350〜1,000 | 45 | 1 |
| `general_kai_vh` | Normal | Attack | Blue/Green/Red/Yellow | 30,000〜90,000 | 300〜700 | 55〜65 | 1 |

**ポイント:**
- `eventchallenge01` は同一コンテンツ内で属性ごとに速度を変えて難易度差をつける設計
- `AdventBattleBoss` 種が Blue と Red で存在（`character_unit_kind` が異なる専用設定）
- `l05anniv_advent` は `attack_combo_cycle=0`（記念イベント演出専用）
- `general` の Yellow のみ HP:220,000 と突出

---

### 2-5. enemy_kai_00001（kai作品・別個体）— 高HP Bossと超低速の対比

`enemy_kai_00101` と同じ `general_kai_vh` / `kai1_advent` コンテンツに共存するペアキャラ。
`general` は **Yellow 1属性のみ**（固有色として扱われている）。

| コンテンツ | kind | role | color | HP | ATK | 速度 | combo |
|---|---|---|---|---|---|---|---|
| `l05anniv_advent` | Normal | Attack | Colorless | **1,000** | **100** | 25 | **0** |
| `glo2_advent`（Boss） | Boss | Attack | Colorless | 10,000 | 300 | **8** | 1 |
| `savage01` | Normal | Attack | Red | 10,000 | 500 | **8** | 1 |
| `kai1_advent`（Normal） | Normal | Attack | Colorless | 10,000 | 500 | 25 | 1 |
| `kai1_advent`（Boss） | **AdventBattleBoss** | Attack | Blue/Red | 10,000 | 500 | 21〜23 | 1 |
| `eventchallenge01` | Normal/Boss | Attack/Defense | Blue/Red/Yellow | 10,000 | 300〜500 | 30〜35 | 1 |
| `general` | Normal/Boss | Attack | **Yellow のみ** | 135,000〜440,000 | 450〜700 | 40 | 1 |
| `general_kai_vh` | Normal/Boss | Attack | Blue/Green/Red | 100,000〜**1,500,000** | 600〜1,200 | 35〜65 | 1 |

**ポイント:**
- `general_kai_vh` の Boss版 HP=1,500,000 は全データ中最大級
- `glo2_advent` と `savage01` の `move_speed=8` は超低速（ほぼ停止状態。特定ギミック・演出用途）
- `general` は Yellow 1属性のみに絞ってあり、固有色として管理されている
- `well_distance` が 0.31〜0.35 と全5体中最大（遠距離攻撃キャラ設計）

---

## 3. 特殊設定値のパターン一覧

| パラメータ | 特殊値 | 使われるコンテンツ | 意味・用途 |
|---|---|---|---|
| `attack_combo_cycle` | `4` | challenge（sur_00101） | 連続攻撃ギミック |
| `attack_combo_cycle` | `0` | savage01 / l05anniv 等 | 攻撃なし（演出・待機用） |
| `hp` | `1,000` | aobaget / l05anniv / advent 等 | キャラ紹介・記念演出用（倒されてよい） |
| `move_speed` | `8` | glo2_advent / savage01（kai） | 超低速（ほぼ停止。ボス演出・ギミック用） |
| `move_speed` | `100` | general 系（mag_00101） | このキャラ種固有の最高速 |
| `well_distance` | `0.30〜0.35` | general_h / kai_00001 系 | 遠距離から攻撃する設定 |
| `transformationConditionType` | `HpPercentage` | trans系（dan） | HP残量%での変身トリガー |
| `character_unit_kind` | `AdventBattleBoss` | kai advent Boss | アドベント専用ボス（通常Bossとは別種） |

---

## 4. chara_ キャラの設定パターン（glo以外3体）

`chara_` プレフィックスはプレイアブルキャラが NPC として出現するデータ。
`enemy_` との設計思想の違いが顕著に現れる。

対象キャラ（2026-03-10 時点、件数上位）:

| キャラID | 件数 | 特徴 |
|---|---|---|
| `chara_osh_00001` | 13件 | combo数で難易度表現・最大combo=10 |
| `chara_dan_00001` | 13件 | role=Defense 全固定・変身なし |
| `chara_spy_00101` | 12件 | 全件 Boss 種・超遠距離（well_dist=0.50） |

---

### 4-1. chara_osh_00001（osh作品）— combo数で難易度を表現

**general 系は HP=1,000 に固定し、combo 数を増やすことで難易度を段階表現する設計。**
`general_osh_vh` Boss/Technical の combo=10 は全データ中最大値。

| コンテンツ | kind | role | color | HP | ATK | 速度 | well_dist | combo |
|---|---|---|---|---|---|---|---|---|
| `general_osh_n`（Boss） | Boss | Technical | Colorless/Green | 1,000 | 100 | 20〜31 | 0.20〜0.30 | **6** |
| `general_osh_n`（Normal） | Normal | Attack | Colorless | 1,000 | 100 | 31 | 0.30 | 5 |
| `general_osh_h`（Boss） | Boss | Technical | Red | 1,000 | 100 | 31 | 0.20 | **6** |
| `general_osh_h`（Normal） | Normal | Attack | Blue | 1,000 | 100 | 31 | 0.30 | 5 |
| `general_osh_vh`（Boss/Technical） | Boss | Technical | Red | 1,000 | 100 | 31 | 0.20 | **10** |
| `general_osh_vh`（Boss/Support） | Boss | Support | Colorless | 1,000 | 100 | 20 | 0.30 | **7** |
| `general_osh_vh`（Normal） | Normal | Attack | Green | 1,000 | 100 | 31 | 0.30 | 5 |
| `challenge` | Boss | Technical | Red | **50,000** | **300** | 32 | 0.27 | 4 |
| `charaget_repeat` | Boss | Support | Colorless | 10,000 | 300 | 32 | 0.27 | 4 |
| `l05anniv_charaget01` | Boss | Support | Blue | 10,000 | 100 | 25 | 0.35 | 5 |
| `osh1savage02` | Boss | Support | Blue | **100,000** | 300 | 25 | 0.35 | 5 |
| `osh1savage03` | Boss | Support | Green | **100,000** | 300 | 25 | 0.35 | 5 |

**ポイント:**
- general 系は HP=1,000 固定で、**combo 数が難易度指標**（n:5〜6 → h:5〜6 → vh:7〜10）
- challenge は HP=50,000・combo=4 と、HP を上げることで難易度を付加する別方針に切り替え
- savage 系は HP=100,000（general の100倍）と大幅増
- Boss 種が大半を占め、Normal は general 系の3件のみ

---

### 4-2. chara_dan_00001（dan作品）— role=Defense 全固定・enemy版と変身有無が異なる

**全13件が role=Defense に固定。** 同キャラの `enemy_dan_00001`（trans 9件）と異なり、chara 版には変身ギミックが一切ない。

| コンテンツ | kind | role | color | HP | ATK | 速度 | well_dist | combo |
|---|---|---|---|---|---|---|---|---|
| `general_n` | Normal | **Defense** | Red | 10,000 | **50** | 31 | 0.20 | 6 |
| `general_h`（Boss） | Boss | **Defense** | Colorless | 10,000 | 100 | 31 | 0.20 | 5 |
| `general_h`（Normal） | Normal | **Defense** | Red | 10,000 | 100 | 31 | 0.20 | 6 |
| `general_vh`（Boss） | Boss | **Defense** | Green/Red | 10,000 | 100 | 31 | 0.20 | 5 |
| `general_vh`（Normal） | Normal | **Defense** | Green | 10,000 | 100 | 31 | 0.20 | 5 |
| `1d1c` | Normal | **Defense** | Colorless | **30,000** | 100 | **20** | **0.15** | 4 |
| `airaget` | Normal | **Defense** | Red | 20,000 | 100 | 30 | 0.20 | 4 |
| `bbaget`（Boss） | Boss | **Defense** | Blue/Colorless | 20,000 | 100 | 30 | 0.20 | 4 |
| `bbaget`（Normal） | Normal | **Defense** | Blue | 20,000 | 100 | 30 | 0.20 | 4 |
| `mainquest_glo2` | Normal | **Defense** | Red | 10,000 | 100 | 30 | **0.16** | 5 |
| `savage` | Normal | **Defense** | Yellow | 20,000 | 100 | **40** | 0.20 | 4 |

**ポイント:**
- 全件 role=Defense 固定（プレイアブルキャラの固有ロールが設定に直接反映）
- 変身（trans）が一切ない（enemy_dan_00001 は同キャラで trans 9件あり）
- `general_n` のみ ATK=50（他は全て100）→ ノーマルだけ攻撃力が半分
- `1d1c` は well_distance=0.15（全データ中最短）かつ speed=20（低速）
- HP の段階: general:10,000 → get/savage:20,000 → 1d1c:30,000（コンテンツで段階的に増加）
- `savage` は speed=40 で全コンテンツ中最速

---

### 4-3. chara_spy_00101（spy作品）— 全件 Boss 種・超遠距離攻撃

**general 系の well_distance=0.50 は全データ中最大。** 全件 role=Attack 固定、Normal 種が1件のみ。

| コンテンツ | kind | role | color | HP | ATK | 速度 | well_dist | combo |
|---|---|---|---|---|---|---|---|---|
| `general_n` | Boss | **Attack** | Blue/Red | 10,000 | **50** | 31 | **0.50** | **7** |
| `general_h` | Boss | **Attack** | Blue/Red | 10,000 | 50 | 31 | **0.50** | 5 |
| `general_vh` | Boss | **Attack** | Blue/Colorless/Red | 10,000 | 100 | 31 | **0.50** | 5 |
| `1d1c` | Normal | **Attack** | Colorless | 20,000 | 100 | 25 | 0.35 | 5 |
| `damianget` | Boss | **Attack** | Colorless/Red | **25,000** | **400** | 30 | 0.35 | 5 |
| `frankyget` | Boss | **Attack** | Yellow | **25,000** | **400** | 30 | 0.35 | 5 |
| `spy1savage` | Boss | **Attack** | Blue | **25,000** | **400** | 30 | 0.35 | 5 |

**ポイント:**
- general 系の well_distance=0.50 は全データ中最大（通常の倍以上の射程）
- `general_n` の combo=7 が `general_h/vh`（combo=5）より高い → n < h < vh の通常順序が逆転する珍しいパターン
- `get` 系（damianget / frankyget）が存在 → 自作品以外のキャラゲットコンテンツにも出現
- `1d1c` のみ Normal 種（他は全て Boss 種）
- ATK が general 系:50〜100 に対し、get/savage 系:400 と4〜8倍に跳ね上がる

---

## 5. enemy_ vs chara_ の設計思想の違い

| 観点 | `enemy_` | `chara_` |
|---|---|---|
| **role** | コンテンツ・属性ごとにバラつく | **全件固定**（プレイアブルキャラの固有ロール） |
| **変身（trans）** | あり（dan は9件など） | **なし** |
| **kind** | Normal/Boss 混在が多い | Boss 種に偏る（Normal が少ない） |
| **HP（general系）** | コンテンツ間で変動 | 低め固定（1,000〜10,000）が多い |
| **難易度の表現方法** | HP・ATK・speed を上下させる | **combo 数を増減**させることが多い |
| **well_distance** | 0.11〜0.35 が主流 | spy は 0.50 と極端な設定もある |
| **get 系コンテンツ** | ほぼ存在しない | キャラゲット・繰り返し get 等が存在する |
