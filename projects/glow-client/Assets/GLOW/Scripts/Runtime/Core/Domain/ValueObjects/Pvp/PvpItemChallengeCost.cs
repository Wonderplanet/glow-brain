using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Pvp
{
    public record PvpItemChallengeCost(ObscuredInt Value)
    {
        public static PvpItemChallengeCost Empty { get; } = new PvpItemChallengeCost(0);
        public static PvpItemChallengeCost Zero { get; } = new PvpItemChallengeCost(0);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}