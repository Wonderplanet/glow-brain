# ターボババア (`enemy_dan_00201`)

## 基本情報

| 項目 | 値 |
|------|-----|
| キャラクターID | `enemy_dan_00201` |
| 名前 | ターボババア |
| 作品 | DAN（ダンダダン） |
| `mst_series_id` | `dan` |
| 図鑑表示 | あり (`is_displayed_encyclopedia = true`) |
| 幻体 | なし (`is_phantomized = false`) |
| 変身形態 | なし |

---

## 特徴サマリー

- **圧倒的なスピード**が最大の特徴。`move_speed` が 50〜75 と dan 系敵の中で断トツに高い
- **ほぼ全てのバリアントで Boss または高HPの強敵**として配置される
- 変身機能はなく、シンプルに「速くて強いボス」として設計されている
- `drop_battle_point` は 500 と高く（一部コンテンツを除く）、倒した際の報酬が大きい
- 攻撃コンボサイクルは全バリアント `1`（最短コンボ）

---

## コンテンツ別パラメータ一覧

### 汎用ベリーノーマル（`general_vn`）

| ID | unit_kind | role_type | color | HP | attack | move_speed | well_distance | drop_bp |
|---|---|---|---|---|---|---|---|---|
| `e_dan_00201_general_vn_Normal_Colorless` | Normal | Attack | Colorless | 10,000 | 100 | **75** | 0.24 | 500 |

> `general_vn` (ベリーノーマル) にも関わらず Normal ロールで `move_speed = 75`。低難易度でもスピード感を演出。

---

### 汎用ノーマル（`general_n`）

| ID | unit_kind | role_type | color | HP | attack | move_speed | well_distance | drop_bp |
|---|---|---|---|---|---|---|---|---|
| `e_dan_00201_general_n_Boss_Colorless` | Boss | Attack | Colorless | **100,000** | **1,000** | **75** | 0.24 | 500 |
| `e_dan_00201_general_n_Boss_Red` | Boss | Attack | Red | 10,000 | 50 | 65 | 0.24 | 500 |

> **`general_n_Boss_Colorless` は HP 100,000 / attack 1,000** と dan 系最高クラスのスペックを誇る汎用ボス。
> Red バリアントは HP 10,000 / attack 50 と控えめで、同コンテンツ内での調整幅として使われる。

---

### メインクエスト GLO2（`mainquest_glo2`）

| ID | unit_kind | role_type | color | HP | attack | move_speed | well_distance | drop_bp |
|---|---|---|---|---|---|---|---|---|
| `e_dan_00201_mainquest_glo2_Boss_Blue` | Boss | Attack | Blue | 30,000 | 400 | 55 | 0.24 | 400 |

> ストーリー進行の中ボス的な役割。HP 30,000 / attack 400 / `move_speed = 55` とバランスのとれたパラメータ。

---

### DAN1チャレンジ（`dan1challenge`）

| ID | unit_kind | role_type | color | HP | attack | move_speed | well_distance | drop_bp |
|---|---|---|---|---|---|---|---|---|
| `e_dan_00201_dan1challenge_Boss_Blue` | Boss | Attack | Blue | 10,000 | 100 | 55 | 0.25 | 200 |

> チャレンジコンテンツのため HP・attack は控えめ（10,000 / 100）。スピードは 55 を維持し緊張感を保つ。
> `drop_battle_point = 200` と他バリアントより低い。

---

### DAN1降臨（`dan1_advent`）

| ID | unit_kind | role_type | color | HP | attack | move_speed | well_distance | drop_bp |
|---|---|---|---|---|---|---|---|---|
| `e_dan_00201_dan1_advent_Boss_Yellow` | Boss | **Technical** | Yellow | **100,000** | **500** | 50 | 0.24 | 100 |

> 降臨コンテンツのラスボス相当。`role_type = Technical` で Yellow という特殊な組み合わせ。
> HP 100,000 / attack 500 の高難易度仕様。`drop_battle_point = 100` と低いのはコンテンツ構造上の設計（周回前提ではない）。

---

## スペック比較グラフ（move_speed）

```
汎用VN / 汎用N (Colorless)   [==================================] 75
汎用N (Red)                  [==============================   ] 65
メインクエスト GLO2          [==========================       ] 55
DAN1チャレンジ               [==========================       ] 55
DAN1降臨                     [========================         ] 50

（参考）セルポ星人の最大値    [====================             ] 40
```

> ターボババアの `move_speed` はセルポ星人（max 40）と比較して **1.25〜1.9倍**の速度を持つ。

---

## 設計上のポイント

- **「速くて強い」ボス敵** として一貫したデザイン。変身なし・スキルなしでシンプルに圧力をかける
- **汎用N の Colorless Boss は HP 100,000 / attack 1,000** とdan系最強格のパラメータを持ち、汎用コンテンツの最終障壁として機能
- `move_speed` の幅（50〜75）を活かして、コンテンツごとの体感難易度を調整している
  - 汎用N・VN では 75 で最大の圧力
  - 高難度コンテンツ（降臨）では 50 に抑え、攻撃力・HP で強さを演出
- `drop_battle_point` が 500 と高いため、バトルポイント効率の高い報酬源になる
- `attack_combo_cycle = 1` 固定で攻撃は最短間隔。high-attack の数値がそのまま脅威に直結する

---

## 関連データ

| 関連先 | ID | 概要 |
|---|---|---|
| 所属シリーズ | `dan` | ダンダダン |
| 幻体版 | `chara_dan_00301` | 招き猫 ターボババア（プレイアブルキャラクター） |
