using GLOW.Core.Domain.Models;

namespace GLOW.Scenes.EventQuestSelect.Domain.Evaluator
{
    public interface IQuestReleaseCheckSampleFinder
    {
        MstStageModel GetSampleAtMstStageModel(MstQuestModel mstQuest);
    }
}
