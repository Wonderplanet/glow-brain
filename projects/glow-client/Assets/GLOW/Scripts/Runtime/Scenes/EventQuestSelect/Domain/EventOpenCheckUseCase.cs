using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.EventQuestSelect.Domain
{
    public class EventOpenCheckUseCase
    {
        [Inject] IMstEventDataRepository MstEventDataRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        public bool IsOpenEvent(MasterDataId mstEventId)
        {
            var mstEvent = MstEventDataRepository.GetEventFirstOrDefault(mstEventId);
            if (mstEvent.IsEmpty()) return false;

            return CalculateTimeCalculator.IsValidTime(
                TimeProvider.Now,
                mstEvent.StartAt,
                mstEvent.EndAt);
        }
    }
}
