using System.Collections.Generic;
using GLOW.Scenes.Home.Domain.ValueObjects;

namespace GLOW.Scenes.Home.Domain.Models
{
    public record HomeMainKomaUseCaseModel(
        HomeMainKomaPatternAssetPath HomeMainKomaPatternAssetPath,
        IReadOnlyList<HomeMainKomaUnitUseCaseModel> HomeMainKomaUnitUseCaseModels)
    {
        public static HomeMainKomaUseCaseModel Empty { get; } = new(
            HomeMainKomaPatternAssetPath.Empty,
            new List<HomeMainKomaUnitUseCaseModel>()
        );
    };
}
