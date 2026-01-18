using DG.Tweening;
using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Core.Presentation.Components;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class InGameFlashComponent : UIObject
    {
        const float FadeStartDuration = 0.03f;
        const float FadeEndDuration = 0.1f;

        [SerializeField] Image _flashImage;

        readonly MultipleSwitchController _pauseController = new ();
        readonly MultipleSwitchController _flashController = new ();

        Tween _tween;

        protected override void Awake()
        {
            base.Awake();
            _flashController.OnStateChanged = OnFlashStateChanged;
            _pauseController.OnStateChanged = OnPause;
        }

        protected override void OnDestroy()
        {
            base.OnDestroy();

            _tween?.Kill();

            _pauseController.Dispose();
            _flashController.Dispose();
        }

        public MultipleSwitchHandler Pause(MultipleSwitchHandler handler)
        {
            return _pauseController.TurnOn(handler);
        }

        public MultipleSwitchHandler StartFlash()
        {
            return _flashController.TurnOn();
        }

        void OnFlashStateChanged(bool isFlash)
        {
            _tween?.Kill();

            if (isFlash)
            {
                Hidden = false;

                _tween = _flashImage
                    .DOFade(1, FadeStartDuration)
                    .SetEase(Ease.Linear);
            }
            else
            {
                _tween = _flashImage
                    .DOFade(0, FadeEndDuration)
                    .SetEase(Ease.Linear)
                    .OnComplete(() => Hidden = true);
            }
        }

        void OnPause(bool pause)
        {
            if (pause)
            {
                _tween?.Pause();
            }
            else
            {
                _tween?.Play();
            }
        }
    }
}
