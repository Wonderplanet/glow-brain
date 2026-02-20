# MstAutoPlayerSequence 行動パターン分析

> 参照データ: `projects/glow-masterdata/MstAutoPlayerSequence.csv`（リリースキー 202602015 時点）
> 分析作成日: 2026-02-19

---

## 概要

MstAutoPlayerSequence の全4389件（485インゲーム、952種類の敵パラメータ）を対象に、
インゲームで出現する敵の行動パターンを多角的に分析した。

---

## 1. ステージ種別ごとの規模感

| ステージ種別 | インゲーム数 | 平均行数 | 最小行数 | 最大行数 | 特徴 |
|------------|------------|---------|---------|---------|------|
| 1日1回クエスト | 13 | 3.3 | 1 | 6 | 最もシンプル。ほぼElapsedTime＋SummonEnemy |
| キャラ取得強化 | 153 | 5.6 | 1 | 16 | 量が最も多い。単純〜中程度 |
| チャレンジ | 48 | 7.8 | 1 | 14 | ボス1体＋段階的雑魚出現が多い |
| Hard（常設） | 52 | 13.7 | 1 | 26 | 中〜高複雑度 |
| Normal（常設） | 78 | 13.3 | 1 | 26 | 同様に中複雑度 |
| サベージ | 31 | 16.9 | 2 | 34 | 高複雑度。複数ボス・多種条件組み合わせ |
| VeryHard（常設） | 78 | 20.3 | 4 | 46 | 最も複雑。多段階フェーズが多い |
| レイド | 11 | 43.3 | 23 | 60 | 桁違いの規模。ループ構造を持つ |

---

## 2. 発火条件（condition_type）の使用頻度

| 条件タイプ | 件数 | 使用インゲーム数 | 説明 |
|-----------|------|----------------|------|
| `ElapsedTime` | 1540 | 431 | バトル開始からN×100ms後。最多使用 |
| `FriendUnitDead` | 1405 | 292 | 敵の累計撃破数がNになったとき |
| `ElapsedTimeSinceSequenceGroupActivated` | 550 | 55 | グループ切替後の経過時間。フェーズ内タイミング制御 |
| `InitialSummon` | 341 | 141 | バトル開始時即発火。ボス配置に多用 |
| `EnterTargetKomaIndex` | 213 | 96 | 特定コマに敵が到達したとき |
| `OutpostHpPercentage` | 144 | 78 | 砦HPが特定%以下になったとき |
| `OutpostDamage` | 130 | 65 | 砦がNダメージ受けたとき（1ダメージが最多） |
| `DarknessKomaCleared` | 41 | 13 | 暗黒コマがクリアされたとき（mag/spy系特有） |
| `FriendUnitTransform` | 20 | 9 | 味方ユニットが変身したとき（dan系特有） |
| `FriendUnitSummoned` | 5 | 1 | 味方ユニットが召喚されたとき |

---

## 3. キャラ固有パターンの分析

### 3-1. 固有キャラ（c_）vs 汎用敵（e_）

| 区分 | 件数 | ユニーク種数 | 特徴 |
|------|------|------------|------|
| c_（固有キャラ） | 862件 | 442種 | ボス・ストーリーキャラに使用。各ステージ専用パラメータを持つことが多い |
| e_（汎用敵） | 3302件 | 490種 | 雑魚敵に多用。シリーズ汎用パラメータで複数ステージ流用 |

### 3-2. 同一固有キャラが複数ステージに登場するケース

同一の `c_（固有キャラ）` パラメータが3ステージ以上で使われているケースを抽出:

| キャラID | 登場ステージ数 | オーラ種別 | HP倍率範囲 | 備考 |
|---------|-------------|---------|-----------|------|
| `c_spy_00201_damianget_Boss_Red` | 7 | Default / AdventBoss2 | 0.65〜10.0 | 同じキャラでBossにもAdventBoss2にも使われる |
| `c_spy_00101_frankyget_Boss_Yellow` | 6 | Boss / Default | 1.0〜8.0 | |
| `c_you_00201_you1_charaget01_Boss_Yellow` | 5 | Default | 1.5〜8.5 | charaget用ボス |
| `c_jig_00101_mainquest_Boss_Green` | 5 | Default | 14.0〜140.0 | HP倍率が10倍変動する |
| `c_spy_00401_frankyget_Boss_Red` | 4 | Default / AdventBoss1 / AdventBoss2 | 2.0〜15.0 | 3種のauraに使い分け |
| `c_rik_00001_general_Normal_Colorless` | 4 | Boss | 1.0 | rik系専用。大量召喚専門キャラ |
| `c_dan_00001_general_h_Normal_Red` | 4 | Boss / Default | 3.5〜7.0 | hard系dan専用ボス |

