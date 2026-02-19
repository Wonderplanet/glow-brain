using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Scenes.EventQuestSelect.Domain.Evaluator;
using Zenject;

namespace GLOW.Scenes.EventQuestSelect.Domain.Factory
{
    public class ReleaseRequiredMstQuestFactory : IReleaseRequiredMstQuestFactory
    {
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }
        [Inject] IQuestReleaseCheckSampleFinder QuestReleaseCheckSampleFinder { get; }

        MstQuestModel IReleaseRequiredMstQuestFactory.Create(MstQuestModel targetMstQuestModel)
        {
            var releaseTargetMstStage = QuestReleaseCheckSampleFinder.GetSampleAtMstStageModel(targetMstQuestModel);
            if(releaseTargetMstStage.ReleaseRequiredMstStageId.IsEmpty()) return MstQuestModel.Empty;

            var releaseRequiredMstStage = MstStageDataRepository.GetMstStage(releaseTargetMstStage.ReleaseRequiredMstStageId);
            return MstQuestDataRepository.GetMstQuestModel(releaseRequiredMstStage.MstQuestId);
        }
    }
}