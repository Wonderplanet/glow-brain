using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Gacha;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public class GachaDrawResultDataTranslator
    {
        public static GachaDrawResultModel ToGachaDrawResultModel(GachaDrawResultData data)
        {
            var gachaResultModels = data.GachaResults
                .Select(ToGachaResultModel)
                .ToList();

            var userUnitModels = data.UsrUnits
                .Select(UserUnitDataTranslator.ToUserUnitModel)
                .ToList();

            var userItemModels = data.UsrItems
                .Select(ItemDataTranslator.ToUserItemModel)
                .ToList();

            var userGachaUpperModels = data.UsrGachaUppers
                .Select(UserGachaUpperDataTranslator.ToUserDrawCountThresholdModel)
                .ToList();

            GachaDrawResultModel gachaDrawResultModel = new GachaDrawResultModel(
                gachaResultModels,
                userUnitModels,
                userItemModels,
                UserParameterTranslator.ToUserParameterModel(data.UsrParameter),
                userGachaUpperModels,
                UserGachaDataTranslator.ToUserGachaModel(data.UsrGacha)
            );
            return gachaDrawResultModel;
        }

        static GachaResultModel ToGachaResultModel(GachaResultData data)
        {
            return new GachaResultModel(
                    data.Reward.ResourceType,
                    new MasterDataId(data.Reward.ResourceId),
                    new ObscuredPlayerResourceAmount(data.Reward.ResourceAmount),
                    PreConversionResourceModelTranslator.ToPreConversionResourceModel(data.Reward.PreConversionResource)
                    );
        }
    }
}
