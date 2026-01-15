using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.Mission.Domain.Model.DailyBonusMission;
using GLOW.Scenes.Mission.Presentation.ViewModel.DailyBonus;

namespace GLOW.Scenes.Mission.Presentation.Translator
{
    public class DailyBonusMissionViewModelTranslator
    {
        public static IDailyBonusMissionViewModel ToDailyBonusMissionViewModel(MissionDailyBonusResultModel missionDailyBonusResultModel, RemainingTimeSpan nextUpdateDatetime)
        {
            var dailyBonusViewModelList = missionDailyBonusResultModel.MissionDailyBonusCellModels
                .Select(ToDailyBonusMissionCellViewModel).ToList();
            var unreceivedMissionRewardCount =
                new UnreceivedMissionRewardCount(dailyBonusViewModelList.Count(model =>
                    model.MissionStatus == MissionStatus.Receivable));

            return new DailyBonusMissionViewModel(
                dailyBonusViewModelList,
                unreceivedMissionRewardCount);
        }

        static IDailyBonusMissionCellViewModel ToDailyBonusMissionCellViewModel(MissionDailyBonusCellModel missionDailyBonusCellModel)
        {
            return new DailyBonusMissionCellViewModel(
                missionDailyBonusCellModel.MissionDailyBonusId,
                missionDailyBonusCellModel.MissionStatus,
                missionDailyBonusCellModel.LoginDayCount,
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModels(missionDailyBonusCellModel.PlayerResourceModels));
        }
    }
}
