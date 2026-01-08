using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.ValueObjects.Pvp;

namespace GLOW.Scenes.PvpPreviousSeasonResult.Domain.Models
{
    public record PvpPreviousSeasonResultAnimationModel(
        PvpRankClassType PvpRankClassType,
        PvpRankLevel RankClassLevel,
        PvpPoint Point,
        PvpRankingRank Ranking,
        IReadOnlyList<PlayerResourceModel> PvpRewards
    )
    {
        public static PvpPreviousSeasonResultAnimationModel Empty { get; } = new(
            PvpRankClassType.Bronze,
            PvpRankLevel.Empty,
            PvpPoint.Zero,
            PvpRankingRank.Empty,
            new List<PlayerResourceModel>()
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
