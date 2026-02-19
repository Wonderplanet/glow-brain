using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public interface IAttackFeedbackHPCalculator
    {
        AttackFeedbackHPCalculatorResultModel CalculateHp(
            IReadOnlyList<AttackFeedbackModel> attackFeedbacks,
            FieldObjectId attackerId,
            HP currentHp,
            HP maxHp,
            HealInvalidationFlag isHealInvalidation);
    }
}
