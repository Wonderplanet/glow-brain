using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Scenes.Mission.Domain.Model.DailyMission;
using GLOW.Scenes.Mission.Presentation.ViewModel.DailyMission;

namespace GLOW.Scenes.Mission.Presentation.Translator
{
    public class DailyMissionViewModelTranslator
    {
        public static IDailyMissionViewModel ToDailyMissionViewModel(MissionDailyResultModel missionDailyResultModel, RemainingTimeSpan nextUpdateDatetime)
        {
            var dailyViewModelList = missionDailyResultModel.MissionDailyModels
                .Select(ToDailyMissionCellViewModel).ToList();
            var bonusPointMissionViewModel = BonusPointMissionViewModelTranslator.ToBonusPointMissionViewModel(
                missionDailyResultModel.BonusPointResultModel, nextUpdateDatetime);
            var unreceivedMissionRewardCount =
                new UnreceivedMissionRewardCount(dailyViewModelList.Count(model =>
                    model.MissionStatus == MissionStatus.Receivable));

            return new DailyMissionViewModel(bonusPointMissionViewModel,
                dailyViewModelList,
                unreceivedMissionRewardCount);
        }

        static IDailyMissionCellViewModel ToDailyMissionCellViewModel(MissionDailyCellModel missionDailyCellModel)
        {
            return new DailyMissionCellViewModel(
                missionDailyCellModel.MissionDailyId,
                missionDailyCellModel.MissionStatus,
                missionDailyCellModel.MissionProgress,
                missionDailyCellModel.CriterionCount,
                missionDailyCellModel.BonusPoint,
                missionDailyCellModel.MissionDescription,
                missionDailyCellModel.DestinationScene);
        }
    }
}
