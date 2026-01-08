using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Extensions;
using Zenject;

namespace GLOW.Scenes.EventQuestSelect.Domain.Evaluator
{
    public class QuestReleaseCheckSampleFinder : IQuestReleaseCheckSampleFinder
    {
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }

        MstStageModel IQuestReleaseCheckSampleFinder.GetSampleAtMstStageModel(MstQuestModel mstQuest)
        {
            var mstStageModel =  MstStageDataRepository.GetMstStages()
                .Where(s =>
                    s.MstQuestId == mstQuest.Id
                    && s.StartAt <= TimeProvider.Now
                    && TimeProvider.Now <= s.EndAt)
                .MinBy(s => s.StageNumber.Value);

            return mstStageModel ?? MstStageModel.Empty;
        }
    }
}
