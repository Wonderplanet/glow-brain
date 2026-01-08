using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Scenes.Mission.Presentation.ViewModel.BonusPointMission;

namespace GLOW.Scenes.Mission.Presentation.ViewModel.DailyMission
{
    public class DailyMissionViewModel : IDailyMissionViewModel
    {
        public IBonusPointMissionViewModel BonusPointMissionViewModel { get; }
        public IReadOnlyList<IDailyMissionCellViewModel> DailyMissionCellViewModels { get; }
        public UnreceivedMissionRewardCount UnreceivedMissionRewardCount { get; }

        public DailyMissionViewModel(IBonusPointMissionViewModel bonusPointMissionViewModel, IReadOnlyList<IDailyMissionCellViewModel> dailyMissionCellViewModels, UnreceivedMissionRewardCount unreceivedMissionRewardCount)
        {
            BonusPointMissionViewModel = bonusPointMissionViewModel;
            DailyMissionCellViewModels = dailyMissionCellViewModels;
            UnreceivedMissionRewardCount = unreceivedMissionRewardCount;
        }
        
        public bool IsReceivableRewardExist()
        {
            return !UnreceivedMissionRewardCount.IsZero() || !BonusPointMissionViewModel.UnreceivedMissionRewardCount.IsZero();
        }
    }
}