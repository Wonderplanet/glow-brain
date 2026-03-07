using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.ArtworkEffect;

namespace GLOW.Core.Domain.Models
{
    public record MstArtworkEffectModel(
        ArtworkEffectType Type,
        ArtworkEffectValue Grade1Value,
        ArtworkEffectValue Grade2Value,
        ArtworkEffectValue Grade3Value,
        ArtworkEffectValue Grade4Value,
        ArtworkEffectValue Grade5Value,
        IReadOnlyList<MstArtworkEffectTargetRuleModel> TargetRules,
        IReadOnlyList<MstArtworkEffectActivationRuleModel> ActivationRules)
    {
        public static MstArtworkEffectModel Empty { get; } = new (
            ArtworkEffectType.AttackPowerUp,
            ArtworkEffectValue.Empty,
            ArtworkEffectValue.Empty,
            ArtworkEffectValue.Empty,
            ArtworkEffectValue.Empty,
            ArtworkEffectValue.Empty,
            new List<MstArtworkEffectTargetRuleModel>(),
            new List<MstArtworkEffectActivationRuleModel>());

        public ArtworkEffectValue GetGradeValue(ArtworkGradeLevel grade)
        {
            switch (grade.Value)
            {
                case 1: return Grade1Value;
                case 2: return Grade2Value;
                case 3: return Grade3Value;
                case 4: return Grade4Value;
                case 5: return Grade5Value;
                default: return ArtworkEffectValue.Empty;
            }
        }
    }
}