**キャラ固有特性**: 多くの固有キャラは「1回しか使わない専用パラメータ」として定義されるが、
`c_spy_00201` `c_jig_00101` のような人気キャラは複数ステージで流用され、
HP・攻撃倍率で難易度調整される。

### 3-3. 同一キャラでも異なる行動パターンになるケース

例: `c_spy_00201_damianget_Boss_Red`（ダミアン ボス）

| 登場ステージ | aura_type | HP倍率 | 攻撃倍率 | 発火条件 | 備考 |
|-----------|---------|---------|---------|---------|------|
| spy1_challenge序盤 | Default | 0.65 | 0.30 | InitialSummon | 弱め設定。砦付近配置 |
| spy1_challenge中盤 | Default | 3.0〜5.0 | 1.0〜2.0 | FriendUnitDead | 撃破時に追加召喚 |
| spy1_challenge終盤 | AdventBoss2 | 10.0 | 3.5 | ElapsedTime | 最強演出で登場 |

→ **同一キャラでも演出レベル・HP倍率・発火条件が全く異なる**。ストーリー的な「強くなっていく」表現が可能。

---

## 4. ボスキャラの行動パターン

### 4-1. 配置方式の分布

| move_start_condition_type | 件数 | 使用インゲーム数 | 説明 |
|--------------------------|------|----------------|------|
| `None`（即時移動） | 532 | 246+ | 大多数のBoss。召喚と同時に移動開始 |
| `ElapsedTime` | 30 | 23 | N ms後に動き始める。入場演出などに使用 |
| `Damage` | 7 | 6 | 1ダメージ受けるまで静止。砦付近で威圧待機 |
| `EnterTargetKoma` | 15+ | 12+ | 特定コマに達したら動き始める |

### 4-2. ボスの配置場所

- 通常配置: `summon_position` は 1.0〜3.6（平均 2.7）
- 砦付近待機ボス: `summon_position = 1.7`、`move_start_condition_type = Damage`
  - 「砦の横に立ちはだかり、攻撃されるまで動かない」威圧パターン

### 4-3. ボスのオーラ演出とステージ種別

| aura_type | 件数 | 使用ステージ種別 |
|-----------|------|---------------|
| `Default` | 3497 | 全種別（雑魚含む） |
| `Boss` | 574 | チャレンジ・サベージ・VeryHard・常設Hard/Normalのボス |
| `AdventBoss1` | 32 | **レイドのみ**（w1〜w2のボス） |
| `AdventBoss2` | 33 | **レイドのみ**（w3〜w4のボス） |
| `AdventBoss3` | 28 | **レイドのみ**（最終Waveのボス・最も強い演出） |

> **ポイント**: AdventBoss系はレイドと一部特殊ステージのみ。通常イベントでは`Boss`が最高格。

### 4-4. ボスが複数体出現するパターン

| ステージ例 | ボス数 | 特徴 |
|----------|------|------|
| `veryhard_glo4_00002` | 17 | ボスアラッシュ。FriendUnitDead連鎖で次々とボス登場 |
| `veryhard_rik_00002` | 8 | 砦HP条件（OutpostDamage）でボス追加召喚 |
| `event_you1_savage_00003` | 7 | サベージ最高難度。多彩な条件でボス乱立 |
| `raid_spy1_00001` | 13 | レイドのAdventBoss群 |

---

## 5. 雑魚キャラの行動パターン

### 5-1. 雑魚は「ほぼ同じパターン」が多い

VeryHard/Hard/Normalの常設系では、汎用雑魚パラメータを流用して
倍率でのみ強さを調整するパターンが主流:

```
e_glo_00001_general_n_Normal_Colorless   ← 最も使われる汎用雑魚（20ステージで使用）
e_glo_00001_general_sur_vh_Normal_*      ← sur系VeryHard専用
e_glo_00001_general_mag_vh_Normal_*      ← mag系VeryHard専用
```

### 5-2. 雑魚の召喚パターン（間隔分布）

| 間隔パターン | 件数 | 平均召喚数 | 説明 |
|-----------|------|---------|------|
| 同時召喚（interval=0） | 2054件 | 1.0体 | 1体ずつ逐次発火が多い |
| 短い間隔（1〜500ms） | 1212件 | 13.8体 | 小刻みに複数体ずつ送り込む |
| 中間隔（501〜1000ms） | 620件 | 37.2体 | まとまった数を均等な間隔で |
| 長い間隔（1001〜2000ms） | 243件 | 41.1体 | 大量召喚を長い間隔でじわじわ |
| 超長間隔（2000ms+） | 35件 | 24.3体 | 特殊演出系 |

