# インゲームマスタデータ設計書

## 基本情報
- 生成日時: 2026-03-02
- コンテンツ種別: dungeon（限界チャレンジ）
- ステージ種別: dungeon_boss（ボスブロック）
- シリーズ: 幼稚園WARS（you）

## インゲームID命名案
- インゲームID: `dungeon_you_boss_00001`
- 命名根拠: `dungeon_{シリーズ}_boss_{連番5桁}` 規則に従い、you シリーズのボスブロック第1弾

## 生成対象テーブル一覧
| テーブル | 生成件数 | 備考 |
|---------|---------|------|
| MstEnemyStageParameter | 2件 | ボス1体（chara_you_00001 / リタ）、護衛雑魚1体（enemy_glo_00001） |
| MstEnemyOutpost | 1件 | HP: 1,000（dungeon_boss固定） |
| MstPage | 1件 | id = dungeon_you_boss_00001 |
| MstKomaLine | 1件 | 1行構成（dungeon_boss固定） |
| MstAutoPlayerSequence | 4件 | ボス初期配置1行 + 護衛雑魚3行 |
| MstInGame | 1件 | id = dungeon_you_boss_00001 |

## MstInGame 主要パラメータ設計
- `bgm_asset_key`: `SSE_SBG_003_001`（要件テキスト指定: 通常BGM）
- `boss_bgm_asset_key`: `SSE_SBG_003_004`（要件テキスト指定: ボス登場時BGM）
- `loop_background_asset_key`: `you_00001`（要件テキスト指定）
- `player_outpost_asset_key`: 空（未設定、投入時に確認）
- `normal_enemy_hp_coef`: `1`（基本等倍）
- `normal_enemy_attack_coef`: `1`（基本等倍）
- `normal_enemy_speed_coef`: `1`（基本等倍）
- `boss_enemy_hp_coef`: `1`（基本等倍、ボスHPはパラメータで制御）
- `boss_enemy_attack_coef`: `1`（基本等倍）
- `boss_enemy_speed_coef`: `1`（基本等倍）
- `boss_mst_enemy_stage_parameter_id`: `c_you_00001_you_dungeon_Boss_Red`
- `boss_count`: `1`
- `release_key`: `999999999`（dungeon限界チャレンジのリリースキーが未定のため暫定値）

## MstEnemyOutpost 設計
| カラム | 値 | 根拠 |
|--------|-----|------|
| `hp` | `1000` | dungeon_boss 固定値 |
| `is_damage_invalidation` | 空（ダメージ有効） | ボス撃破後にゲートダメージ有効化仕様のため通常設定（ゲームロジック側で制御） |
| `artwork_asset_key` | `you_00001` | you系ループ背景アセット |
| `outpost_asset_key` | `you_00001` | you系ループ背景アセット |

## MstEnemyStageParameter 敵パラメータ設計

### ボス: 元殺し屋の新人教諭 リタ（chara_you_00001）
- dungeon_boss 仕様に基づき通常イベントより低HP・高攻撃力で設定
- 要件指定: HP=10,000程度（base）、攻撃力=500程度、移動速度=速め（move_speed=45）
- 攻撃コンボ: 6回、必殺ワザあり
- 属性: Red（赤属性）、青属性キャラが有利

| 識別子 | 役割 | mst_enemy_character_id | HP | 攻撃力 | スピード |
|--------|------|----------------------|----|-------|--------|
| `c_you_00001_you_dungeon_Boss_Red` | ボス | `chara_you_00001` | 10,000 | 500 | 45 |

- `character_unit_kind`: `Boss`
- `color`: `Red`（赤属性）
- `well_distance`: `0.35`（ボスらしい広めの索敵範囲）
- `damage_knock_back_count`: `3`（ボスらしいノックバック耐性）
- `attack_combo_cycle`: `6`（要件指定: 6回コンボ）
- `mst_unit_ability_id1`: 空（必殺ワザ有りだが ability_id は別途設定）
- `drop_battle_point`: `1000`

