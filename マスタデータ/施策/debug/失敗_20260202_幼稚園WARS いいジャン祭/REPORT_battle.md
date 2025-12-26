## battle 関連マスタデータ生成結果

### 生成ファイル
- MstAdventBattle.csv: 1件
- MstAdventBattleClearReward.csv: 15件

### スキーマ検証
- MstAdventBattle.csv: ✅ 問題なし（ENUMカラムに存在しないカラムを削除して修正済み）
- MstAdventBattleClearReward.csv: ✅ 問題なし

### 生成内容

#### MstAdventBattle.csv
- **降臨バトルID**: advent_battle_you_00001
- **バトルタイプ**: ScoreChallenge（スコアチャレンジ）
- **イベントID**: event_you_00001
- **インゲームID**: advent_battle_you_00001
- **アセットキー**: you_00001
- **初期バトルポイント**: 300
- **挑戦可能回数**: 3回/日
- **広告視聴挑戦回数**: 2回/日
- **開催期間**: 2026-02-02 00:00:00 〜 2026-02-16 23:59:59
- **リリースキー**: 202602020
- **スコア加算タイプ**: AllEnemiesAndOutPost
- **スコア加算係数**: 0.050

#### MstAdventBattleClearReward.csv
降臨バトルクリア報酬を3種類のカテゴリで設定:

1. **FirstClear（初回クリア報酬）**: 5種類のメモリーアイテム x 10個（各20%）
2. **Always（毎回必ずもらえる報酬）**: 5種類のメモリーアイテム x 5個（各15%）
3. **Random（ランダム報酬）**: 5種類のメモリーアイテム x 3個（各10%）

### 備考
- テンプレートファイルに含まれていた`time_limit_seconds`と`score_addition_target_mst_enemy_stage_parameter_id`カラムは、現在のスキーマに存在しないため削除しました
- I18n対応は不要（MstAdventBattleI18nテーブルは存在しません）
- 報酬アイテムIDは既存パターン（memory_glo_00001〜00005）を使用しています
