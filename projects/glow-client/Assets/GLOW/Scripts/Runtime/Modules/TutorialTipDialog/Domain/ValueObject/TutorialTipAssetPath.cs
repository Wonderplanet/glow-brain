using Cysharp.Text;

namespace GLOW.Modules.TutorialTipDialog.Domain.ValueObject
{
    public record TutorialTipAssetPath(string Value)
    {
        const string AssetPathFormat = "tutorial_tip_image_{0}";

        public static TutorialTipAssetPath Empty { get; } = new TutorialTipAssetPath(string.Empty);

        public static TutorialTipAssetPath FromAssetKey(TutorialTipAssetKey assetKey)
        {
            return new TutorialTipAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }
    }
}
