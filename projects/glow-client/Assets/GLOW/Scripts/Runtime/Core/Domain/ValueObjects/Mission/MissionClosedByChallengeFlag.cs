

namespace GLOW.Core.Domain.ValueObjects.Mission
{
    public record MissionClosedByChallengeFlag(bool Value)
    {
        public static MissionClosedByChallengeFlag True { get; } = new MissionClosedByChallengeFlag(true);
        public static MissionClosedByChallengeFlag False { get; } = new MissionClosedByChallengeFlag(false);

        public static implicit operator bool(MissionClosedByChallengeFlag flag) => flag.Value;
    }
}
