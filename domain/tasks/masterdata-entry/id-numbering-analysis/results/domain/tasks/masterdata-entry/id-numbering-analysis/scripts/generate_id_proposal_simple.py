#!/usr/bin/env python3
"""
通算連番テーブルの提案サマリーを作成する簡易スクリプト
"""

import csv
from pathlib import Path

# 通算連番テーブルのリスト
numbering_tables = {
    'MstComebackBonus', 'MstDailyBonusReward', 'MstDummyOutpost', 'MstDummyUserI18n', 'MstDummyUserUnit',
    'MstEventBonusUnit', 'MstExchangeI18n', 'MstFragmentBox', 'MstIdleIncentiveItem', 'MstIdleIncentiveReward',
    'MstInGameSpecialRuleUnitStatus', 'MstItemRarityTrade', 'MstItemTransition', 'MstMissionAchievement',
    'MstMissionAchievementDependency', 'MstMissionDailyBonus', 'MstMissionEventDependency', 'MstMissionLimitedTerm',
    'MstMissionReward', 'MstNgWord', 'MstOutpostEnhancement', 'MstPackContent', 'MstPvpDummy',
    'MstQuestBonusUnit', 'MstQuestEventBonusSchedule', 'MstResultTipI18n', 'MstShopPass', 'MstShopPassEffect',
    'MstSpecialAttackI18n', 'MstSpeechBalloonI18n', 'MstStageEnhanceRewardParam', 'MstStageEventReward',
    'MstStageEventSetting', 'MstStageReward', 'MstStoreProduct', 'MstTutorialTipI18n', 'MstUnitEncyclopediaEffect',
    'MstUnitEncyclopediaReward', 'MstUnitFragmentConvert', 'MstUnitGradeCoefficient', 'MstUnitGradeUp',
    'MstUnitLevelUp', 'MstUnitRankCoefficient', 'MstUnitRankUp', 'MstUnitRoleBonus', 'MstUserLevel',
    'MstUserLevelBonus', 'MstUserLevelBonusGroup', 'MstWhiteWord', 'OprGachaDisplayUnitI18n', 'OprGachaUpper',
    'OprGachaUseResource', 'OprProduct'
}

def main():
    base_dir = Path('domain/tasks/masterdata-entry/id-numbering-analysis/results')
    
    # 提案CSVを読み込み
    with open(base_dir / 'id_pattern_proposal.csv', 'r', encoding='utf-8') as f:
        reader = csv.DictReader(f)
        rows = [row for row in reader if row['テーブル'] in numbering_tables]
    
    # 通算連番テーブルの提案のみを出力
    with open(base_dir / 'id_pattern_proposal_summary.csv', 'w', encoding='utf-8', newline='') as f:
        fieldnames = ['テーブル', '列', '現在のパターン', '説明', '提案パターン', '提案理由']
        writer = csv.DictWriter(f, fieldnames=fieldnames)
        writer.writeheader()
        for row in rows:
            writer.writerow({
                'テーブル': row['テーブル'],
                '列': row['列'],
                '現在のパターン': row['パターン'],
                '説明': row['説明'],
                '提案パターン': row['提案パターン'],
                '提案理由': row['提案理由']
            })
    
    print(f"通算連番テーブルの提案サマリーを作成しました: {len(rows)}件")
    print(f"出力先: {base_dir / 'id_pattern_proposal_summary.csv'}")

if __name__ == "__main__":
    main()
