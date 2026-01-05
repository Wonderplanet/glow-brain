using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Scenes.Mission.Domain.Model.BonusPointMission
{
    public record MissionBonusPointCellModel(
        MasterDataId MissionBonusPointId,
        MissionType MissionType,
        MissionStatus MissionStatus,
        CriterionCount CriterionCount,
        IReadOnlyList<PlayerResourceModel> BonusPointRewardModels)
    {
        public static MissionBonusPointCellModel Empty { get; } =
            new(MasterDataId.Empty,
                MissionType.Achievement,
                MissionStatus.Receivable,
                CriterionCount.Empty,
                new List<PlayerResourceModel>()
                );
        public bool IsEmpty() => ReferenceEquals(this, Empty);
    };
}
