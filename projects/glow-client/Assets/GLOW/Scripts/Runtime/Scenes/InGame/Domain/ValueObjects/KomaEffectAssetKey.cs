using GLOW.Core.Domain.Constants;

namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record KomaEffectAssetKey(string Value)
    {
        public static KomaEffectAssetKey Empty { get; } = new(string.Empty);

        public static KomaEffectAssetKey FromKomaEffectType(KomaEffectType komaEffectType)
        {
            return new KomaEffectAssetKey(komaEffectType.ToString());
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
