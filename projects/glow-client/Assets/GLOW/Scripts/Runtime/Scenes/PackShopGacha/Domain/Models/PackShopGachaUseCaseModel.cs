using System.Collections.Generic;

namespace GLOW.Scenes.PackShopGacha.Domain.Models
{
    public record PackShopGachaUseCaseModel(IReadOnlyList<PackShopGachaBannerModel> PackShopGachaBannerModels)
    {
        public static PackShopGachaUseCaseModel Empty { get; } =
            new PackShopGachaUseCaseModel(new List<PackShopGachaBannerModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}