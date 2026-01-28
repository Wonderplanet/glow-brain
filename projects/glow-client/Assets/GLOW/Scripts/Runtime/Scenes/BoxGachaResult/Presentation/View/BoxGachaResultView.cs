using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.BoxGachaResult.Presentation.Component;
using GLOW.Scenes.GachaResult.Presentation;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.BoxGachaResult.Presentation.View
{
    public class BoxGachaResultView : UIView
    {
        [SerializeField] BoxGachaResultComponent _boxGachaResultComponent;
        [SerializeField] Animator _animator;
        
        [Header("閉じるボタン")]
        [SerializeField] UIObject _closeButtonObject;
        [SerializeField] UIText _closeText;
        [SerializeField] CanvasGroup _closeTextCanvasGroup;
        
        [Header("次へボタン")]
        [SerializeField] UIObject _nextButtonObject;
        [SerializeField] UIText _nextText;
        [SerializeField] CanvasGroup _nextTextCanvasGroup;
        
        static readonly int In = Animator.StringToHash("in");
        static readonly int Out = Animator.StringToHash("out");
        
        public void SetIconModels(
            IReadOnlyList<GachaResultCellViewModel> viewModels,
            IReadOnlyList<PlayerResourceIconViewModel> convertedViewModels,
            PreConversionResourceExistenceFlag existsPreConversionResource,
            Action<PlayerResourceIconViewModel> onCellTappedAction,
            Action onCompleteCellAnimation)
        {
            _boxGachaResultComponent.SetIconModels(
                viewModels,
                convertedViewModels,
                existsPreConversionResource,
                onCellTappedAction,
                onCompleteCellAnimation);
        }

        public void SetAvatarModel(IReadOnlyList<PlayerResourceIconViewModel> avatarViewModels)
        {
            _boxGachaResultComponent.SetAvatarModel(avatarViewModels);
        }

        public void StartAnimation()
        {
            _boxGachaResultComponent.StartAnimation();
        }
        
        public void SkipAvatarCellAnimation()
        {
            _boxGachaResultComponent.SkipAvatarCellAnimation();
        }
        
        public void SkipCellAnimation(
            IReadOnlyList<PlayerResourceIconViewModel> convertedViewModels, 
            PreConversionResourceExistenceFlag existsPreConversionResource)
        {
            _boxGachaResultComponent.SkipCellAnimation(
                convertedViewModels, 
                existsPreConversionResource);
        }
        
        public async UniTask PlayOpenAnimation(CancellationToken cancellationToken)
        {
            _animator.SetBool(In, true);
            
            await UniTask.WaitUntil(
                () =>  _animator.GetCurrentAnimatorStateInfo(0).normalizedTime >= 1.0f, 
                cancellationToken: cancellationToken);
            
            _closeButtonObject.IsVisible = true;
        }

        public async UniTask PlayCloseAnimation(CancellationToken cancellationToken)
        {
            _animator.SetBool(Out, true);
            
            await UniTask.WaitUntil(
                () =>  _animator.GetCurrentAnimatorStateInfo(0).normalizedTime >= 1.0f, 
                cancellationToken: cancellationToken);
        }
        
        public void FadeInCloseText()
        {
            _closeTextCanvasGroup.alpha = 0.0f;
            _closeText.IsVisible = true;
            _closeTextCanvasGroup.DOFade(1.0f, 0.3f);
        }

        public void SetNextButtonVisible(bool isVisible)
        {
            _nextButtonObject.IsVisible = isVisible;
        }
        
        public void SetNextTextVisible(bool isVisible)
        {
            _nextText.IsVisible = isVisible;
        }
        
        public void FadeInNextText()
        {
            _nextTextCanvasGroup.alpha = 0.0f;
            _nextText.IsVisible = true;
            _nextTextCanvasGroup.DOFade(1.0f, 0.3f);
        }
    }
}