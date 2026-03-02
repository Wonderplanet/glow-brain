# インゲームマスタデータ設計書

## 基本情報
- 生成日時: 2026-03-02
- コンテンツ種別: dungeon（限界チャレンジ）
- ステージ種別: dungeon_normal（通常ブロック）
- シリーズ: yuw（2.5次元の誘惑）

## インゲームID命名案
- インゲームID: `dungeon_yuw_normal_00001`
- 命名根拠: dungeon_{シリーズ}_{ブロック種別}_{連番5桁} の命名規則に従う

## 生成対象テーブル一覧
| テーブル | 生成件数 | 備考 |
|---------|---------|------|
| MstEnemyStageParameter | 2件 | 雑魚2種（リリサ×1、美花莉×1） |
| MstEnemyOutpost | 1件 | HP: 100（dungeon normal 固定） |
| MstPage | 1件 | |
| MstKomaLine | 3件 | 3行構成（dungeon normal 固定） |
| MstAutoPlayerSequence | 5件 | 5行シーケンス |
| MstInGame | 1件 | |

## MstInGame 主要パラメータ設計
- `id`: `dungeon_yuw_normal_00001`
- `bgm_asset_key`: `SSE_SBG_003_006`（yuw固有通常バトルBGM）
- `boss_bgm_asset_key`: 空（ボスなし）
- `loop_background_asset_key`: `yuw_00001`
- `normal_enemy_hp_coef`: 1（等倍）
- `normal_enemy_attack_coef`: 1（等倍）
- `normal_enemy_speed_coef`: 1（等倍）
- `boss_enemy_hp_coef`: 1（等倍）
- `boss_enemy_attack_coef`: 1（等倍）
- `boss_enemy_speed_coef`: 1（等倍）
- `boss_count`: 1（固定値）
- `boss_mst_enemy_stage_parameter_id`: 空（ボスなし）

## MstEnemyStageParameter 敵パラメータ設計

| 識別子 | 役割 | mst_enemy_character_id | HP | 攻撃力 | スピード | 属性 | ロール |
|--------|------|----------------------|-----|-------|--------|-----|------|
| `c_yuw_00001_dungeon_Normal_Yellow` | 主力雑魚（メイン） | `chara_yuw_00001` | 10,000 | 320 | 34 | Yellow | Attack |
| `c_yuw_00101_dungeon_Normal_Green` | サブ雑魚（Technical型） | `chara_yuw_00101` | 12,000 | 280 | 29 | Green | Technical |

- 既存データ参照: `c_yuw_00001_*` は move_speed=34, well_distance=0.24 が実績値
- 既存データ参照: `c_yuw_00101_*` は move_speed=29, well_distance=0.25 が実績値
- dungeon専用パラメータとして新規作成（hp_coef=1.0で実HP=設定値のまま）

## MstAutoPlayerSequence ウェーブ構成設計
- 総行数: 5行
- グループ切り替え: なし（デフォルトグループ単一構成）

| 行 | element_id | condition_type | condition_value | action_value（敵ID） | summon_count |
|----|-----------|--------------|----------------|---------------------|-------------|
| 1 | 1 | ElapsedTime | 5 (=500ms) | `c_yuw_00001_dungeon_Normal_Yellow` | 1 |
| 2 | 2 | ElapsedTime | 20 (=2,000ms) | `c_yuw_00001_dungeon_Normal_Yellow` | 1 |
| 3 | 3 | ElapsedTime | 40 (=4,000ms) | `c_yuw_00101_dungeon_Normal_Green` | 1 |
| 4 | 4 | ElapsedTime | 60 (=6,000ms) | `c_yuw_00001_dungeon_Normal_Yellow` | 1 |
| 5 | 5 | ElapsedTime | 80 (=8,000ms) | `c_yuw_00101_dungeon_Normal_Green` | 1 |

## MstPage / MstKomaLine 構成
- ページ数: 1（id: `dungeon_yuw_normal_00001`）
- コマ行数: 3行（dungeon normal 固定）
- コマ効果: None（全コマ）
- コマアセット: `yuw_00001`

| 行 | id | row | height | koma_line_layout_asset_key | 構成 |
|----|-----|-----|--------|--------------------------|------|
| 行1 | `dungeon_yuw_normal_00001_1` | 1 | 0.34 | 2 | koma1(0.6) + koma2(0.4) |
| 行2 | `dungeon_yuw_normal_00001_2` | 2 | 0.33 | 4 | koma1(0.75) + koma2(0.25) |
| 行3 | `dungeon_yuw_normal_00001_3` | 3 | 0.33 | 1 | koma1(1.0) |

（合計 height: 0.34 + 0.33 + 0.33 = 1.0）

## MstEnemyOutpost 敵砦設定
- `id`: `dungeon_yuw_normal_00001`
- `hp`: 100（dungeon normal 固定）
- `is_damage_invalidation`: 空（ダメージ有効）
- `artwork_asset_key`: `yuw_00001`

## テキスト設定
- `result_tips.ja`: 黄属性の敵には緑属性、緑属性の敵には赤属性が有利です
- `description.ja`: コスプレの世界へようこそ！天乃リリサたちが進軍してくるぞ。黄属性と緑属性の敵が入り混じって登場するため、両属性に対応できる編成で挑もう！

## 参照した既存データ
- `normal_spy_00001` ～ `normal_spy_00005`: MstInGame の倍率設定パターン参照（全係数1.0）
- `pvp_yuw_01` / `pvp_yuw_02`: MstKomaLine の yuw アセットキー参照（`yuw_00001`）
- `c_yuw_00001_*` / `c_yuw_00101_*`: 既存パラメータのスピード・索敵距離参照
- リリースキー: `999999999`（投入予定リリースキー未定のため開発用）

## 不確定事項・要確認事項
- なし（インゲーム要件テキストは設計・承認済み）
