using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.PvpPreviousSeasonResult.Presentation.ViewModels
{
    public record PvpPreviousSeasonResultViewModel(
        PvpRankClassType PvpRankClassType,
        PvpRankLevel RankClassLevel,
        PvpPoint Point,
        PvpRankingRank Ranking,
        IReadOnlyList<PlayerResourceIconViewModel> PvpRewards)
    {
        public static PvpPreviousSeasonResultViewModel Empty { get; } = new(
            PvpRankClassType.Bronze,
            PvpRankLevel.Empty,
            PvpPoint.Zero,
            PvpRankingRank.Empty,
            new List<PlayerResourceIconViewModel>()
        );

        public bool IsEmpty() => ReferenceEquals(this, Empty);
    }
}

