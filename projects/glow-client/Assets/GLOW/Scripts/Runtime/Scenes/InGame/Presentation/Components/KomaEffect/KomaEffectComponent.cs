using GLOW.Core.Domain.Constants;
using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class KomaEffectComponent : UIObject
    {
        KomaEffectView _komaEffectView;

        public void InstantiateKomaEffect(KomaEffectType komaEffectType)
        {
            _komaEffectView = InstantiateKomaEffectView(komaEffectType);
        }
        
        public void SetUpKomaEffect(IKomaEffectModel komaEffectModel)
        {
            if (_komaEffectView == null) return;

            _komaEffectView.SetUpKomaEffect(komaEffectModel);
        }

        public void UpdateKomaEffect(IKomaEffectModel komaEffectModel)
        {
            if (_komaEffectView == null) return;

            _komaEffectView.UpdateKomaEffect(komaEffectModel);
        }

        public void ResetKomaEffect(IKomaEffectModel komaEffectModel)
        {
            if (_komaEffectView == null) return;

            _komaEffectView.ResetKomaEffect(komaEffectModel);
        }

        public MultipleSwitchHandler Pause(MultipleSwitchHandler handler)
        {
            if (_komaEffectView == null) return handler;

            return _komaEffectView.Pause(handler);
        }

        public MultipleSwitchHandler PauseWithoutDarknessClear(MultipleSwitchHandler handler)
        {
            if (_komaEffectView == null) return handler;

            return _komaEffectView.PauseWithoutDarknessClear(handler);
        }

        public bool IsDarknessCleared()
        {
            if (_komaEffectView is KomaEffectDarknessFrontView darknessView)
            {
                return darknessView.IsCleared();
            }

            return true;
        }

        protected virtual KomaEffectView InstantiateKomaEffectView(KomaEffectType komaEffectType)
        {
            return null;
        }
    }
}
