namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record KomaEffectBackPrefabAssetPath(string Value)
    {
        const string AssetPathFormat = "koma_effect_{0}_back";

        public static KomaEffectBackPrefabAssetPath Empty { get; } = new(string.Empty);

        public static KomaEffectBackPrefabAssetPath FromAssetKey(KomaEffectAssetKey assetKey)
        {
            return new KomaEffectBackPrefabAssetPath(string.Format(AssetPathFormat, assetKey.Value));
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
