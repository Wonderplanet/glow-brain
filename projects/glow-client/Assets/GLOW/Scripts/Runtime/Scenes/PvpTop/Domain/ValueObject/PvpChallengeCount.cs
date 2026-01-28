using System.Globalization;
using GLOW.Core.Domain.ValueObjects;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.PvpTop.Domain.ValueObject
{
    public record PvpChallengeCount(ObscuredInt Value)
    {
        public static PvpChallengeCount Empty { get; } = new PvpChallengeCount(0);
        public static PvpChallengeCount Zero => new PvpChallengeCount(0);

        public static PvpChallengeCount operator -(PvpChallengeCount a, PvpChallengeCount b) => new (a.Value - b.Value);
        public static PvpChallengeCount operator +(PvpChallengeCount a, PvpChallengeCount b) => new (a.Value + b.Value);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public override string ToString()
        {
            return Value.ToString("N0", CultureInfo.InvariantCulture);
        }

        public bool IsEnough()
        {
            return Value > 0;
        }
    }
}
