using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.StaminaRecover.Presentation.ViewModel
{
    public record StaminaRecoverAdvertisementConfirmViewModel(
        string HeaderTitle,
        string Description,
        Stamina AdvRecoverStaminaValue);
}
