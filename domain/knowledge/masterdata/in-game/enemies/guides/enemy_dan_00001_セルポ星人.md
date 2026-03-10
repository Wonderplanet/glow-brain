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

---

## 特徴サマリー

- **雑魚〜ボスまで幅広い役割**で登場する汎用敵キャラ
- **変身機能を持つ**唯一のdan系通常敵。HP50%または1%で `enemy_dan_00101`（セルポ星人変身形態）に変態する
- 難易度（汎用N/H/VH）やコンテンツごとに `role_type` が **Defense** から **Attack** に変化する
- 攻撃コンボサイクルは全バリアント `1`（最短コンボ）
- `move_speed` は 25〜40 の標準域で動作する

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

---

## 設計上のポイント

- **汎用N/H では Defense ロール** を担い、壁役として配置されやすい
- **変身バリアント (`trans_`)** は、単純な高HP配置の代替として変化に富んだ戦闘体験を提供
- **VH での HP1% 変身** は「変身する演出は出すが実際には変身させない」設計意図と読める
- `attack_combo_cycle = 1` 固定のため攻撃間隔は全て最短。攻撃力の数値差で難易度を調整している
- `drop_battle_point` は一律 100（ただしメインクエストGLO2のみ 200）

---

## 関連データ

| 関連先 | ID | 概要 |
|---|---|---|
| 変身形態 | `enemy_dan_00101` | セルポ星人（変身） — HP50%/1%到達で変身先となるキャラ |
| 所属シリーズ | `dan` | ダンダダン |
