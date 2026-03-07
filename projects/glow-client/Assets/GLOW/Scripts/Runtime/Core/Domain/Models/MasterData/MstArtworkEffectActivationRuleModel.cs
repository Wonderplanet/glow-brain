using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.ArtworkEffect;

namespace GLOW.Core.Domain.Models
{
    public record MstArtworkEffectActivationRuleModel(
        ArtworkEffectActivationRuleType Type,
        ArtworkEffectActivationValue Value)
    {
        public static MstArtworkEffectActivationRuleModel Empty { get; } = new (
            ArtworkEffectActivationRuleType.None,
            ArtworkEffectActivationValue.Empty);

        public bool IsEmpty() => ReferenceEquals(this, Empty);
    }
}
