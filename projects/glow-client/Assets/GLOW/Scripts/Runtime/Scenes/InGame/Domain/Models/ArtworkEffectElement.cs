using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.ArtworkEffect;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record ArtworkEffectElement(
        ArtworkEffectType EffectType,
        ArtworkEffectValue EffectValue,
        IReadOnlyList<ArtworkEffectTargetRuleModel> TargetRules,
        IReadOnlyList<ArtworkEffectActivationRuleModel> ActivationRules)
    {
        public static ArtworkEffectElement Empty { get; } = new(
            ArtworkEffectType.AttackPowerUp,
            ArtworkEffectValue.Empty,
            new List<ArtworkEffectTargetRuleModel>(),
            new List<ArtworkEffectActivationRuleModel>()
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
