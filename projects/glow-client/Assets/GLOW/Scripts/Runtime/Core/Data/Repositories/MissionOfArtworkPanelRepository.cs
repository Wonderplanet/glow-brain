using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Core.Data.Repositories
{
    public class MissionOfArtworkPanelRepository : IMissionOfArtworkPanelRepository
    {
        // ミッションの情報
        IReadOnlyList<UserMissionLimitedTermModel> _userMissionLimitedTermModels;

        IReadOnlyList<UserArtworkModel> _receivedUserArtworkModels = new List<UserArtworkModel>();
        IReadOnlyList<UserArtworkFragmentModel> _receivedUserArtworkFragmentModels = new List<UserArtworkFragmentModel>();
        IReadOnlyList<UserArtworkModel> _beforeUserArtworkModels = new List<UserArtworkModel>();
        IReadOnlyList<UserArtworkFragmentModel> _beforeUserArtworkFragmentModels = new List<UserArtworkFragmentModel>();

        void IMissionOfArtworkPanelRepository.SetUserMissionLimitedTermModels(IReadOnlyList<UserMissionLimitedTermModel> userMissionLimitedTermModels)
        {
            _userMissionLimitedTermModels = userMissionLimitedTermModels;
        }

        IReadOnlyList<UserMissionLimitedTermModel> IMissionOfArtworkPanelRepository.GetUserMissionLimitedTermModels()
        {
            return _userMissionLimitedTermModels;
        }

        void IMissionOfArtworkPanelRepository.SaveReceivedArtworkPanelInfo(
            IReadOnlyList<UserArtworkModel> receivedUserArtworkModels,
            IReadOnlyList<UserArtworkFragmentModel> receivedUserArtworkFragmentModels,
            IReadOnlyList<UserArtworkModel> beforeUserArtworkModels,
            IReadOnlyList<UserArtworkFragmentModel> beforeUserArtworkFragmentModels)
        {
            _receivedUserArtworkModels = receivedUserArtworkModels;
            _receivedUserArtworkFragmentModels = receivedUserArtworkFragmentModels;
            _beforeUserArtworkModels = beforeUserArtworkModels;
            _beforeUserArtworkFragmentModels = beforeUserArtworkFragmentModels;
        }

        IReadOnlyList<UserArtworkModel> IMissionOfArtworkPanelRepository.GetReceivedUserArtworkModels()
        {
            return _receivedUserArtworkModels;
        }

        IReadOnlyList<UserArtworkFragmentModel> IMissionOfArtworkPanelRepository.GetAcquiredUserArtworkFragmentModels()
        {
            return _receivedUserArtworkFragmentModels;
        }

        IReadOnlyList<UserArtworkModel> IMissionOfArtworkPanelRepository.GetBeforeUserArtworkModels()
        {
            return _beforeUserArtworkModels;
        }

        IReadOnlyList<UserArtworkFragmentModel> IMissionOfArtworkPanelRepository.GetBeforeUserArtworkFragmentModels()
        {
            return _beforeUserArtworkFragmentModels;
        }

        void IMissionOfArtworkPanelRepository.ClearReceivedArtworkPanelInfo()
        {
            _receivedUserArtworkModels = new List<UserArtworkModel>();
            _receivedUserArtworkFragmentModels = new List<UserArtworkFragmentModel>();
            _beforeUserArtworkModels = new List<UserArtworkModel>();
            _beforeUserArtworkFragmentModels = new List<UserArtworkFragmentModel>();
        }
    }
}
