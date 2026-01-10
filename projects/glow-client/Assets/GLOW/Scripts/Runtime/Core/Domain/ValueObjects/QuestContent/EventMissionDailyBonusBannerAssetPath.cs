using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects
{
    public record EventMissionDailyBonusBannerAssetPath(string Value)
    {
        //EventContentBannerAssetPathと統合できるかもしれない
        const string AssetPath = "{0}_loginbonus";
        public static EventMissionDailyBonusBannerAssetPath Empty { get; } = new(string.Empty);

        public bool IsEmpty() => ReferenceEquals(this, Empty);

        public static EventMissionDailyBonusBannerAssetPath FromAssetKey(EventAssetKey key) => new(ZString.Format(AssetPath, key.Value));
    };
}
