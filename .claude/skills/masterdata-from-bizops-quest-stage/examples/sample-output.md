# クエスト・ステージ マスタデータ サンプル出力

このファイルは、クエスト・ステージスキルの出力例を示します。

## 入力例

```
クエスト・ステージの運営仕様書からマスタデータを作成してください。

添付ファイル:
- クエスト設計書_地獄楽_いいジャン祭.xlsx

パラメータ:
- release_key: 202601010
- mst_series_id: jig
- mst_event_id: event_jig_00001
- quest_ids: quest_event_jig1_charaget01,quest_event_jig1_1day
- quest_names: 必ず生きて帰る,本能が告げている 危険だと
- start_date: 2026-01-16 15:00:00
- end_date: 2026-02-16 10:59:59
```

## 出力例

### 1. MstQuest シート

| ENABLE | id | quest_type | mst_event_id | sort_order | asset_key | start_date | end_date | quest_group | difficulty | release_key |
|--------|----|-----------|--------------|-----------|---------|-----------|---------|-----------|-----------|---------||
| e | quest_event_jig1_charaget01 | event | event_jig_00001 | 1 | jig1_charaget01 | 2026-01-16 15:00:00 | 2026-02-16 10:59:59 | event_jig1_charaget_mei | Normal | 202601010 |
| e | quest_event_jig1_1day | event | event_jig_00001 | 5 | jig1_1day | 2026-01-16 15:00:00 | 2026-02-2 03:59:59 | event_jig1_1day | Normal | 202601010 |

### 2. MstQuestI18n シート

| ENABLE | release_key | id | mst_quest_id | language | name | category_name | flavor_text |
|--------|-------------|----|--------------|---------|----|--------------|------------|
| e | 202601010 | quest_event_jig1_charaget01_ja | quest_event_jig1_charaget01 | ja | 必ず生きて帰る | ストーリー | |
| e | 202601010 | quest_event_jig1_1day_ja | quest_event_jig1_1day | ja | 本能が告げている 危険だと | デイリー | |

### 3. MstQuestBonusUnit シート

| ENABLE | id | mst_quest_id | mst_unit_id | coin_bonus_rate | start_at | end_at | release_key |
|--------|----|--------------|-----------|--------------|---------|---------|---------||
| e | 58 | quest_event_jig1_charaget01 | chara_jig_00001 | 0.15 | 2026-01-16 15:00:00 | 2026-02-16 10:59:59 | 202601010 |
| e | 62 | quest_event_jig1_charaget01 | chara_jig_00401 | 0.2 | 2026-01-16 15:00:00 | 2026-02-16 10:59:59 | 202601010 |

### 4. MstStage シート

| ENABLE | id | mst_quest_id | mst_in_game_id | stage_number | recommended_level | cost_stamina | exp | coin | prev_mst_stage_id | mst_stage_tips_group_id | auto_lap_type | max_auto_lap_count | sort_order | asset_key | mst_stage_limit_status_id | release_key | mst_artwork_fragment_drop_group_id | start_at | end_at |
|--------|----|--------------|--------------|-----------|--------------|-----------|----|----|-----------------|-----------------------|--------------|------------------|-----------|---------|-----------------------|-----------|---------------------------------|---------|---------||
| e | event_jig1_charaget01_00001 | quest_event_jig1_charaget01 | event_jig1_charaget01_00001 | 1 | 10 | 5 | 50 | 100 | | 1 | AfterClear | 5 | 1 | event_jig1_00001 | | 202601010 | event_jig_a_0001 | 2026-01-16 15:00:00 | 2026-02-16 10:59:59 |
| e | event_jig1_charaget01_00002 | quest_event_jig1_charaget01 | event_jig1_charaget01_00002 | 2 | 15 | 5 | 50 | 100 | event_jig1_charaget01_00001 | 1 | AfterClear | 5 | 2 | general_diamond | | 202601010 | event_jig_a_0002 | 2026-01-16 15:00:00 | 2026-02-16 10:59:59 |
| e | event_jig1_1day_00001 | quest_event_jig1_1day | event_jig1_1day_00001 | 1 | 1 | 1 | 100 | 1500 | | 1 | __NULL__ | 1 | 1 | general_diamond | | 202601010 | __NULL__ | 2026-01-16 15:00:00 | 2026-02-2 03:59:59 |

### 5. MstStageI18n シート

