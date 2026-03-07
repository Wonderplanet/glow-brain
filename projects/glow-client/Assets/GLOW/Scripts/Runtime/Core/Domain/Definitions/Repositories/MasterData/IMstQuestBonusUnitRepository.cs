using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstQuestBonusUnitRepository
    {
        IReadOnlyList<MstQuestBonusUnitModel> GetQuestBonusUnits(MasterDataId mstQuestId);
    }
}
