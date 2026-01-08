using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Event;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstQuestDataRepository
    {
        IReadOnlyList<MstQuestModel> GetMstQuestModels();
        IReadOnlyList<MstQuestModel> GetMstQuestModelsFromEvent(MasterDataId mstEventId);
        IReadOnlyList<MstQuestModel> GetMstQuestModelsByQuestGroup(MasterDataId mstQuestGroupId);
        MstQuestModel GetMstQuestModel(MasterDataId id);
        MstQuestModel GetMstQuestModelFirstOrDefault(MasterDataId id);
        IReadOnlyList<MstEventDisplayUnitModel> GetEventDisplayUnits();
    }
}
