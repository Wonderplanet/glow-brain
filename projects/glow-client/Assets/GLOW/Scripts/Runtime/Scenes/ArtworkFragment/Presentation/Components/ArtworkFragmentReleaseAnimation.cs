using System;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules.Audio;
using UnityEngine;

namespace GLOW.Scenes.ArtworkFragment.Presentation.Components
{
    public class ArtworkFragmentReleaseAnimation : UIObject
    {
        [SerializeField] AnimationPlayer _animationPlayer;

        public Action OnArtworkFragmentReleaseEventAction { get; set; }

        public void OnArtworkFragmentReleaseEvent()
        {
            OnArtworkFragmentReleaseEventAction?.Invoke();
        }

        public void PlayArtworkFragmentAnimation(Action releaseEventAction)
        {
            OnArtworkFragmentReleaseEventAction = releaseEventAction;
            _animationPlayer.OnDone = () =>
            {
                IsVisible = false;
            };
            IsVisible = true;
        }

        public void OnArtworkFragmentReleaseSoundEffect()
        {
            SoundEffectPlayer.Play(SoundEffectId.SSE_072_002);
        }
    }
}
