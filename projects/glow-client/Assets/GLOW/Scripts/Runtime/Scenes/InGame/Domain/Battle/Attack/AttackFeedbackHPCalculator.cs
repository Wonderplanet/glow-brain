using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class AttackFeedbackHPCalculator : IAttackFeedbackHPCalculator
    {
        record HealCalculationResult(
            Heal Heal,
            HP UpdatedHp,
            IReadOnlyList<AttackFeedbackHPCalculatorResultDetailModel> ResultDetails);

        static readonly IReadOnlyList<PercentageM> EmptyPercentages = new List<PercentageM>();

        public AttackFeedbackHPCalculatorResultModel CalculateHp(
            IReadOnlyList<AttackFeedbackModel> attackFeedbacks,
            FieldObjectId attackerId,
            HP currentHp,
            HP maxHp,
            HealInvalidationFlag isHealInvalidation)
        {
            var healResult = CalculateTotalHeal(
                attackFeedbacks,
                attackerId,
                currentHp,
                maxHp,
                isHealInvalidation);

            return new AttackFeedbackHPCalculatorResultModel(
                healResult.UpdatedHp,
                healResult.Heal,
                healResult.ResultDetails);
        }

        HealCalculationResult CalculateTotalHeal(
            IReadOnlyList<AttackFeedbackModel> attackFeedbacks,
            FieldObjectId attackerId,
            HP currentHp,
            HP maxHp,
            HealInvalidationFlag isHealInvalidation)
        {
            var details = new List<AttackFeedbackHPCalculatorResultDetailModel>();
            var totalHeal = Heal.Zero;
            var updatedHp = currentHp;

            foreach (var feedback in attackFeedbacks)
            {
                if (feedback.AttackerId != attackerId) continue;
                if (feedback.AttackDamageType != AttackDamageType.Heal) continue;

                var power = AttackPowerCalculator.CalculateAttackPower(
                    feedback.BasePower,
                    feedback.PowerParameter,
                    EmptyPercentages,
                    EmptyPercentages,
                    maxHp);

                var heal = isHealInvalidation || updatedHp.IsZero() ? Heal.Zero : power.ToHeal();
                totalHeal += heal;

                var hpDiff = maxHp - updatedHp;
                var appliedHeal = Heal.Min(heal, hpDiff.ToHeal());

                var prevUpdatedHp = updatedHp;
                updatedHp += appliedHeal;

                var detail = new AttackFeedbackHPCalculatorResultDetailModel(
                    feedback,
                    heal,
                    appliedHeal,
                    prevUpdatedHp,
                    updatedHp);

                details.Add(detail);
            }

            return new HealCalculationResult(totalHeal, updatedHp, details);
        }
    }
}
