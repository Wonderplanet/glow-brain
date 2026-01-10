using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.IdleIncentive;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Scenes.PassShop.Domain.Model;

namespace GLOW.Scenes.IdleIncentiveQuickReward.Domain.Moels
{
    public record IdleIncentiveQuickReceiveWindowModel(
        IdleIncentiveRemainCount AdCount,
        IdleIncentiveRemainCount ConsumeItemCount,
        ItemAmount RequireDiamondAmount,
        EnoughItem IsEnoughRequireItem,
        TimeSpan QuickRewardTimeSpan,
        HeldAdSkipPassInfoModel HeldAdSkipPassInfoModel);
}
