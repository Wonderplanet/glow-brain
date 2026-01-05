using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record CampaignPercentage(ObscuredInt Value)
    {
        public static CampaignPercentage Empty { get; } = new (-1);
        public static CampaignPercentage Zero { get; } = new (0);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }

}
