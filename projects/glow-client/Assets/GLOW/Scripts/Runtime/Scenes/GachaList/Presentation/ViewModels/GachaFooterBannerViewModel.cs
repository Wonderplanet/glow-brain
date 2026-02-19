using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.GachaList.Presentation.ViewModels
{
    public record GachaFooterBannerViewModel(
        MasterDataId OprGachaId,
        GachaBannerAssetPath GachaBannerAssetPath
    )
    {
        public static GachaFooterBannerViewModel Empty { get;} =
            new GachaFooterBannerViewModel(
                MasterDataId.Empty,
                GachaBannerAssetPath.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    };
}
