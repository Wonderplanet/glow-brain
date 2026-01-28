using System.Linq;
using GLOW.Core.Domain.Repositories;
using Zenject;

namespace GLOW.Scenes.OutpostEnhance.Domain.UseCases
{
    public class InitializeOutpostArtworkCacheUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IOutpostArtworkCacheRepository OutpostArtworkCacheRepository { get; }
        [Inject] IOutpostArtworkBadgeRepository OutpostArtworkBadgeRepository { get; }

        public void InitializeOutpostArtworkCache()
        {
            var gameFetchOther = GameRepository.GetGameFetchOther();
            var userOutpost = gameFetchOther.UserOutpostModels.First(outpost => outpost.IsUsed);
            var displayedOutpostArtworkIds = OutpostArtworkBadgeRepository.DisplayedOutpostArtworkIds;

            OutpostArtworkCacheRepository.SetArtworkList(displayedOutpostArtworkIds, userOutpost.MstArtworkId);
        }
    }
}
