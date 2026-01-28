using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaList.Presentation.ViewModels
{
    public record PremiumGachaViewModel(
        MasterDataId GachaId,
        GachaBannerAssetPath GachaBannerAssetPath,
        GachaDescription GachaDescription,
        NotificationBadge NotificationBadge,
        CostType CostType,
        PlayerResourceIconAssetPath SingleDrawCostIconAssetPath,
        CostAmount SingleDrawCostAmount,
        PlayerResourceIconAssetPath MultiDrawCostIconAssetPath,
        CostAmount MultiDrawCostAmount,
        AdGachaDrawableFlag AdDrawableFlag,
        AdGachaResetRemainingText AdGachaResetRemainingText,
        AdGachaDrawableCount AdGachaDrawableCount,
        GachaRemainingTimeText GachaRemainingTimeText,
        GachaThresholdText GachaThresholdText,
        GachaFixedPrizeDescription GachaFixedPrizeDescription
    )
    {
        public static PremiumGachaViewModel Empty { get; } = new(
            MasterDataId.Empty,
            GachaBannerAssetPath.Empty,
            GachaDescription.Empty,
            NotificationBadge.False,
            CostType.Item,
            PlayerResourceIconAssetPath.Empty,
            CostAmount.Empty,
            PlayerResourceIconAssetPath.Empty,
            CostAmount.Empty,
            AdGachaDrawableFlag.False,
            AdGachaResetRemainingText.Empty,
            AdGachaDrawableCount.Zero,
            GachaRemainingTimeText.Empty,
            GachaThresholdText.Empty,
            GachaFixedPrizeDescription.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
