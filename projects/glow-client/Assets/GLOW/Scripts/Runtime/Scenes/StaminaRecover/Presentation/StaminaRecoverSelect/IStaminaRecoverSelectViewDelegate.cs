using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.StaminaRecover.Presentation.StaminaRecoverSelect
{
    public interface IStaminaRecoverSelectViewDelegate
    {
        void OnViewDidLoad();
        void OnRecoverAtAd(Stamina recoverStamina);
        void OnRecoverAtDiamond();
        void OnClose();
    }
}
