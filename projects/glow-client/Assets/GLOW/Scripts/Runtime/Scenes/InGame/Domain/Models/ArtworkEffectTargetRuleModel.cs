using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.ArtworkEffect;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record ArtworkEffectTargetRuleModel(
        ArtworkEffectTargetRuleType TargetType,
        ArtworkEffectTargetValue EffectTargetValue)
    {
        public static ArtworkEffectTargetRuleModel Empty { get; } = new(
            ArtworkEffectTargetRuleType.All,
            ArtworkEffectTargetValue.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
