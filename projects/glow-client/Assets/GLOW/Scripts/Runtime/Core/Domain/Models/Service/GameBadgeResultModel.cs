

using System.Collections.Generic;

namespace GLOW.Core.Domain.Models
{
    public record GameBadgeResultModel(
        BadgeModel Badge,
        IReadOnlyList<MngContentCloseModel> MngContentCloses)
    {
        public static GameBadgeResultModel Empty { get; } = new GameBadgeResultModel(
            BadgeModel.Empty,
            new List<MngContentCloseModel>());
    }
}
