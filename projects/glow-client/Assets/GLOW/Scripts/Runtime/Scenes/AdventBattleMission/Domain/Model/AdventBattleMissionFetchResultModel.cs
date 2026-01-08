using System.Collections.Generic;

namespace GLOW.Scenes.AdventBattleMission.Domain.Model
{
    public record AdventBattleMissionFetchResultModel(
        IReadOnlyList<AdventBattleMissionCellModel> AdventBattleMissionCellModels)
    {
        public static AdventBattleMissionFetchResultModel Empty { get; } = new(
            new List<AdventBattleMissionCellModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}