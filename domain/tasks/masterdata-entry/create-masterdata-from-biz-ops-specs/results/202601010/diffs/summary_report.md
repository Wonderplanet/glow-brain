# マスタデータ生成結果 精度評価レポート（完全版）

リリースキー: **202601010**

## エグゼクティブサマリー

masterdata-from-bizops-allスキルを使用して生成したマスタデータの精度評価を実施しました。

### 🚨 重要な発見

**正解データには78個のCSVファイルが存在しますが、生成結果は25個のみでした。**

- **ファイル生成率**: 32.1%
- **生成されていないファイル数**: 53個

### 主要な結果（生成された25ファイルの分析）

- **完全一致ファイル**: 8/25 (32.0%)
- **差分ありファイル**: 17/25 (68.0%)
- **総差分行数**: 569 (追加: 282, 削除: 247, 変更: 40)

## ファイルレベルの差分

### 統計

| 項目 | 値 |
|------|------|
| 正解データのファイル数 | 78 |
| 生成結果のファイル数 | 25 |
| 生成されていないファイル数 | 53 |
| ファイル生成率 | 32.1% |

### 生成されていないファイル（53個）

**Unit関連 (10個):**

- MstEventBonusUnit.csv
- MstEventDisplayUnit.csv
- MstEventDisplayUnitI18n.csv
- MstInGameSpecialRuleUnitStatus.csv
- MstQuestBonusUnit.csv
- MstUnit.csv
- MstUnitAbility.csv
- MstUnitI18n.csv
- MstUnitSpecificRankUp.csv
- OprGachaDisplayUnitI18n.csv

**Enemy関連 (4個):**

- MstEnemyCharacter.csv
- MstEnemyCharacterI18n.csv
- MstEnemyOutpost.csv
- MstEnemyStageParameter.csv

**Ability関連 (2個):**

- MstAbility.csv
- MstAbilityI18n.csv

**Attack関連 (5個):**

- MstAttack.csv
- MstAttackElement.csv
- MstAttackI18n.csv
- MstSpecialAttackI18n.csv
- MstSpecialRoleLevelUpAttackElement.csv

**Gacha関連 (5個):**

- OprGacha.csv
- OprGachaI18n.csv
- OprGachaPrize.csv
- OprGachaUpper.csv
- OprGachaUseResource.csv

**Mission関連 (8個):**

- MstMissionEvent.csv
- MstMissionEventDailyBonus.csv
- MstMissionEventDailyBonusSchedule.csv
- MstMissionEventDependency.csv
- MstMissionEventI18n.csv
- MstMissionLimitedTerm.csv
- MstMissionLimitedTermI18n.csv
- MstMissionReward.csv

**InGame関連 (3個):**

- MstInGame.csv
- MstInGameI18n.csv
- MstInGameSpecialRule.csv

**Artwork関連 (5個):**

- MstArtwork.csv
- MstArtworkFragment.csv
- MstArtworkFragmentI18n.csv
- MstArtworkFragmentPosition.csv
- MstArtworkI18n.csv

**Other関連 (11個):**

- MstAutoPlayerSequence.csv
- MstKomaLine.csv
- MstMangaAnimation.csv
- MstPage.csv
- MstPvp.csv
- MstPvpI18n.csv
- MstQuestEventBonusSchedule.csv
- MstSpeechBalloonI18n.csv
- MstStageEndCondition.csv
- OprProduct.csv
- OprProductI18n.csv

## 行レベルの差分（生成された25ファイルの詳細）

| ファイル名 | 差分 | 追加 | 削除 | 変更 |
|-----------|------|------|------|------|
| MstAdventBattle.csv | ✗ | 0 | 0 | 1 |
| MstAdventBattleClearReward.csv | ✗ | 0 | 1 | 0 |
| MstAdventBattleI18n.csv | ✓ | 0 | 0 | 0 |
| MstAdventBattleRank.csv | ✓ | 0 | 0 | 0 |
| MstAdventBattleReward.csv | ✗ | 120 | 102 | 0 |
| MstAdventBattleRewardGroup.csv | ✗ | 43 | 25 | 3 |
| MstEmblem.csv | ✓ | 0 | 0 | 0 |
| MstEmblemI18n.csv | ✓ | 0 | 0 | 0 |
| MstEvent.csv | ✓ | 0 | 0 | 0 |
| MstEventI18n.csv | ✗ | 0 | 0 | 1 |
| MstHomeBanner.csv | ✗ | 3 | 1 | 0 |
| MstItem.csv | ✓ | 0 | 0 | 0 |
| MstItemI18n.csv | ✓ | 0 | 0 | 0 |
| MstPack.csv | ✗ | 1 | 0 | 1 |
| MstPackContent.csv | ✗ | 4 | 0 | 0 |
| MstPackI18n.csv | ✗ | 1 | 0 | 0 |
| MstQuest.csv | ✗ | 0 | 0 | 2 |
| MstQuestI18n.csv | ✗ | 0 | 0 | 1 |
| MstStage.csv | ✗ | 8 | 12 | 11 |
| MstStageClearTimeReward.csv | ✓ | 0 | 0 | 0 |
| MstStageEventReward.csv | ✗ | 70 | 78 | 0 |
| MstStageEventSetting.csv | ✗ | 20 | 24 | 0 |
| MstStageI18n.csv | ✗ | 0 | 4 | 20 |
| MstStoreProduct.csv | ✗ | 6 | 0 | 0 |
| MstStoreProductI18n.csv | ✗ | 6 | 0 | 0 |

## 改善の方向性

### 最優先課題: ファイル生成の完全性

**現状**: 78ファイル中25ファイルのみ生成（32.1%）

**考えられる原因:**
1. 運営仕様書にこれらのテーブルに関する記述がなかった
2. masterdata-from-bizops-allスキルがこれらのテーブルに対応していない
3. これらのテーブルは今回のリリースキー202601010では更新対象外（過去データをそのまま使用）

**対応策:**
1. 運営仕様書の網羅性確認
2. スキルの対応範囲確認
3. 過去データ継承の仕組み確認

### 個別ファイルの詳細レポート

各ファイルの詳細な差分は、以下のレポートを参照してください:

- [MstAdventBattle.csv](./MstAdventBattle_diff.md)
- [MstAdventBattleClearReward.csv](./MstAdventBattleClearReward_diff.md)
- [MstAdventBattleReward.csv](./MstAdventBattleReward_diff.md)
- [MstAdventBattleRewardGroup.csv](./MstAdventBattleRewardGroup_diff.md)
- [MstEventI18n.csv](./MstEventI18n_diff.md)
- [MstHomeBanner.csv](./MstHomeBanner_diff.md)
- [MstPack.csv](./MstPack_diff.md)
- [MstPackContent.csv](./MstPackContent_diff.md)
- [MstPackI18n.csv](./MstPackI18n_diff.md)
- [MstQuest.csv](./MstQuest_diff.md)
- [MstQuestI18n.csv](./MstQuestI18n_diff.md)
- [MstStage.csv](./MstStage_diff.md)
- [MstStageEventReward.csv](./MstStageEventReward_diff.md)
- [MstStageEventSetting.csv](./MstStageEventSetting_diff.md)
- [MstStageI18n.csv](./MstStageI18n_diff.md)
- [MstStoreProduct.csv](./MstStoreProduct_diff.md)
- [MstStoreProductI18n.csv](./MstStoreProductI18n_diff.md)
