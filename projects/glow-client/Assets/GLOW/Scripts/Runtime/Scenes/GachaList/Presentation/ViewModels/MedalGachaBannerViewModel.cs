using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaList.Presentation.ViewModels
{
    public record MedalGachaBannerViewModel(
        MasterDataId GachaId,
        GachaBannerAssetPath GachaBannerAssetPath,
        PlayerResourceIconAssetPath IconAssetPath,
        GachaDescription GachaDescription,
        CostAmount DrawCostAmount,
        DrawableFlag DrawableFlag,
        GachaRemainingTimeText GachaRemainingTimeText,
        GachaThresholdText GachaThresholdText);
}
