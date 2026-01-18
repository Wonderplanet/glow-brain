using System;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Core.Domain.ValueObjects.IdleIncentive;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.IdleIncentiveTop.Domain.Calculator
{
    public interface IIdleIncentiveRewardAmountCalculator
    {
        IdleIncentiveRewardAmount CalculateRewardAmountPerHour(
            IdleIncentiveRewardAmount baseAmount, 
            TimeSpan intervalMinute);
        
        IdleIncentiveRewardAmount CalculateRewardAmount(
            IdleIncentiveRewardAmount amount, 
            PassEffectValue passEffectValue);
        
        PlayerResourceAmount CalculatePlayerResourceAmount(
            IdleIncentiveRewardAmount baseAmount,
            TimeSpan elapsedTime,
            TimeSpan intervalMinute,
            PassEffectValue passEffectValue);
    }
}