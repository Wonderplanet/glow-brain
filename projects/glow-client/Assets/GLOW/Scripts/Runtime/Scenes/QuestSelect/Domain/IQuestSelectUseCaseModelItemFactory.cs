using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.QuestSelect.Domain
{
    public interface IQuestSelectUseCaseModelItemFactory
    {
        QuestSelectContentUseCaseModel CreateQuestSelectContentUseCaseModel(
            MstQuestModel targetMstQuestModel,
            MstStageModel releaseTargetMstStage);
    }
}
