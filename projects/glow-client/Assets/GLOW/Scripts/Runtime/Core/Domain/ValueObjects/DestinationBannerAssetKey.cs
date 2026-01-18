namespace GLOW.Core.Domain.ValueObjects
{
    public record DestinationBannerAssetKey(string Value)
    {
        public static DestinationBannerAssetKey Empty { get; } = new(string.Empty);
        public static string GenerateBaicShopBannerAssetKey => "BasicShopBanner";
        public string AssetPath => $"{Value}";

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
