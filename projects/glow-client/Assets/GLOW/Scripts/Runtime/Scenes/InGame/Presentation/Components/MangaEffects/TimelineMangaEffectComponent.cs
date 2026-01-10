using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules.Audio;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    [RequireComponent(typeof(TimelineAnimation))]
    public class TimelineMangaEffectComponent : AbstractMangaEffectComponent
    {
        [SerializeField] TimelineAnimation _timelineAnimation;

        protected override void Awake()
        {
            base.Awake();

            MangaEffectElements = GetComponentsInChildren<MangaEffectElement>().ToList();
        }

        protected override void OnDestroy()
        {
            base.OnDestroy();
            OnCompleted?.Invoke();
        }

        public override void Destroy()
        {
            Destroy(gameObject);
        }

        public override AbstractMangaEffectComponent Play()
        {
            _timelineAnimation.OnCompleted = OnAnimationCompleted;
            _timelineAnimation.Play();

            return this;
        }

        public List<SoundEffectId> GetSoundEffectIds()
        {
            if (_timelineAnimation == null) return new List<SoundEffectId>();
            
            return _timelineAnimation.GetSoundEffectIds();
        }

        protected override void OnPause(bool pause)
        {
            _timelineAnimation.Pause(pause);
        }

        void OnAnimationCompleted()
        {
            Destroy(gameObject);
        }
    }
}
