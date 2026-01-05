using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaList.Presentation.ViewModels
{
    public record TutorialGachaBannerViewModel(
        MasterDataId GachaId,
        GachaBannerAssetPath GachaBannerAssetPath,
        GachaDescription GachaDescription)
    {
        public static TutorialGachaBannerViewModel Empty { get; }= new TutorialGachaBannerViewModel(
            MasterDataId.Empty,
            GachaBannerAssetPath.Empty,
            GachaDescription.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
