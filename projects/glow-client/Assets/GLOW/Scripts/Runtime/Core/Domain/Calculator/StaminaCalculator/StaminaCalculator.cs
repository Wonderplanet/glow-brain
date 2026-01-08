using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.StaminaRecover.Domain.ValueObject;
using UnityEngine;

namespace GLOW.Core.Domain.Calculator
{
    public class StaminaCalculator
    {
        public static RemainStaminaRecoverSecond CalcStaminaFullRecoverySecond(
            DateTimeOffset? staminaUpdateAt,
            DateTimeOffset nowTime,
            Stamina currentStamina,
            Stamina maxStamina,
            RecoveryStaminaMinutes recoveryStaminaMinutes)
        {
            if (!staminaUpdateAt.HasValue)
            {
                return RemainStaminaRecoverSecond.Empty;
            }
            
            if(currentStamina >= maxStamina)
            {
                return RemainStaminaRecoverSecond.Empty;
            }

            // 自然回復中のスタミナは省く
            var remainStamina = maxStamina - currentStamina;
            var recoverySeconds = recoveryStaminaMinutes.ToRemainStaminaRecoverSecond().Value;
            var fullRecoveryDate = staminaUpdateAt.Value.AddSeconds(remainStamina.Value * recoverySeconds);
            var staminaFullRecoverySeconds = Mathf.Max((int)fullRecoveryDate.Subtract(nowTime).TotalSeconds, 0);
            
            return new RemainStaminaRecoverSecond(staminaFullRecoverySeconds);
        }
        
        public static StaminaCalculatorResult CalcStaminaInfo(
            DateTimeOffset? staminaUpdateAt,
            DateTimeOffset nowTime,
            Stamina currentStamina,
            Stamina maxStamina,
            RecoveryStaminaMinutes recoveryStaminaMinutes)
        {
            // NOTE: 自然回復が許容されているかどうかは
            //       UserParameterModelのStaminaがMaxStaminaRecovery以下であるかどうかで判断する
            var allowTimeBasedStaminaRecovery = currentStamina < maxStamina;

            var calculatedStamina = CalcCurrentStamina(
                staminaUpdateAt, 
                nowTime, 
                currentStamina, 
                maxStamina, 
                recoveryStaminaMinutes);
            
            var calculatedSecond = CalcUpdateRemainSecond(staminaUpdateAt, nowTime, recoveryStaminaMinutes);

            // NOTE: 自然回復が許容されていない場合は
            //       残り時間を0にしてスタミナはUserParameterModelを返すようにする
            if (!allowTimeBasedStaminaRecovery)
            {
                calculatedStamina = currentStamina;
                calculatedSecond = RemainStaminaRecoverSecond.Empty;
            }
            else
            {
                // NOTE: 時間経過回復をした際に自然回復の最大値を上回っている場合は自然回復の最大値で上書きする
                if (calculatedStamina > maxStamina)
                {
                    calculatedStamina = maxStamina;
                    calculatedSecond = RemainStaminaRecoverSecond.Empty;
                }
            }

            return new StaminaCalculatorResult(
                calculatedStamina, 
                calculatedSecond);
        }

        static RemainStaminaRecoverSecond CalcUpdateRemainSecond(
            DateTimeOffset? staminaUpdatedAt, 
            DateTimeOffset nowTime, 
            RecoveryStaminaMinutes recoveryStaminaMinutes)
        {
            if (!staminaUpdatedAt.HasValue)
            {
                return RemainStaminaRecoverSecond.Empty;
            }
            
            int remainSecond =  recoveryStaminaMinutes.ToRemainStaminaRecoverSecond().Value - 
                                (int)(nowTime - staminaUpdatedAt.Value).TotalSeconds % 
                                recoveryStaminaMinutes.ToRemainStaminaRecoverSecond().Value;

            return new RemainStaminaRecoverSecond(remainSecond);
        }

        static Stamina CalcCurrentStamina(
            DateTimeOffset? staminaUpdatedAt, 
            DateTimeOffset nowTime, 
            Stamina  currentStamina, 
            Stamina maxStamina, 
            RecoveryStaminaMinutes recoveryStaminaMinutes)
        {
            if (!staminaUpdatedAt.HasValue)
            {
                return Stamina.Empty;
            }

            var totalSecond = (int)(nowTime - staminaUpdatedAt.Value).TotalSeconds;
            var updatedStamina = currentStamina + totalSecond / recoveryStaminaMinutes.ToRemainStaminaRecoverSecond().Value;
            return Stamina.Min(updatedStamina, maxStamina);
        }
    }
}
