using System.Collections.Generic;

namespace GLOW.Scenes.Home.Domain.Models
{
    public record HomeMainKomaSettingUseCaseModel(
        HomeMainKomaSettingIndex InitialSelectedIndex,
        IReadOnlyList<HomeMainKomaPatternUseCaseModel>  HomeMainKomaPatternUseCaseModels)
    {
        public static HomeMainKomaSettingUseCaseModel Empty { get; } = new(
            HomeMainKomaSettingIndex.Empty,
            new List<HomeMainKomaPatternUseCaseModel>()
        );
    };
}
