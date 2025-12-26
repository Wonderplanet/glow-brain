# 降臨バトル「誰の依頼だ？」マスタデータ生成レポート

## 要件概要

幼稚園WARS いいジャン祭の期間限定降臨バトル「誰の依頼だ？」のマスタデータを生成しました。

### 基本情報
- **名称**: 誰の依頼だ？
- **開催期間**: 2026年2月9日(月) 15:00 ～ 2月15日(日) 14:59
- **降臨バトルタイプ**: ScoreChallenge（スコアチャレンジ）
- **推奨レベル**: 20-30
- **挑戦回数**: 1日最大5回（内2回は広告視聴）
- **時間制限**: 90秒
- **獲得リーダーEXP**: 100
- **ステージ段数**: 4

### ボーナスキャラ
- 元殺し屋の新人教諭 リタ (chara_you_00001): 30%
- ルーク (chara_you_00101): 30%
- ダグ (chara_you_00201): 30%
- ハナ (chara_you_00301): 30%

## 生成データ一覧

### MstAdventBattle.csv
- **レコード数**: 1件
- **主要カラム**: id, mst_event_id, asset_key, advent_battle_type, challengeable_count, ad_challengeable_count, exp, start_at, end_at
- **データ概要**: 降臨バトルの基本設定（クエストID: quest_you_00001）

### MstAdventBattleI18n.csv
- **レコード数**: 2件（ja, en）
- **主要カラム**: id, mst_advent_battle_id, language, name, boss_description
- **データ概要**: 降臨バトルの多言語対応テキスト

### MstAdventBattleRank.csv
- **レコード数**: 16件
- **主要カラム**: id, mst_advent_battle_id, rank_type, rank_level, required_lower_score
- **データ概要**: ランク到達報酬の設定
  - Bronze: 4段階（1,000pt ～ 4,000pt）
  - Silver: 4段階（5,000pt ～ 12,500pt）
  - Gold: 4段階（15,000pt ～ 75,000pt）
  - Master: 4段階（100,000pt ～ 1,000,000pt）

### MstAdventBattleClearReward.csv
- **レコード数**: 6件
- **主要カラム**: id, mst_advent_battle_id, reward_category, resource_type, resource_id, resource_amount, percentage
- **データ概要**: クリア報酬とランダムドロップ報酬
  - Always報酬: コイン300枚
  - Random報酬: カラーメモリー各色3個（ドロップ率20%）

### MstAdventBattleRewardGroup.csv
- **レコード数**: 36件
- **主要カラム**: id, mst_advent_battle_id, reward_category, condition_value
- **データ概要**: 報酬グループの定義
  - Ranking報酬グループ: 9グループ（1位、2位、3位、4-50位、...）
  - MaxScore報酬グループ: 11グループ（5,000pt ～ 200,000pt）
  - Rank報酬グループ: 16グループ（Bronze-1 ～ Master-4）

### MstAdventBattleReward.csv
- **レコード数**: 101件
- **主要カラム**: id, mst_advent_battle_reward_group_id, resource_type, resource_id, resource_amount
- **データ概要**: 各報酬グループの具体的な報酬内容
  - ランキング報酬: プリズム、コイン、エンブレム、スペシャルガシャチケット
  - ハイスコア目標達成報酬: プリズム、コイン、メモリーフラグメント・上級、スペシャルガシャチケット
  - ランク到達報酬: プリズム、コイン、メモリーフラグメント（初級・中級・上級）

## データ設計の詳細

### ID範囲
- MstAdventBattle: quest_you_00001
- MstAdventBattleI18n: quest_you_00001_i18n_ja, quest_you_00001_i18n_en
- MstAdventBattleRank: quest_you_00001_rank_bronze_1 ～ quest_you_00001_rank_master_4
- MstAdventBattleClearReward: quest_you_00001_clear_reward_1 ～ quest_you_00001_random_reward_5
- MstAdventBattleRewardGroup: quest_you_00001_ranking_1 ～ quest_you_00001_rank_master_4_reward
- MstAdventBattleReward: quest_you_00001_ranking_1_reward_1 ～ quest_you_00001_rank_master_4_reward_5

### 命名規則
- **IDパターン**: `quest_you_00001_<type>_<detail>`
  - 降臨バトル本体: `quest_you_00001`
  - ランク: `quest_you_00001_rank_<rank_type>_<level>`
  - 報酬グループ: `quest_you_00001_<category>_<condition>`
  - 報酬: `quest_you_00001_<category>_<condition>_reward_<n>`
- **asset_keyパターン**: `you_00001`（幼稚園WARSの略称"you"＋連番）

### 参照した既存データ
- MstAdventBattle.csv: 既存の降臨バトル設定パターン
- MstAdventBattleRank.csv: ランク設定の段階的な構造
- MstAdventBattleReward.csv: 報酬種別とリソースタイプ

