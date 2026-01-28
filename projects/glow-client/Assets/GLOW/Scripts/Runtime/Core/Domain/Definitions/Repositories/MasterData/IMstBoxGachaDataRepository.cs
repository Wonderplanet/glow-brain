using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.BoxGacha;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstBoxGachaDataRepository
    {
        MstBoxGachaModel GetMstBoxGachaModelByMstEventIdFirstOrDefault(MasterDataId mstEventId);
        MstBoxGachaModel GetMstBoxGachaModelFirstOrDefault(MasterDataId mstBoxGachaId);
        MstBoxGachaGroupModel GetMstBoxGachaGroupModelFirstOrDefault(
            MasterDataId mstBoxGachaId, 
            BoxLevel boxLevel);
    }
}