using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record ArtworkAssetKey(ObscuredString Value)
    {
        public static ArtworkAssetKey Empty { get; } = new (string.Empty);
        public static ArtworkAssetKey Default { get; } = new ("tutorial_0001");
        
        // アイテムアイコンなどで原画を表示する際に使用する汎用画像のアセットキー
        public static ArtworkAssetKey ArtworkIconAssetKey { get; } = new ("item_glo_90000");

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public PlayerResourceAssetKey ToPlayerResourceAssetKey()
        {
            return new PlayerResourceAssetKey(Value);
        }
    }
}
