# criterion_typeとdestination_sceneの関係性解析

本ドキュメントは、GLOWマスタデータにおけるMstMission系テーブルの`criterion_type`と`destination_scene`の関係性を解析した結果をまとめたものです。

## 解析対象テーブル

以下の7つのMstMission系テーブルを解析しました:

| テーブル名 | ユニークなcriterion_type | ユニークなdestination_scene | 総レコード数 |
|-----------|--------------------------|----------------------------|-------------|
| MstMissionAchievement | 11 | 8 | 103 |
| MstMissionBeginner | 9 | 7 | 36 |
| MstMissionDaily | 7 | 5 | 11 |
| MstMissionEvent | 10 | 3 | 360 |
| MstMissionEventDaily | 0 | 0 | 0 |
| MstMissionLimitedTerm | 1 | 1 | 44 |
| MstMissionWeekly | 5 | 4 | 11 |

**注記**: MstMissionEventDailyは現在データが設定されていません。

## 主要な発見

### 1. criterion_typeとdestination_sceneの関係は**ほぼ1:1**

24種類のcriterion_typeのうち、**20種類（約83%）は1つのdestination_sceneに固定**されています。

### 2. 例外的なケース（1:多の関係）

以下の3つのcriterion_typeは、複数のdestination_sceneを持ちます:

| criterion_type | destination_scenes | 総レコード数 |
|---------------|-------------------|-------------|
| DefeatBossEnemyCount | Event, StageSelect | 25 |
| DefeatEnemyCount | Event, StageSelect | 104 |
| SpecificQuestClear | Event, QuestSelect | 94 |

これらは、イベントミッションと通常ミッションの両方で使用されるため、遷移先が異なるパターンです。

### 3. destination_sceneがNULLのケース

`MissionBonusPoint`は、destination_sceneがNULL（遷移先がない）で設定されています。これは、ボーナスポイント累積ミッションが特定の画面遷移を必要としないためと考えられます。

## criterion_typeとdestination_sceneの完全なマッピング

以下は、全てのcriterion_typeとdestination_sceneの組み合わせパターンです:

| criterion_type | destination_scene | レコード数 | 使用テーブル |
|---------------|------------------|-----------|-------------|
| AccessWeb | Web | 1 | MstMissionAchievement |
| AccountCompleted | LinkBnId | 1 | MstMissionAchievement |
| AdventBattleChallengeCount | AdventBattle | 44 | MstMissionLimitedTerm |
| ArtworkCompletedCount | QuestSelect | 5 | MstMissionBeginner |
| CoinCollect | StageSelect | 7 | MstMissionDaily, MstMissionBeginner, MstMissionWeekly, MstMissionAchievement |
| DefeatBossEnemyCount | **Event** | 22 | MstMissionEvent |
| DefeatBossEnemyCount | **StageSelect** | 3 | MstMissionAchievement |
| DefeatEnemyCount | **Event** | 97 | MstMissionEvent |
| DefeatEnemyCount | **StageSelect** | 7 | MstMissionAchievement |
| FollowCompleted | Web | 1 | MstMissionAchievement |
| IdleIncentiveCount | IdleIncentive | 7 | MstMissionBeginner, MstMissionWeekly, MstMissionDaily, MstMissionAchievement |
| IdleIncentiveQuickCount | IdleIncentive | 1 | MstMissionDaily |
| LoginCount | Home | 32 | MstMissionWeekly, MstMissionDaily, MstMissionBeginner, MstMissionAchievement |
| MissionBonusPoint | **NULL** | 18 | MstMissionDaily, MstMissionBeginner, MstMissionWeekly |
| OutpostEnhanceCount | OutpostEnhance | 7 | MstMissionAchievement, MstMissionBeginner |
| PvpChallengeCount | Pvp | 2 | MstMissionDaily, MstMissionWeekly |
| SpecificGachaDrawCount | Gacha | 6 | MstMissionDaily, MstMissionEvent |
| SpecificQuestClear | **Event** | 39 | MstMissionEvent |
| SpecificQuestClear | **QuestSelect** | 55 | MstMissionAchievement, MstMissionBeginner |
| SpecificStageClearCount | Event | 3 | MstMissionEvent |
| SpecificUnitGradeUpCount | UnitList | 64 | MstMissionEvent |
| SpecificUnitLevel | UnitList | 80 | MstMissionEvent |
| SpecificUnitStageChallengeCount | Event | 9 | MstMissionEvent |
| SpecificUnitStageClearCount | Event | 14 | MstMissionEvent |
| StageClearCount | Event | 27 | MstMissionEvent |
| UnitAcquiredCount | Gacha | 1 | MstMissionBeginner |
| UnitLevelUpCount | UnitList | 12 | MstMissionBeginner, MstMissionAchievement |

