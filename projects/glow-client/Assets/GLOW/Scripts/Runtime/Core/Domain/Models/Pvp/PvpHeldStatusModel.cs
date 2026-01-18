using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;

namespace GLOW.Core.Domain.Models.Pvp
{
    public record PvpHeldStatusModel(
        ContentSeasonSystemId SysPvpSeasonId,
        PvpHeldNumber HeldNumber,
        PvpStartAt StartAt,
        PvpEndAt EndAt
    )
    {
        public static PvpHeldStatusModel Empty { get; } = new(
            ContentSeasonSystemId.Empty,
            PvpHeldNumber.Empty,
            PvpStartAt.Empty,
            PvpEndAt.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
