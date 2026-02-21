using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record GachaFixedPrizeDescription(ObscuredString Value)
    {
        public static GachaFixedPrizeDescription Empty { get; } = new GachaFixedPrizeDescription("");
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}