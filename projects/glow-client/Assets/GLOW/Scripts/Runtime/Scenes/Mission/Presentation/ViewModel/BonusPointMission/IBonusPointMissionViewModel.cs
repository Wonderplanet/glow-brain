using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Scenes.Mission.Presentation.ViewModel.BonusPointMission
{
    public interface IBonusPointMissionViewModel
    {
        public BonusPoint BonusPoint { get; }
        public IReadOnlyList<IBonusPointMissionCellViewModel> BonusPointMissionCellViewModels { get; }
        public RemainingTimeSpan NextUpdateDatetime { get; }
        public UnreceivedMissionRewardCount UnreceivedMissionRewardCount { get; }
    }
}
