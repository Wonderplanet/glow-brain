using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaList.Domain.Model
{
    public record PremiumGachaModel(
        MasterDataId GachaId,
        GachaBannerAssetPath GachaBannerAssetPath,
        GachaDescription GachaDescription,
        NotificationBadge NotificationBadge,
        CostType SingleDrawCostType,
        PlayerResourceModel SinglePlayerResourceModel,
        CostAmount SingleDrawCostAmount,
        PlayerResourceModel MultiPlayerResourceModel,
        CostAmount MultiDrawCostAmount,
        AdGachaDrawableFlag CanAdGachaDraw,
        AdGachaResetRemainingText AdGachaResetRemainingText,
        AdGachaDrawableCount AdGachaDrawableCount,
        GachaRemainingTimeText GachaRemainingTimeText,
        GachaThresholdText GachaThresholdText,
        GachaFixedPrizeDescription GachaFixedPrizeDescription
        )
    {
        public static PremiumGachaModel Empty { get; } = new(
            MasterDataId.Empty,
            GachaBannerAssetPath.Empty,
            GachaDescription.Empty,
            NotificationBadge.False,
            CostType.Item,
            PlayerResourceModel.Empty,
            CostAmount.Empty,
            PlayerResourceModel.Empty,
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
