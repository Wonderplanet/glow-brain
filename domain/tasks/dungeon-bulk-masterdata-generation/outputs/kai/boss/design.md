# インゲームマスタデータ設計書

## 基本情報

- 生成日時: 2026-03-01
- コンテンツ種別: dungeon（限界チャレンジ）
- ステージ種別: dungeon_boss（ボスブロック）

---

## インゲームID

- インゲームID: `dungeon_kai_boss_00001`
- 命名根拠: `dungeon_{シリーズ}_boss_{連番5桁}` の命名規則に従い、kaiシリーズのボスブロック第1弾

---

## 生成対象テーブル一覧

| テーブル | 生成件数 | 備考 |
|---------|---------|------|
| MstEnemyStageParameter | 2件 | ボス1体（カフカ）、雑魚1体（余獣） |
| MstEnemyOutpost | 1件 | HP: 1,000（dungeonボスブロック固定） |
| MstPage | 1件 | |
| MstKomaLine | 1件 | 1行構成（ボスブロック固定） |
| MstAutoPlayerSequence | 3件 | 3行構成（ボス初期召喚 + 護衛雑魚2波） |
| MstInGame | 1件 | |

---

## MstInGame 主要パラメータ設計

- `content_type`: dungeon（※MstInGameに直接カラムなし、stage_typeで区別）
- `id`: `dungeon_kai_boss_00001`
- `bgm_asset_key`: `SSE_SBG_003_001`（kai系全コンテンツ共通）
- `boss_bgm_asset_key`: 空（ボスBGMなし）
- `loop_background_asset_key`: `kai_00001`（kai作品専用背景）
- `normal_enemy_hp_coef`: `1`（等倍）
- `normal_enemy_attack_coef`: `1`
- `normal_enemy_speed_coef`: `1`
- `boss_enemy_hp_coef`: `1`（等倍）
- `boss_enemy_attack_coef`: `1`
- `boss_enemy_speed_coef`: `1`
- `boss_mst_enemy_stage_parameter_id`: `c_kai_00001_general_Normal_Yellow`
- `boss_count`: `1`

---

## MstEnemyStageParameter 敵パラメータ設計

| 識別子 | 役割 | mst_enemy_character_id | HP | 攻撃力 | 移動速度 | character_unit_kind | color |
|--------|------|----------------------|-----|-------|---------|---------------------|-------|
| `c_kai_00001_general_Normal_Yellow` | ボス（カフカ） | `chara_kai_00001` | 150,000 | 700 | 35 | Normal | Yellow |
| `e_kai_00101_general_Normal_Colorless` | 護衛雑魚（余獣） | `enemy_kai_00101` | 25,000 | 350 | 45 | Normal | Colorless |

**注意**: `c_kai_00001_general_Normal_Yellow` はすでにMasterDataに存在する既存パラメータIDのため、新規作成不要。
`e_kai_00101_general_Normal_Colorless` も同様に既存データを確認してから判断する。

**既存データ確認**:
- `c_kai_00001_general_Normal_Yellow`: MstEnemyStageParameterに存在（HP=150,000, 攻撃=700, 速度=35）- 既存データそのまま利用
- `e_kai_00101_general_Normal_Colorless`: 未存在のため新規作成が必要

---

## MstAutoPlayerSequence ウェーブ構成設計

- 総シーケンス行数: 3行
- グループ切り替え: なし（単一デフォルトグループ）

| 行 | sequence_element_id | condition_type | condition_value | action_type | action_value | summon_count | 備考 |
|----|---------------------|---------------|----------------|------------|--------------|--------------|------|
| 1 | 1 | InitialSummon | 0 | SummonEnemy | c_kai_00001_general_Normal_Yellow | 1 | ボス召喚（砦付近配置、ダメージ無効フラグ有効） |
| 2 | 2 | ElapsedTime | 30 | SummonEnemy | e_kai_00101_general_Normal_Colorless | 1 | 護衛雑魚 第1波（3,000ms後） |
| 3 | 3 | ElapsedTime | 60 | SummonEnemy | e_kai_00101_general_Normal_Colorless | 1 | 護衛雑魚 第2波（6,000ms後） |

**行1（ボス）の詳細設定**:
- `summon_position`: `1.7`（砦付近）
- `move_start_condition_type`: `Damage`
- `move_start_condition_value`: `1`（1ダメージ受けたら移動開始）
- `is_summon_unit_outpost_damage_invalidation`: `1`（ボス撃破まで敵ゲートダメージ無効）
- `aura_type`: `Boss`（ボス演出）

---

## MstPage / MstKomaLine 構成

- ページID: `dungeon_kai_boss_00001`（MstInGame.idと同一）
- ページ数: 1
- コマ行数: **1行**（ボスブロック固定）
- コマ行ID: `dungeon_kai_boss_00001_1`
- height: `1.0`（1行のみなので全幅）
- コマレイアウト: 1コマ（koma1のみ、width=1.0）
- コマアセットキー: `kai_00001`
- コマ効果: なし（None）

---

## MstEnemyOutpost 構成

- id: `dungeon_kai_boss_00001`
- HP: `1,000`（dungeonボスブロック固定値）
- `is_damage_invalidation`: 空（ダメージ有効）
  - ※ボス撃破まで無効化は MstAutoPlayerSequence の `is_summon_unit_outpost_damage_invalidation` で設定
- `artwork_asset_key`: `kai_00001`

---

## 参照した既存データ

- `normal_kai_00003`〜`_00005`: `kai_00001` ループ背景使用確認
- `c_kai_00001_general_Normal_Yellow`: 既存パラメータ（HP=150,000, atk=700, spd=35）確認済み
- BGMキー `SSE_SBG_003_001`: kai系全コンテンツ共通で使用確認済み
- 最新リリースキー: `202604010`（仮。実際のdungeon投入リリースキーが確定次第変更）

---

## 不確定事項・要確認事項

- `release_key`: dungeonコンテンツの正式なリリースキーが未確定のため `999999999` を使用
- `e_kai_00101_general_Normal_Colorless` は新規作成（enemy_kai_00101は既存、このparameter IDは未存在）
- `outpost_asset_key` / `player_outpost_asset_key`: dungeon系の砦アセットキーは未確認。空またはデフォルト値使用
