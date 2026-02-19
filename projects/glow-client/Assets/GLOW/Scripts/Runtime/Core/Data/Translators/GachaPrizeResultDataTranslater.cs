using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Gacha;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public class GachaPrizeResultDataTranslator
    {
        public static GachaPrizeResultModel ToGachaDrawResultModel(GachaPrizeResultData data)
        {
            GachaPrizeResultModel gachaDrawResultModel = new GachaPrizeResultModel(
                ToNormalGachaPrizePageModel(data),
                ToFixedProbabilityGachaPrizePageModel(data),
                ToUpperProbabilityGachaPrizePageModel(data),
                ToPickupGachaPrizePageModel(data)
            );
            return gachaDrawResultModel;
        }

        // 通常:排出枠
        static GachaPrizePageModel ToNormalGachaPrizePageModel(GachaPrizeResultData data)
        {
            return new GachaPrizePageModel(
                data.RarityProbabilities.Select(ToGachaRarityProbabilityModel).ToArray(),
                data.ProbabilityGroups.Select(ToGachaProbabilityGroupModel).ToArray()
            );
        }

        // 通常:確定枠
        static GachaPrizePageModel ToFixedProbabilityGachaPrizePageModel(GachaPrizeResultData data)
        {
            return new GachaPrizePageModel(
                data.FixedProbabilities.RarityProbabilities.Select(ToGachaRarityProbabilityModel).ToArray(),
                data.FixedProbabilities.ProbabilityGroups.Select(ToGachaProbabilityGroupModel).ToArray()
            );
        }

        // 天井:最高レアリティ枠
        static GachaPrizePageModel ToUpperProbabilityGachaPrizePageModel(GachaPrizeResultData data)
        {
            if (data.UpperProbabilities.Length == 0 || data.UpperProbabilities.All(upper => upper.UpperType != UpperType.MaxRarity)) return GachaPrizePageModel.Empty;

            var maxRarity = data.UpperProbabilities.First(upper => upper.UpperType == UpperType.MaxRarity);
            return new GachaPrizePageModel(
                maxRarity.RarityProbabilities.Select(ToGachaRarityProbabilityModel).ToArray(),
                maxRarity.ProbabilityGroups.Select(ToGachaProbabilityGroupModel).ToArray()
            );
        }

        // 天井:ピックアップ枠
        static GachaPrizePageModel ToPickupGachaPrizePageModel(GachaPrizeResultData data)
        {
            if (data.UpperProbabilities.Length == 0 || data.UpperProbabilities.All(upper => upper.UpperType != UpperType.Pickup)) return GachaPrizePageModel.Empty;

            var pickup = data.UpperProbabilities.First(upper => upper.UpperType == UpperType.Pickup);
            return new GachaPrizePageModel(
                pickup.RarityProbabilities.Select(ToGachaRarityProbabilityModel).ToArray(),
                pickup.ProbabilityGroups.Select(ToGachaProbabilityGroupModel).ToArray()
            );
        }

        static GachaRarityProbabilityModel ToGachaRarityProbabilityModel(RarityProbabilityData data)
        {
            return new GachaRarityProbabilityModel(
                    data.Rarity,
                    data.Probability
                    );
        }

        static GachaProbabilityGroupModel ToGachaProbabilityGroupModel(ProbabilityGroupData data)
        {
            return new GachaProbabilityGroupModel(
                data.Rarity,
                data.Prizes.Select(ToGachaPrizeModel).ToList()
            );
        }

        static GachaPrizeModel ToGachaPrizeModel(GachaPrizeData data)
        {
            return new GachaPrizeModel(
                data.ResourceType,
                new MasterDataId(data.ResourceId),
                data.ResourceAmount,
                data.Probability,
                data.IsPickup
            );
        }
    }
}
