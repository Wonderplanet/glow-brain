using GLOW.Core.Domain.Repositories;
using GLOW.Scenes.EnhanceQuestTop.Domain.Factories;
using GLOW.Scenes.EnhanceQuestTop.Domain.Models;
using Zenject;

namespace GLOW.Scenes.EnhanceQuestTop.Domain.UseCases
{
    public class UpdateEnhanceQuestTopUseCase
    {
        [Inject] IPartyCacheRepository PartyCacheRepository { get; }
        [Inject] IQuestPartyModelFactory QuestPartyModelFactory { get; }
        [Inject] IEnhanceQuestModelFactory EnhanceQuestModelFactory { get; }

        public UpdateEnhanceQuestTopUseCaseModel UpdateEnhanceQuestTop()
        {
            var currentParty = PartyCacheRepository.GetCurrentPartyModel();
            var enhanceQuestModel = EnhanceQuestModelFactory.CreateCurrentEnhanceQuestModel();
            var questPartyModel = QuestPartyModelFactory.Create(currentParty, enhanceQuestModel.MstQuest.Id);

            return new UpdateEnhanceQuestTopUseCaseModel(questPartyModel);
        }
    }
}

