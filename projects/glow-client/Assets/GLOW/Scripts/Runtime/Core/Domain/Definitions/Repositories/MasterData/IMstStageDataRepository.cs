using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstStageDataRepository
    {
        MstStageModel GetMstStageFirstOrDefault(MasterDataId id);
        MstStageModel GetMstStage(MasterDataId id);
        IReadOnlyList<MstStageModel> GetMstStages();
        IReadOnlyList<MstStageModel> GetMstStagesFromMstQuestId(MasterDataId mstQuestId);
    }
}
