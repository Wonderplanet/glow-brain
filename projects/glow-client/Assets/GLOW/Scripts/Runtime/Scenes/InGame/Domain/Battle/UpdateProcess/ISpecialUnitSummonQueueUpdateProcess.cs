using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public interface ISpecialUnitSummonQueueUpdateProcess
    {
        SpecialUnitSummonQueueUpdateProcessResult UpdateSummonQueue(
            IReadOnlyList<SpecialUnitModel> units,
            IReadOnlyList<MasterDataId> usedSpecialUnitIdsBeforeNextRush,
            SpecialUnitSummonQueueModel summonQueueModel,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            MstPageModel page);
    }
}
