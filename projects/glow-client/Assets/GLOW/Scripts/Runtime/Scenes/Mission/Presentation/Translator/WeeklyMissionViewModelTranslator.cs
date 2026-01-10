using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Scenes.Mission.Domain.Model.WeeklyMission;
using GLOW.Scenes.Mission.Presentation.ViewModel.WeeklyMission;

namespace GLOW.Scenes.Mission.Presentation.Translator
{
    public class WeeklyMissionViewModelTranslator
    {
        public static IWeeklyMissionViewModel ToWeeklyMissionViewModel(MissionWeeklyResultModel missionWeeklyResultModel, RemainingTimeSpan nextUpdateDatetime)
        {
            var weeklyViewModelList = missionWeeklyResultModel.MissionWeeklyModels
                .Select(ToWeeklyMissionCellViewModel).ToList();
            var bonusPointMissionViewModel = BonusPointMissionViewModelTranslator.ToBonusPointMissionViewModel(
                missionWeeklyResultModel.BonusPointResultModel, nextUpdateDatetime);
            var unreceivedMissionRewardCount =
                new UnreceivedMissionRewardCount(weeklyViewModelList.Count(model =>
                    model.MissionStatus == MissionStatus.Receivable));

            return new WeeklyMissionViewModel(bonusPointMissionViewModel,
                weeklyViewModelList,
                unreceivedMissionRewardCount);
        }

        static IWeeklyMissionCellViewModel ToWeeklyMissionCellViewModel(MissionWeeklyCellModel missionWeeklyCellModel)
        {
            return new WeeklyMissionCellViewModel(
                missionWeeklyCellModel.MissionWeeklyId,
                missionWeeklyCellModel.MissionStatus,
                missionWeeklyCellModel.MissionProgress,
                missionWeeklyCellModel.CriterionCount,
                missionWeeklyCellModel.BonusPoint,
                missionWeeklyCellModel.MissionDescription,
                missionWeeklyCellModel.DestinationScene);
        }
    }
}
