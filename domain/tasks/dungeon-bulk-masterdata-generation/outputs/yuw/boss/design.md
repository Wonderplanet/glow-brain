# インゲームマスタデータ設計書

## 基本情報
- 生成日時: 2026-03-02
- コンテンツ種別: dungeon（限界チャレンジ）
- ステージ種別: dungeon_boss（ボスブロック）
- 対象シリーズ: 2.5次元の誘惑（yuw）

## インゲームID命名案
- インゲームID: `dungeon_yuw_boss_00001`
- 命名根拠: dungeon_{シリーズ}_boss_{連番5桁} の規則に従い、yuwシリーズの初回ボスブロックとして命名

## 生成対象テーブル一覧

| テーブル | 生成件数 | 備考 |
|---------|---------|------|
| MstEnemyStageParameter | 2件 | ボス1体（乃愛）、護衛雑魚1種（リリサ） |
| MstEnemyOutpost | 1件 | HP: 1,000（dungeon boss固定値） |
| MstPage | 1件 | |
| MstKomaLine | 1件 | 1行構成（dungeon boss固定） |
| MstAutoPlayerSequence | 3件 | 3行構成（ボス即時+護衛×2時間差） |
| MstInGame | 1件 | |

## MstInGame 主要パラメータ設計

| パラメータ | 値 | 根拠 |
|-----------|-----|------|
| `id` | `dungeon_yuw_boss_00001` | インゲームID命名規則に従う |
| `mst_auto_player_sequence_set_id` | `dungeon_yuw_boss_00001` | sequence_set_idと一致 |
| `mst_page_id` | `dungeon_yuw_boss_00001` | MstPage.idと一致 |
| `mst_enemy_outpost_id` | `dungeon_yuw_boss_00001` | MstEnemyOutpost.idと一致 |
| `bgm_asset_key` | `SSE_SBG_003_006` | yuwシリーズ固有の通常バトルBGM |
| `boss_bgm_asset_key` | `SSE_SBG_003_007` | yuwシリーズのボス専用BGM（ボス出現時に切替） |
| `loop_background_asset_key` | `yuw_00001` | yuwシリーズの背景アセット |
| `boss_mst_enemy_stage_parameter_id` | `c_yuw_00301_yuw_dungeon_Boss_Blue` | ボス乃愛のパラメータID |
| `boss_count` | `1` | ボス1体 |
| `normal_enemy_hp_coef` | `1` | 等倍（dungeon仕様） |
| `normal_enemy_attack_coef` | `1` | 等倍 |
| `normal_enemy_speed_coef` | `1` | 等倍 |
| `boss_enemy_hp_coef` | `1` | 等倍（dungeon仕様） |
| `boss_enemy_attack_coef` | `1` | 等倍 |
| `boss_enemy_speed_coef` | `1` | 等倍 |
| `release_key` | `999999999` | 投入リリース未定につきデフォルト値 |
| `result_tips.ja` | 「ボスを倒すまで敵ゲートにダメージが入りません...」 | 要件テキストより |
| `description.ja` | 「乃愛がボスとして立ちはだかる...」 | 要件テキストより |

## MstEnemyStageParameter 敵パラメータ設計

| 識別子 | 役割 | mst_enemy_character_id | HP | 攻撃力 | スピード | well_distance | KBCount | AtkCombo |
|--------|------|----------------------|-----|-------|---------|--------------|---------|---------|
| `c_yuw_00301_yuw_dungeon_Boss_Blue` | ボス（乃愛） | `chara_yuw_00301` | 15,000 | 800 | 29 | 0.23 | 3 | 6 |
| `c_yuw_00001_yuw_dungeon_Normal_Yellow` | 護衛雑魚（リリサ） | `chara_yuw_00001` | 10,000 | 320 | 34 | 0.24 | 1 | 1 |

**設計根拠:**
- ボス（乃愛）: Blue属性・Technical。dungeon boss仕様「HPは低め・攻撃力は高め」に従い、HP=15,000/攻撃力=800。速度29は「遅い」寄りで砦付近からじっくり戦う演出。
- 護衛（リリサ）: Yellow属性・Attack。時間差で2体出現する護衛役。HP=10,000/攻撃力=320で適度な強度。速度34でやや素早く前進してくる。

## MstAutoPlayerSequence ウェーブ構成設計

| 行 | element_id | condition_type | condition_value | action_type | action_value | 体数 | summon_position | move_start | 特記 |
|----|-----------|----------------|----------------|-------------|-------------|------|----------------|------------|------|
| 1 | `1` | InitialSummon | 0 | SummonEnemy | `c_yuw_00301_yuw_dungeon_Boss_Blue` | 1 | 1.7（砦付近） | Damage/1 | ボス乃愛初期配置・ゲートダメージ無効 |
| 2 | `2` | ElapsedTime | 3000（30×100ms） | SummonEnemy | `c_yuw_00001_yuw_dungeon_Normal_Yellow` | 1 | （空） | None | 護衛リリサ1体目 |
| 3 | `3` | ElapsedTime | 6000（60×100ms） | SummonEnemy | `c_yuw_00001_yuw_dungeon_Normal_Yellow` | 1 | （空） | None | 護衛リリサ2体目 |

- グループ切り替え: なし（デフォルトグループ単一構成）
- ボス行（行1）: `is_summon_unit_outpost_damage_invalidation=1`でゲートダメージ無効を実装
- aura_type: ボスはBoss、護衛雑魚はDefault

## MstPage / MstKomaLine 構成

| 項目 | 値 |
|------|-----|
| ページ数 | 1 |
| コマ行数 | 1行（dungeon boss固定） |
| コマアセット | `yuw_00001` |
| コマ幅 | 1.0（全幅単一レイアウト） |
| コマ効果 | None（エフェクトなし） |
| layout_asset_key | `1`（1コマ全幅） |
| height | 1.0 |

## MstEnemyOutpost 設定

| 項目 | 値 |
|------|-----|
| id | `dungeon_yuw_boss_00001` |
| hp | 1,000（dungeon boss固定値） |
| is_damage_invalidation | 空（ダメージ有効）※ボス生存中の無効はシーケンスで制御 |

## 参照した既存データ

- 参照ブロック: `dungeon_chi_boss_00001`（チェンソーマン・ボスブロック）
  - MstInGame の構造・列順を参照
  - MstAutoPlayerSequence の行フォーマットを参照
  - MstKomaLine の1行構成パターンを参照

## 不確定事項・要確認事項

- なし（インゲーム要件テキストに必要な情報がすべて明示されている）
