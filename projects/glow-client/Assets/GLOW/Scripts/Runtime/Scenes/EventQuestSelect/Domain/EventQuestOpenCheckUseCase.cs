using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using Zenject;

namespace GLOW.Scenes.EventQuestSelect.Domain
{
    public class EventQuestOpenCheckUseCase
    {
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        public bool IsOpenEventQuest(MasterDataId mstQuestGroupId)
        {
            var mstQuest = MstQuestDataRepository.GetMstQuestModelsByQuestGroup(mstQuestGroupId)
                .FirstOrDefault(MstQuestModel.Empty);

            if (mstQuest.IsEmpty()) return false;

            return CalculateTimeCalculator.IsValidTime(
                TimeProvider.Now,
                mstQuest.StartDate,
                mstQuest.EndDate);
        }
    }
}
