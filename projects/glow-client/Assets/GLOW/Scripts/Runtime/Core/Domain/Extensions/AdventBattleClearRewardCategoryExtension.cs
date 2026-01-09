using System;
using GLOW.Core.Domain.Constants;

namespace GLOW.Core.Domain.Extensions
{
    public static class AdventBattleClearRewardCategoryExtension
    {
        public static RewardCategory ToRewardCategory(this AdventBattleClearRewardCategory rewardCategory) 
        {
            return rewardCategory switch 
            {
                AdventBattleClearRewardCategory.FirstClear => RewardCategory.FirstClear,
                AdventBattleClearRewardCategory.Always => RewardCategory.Always,
                AdventBattleClearRewardCategory.Random => RewardCategory.Random,
                _ => throw new ArgumentOutOfRangeException(nameof(rewardCategory), rewardCategory, null)
            };
        }
    }
}
