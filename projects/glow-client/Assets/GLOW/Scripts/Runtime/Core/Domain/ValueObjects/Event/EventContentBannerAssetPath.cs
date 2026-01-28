using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects
{
    public record EventContentBannerAssetPath(string Value)
    {
        public static EventContentBannerAssetPath Empty { get; } = new(string.Empty);
        public bool IsEmpty() => string.IsNullOrEmpty(Value);

        const string AssetPath = "{0}_banner";
        public static EventContentBannerAssetPath FromAssetKey(EventAssetKey key) => new EventContentBannerAssetPath(ZString.Format(AssetPath, key.Value));
    }
}
