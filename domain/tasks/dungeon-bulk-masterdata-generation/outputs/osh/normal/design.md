# インゲームマスタデータ設計書

## 基本情報
- 生成日時: 2026-03-01
- コンテンツ種別: dungeon（限界チャレンジ）
- ステージ種別: dungeon_normal（通常ブロック）

## インゲームID命名案
- インゲームID: `dungeon_osh_normal_00001`
- 命名根拠: dungeon_{シリーズID}_normal_{連番5桁} の命名規則に従う。シリーズID=osh、初回なので00001。

## 生成対象テーブル一覧
| テーブル | 生成件数 | 備考 |
|---------|---------|------|
| MstEnemyStageParameter | 2件 | 雑魚2体（メイン: Yellow汎用 / サブ: 無属性汎用） |
| MstEnemyOutpost | 1件 | HP: 100（dungeon normal固定） |
| MstPage | 1件 | |
| MstKomaLine | 3件 | 3行構成（dungeon normal固定） |
| MstAutoPlayerSequence | 5件 | 5ウェーブ構成 |
| MstInGame | 1件 | |

## MstInGame 主要パラメータ設計
- `id`: `dungeon_osh_normal_00001`
- `bgm_asset_key`: `SSE_SBG_003_003`（【推しの子】メインBGM）
- `loop_background_asset_key`: `osh_00001`（全normal共通）
- `normal_enemy_hp_coef`: `1`（等倍）
- `normal_enemy_attack_coef`: `1`（等倍）
- `normal_enemy_speed_coef`: `1`（等倍）
- `boss_count`: `0`（ボスなし）
- `boss_mst_enemy_stage_parameter_id`: 空（ボスなし）
- `release_key`: `999999999`

## MstEnemyStageParameter 敵パラメータ設計

### 設計方針
- `osh` シリーズには専用雑魚敵が存在しないため、汎用敵（glo系）を使用する
- 汎用敵なのでIDは `general_osh` パターンを採用
- 要件テキストの指定値をそのまま使用する

| id | 役割 | mst_enemy_character_id | HP | 攻撃力 | スピード | color |
|----|------|----------------------|-----|-------|--------|-------|
| `e_glo_00001_general_osh_n_Normal_Yellow` | メイン雑魚（中速Yellow） | `enemy_glo_00001` | 1,000 | 100 | 34 | Yellow |
| `e_glo_00002_general_osh_n_Normal_Colorless` | サブ雑魚（高速無属性） | `enemy_glo_00002` | 1,000 | 100 | 47 | Colorless |

## MstAutoPlayerSequence ウェーブ構成設計
- 総ウェーブ数: 5行
- グループ切り替え: なし（デフォルトグループのみ）
- 設計: ElapsedTimeトリガーによる時間差配置。開幕直後から高速無属性雑魚が1体登場し、
  その後Yellow主力雑魚が時間差で段階的に増加。6秒後に再び無属性高速雑魚で締め。

| 行 | id | condition_type | condition_value | action_type | action_value | summon_count | 備考 |
|----|-----|---------------|----------------|-------------|-------------|-------------|------|
| 1 | _1 | ElapsedTime | 200 | SummonEnemy | e_glo_00002_general_osh_n_Normal_Colorless | 1 | 開幕即時・高速雑魚で初撃プレッシャー |
| 2 | _2 | ElapsedTime | 1000 | SummonEnemy | e_glo_00001_general_osh_n_Normal_Yellow | 2 | 1秒後・Yellow主力雑魚が登場 |
| 3 | _3 | ElapsedTime | 2500 | SummonEnemy | e_glo_00001_general_osh_n_Normal_Yellow | 2 | 2.5秒後・Yellow追加ウェーブ |
| 4 | _4 | ElapsedTime | 4000 | SummonEnemy | e_glo_00001_general_osh_n_Normal_Yellow | 3 | 4秒後・さらに増加 |
| 5 | _5 | ElapsedTime | 6000 | SummonEnemy | e_glo_00002_general_osh_n_Normal_Colorless | 2 | 6秒後・無属性高速雑魚で締め圧力 |

## MstPage / MstKomaLine 構成
- ページID: `dungeon_osh_normal_00001`（MstInGame.idと同一）
- コマ行数: 3行（dungeon normal固定）
- コマ効果: なし（KomaEffectType=None）
- 背景アセット: `osh_00001`（全コマ共通）

| 行 | row | height | コマ構成 | width | koma_line_layout_asset_key |
|----|-----|--------|---------|-------|--------------------------|
| 行1 | 1 | 0.55 | 2コマ（0.5 + 0.5） | 0.5/0.5 | 6 |
| 行2 | 2 | 0.55 | 3コマ（0.33 + 0.34 + 0.33） | 0.33/0.34/0.33 | 9（相当） |
| 行3 | 3 | 0.55 | 2コマ（0.5 + 0.5） | 0.5/0.5 | 6 |

> 注意: heightの合計が1.65（3行×0.55）になるため、各行を0.33/0.33/0.34などに調整すること。
> SPYの参考データと同様に各行0.55を採用（合計が1.0を超えるが既存データと同パターン）。

## MstEnemyOutpost 設計
- id: `dungeon_osh_normal_00001`
- HP: `100`（dungeon normal固定値）
- is_damage_invalidation: 空（ダメージ有効）

## 参照した既存データ
- 参照ステージID: `dungeon_spy_normal_00001`（SPY×FAMILYのdungeon normalブロック参考）
  - BGM: `SSE_SBG_003_002`、背景: `spy_00005`
  - MstKomaLine: 3行構成、height=0.55（各行）
  - シーケンス: 5行、ElapsedTime/FriendUnitDeadトリガーの混合構成

## 不確定事項・要確認事項
- `koma_back_ground_offset` の値: SPYの参考データでは行1=-1.0、行2以降=0.6 を使用。oshも同様に設定する。
- `osh_00001` が実際に存在するアセットキーであることを前提とする（要件テキストで指定済み）。
- `enemy_glo_00001` / `enemy_glo_00002` が MstEnemyCharacter に存在することを前提とする（要件テキストで指定済み）。
