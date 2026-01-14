using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects
{
    public record EventBackgroundAssetPath(string Value)
    {
        const string AssetPath = "{0}_quest_select";
        public static EventBackgroundAssetPath ToBackgroundAssetPath(EventAssetKey key) => new EventBackgroundAssetPath(ZString.Format(AssetPath, key.Value));
    }
}
