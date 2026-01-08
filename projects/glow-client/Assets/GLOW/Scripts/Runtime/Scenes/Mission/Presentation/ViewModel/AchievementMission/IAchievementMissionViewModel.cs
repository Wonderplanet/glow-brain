using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Scenes.Mission.Presentation.ViewModel.AchievementMission
{
    public interface IAchievementMissionViewModel
    {
        public IReadOnlyList<IAchievementMissionCellViewModel> AchievementMissionCellViewModels { get; }
        public UnreceivedMissionRewardCount UnreceivedMissionRewardCount { get; }
        public bool IsReceivableRewardExist();
    }
}