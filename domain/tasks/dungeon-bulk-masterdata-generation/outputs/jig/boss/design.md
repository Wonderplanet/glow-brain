# インゲームマスタデータ設計書

## 基本情報
- 生成日時: 2026-03-01
- コンテンツ種別: dungeon（限界チャレンジ）
- ステージ種別: dungeon_boss（ボスブロック）

## インゲームID命名案
- インゲームID: `dungeon_jig_boss_00001`
- 命名根拠: dungeon_{シリーズID}_boss_{連番5桁} の命名規則に従い、地獄楽シリーズ(jig)の初回ボスブロック

## 生成対象テーブル一覧
| テーブル | 生成件数 | 備考 |
|---------|---------|------|
| MstEnemyStageParameter | 2件 | ボス1体（がらんの画眉丸）、雑魚1体（門神） |
| MstEnemyOutpost | 1件 | HP: 1,000（dungeon固定） |
| MstPage | 1件 | |
| MstKomaLine | 1件 | 1行構成（dungeon_boss固定） |
| MstAutoPlayerSequence | 3件 | ボス初期配置1行 + 護衛雑魚時間差2行 |
| MstInGame | 1件 | |

## MstInGame 主要パラメータ設計
- `bgm_asset_key`: `SSE_SBG_003_003`（地獄楽通常BGM）
- `boss_bgm_asset_key`: 空（ボス専用BGMなし）
- `loop_background_asset_key`: `jig_00002`
- `mst_page_id`: `dungeon_jig_boss_00001`
- `mst_enemy_outpost_id`: `dungeon_jig_boss_00001`
- `boss_mst_enemy_stage_parameter_id`: `c_jig_00001_jig_dungeon_Boss_Red`
- `boss_count`: 1
- `normal_enemy_hp_coef`: 1（等倍）
- `normal_enemy_attack_coef`: 1（等倍）
- `normal_enemy_speed_coef`: 1（等倍）
- `boss_enemy_hp_coef`: 1（等倍）
- `boss_enemy_attack_coef`: 1（等倍）
- `boss_enemy_speed_coef`: 1（等倍）
- `release_key`: 999999999（開発テスト用）
- `description.ja`: ボスブロック説明文（青属性有利のヒント含む）

## MstEnemyStageParameter 敵パラメータ設計

| 識別子 | 役割 | mst_enemy_character_id | HP | 攻撃力 | スピード |
|--------|------|----------------------|-----|-------|--------|
| `c_jig_00001_jig_dungeon_Boss_Red` | ボス（がらんの画眉丸） | `chara_jig_00001` | 5,000 | 100 | 41 |
| `e_jig_00001_jig_dungeon_Normal_Colorless` | 護衛雑魚（門神） | `enemy_jig_00001` | 3,500 | 50 | 31 |

### ボス（がらんの画眉丸）詳細
- `character_unit_kind`: Boss
- `role_type`: Technical（テクニカル）
- `color`: Red（赤属性）
- `hp`: 5,000（基準値）※シーケンスのenemy_hp_coef=4と組み合わせて実質HP約20,000
- `attack_power`: 100（基準値）※シーケンスのenemy_attack_coef=3で実質攻撃力300
- `move_speed`: 41（高速）
- `attack_combo_cycle`: 5（5連続攻撃）
- `damage_knock_back_count`: 2
- `well_distance`: 0.3

### 護衛雑魚（門神）詳細
- `character_unit_kind`: Normal
- `role_type`: Defense（防御型）
- `color`: Colorless（無属性）
- `hp`: 3,500（基準値）※シーケンスのenemy_hp_coef=1で実質HP3,500
- `attack_power`: 50（基準値）※シーケンスのenemy_attack_coef=2で実質攻撃力100
- `move_speed`: 31
- `attack_combo_cycle`: 1
- `damage_knock_back_count`: 2
- `well_distance`: 0.25

## MstAutoPlayerSequence ウェーブ構成設計
- 総行数: 3行
- グループ切り替え: なし（デフォルトグループ単一構成）
- 行1: InitialSummon（0ms）→ ボス（がらんの画眉丸）×1体、summon_position=1.7（砦付近）、is_summon_unit_outpost_damage_invalidation=1
- 行2: ElapsedTime（2,000ms）→ 護衛雑魚（門神）×2体
- 行3: ElapsedTime（5,000ms）→ 護衛雑魚（門神）×3体

## MstPage / MstKomaLine 構成
- ページ数: 1
- コマ行数: 1行（dungeon_boss固定）
- コマ効果: None（エフェクトなし）
- 背景アセット: `jig_00002`
- koma1_width: 1.0（全幅）
- koma_line_layout_asset_key: 1

## 参照した既存データ
- CHIボスブロック（`dungeon_chi_boss_00001`）を参照：ボスシーケンスのis_summon_unit_outpost_damage_invalidation=1設定、1行コマ構成パターン
- jig系MstEnemyStageParameter既存データ：`c_jig_00001_mainquest_Boss_Red`（HP=5000, move_speed=41, attack_combo_cycle=5）を確認

## 不確定事項・要確認事項
- なし（要件テキストに全情報が記載されており承認済み）
