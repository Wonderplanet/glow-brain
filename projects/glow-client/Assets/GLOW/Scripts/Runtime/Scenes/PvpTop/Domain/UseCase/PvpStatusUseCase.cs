using GLOW.Scenes.PvpTop.Domain.Model;
using GLOW.Scenes.QuestContentTop.Domain.Factory;
using GLOW.Scenes.QuestContentTop.Domain.UseCaseModel;
using Zenject;

namespace GLOW.Scenes.PvpTop.Domain
{
    public class PvpStatusUseCase
    {
        [Inject] IPvpQuestContentOpeningStatusModelFactory PvpQuestContentOpeningStatusModelFactory { get; }
        [Inject] IContentNoticePvpFactory ContentNoticePvpFactory { get; }

        public HomeMainPvpStatusUseCaseModel GetModel()
        {
            return new HomeMainPvpStatusUseCaseModel(
                PvpQuestContentOpeningStatusModelFactory.Create(),
                ContentNoticePvpFactory.Create()
            );
        }
    }
}
