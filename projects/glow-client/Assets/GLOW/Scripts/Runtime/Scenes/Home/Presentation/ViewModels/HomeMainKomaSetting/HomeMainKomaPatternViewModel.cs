using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Views.RotationBanner;
using GLOW.Scenes.Home.Domain.ValueObjects;

namespace GLOW.Scenes.Home.Presentation.ViewModels
{
    public record HomeMainKomaPatternViewModel(
        MasterDataId MstHomeMainKomaPatternId,
        HomeMainKomaPatternName Name,
        HomeMainKomaPatternAssetPath AssetPath,
        IReadOnlyList<HomeMainKomaUnitViewModel> HomeMainKomaUnitViewModels)
        : IRotationPageItemViewModel
    {
        IRotationPageItemViewModel IRotationPageItemViewModel.Duplicate()
        {
            return new HomeMainKomaPatternViewModel(
                MstHomeMainKomaPatternId,
                Name,
                AssetPath,
                HomeMainKomaUnitViewModels
            );
        }
    }
}