## スキーマ検証と修正

### MstAdventBattle.csv
- ⚠️ 修正内容:
  - 削除したカラム: `time_limit_seconds`, `score_addition_target_mst_enemy_stage_parameter_id`, `name.ja`, `boss_description.ja`（スキーマJSONに存在しないか、I18nテーブルに移動すべきカラムのため）
  - テンプレートファイルとスキーマJSONの不一致により修正が発生

### MstAdventBattleI18n.csv
- ✅ スキーマチェック完了: 問題なし

### MstAdventBattleRank.csv
- ✅ スキーマチェック完了: 問題なし

### MstAdventBattleClearReward.csv
- ✅ スキーマチェック完了: 問題なし

### MstAdventBattleRewardGroup.csv
- ✅ スキーマチェック完了: 問題なし

### MstAdventBattleReward.csv
- ✅ スキーマチェック完了: 問題なし

## データ整合性チェック

- [x] **スキーマJSONとの整合性を確認**
- [x] **CSVテンプレートファイルのヘッダーに従っている**（一部スキーマと不一致のため調整）
- [x] IDの重複がないことを確認
- [x] 必須カラムがすべて埋まっている
- [x] 日時形式が正しい（YYYY-MM-DD HH:MM:SS）
- [x] 外部キー制約を満たしている
  - mst_advent_battle_id → MstAdventBattle.id
  - mst_advent_battle_reward_group_id → MstAdventBattleRewardGroup.id
- [x] 命名規則に準拠している
- [x] ENUM型の値が許可された値のみであることを確認
  - advent_battle_type: ScoreChallenge ✅
  - rank_type: Bronze, Silver, Gold, Master ✅
  - reward_category (ClearReward): Always, Random ✅
  - reward_category (RewardGroup): Ranking, MaxScore, Rank ✅
  - resource_type: Coin, FreeDiamond, Item, Emblem ✅
- [x] データ型が正しいことを確認
- [x] **要件に含まれる全てのマスタデータが生成されている**

## 報酬合計数

### クリア報酬（毎回）
- コイン: 300枚/回

### ランダムクリア報酬（20%ドロップ）
- カラーメモリー・レッド: 3個
- カラーメモリー・グリーン: 3個
- カラーメモリー・イエロー: 3個
- カラーメモリー・ブルー: 3個
- カラーメモリー・グレー: 3個

### ランク到達報酬（累計）
- プリズム: 400個
- コイン: 40,000枚
- メモリーフラグメント・初級: 30個
- メモリーフラグメント・中級: 15個
- メモリーフラグメント・上級: 1個

### ハイスコア目標達成報酬（累計）
- プリズム: 250個
- コイン: 15,000枚
- メモリーフラグメント・上級: 1個
- スペシャルガシャチケット: 1枚

### ランキング報酬（1位想定）
- エンブレム: 1個（幼稚園WARS専用）
- プリズム: 1,000個
- コイン: 100,000枚
- スペシャルガシャチケット: 5枚

## 備考

### テンプレートファイルとスキーマJSONの不一致
- CSVテンプレートファイル（`projects/glow-masterdata/sheet_schema/MstAdventBattle.csv`）には`time_limit_seconds`と`score_addition_target_mst_enemy_stage_parameter_id`というカラムが含まれていますが、スキーマJSON（`master_tables_schema.json`）には存在しません。
- これらのカラムは削除し、スキーマJSONに準拠したCSVを生成しました。
- `name`と`boss_description`はI18nテーブル（MstAdventBattleI18n）に移動しました。

### 時間制限について
- 要件では「時間制限: 90秒」とありましたが、スキーマJSONに`time_limit_seconds`カラムが存在しないため、この情報は現時点ではマスタデータに含まれていません。
- インゲーム側の設定（mst_in_game_id）で時間制限が管理されている可能性があります。

### エンブレムIDについて
- ランキング報酬のエンブレムIDは`emblem_you_00001`を仮設定しています。
- 実際のエンブレムマスタデータとの整合性を確認してください。

### アイテムIDについて
- 報酬のアイテムIDは既存パターンに基づいて設定しています:
  - `item_gacha_ticket_special`: スペシャルガシャチケット
  - `item_memory_fragment_low`: メモリーフラグメント・初級
  - `item_memory_fragment_mid`: メモリーフラグメント・中級
  - `item_memory_fragment_high`: メモリーフラグメント・上級
  - `item_color_memory_red/green/yellow/blue/gray`: カラーメモリー各色

### イベントIDについて
- `mst_event_id`は`event_you_00001`を設定していますが、別途MstEventマスタデータの作成が必要です。
- `event_bonus_group_id`も同様に`you_00001`を設定していますが、MstEventBonusUnitsマスタデータの作成が必要です。

---

生成日時: 2025-12-26 12:24:00
生成者: Claude Code (masterdata-generator skill)
