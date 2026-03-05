# インゲームマスタデータ設計書

## 基本情報
- 生成日時: 2026-03-01
- コンテンツ種別: dungeon（限界チャレンジ）
- ステージ種別: dungeon_normal（通常ブロック）
- シリーズ: 地獄楽（jig）

## インゲームID命名案
- インゲームID: `dungeon_jig_normal_00001`
- 命名根拠: `dungeon_{シリーズ}_normal_{連番5桁}` の命名規則に従う。シリーズID=jig、通常ブロック第1弾

## 生成対象テーブル一覧
| テーブル | 生成件数 | 備考 |
|---------|---------|------|
| MstEnemyStageParameter | 2件 | 雑魚2体（門神・極楽蝶） |
| MstEnemyOutpost | 1件 | HP: 100（dungeon固定） |
| MstPage | 1件 | |
| MstKomaLine | 3件 | 3行構成（dungeon_normal固定） |
| MstAutoPlayerSequence | 5件 | 5ウェーブ構成（ElapsedTimeのみ） |
| MstInGame | 1件 | |

## MstInGame 主要パラメータ設計
- `id`: `dungeon_jig_normal_00001`
- `bgm_asset_key`: `SSE_SBG_003_003`（地獄楽通常BGM）
- `boss_bgm_asset_key`: 空（ボスなし）
- `loop_background_asset_key`: `jig_00002`
- `mst_page_id`: `dungeon_jig_normal_00001`
- `mst_enemy_outpost_id`: `dungeon_jig_normal_00001`
- `boss_mst_enemy_stage_parameter_id`: 空（ボスなし）
- `boss_count`: `0`
- `normal_enemy_hp_coef`: `1`（等倍）
- `normal_enemy_attack_coef`: `1`（等倍）
- `normal_enemy_speed_coef`: `1`（等倍）
- `boss_enemy_hp_coef`: `1`
- `boss_enemy_attack_coef`: `1`
- `boss_enemy_speed_coef`: `1`
- `release_key`: `999999999`
- 参照: SPY dungeon_normal_00001と同パターンを使用

## MstEnemyOutpost 設計
- `id`: `dungeon_jig_normal_00001`
- `hp`: `100`（dungeon_normal固定値）
- `is_damage_invalidation`: 空（ダメージ有効）
- `outpost_asset_key`: 空
- `artwork_asset_key`: 空

## MstEnemyStageParameter 敵パラメータ設計
| 識別子 | 役割 | mst_enemy_character_id | キャラクター名 | HP | 攻撃力 | スピード | color | role_type |
|--------|------|----------------------|-------------|-----|-------|--------|-------|----------|
| `e_jig_00001_mainquest_Normal_Colorless` | 雑魚A（壁役・防御型） | `enemy_jig_00001` | 門神 | 3,500 | 50 | 31 | Colorless | Defense |
| `e_jig_00401_mainquest_Normal_Colorless` | 雑魚B（追撃役・攻撃型） | `enemy_jig_00401` | 極楽蝶 | 3,000 | 100 | 32 | Colorless | Attack |

### パラメータ設計根拠
- `e_jig_00001_mainquest_Normal_Colorless`: 既存データに存在（`enemy_jig_00001`で使用実績あり）。防御タイプで低速の壁役。HP3500は通常雑魚（1,000〜10,000）の範囲内で、防御型として若干高め
- `e_jig_00401_mainquest_Normal_Colorless`: 既存データ確認済み（hp=3000, move_speed=32, attack_power=100が既存値）。攻撃型・大量召喚向けの設定
- MstInGame.normal_enemy_*_coef = 1（全等倍）のため、ここに設定した値がそのまま最終値となる

## MstAutoPlayerSequence ウェーブ構成設計
- 総シーケンス行数: 5行
- グループ切り替え: なし（デフォルトグループのみ）
- トリガー種別: ElapsedTimeのみ（FriendUnitDeadトリガー不使用）

| 行 | id | condition_type | condition_value | action_value | summon_count | summon_interval |
|----|-----|--------------|-----------------|-------------|-------------|----------------|
| 1 | dungeon_jig_normal_00001_1 | ElapsedTime | 250 | e_jig_00401_mainquest_Normal_Colorless | 10 | 800 |
| 2 | dungeon_jig_normal_00001_2 | ElapsedTime | 700 | e_jig_00001_mainquest_Normal_Colorless | 5 | 1200 |
| 3 | dungeon_jig_normal_00001_3 | ElapsedTime | 3000 | e_jig_00401_mainquest_Normal_Colorless | 15 | 600 |
| 4 | dungeon_jig_normal_00001_4 | ElapsedTime | 4500 | e_jig_00001_mainquest_Normal_Colorless | 10 | 1000 |
| 5 | dungeon_jig_normal_00001_5 | ElapsedTime | 7000 | e_jig_00401_mainquest_Normal_Colorless | 10 | 800 |

- 極楽蝶: 波状に大量出現（10体→15体→10体）。summon_intervalで時間差をつける
- 門神: 低速・防御型として後続で出現し前線を固める（5体→10体）
- 全て aura_type=Default、death_type=Normal

## MstPage / MstKomaLine 構成
- ページID: `dungeon_jig_normal_00001`（MstInGame.idと同値）
- ページ数: 1
- コマ行数: 3行（dungeon_normal固定）
- コマ効果: なし（None）
- 背景アセット: `jig_00002`

| row | id | height | koma構成 | layout_asset_key |
|-----|-----|--------|---------|----------------|
| 1 | dungeon_jig_normal_00001_1 | 0.33 | koma1(0.5) + koma2(0.5) | 6 |
| 2 | dungeon_jig_normal_00001_2 | 0.33 | koma1(0.4) + koma2(0.6) | 3 |
| 3 | dungeon_jig_normal_00001_3 | 0.34 | koma1(1.0) | 1 |

- SPYのdungeon_normal_00001（3行構成）を参考パターンとして採用
- height合計: 0.33 + 0.33 + 0.34 = 1.0

## 参照した既存データ
- 参照ステージID: `dungeon_spy_normal_00001`（SPY dungeon normalブロック）
  - HP=100、3行コマ構成、5シーケンス行のパターンを踏襲
- 参照パラメータ: `e_jig_00401_mainquest_Normal_Colorless`（hp=3000, move_speed=32, attack_power=100、既存値を活用）
- 地獄楽jig系KomaLine: `jig_00002`を背景アセットとして使用（challenge01_00002等で使用実績）

## 不確定事項・要確認事項
- `e_jig_00001_mainquest_Normal_Colorless` のIDが既存MstEnemyStageParameterに存在するか確認が必要（要件テキスト記載のIDだが、既存データにはenemy_jig_00001を参照するエントリが存在する）
- コマ背景オフセット値はSPY参考値を流用（-1.0 / 0.6 / 0.6）
