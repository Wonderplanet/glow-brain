using System.Globalization;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record EnhanceQuestChallengeCount(ObscuredInt Value) : IQuestChallengeCountable
    {
        public static EnhanceQuestChallengeCount Empty { get; } = new EnhanceQuestChallengeCount(0);
        public static EnhanceQuestChallengeCount Zero => new EnhanceQuestChallengeCount(0);

        public static EnhanceQuestChallengeCount operator -(EnhanceQuestChallengeCount a, EnhanceQuestChallengeCount b) => new (a.Value - b.Value);
        public static EnhanceQuestChallengeCount operator +(EnhanceQuestChallengeCount a, EnhanceQuestChallengeCount b) => new (a.Value + b.Value);

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
