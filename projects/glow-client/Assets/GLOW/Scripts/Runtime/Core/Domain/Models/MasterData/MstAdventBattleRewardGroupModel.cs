using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;

namespace GLOW.Core.Domain.Models
{
    public record MstAdventBattleRewardGroupModel(
        MasterDataId Id,
        MasterDataId MstAdventBattleId,
        IReadOnlyList<MstAdventBattleRewardModel> Rewards,
        AdventBattleRewardCategory RewardCategory,
        AdventBattleRewardCondition RewardCondition)
    {
        public static MstAdventBattleRewardGroupModel Empty { get; } = new(
            MasterDataId.Empty,
            MasterDataId.Empty,
            new List<MstAdventBattleRewardModel>(),
            AdventBattleRewardCategory.Rank,
            AdventBattleRewardCondition.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}