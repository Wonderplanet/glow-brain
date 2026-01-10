using System.Collections.Generic;
using GLOW.Scenes.Mission.Domain.Model.BonusPointMission;

namespace GLOW.Scenes.BeginnerMission.Domain.Model
{
    public record MissionBeginnerResultModel(
        MissionBonusPointResultModel BonusPointResultModel,
        IReadOnlyList<MissionBeginnerCellModel> MissionBeginnerModel)
    {
        public static MissionBeginnerResultModel Empty { get; } = new(MissionBonusPointResultModel.Empty, new List<MissionBeginnerCellModel>());
        public bool IsEmpty() => ReferenceEquals(this, Empty);
    };
}
