using GLOW.Scenes.PvpTop.Domain.ValueObject;

namespace GLOW.Scenes.PvpTop.Domain.Model
{
    public record PvpTopRankingState(
        PvpRankingTargetType PvpRankingTargetType,
        PvpRankingOpeningType PvpRankingOpeningType
    )
    {
        public static PvpTopRankingState Empty { get; } = new(
            PvpRankingTargetType.None,
            PvpRankingOpeningType.NotStarted
        );

        public bool IsRankingButtonGrayOutVisible()
        {
            if (PvpRankingTargetType == PvpRankingTargetType.None)
            {
                return true;
            }

            return PvpRankingOpeningType != PvpRankingOpeningType.Opening;
        }
    };
}
