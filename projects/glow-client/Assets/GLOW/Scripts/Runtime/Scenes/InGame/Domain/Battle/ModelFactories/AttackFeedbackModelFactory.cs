using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class AttackFeedbackModelFactory : IAttackFeedbackModelFactory
    {
        public List<AttackFeedbackModel> Create(IReadOnlyList<AppliedAttackResultModel> appliedAttackResults)
        {
            return appliedAttackResults
                .Select(Create)
                .Where(model => !model.IsEmpty())
                .ToList();
        }

        AttackFeedbackModel Create(AppliedAttackResultModel appliedAttackResult)
        {
            return appliedAttackResult.AttackHitData.HitType switch
            {
                AttackHitType.Drain => CreateDrainFeedback(appliedAttackResult),
                _ => AttackFeedbackModel.Empty
            };
        }

        AttackFeedbackModel CreateDrainFeedback(AppliedAttackResultModel appliedAttackResult)
        {
            var healPercentage = appliedAttackResult.AttackHitData.HitParameter1.ToPercentage();
            var heal = appliedAttackResult.AppliedDamage.ToHeal() * healPercentage;

            return new AttackFeedbackModel(
                appliedAttackResult.AttackerId,
                AttackDamageType.Heal,
                appliedAttackResult.AttackHitData,
                AttackPower.Zero,
                new AttackPowerParameter(AttackPowerParameterType.Fixed, heal.ToFloat()));
        }
    }
}
