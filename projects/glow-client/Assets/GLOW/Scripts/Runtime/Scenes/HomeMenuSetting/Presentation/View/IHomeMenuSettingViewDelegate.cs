using GLOW.Modules.GameOption.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Constants;

namespace GLOW.Scenes.HomeMenuSetting.Presentation.View
{
    public interface IHomeMenuSettingViewDelegate
    {
        void OnViewWillAppear();
        void OnBgmMuteToggleSwitched();
        void OnSeMuteToggleSwitched();
        void OnDamageDisplayToggleSwitched();
        void OnSpecialAttackCutInToggleSwitched(SpecialAttackCutInPlayType specialAttackCutInPlayType);
        void OnPushOffToggleSwitched();
        void OnCloseSelected();
    }
}