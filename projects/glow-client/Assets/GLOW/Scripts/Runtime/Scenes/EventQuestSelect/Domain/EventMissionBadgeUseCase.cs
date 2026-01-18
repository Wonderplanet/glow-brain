using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.EventQuestSelect.Domain
{
    public class EventMissionBadgeUseCase
    {
        [Inject] IGameRepository GameRepository { get; }

        public bool GetEventMissionBadge(MasterDataId mstEventId)
        {
            return !GameRepository.GetGameFetch().BadgeModel.UnreceivedMissionEventRewardCountById(mstEventId).IsZero();
        }
    }
}