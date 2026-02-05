using GLOW.Scenes.GachaContent.Presentation.ViewModels;

namespace GLOW.Scenes.GachaList.Presentation.ViewModels
{
    public record GachaListElementViewModel(
        GachaFooterBannerViewModel GachaFooterBannerViewModel,
        GachaContentAssetViewModel GachaContentAssetViewModel,
        GachaContentViewModel GachaContentViewModel)
    {
        public static GachaListElementViewModel Empty { get; } =
            new(
                GachaFooterBannerViewModel.Empty,
                GachaContentAssetViewModel.Empty,
                GachaContentViewModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    };
}
