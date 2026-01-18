using System.Collections.Generic;
using GLOW.Core.Domain.Models;

namespace GLOW.Scenes.QuestSelect.Domain
{
    public interface IQuestDifficultyUseCaseModelItemFactory
    {
        IReadOnlyList<QuestSelectDifficultyUseCaseModel> CreateDifficultyItems(MstQuestModel targetMstQuestModel);
    }
}