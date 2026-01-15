using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects
{
    public record ItemIconAssetPath(string Value)
    {
        const string AssetPathFormat = "item_icon_{0}";

        public static ItemIconAssetPath Empty { get; } =  new (string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public static ItemIconAssetPath FromAssetKey(ItemAssetKey assetKey)
        {
            return new ItemIconAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }

        public static ItemIconAssetPath FromAssetKey(PlayerResourceAssetKey assetKey)
        {
            return new ItemIconAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }

        public PlayerResourceIconAssetPath ToPlayerResourceIconAssetPath()
        {
            return new PlayerResourceIconAssetPath(Value);
        }
    }
}
