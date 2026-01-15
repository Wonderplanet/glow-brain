using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.BoxGacha;

namespace GLOW.Core.Data.Translators
{
    public static class UserBoxGachaDataTranslator
    {
        public static UserBoxGachaModel ToUserBoxGachaModel(UsrBoxGachaData data)
        {
            var mstBoxGachaId = new MasterDataId(data.MstBoxGachaId);
            var resetCount = new BoxResetCount(data.ResetCount);
            var totalDrawCount = new BoxDrawCount(data.TotalDrawCount);
            var currentBoxLevel = new BoxLevel(data.CurrentBoxLevel);
            var userBoxGachaPrizeModels = data.DrawPrizes
                .Select(UserBoxGachaPrizeDataTranslator.ToUserBoxGachaPrizeModel)
                .ToList();

            return new UserBoxGachaModel(
                mstBoxGachaId,
                resetCount,
                totalDrawCount,
                currentBoxLevel,
                userBoxGachaPrizeModels);
        }
    }
}