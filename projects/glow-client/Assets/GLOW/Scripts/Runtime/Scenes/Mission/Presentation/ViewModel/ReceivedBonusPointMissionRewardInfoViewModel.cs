using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Modules.CommonReceiveView.Presentation.ViewModel;

namespace GLOW.Scenes.Mission.Presentation.ViewModel
{
    public record ReceivedBonusPointMissionRewardInfoViewModel(
        BonusPoint BeforeBonusPoint,
        BonusPoint UpdatedBonusPoint,
        BonusPoint MaxBonusPoint,
        IReadOnlyList<CommonReceiveResourceViewModel> ReceivedBonusPointMissionRewards,
        IReadOnlyList<BonusPoint> ReceivedRewardBonusPoints)
    {
        public static ReceivedBonusPointMissionRewardInfoViewModel Empty { get; } = new(
            BonusPoint.Empty,
            BonusPoint.Empty,
            BonusPoint.Empty,
            new List<CommonReceiveResourceViewModel>(),
            new List<BonusPoint>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
