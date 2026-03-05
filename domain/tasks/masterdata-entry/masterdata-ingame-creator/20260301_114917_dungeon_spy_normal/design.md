# インゲームマスタデータ設計書

## 基本情報

- 生成日時: 2026-03-01 11:49:17
- コンテンツ種別: `dungeon`（限界チャレンジ）
- ブロック種別: `dungeon_normal`（通常ブロック）
- シリーズ: SPY×FAMILY

---

## インゲームID

| 項目 | 値 |
|------|-----|
| インゲームID | `dungeon_spy_normal_00001` |
| 命名根拠 | `dungeon_{シリーズ}_normal_{連番5桁}` の命名規則に従い、SPYシリーズ dungeon_normal の第1弾として連番 `00001` を付与 |

このIDは以下のテーブルで共通使用する:
- `MstInGame.id`
- `MstAutoPlayerSequence.sequence_set_id`
- `MstPage.id`
- `MstEnemyOutpost.id`

---

## 生成対象テーブル一覧

| テーブル | 生成件数 | 備考 |
|---------|---------|------|
| MstEnemyStageParameter | 2件 | 雑魚2種（enemy_spy_00001, enemy_spy_00101） |
| MstEnemyOutpost | 1件 | HP: 100（dungeon_normal 固定値） |
| MstPage | 1件 | |
| MstKomaLine | 3件 | dungeon_normal 固定3行構成 |
| MstAutoPlayerSequence | 5件 | 5ウェーブ構成 |
| MstInGame | 1件 | ボスなし |

---

## MstEnemyStageParameter 敵パラメータ設計

dungeon_spy_normal 専用の新規パラメータIDを作成する。
既存の `general_n` パラメータ（HP=1000, attack=50）は流用せず、dungeon用パラメータとして別途作成する。

| ID | キャラID | kind | role | color | HP | 攻撃力 | 速度 | well_dist | ability | 備考 |
|----|---------|------|------|-------|----|--------|------|-----------|---------|------|
| `e_spy_00001_spy_dungeon_Normal_Colorless` | `enemy_spy_00001` | Normal | Attack | Colorless | 1000 | 5000 | 40 | 0.4 | （空） | 速度=40：既存standard(34)より「少し速い」設定 |
| `e_spy_00101_spy_dungeon_Normal_Colorless` | `enemy_spy_00101` | Normal | Attack | Colorless | 1000 | 5000 | 40 | 0.2 | （空） | 速度=40：既存standard(31)より「少し速い」設定 |

**【設計メモ】ゴールドファントム（未実装）**
- 登場想定: 1体のみ（どちらかの雑魚がゴールドファントムに変化）
- 登場確率: 10%（一律）
- 現段階での対応: クライアント/サーバーの確率設定が未実装のため、CSVへの設定は行わない
- 今後実装時には `MstEnemyCharacter.is_phantomized = 1` の仕組みと組み合わせて対応予定

**【確認事項】攻撃力5000について**
- ユーザー指定値: 攻撃力 5000
- 既存SPY通常/ハードの雑魚は attack_power=50（倍率1〜2x）
- 既存SPYチャレンジ系の雑魚は attack_power=200〜300
- 今回は dungeon用として `attack_power=5000`（base値として設定）、MstInGame/MstAutoPlayerSequenceの倍率は1xで運用する

---

## MstEnemyOutpost 設計

| 項目 | 値 | 根拠 |
|------|-----|------|
| id | `dungeon_spy_normal_00001` | インゲームIDと同一 |
| hp | `100` | dungeon_normal 固定値 |
| is_damage_invalidation | （空） | ダメージ有効 |
| outpost_asset_key | （空） | デフォルト |
| artwork_asset_key | `spy_0001` | SPYシリーズ背景アートワーク（要確認） |

---

## MstPage / MstKomaLine 構成

- ページID: `dungeon_spy_normal_00001`
- コマ行数: 3行（dungeon_normal 固定）
- コマ効果: なし（None）

| row | height | layout | コマ構成 |
|-----|--------|--------|---------|
| 1 | 0.52 | 7 | 3分割（0.3 / 0.3 / 0.4） |
| 2 | 0.52 | 4 | 2分割（0.5 / 0.5） |
| 3 | 0.52 | 1 | 1コマ（1.0） |

> コマアセットキー（`koma1_asset_key` 等）は SPY×FAMILY シリーズの既存アセットキーを使用予定（要確認・仮設定）

---

## MstAutoPlayerSequence ウェーブ構成設計

- sequence_set_id: `dungeon_spy_normal_00001`
- 総ウェーブ数: 5
- ボスなし（dungeon_normal のため）
- 全行の aura_type: `Default`、death_type: `Normal`
- MstInGame の normal_enemy_hp_coef, attack_coef は 1x（個別シーケンスの coef で調整）

