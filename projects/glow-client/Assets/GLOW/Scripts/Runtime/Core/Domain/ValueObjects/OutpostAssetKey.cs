using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record OutpostAssetKey(ObscuredString Value)
    {
        public static OutpostAssetKey Empty { get; } = new (string.Empty);
        public static OutpostAssetKey PlayerDefault { get; } = new OutpostAssetKey("player_default");
        public static OutpostAssetKey EnemyDefault { get; } = new OutpostAssetKey("enemy_default");
        public static OutpostAssetKey PvpOpponentDefault { get; } = new OutpostAssetKey("pvpplayer_default");

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
