using GLOW.Scenes.EnhanceQuestTop.Domain.Models;
using GLOW.Scenes.EnhanceQuestTop.Presentation.ViewModels;

namespace GLOW.Scenes.EnhanceQuestTop.Presentation.Translators
{
    public static class UpdatedEnhanceQuestTopViewModelTranslator
    {
        public static UpdatedEnhanceQuestTopViewModel ToViewModel(UpdateEnhanceQuestTopUseCaseModel model)
        {
            return new UpdatedEnhanceQuestTopViewModel(
                model.QuestPartyModel.PartyName,
                model.QuestPartyModel.TotalBonusPercentage
            );
        }
    }
}

