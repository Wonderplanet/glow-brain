using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Scenes.Mission.Presentation.ViewModel.BonusPointMission;

namespace GLOW.Scenes.Mission.Presentation.ViewModel.WeeklyMission
{
    public interface IWeeklyMissionViewModel
    {
        public IBonusPointMissionViewModel BonusPointMissionViewModel { get; }
        public IReadOnlyList<IWeeklyMissionCellViewModel> WeeklyMissionCellViewModels { get; }
        public UnreceivedMissionRewardCount UnreceivedMissionRewardCount { get; }
        public bool IsReceivableRewardExist();
    }
}