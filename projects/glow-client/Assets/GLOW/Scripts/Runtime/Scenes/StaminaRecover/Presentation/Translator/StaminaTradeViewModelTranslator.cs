using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.StaminaRecover.Domain.UseCaseModel;
using GLOW.Scenes.StaminaRecover.Presentation.ViewModel;

namespace GLOW.Scenes.StaminaRecover.Presentation.Translator
{
    public class StaminaTradeViewModelTranslator
    {
        public static StaminaTradeViewModel Translate(StaminaTradeUseCaseModel useCaseModel)
        {
            var playerResourceIconViewModel =
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(useCaseModel.ItemIconModel);

            return new StaminaTradeViewModel(
                useCaseModel.MstItemId,
                useCaseModel.Name,
                useCaseModel.EffectValue,
                useCaseModel.CurrentUserStamina,
                useCaseModel.MaxPurchasableCount,
                playerResourceIconViewModel,
                useCaseModel.MaxStamina);
        }
    }
}
