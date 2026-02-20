# クエスト・ステージマスタデータ 推測値レポート

## 生成日時
2026-02-11 20:13:28

## 概要
リリースキー202601010のクエスト・ステージマスタデータを、過去データベースの実用的アプローチで生成しました。

## 推測値・仮値一覧

以下のフィールドは推測値または仮値を使用しています:

- MstQuest: quest_event_jig1_1day - asset_key=jig_event_1day (推測値), difficulty=Normal (仮値)
- MstQuest: quest_event_jig1_charaget01 - asset_key=jig_event_charaget01 (推測値), difficulty=Normal (仮値)
- MstQuest: quest_event_jig1_challenge01 - asset_key=jig_event_challenge01 (推測値), difficulty=Normal (仮値)
- MstQuest: quest_event_jig1_savage - asset_key=jig_event_savage (推測値), difficulty=Normal (仮値)
- MstQuestI18n: quest_event_jig1_1day - flavor_text='地獄楽イベントクエスト' (仮値)
- MstQuestI18n: quest_event_jig1_charaget01 - flavor_text='地獄楽イベントクエスト' (仮値)
- MstQuestI18n: quest_event_jig1_challenge01 - flavor_text='地獄楽イベントクエスト' (仮値)
- MstQuestI18n: quest_event_jig1_savage - flavor_text='地獄楽イベントクエスト' (仮値)
- MstStage: event_jig1_1day_00001 - recommended_level=10, cost_stamina=5, exp=50, coin=100 (全て仮値)
- MstStage: event_jig1_charaget01_00001 - recommended_level=10, cost_stamina=5, exp=50, coin=100 (全て仮値)
- MstStage: event_jig1_charaget01_00002 - recommended_level=10, cost_stamina=5, exp=50, coin=100 (全て仮値)
- MstStage: event_jig1_charaget01_00003 - recommended_level=10, cost_stamina=5, exp=50, coin=100 (全て仮値)
- MstStage: event_jig1_charaget01_00004 - recommended_level=10, cost_stamina=5, exp=50, coin=100 (全て仮値)
- MstStage: event_jig1_charaget01_00005 - recommended_level=10, cost_stamina=5, exp=50, coin=100 (全て仮値)
- MstStage: event_jig1_charaget01_00006 - recommended_level=10, cost_stamina=5, exp=50, coin=100 (全て仮値)
- MstStage: event_jig1_challenge01_00001 - recommended_level=10, cost_stamina=5, exp=50, coin=100 (全て仮値)
- MstStage: event_jig1_challenge01_00002 - recommended_level=10, cost_stamina=5, exp=50, coin=100 (全て仮値)
- MstStage: event_jig1_challenge01_00003 - recommended_level=10, cost_stamina=5, exp=50, coin=100 (全て仮値)
- MstStage: event_jig1_challenge01_00004 - recommended_level=10, cost_stamina=5, exp=50, coin=100 (全て仮値)
- MstStage: event_jig1_savage_00001 - recommended_level=10, cost_stamina=5, exp=50, coin=100 (全て仮値)
- MstStage: event_jig1_savage_00002 - recommended_level=10, cost_stamina=5, exp=50, coin=100 (全て仮値)
- MstStage: event_jig1_savage_00003 - recommended_level=10, cost_stamina=5, exp=50, coin=100 (全て仮値)


## 対応が必要な項目

以下の項目は手動での確認・修正が推奨されます:

1. **MstStage**
   - recommended_level: 推奨レベル（現在は全て10）
   - cost_stamina: スタミナコスト（現在は全て5）
   - exp, coin: 報酬値（仮値）

2. **MstStageReward**
   - 各ステージの詳細報酬データ（過去データをそのまま使用）

3. **MstAutoPlayerSequence**
   - エネミー出現シーケンス（過去データをそのまま使用）

## 生成方針

- 過去データ(past_tables)をベースとして全テーブルをコピー
- 新規クエスト4件、ステージ14件を追加
- 複雑なフィールドは過去データのパターンを踏襲
- 不明な値は仮値を設定し、このレポートに記録

## 次のステップ

1. 生成されたCSVファイルを確認
2. 推測値・仮値を運営仕様書と照合
3. 必要に応じて手動で修正
4. enemy-generator, reward-generatorで詳細データを補完
