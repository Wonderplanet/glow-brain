using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.StaminaRecover.Domain.UseCaseModel;
using GLOW.Scenes.StaminaRecover.Presentation.ViewModel;

namespace GLOW.Scenes.StaminaRecover.Presentation.Translator
{
    public class StaminaListCellViewModelTranslator
    {
        public static StaminaListCellViewModel Translate(StaminaRecoverySelectCellUseCaseModel useCaseModel)
        {
            return new StaminaListCellViewModel(
                useCaseModel.MstItemId,
                useCaseModel.Name,
                useCaseModel.StaminaEffectValue,
                useCaseModel.RequiredItemAmount,
                useCaseModel.AvailableStatus,
                useCaseModel.Availability,
                useCaseModel.RemainingTime,
                ItemIconAssetPath.FromAssetKey(useCaseModel.ItemAssetKey));
        }
    }
}
