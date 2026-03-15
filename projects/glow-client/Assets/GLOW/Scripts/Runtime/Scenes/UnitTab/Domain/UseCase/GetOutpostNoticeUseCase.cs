using System.Linq;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.UnitTab.Domain.UseCase
{
    public class GetOutpostNoticeUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IOutpostArtworkBadgeRepository OutpostArtworkBadgeRepository { get; }

        public NotificationBadge GetUnitNotification()
        {
            var userArtworks = GameRepository.GetGameFetchOther().UserArtworkModels;
            var displayedIds = OutpostArtworkBadgeRepository.DisplayedOutpostArtworkIds;

            var isNew = userArtworks
                .All(artwork => !displayedIds.Contains(artwork.MstArtworkId));

            return new NotificationBadge(isNew);
        }

    }
}
