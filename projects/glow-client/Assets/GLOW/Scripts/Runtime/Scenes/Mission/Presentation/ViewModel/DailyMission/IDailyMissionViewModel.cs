using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Scenes.Mission.Presentation.ViewModel.BonusPointMission;

namespace GLOW.Scenes.Mission.Presentation.ViewModel.DailyMission
{
    public interface IDailyMissionViewModel
    {
        public IBonusPointMissionViewModel BonusPointMissionViewModel { get; }
        public IReadOnlyList<IDailyMissionCellViewModel> DailyMissionCellViewModels { get; }
        public UnreceivedMissionRewardCount UnreceivedMissionRewardCount { get; }
        public bool IsReceivableRewardExist();
    }
}