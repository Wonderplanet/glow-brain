using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IQuestCacheRepository
    {
        public void SetMainStageId(MasterDataId mstStageId);
        public MasterDataId GetSelectedMainStageId();
        public void Clear();
    }
}
