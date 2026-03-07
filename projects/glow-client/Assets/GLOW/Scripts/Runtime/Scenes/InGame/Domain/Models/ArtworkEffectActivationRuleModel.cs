using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.ArtworkEffect;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record ArtworkEffectActivationRuleModel(
        ArtworkEffectActivationRuleType ConditionType,
        ArtworkEffectActivationValue EffectActivationValue)
    {
        public static ArtworkEffectActivationRuleModel Empty { get; } = new(
            ArtworkEffectActivationRuleType.None,
            ArtworkEffectActivationValue.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