### 5-3. 雑魚の大量連続召喚パターン（rikキャラ・gom系）

特定のシリーズでは **同一雑魚キャラを99体×複数回** 召喚する「無限波」パターンが存在:

| ステージ | キャラ | 累計召喚数 | 説明 |
|---------|-------|---------|------|
| `raid_kai_00001` | `e_kai_00101_kai1_advent_Normal_Colorless` | **1588体** | 99体召喚を17回 |
| `veryhard_gom_00002` | `e_gom_00402_general_vh_Normal_Blue` | 744体 | 99体×複数回 |
| `veryhard_gom_00002` | `e_gom_00402_general_n_Normal_Yellow` | 636体 | 同上 |
| `hard_rik_00001` | `c_rik_00001_general_Normal_Colorless` | 570体 | rik専用キャラを連続 |
| `normal_rik_00001` | `c_rik_00001_general_Normal_Colorless` | 550体 | 同上 |

> **rikキャラの特性**: `c_rik_00001_general_Normal_Colorless` は固有キャラ（c_）にもかかわらず
> 雑魚として大量召喚される特殊なキャラ。HP倍率・攻撃倍率が全て1.0固定で、
> 「大量出現で圧倒する」という独自コンセプトのステージ設計。

---

## 6. 別キャラでも同じ行動パターンになるケース

### 6-1. 汎用「時間差波状攻撃」パターン

最も多い共通パターン。複数シリーズで同じ構造を持つ:

```
行1: ElapsedTime(N) → SummonEnemy(雑魚A) 1〜3体
行2: ElapsedTime(N+500) → SummonEnemy(雑魚A/B) 1〜5体
行3: FriendUnitDead(N) → SummonEnemy(追加雑魚) 複数体
```

→ you系・spy系・sur系・jig系など多くのキャラ取得強化ステージで同じ骨格。

### 6-2. 汎用「ボス待機＋雑魚先行」パターン

イベントチャレンジで頻出:

```
行1: InitialSummon → ボス(砦付近、Damage受けるまで静止)
行2: ElapsedTime(500) → 雑魚5体(interval=1500で順次)
行3: ElapsedTime(3000) → 別の雑魚11体(interval=3000で順次)
```

→ spy1・you1・jig1・hut1など多シリーズで類似構造。

### 6-3. 汎用「コマ到達トリガー」パターン

EnterTargetKomaIndex が多用される中〜上級ステージのパターン:

```
ElapsedTime(初期雑魚) → 時間で雑魚を出す
EnterTargetKomaIndex=3 → 雑魚が中盤に達したらさらに追加
EnterTargetKomaIndex=5 → 終盤に強化雑魚 or ボス
```

→ charaget・challenge・VeryHardで共通して見られる。

---

## 7. ボスキャラ特有のパターン

### 7-1. 最強ボス演出パターン（AdventBoss3）

レイドの最終Waveにのみ登場。パターンの特徴:
- `ElapsedTimeSinceSequenceGroupActivated(0)` で即座に登場（フェーズ切り替え直後）
- `aura_type = AdventBoss3`（最も派手な入場演出）
- `defeated_score` が最高値（raid_osh1では500点など）

### 7-2. 砦待機ボスパターン

```
summon_position:            1.7  （砦の横）
move_start_condition_type:  Damage
move_start_condition_value: 1    （1ダメージで動き始める）
```

使用数: 約7件（チャレンジ・キャラ強化の難易度高めステージに多い）

### 7-3. 段階的HP変化ボスパターン

同一ボスキャラが異なる行で HP倍率を変えて再登場:

例 `c_jig_00101_mainquest_Boss_Green`:
- ステージ01: hp_coef=14.0 → ステージ06: hp_coef=140.0 (10倍！)

→ シリーズ最終ステージに向けて同キャラが「進化」するような設計。

---

## 8. シリーズ（キャラ）固有の特殊パターン

### 8-1. dan系：FriendUnitTransform（変身）連動パターン

`FriendUnitTransform` は **dan系のみ** が活用する特殊条件。
normal/hard/veryhard の全難度を通じて、dan系は「変身」を行動パターンのトリガーとして使用:

