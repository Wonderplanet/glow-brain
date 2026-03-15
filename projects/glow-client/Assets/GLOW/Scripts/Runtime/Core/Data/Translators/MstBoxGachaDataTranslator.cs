using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.BoxGacha;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Core.Data.Translators
{
    public static class MstBoxGachaDataTranslator
    {
        public static MstBoxGachaModel Translate(MstBoxGachaData boxGachaData, MstBoxGachaI18nData i18n)
        {
            var displayMstUnitId1 = string.IsNullOrEmpty(boxGachaData.DisplayMstUnitId1) 
                ? MasterDataId.Empty
                : new MasterDataId(boxGachaData.DisplayMstUnitId1);
            
            var displayMstUnitId2 = string.IsNullOrEmpty(boxGachaData.DisplayMstUnitId2) 
                ? MasterDataId.Empty
                : new MasterDataId(boxGachaData.DisplayMstUnitId2);
            
            return new MstBoxGachaModel(
                new MasterDataId(boxGachaData.Id),
                new MasterDataId(boxGachaData.MstEventId),
                new MasterDataId(boxGachaData.CostId),
                new CostAmount(boxGachaData.CostNum),
                boxGachaData.LoopType,
                new BoxGachaName(i18n.Name),
                new KomaBackgroundAssetKey(boxGachaData.AssetKey),
                displayMstUnitId1,
                displayMstUnitId2
            );
        }
    }
}