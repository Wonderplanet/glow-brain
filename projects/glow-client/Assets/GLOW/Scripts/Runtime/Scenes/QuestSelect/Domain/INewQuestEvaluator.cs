using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Scenes.Home.Domain.ValueObjects;

namespace GLOW.Scenes.QuestSelect.Domain
{
    public interface INewQuestEvaluator
    {
        NewQuestFlag IsNewQuest(QuestOpenStatus questOpenStatus, MstQuestModel mstQuestModel);
        NewQuestFlag IsNewQuestAtEvent(QuestOpenStatus questOpenStatus, MstQuestModel mstQuestModel);
    }
}
