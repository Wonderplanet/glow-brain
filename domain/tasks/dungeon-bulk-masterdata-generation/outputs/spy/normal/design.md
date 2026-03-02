# インゲームマスタデータ設計書

## 基本情報
- 生成日時: 2026-03-01
- コンテンツ種別: dungeon（限界チャレンジ）
- ステージ種別: dungeon_normal（通常ブロック）

## インゲームID命名案
- インゲームID: `dungeon_spy_normal_00001`
- 命名根拠: `dungeon_{シリーズ}_{ブロック種別}_{連番5桁}` の規則に従い、シリーズID=spy、ブロック種別=normal、連番=00001

## 生成対象テーブル一覧

| テーブル | 生成件数 | 備考 |
|---------|---------|------|
| MstEnemyStageParameter | 2件 | 雑魚2種（メイン: enemy_spy_00001、サブ: enemy_spy_00101） |
| MstEnemyOutpost | 1件 | HP: 100（dungeon_normal固定） |
| MstPage | 1件 | |
| MstKomaLine | 3件 | 3行構成（dungeon_normal固定） |
| MstAutoPlayerSequence | 5件 | 5行構成（グループ切り替えなし） |
| MstInGame | 1件 | ボスなし |

## MstInGame 主要パラメータ設計
- `content_type`: （content_typeカラムなし、mst_auto_player_sequence_set_idで参照）
- `bgm_asset_key`: `SSE_SBG_003_002`
- `boss_bgm_asset_key`: （空・ボスなし）
- `loop_background_asset_key`: `spy_00005`
- `mst_page_id`: `dungeon_spy_normal_00001`
- `mst_enemy_outpost_id`: `dungeon_spy_normal_00001`
- `boss_mst_enemy_stage_parameter_id`: （空・ボスなし）
- `boss_count`: （空）
- `normal_enemy_hp_coef`: `1`
- `normal_enemy_attack_coef`: `1`
- `normal_enemy_speed_coef`: `1`
- `boss_enemy_hp_coef`: `1`
- `boss_enemy_attack_coef`: `1`
- `boss_enemy_speed_coef`: `1`
- `release_key`: `999999999`

## MstEnemyStageParameter 敵パラメータ設計

| 識別子 | 役割 | mst_enemy_character_id | HP | 攻撃力 | スピード | well_distance | 備考 |
|--------|------|----------------------|-----|-------|--------|--------------|------|
| `e_spy_00001_spy_dungeon_Normal_Colorless` | 雑魚メイン（密輸組織の残党） | `enemy_spy_00001` | 1000 | 200 | 40 | 0.32 | 必殺ワザなし |
| `e_spy_00101_spy_dungeon_Normal_Colorless` | 雑魚サブ（グエン） | `enemy_spy_00101` | 1000 | 200 | 38 | 0.20 | 必殺ワザなし |

**パラメータ設計根拠:**
- HPは1,000（要件通り）。MstInGameのcoef=1のため最終HP=1,000
- 攻撃力は既存SPY雑魚（attack_power=50〜200）より高め（dungeon向けの高難度設定）にenemy_attack_coef側で調整。基準値200に設定し、シーケンスのenemy_attack_coefで5,000程度になるよう倍率25を設定
- move_speedは「少し速め」の要件通り40前後（enemy_spy_00001参考値: 34→dungeon向けに40〜42に設定）
- 無属性（Colorless）、必殺ワザなし（mst_unit_ability_id1は空）

## MstAutoPlayerSequence ウェーブ構成設計

- 総行数: 5行
- グループ切り替え: なし（シングルグループ）
- 構成思想: グエン（サブ）が先行して近づいてきた後、密輸組織の残党（メイン）が波状に出現する

| 行 | condition_type | condition_value | action_value | summon_count | 説明 |
|----|---------------|-----------------|--------------|-------------|------|
| 1 | `ElapsedTime` | `250` | `e_spy_00101_spy_dungeon_Normal_Colorless` | 1 | グエンが先行出現 |
| 2 | `ElapsedTime` | `800` | `e_spy_00001_spy_dungeon_Normal_Colorless` | 2 | 残党が2体出現 |
| 3 | `ElapsedTime` | `1500` | `e_spy_00001_spy_dungeon_Normal_Colorless` | 3 | 残党が3体波状出現（interval=1000） |
| 4 | `ElapsedTime` | `3000` | `e_spy_00101_spy_dungeon_Normal_Colorless` | 1 | グエンが再出現 |
| 5 | `ElapsedTime` | `4500` | `e_spy_00001_spy_dungeon_Normal_Colorless` | 3 | 残党が追加波状出現（interval=1000） |

- enemy_hp_coef: 1（MstInGame側で一律管理）
- enemy_attack_coef: 25（攻撃力200 × 25 = 5,000相当。要件の攻撃力5,000程度に合致）
- enemy_speed_coef: 1

## MstPage / MstKomaLine 構成

- ページ数: 1（id: `dungeon_spy_normal_00001`）
- コマ行数: 3行（dungeon_normal固定）
- コマ効果: None（要件通り、コマ効果なし）
- アセットキー: `spy_00005`（ループ背景に合わせてSPYシリーズのアセット使用）

| row | height | layout | koma構成 | 備考 |
|-----|--------|--------|----------|------|
| 1 | 0.34 | 9 | koma1(0.25) + koma2(0.5) + koma3(0.25) | 3コマ均等配置 |
| 2 | 0.33 | 9 | koma1(0.25) + koma2(0.5) + koma3(0.25) | 3コマ均等配置 |
| 3 | 0.33 | 9 | koma1(0.25) + koma2(0.5) + koma3(0.25) | 3コマ均等配置 |

- height合計: 0.34 + 0.33 + 0.33 = 1.0（OK）
- koma幅合計: 0.25 + 0.5 + 0.25 = 1.0（OK）

## 参照した既存データ

- 参照データ: `event_spy1_charaget01_00001`〜`event_spy1_charaget01_00008`（SPYシリーズイベントクエスト）
- 参照シリーズ: spy系MstEnemyStageParameter（enemy_spy_00001, enemy_spy_00101の既存パラメータ確認）
- 既存SPYシリーズのKomaLine: `spy_00005`アセット使用実績あり（event_spy1_charaget01_00002で確認）

## 不確定事項・要確認事項

- `outpost_asset_key` / `artwork_asset_key`: dungeonブロック用アセットが未定のため空欄とする（後で設定）
- `player_outpost_asset_key`: MstInGameのプレイヤー砦アセット（既存データに合わせて空欄）
- `mst_defense_target_id`: 既存データを見ると空欄が多いため空欄とする
- `release_key`: dungeon限界チャレンジのリリースキーが未定のため `999999999` を使用
