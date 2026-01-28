using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public record SpecialUnitSummonQueueUpdateProcessResult(
        IReadOnlyList<SpecialUnitModel> SummonedSpecialUnits,
        IReadOnlyList<SpecialUnitModel> UpdatedSpecialUnits,
        IReadOnlyList<MasterDataId> UpdatedUsedSpecialUnitIdsBeforeNextRush,
        SpecialUnitSummonQueueModel UpdatedSummonQueueModel)
    {
        public static SpecialUnitSummonQueueUpdateProcessResult Empty { get; } = new(
            new List<SpecialUnitModel>(),
            new List<SpecialUnitModel>(),
            new List<MasterDataId>(),
            SpecialUnitSummonQueueModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