| ステージ | 変身時の動作 |
|---------|------------|
| `hard_dan_00003` | 変身 → SwitchSequenceGroup（フェーズ移行） |
| `hard_dan_00004` | 変身 → 雑魚3体×3行を同時召喚 |
| `veryhard_dan_00002` | 変身 → 雑魚3体+50体+30体の大量召喚 |
| `veryhard_dan_00005` | グループ内で変身 → 別シリーズ雑魚も召喚 |

### 8-2. mag系：マンホールギミックパターン

magシリーズの特定ステージでは **SummonGimmickObject（マンホール）** が使われる:

```
InitialSummon → SummonGimmickObject(mag_manhole_enemy) を3〜5個配置
EnterTargetKomaIndex=5 → TransformGimmickObjectToEnemy (敵に変換) × 複数
FriendUnitDead(N) → さらにTransformGimmickObjectToEnemy
```

→ 最初は「地面のギミック」として存在し、条件が満たされると「突然敵に変化」する演出。
使用ステージ: `event_mag1_savage_00002`、`hard_mag_00005`、`normal_mag_00005` など。

### 8-3. kai系：本珠ギミックパターン

kaiシリーズの特定ステージでは **kai_honju_enemy_vh** というギミックオブジェクトを配置:

```
InitialSummon(2) → SummonGimmickObject(kai_honju_enemy_vh) × 3〜5個
FriendUnitDead(N) → TransformGimmickObjectToEnemy(e_kai_00001_general_kai_vh_*) × 1
```

→ mag系と同様、ギミック→敵変換パターン。kai系VeryHard（00003、00006）で使用。

### 8-4. rik系：同一キャラ大量連続出現パターン

rik専用キャラ `c_rik_00001_general_Normal_Colorless` を使った特殊パターン:

```
ElapsedTime(200) → 3体
FriendUnitDead(2) → 3体×2行（同時発火）
FriendUnitDead(5) → 7体×3行（同時発火）
ElapsedTime(8500) → 99体
OutpostDamage(1) → 99体×2行（砦攻撃されたら大量追加）
```

→ 同一キャラを20回以上召喚する「無限湧き」コンセプト。
HP倍率がすべて1.0で「弱いが多い」というシリーズの世界観を表現。

### 8-5. spy系：暗黒コマ（DarknessKomaCleared）パターン

spy系の特定サベージ・VeryHardで `DarknessKomaCleared` を使用:

```
DarknessKomaCleared=1 → 雑魚召喚
DarknessKomaCleared=2 → 強化雑魚 or ボス
DarknessKomaCleared=3 → さらに強い敵
DarknessKomaCleared=4 → ボス登場
DarknessKomaCleared=5 → ラスボス
```

→ コマクリア数に応じてエスカレートする独自の難易度制御。
使用ステージ: `event_spy1_savage_00001`、`event_spy1_savage_00002`など。

---

## 9. フェーズ切り替え（SwitchSequenceGroup）パターン

### 9-1. 使用実績

| 用途 | 件数 | 主な使用ステージ |
|------|------|---------------|
| FriendUnitDead → SwitchSequenceGroup | 最多 | チャレンジ・サベージ・VeryHard・レイド全般 |
| ElapsedTime → SwitchSequenceGroup | あり | 特定条件達成後に移行 |
| OutpostHpPercentage → SwitchSequenceGroup | あり | 砦HP〇%以下で移行 |
| FriendUnitTransform → SwitchSequenceGroup | あり | 変身したら移行（dan系） |
| OutpostDamage → SwitchSequenceGroup | 1件のみ | `veryhard_dan_00001` |

### 9-2. フェーズ構造の複雑度

| ステージ | グループ数 | 切り替え数 | パターン |
|---------|---------|---------|---------|
| 単純（グループなし） | 1（デフォルト） | 0 | キャラ強化・1日1回など |
| 2フェーズ | 2 | 1 | チャレンジ・サベージの基本形 |
| 3〜5フェーズ | 3〜5 | 2〜4 | 難易度高めのサベージ・VeryHard |
| マルチフェーズ（最大8グループ） | 8 | 8 | `veryhard_gom_00002`（46行） |
| レイドループ | 6〜8 | 7〜9 | w1〜w6/w7のループ構造 |

---

## 10. レイド専用パターン

レイドは他ステージと本質的に異なる構造を持つ:

### 10-1. ループ構造

```
デフォルト → w1 → w2 → w3 → w4 → w5 → w6 → w1（ループ）
```

最終グループの条件達成で **w1に戻る**。永続バトルのための無限ループ。

### 10-2. スコアシステム

全レイドで `defeated_score > 0` の行が存在。通常ステージは全て `0`。

