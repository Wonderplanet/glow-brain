using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.BoxGacha;

namespace GLOW.Core.Data.Translators
{
    public static class MstBoxGachaGroupDataTranslator
    {
        public static MstBoxGachaGroupModel Translate(
            MstBoxGachaGroupData boxGachaGroupData, 
            IReadOnlyList<MstBoxGachaPrizeData> boxGachaPrizeData)
        {
            var boxGachaPrizeModels = boxGachaPrizeData
                .Select(MstBoxGachaPrizeDataTranslator.Translate)
                .ToList();
            
            return new MstBoxGachaGroupModel(
                new MasterDataId(boxGachaGroupData.MstBoxGachaId),
                new BoxLevel(boxGachaGroupData.BoxLevel),
                boxGachaPrizeModels
            );
        }
    }
}