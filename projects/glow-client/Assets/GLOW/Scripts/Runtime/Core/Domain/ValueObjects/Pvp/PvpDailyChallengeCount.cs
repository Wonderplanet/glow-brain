using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Pvp
{
    public record PvpDailyChallengeCount(ObscuredInt Value) : IQuestChallengeCountable
    {
        public static PvpDailyChallengeCount Empty { get; } = new PvpDailyChallengeCount(0);
        public static PvpDailyChallengeCount Zero { get; } = new PvpDailyChallengeCount(0);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsEnough()
        {
            return Value > 0;
        }
    }
}