| ENABLE | release_key | id | mst_stage_id | language | name | category_name |
|--------|-------------|----|--------------|---------|----|--------------|
| e | 202601010 | event_jig1_charaget01_00001_ja | event_jig1_charaget01_00001 | ja | 必ず生きて帰る | |
| e | 202601010 | event_jig1_charaget01_00002_ja | event_jig1_charaget01_00002 | ja | 必ず生きて帰る | |
| e | 202601010 | event_jig1_1day_00001_ja | event_jig1_1day_00001 | ja | 本能が告げている 危険だと | |

### 6. MstStageEventReward シート

| ENABLE | id | mst_stage_id | reward_category | resource_type | resource_id | resource_amount | percentage | sort_order | release_key |
|--------|----|--------------|--------------|--------------|-----------|--------------|-----------|-----------|---------||
| e | 569 | event_jig1_charaget01_00001 | FirstClear | Unit | chara_jig_00701 | 1 | 100 | 1 | 202601010 |
| e | 570 | event_jig1_charaget01_00001 | FirstClear | FreeDiamond | prism_glo_00001 | 40 | 100 | 2 | 202601010 |
| e | 571 | event_jig1_charaget01_00001 | FirstClear | Coin | | 500 | 100 | 3 | 202601010 |
| e | 572 | event_jig1_charaget01_00001 | Random | Item | piece_jig_00701 | 1 | 10 | 4 | 202601010 |
| e | 539 | event_jig1_1day_00001 | FirstClear | FreeDiamond | prism_glo_00001 | 20 | 100 | 1 | 202601010 |
| e | 540 | event_jig1_1day_00001 | Random | Coin | | 2000 | 50 | 2 | 202601010 |

### 7. MstStageEventSetting シート

| ENABLE | id | mst_stage_id | reset_type | clearable_count | ad_challenge_count | mst_stage_rule_group_id | start_at | end_at | release_key | background_asset_key |
|--------|----|--------------|-----------|--------------|-----------------|-----------------------|---------|---------|-----------|--------------------|
| e | 162 | event_jig1_1day_00001 | Daily | 1 | 0 | __NULL__ | 2026-01-16 15:00:00 | 2026-02-2 03:59:59 | 202601010 | jig_00001 |
| e | 169 | event_jig1_charaget01_00001 | __NULL__ | | 0 | __NULL__ | 2026-01-16 15:00:00 | 2026-02-16 10:59:59 | 202601010 | jig_00003 |
| e | 170 | event_jig1_charaget01_00002 | __NULL__ | | 0 | __NULL__ | 2026-01-16 15:00:00 | 2026-02-16 10:59:59 | 202601010 | jig_00003 |

### 8. MstStageClearTimeReward シート

(タイムアタック報酬がある場合のみ作成)

チャレンジクエストや高難度クエストの例:

| ENABLE | id | mst_stage_id | upper_clear_time_ms | resource_type | resource_id | resource_amount | release_key |
|--------|----|--------------|--------------------|--------------|-----------|---------------|---------||
| e | event_jig1_challenge01_00001_1 | event_jig1_challenge01_00001 | 140000 | FreeDiamond | | 20 | 202601010 |
| e | event_jig1_challenge01_00001_2 | event_jig1_challenge01_00001 | 200000 | FreeDiamond | | 20 | 202601010 |
| e | event_jig1_challenge01_00001_3 | event_jig1_challenge01_00001 | 300000 | FreeDiamond | | 20 | 202601010 |

## 推測値レポート

### MstStage.mst_artwork_fragment_drop_group_id
- **値**: event_jig_a_0001, event_jig_a_0002
- **理由**: 設計書に原画欠片ドロップグループIDの記載がなかったため、イベントIDから推測して設定
- **確認事項**: 正しい原画欠片ドロップグループIDを確認し、必要に応じて差し替えてください

### MstStageEventSetting.background_asset_key
- **値**: jig_00001, jig_00003
- **理由**: 設計書に背景アセットキーの記載がなかったため、シリーズIDから推測して設定
- **確認事項**: 正しい背景アセットキーを確認し、必要に応じて差し替えてください

### MstQuestBonusUnit.coin_bonus_rate
- **値**: 0.15, 0.2
- **理由**: 設計書に特効ボーナス率の記載がなかったため、標準的な値(15%、20%)を推測して設定
- **確認事項**: 正しい特効ボーナス率を確認し、必要に応じて差し替えてください
