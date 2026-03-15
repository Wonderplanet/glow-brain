using System.Collections.Generic;

namespace GLOW.Core.Domain.Models.Pvp
{
    public record PvpChangeOpponentResultModel(
        IReadOnlyList<OpponentSelectStatusModel> OpponentSelectStatuses
    )
    {
        public static PvpChangeOpponentResultModel Empty { get; } = new(new List<OpponentSelectStatusModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

