using System.Collections.Generic;
using GLOW.Scenes.PvpTop.Domain.ValueObject;

namespace GLOW.Scenes.PvpTop.Domain.Model
{
    public record PvpTopOpponentUseCaseModel(
        PvpOpponentRefreshCoolTime PvpOpponentRefreshCoolTime,
        IReadOnlyList<PvpTopOpponentModel> OpponentModels)
    {
        public static PvpTopOpponentUseCaseModel Empty { get; } = new(
            PvpOpponentRefreshCoolTime.Empty,
            new List<PvpTopOpponentModel>()
            );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
