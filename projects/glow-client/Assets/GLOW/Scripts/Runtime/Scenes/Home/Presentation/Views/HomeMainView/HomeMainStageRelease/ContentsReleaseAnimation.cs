using System;
using GLOW.Core.Presentation.Modules.Audio;
using UnityEngine;

namespace GLOW.Scenes.Home.Presentation.Views
{
    public sealed class ContentsReleaseAnimation : MonoBehaviour
    {
        [SerializeField] Animator _animator;
        static readonly int ReleaseAnimationTriggerId = Animator.StringToHash("OnRelease");
        public Action OnStageReleaseEventAction { get; set; }

        public void ShowAnimation()
        {
            _animator.SetTrigger(ReleaseAnimationTriggerId);
        }
        public void OnStageReleaseEvent()
        {
            OnStageReleaseEventAction?.Invoke();
        }

        public void OnAnimationStartSEEvent()
        {
            SoundEffectPlayer.Play(SoundEffectId.SSE_012_001);
        }

        public void OnAnimationUnlockSEEvent()
        {
            SoundEffectPlayer.Play(SoundEffectId.SSE_012_002);
        }
    }
}
