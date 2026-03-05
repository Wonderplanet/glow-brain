# インゲームマスタデータ設計書

## 基本情報
- 生成日時: 2026-03-01
- コンテンツ種別: dungeon（限界チャレンジ）
- ステージ種別: dungeon_boss（ボスブロック）
- シリーズ: chi（チェンソーマン）

## インゲームID
- インゲームID: `dungeon_chi_boss_00001`
- 命名根拠: `dungeon_{シリーズ}_boss_{連番5桁}` のdungeon専用命名規則に従い、chiシリーズ第1ボスブロックとして採番

## 生成対象テーブル一覧

| テーブル | 生成件数 | 備考 |
|---------|---------|------|
| MstEnemyStageParameter | 2件 | ボス1体（c_chi_00002）、雑魚1種（e_chi_00101_Yellow） |
| MstEnemyOutpost | 1件 | HP: 1,000（dungeonボスブロック固定値） |
| MstPage | 1件 | |
| MstKomaLine | 1件 | 1行構成（dungeonボスブロック固定値） |
| MstAutoPlayerSequence | 3件 | 3行構成（ボスInitialSummon + 雑魚ElapsedTime×2） |
| MstInGame | 1件 | is_summon_unit_outpost_damage_invalidation設定あり |

## MstInGame 主要パラメータ設計

| カラム | 値 | 根拠 |
|--------|-----|------|
| `id` | `dungeon_chi_boss_00001` | dungeon命名規則 |
| `bgm_asset_key` | `SSE_SBG_003_001` | chiシリーズ共通BGM |
| `boss_bgm_asset_key` | （空） | ボスBGM別途なし |
| `loop_background_asset_key` | `glo_00016` | chiシリーズ代表背景 |
| `mst_page_id` | `dungeon_chi_boss_00001` | MstInGame.idと同一 |
| `mst_enemy_outpost_id` | `dungeon_chi_boss_00001` | MstInGame.idと同一 |
| `boss_mst_enemy_stage_parameter_id` | `c_chi_00002_chi_dungeon_Boss_Yellow` | ボスパラメータID |
| `boss_count` | `1` | ボス1体 |
| `normal_enemy_hp_coef` | `1` | 等倍（基本値） |
| `normal_enemy_attack_coef` | `1` | 等倍（基本値） |
| `normal_enemy_speed_coef` | `1` | 等倍（基本値） |
| `boss_enemy_hp_coef` | `1` | 等倍（基本値） |
| `boss_enemy_attack_coef` | `1` | 等倍（基本値） |
| `boss_enemy_speed_coef` | `1` | 等倍（基本値） |
| `result_tips.ja` | バトルヒントテキスト | 設計テキスト準拠 |
| `description.ja` | ステージ説明文 | 設計テキスト準拠 |
| `release_key` | `999999999` | 開発・テスト用（後で正式キーに変更） |

## MstEnemyStageParameter 敵パラメータ設計

| ID | 役割 | mst_enemy_character_id | HP | 攻撃力 | 速度 | コンボ | ロール | 属性 |
|----|------|----------------------|----|--------|------|--------|--------|------|
| `c_chi_00002_chi_dungeon_Boss_Yellow` | ボス（ゲートダメージ無効化持ち） | `chara_chi_00002` | 200,000 | 900 | 50 | 5 | Technical | Yellow |
| `e_chi_00101_chi_dungeon_Normal_Yellow` | 護衛の雑魚（ゾンビ） | `enemy_chi_00101` | 13,000 | 720 | 35 | 1 | Technical | Yellow |

**設計ポイント:**
- ボスHP 200,000は元スペック400,000の50%（dungeonボスブロック設計指針「HPは通常より低め」）
- ボス攻撃力 900は元スペック450の2倍（「攻撃力は高め」）
- 雑魚パラメータは既存の `e_chi_00101_general_Normal_Yellow`（HP:13,000 / 攻撃力:720 / 速度:35）と一致

## MstAutoPlayerSequence ウェーブ構成設計

| 行番号 | element_id | condition_type | condition_value | action_value | summon_count | is_damage_invalidation | 備考 |
|--------|-----------|----------------|----------------|-------------|------------|------------------------|------|
| 1 | `1` | `InitialSummon` | `0` | `c_chi_00002_chi_dungeon_Boss_Yellow` | 1 | `1` | ボス即時配置、ゲートダメージ無効 |
| 2 | `2` | `ElapsedTime` | `1500` | `e_chi_00101_chi_dungeon_Normal_Yellow` | 1 | （空） | 護衛ゾンビ第1波（1,500ms後） |
| 3 | `3` | `ElapsedTime` | `2500` | `e_chi_00101_chi_dungeon_Normal_Yellow` | 1 | （空） | 護衛ゾンビ第2波（2,500ms後） |

**シーケンス設計ポイント:**
- ボスはInitialSummon（condition_value=0）で即時配置し、summon_position=1.7（砦付近）に配置
- ボス行に `is_summon_unit_outpost_damage_invalidation=1` を設定してゲートダメージを無効化
- ボスのmove_start_condition_type=Damageで1ダメージ受けたら前進開始（砦付近待機パターン）
- aura_type=Bossでボスアウラ演出
- 雑魚はElapsedTimeで時間差出現（デフォルトグループのみ・グループ切り替えなし）

## MstPage / MstKomaLine 構成

- ページ数: 1（id: `dungeon_chi_boss_00001`）
- コマ行数: **1行**（dungeonボスブロック固定値）
- コマ効果: None（エフェクトなし）
- コマアセットキー: `glo_00016`（chiシリーズ代表背景アセット）
- コマ幅: 1.0（全幅1コマ）
- koma_line_layout_asset_key: `1`（1コマ全幅パターン）

## MstEnemyOutpost 設計

| カラム | 値 |
|--------|-----|
| `id` | `dungeon_chi_boss_00001` |
| `hp` | `1,000`（dungeonボスブロック固定値） |
| `is_damage_invalidation` | （空）※ゲートダメージはシーケンス側で制御 |
| `outpost_asset_key` | （空） |
| `artwork_asset_key` | （空） |

## 参照した既存データ

- 参照ステージID: `dungeon_spy_normal_00001`（SPYシリーズnormalブロック参考CSVから列構造を確認）
- `c_chi_00002_general_Boss_Yellow`: HP=400,000 / attack_power=450 / move_speed=50 / attack_combo_cycle=5 / Technical / Yellow
- `e_chi_00101_general_Normal_Yellow`: HP=13,000 / attack_power=720 / move_speed=35 / Technical / Yellow
- is_summon_unit_outpost_damage_invalidation=1の設定例を既存データから確認
- コマアセットキー: chiシリーズ既存KomaLineから `glo_00016` を確認

## 不確定事項・要確認事項

- release_keyは開発テスト用 `999999999` を使用（投入時に正式キーへ変更が必要）
- 背景アセット `glo_00016` はchiシリーズ代表として使用（他作品との共有アセット）
