using WondlerPlanet.CheatProtectKit.ObscuredTypes;
namespace GLOW.Core.Domain.ValueObjects
{
    public record CampaignTitle(ObscuredString Value)
    {
        public static CampaignTitle Empty { get; } = new(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}