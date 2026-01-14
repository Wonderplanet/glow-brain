using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Scenes.Mission.Presentation.ViewModel.DailyBonus
{
    public class DailyBonusMissionViewModel : IDailyBonusMissionViewModel
    {
        public IReadOnlyList<IDailyBonusMissionCellViewModel> DailyBonusMissionCellViewModels { get; }
        public UnreceivedMissionRewardCount UnreceivedMissionRewardCount { get; }

        public DailyBonusMissionViewModel(
            IReadOnlyList<IDailyBonusMissionCellViewModel> dailyBonusMissionCellViewModels, 
            UnreceivedMissionRewardCount unreceivedMissionRewardCount)
        {
            DailyBonusMissionCellViewModels = dailyBonusMissionCellViewModels;
            UnreceivedMissionRewardCount = unreceivedMissionRewardCount;
        }

        public bool IsReceivableRewardExist()
        {
            return !UnreceivedMissionRewardCount.IsZero();
        }
    }
}
