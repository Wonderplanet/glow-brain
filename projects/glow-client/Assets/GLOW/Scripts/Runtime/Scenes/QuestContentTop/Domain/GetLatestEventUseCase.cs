using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Extensions;
using Zenject;

namespace GLOW.Scenes.QuestContentTop.Domain
{
    public class GetLatestEventUseCase
    {
        [Inject] IMstEventDataRepository MstEventDataRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        public MstEventModel GetLatestMstEventModel()
        {
            var latestMstEventModel = MstEventDataRepository.GetEvents()
                .Where(m => CalculateTimeCalculator.IsValidTime(TimeProvider.Now, m.StartAt, m.EndAt))
                .MaxBy(m =>m.StartAt);

            return latestMstEventModel ?? MstEventModel.Empty;
        }
    }
}
