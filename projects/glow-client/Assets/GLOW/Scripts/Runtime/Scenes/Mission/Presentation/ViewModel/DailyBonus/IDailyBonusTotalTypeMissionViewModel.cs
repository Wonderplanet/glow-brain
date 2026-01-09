using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Scenes.Mission.Presentation.ViewModel.DailyBonus
{
    public interface IDailyBonusTotalTypeMissionViewModel
    {
        public LoginDayCount CurrentLoginDayCountProgress { get; }
        public IReadOnlyList<IDailyBonusTotalTypeMissionCellViewModel> DailyBonusTotalTypeMissionCellViewModel { get; }
        public RemainingTimeSpan NextUpdateDatetime { get; }
        public UnreceivedMissionRewardCount UnreceivedMissionRewardCount { get; }
    }
}
