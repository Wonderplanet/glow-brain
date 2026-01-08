using System.Linq;
using GLOW.Core.Domain.Repositories;
using Zenject;

namespace GLOW.Scenes.OutpostEnhance.Domain.UseCases
{
    public class UpdateDisplayedArtworkUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IOutpostArtworkBadgeRepository OutpostArtworkBadgeRepository { get; }
        [Inject] IOutpostArtworkCacheRepository OutpostArtworkCacheRepository { get; }

        public void UpdateDisplayedArtwork()
        {
            var mstArtworkIds = GameRepository.GetGameFetchOther().UserArtworkModels
                .Select(artwork => artwork.MstArtworkId)
                .ToList();

            OutpostArtworkBadgeRepository.DisplayedOutpostArtworkIds = mstArtworkIds;
            var selectedArtworkId = OutpostArtworkCacheRepository.GetSelectedArtwork();

            OutpostArtworkCacheRepository.SetArtworkList(mstArtworkIds, selectedArtworkId);
        }
    }
}
