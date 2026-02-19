namespace GLOW.Scenes.ExchangeShop.Domain.ValueObject
{
    public record ExchangeContentBannerAssetKey(string Value)
    {
        public static ExchangeContentBannerAssetKey Empty { get; } = new ExchangeContentBannerAssetKey(string.Empty);
    }
}
