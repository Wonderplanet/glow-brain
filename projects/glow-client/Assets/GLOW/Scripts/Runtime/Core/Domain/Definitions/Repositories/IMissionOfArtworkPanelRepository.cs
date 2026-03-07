using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMissionOfArtworkPanelRepository
    {
        void SetUserMissionLimitedTermModels(IReadOnlyList<UserMissionLimitedTermModel> userMissionLimitedTermModels);
        IReadOnlyList<UserMissionLimitedTermModel> GetUserMissionLimitedTermModels();
        void SaveReceivedArtworkPanelInfo(
            IReadOnlyList<UserArtworkModel> receivedUserArtworkModels,
            IReadOnlyList<UserArtworkFragmentModel> receivedUserArtworkFragmentModels,
            IReadOnlyList<UserArtworkModel> beforeUserArtworkModels,
            IReadOnlyList<UserArtworkFragmentModel> beforeUserArtworkFragmentModels);
        IReadOnlyList<UserArtworkModel> GetReceivedUserArtworkModels();
        IReadOnlyList<UserArtworkFragmentModel> GetAcquiredUserArtworkFragmentModels();
        IReadOnlyList<UserArtworkModel> GetBeforeUserArtworkModels();
        IReadOnlyList<UserArtworkFragmentModel> GetBeforeUserArtworkFragmentModels();
        void ClearReceivedArtworkPanelInfo();
    }
}
