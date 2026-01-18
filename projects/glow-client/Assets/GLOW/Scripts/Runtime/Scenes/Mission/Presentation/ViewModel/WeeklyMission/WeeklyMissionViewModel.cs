using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Scenes.Mission.Presentation.ViewModel.BonusPointMission;

namespace GLOW.Scenes.Mission.Presentation.ViewModel.WeeklyMission
{
    public class WeeklyMissionViewModel : IWeeklyMissionViewModel
    {
        public IBonusPointMissionViewModel BonusPointMissionViewModel { get; }
        public IReadOnlyList<IWeeklyMissionCellViewModel> WeeklyMissionCellViewModels { get; }
        public UnreceivedMissionRewardCount UnreceivedMissionRewardCount { get; }
        public WeeklyMissionViewModel(IBonusPointMissionViewModel bonusPointMissionViewModel, IReadOnlyList<IWeeklyMissionCellViewModel> weeklyMissionCellViewModels, UnreceivedMissionRewardCount unreceivedMissionRewardCount)
        {
            BonusPointMissionViewModel = bonusPointMissionViewModel;
            WeeklyMissionCellViewModels = weeklyMissionCellViewModels;
            UnreceivedMissionRewardCount = unreceivedMissionRewardCount;
        }

        public bool IsReceivableRewardExist()
        {
            return !UnreceivedMissionRewardCount.IsZero() || !BonusPointMissionViewModel.UnreceivedMissionRewardCount.IsZero();
        }
    }
}