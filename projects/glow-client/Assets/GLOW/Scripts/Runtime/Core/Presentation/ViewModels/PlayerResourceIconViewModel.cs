using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Constants;

namespace GLOW.Core.Presentation.ViewModels
{
    public record PlayerResourceIconViewModel(
        MasterDataId Id,
        ResourceType ResourceType,
        PlayerResourceIconAssetPath AssetPath,
        IconRarityFrameType RarityFrameType,
        Rarity Rarity,
        PlayerResourceAmount Amount,
        PlayerResourceAcquiredFlag IsAcquired,
        StageClearTime ClearTime,
        RewardCategoryLabel RewardCategoryLabel)
    {
        public static PlayerResourceIconViewModel Empty { get; } = new(
            MasterDataId.Empty,
            ResourceType.Coin,
            PlayerResourceIconAssetPath.Empty,
            IconRarityFrameType.Item,
            Rarity.R,
            PlayerResourceAmount.Empty,
            PlayerResourceAcquiredFlag.False,
            StageClearTime.Empty,
            RewardCategoryLabel.None
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
