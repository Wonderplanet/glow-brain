using System;
using System.Globalization;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Campaign
{
    public record CampaignStartAt(ObscuredDateTimeOffset Value)
    {
        public static CampaignStartAt Empty { get; } = new(DateTimeOffset.MinValue);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public string ToFormattedString()
        {
            return Value.ToString("yyyy/MM/dd", CultureInfo.InvariantCulture);
        }
    }
}