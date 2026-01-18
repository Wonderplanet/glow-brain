using GLOW.Core.Domain.ValueObjects.Pvp;

namespace GLOW.Core.Domain.Models.Pvp
{
    public record PvpEndResultBonusPointModel(
        PvpPoint ResultPoint,
        PvpPoint OpponentBonusPoint,
        PvpPoint TimeBonusPoint)
    {
        public static PvpEndResultBonusPointModel Empty { get; } = new(
            PvpPoint.Empty,
            PvpPoint.Empty,
            PvpPoint.Empty
        );

        public PvpPoint AllBonusPoint => ResultPoint + OpponentBonusPoint + TimeBonusPoint;

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
