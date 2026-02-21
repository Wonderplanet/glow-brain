using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Scenes.EventMission.Presentation.ViewModel.EventMissionCell;

namespace GLOW.Scenes.EventMission.Presentation.ViewModel.EventAchievementMission
{
    public record EventAchievementMissionViewModel(
        MasterDataId MstEventIdForTimeInformation,
        IReadOnlyList<MasterDataId> TargetMstEventIds,
        IReadOnlyList<IEventMissionCellViewModel> EventAchievementMissionCellViewModels,
        UnreceivedMissionRewardCount UnreceivedMissionRewardCount)
    {
        public bool IsReceivableRewardExist()
        {
            return !UnreceivedMissionRewardCount.IsZero();
        }
    };
}
