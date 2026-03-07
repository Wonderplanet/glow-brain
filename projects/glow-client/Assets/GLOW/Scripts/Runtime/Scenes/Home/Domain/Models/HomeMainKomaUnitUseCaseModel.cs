using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Domain.ValueObjects;

namespace GLOW.Scenes.Home.Domain.Models
{
    public record HomeMainKomaUnitUseCaseModel(
        MasterDataId MstUnitId,
        HomeMainKomaUnitAssetSetPlaceIndex PlaceIndex,
        HomeMainKomaUnitAssetPath HomeMainKomaUnitAssetPath)
    {
        public static HomeMainKomaUnitUseCaseModel CreateEmpty(HomeMainKomaUnitAssetSetPlaceIndex index)
        {
            return new HomeMainKomaUnitUseCaseModel(
                MasterDataId.Empty,
                index,
                HomeMainKomaUnitAssetPath.Empty
                );
        }
    };
}
