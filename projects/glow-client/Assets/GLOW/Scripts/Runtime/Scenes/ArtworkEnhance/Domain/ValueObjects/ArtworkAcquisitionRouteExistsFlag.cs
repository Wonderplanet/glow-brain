using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.ArtworkEnhance.Domain.ValueObjects
{
    public record ArtworkAcquisitionRouteExistsFlag(ObscuredBool Value)
    {
        public static ArtworkAcquisitionRouteExistsFlag False { get; } = new ArtworkAcquisitionRouteExistsFlag(false);
        public static ArtworkAcquisitionRouteExistsFlag True { get; } = new ArtworkAcquisitionRouteExistsFlag(true);

        public static implicit operator bool(ArtworkAcquisitionRouteExistsFlag acquisitionRouteExistsFlag) => acquisitionRouteExistsFlag.Value;
    }
}
