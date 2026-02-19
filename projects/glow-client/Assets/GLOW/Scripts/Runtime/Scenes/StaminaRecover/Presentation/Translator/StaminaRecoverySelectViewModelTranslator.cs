using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.StaminaRecover.Domain.UseCaseModel;
using GLOW.Scenes.StaminaRecover.Domain.ValueObject;
using GLOW.Scenes.StaminaRecover.Presentation.ViewModel;

namespace GLOW.Scenes.StaminaRecover.Presentation.Translator
{
    public class StaminaRecoverySelectViewModelTranslator
    {
        public static StaminaRecoverySelectViewModel Translate(
            StaminaShortageFlag isStaminaShortage,
            IReadOnlyList<StaminaRecoverySelectCellUseCaseModel> staminaRecoveryItems)
        {
            var staminaItemCellViewModels = staminaRecoveryItems
                .Select(StaminaListCellViewModelTranslator.Translate)
                .ToList();

            return new StaminaRecoverySelectViewModel(
                isStaminaShortage,
                staminaItemCellViewModels);
        }
    }
}
