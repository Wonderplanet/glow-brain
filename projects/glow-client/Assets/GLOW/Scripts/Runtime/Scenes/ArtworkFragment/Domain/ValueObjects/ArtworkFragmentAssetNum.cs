using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.ArtworkFragment.Domain.ValueObjects
{
    public record ArtworkFragmentAssetNum(ObscuredInt Value)
    {
        public static ArtworkFragmentAssetNum Empty => new(0);
    }
}
