# インゲームマスタデータ設計書

## 基本情報
- 生成日時: 2026-03-02
- コンテンツ種別: dungeon（限界チャレンジ）
- ステージ種別: dungeon_normal（通常ブロック）
- シリーズ: 幼稚園WARS（you）

## インゲームID
- インゲームID: `dungeon_you_normal_00001`
- 命名根拠: `dungeon_{シリーズ}_normal_{連番5桁}` の命名規則に従い、youシリーズ・normalブロック第1弾

## 生成対象テーブル一覧

| テーブル | 生成件数 | 備考 |
|---------|---------|------|
| MstEnemyStageParameter | 1件 | 雑魚1体（GLO汎用敵 enemy_glo_00001） |
| MstEnemyOutpost | 1件 | HP: 100（dungeon_normal固定値） |
| MstPage | 1件 | |
| MstKomaLine | 3件 | 3行構成（dungeon_normal固定） |
| MstAutoPlayerSequence | 6件 | ElapsedTime時間差配置 + FriendUnitDead追加 |
| MstInGame | 1件 | |

## MstInGame 主要パラメータ設計

- `bgm_asset_key`: `SSE_SBG_003_001`（通常バトルBGM）
- `loop_background_asset_key`: `you_00001`（幼稚園WARSの背景）
- `normal_enemy_hp_coef`: `1`（等倍）
- `normal_enemy_attack_coef`: `1`（等倍）
- `normal_enemy_speed_coef`: `1`（等倍）
- `boss_count`: `0`（normalブロックはボスなし）
- `mst_page_id`: `dungeon_you_normal_00001`
- `mst_enemy_outpost_id`: `dungeon_you_normal_00001`
- `mst_auto_player_sequence_set_id`: `dungeon_you_normal_00001`

参照: spy normalブロック（dungeon_spy_normal_00001）を参考に設計

## MstEnemyStageParameter 敵パラメータ設計

| 識別子 | 役割 | mst_enemy_character_id | HP | 攻撃力 | スピード | 索敵距離 | ノックバック |
|--------|------|----------------------|-----|-------|--------|---------|-----------|
| `e_glo_00001_you_dungeon_Normal_Colorless` | 雑魚（GLO汎用・刺客イメージ） | `enemy_glo_00001` | 1,000 | 100 | 30 | 0.3 | 0 |

- 属性: Colorless（無属性）
- 必殺ワザ: なし（mst_unit_ability_id1 = 空）
- drop_battle_point: 100
- character_unit_kind: Normal
- role_type: Attack
- move_speed: 30（中速 - 要件テキスト「move_speed=30前後」に準拠）
- attack_combo_cycle: 1（シンプルな攻撃）

## MstAutoPlayerSequence ウェーブ構成設計

シングルグループ構成（グループ切り替えなし）。ElapsedTimeトリガーで時間差配置。
幼稚園WARSの「普通に見えて実は手強い刺客たちが連続出現する」テンポを演出。

| 行番号 | condition_type | condition_value | action_type | action_value | summon_count | summon_interval | 説明 |
|--------|---------------|----------------|-------------|-------------|-------------|----------------|------|
| 1 | ElapsedTime | 500 | SummonEnemy | e_glo_00001_you_dungeon_Normal_Colorless | 1 | | 先行1体（500ms後） |
| 2 | ElapsedTime | 1200 | SummonEnemy | e_glo_00001_you_dungeon_Normal_Colorless | 1 | | 追加刺客（1,200ms後） |
| 3 | ElapsedTime | 2000 | SummonEnemy | e_glo_00001_you_dungeon_Normal_Colorless | 1 | | 追加刺客（2,000ms後） |
| 4 | ElapsedTime | 2800 | SummonEnemy | e_glo_00001_you_dungeon_Normal_Colorless | 1 | | 追加刺客（2,800ms後） |
| 5 | FriendUnitDead | 2 | SummonEnemy | e_glo_00001_you_dungeon_Normal_Colorless | 2 | | 2体撃破後に追加出現 |
| 6 | FriendUnitDead | 4 | SummonEnemy | e_glo_00001_you_dungeon_Normal_Colorless | 1 | | 4体撃破後にトドメの1体 |

- 合計6行
- グループ切り替え: なし（シングルグループ）
- aura_type: Default（雑魚敵）
- death_type: Normal

## MstPage / MstKomaLine 構成

- ページ数: 1
- コマ行数: 3行（dungeon_normal固定）
- コマ効果: None（要件テキスト通り）
- 背景アセット: `you_00001`

| row | height | layout | koma1_width | koma2_width | コマ効果 |
|-----|--------|--------|------------|------------|---------|
| 1 | 0.55 | 6（2コマ等分） | 0.5 | 0.5 | None/None |
| 2 | 0.55 | 3（2コマ非対称） | 0.4 | 0.6 | None/None |
| 3 | 0.55 | 1（1コマ全幅） | 1.0 | — | None |

SPY normalブロックのKomaLineレイアウトを参考にyouシリーズの背景（you_00001）を適用。

## MstEnemyOutpost 設計

- id: `dungeon_you_normal_00001`
- hp: `100`（dungeon_normal固定値）
- is_damage_invalidation: 空（ダメージ有効）
- outpost_asset_key: 空
- artwork_asset_key: 空

## 参照した既存データ

- 参照ステージID: `dungeon_spy_normal_00001`（SPY×FAMILYのnormalブロック参考フォルダより）
  - 同様の構造（HP100・3行コマ・ElapsedTime+FriendUnitDeadシーケンス）を踏襲
  - youシリーズ固有の背景・BGMに変更

## 不確定事項・要確認事項

- `you_00001` の背景アセットキーが実際に存在するか要確認（要件テキストに明記あり）
- `enemy_glo_00001` がMstEnemyCharacterに存在するか要確認（GLO汎用敵）
- release_key: `999999999`（開発テスト用、後で正式キーに変更）
