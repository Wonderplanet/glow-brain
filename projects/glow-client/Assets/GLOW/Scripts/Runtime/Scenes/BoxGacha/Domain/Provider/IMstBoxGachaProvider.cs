using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.BoxGacha.Domain.Provider
{
    public interface IMstBoxGachaProvider
    {
        MstBoxGachaModel GetMstBoxGachaModelByEventId(MasterDataId mstEventId);
    }
}