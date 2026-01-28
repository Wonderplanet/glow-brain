using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.BoxGacha;

namespace GLOW.Core.Data.Translators
{
    public static class MstBoxGachaPrizeDataTranslator
    {
        public static MstBoxGachaPrizeModel Translate(MstBoxGachaPrizeData boxGachaPrizeData)
        {
            return new MstBoxGachaPrizeModel(
                new MasterDataId(boxGachaPrizeData.Id),
                new BoxGachaGroupId(boxGachaPrizeData.MstBoxGachaGroupId),
                new PickUpFlag(boxGachaPrizeData.IsPickup),
                boxGachaPrizeData.ResourceType,
                new MasterDataId(boxGachaPrizeData.ResourceId),
                new ObscuredPlayerResourceAmount(boxGachaPrizeData.ResourceAmount),
                new BoxGachaPrizeStock(boxGachaPrizeData.Stock)
            );
        }
    }
}