using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Pvp
{
    public record PvpRankAssetKey(ObscuredString Value)
    {
        public static PvpRankAssetKey Empty { get; } = new PvpRankAssetKey(string.Empty);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}