**太字**は、複数のdestination_sceneを持つcriterion_typeです。

## destination_sceneの使用頻度

全てのdestination_sceneの使用頻度は以下の通りです:

| destination_scene | 使用回数 | 主な用途 |
|------------------|---------|---------|
| Event | 211 | イベント関連ミッションの遷移先 |
| UnitList | 156 | ユニット育成系ミッションの遷移先 |
| QuestSelect | 60 | クエスト系ミッションの遷移先 |
| AdventBattle | 44 | 降臨バトル系ミッションの遷移先 |
| Home | 32 | ログイン系ミッションの遷移先 |
| NULL | 18 | ボーナスポイント系（遷移なし） |
| StageSelect | 17 | ステージ選択系ミッションの遷移先 |
| IdleIncentive | 8 | 放置報酬系ミッションの遷移先 |
| Gacha | 7 | ガチャ系ミッションの遷移先 |
| OutpostEnhance | 7 | 拠点強化系ミッションの遷移先 |
| Pvp | 2 | PVP系ミッションの遷移先 |
| Web | 2 | Web遷移系ミッションの遷移先 |
| LinkBnId | 1 | アカウント連携系ミッションの遷移先 |

## 設定時の推奨ルール

1. **基本は1:1の関係**: 新しいcriterion_typeを設定する際は、1つのdestination_sceneに固定することを推奨
2. **例外的なケースは明確な理由が必要**: イベントミッションと通常ミッションで同じcriterion_typeを使う場合のみ、複数のdestination_sceneを許容
3. **MissionBonusPointはNULLで固定**: ボーナスポイント累積系のミッションは、遷移先を設定しない（NULL）
4. **イベント系のミッションはEventを推奨**: MstMissionEventで使用するcriterion_typeは、destination_sceneを"Event"に設定することを推奨

## 解析に使用したSQLクエリ

```sql
-- criterion_typeとdestination_sceneの組み合わせパターン
WITH combined AS (
  SELECT criterion_type, destination_scene FROM 'MstMissionAchievement.csv' WHERE ENABLE = 'e'
  UNION ALL
  SELECT criterion_type, destination_scene FROM 'MstMissionBeginner.csv' WHERE ENABLE = 'e'
  UNION ALL
  SELECT criterion_type, destination_scene FROM 'MstMissionDaily.csv' WHERE ENABLE = 'e'
  UNION ALL
  SELECT criterion_type, destination_scene FROM 'MstMissionEvent.csv' WHERE ENABLE = 'e'
  UNION ALL
  SELECT criterion_type, destination_scene FROM 'MstMissionEventDaily.csv' WHERE ENABLE = 'e'
  UNION ALL
  SELECT criterion_type, destination_scene FROM 'MstMissionLimitedTerm.csv' WHERE ENABLE = 'e'
  UNION ALL
  SELECT criterion_type, destination_scene FROM 'MstMissionWeekly.csv' WHERE ENABLE = 'e'
)
SELECT
  criterion_type,
  COUNT(DISTINCT destination_scene) as scene_count,
  STRING_AGG(DISTINCT destination_scene, ', ' ORDER BY destination_scene) as destination_scenes,
  COUNT(*) as total_records
FROM combined
GROUP BY criterion_type
ORDER BY scene_count DESC, criterion_type;
```

---

**解析日**: 2026-01-19
**データソース**: projects/glow-masterdata (本番リリース済みマスタデータ)
