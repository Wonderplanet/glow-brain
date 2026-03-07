using GLOW.Scenes.GachaContent.Presentation.ViewModels;
using GLOW.Scenes.GachaList.Presentation.ViewModels.StepUpGacha;

namespace GLOW.Scenes.GachaList.Presentation.ViewModels
{
    public record GachaListElementViewModel(
        GachaFooterBannerViewModel GachaFooterBannerViewModel,
        GachaContentAssetViewModel GachaContentAssetViewModel,
        GachaContentViewModel GachaContentViewModel,
        StepUpGachaViewModel StepUpGachaViewModel)
    {
        public static GachaListElementViewModel Empty { get; } =
            new(
                GachaFooterBannerViewModel.Empty,
                GachaContentAssetViewModel.Empty,
                GachaContentViewModel.Empty,
                StepUpGachaViewModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsStepUpGacha()
        {
            return !StepUpGachaViewModel.IsEmpty();
        }
    };
}
