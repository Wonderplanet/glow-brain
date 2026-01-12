namespace GLOW.Scenes.ExchangeShop.Domain.ValueObject
{
    public record ExchangeContentBannerAssetPath(string Value)
    {
        const string AssetPath = "shop_{0}";

        public static ExchangeContentBannerAssetPath Empty { get; } = new ExchangeContentBannerAssetPath(string.Empty);

        public static ExchangeContentBannerAssetPath FromAssetKey(string assetKey)
        {
            return new ExchangeContentBannerAssetPath(string.Format(AssetPath, assetKey));
        }
    }
}