| elem | id | 条件 | condition_value | action | 召喚対象 | 召喚数 | interval(ms) | hp_coef | atk_coef | spd_coef |
|------|-----|------|----------------|--------|---------|--------|-------------|---------|---------|---------|
| 1 | `dungeon_spy_normal_00001_1` | InitialSummon | 0 | SummonEnemy | `e_spy_00101_spy_dungeon_Normal_Colorless` | 2 | 1000 | 1.0 | 1 | 1 |
| 2 | `dungeon_spy_normal_00001_2` | ElapsedTime | 5000 | SummonEnemy | `e_spy_00001_spy_dungeon_Normal_Colorless` | 3 | 1000 | 1.0 | 1 | 1 |
| 3 | `dungeon_spy_normal_00001_3` | ElapsedTime | 15000 | SummonEnemy | `e_spy_00101_spy_dungeon_Normal_Colorless` | 3 | 1000 | 1.5 | 1 | 1 |
| 4 | `dungeon_spy_normal_00001_4` | ElapsedTime | 25000 | SummonEnemy | `e_spy_00001_spy_dungeon_Normal_Colorless` | 3 | 800 | 1.5 | 1 | 1 |
| 5 | `dungeon_spy_normal_00001_5` | ElapsedTime | 35000 | SummonEnemy | `e_spy_00101_spy_dungeon_Normal_Colorless` | 4 | 800 | 2.0 | 1 | 1 |

**ウェーブ設計の意図:**
- 第1波（開始直後）: enemy_spy_00101（well_dist=0.2の近接型）2体で序盤の圧を作る
- 第2波（5秒後）: enemy_spy_00001（well_dist=0.4の中距離型）3体を追加
- 第3波（15秒後）: hp_coef=1.5 で強化したenemy_spy_00101 を3体、中盤の難易度上昇
- 第4波（25秒後）: hp_coef=1.5 で強化したenemy_spy_00001 を3体、interval短縮で圧増加
- 第5波（35秒後）: hp_coef=2.0（最終強化）の enemy_spy_00101 を4体（最終波）

**【設計メモ】ゴールドファントム（未実装）**
- 上記ウェーブのいずれかで、`is_phantomized=1` のキャラが10%の確率で1体ゴールドファントムとして出現する想定
- 実装時には本テーブルまたは専用テーブルに確率設定カラムを追加して対応予定

---

## MstInGame 主要パラメータ設計

| カラム | 値 | 備考 |
|--------|-----|------|
| id | `dungeon_spy_normal_00001` | |
| mst_auto_player_sequence_set_id | `dungeon_spy_normal_00001` | |
| bgm_asset_key | `SSE_SBG_003_002` | SPY×FAMILYシリーズ通常BGM（要確認） |
| boss_bgm_asset_key | （空） | ボスなし |
| loop_background_asset_key | （空） | |
| player_outpost_asset_key | （空） | デフォルト |
| mst_page_id | `dungeon_spy_normal_00001` | |
| mst_enemy_outpost_id | `dungeon_spy_normal_00001` | |
| mst_defense_target_id | （空） | |
| boss_mst_enemy_stage_parameter_id | （空） | ボスなし |
| boss_count | （空） | ボスなし |
| normal_enemy_hp_coef | 1 | 個別 hp_coef でウェーブごとに調整 |
| normal_enemy_attack_coef | 1 | |
| normal_enemy_speed_coef | 1 | |
| boss_enemy_hp_coef | 1 | |
| boss_enemy_attack_coef | 1 | |
| boss_enemy_speed_coef | 1 | |
| release_key | 999999999 | 仮置き（投入時に正式キーへ変更） |

---

## 参照した正規データ

- 参照元: `projects/glow-masterdata/MstEnemyStageParameter.csv`
- 既存SPY dungeon データ: **0件**（今回が初）
- 既存SPY 雑魚パラメータ（参考）:
  - `e_spy_00001_general_n_Normal_Colorless`: HP=1000, atk=50, speed=34
  - `e_spy_00101_general_n_Normal_Colorless`: HP=1000, atk=50, speed=31

---

## 不確定事項・要確認事項

| # | 項目 | 内容 |
|---|------|------|
| 1 | artwork_asset_key | MstEnemyOutpost の `artwork_asset_key` は SPY×FAMILY の背景アセットキーを指定（`spy_0001` 等、正式キーを要確認） |
| 2 | koma_asset_key | MstKomaLine の各コマアセットキーは SPY×FAMILY のステージ背景コマアセットキーを要確認 |
| 3 | bgm_asset_key | `SSE_SBG_003_002` で問題ないか要確認 |
| 4 | ゴールドファントム確率設定 | 未実装。実装後に設計書を更新し、CSVに追加する |
