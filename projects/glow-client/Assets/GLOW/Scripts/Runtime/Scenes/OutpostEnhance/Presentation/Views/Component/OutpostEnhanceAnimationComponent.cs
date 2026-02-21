using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.OutpostEnhance.Presentation.ViewModels;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.OutpostEnhance.Presentation.Views.Component
{
    public class OutpostEnhanceAnimationComponent : MonoBehaviour
    {
        [SerializeField] Animator _gateAnimation;
        [SerializeField] Animator _enhanceEffectAnimation;
        [SerializeField] Animator _backgroundAnimation;
        [SerializeField] Animator _buttonGrayOutAnimation;
        [SerializeField] OutpostEnhanceAnimationWindowComponent _enhanceResultWindow;
        [SerializeField] Button _skipButton;

        readonly string _animationTrigger = "Play";
        readonly string _gateDef = "GateDef";
        readonly string _fadeOutTrigger = "FadeOut";

        void Awake()
        {
            _enhanceEffectAnimation.gameObject.SetActive(false);
            _backgroundAnimation.gameObject.SetActive(false);
        }

        public void SetSkipButtonAction(Action action)
        {
            _skipButton.onClick.RemoveAllListeners();
            _skipButton.onClick.AddListener(() => action?.Invoke());
            _skipButton.gameObject.SetActive(true);
        }

        public async UniTask PlayEnhanceEffectAnimation(CancellationToken cancellationToken)
        {
            _gateAnimation.SetTrigger(_animationTrigger);
            _enhanceEffectAnimation.gameObject.SetActive(true);
            _backgroundAnimation.gameObject.SetActive(true);

            SoundEffectPlayer.Play(SoundEffectId.SSE_031_001);

            await UniTask.Delay(TimeSpan.FromSeconds(0.75f), cancellationToken: cancellationToken);
        }

        public async UniTask PlayEnhanceWindowAnimation(OutpostEnhanceResultViewModel model, CancellationToken cancellationToken)
        {
            _enhanceResultWindow.Setup(model);

            _enhanceResultWindow.gameObject.SetActive(true);
            _backgroundAnimation.Play(_fadeOutTrigger);

            SoundEffectPlayer.Play(SoundEffectId.SSE_031_002);

            await UniTask.Delay(TimeSpan.FromSeconds(1.8f), cancellationToken: cancellationToken);
        }

        public void SkipEnhanceEffectAnimation()
        {
            _gateAnimation.Play(_gateDef);
            _enhanceEffectAnimation.gameObject.SetActive(false);
        }

        public void EndAnimation()
        {
            _skipButton.gameObject.SetActive(false);
            _enhanceEffectAnimation.gameObject.SetActive(false);
            _backgroundAnimation.gameObject.SetActive(false);
            _enhanceResultWindow.gameObject.SetActive(false);
            _buttonGrayOutAnimation.Play(_fadeOutTrigger);
        }
    }
}
