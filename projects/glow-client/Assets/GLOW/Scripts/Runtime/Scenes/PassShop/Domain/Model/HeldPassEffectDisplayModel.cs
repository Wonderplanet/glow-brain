using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pass;

namespace GLOW.Scenes.PassShop.Domain.Model
{
    public record HeldPassEffectDisplayModel(
        MasterDataId MstShopPassId,
        DisplayHoldingPassBannerAssetPath DisplayHoldingPassBannerAssetPath,
        RemainingTimeSpan RemainingTimeSpan)
    {
        public static HeldPassEffectDisplayModel Empty { get; } = new HeldPassEffectDisplayModel(
            MasterDataId.Empty,
            DisplayHoldingPassBannerAssetPath.Empty,
            RemainingTimeSpan.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}