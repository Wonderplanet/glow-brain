using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record AttackFeedbackHPCalculatorResultModel(
        HP HP,
        Heal TotalHeal,
        IReadOnlyList<AttackFeedbackHPCalculatorResultDetailModel> Details);
}
