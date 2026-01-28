using Cysharp.Text;

namespace GLOW.Modules.Tutorial.Domain.ValueObject
{
    public record TutorialSequenceAssetPath(string Value)
    {
        const string AssetPath = "tutorial_sequence_{0}";

        public static TutorialSequenceAssetPath ToTutorialSequenceAssetPath(TutorialSequenceAssetKey key)
        {
            return new TutorialSequenceAssetPath(ZString.Format(AssetPath, key.Value));
        }
    }
}
