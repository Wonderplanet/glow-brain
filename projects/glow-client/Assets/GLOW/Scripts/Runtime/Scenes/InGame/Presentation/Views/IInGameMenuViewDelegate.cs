using GLOW.Modules.GameOption.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Constants;

namespace GLOW.Scenes.InGame.Presentation.Views
{
    public interface IInGameMenuViewDelegate
    {
        void OnViewDidLoad();
        void OnViewDidUnload();
        void Abort();
        void OnBgmMuteToggleSwitched();
        void OnSeMuteToggleSwitched();
        void OnSpecialAttackCutInToggleSwitched(SpecialAttackCutInPlayType specialAttackCutInPlayType);
        void OnTwoRowDeckToggleSwitched();
        void OnDamageDisplayToggleSwitched();
    }
}