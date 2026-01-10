using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules;
using GLOW.Core.Presentation.Views.UIAnimator;
using GLOW.Scenes.PassShop.Presentation.ViewModel;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.Home.Presentation.Components
{
    public class HomeHeldPassBannerComponent : UIObject
    {
        [SerializeField] UIImage _passBannerImage;
        [SerializeField] UIText _passRemainingTimeSpan;
        [SerializeField] UIAnimator _uiAdditiveEffectAnimator;
        [SerializeField] CanvasGroup _canvasGroup;

        CancellationTokenSource _cancellationTokenSource;
        IReadOnlyList<HeldPassEffectDisplayViewModel> _viewModels = new List<HeldPassEffectDisplayViewModel>();
        int _displayingIndex;

        public void SetUp(IReadOnlyList<HeldPassEffectDisplayViewModel> viewModels)
        {
            if (viewModels.IsEmpty())
            {
                Hidden = true;
                return;
            }
            
            _canvasGroup.alpha = 0.0f;
            Hidden = false;
            _viewModels = viewModels;
            
            PlayAnimation();
        }

        void PlayAnimation()
        {
            _cancellationTokenSource?.Cancel();
            _cancellationTokenSource?.Dispose();
            _cancellationTokenSource = new CancellationTokenSource();

            var linkedTokenSource = CancellationTokenSource.CreateLinkedTokenSource(
                this.GetCancellationTokenOnDestroy(), _cancellationTokenSource.Token);

            if (_viewModels.Count == 1)
            {
                PlayAnimationSinglePass(linkedTokenSource.Token).Forget();
            }
            else if (_viewModels.Count > 1)
            {
                PlayAnimationMultiPass(linkedTokenSource.Token).Forget();
            }
        }

        async UniTask PlayAnimationSinglePass(CancellationToken cancellationToken)
        {
            var viewModel = _viewModels.First(); 
            
            _passRemainingTimeSpan.SetText(TimeSpanFormatter.FormatRemaining(viewModel.RemainingTimeSpan));
            
            SpriteLoaderUtil.Clear(_passBannerImage.Image);
            await UISpriteUtil.Load(
                cancellationToken, 
                _passBannerImage.Image, 
                viewModel.DisplayHoldingPassBannerAssetPath.Value);
            
            _uiAdditiveEffectAnimator.PlayAnimation();
            await _canvasGroup
                .DOFade(1f, 0.2f)
                .WithCancellation(cancellationToken);
        }
        
        async UniTask PlayAnimationMultiPass(CancellationToken cancellationToken)
        {
            _displayingIndex = 0;

            while (true)
            {
                if (_displayingIndex >= _viewModels.Count)
                {
                    _displayingIndex = 0;
                }

                var viewModel = _viewModels[_displayingIndex];
                
                _passRemainingTimeSpan.SetText(TimeSpanFormatter.FormatRemaining(viewModel.RemainingTimeSpan));
            
                SpriteLoaderUtil.Clear(_passBannerImage.Image);
                await UISpriteUtil.Load(
                    cancellationToken, 
                    _passBannerImage.Image, 
                    viewModel.DisplayHoldingPassBannerAssetPath.Value);
                
                _uiAdditiveEffectAnimator.PlayAnimation();
                await _canvasGroup
                    .DOFade(1f, 0.2f)
                    .ToUniTask(TweenCancelBehaviour.KillAndCancelAwait, cancellationToken);

                await UniTask.Delay(TimeSpan.FromSeconds(2.6f), cancellationToken: cancellationToken);

                await _canvasGroup
                    .DOFade(0f, 0.2f)
                    .ToUniTask(TweenCancelBehaviour.KillAndCancelAwait, cancellationToken);
                _uiAdditiveEffectAnimator.StopAnimation();
                
                _displayingIndex++;
            }
        }
    }
}