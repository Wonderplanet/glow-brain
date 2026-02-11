#!/usr/bin/env python3
"""
CSVファイルリスト比較スクリプト
生成結果と正解データのファイルリストを比較し、3つのカテゴリに分類する
"""

import json
from pathlib import Path

# ファイルリスト（コマンド実行結果から取得）
generated_files = [
    "MstAbility.csv",
    "MstAbilityI18n.csv",
    "MstAdventBattle.csv",
    "MstAdventBattleClearReward.csv",
    "MstAdventBattleI18n.csv",
    "MstAdventBattleRank.csv",
    "MstAdventBattleReward.csv",
    "MstAdventBattleRewardGroup.csv",
    "MstAttack.csv",
    "MstAttackElement.csv",
    "MstAttackI18n.csv",
    "MstDailyBonusReward.csv",
    "MstEmblem.csv",
    "MstEmblemI18n.csv",
    "MstEnemyCharacter.csv",
    "MstEnemyOutpost.csv",
    "MstEvent.csv",
    "MstEventI18n.csv",
    "MstHomeBanner.csv",
    "MstInGame.csv",
    "MstInGameI18n.csv",
    "MstItem.csv",
    "MstItemI18n.csv",
    "MstKomaLine.csv",
    "MstMissionEvent.csv",
    "MstMissionEventI18n.csv",
    "MstMissionReward.csv",
    "MstPack.csv",
    "MstPackContent.csv",
    "MstPackI18n.csv",
    "MstPvp.csv",
    "MstPvpI18n.csv",
    "MstQuest.csv",
    "MstQuestI18n.csv",
    "MstStage.csv",
    "MstStageI18n.csv",
    "MstStoreProduct.csv",
    "MstStoreProductI18n.csv",
    "MstUnit.csv",
    "MstUnitAbility.csv",
    "MstUnitI18n.csv",
    "OprGacha.csv",
    "OprGachaDisplayUnitI18n.csv",
    "OprGachaI18n.csv",
    "OprGachaPrize.csv",
    "OprGachaUpper.csv",
    "OprGachaUseResource.csv",
]

reference_files = [
    "MstAbility.csv",
    "MstAbilityI18n.csv",
    "MstAdventBattle.csv",
    "MstAdventBattleClearReward.csv",
    "MstAdventBattleI18n.csv",
    "MstAdventBattleRank.csv",
    "MstAdventBattleReward.csv",
    "MstAdventBattleRewardGroup.csv",
    "MstArtwork.csv",
    "MstArtworkFragment.csv",
    "MstArtworkFragmentI18n.csv",
    "MstArtworkFragmentPosition.csv",
    "MstArtworkI18n.csv",
    "MstAttack.csv",
    "MstAttackElement.csv",
    "MstAttackI18n.csv",
    "MstAutoPlayerSequence.csv",
    "MstEmblem.csv",
    "MstEmblemI18n.csv",
    "MstEnemyCharacter.csv",
    "MstEnemyCharacterI18n.csv",
    "MstEnemyOutpost.csv",
    "MstEnemyStageParameter.csv",
    "MstEvent.csv",
    "MstEventBonusUnit.csv",
    "MstEventDisplayUnit.csv",
    "MstEventDisplayUnitI18n.csv",
    "MstEventI18n.csv",
    "MstHomeBanner.csv",
    "MstInGame.csv",
    "MstInGameI18n.csv",
    "MstInGameSpecialRule.csv",
    "MstInGameSpecialRuleUnitStatus.csv",
    "MstItem.csv",
    "MstItemI18n.csv",
    "MstKomaLine.csv",
    "MstMangaAnimation.csv",
    "MstMissionEvent.csv",
    "MstMissionEventDailyBonus.csv",
    "MstMissionEventDailyBonusSchedule.csv",
    "MstMissionEventDependency.csv",
    "MstMissionEventI18n.csv",
    "MstMissionLimitedTerm.csv",
    "MstMissionLimitedTermI18n.csv",
    "MstMissionReward.csv",
    "MstPack.csv",
    "MstPackContent.csv",
    "MstPackI18n.csv",
    "MstPage.csv",
    "MstPvp.csv",
    "MstPvpI18n.csv",
    "MstQuest.csv",
    "MstQuestBonusUnit.csv",
    "MstQuestEventBonusSchedule.csv",
    "MstQuestI18n.csv",
    "MstSpecialAttackI18n.csv",
    "MstSpecialRoleLevelUpAttackElement.csv",
    "MstSpeechBalloonI18n.csv",
    "MstStage.csv",
    "MstStageClearTimeReward.csv",
    "MstStageEndCondition.csv",
    "MstStageEventReward.csv",
    "MstStageEventSetting.csv",
    "MstStageI18n.csv",
    "MstStoreProduct.csv",
    "MstStoreProductI18n.csv",
    "MstUnit.csv",
    "MstUnitAbility.csv",
    "MstUnitI18n.csv",
    "MstUnitSpecificRankUp.csv",
    "OprGacha.csv",
    "OprGachaDisplayUnitI18n.csv",
    "OprGachaI18n.csv",
    "OprGachaPrize.csv",
    "OprGachaUpper.csv",
    "OprGachaUseResource.csv",
    "OprProduct.csv",
    "OprProductI18n.csv",
]

