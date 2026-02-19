using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public interface IAttackFeedbackModelFactory
    {
        List<AttackFeedbackModel> Create(IReadOnlyList<AppliedAttackResultModel> appliedAttackResults);
    }
}
