using System.Collections.Generic;
using GLOW.Scenes.Home.Domain.ValueObjects;

namespace GLOW.Scenes.Home.Presentation.ViewModels
{
    public record HomeMainKomaViewModel(
        HomeMainKomaPatternAssetPath HomeMainKomaPatternAssetPath,
        IReadOnlyList<HomeMainKomaUnitViewModel> HomeMainKomaUnitViewModels);
}