# セットに変換
generated_set = set(generated_files)
reference_set = set(reference_files)

# 3つのカテゴリに分類
common_files = sorted(generated_set & reference_set)
generated_only = sorted(generated_set - reference_set)
reference_only = sorted(reference_set - generated_set)

# 結果オブジェクト
result = {
    "summary": {
        "total_generated": len(generated_files),
        "total_reference": len(reference_files),
        "common_files": len(common_files),
        "generated_only": len(generated_only),
        "reference_only": len(reference_only),
    },
    "common_files": common_files,
    "generated_only": generated_only,
    "reference_only": reference_only,
}

# JSON出力
output_dir = Path(__file__).parent
json_path = output_dir / "file_list.json"
with open(json_path, "w", encoding="utf-8") as f:
    json.dump(result, f, ensure_ascii=False, indent=2)

print(f"JSON出力完了: {json_path}")

# Markdown出力
md_path = output_dir / "file_list.md"
with open(md_path, "w", encoding="utf-8") as f:
    f.write("# マスタデータCSVファイルリスト比較結果\n\n")
    f.write("## サマリー\n\n")
    f.write(f"- **生成結果ファイル数**: {result['summary']['total_generated']}\n")
    f.write(f"- **正解データファイル数**: {result['summary']['total_reference']}\n")
    f.write(f"- **共通ファイル数（比較対象）**: {result['summary']['common_files']}\n")
    f.write(f"- **生成結果のみのファイル数（余分）**: {result['summary']['generated_only']}\n")
    f.write(f"- **正解データのみのファイル数（生成漏れ）**: {result['summary']['reference_only']}\n\n")

    f.write("## 1. 共通ファイル（比較対象）\n\n")
    f.write(f"両方のディレクトリに存在するファイル（{len(common_files)}個）\n\n")
    for file in common_files:
        f.write(f"- {file}\n")

    f.write("\n## 2. 生成結果のみに存在するファイル（余分なファイル）\n\n")
    if generated_only:
        f.write(f"生成結果にのみ存在し、正解データに存在しないファイル（{len(generated_only)}個）\n\n")
        for file in generated_only:
            f.write(f"- {file}\n")
    else:
        f.write("なし\n")

    f.write("\n## 3. 正解データのみに存在するファイル（生成漏れ）\n\n")
    if reference_only:
        f.write(f"正解データにのみ存在し、生成結果に存在しないファイル（{len(reference_only)}個）\n\n")
        for file in reference_only:
            f.write(f"- {file}\n")
    else:
        f.write("なし\n")

print(f"Markdown出力完了: {md_path}")
print("\n=== 比較結果サマリー ===")
print(f"共通ファイル: {len(common_files)}")
print(f"生成結果のみ: {len(generated_only)}")
print(f"正解データのみ: {len(reference_only)}")
