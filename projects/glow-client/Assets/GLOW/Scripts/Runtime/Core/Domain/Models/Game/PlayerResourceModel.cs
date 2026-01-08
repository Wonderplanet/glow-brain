using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record PlayerResourceModel(
        ResourceType Type,
        MasterDataId Id,
        Rarity Rarity,
        PlayerResourceName Name,
        PlayerResourceDescription Description,
        PlayerResourceGroupSortOrder GroupSortOrder,
        SortOrder SortOrder,
        PlayerResourceAssetKey AssetKey,
        PlayerResourceAmount Amount,
        RewardCategory RewardCategory,
        bool IsAcquired,
        StageClearTime ClearTime)
    {
        public static PlayerResourceModel Empty { get; } = new (
            ResourceType.Item,
            MasterDataId.Empty,
            Rarity.R,
            PlayerResourceName.Empty,
            PlayerResourceDescription.Empty,
            PlayerResourceGroupSortOrder.MaxValue,
            SortOrder.MaxValue,
            PlayerResourceAssetKey.Empty,
            PlayerResourceAmount.Empty,
            RewardCategory.Always,
            false,
            StageClearTime.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
