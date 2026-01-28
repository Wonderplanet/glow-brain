using System;
using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class UIEffectComponent : UIObject
    {
        [SerializeField] TimelineAnimation _timelineAnimation;
        [SerializeField] AnimationPlayer _animationPlayer;

        readonly MultipleSwitchController _pauseController = new ();

        public Action OnCompleted { get; set; }

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

        public void Destroy()
        {
            OnAnimationCompleted();
        }

        public void Play()
        {
            if (_timelineAnimation != null)
            {
                _timelineAnimation.OnCompleted = OnAnimationCompleted;
                _timelineAnimation.Play();
            }
            else if (_animationPlayer != null)
            {
                _animationPlayer.OnDone = OnAnimationCompleted;
                _animationPlayer.Play();
            }
        }

        public MultipleSwitchHandler Pause(MultipleSwitchHandler handler)
        {
            return _pauseController.TurnOn(handler);
        }

        void OnPause(bool isPause)
        {
            if (_timelineAnimation != null)
            {
                _timelineAnimation.Pause(isPause);
            }
            else if (_animationPlayer != null)
            {
                _animationPlayer.Pause(isPause);
            }
        }

        void OnAnimationCompleted()
        {
            OnCompleted?.Invoke();
            Destroy(gameObject);
        }
    }
}
