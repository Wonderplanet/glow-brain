using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstAdventBattleClearRewardModel(
        MasterDataId Id,
        MasterDataId MstAdventBattleId,
        AdventBattleClearRewardCategory RewardCategory,
        ResourceType ResourceType,
        MasterDataId ResourceId,
        ObscuredPlayerResourceAmount ResourceAmount,
        Percentage Percentage,
        SortOrder SortOrder)
    {
        public static MstAdventBattleClearRewardModel Empty = new(
            MasterDataId.Empty,
            MasterDataId.Empty,
            AdventBattleClearRewardCategory.Always,
            ResourceType.Item,
            MasterDataId.Empty,
            ObscuredPlayerResourceAmount.Empty,
            Percentage.Empty,
            SortOrder.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}