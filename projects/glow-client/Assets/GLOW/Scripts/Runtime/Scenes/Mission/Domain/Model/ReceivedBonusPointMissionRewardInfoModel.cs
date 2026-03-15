using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Modules.CommonReceiveView.Domain.Model;

namespace GLOW.Scenes.Mission.Domain.Model
{
    public record ReceivedBonusPointMissionRewardInfoModel(
        BonusPoint BeforeBonusPoint,
        BonusPoint AfterBonusPoint,
        BonusPoint MaxBonusPoint,
        IReadOnlyList<CommonReceiveResourceModel> CommonReceiveResourceModels,
        IReadOnlyList<BonusPoint> ReceivedRewardBonusPoints)
    {
        public static ReceivedBonusPointMissionRewardInfoModel Empty { get; } = new(
            BonusPoint.Empty,
            BonusPoint.Empty,
            BonusPoint.Empty,
            new List<CommonReceiveResourceModel>(),
            new List<BonusPoint>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
