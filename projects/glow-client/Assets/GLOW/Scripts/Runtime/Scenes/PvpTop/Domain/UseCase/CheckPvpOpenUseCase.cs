using GLOW.Scenes.QuestContentTop.Domain.Factory;
using GLOW.Scenes.QuestContentTop.Domain.UseCaseModel;
using Zenject;

namespace GLOW.Scenes.PvpTop.Domain
{
    public class CheckPvpOpenUseCase
    {
        [Inject] IPvpQuestContentOpeningStatusModelFactory PvpQuestContentOpeningStatusModelFactory { get; }

        public QuestContentOpeningStatusModel GetModel()
        {
            return PvpQuestContentOpeningStatusModelFactory.Create();
        }
    }
}