### 護衛雑魚: 組織の刺客（enemy_glo_00001 / GLO汎用敵、無属性）
- 護衛としてボスとの同時対処でプレイヤーへの圧力を高める設計
- 無属性（Colorless）、要件指定のGLO汎用敵

| 識別子 | 役割 | mst_enemy_character_id | HP | 攻撃力 | スピード |
|--------|------|----------------------|----|-------|--------|
| `e_glo_00001_you_dungeon_Normal_Colorless` | 護衛雑魚 | `enemy_glo_00001` | 3,000 | 400 | 45 |

- `character_unit_kind`: `Normal`
- `color`: `Colorless`（無属性）
- `well_distance`: `0.30`（普通の索敵範囲）
- `damage_knock_back_count`: `1`
- `attack_combo_cycle`: `1`
- `drop_battle_point`: `200`

## MstAutoPlayerSequence ウェーブ構成設計
- 総シーケンス行数: 4行（ボス1行 + 護衛雑魚3行）
- グループ切り替え: なし（シングルグループ）

| 行番号 | condition_type | condition_value | action_type | action_value | summon_count | 備考 |
|--------|---------------|----------------|-------------|-------------|-------------|------|
| 1 | `InitialSummon` | `1` | `SummonEnemy` | `c_you_00001_you_dungeon_Boss_Red` | `1` | ボスをゲート前（summon_position=1.7）に配置 |
| 2 | `ElapsedTime` | `2000` | `SummonEnemy` | `e_glo_00001_you_dungeon_Normal_Colorless` | `2` | バトル開始20秒後に護衛雑魚登場（1回目） |
| 3 | `ElapsedTime` | `5000` | `SummonEnemy` | `e_glo_00001_you_dungeon_Normal_Colorless` | `3` | バトル開始50秒後に護衛雑魚（2回目） |
| 4 | `ElapsedTime` | `9000` | `SummonEnemy` | `e_glo_00001_you_dungeon_Normal_Colorless` | `3` | バトル開始90秒後に護衛雑魚（3回目） |

- ボス行: `summon_position=1.7`、`move_start_condition_type=Damage`、`move_start_condition_value=1`（ダメージを受けたら前進）
- `aura_type`: ボス=`Boss`、雑魚=`Default`
- `death_type`: `Normal`
- 全行 `enemy_hp_coef=1`、`enemy_attack_coef=1`、`enemy_speed_coef=1`

## MstPage / MstKomaLine 構成
- ページ数: 1（id: `dungeon_you_boss_00001`）
- コマ行数: **1行**（dungeon_boss 固定）
- コマ効果: なし（None）
- koma1_asset_key: `you_00001`（you系ループ背景アセット）
- koma1_width: `1.0`（1コマ全幅）
- height: `1.0`（1行の場合は全高）
- koma_line_layout_asset_key: `1`（1コマ全幅パターン）

## 参照した既存データ
- SPY boss設計書（dungeon_spy_boss_00001）: ボスブロック設計のパターン参照
- SPY normalのMstAutoPlayerSequence: シーケンスCSVフォーマット参照
- 最新リリースキー: 202604010（dungeon用は999999999を暫定使用）

## 不確定事項・要確認事項
- ボスの必殺ワザ（スキル）については `mst_unit_ability_id1` として既存の ability ID を設定する必要があるが、今回は空欄とし、投入時に追加設定することを推奨
- `outpost_asset_key` / `artwork_asset_key`: you系のdungeonブロック用アセットが未確定のため `you_00001`（ループ背景と同一）を暫定設定
- `player_outpost_asset_key`: 既存データに合わせて空欄
- `release_key`: dungeon限界チャレンジのリリースキーが未定のため `999999999` を暫定使用
- `is_damage_invalidation` の詳細挙動（ボス撃破後にゲートダメージが有効化される仕組み）はゲームロジック側で制御されるため、CSV上は空欄設定で問題なし
