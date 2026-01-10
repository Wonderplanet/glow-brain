using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class KomaEffectView : UIObject
    {
        readonly MultipleSwitchController _pauseController = new ();

        protected override void Awake()
        {
            base.Awake();
            _pauseController.OnStateChanged = OnPause;
        }

        protected override void OnDestroy()
        {
            base.OnDestroy();
            _pauseController.Dispose();
        }
        
        public virtual void SetUpKomaEffect(IKomaEffectModel komaEffectModel)
        {
        }
        
        public virtual void UpdateKomaEffect(IKomaEffectModel komaEffectModel)
        {
        }

        public virtual void ResetKomaEffect(IKomaEffectModel komaEffectModel)
        {
        }

        public MultipleSwitchHandler Pause(MultipleSwitchHandler handler)
        {
            return _pauseController.TurnOn(handler);
        }

        public virtual MultipleSwitchHandler PauseWithoutDarknessClear(MultipleSwitchHandler handler)
        {
            return _pauseController.TurnOn(handler);
        }

        protected virtual void OnPause(bool pause)
        {
        }
    }
}
