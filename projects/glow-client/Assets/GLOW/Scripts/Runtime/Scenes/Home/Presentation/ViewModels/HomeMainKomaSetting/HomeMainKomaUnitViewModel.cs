using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Domain.ValueObjects;

namespace GLOW.Scenes.Home.Presentation.ViewModels
{
    public record HomeMainKomaUnitViewModel(
        MasterDataId MstUnitId,
        HomeMainKomaUnitAssetSetPlaceIndex PlaceIndex,
        HomeMainKomaUnitAssetPath HomeMainKomaUnitAssetPath
    )
    {
        public static HomeMainKomaUnitViewModel CreateEmpty(HomeMainKomaUnitAssetSetPlaceIndex index)
        {
            return new HomeMainKomaUnitViewModel(
                MasterDataId.Empty,
                index,
                HomeMainKomaUnitAssetPath.Empty
                );
        }
    }
}
