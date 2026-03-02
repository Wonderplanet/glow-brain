# インゲームマスタデータ設計書

## 基本情報
- 生成日時: 2026-03-01
- コンテンツ種別: dungeon（限界チャレンジ）
- ステージ種別: dungeon_boss（ボスブロック）

## インゲームID命名案
- インゲームID: `dungeon_kim_boss_00001`
- 命名根拠: `dungeon_{シリーズ}_boss_{連番5桁}` の命名規則に従い、シリーズID `kim` + `boss` + 連番 `00001` を結合

## 生成対象テーブル一覧
| テーブル | 生成件数 | 備考 |
|---------|---------|------|
| MstEnemyStageParameter | 2件 | ボス1体（花園 羽々里）、護衛雑魚1種 |
| MstEnemyOutpost | 1件 | HP: 1,000（dungeon boss固定値） |
| MstPage | 1件 | |
| MstKomaLine | 1件 | 1行構成（dungeon boss固定） |
| MstAutoPlayerSequence | 3件 | 3行構成（ボス即時配置＋護衛雑魚2パターン） |
| MstInGame | 1件 | ボスダメージ無効フラグ設定あり |

## MstInGame 主要パラメータ設計
- `bgm_asset_key`: `SSE_SBG_003_009`
- `boss_bgm_asset_key`: `SSE_SBG_003_007`（ボス登場時切り替え）
- `loop_background_asset_key`: `kim_00001`
- `normal_enemy_hp_coef`: `1`
- `normal_enemy_attack_coef`: `1`
- `normal_enemy_speed_coef`: `1`
- `boss_enemy_hp_coef`: `1`
- `boss_enemy_attack_coef`: `1`
- `boss_enemy_speed_coef`: `1`
- `boss_count`: `1`
- `boss_mst_enemy_stage_parameter_id`: `c_kim_00001_kim1_challenge_Boss_Red`
  - 根拠: kimシリーズのURキャラ（chara_kim_00001）を参照する既存challengeボスIDを流用。dungeon専用IDは未存在のため、既存challenge IDを参照する
- `mst_enemy_outpost_id`: `dungeon_kim_boss_00001`
- `mst_page_id`: `dungeon_kim_boss_00001`
- `mst_auto_player_sequence_set_id`: `dungeon_kim_boss_00001`

## MstEnemyStageParameter 敵パラメータ設計

dungeonボス仕様（HPは通常登場より低め・攻撃力は高め）に従い設定。

| 識別子 | 役割 | mst_enemy_character_id | HP | 攻撃力 | スピード | damage_knock_back_count | well_distance | attack_combo_cycle |
|--------|------|----------------------|-----|-------|--------|------------------------|--------------|-------------------|
| `c_kim_00001_kim1_challenge_Boss_Red` | ボス（花園 羽々里） | `chara_kim_00001` | 50,000 | 100（シーケンス倍率で攻撃的設定） | 40 | 2 | 0.18 | 5 |
| `e_glo_00001_kim1_challenge_Normal_Colorless` | 護衛雑魚（汎用ファントム） | `enemy_glo_00001` | 10,000 | 100 | 40 | 2 | 0.18 | 1 |

> **注意**: 上記2つのIDは既存マスタデータ（MstEnemyStageParameter）に既に存在するため、今回は **新規生成しない**。
> CSV生成フェーズでは、これらのIDを `MstAutoPlayerSequence.action_value` と `MstInGame.boss_mst_enemy_stage_parameter_id` で参照するのみとする。

## MstAutoPlayerSequence ウェーブ構成設計
- 総ウェーブ数: 3
- シーケンス行1（InitialSummon）: ボスの花園 羽々里をゲート前（summon_position=1.7）に配置
  - `is_summon_unit_outpost_damage_invalidation = 1`（ボス撃破までゲートダメージ無効）
  - `move_start_condition_type = Damage`, `move_start_condition_value = 1`（ダメージを受けたら進軍開始）
  - `aura_type = Boss`
- シーケンス行2（ElapsedTime 1500ms）: 護衛雑魚1体目登場
- シーケンス行3（ElapsedTime 3000ms）: 護衛雑魚2体追加（summon_interval=600ms）

## MstPage / MstKomaLine 構成
- ページ数: 1
- コマ行数: 1行（dungeon bossブロック固定）
- コマ効果: None
- コマアセット: `kim_00001`（kim専用背景）
- koma1_width: 1.0（全幅）
- koma_line_layout_asset_key: `1`

## 参照した既存データ
- 参照データ: `c_kim_00001_kim1_challenge_Boss_Red`（kimシリーズURキャラのボスパラメータ）
  - HP: 50,000 / 攻撃力: 100 / 速度: 40 / attack_combo_cycle: 5 / damage_knock_back_count: 2
- 参照データ: `e_glo_00001_kim1_challenge_Normal_Colorless`（kimシリーズ護衛雑魚パラメータ）
  - HP: 10,000 / 攻撃力: 100 / 速度: 40 / attack_combo_cycle: 1
- 参照データ: spy normalブロック（dungeon構造の参考）
  - release_key: 999999999

## 不確定事項・要確認事項
- なし（既存kimシリーズのEnemyStageParameterを流用するため新規生成不要）
- release_key は 999999999（開発テスト用）を使用
