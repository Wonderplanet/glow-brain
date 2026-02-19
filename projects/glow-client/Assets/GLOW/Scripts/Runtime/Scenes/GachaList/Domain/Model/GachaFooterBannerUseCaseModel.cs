using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.GachaList.Domain.Model
{
    public record GachaFooterBannerUseCaseModel(
        MasterDataId OprGachaId,
        GachaBannerAssetPath GachaBannerAssetPath
    )
    {
        public static GachaFooterBannerUseCaseModel Empty { get; } =
            new(
                MasterDataId.Empty,
                GachaBannerAssetPath.Empty);
    };
}
