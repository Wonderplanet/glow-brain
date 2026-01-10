using Cysharp.Text;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.QuestContent
{
    public record EventMissionBannerAssetPath(ObscuredString Value)
    {
        //EventContentBannerAssetPathと統合できるかもしれない
        const string AssetPath = "{0}_mission";
        public static EventMissionBannerAssetPath Empty { get; } = new (string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public static EventMissionBannerAssetPath FromAssetKey(EventAssetKey key)
        {
            return new EventMissionBannerAssetPath(ZString.Format(AssetPath, key.Value));
        }
    }
}