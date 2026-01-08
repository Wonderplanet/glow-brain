using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record ItemDescription(ObscuredString Value)
    {
        public static ItemDescription Empty { get; } = new ItemDescription("");

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
