using System.Collections.Generic;
using GLOW.Scenes.QuestContentTop.Domain.UseCaseModel;

namespace GLOW.Scenes.QuestContentTop.Domain.Factory
{
    public interface IQuestContentTopPvpModelFactory
    {
        IReadOnlyList<QuestContentTopElementUseCaseModel> CreatePvpQuestContentTopElementUseCaseModels();
    }
}
