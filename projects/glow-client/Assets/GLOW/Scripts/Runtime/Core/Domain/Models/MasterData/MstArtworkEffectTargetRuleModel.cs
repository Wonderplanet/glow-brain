using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.ArtworkEffect;

namespace GLOW.Core.Domain.Models
{
    public record MstArtworkEffectTargetRuleModel(
        ArtworkEffectTargetRuleType Type,
        ArtworkEffectTargetValue Value)
    {
        public static MstArtworkEffectTargetRuleModel Empty { get; } = new (
            ArtworkEffectTargetRuleType.All,
            ArtworkEffectTargetValue.Empty);

        public bool IsEmpty() => ReferenceEquals(this, Empty);
    }
}
