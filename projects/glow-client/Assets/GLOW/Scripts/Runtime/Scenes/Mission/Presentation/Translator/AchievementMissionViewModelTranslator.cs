using System.Linq;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.Mission.Domain.Model.AchievementMission;
using GLOW.Scenes.Mission.Presentation.ViewModel.AchievementMission;

namespace GLOW.Scenes.Mission.Presentation.Translator
{
    public class AchievementMissionViewModelTranslator
    {
        public static IAchievementMissionViewModel ToAchievementMissionViewModel(MissionAchievementResultModel missionAchievementResultModel)
        {
            var achievementViewModelList = missionAchievementResultModel.AchievementCellModels
                .Select(ToAchievementMissionCellViewModel).ToList();
            var unreceivedMissionRewardCount = new UnreceivedMissionRewardCount(
                achievementViewModelList.Count(model => model.MissionStatus == MissionStatus.Receivable));
            return new AchievementMissionViewModel(achievementViewModelList, unreceivedMissionRewardCount);
        }
        
        static IAchievementMissionCellViewModel ToAchievementMissionCellViewModel(MissionAchievementCellModel missionAchievementCellModel)
        {
            return new AchievementMissionCellViewModel(
                missionAchievementCellModel.MissionAchievementId,
                missionAchievementCellModel.MissionStatus,
                missionAchievementCellModel.MissionProgress,
                missionAchievementCellModel.CriterionValue,
                missionAchievementCellModel.CriterionCount,
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModels(missionAchievementCellModel.PlayerResourceModels),
                missionAchievementCellModel.MissionDescription,
                missionAchievementCellModel.DestinationScene);
        }
    }
}