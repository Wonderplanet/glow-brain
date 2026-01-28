using Cysharp.Text;

namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record KomaEffectFrontPrefabAssetPath(string Value)
    {
        const string AssetPathFormat = "koma_effect_{0}_front";

        public static KomaEffectFrontPrefabAssetPath Empty { get; } = new(string.Empty);

        public static KomaEffectFrontPrefabAssetPath FromAssetKey(KomaEffectAssetKey assetKey)
        {
            return new KomaEffectFrontPrefabAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
