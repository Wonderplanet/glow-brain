using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.IdleIncentive;
using GLOW.Scenes.IdleIncentiveTop.Domain.ValueObjects;
using GLOW.Scenes.PassShop.Domain.Model;

namespace GLOW.Scenes.IdleIncentiveTop.Domain.Models
{
    public record IdleIncentiveTopModel(
        IdleIncentiveRewardAmount OneHourCoinReward,
        IdleIncentiveRewardAmount PassEffectCoinReward,
        IdleIncentiveRewardAmount OneHourExpReward,
        IdleIncentiveRewardAmount PassEffectExpReward,
        EnableQuickReceiveFlag EnableQuickReward,
        TimeSpan MaxIdleHour,
        TimeSpan IdleIntervalMinute,
        IReadOnlyList<HeldPassEffectDisplayModel> PassEffectDisplayModels);
}
