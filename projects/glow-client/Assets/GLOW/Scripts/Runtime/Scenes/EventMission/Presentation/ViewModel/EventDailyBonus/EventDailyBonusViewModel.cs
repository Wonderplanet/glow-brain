using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Modules.CommonReceiveView.Presentation.ViewModel;

namespace GLOW.Scenes.EventMission.Presentation.ViewModel.EventDailyBonus
{
    public record EventDailyBonusViewModel(
        MasterDataId MstEventIdForTimeInformation,
        LoginDayCount ProgressLoginDayCount,
        IReadOnlyList<DailyBonusCollectionCellViewModel> EventMissionDailyBonusCellModels,
        IReadOnlyList<CommonReceiveResourceViewModel> ReceiveResourceRewardViewModels)
    {
        public static EventDailyBonusViewModel Empty { get; } = new(
            MasterDataId.Empty,
            LoginDayCount.Empty,
            new List<DailyBonusCollectionCellViewModel>(),
            new List<CommonReceiveResourceViewModel>());

        public DailyBonusCollectionCellViewModel GetReceivingAnimationPlayDayCell()
        {
            var receivingReward = EventMissionDailyBonusCellModels
                .FirstOrDefault(cell => cell.DailyBonusReceiveStatus == DailyBonusReceiveStatus.Receiving,
                    DailyBonusCollectionCellViewModel.Empty);
            return receivingReward;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    };
}
