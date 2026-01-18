using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.ExchangeShop;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Core.Data.Translators
{
    public class ExchangeTradeResultDataTranslator
    {
        public static ExchangeTradeResultModel Translate(ExchangeTradeResultData data)
        {
            return new ExchangeTradeResultModel(
                UserParameterTranslator.ToUserParameterModel(data.UsrParameter),
                data.UsrItems?.Select(ItemDataTranslator.ToUserItemModel).ToList() ?? new List<UserItemModel>(),
                data.UsrEmblems?.Select(UserEmblemDataTranslator.ToUserEmblemModel).ToList() ?? new List<UserEmblemModel>(),
                data.UsrUnits?.Select(UserUnitDataTranslator.ToUserUnitModel).ToList() ?? new List<UserUnitModel>(),
                data.UsrArtworks?.Select(UserArtworkDataTranslator.ToUserArtworkModel).ToList() ?? new List<UserArtworkModel>(),
                data.UsrArtworkFragments?.Select(UserArtworkFragmentDataTranslator.ToUserArtworkFragmentModel).ToList() ?? new List<UserArtworkFragmentModel>(),
                data.UsrExchangeLineups?.Select(UsrExchangeLineupDataTranslator.Translate).ToList() ?? new List<UserExchangeLineupModel>(),
                data.ExchangeRewards?.Select(ExchangeRewardDataTranslator.Translate).ToList() ?? new List<ExchangeRewardModel>());
        }
    }
}