| レイド | スコア付き行数 | スコア範囲 |
|------|------------|---------|
| `raid_osh1_00001`（60行） | 53行 | 10〜500点 |
| `raid_you1_00001`（50行） | 43行 | 10〜300点 |
| `raid_hut1_00001`（48行） | 41行 | 10〜200点 |

### 10-3. 全レイドがAdventBoss系を使用

AdventBoss1/2/3 は **全12レイド＋1特殊ステージ**（veryhard_sum_00004）のみに登場。
w1〜w4でAdventBoss1→2へとエスカレートし、最終waveでAdventBoss3が登場。

### 10-4. 各レイドの規模

| レイドID | 行数 | グループ数 | ボス行数 | 使用雑魚例 |
|---------|------|---------|---------|---------|
| `raid_osh1_00001` | 60 | 6 | 6 | e_glo_00002系を28回登場 |
| `raid_you1_00001` | 50 | 6 | 10 | e_you_00001系を18回登場 |
| `raid_yuw1_00001` | 49 | 7 | 8 | 7グループ（最多） |
| `raid_hut1_00001` | 48 | 6 | 8 | |
| `raid_kai_00001` | 40 | 7 | 9 | e_kai_00101を**1588体**召喚（最多） |

---

## 11. 砦ダメージ時の追加召喚パターン

`OutpostDamage` 条件の使用パターン:

| condition_value | 用途 | 件数 |
|----------------|------|------|
| 1 | 1ダメージで即発火（実質「砦に到達したとき」） | 116件 |
| 1000 | 1000ダメージ受けた後に発火 | 1件 |
| 2000 | 2000ダメージで発火 | 1件 |
| 5000 | 5000ダメージで発火 | 9件 |
| 7000 | 7000ダメージで発火 | 2件 |

> **注目パターン**: `is_summon_unit_outpost_damage_invalidation = 1` が設定された
> OutpostDamage応答ボスは「砦にダメージを与えない」ボス。
> 砦近くで戦闘を続けてもプレイヤーが不利にならない配慮がある。

---

## 12. 主要パターン分類まとめ

| パターン名 | 代表ステージ種別 | 特徴 |
|-----------|--------------|------|
| **Type A: シンプル単一** | 1日1回・キャラ強化(序盤) | ElapsedTimeで1〜3体を出すのみ |
| **Type B: ボス待機＋雑魚波** | チャレンジ・キャラ強化(後半) | ボスが砦前待機、雑魚を時間差で送り込む |
| **Type C: 撃破数トリガー段階進行** | チャレンジ全般 | FriendUnitDeadで難易度を段階的に上げる |
| **Type D: コマ到達トリガー** | Hard/VeryHard・サベージ | EnterTargetKomaIndexで敵が近づくほど増援 |
| **Type E: 砦HP/Damage連動** | VeryHard・サベージ | 砦を攻撃されたら反撃ボスや大量雑魚が召喚 |
| **Type F: フェーズ切り替えあり** | サベージ・VeryHard(上位) | SwitchSequenceGroupで多段階構成 |
| **Type G: 大量連続湧き** | rik/kai/gom系VeryHard | 同一キャラを累計数百〜千体以上召喚 |
| **Type H: ギミック変換** | mag/kai系 | SummonGimmickObject→TransformGimmickObjectToEnemy |
| **Type I: 変身連動** | dan系 | FriendUnitTransformで増援・フェーズ移行 |
| **Type J: 暗黒コマ連動** | spy系 | DarknessKomaClearedでエスカレート |
| **Type K: レイドループ** | レイド全般 | マルチウェーブ＋ループ構造＋スコア制 |

---

## 13. 今後の敵配置設計における知見

### やってはいけないこと
- `sequence_set_id` を MstInGame.id と一致させない → 敵が出ない
- `FriendUnitDead` に同じ condition_value を複数行設定すると**全行同時発火**する（意図的多体召喚として使える）
- レイドで `defeated_score` を 0 にすると点数が入らない

### 典型パターンの行数目安
- デイリー・1日1回: 1〜3行
- キャラ強化序盤: 1〜4行
- キャラ強化後半・チャレンジ: 5〜14行
- サベージ: 10〜34行
- Hard/VeryHard常設: 13〜46行
- レイド: 23〜60行

### シリーズ固有条件の使い分け
- dan系に FriendUnitTransform が有効
- mag/kai系にギミックオブジェクト変換が有効
- spy系に DarknessKomaCleared が有効
- 他シリーズはこれらを使っていない → シリーズの「個性」として定義されている

---

*本ドキュメントはデータベース全体のパターン分析であり、個別ステージの詳細は raid_osh1_00001_詳細解説.md などを参照*
