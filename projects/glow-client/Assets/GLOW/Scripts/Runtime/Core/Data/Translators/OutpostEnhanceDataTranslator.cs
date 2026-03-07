using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.OutpostEnhance;

namespace GLOW.Core.Data.Translators
{
    public class OutpostEnhanceDataTranslator
    {
        public static MstOutpostModel ToOutpostModel(MstOutpostData outpostsData, IReadOnlyList<(MstOutpostEnhancementData data, MstOutpostEnhancementI18nData i18n)> enhanceDataList, IReadOnlyList<(MstOutpostEnhancementLevelData data, MstOutpostEnhancementLevelI18nData i18n)> levelDataList)
        {
            var enhancementModels = enhanceDataList.Select(enhanceData =>
            {
                var levels = levelDataList.Where(levelData => levelData.data.MstOutpostEnhancementId == enhanceData.data.Id).Select(levelData => new MstOutpostEnhancementLevelModel(
                    new MasterDataId(levelData.data.Id),
                    new MasterDataId(levelData.data.MstOutpostEnhancementId),
                    new OutpostEnhanceLevel(levelData.data.Level),
                    new Coin(levelData.data.CostCoin),
                    new OutpostEnhanceValue((decimal)levelData.data.EnhancementValue),
                    new OutpostEnhanceDescription(levelData.i18n.Description))).ToList();

                return new MstOutpostEnhancementModel(
                    new MasterDataId(enhanceData.data.Id),
                    new MasterDataId(enhanceData.data.MstOutpostId),
                    enhanceData.data.OutpostEnhancementType,
                    new OutpostEnhanceIconAssetKey(enhanceData.data.AssetKey),
                    new OutpostEnhanceName(enhanceData.i18n.Name),
                    levels);
            }).ToList();

            return new MstOutpostModel(
                new MasterDataId(outpostsData.Id),
                new OutpostImageAssetKey(outpostsData.AssetKey),
                enhancementModels,
                outpostsData.StartAt,
                outpostsData.EndAt);
        }
    }
}
