using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.StaminaRecover.Domain.ValueObject;
using GLOW.Scenes.StaminaRecover.Presentation.ViewModel;

namespace GLOW.Scenes.StaminaRecover.Presentation.StaminaRecoverySelect
{
    public interface IStaminaRecoverySelectViewDelegate
    {
        void OnViewDidLoad();
        void OnUseButtonTapped(
            MasterDataId mstItemId,
            StaminaRecoveryAvailableStatus availableStatus,
            Stamina staminaEffectValue);
        void OnUpdateStaminaResetTime(
            StaminaListCell cell,
            StaminaRecoveryAvailableStatus availableStatus,
            RemainingTimeSpan remainingTime,
            StaminaRecoveryAvailability availability);
        void OnClose();
    }
}
