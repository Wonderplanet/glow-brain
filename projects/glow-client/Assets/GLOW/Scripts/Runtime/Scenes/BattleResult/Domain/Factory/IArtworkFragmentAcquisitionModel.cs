using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.ExchangeShop;
using GLOW.Scenes.ArtworkFragmentAcquisition.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.BattleResult.Domain.Factory
{
    public interface IArtworkFragmentAcquisitionModelFactory
    {
        IReadOnlyList<ArtworkFragmentAcquisitionModel> CreateArtworkFragmentAcquisitionModels(
            StageEndResultModel stageEndResultModel,
            IReadOnlyList<UserArtworkFragmentModel> beforeUserArtworkFragmentModels);

        ArtworkFragmentAcquisitionModel CreateArtworkFragmentAcquisitionModelFromExchangeResultModel(
            ExchangeTradeResultModel exchangeTradeResultModel,
            ExchangeRewardModel exchangeRewardModel,
            IReadOnlyList<UserArtworkFragmentModel> beforeUserArtworkFragments);
    }
}
