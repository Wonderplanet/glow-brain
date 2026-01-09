using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Repositories
{
    public class QuestCacheRepository : IQuestCacheRepository
    {
        MasterDataId _mstStageId = MasterDataId.Empty;

        public void SetMainStageId(MasterDataId mstStageId)
        {
            _mstStageId = mstStageId;
        }

        public MasterDataId GetSelectedMainStageId()
        {
            return _mstStageId;
        }

        public void Clear()
        {
            _mstStageId = MasterDataId.Empty;
        }
    }
}
