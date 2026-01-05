using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;

namespace GLOW.Core.Domain.Models.Pvp
{
    public record SysPvpSeasonModel(
        ContentSeasonSystemId Id,
        PvpStartAt StartAt,
        PvpEndAt EndAt,
        PvpClosedAt ClosedAt
    )
    {
        public static SysPvpSeasonModel Empty { get; } = new(
            ContentSeasonSystemId.Empty,
            PvpStartAt.Empty,
            PvpEndAt.Empty,
            PvpClosedAt.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
