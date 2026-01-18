using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Presentation.Modules.Audio
{
    public record SoundEffect(SoundEffectAssetKey AssetKey, SoundEffectTag Tag)
    {
        public static SoundEffect Empty { get; } = new (
            SoundEffectAssetKey.Empty, 
            SoundEffectTag.Common);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}