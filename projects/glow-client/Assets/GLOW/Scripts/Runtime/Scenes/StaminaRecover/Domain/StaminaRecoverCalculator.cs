using System;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.StaminaRecover;
using GLOW.Scenes.StaminaRecover.Domain.ValueObject;
using UnityEngine;

namespace GLOW.Scenes.StaminaRecover.Domain
{
    public class StaminaRecoverCalculator
    {
        public static Stamina CalcAdRecoverValue(
            StaminaRecoverPercentage adRecoverPercentage, 
            Stamina maxStamina)
        {
            float percentage = adRecoverPercentage.Value / 100f;
            int result = Mathf.FloorToInt(maxStamina.Value * percentage);

            return new Stamina(result);
        }
        public static Stamina CalcDiamondRecoverValue(
            StaminaRecoverPercentage diamondRecoverPercentage, 
            Stamina maxStamina)
        {
            float percentage = diamondRecoverPercentage.Value / 100f;
            int result = Mathf.FloorToInt(maxStamina.Value * percentage);

            return new Stamina(result);
        }

        public static StaminaRecoveryFlag CalcIsAdRecover(
            GameFetchModel gameFetchModel, 
            StaminaCalculatorResult currentStamina,
            Stamina maxStamina,
            RecoveryStaminaMinutes recoveryStaminaMinutes, 
            BuyStaminaAdCount maxDailyBuyStaminaAdCount, 
            DateTimeOffset now)
        {
            //現在スタミナ上限
            var isShortStamina = currentStamina.CurrentStamina.Value < maxStamina.Value;

            //時間経過
            var remainingTimeSpan = CalcReceivableTime(
                gameFetchModel, 
                maxStamina,
                recoveryStaminaMinutes, 
                maxDailyBuyStaminaAdCount, 
                now);

            var isRefreshed = remainingTimeSpan.IsEmpty() || remainingTimeSpan.IsZero();

            //利用上限
            var hasUseableCount = maxDailyBuyStaminaAdCount > gameFetchModel.UserBuyCountModel.DailyBuyStaminaAdCount;

            return new StaminaRecoveryFlag(isShortStamina  && isRefreshed && hasUseableCount);
        }

        public static RemainingTimeSpan CalcReceivableTime(
            GameFetchModel gameFetchModel,
            Stamina maxStamina,
            RecoveryStaminaMinutes recoveryStaminaMinutes, 
            BuyStaminaAdCount maxDailyBuyStaminaAdCount, 
            DateTimeOffset now)
        {
            var isStaminaMax = gameFetchModel.UserParameterModel.CurrentStamina >= maxStamina;
            if (isStaminaMax) return RemainingTimeSpan.Empty;
            
            var hasUsableCount = maxDailyBuyStaminaAdCount > gameFetchModel.UserBuyCountModel.DailyBuyStaminaAdCount;
            if (!hasUsableCount) return RemainingTimeSpan.Empty;

            var intervalTimeSpan = recoveryStaminaMinutes.ToTimeSpan();

            var adRecoverableTime = gameFetchModel.UserBuyCountModel.DailyBuyStaminaAdAt;

            if (adRecoverableTime == null) return RemainingTimeSpan.Empty;

            return now - adRecoverableTime.Value < intervalTimeSpan
                ? new RemainingTimeSpan(intervalTimeSpan - (now - adRecoverableTime.Value))
                : RemainingTimeSpan.Empty;
        }
    }
}
