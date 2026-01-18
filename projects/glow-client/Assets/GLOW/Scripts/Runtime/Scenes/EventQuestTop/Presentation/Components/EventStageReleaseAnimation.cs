using System;
using GLOW.Core.Presentation.Modules.Audio;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.EventStageSelect.Presentation.Components
{
    public sealed class EventStageReleaseAnimation : MonoBehaviour
    {
        [SerializeField] GameObject activeTargetGameObject;
        [SerializeField] Animator _animator;

        [Header("Mask操作オブジェクト")]
        [Header("BackEff")]
        [SerializeField] Image[] backEffImages;
        [Header("Rock")]
        [SerializeField] Image[] lockImages;
        [Header("Kira")]
        [SerializeField] Image[] kiraImages;
        [Header("Kira-2")]
        [SerializeField] Image[] kira2Images;

        static readonly int ReleaseAnimationTriggerId = Animator.StringToHash("OnRelease");
        public Action OnStageReleaseEventAction { get; set; }
        public Action OnStageReleaseAnimationEndAction { get; set; }
        public GameObject ActiveTargetGameObject => activeTargetGameObject;

        void Awake()
        {
            PreProcessAnimation();
        }

        public void ShowAnimation()
        {
            _animator.SetTrigger(ReleaseAnimationTriggerId);
        }
        public void OnStageReleaseEvent()
        {
            OnStageReleaseEventAction?.Invoke();
        }
        public void OnStageReleaseAnimationEnd()
        {
            OnStageReleaseAnimationEndAction?.Invoke();
        }

        public void OnAnimationStartSEEvent()
        {
            SoundEffectPlayer.Play(SoundEffectId.SSE_012_001);
        }

        public void OnAnimationUnlockSEEvent()
        {
            SoundEffectPlayer.Play(SoundEffectId.SSE_012_002);
        }

        void PreProcessAnimation()
        {
            //Maskable false
            foreach (var backEffImage in backEffImages)
            {
                backEffImage.maskable = false;
            }
            foreach (var lockImage in lockImages)
            {
                lockImage.maskable = false;
            }
            foreach (var kiraImage in kiraImages)
            {
                kiraImage.maskable = false;
            }
            foreach (var kira2Image in kira2Images)
            {
                kira2Image.maskable = false;
            }
        }

        public void PostProcessAnimation()
        {
            // Maskable true

            foreach (var lockImage in lockImages)
            {
                lockImage.maskable = true;
            }
            foreach (var kiraImage in kiraImages)
            {
                kiraImage.maskable = true;
            }
            foreach (var kira2Image in kira2Images)
            {
                kira2Image.maskable = true;
            }
        }
    }
}
