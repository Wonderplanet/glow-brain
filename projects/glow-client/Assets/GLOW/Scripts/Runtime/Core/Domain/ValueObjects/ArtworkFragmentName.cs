using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record ArtworkFragmentName(ObscuredString Value)
    {
        public static ArtworkFragmentName Empty { get; } = new("");
        public bool IsEmpty() => ReferenceEquals(this, Empty);
    };
}
