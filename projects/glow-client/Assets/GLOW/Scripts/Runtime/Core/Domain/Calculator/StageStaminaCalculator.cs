using GLOW.Core.Domain.ValueObjects.Campaign;
using GLOW.Core.Domain.ValueObjects.Stage;
using UnityEngine;

namespace GLOW.Core.Domain.Calculator
{
    public class StageStaminaCalculator
    {
        public static StageConsumeStamina CalcConsumeStaminaInCampaign(
            StageConsumeStamina consumeStamina,
            CampaignEffectValue campaignEffectValue)
        {
            // 消費量0ならそのまま
            if (consumeStamina.IsZero())
            {
                return consumeStamina;
            }

            const float baseValue = 100;
            var multiplier = campaignEffectValue.Value / baseValue;
            // 少数切り捨て
            var result = (int)(consumeStamina.Value * multiplier);
            result = Mathf.Max(result, 1);
            return new StageConsumeStamina(result);
        }
    }
}
