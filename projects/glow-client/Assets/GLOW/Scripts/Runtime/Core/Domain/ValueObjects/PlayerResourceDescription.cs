using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record PlayerResourceDescription(ObscuredString Value)
    {
        public static PlayerResourceDescription Empty { get; } = new PlayerResourceDescription("");

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        public static PlayerResourceDescription TranslateFromItemDescription(ItemDescription itemDescription)
        {
            return new PlayerResourceDescription(itemDescription.Value);
        }
    }

}