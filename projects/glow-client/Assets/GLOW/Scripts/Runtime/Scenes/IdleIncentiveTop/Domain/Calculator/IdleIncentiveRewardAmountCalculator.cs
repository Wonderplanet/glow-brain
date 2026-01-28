using System;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Core.Domain.ValueObjects.IdleIncentive;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.IdleIncentiveTop.Domain.Calculator
{
    public class IdleIncentiveRewardAmountCalculator : IIdleIncentiveRewardAmountCalculator
    {
        public IdleIncentiveRewardAmount CalculateRewardAmountPerHour(
            IdleIncentiveRewardAmount baseAmount, 
            TimeSpan intervalMinute)
        {
            if (baseAmount.IsEmpty())
            {
                return IdleIncentiveRewardAmount.Empty;
            }
            
            var amount = baseAmount.Value * 60 / intervalMinute.TotalMinutes;
            return new IdleIncentiveRewardAmount((float)amount);
        }
        
        public IdleIncentiveRewardAmount CalculateRewardAmount(
            IdleIncentiveRewardAmount amount, 
            PassEffectValue passEffectValue)
        {
            if (amount.IsEmpty())
            {
                return IdleIncentiveRewardAmount.Empty;
            }
            
            if (passEffectValue.IsEmpty())
            {
                return amount;
            }
            
            return amount * passEffectValue;
        }
        
        public PlayerResourceAmount CalculatePlayerResourceAmount(
            IdleIncentiveRewardAmount baseAmount,
            TimeSpan elapsedTime,
            TimeSpan intervalMinute,
            PassEffectValue passEffectValue)
        {
            if (baseAmount.IsEmpty())
            {
                return PlayerResourceAmount.Empty;
            }
            
            var tickCount = (int)(Math.Floor(elapsedTime.TotalMinutes) / Math.Floor(intervalMinute.TotalMinutes));
            var rewardAmount = baseAmount.Value * tickCount;
            
            if (passEffectValue.IsEmpty())
            {
                return new PlayerResourceAmount((int)rewardAmount);
            }
            
            var finalAmount = (int)(rewardAmount * passEffectValue.Value);
            return new PlayerResourceAmount(finalAmount);
        }
    }
}