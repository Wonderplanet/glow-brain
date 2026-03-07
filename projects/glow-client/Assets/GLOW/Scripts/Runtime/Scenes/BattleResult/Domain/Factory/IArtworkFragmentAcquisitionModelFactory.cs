using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.ExchangeShop;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFragmentAcquisition.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.BattleResult.Domain.Factory
{
    public interface IArtworkFragmentAcquisitionModelFactory
    {
        IReadOnlyList<ArtworkFragmentAcquisitionModel> CreateArtworkFragmentAcquisitionModels(
            IReadOnlyList<UserArtworkModel> acquiredUserArtworkModels,
            IReadOnlyList<UserArtworkFragmentModel> acquiredUserArtworkFragmentModels,
            IReadOnlyList<UserArtworkModel> beforeUserArtworkModels,
            IReadOnlyList<UserArtworkFragmentModel> beforeUserArtworkFragmentModels);

        ArtworkFragmentAcquisitionModel CreateArtworkFragmentAcquisitionModel(
            IReadOnlyList<UserArtworkModel> acquiredArtworks,
            MasterDataId acquisitionRewardModelResourceId,
            IReadOnlyList<UserArtworkModel> beforeUserArtworkModels,
            IReadOnlyList<UserArtworkFragmentModel> beforeUserArtworkFragmentModels);
    }
}
