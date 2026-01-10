using System;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules.Audio;
using UnityEngine;

namespace GLOW.Scenes.ArtworkFragment.Presentation.Components
{
    public class ArtworkFragmentReleaseAnimation : UIObject
    {
        [SerializeField] Animator _animator;

        public Action OnArtworkFragmentReleaseEventAction { get; set; }

        public void OnArtworkFragmentReleaseEvent()
        {
            OnArtworkFragmentReleaseEventAction?.Invoke();
        }

        public void PlayArtworkFragmentAnimation(Action releaseEventAction)
        {
            OnArtworkFragmentReleaseEventAction = releaseEventAction;
            this.Hidden = false;
        }

        public void OnArtworkFragmentReleaseSoundEffect()
        {
            SoundEffectPlayer.Play(SoundEffectId.SSE_072_002);
        }
    }
}
