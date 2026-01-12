using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Scenes.Mission.Presentation.ViewModel.BonusPointMission
{
    public class BonusPointMissionViewModel : IBonusPointMissionViewModel
    {
        public BonusPoint BonusPoint { get; }
        public IReadOnlyList<IBonusPointMissionCellViewModel> BonusPointMissionCellViewModels { get; }
        public RemainingTimeSpan NextUpdateDatetime { get; }
        public UnreceivedMissionRewardCount UnreceivedMissionRewardCount { get; }

        public BonusPointMissionViewModel(BonusPoint bonusPoint, IReadOnlyList<IBonusPointMissionCellViewModel> bonusPointMissionCellViewModels, RemainingTimeSpan nextUpdateDatetime, UnreceivedMissionRewardCount unreceivedMissionRewardCount)
        {
            BonusPoint = bonusPoint;
            BonusPointMissionCellViewModels = bonusPointMissionCellViewModels;
            NextUpdateDatetime = nextUpdateDatetime;
            UnreceivedMissionRewardCount = unreceivedMissionRewardCount;
        }
    }
}
