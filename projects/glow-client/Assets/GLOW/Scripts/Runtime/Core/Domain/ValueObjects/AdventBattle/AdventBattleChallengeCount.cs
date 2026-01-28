using System.Globalization;
using GLOW.Core.Domain.ValueObjects.Campaign;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.AdventBattle
{
    public record AdventBattleChallengeCount(ObscuredInt Value) : IQuestChallengeCountable
    {
        public static AdventBattleChallengeCount Empty { get; } = new(0);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsZero()
        {
            return Value == 0;
        }

        public static AdventBattleChallengeCount operator -(AdventBattleChallengeCount a, AdventBattleChallengeCount b)
        {
            return new AdventBattleChallengeCount(a.Value - b.Value);
        }

        public static AdventBattleChallengeCount operator +(AdventBattleChallengeCount a, CampaignEffectValue b)
        {
            return new AdventBattleChallengeCount(a.Value + b.Value);
        }

        public string ToStringWithSeparate()
        {
            return Value.ToString("N0", CultureInfo.InvariantCulture);
        }

    }
}
