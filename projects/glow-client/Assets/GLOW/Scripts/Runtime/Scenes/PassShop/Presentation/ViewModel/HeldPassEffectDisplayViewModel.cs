using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pass;

namespace GLOW.Scenes.PassShop.Presentation.ViewModel
{
    public record HeldPassEffectDisplayViewModel(
        DisplayHoldingPassBannerAssetPath DisplayHoldingPassBannerAssetPath,
        RemainingTimeSpan RemainingTimeSpan);
}