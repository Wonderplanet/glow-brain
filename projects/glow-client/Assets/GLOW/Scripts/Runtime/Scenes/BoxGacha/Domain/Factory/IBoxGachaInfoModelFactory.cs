using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.BoxGacha.Domain.Model;

namespace GLOW.Scenes.BoxGacha.Domain.Factory
{
    public interface IBoxGachaInfoModelFactory
    {
        BoxGachaInfoModel Create(
            MasterDataId mstEventId,
            MasterDataId mstBoxGachaId,
            MasterDataId costItemId,
            CostAmount costAmount,
            UserBoxGachaModel userBoxGachaModel);
    }
}