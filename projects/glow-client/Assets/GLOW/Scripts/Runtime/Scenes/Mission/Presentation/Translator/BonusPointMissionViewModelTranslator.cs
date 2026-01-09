using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.Mission.Domain.Model.BonusPointMission;
using GLOW.Scenes.Mission.Presentation.ViewModel.BonusPointMission;

namespace GLOW.Scenes.Mission.Presentation.Translator
{
    public class BonusPointMissionViewModelTranslator
    {
        public static IBonusPointMissionViewModel ToBonusPointMissionViewModel(
            MissionBonusPointResultModel missionBonusPointResultModel, RemainingTimeSpan nextUpdateDatetime)
        {
            var bonusPointRewardViewModelList = missionBonusPointResultModel.BonusPointCellModels
                .Select(ToBonusPointMissionCellViewModel).ToList();
            var unreceivedMissionRewardCount =
                new UnreceivedMissionRewardCount(bonusPointRewardViewModelList.Count(model =>
                    model.MissionStatus == MissionStatus.Receivable));

            return new BonusPointMissionViewModel(missionBonusPointResultModel.BonusPoint,
                bonusPointRewardViewModelList,
                nextUpdateDatetime,
                unreceivedMissionRewardCount);
        }

        static IBonusPointMissionCellViewModel ToBonusPointMissionCellViewModel(MissionBonusPointCellModel missionBonusPointCellModel)
        {
            return new BonusPointMissionCellViewModel(
                missionBonusPointCellModel.MissionBonusPointId,
                missionBonusPointCellModel.MissionStatus,
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModels(missionBonusPointCellModel.BonusPointRewardModels),
                missionBonusPointCellModel.CriterionCount);
        }

    }
}
