using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.BoxGacha.Domain.Evaluator;
using GLOW.Scenes.BoxGacha.Domain.ValueObject;
using Zenject;

namespace GLOW.Scenes.EventQuestSelect.Domain
{
    public class EventQuestBadgeUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstBoxGachaDataRepository MstBoxGachaDataRepository { get; }
        [Inject] IBoxGachaDrawableEvaluator BoxGachaDrawableEvaluator { get; }

        public EventQuestSelectBadgeModel GetEventQuestSelectBadge(MasterDataId mstEventId)
        {
            var badgeModel = GameRepository.GetGameFetch().BadgeModel; 
            var isExistReceivableMission = !badgeModel.UnreceivedMissionEventRewardCountById(mstEventId).IsZero()
                ? NotificationBadge.True
                : NotificationBadge.False;
            
            var boxGachaModel = MstBoxGachaDataRepository.GetMstBoxGachaModelByMstEventIdFirstOrDefault(mstEventId);
            var isBoxGachaDrawable = boxGachaModel.IsEmpty() ? 
                BoxGachaDrawableFlag.False : 
                BoxGachaDrawableEvaluator.Evaluate(mstEventId);
            
            return new EventQuestSelectBadgeModel(
                isExistReceivableMission,
                isBoxGachaDrawable);
        }
    }
}