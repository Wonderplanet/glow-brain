using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaList.Domain.Model
{
    public record MedalGachaModel(
        MasterDataId GachaId,
        GachaBannerAssetPath GachaBannerAssetPath,
        GachaDescription GachaDescription,
        PlayerResourceModel PlayerResourceModel,
        CostAmount DrawCostAmount,
        DrawableFlag DrawableFlag,
        GachaRemainingTimeText GachaRemainingTimeText,
        GachaThresholdText GachaThresholdText)
    {
        public static MedalGachaModel Empty = new (
            MasterDataId.Empty,
            GachaBannerAssetPath.Empty,
            GachaDescription.Empty,
            PlayerResourceModel.Empty,
            CostAmount.Empty,
            DrawableFlag.False,
            GachaRemainingTimeText.Empty,
            GachaThresholdText.Empty
        );
    }
}
