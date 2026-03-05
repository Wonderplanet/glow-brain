# インゲームマスタデータ設計書

## 基本情報
- 生成日時: 2026-03-01
- コンテンツ種別: dungeon（限界チャレンジ）
- ステージ種別: dungeon_boss（ボスブロック）
- シリーズ: SPY×FAMILY（spy）

## インゲームID命名案
- インゲームID: `dungeon_spy_boss_00001`
- 命名根拠: dungeon_{シリーズ}_boss_{連番5桁} 規則に従い、spy シリーズのボスブロック第1弾

## 生成対象テーブル一覧
| テーブル | 生成件数 | 備考 |
|---------|---------|------|
| MstEnemyStageParameter | 2件 | ボス1体（chara_spy_00101）、護衛雑魚1体（enemy_spy_00001） |
| MstEnemyOutpost | 1件 | HP: 1,000（dungeon_boss固定） |
| MstPage | 1件 | id = dungeon_spy_boss_00001 |
| MstKomaLine | 1件 | 1行構成（dungeon_boss固定） |
| MstAutoPlayerSequence | 4件 | ボス初期配置1行 + 護衛雑魚3行 |
| MstInGame | 1件 | id = dungeon_spy_boss_00001 |

## MstInGame 主要パラメータ設計
- `bgm_asset_key`: `SSE_SBG_003_002`（要件テキスト指定）
- `boss_bgm_asset_key`: 空（dungeon_boss では通常設定しない）
- `loop_background_asset_key`: `spy_00005`（要件テキスト指定）
- `player_outpost_asset_key`: `spy_00005`（spy系共通アセット）
- `normal_enemy_hp_coef`: `1`（基本等倍）
- `normal_enemy_attack_coef`: `1`（基本等倍）
- `normal_enemy_speed_coef`: `1`（基本等倍）
- `boss_enemy_hp_coef`: `1`（基本等倍、ボスHP はパラメータで制御）
- `boss_enemy_attack_coef`: `1`（基本等倍）
- `boss_enemy_speed_coef`: `1`（基本等倍）
- `boss_mst_enemy_stage_parameter_id`: `c_spy_00101_spy_dungeon_Boss_Blue`
- `boss_count`: `1`

## MstEnemyOutpost 設計
| カラム | 値 | 根拠 |
|--------|-----|------|
| `hp` | `1,000` | dungeon_boss 固定値 |
| `is_damage_invalidation` | 空（ダメージ有効） | ボス撃破後にゲートダメージ有効化仕様のため通常設定 |
| `artwork_asset_key` | `spy_00005` | spy 系共通アセット |
| `outpost_asset_key` | `spy_00005` | spy 系共通アセット |

## MstEnemyStageParameter 敵パラメータ設計

### ボス: ＜黄昏＞ロイド（chara_spy_00101）
- dungeon_boss 仕様に基づき通常イベントより低HP・高攻撃力で設定
- 既存参照: c_spy_00101_spy1savage_Boss_Blue（HP=25,000, move_speed=30, attack_power=400）
- dungeon_boss は通常よりHP低め・攻撃力高めが設計方針

| 識別子 | 役割 | mst_enemy_character_id | HP | 攻撃力 | スピード |
|--------|------|----------------------|----|-------|--------|
| `c_spy_00101_spy_dungeon_Boss_Blue` | ボス | `chara_spy_00101` | 50,000 | 15,000 | 40 |

- `character_unit_kind`: `Boss`
- `color`: `Blue`（要件指定: 青属性、黄属性有利）
- `well_distance`: `0.35`（既存spy101 ボスと同水準）
- `damage_knock_back_count`: `3`
- `attack_combo_cycle`: `6`（ボスらしいコンボ演出）
- `mst_unit_ability_id1`: 空（必殺ワザ有りだが ability_id は別途設定）
- `drop_battle_point`: `1000`

### 護衛雑魚: 密輸組織の残党（enemy_spy_00001）
- 既存参照: e_spy_00001_spy1savage_Normal_Colorless（HP=5,000, move_speed=70, attack_power=300）
- dungeon_boss 護衛としてやや強め設定

| 識別子 | 役割 | mst_enemy_character_id | HP | 攻撃力 | スピード |
|--------|------|----------------------|----|-------|--------|
| `e_spy_00001_spy_dungeon_Normal_Colorless` | 護衛雑魚 | `enemy_spy_00001` | 3,000 | 500 | 40 |

- `character_unit_kind`: `Normal`
- `color`: `Colorless`（無属性）
- `well_distance`: `0.4`（既存 enemy_spy_00001 と同水準）
- `damage_knock_back_count`: `2`
- `attack_combo_cycle`: `1`
- `drop_battle_point`: `300`

## MstAutoPlayerSequence ウェーブ構成設計
- 総シーケンス行数: 4行（ボス1行 + 護衛雑魚3行）
- グループ切り替え: なし（シングルグループ）

| 行番号 | condition_type | condition_value | action_type | action_value | summon_count | 備考 |
|--------|---------------|----------------|-------------|-------------|-------------|------|
| 1 | `InitialSummon` | `1` | `SummonEnemy` | `c_spy_00101_spy_dungeon_Boss_Blue` | `1` | ボスをゲート前（summon_position=1.7）に配置 |
| 2 | `ElapsedTime` | `2000` | `SummonEnemy` | `e_spy_00001_spy_dungeon_Normal_Colorless` | `2` | バトル開始20秒後に護衛雑魚登場（1回目） |
| 3 | `ElapsedTime` | `5000` | `SummonEnemy` | `e_spy_00001_spy_dungeon_Normal_Colorless` | `3` | バトル開始50秒後に護衛雑魚（2回目） |
| 4 | `ElapsedTime` | `9000` | `SummonEnemy` | `e_spy_00001_spy_dungeon_Normal_Colorless` | `3` | バトル開始90秒後に護衛雑魚（3回目） |

- ボス行: `summon_position=1.7`、`move_start_condition_type=Damage`、`move_start_condition_value=1`（ダメージを受けたら移動開始）
- `aura_type`: ボス=`Boss`、雑魚=`Default`
- `death_type`: `Normal`
- 全行 `enemy_hp_coef=1`、`enemy_attack_coef=1`、`enemy_speed_coef=1`

## MstPage / MstKomaLine 構成
- ページ数: 1
- コマ行数: **1行**（dungeon_boss 固定）
- コマ効果: なし（None）
- koma1_asset_key: `spy_00005`（spy系共通ループ背景）
- koma1_width: `1.0`（1コマ全幅）
- height: `1.0`（1行の場合は全高）
- koma_line_layout_asset_key: `1`（1コマ全幅パターン）

## 参照した既存データ
- spy 系 KomaLine: event_spy1_challenge01_00001（3行構成・spy_00001 アセット）
- spy ボスパラメータ: c_spy_00101_spy1savage_Boss_Blue（HP=25,000, speed=30, attack=400）
- spy 雑魚パラメータ: e_spy_00001_spy1savage_Normal_Colorless（HP=5,000, speed=70, attack=300）
- 最新リリースキー: 202604010

## 不確定事項・要確認事項
- ボスの必殺ワザ（スキル）については mst_unit_ability_id1 として既存の ability ID を設定する必要があるが、今回は空欄とし、投入時に追加設定することを推奨
- is_damage_invalidation の詳細挙動（ボス撃破後にゲートダメージが有効化される仕組み）はゲームロジック側で制御されるため、CSV上は通常通り空欄設定で問題なし
