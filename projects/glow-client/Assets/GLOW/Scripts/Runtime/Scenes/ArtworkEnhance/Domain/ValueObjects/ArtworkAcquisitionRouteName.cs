using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.ArtworkEnhance.Domain.ValueObjects
{
    public record ArtworkAcquisitionRouteName(ObscuredString Value)
    {
        public static ArtworkAcquisitionRouteName Empty { get; } = new ArtworkAcquisitionRouteName(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
