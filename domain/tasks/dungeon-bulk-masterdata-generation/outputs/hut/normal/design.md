# インゲームマスタデータ設計書

## 基本情報
- 生成日時: 2026-03-01
- コンテンツ種別: dungeon（限界チャレンジ）通常ブロック（Normal Block）
- ステージ種別: dungeon_normal
- シリーズID: hut（ふつうの軽音部）

## インゲームID命名案
- インゲームID: `dungeon_hut_normal_00001`
- 命名根拠: `dungeon_{シリーズ}_normal_{連番5桁}` の規則に従い、hut シリーズの通常ブロック第1弾として命名

## 生成対象テーブル一覧
| テーブル | 生成件数 | 備考 |
|---------|---------|------|
| MstEnemyStageParameter | 1件 | 雑魚1体（enemy_glo_00001 / Colorless） |
| MstEnemyOutpost | 1件 | HP: 100（dungeon normal 固定値） |
| MstPage | 1件 | |
| MstKomaLine | 3件 | 3行固定（dungeon normal 仕様） |
| MstAutoPlayerSequence | 5件 | 5行構成・ElapsedTimeによる時間差配置 |
| MstInGame | 1件 | |

## MstInGame 主要パラメータ設計
- `bgm_asset_key`: `SSE_SBG_003_002`（ふつうの軽音部通常バトル曲、既存hutイベントと同一）
- `boss_bgm_asset_key`: 空（ボスなし）
- `loop_background_asset_key`: 空（hut専用背景アセット未存在）
- `mst_page_id`: `dungeon_hut_normal_00001`
- `mst_enemy_outpost_id`: `dungeon_hut_normal_00001`
- `mst_auto_player_sequence_set_id`: `dungeon_hut_normal_00001`
- `boss_mst_enemy_stage_parameter_id`: 空（ボスなし）
- `boss_count`: 空
- `normal_enemy_hp_coef`: `1`（等倍）
- `normal_enemy_attack_coef`: `1`（等倍）
- `normal_enemy_speed_coef`: `1`（等倍）
- `boss_enemy_hp_coef`: `1`
- `boss_enemy_attack_coef`: `1`
- `boss_enemy_speed_coef`: `1`
- `release_key`: `999999999`（開発・テスト用）

## MstEnemyStageParameter 敵パラメータ設計

| 識別子 | 役割 | mst_enemy_character_id | HP | 攻撃力 | スピード | 索敵距離 |
|--------|------|----------------------|-----|-------|--------|---------|
| `e_glo_00001_hut_dungeon_Normal_Colorless` | 雑魚（メイン） | `enemy_glo_00001` | 1,000 | 100 | 40 | 0.2 |

- character_unit_kind: `Normal`
- role_type: `Attack`
- color: `Colorless`
- damage_knock_back_count: `1`
- attack_combo_cycle: `1`
- drop_battle_point: `100`
- enemy_hp_coef（シーケンス）: `1.0`（仕様固定値をそのまま使用）
- 命名根拠: `e_glo_00001_{インゲームID短縮形}_Normal_Colorless` → dungeon系短縮形は `hut_dungeon`

## MstEnemyOutpost 設計
- id: `dungeon_hut_normal_00001`
- hp: `100`（dungeon normal 固定値）
- is_damage_invalidation: 空（ダメージ有効）
- artwork_asset_key: 空（dungeon専用アセットなし）

## MstAutoPlayerSequence ウェーブ構成設計
- 総ウェーブ数: 5行（グループ切り替えなし・デフォルトグループのみ）
- ElapsedTimeトリガーによる時間差配置を採用

| 行番号 | condition_type | condition_value（ms） | action_value | summon_count | 備考 |
|--------|---------------|----------------------|-------------|-------------|------|
| 1 | ElapsedTime | 500 | e_glo_00001_hut_dungeon_Normal_Colorless | 1 | 先行出現、慣れさせる時間確保 |
| 2 | ElapsedTime | 1,000 | e_glo_00001_hut_dungeon_Normal_Colorless | 1 | |
| 3 | ElapsedTime | 2,000 | e_glo_00001_hut_dungeon_Normal_Colorless | 1 | |
| 4 | ElapsedTime | 3,000 | e_glo_00001_hut_dungeon_Normal_Colorless | 1 | |
| 5 | ElapsedTime | 4,500 | e_glo_00001_hut_dungeon_Normal_Colorless | 1 | 徐々に圧力を高める |

- enemy_hp_coef: 1.0（MstInGame側も1倍 → 最終HP 1,000 × 1.0 × 1.0 = 1,000）
- enemy_attack_coef: 1.0
- enemy_speed_coef: 1
- aura_type: Default（雑魚）
- death_type: Normal

## MstPage / MstKomaLine 構成
- ページ数: 1
- コマ行数: 3行（dungeon normal 固定）
- コマ効果: None（全コマ）
- コマアセット: `glo_00014`（hut pvpステージで使用実績ある汎用アセット）
- 3行構成の高さ設計:
  - row=1: height=0.34（2コマ: 0.4 / 0.6 → layout_key=3）
  - row=2: height=0.33（1コマ: 1.0 → layout_key=1）
  - row=3: height=0.33（2コマ: 0.6 / 0.4 → layout_key=2）

## バトルテキスト
- result_tips.ja: 「無属性の敵が登場するぞ！素早く迎撃して限界チャレンジを突破せよ！」
- description.ja: 「無属性の敵が次々と現れる。キャラを上手に操作して敵を倒し、ゲートを守り抜こう！」

## 参照した既存データ
- 参照ステージID: `event_hut1_charaget01_*`（hut1シリーズ：BGM=SSE_SBG_003_002、コマアセット=glo_00014）
- 参照パラメータ: `e_glo_00001_hut1_charaget01_Normal_Blue`（HP=1,000、move_speed=40、well_distance=0.2、attack_power=100）
- MstInGame倍率: 全倍率 1.0（既存hutシリーズと同一パターン）
- KomaLine height: 0.55（既存パターン） → 3行合計にする場合は各行調整

## 不確定事項・要確認事項
- dungeon向けのリリースキーは 999999999 を仮設定（正式リリース時に変更が必要）
- コマアセットは hut pvp（glo_00014）を踏襲。要件テキスト記載通り
