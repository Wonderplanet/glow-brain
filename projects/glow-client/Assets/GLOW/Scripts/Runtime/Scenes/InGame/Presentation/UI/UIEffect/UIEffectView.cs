using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules.Audio;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.UI.UIEffect
{
    public class UIEffectView : BaseUIEffectView
    {
        [SerializeField] TimelineAnimation _timelineAnimation;
        [SerializeField] AnimationPlayer _animationPlayer;
        [SerializeField] ParticleSystemComponent _particleSystemComponent;

        public override void Destroy()
        {
            base.Destroy();
            Complete();
        }

        public override BaseUIEffectView Play()
        {
            if (_timelineAnimation != null)
            {
                _timelineAnimation.OnCompleted = Complete;
                _timelineAnimation.Play();
            }
            else if (_animationPlayer != null)
            {
                _animationPlayer.OnDone = Complete;
                _animationPlayer.Play();
            }
            else if (_particleSystemComponent != null)
            {
                _particleSystemComponent.OnStopped = Complete;
                _particleSystemComponent.Play();
            }

            return base.Play();
        }

        protected override void OnPause(bool isPause)
        {
            base.OnPause(isPause);

            if (_timelineAnimation != null)
            {
                _timelineAnimation.Pause(isPause);
            }
            else if (_animationPlayer != null)
            {
                _animationPlayer.Pause(isPause);
            }
            else if (_particleSystemComponent != null)
            {
                _particleSystemComponent.Pause(isPause);
            }
        }

        void Complete()
        {
            _isCompleted = true;
            Destroy(gameObject);
        }
    }
}

