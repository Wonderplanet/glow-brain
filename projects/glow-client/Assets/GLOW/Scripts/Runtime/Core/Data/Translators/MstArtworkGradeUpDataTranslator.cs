using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Encyclopedia;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkEnhance.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public class MstArtworkGradeUpDataTranslator
    {
        public static MstArtworkGradeUpModel Translate(
            MstArtworkGradeUpData data,
            IReadOnlyList<MstArtworkGradeUpCostData> costDatas)
        {
            var costModels = costDatas
                .Select(cost =>
                {
                    return new ArtworkGradeUpCostModel(
                        new MasterDataId(cost.Id),
                        new MasterDataId(cost.ResourceId),
                        new ItemAmount(cost.ResourceAmount));
                })
                .ToList();

            return new MstArtworkGradeUpModel(
                new MasterDataId(data.Id),
                data.Rarity,
                new ArtworkGradeLevel(data.GradeLevel),
                new MasterDataId(data.MstSeriesId),
                new MasterDataId(data.MstArtworkId),
                costModels);
        }
    }
}
