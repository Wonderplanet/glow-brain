using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.Translators;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.EventMission.Domain.Model;
using GLOW.Scenes.EventMission.Presentation.ViewModel.EventAchievementMission;
using GLOW.Scenes.EventMission.Presentation.ViewModel.EventDailyBonus;
using GLOW.Scenes.EventMission.Presentation.ViewModel.EventMissionCell;
using GLOW.Scenes.EventMission.Presentation.ViewModel.EventMissionMain;

namespace GLOW.Scenes.EventMission.Presentation.Tranalator
{
    public class EventMissionViewModelTranslator
    {
        public static EventMissionMainViewModel ToEventMissionMainViewModel(EventMissionFetchResultModel model)
        {
            return new EventMissionMainViewModel(
                ToEventAchievementMissionViewModel(model.MstEventIdForTimeInformation, model.AchievementResultModel),
                ToEventDailyBonusViewModel(model.MstEventIdForTimeInformation, model.DailyBonusResultModel));
        }

        public static EventAchievementMissionViewModel ToEventAchievementMissionViewModel(
            MasterDataId mstEventIdForTimeInformation,
            EventMissionAchievementResultModel model)
        {
            var cellViewModels = model.OpeningEventAchievementCellModels
                .Select(ToEventMissionCellViewModel)
                .ToList();
            var unreceivedMissionRewardCount = new UnreceivedMissionRewardCount(
                cellViewModels.Count(cell => cell.MissionStatus == MissionStatus.Receivable));
            return new EventAchievementMissionViewModel(
                mstEventIdForTimeInformation,
                model.OpeningMstEventModels.Select(m => m.Id).ToList(),
                cellViewModels,
                unreceivedMissionRewardCount);
        }

        public static EventDailyBonusViewModel ToEventDailyBonusViewModel(
            MasterDataId mstEventIdForTimeInformation,
            EventMissionDailyBonusResultModel model)
        {
            if (model.IsEmpty())
            {
                return EventDailyBonusViewModel.Empty;
            }

            var cellViewModels = model.EventMissionDailyBonusCellModels
                .Select(ToEventDailyBonusCellViewModel)
                .ToList();

            var receiveRewardViewModel = model.CommonReceiveResourceModels
                .Select(m =>
                    CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(m))
                .ToList();
            return new EventDailyBonusViewModel(
                mstEventIdForTimeInformation,
                model.ProgressLoginDayCount,
                cellViewModels,
                receiveRewardViewModel);
        }

        static IEventMissionCellViewModel ToEventMissionCellViewModel(EventMissionCellModel cellModel)
        {
            return new EventMissionCellViewModel(
                cellModel.EventMissionId,
                cellModel.EventId,
                cellModel.MissionStatus,
                cellModel.MissionProgress,
                cellModel.CriterionCount,
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModels(cellModel.PlayerResourceModels),
                cellModel.MissionDescription,
                cellModel.DestinationScene);
        }

        static DailyBonusCollectionCellViewModel ToEventDailyBonusCellViewModel(EventMissionDailyBonusCellModel cellModel)
        {
            return new DailyBonusCollectionCellViewModel(
                cellModel.DailyBonusReceiveStatus,
                cellModel.LoginDayCount,
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(cellModel.CommonReceiveResourceModel.PlayerResourceModel),
                cellModel.SortOrder);
        }
    }
}
