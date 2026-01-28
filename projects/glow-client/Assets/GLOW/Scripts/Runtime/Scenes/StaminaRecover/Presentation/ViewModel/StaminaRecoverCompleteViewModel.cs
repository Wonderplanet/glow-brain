using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.StaminaRecover.Presentation.ViewModel
{
    public record StaminaRecoverCompleteViewModel(
        string HeaderTitle,
        string Description,
        Stamina AdvRecoverStaminaValue);
}
