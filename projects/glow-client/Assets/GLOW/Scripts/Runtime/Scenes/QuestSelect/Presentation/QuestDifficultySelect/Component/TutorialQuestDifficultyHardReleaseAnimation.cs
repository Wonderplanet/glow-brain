using GLOW.Core.Presentation.Modules.Audio;
using UnityEngine;

namespace GLOW.Scenes.QuestSelect.Presentation.QuestDifficultySelect.Component
{
    public class TutorialQuestDifficultyHardReleaseAnimation : MonoBehaviour
    {
        [SerializeField] Animator _animator;
        static readonly int ReleaseAnimationTriggerId = Animator.StringToHash("OnRelease");

        public void ShowAnimation()
        {
            _animator.SetTrigger(ReleaseAnimationTriggerId);
        }

        // NOTE: Animator流用のため、Function使用のメソッドのみ用意
        public void OnStageReleaseEvent() { }

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
