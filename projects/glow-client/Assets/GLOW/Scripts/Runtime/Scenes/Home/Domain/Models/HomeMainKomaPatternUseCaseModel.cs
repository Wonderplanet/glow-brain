using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Domain.ValueObjects;

namespace GLOW.Scenes.Home.Domain.Models
{
    public record HomeMainKomaPatternUseCaseModel(
        MasterDataId MstKomaPatterId,
        HomeMainKomaPatternName Name,
        HomeMainKomaPatternAssetPath AssetPath,
        IReadOnlyList<HomeMainKomaUnitUseCaseModel> HomeMainKomaUnitUseCaseModels
    )
    {
        public static HomeMainKomaPatternUseCaseModel Empty { get; } = new(
            MasterDataId.Empty,
            HomeMainKomaPatternName.Empty,
            HomeMainKomaPatternAssetPath.Empty,
            new List<HomeMainKomaUnitUseCaseModel>()
        );
    };
}