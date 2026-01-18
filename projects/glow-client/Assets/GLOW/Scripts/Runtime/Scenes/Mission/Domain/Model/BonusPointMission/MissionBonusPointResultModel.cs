using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Scenes.Mission.Domain.Model.BonusPointMission
{
    public record MissionBonusPointResultModel(
        MissionType MissionType,
        BonusPoint BonusPoint,
        IReadOnlyList<MissionBonusPointCellModel> BonusPointCellModels)
    {
        public static MissionBonusPointResultModel Empty { get; } = new(MissionType.Achievement, BonusPoint.Empty, new List<MissionBonusPointCellModel>());
        public bool IsEmpty() => ReferenceEquals(this, Empty);

        public bool IsBonusPointMissionAllReceived()
        {
            return BonusPointCellModels.All(cellModel => cellModel.MissionStatus == MissionStatus.Received);
        }
    }
}
