using System.Collections.Generic;
using GLOW.Scenes.StaminaRecover.Domain.ValueObject;

namespace GLOW.Scenes.StaminaRecover.Presentation.ViewModel
{
    public record StaminaRecoverySelectViewModel(
        StaminaShortageFlag IsStaminaShortage,
        IReadOnlyList<StaminaListCellViewModel> StaminaRecoveryItems);
}
