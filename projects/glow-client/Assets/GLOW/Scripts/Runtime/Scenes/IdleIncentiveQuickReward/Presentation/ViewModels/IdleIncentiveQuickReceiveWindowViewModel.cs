using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.IdleIncentive;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Scenes.IdleIncentiveTop.Presentation.ViewModels;
using GLOW.Scenes.PassShop.Presentation.ViewModel;

namespace GLOW.Scenes.IdleIncentiveQuickReward.Presentation.ViewModels
{
    public record IdleIncentiveQuickReceiveWindowViewModel(
        IdleIncentiveRemainCount AdCount,
        IdleIncentiveRemainCount ConsumeItemCount,
        ItemAmount RequireItemAmount,
        EnoughItem IsEnoughRequireItem,
        IdleIncentiveRewardListViewModel RewardList,
        TimeSpan QuickRewardTimeSpan,
        HeldAdSkipPassInfoViewModel HeldAdSkipPassInfoViewModel);
}
