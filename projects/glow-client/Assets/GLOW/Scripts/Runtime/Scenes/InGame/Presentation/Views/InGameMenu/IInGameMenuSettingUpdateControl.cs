using GLOW.Modules.GameOption.Domain.Constants;
using GLOW.Modules.GameOption.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Presentation.Views.InGameMenu
{
    public interface IInGameMenuSettingUpdateControl
    {
        void SwitchDeckLayout();
        void SetSpecialAttackCutInPlayType(SpecialAttackCutInPlayType specialAttackCutInPlayType);
        void SetDamageDisplay(DamageDisplayFlag isDamageDisplay);
    }
}