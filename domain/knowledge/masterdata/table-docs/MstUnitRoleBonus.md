# MstUnitRoleBonus 詳細説明

> CSVパス: `projects/glow-masterdata/MstUnitRoleBonus.csv`

---

## 概要

インゲーム中のロールタイプごとに、属性有利時の攻撃・防御ダメージ計算に適用するボーナス係数を定義するテーブル。
攻撃時の属性有利ボーナス係数（1.5など）と防御時の属性有利ボーナス係数（0.7など）をロールタイプ別に設定する。
Special・Unique ロールは属性有利の影響を受けない（係数1.0）設計になっている。

---

## 全カラム一覧（テーブル形式）

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---|---|---|---|---|
| id | varchar(255) | NOT NULL | - | UUID（CSVでは連番整数） |
| release_key | bigint | NOT NULL | 1 | リリースキー |
| role_type | enum | NOT NULL | - | ロールタイプ |
| color_advantage_attack_bonus | decimal(10,2) | NOT NULL | - | 攻撃時に属性有利だった時のダメージ係数（1.0が基準値） |
| color_advantage_defense_bonus | decimal(10,2) | NOT NULL | - | 防御時に属性有利だった時のダメージ係数（1.0が基準値） |

---

## CharacterUnitRoleType（role_type の enum 値）

| 値 | 説明 | 攻撃ボーナス | 防御ボーナス |
|---|---|---|---|
| None | ロールなし | - | - |
| Attack | アタックロール | 1.5 | 0.7 |
| Balance | バランスロール | 1.5 | 0.7 |
| Defense | ディフェンスロール | 1.5 | 0.7 |
| Support | サポートロール | 1.5 | 0.7 |
| Unique | ユニークロール | 1.0 | 1.0 |
| Technical | テクニカルロール | 1.5 | 0.7 |
| Special | スペシャルロール | 1.0 | 1.0 |

---

## 他テーブルとの連携

| 関連テーブル | 連携カラム | 説明 |
|---|---|---|
| mst_units | role_type | ユニット本体のロールタイプ参照 |
| mst_attacks | - | インゲームの攻撃計算でロールボーナスを参照 |

---

## 実データ例（CSVから取得）

### パターン1: 属性有利が適用される通常ロール（Attack）

| id | role_type | color_advantage_attack_bonus | color_advantage_defense_bonus | release_key |
|---|---|---|---|---|
| 1 | Attack | 1.5 | 0.7 | 202509010 |
| 2 | Defense | 1.5 | 0.7 | 202509010 |
| 3 | Support | 1.5 | 0.7 | 202509010 |
| 4 | Technical | 1.5 | 0.7 | 202509010 |

### パターン2: 属性有利が適用されない特殊ロール（Special・Unique）

| id | role_type | color_advantage_attack_bonus | color_advantage_defense_bonus | release_key |
|---|---|---|---|---|
| 5 | Special | 1.0 | 1.0 | 202509010 |
| 6 | Unique | 1.0 | 1.0 | 202509010 |

---

## 設定時のポイント

1. **係数1.0が基準値**: 攻撃・防御ともに1.0が「有利/不利なし」の基準。1.0より大きい値でダメージ増加、小さい値でダメージ減少。
2. **攻撃ボーナス（1.5）は攻撃側に有利な設定**: 属性有利時に攻撃ダメージが1.5倍になる。
3. **防御ボーナス（0.7）は攻撃側に有利な設定**: 防御側が属性有利時に被ダメージが0.7倍に軽減される（防御側の有利）。
4. **Special・Unique ロールは属性ボーナス無効**: ゲームバランス上、これらのロールには属性有利の恩恵を与えない設計。
5. **Balance ロールは現行データに存在しない**: enum定義には Balance があるが、CSVには登録されていない。必要に応じて追加する。
6. **None ロールも CSVに未登録**: ロールなしユニット向けの設定が必要な場合は追加する。
7. **ロールタイプは mst_units の role_type と一致させる**: 設定するロールタイプはユニットに実際に使用されているものを網羅すること。
