using WondlerPlanet.CheatProtectKit.ObscuredTypes;
namespace GLOW.Core.Domain.ValueObjects.Campaign
{
    public record CampaignDescription(ObscuredString Value)
    {
        public static CampaignDescription Empty { get; } = new(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    };
}
