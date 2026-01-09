using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Core.Domain.Models.ExchangeShop
{
    public record ExchangeTradeResultModel(
        UserParameterModel UserParameter,
        IReadOnlyList<UserItemModel> UserItems,
        IReadOnlyList<UserEmblemModel> UserEmblems,
        IReadOnlyList<UserUnitModel> UserUnits,
        IReadOnlyList<UserArtworkModel> UserArtworks,
        IReadOnlyList<UserArtworkFragmentModel> UserArtworkFragments,
        IReadOnlyList<UserExchangeLineupModel> UserExchangeLineups,
        IReadOnlyList<ExchangeRewardModel> ExchangeRewards)
    {
        public static ExchangeTradeResultModel Empty { get; } = new ExchangeTradeResultModel(
            UserParameterModel.Empty,
            new List<UserItemModel>(),
            new List<UserEmblemModel>(),
            new List<UserUnitModel>(),
            new List<UserArtworkModel>(),
            new List<UserArtworkFragmentModel>(),
            new List<UserExchangeLineupModel>(),
            new List<ExchangeRewardModel>());
    }
}
