using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.Quest;

namespace GLOW.Scenes.EventQuestSelect.Domain.Evaluator
{
    public interface IQuestOpenStatusEvaluator
    {
        QuestOpenStatus Evaluate(MstQuestModel targetMstQuestModel);
        IReadOnlyList<QuestOpenStatus> EvaluateGetAllStatus(MstQuestModel targetMstQuestModel);
    }
}
