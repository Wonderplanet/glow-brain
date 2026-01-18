using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Scenes.Mission.Presentation.ViewModel.AchievementMission
{
    public class AchievementMissionViewModel : IAchievementMissionViewModel
    {
        public IReadOnlyList<IAchievementMissionCellViewModel> AchievementMissionCellViewModels { get; }
        public UnreceivedMissionRewardCount UnreceivedMissionRewardCount { get; }

        public AchievementMissionViewModel(IReadOnlyList<IAchievementMissionCellViewModel> achievementMissionCellViewModels, UnreceivedMissionRewardCount unreceivedMissionRewardCount)
        {
            AchievementMissionCellViewModels = achievementMissionCellViewModels;
            UnreceivedMissionRewardCount = unreceivedMissionRewardCount;
        }
        
        public bool IsReceivableRewardExist()
        {
            return !UnreceivedMissionRewardCount.IsZero();
        }
    }
}