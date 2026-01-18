using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.BoxGacha;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Core.Data.Translators
{
    public static class MstBoxGachaDataTranslator
    {
        public static MstBoxGachaModel Translate(MstBoxGachaData boxGachaData, MstBoxGachaI18nData i18n)
        {
            return new MstBoxGachaModel(
                new MasterDataId(boxGachaData.Id),
                new MasterDataId(boxGachaData.MstEventId),
                new MasterDataId(boxGachaData.CostId),
                new CostAmount(boxGachaData.CostNum),
                boxGachaData.LoopType,
                new BoxGachaName(i18n.Name),
                new KomaBackgroundAssetKey(boxGachaData.AssetKey),
                new MasterDataId(boxGachaData.DisplayMstUnitId1),
                new MasterDataId(boxGachaData.DisplayMstUnitId2)
            );
        }
    }
}