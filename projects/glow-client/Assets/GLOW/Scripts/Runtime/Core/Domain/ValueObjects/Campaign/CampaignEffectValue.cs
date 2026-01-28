using System.Globalization;
using Cysharp.Text;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Campaign
{
    public record CampaignEffectValue(ObscuredInt Value)
    {
        public static CampaignEffectValue Empty { get; } = new (-1);
        public static CampaignEffectValue Zero { get; } = new (0);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public string ToFraction()
        {
            const int baseValue = 100;
            int denominator = baseValue / Value;
            return ZString.Format("1/{0}", denominator.ToString("N0", CultureInfo.InvariantCulture));
        }

        public string ToStringTimes()
        {
            const int baseValue = 100;
            int multiplier = Value / baseValue;
            return ZString.Format("{0}", multiplier.ToString("N0", CultureInfo.InvariantCulture));
        }
    }
}