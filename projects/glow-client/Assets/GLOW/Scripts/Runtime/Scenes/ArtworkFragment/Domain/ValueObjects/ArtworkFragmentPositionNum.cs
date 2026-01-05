using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.ArtworkFragment.Domain.ValueObjects
{
    public record ArtworkFragmentPositionNum(ObscuredInt  Value)
    {
        public static ArtworkFragmentPositionNum Empty { get; } = new(0);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    };
}
