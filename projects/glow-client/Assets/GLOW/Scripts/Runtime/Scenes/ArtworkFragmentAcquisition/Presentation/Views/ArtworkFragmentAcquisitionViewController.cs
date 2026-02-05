using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFragment.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFragmentAcquisition.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.ArtworkFragmentAcquisition.Presentation.Views
{
    /// <summary>
    /// 41_メインクエスト・ステージ
    /// 　41-2_ステージ共通
    /// 　　41-2-10_原画のかけら獲得ダイアログ
    /// </summary>
    public class ArtworkFragmentAcquisitionViewController : UIViewController<ArtworkFragmentAcquisitionView>, IEscapeResponder
    {
        public record Argument(
            ArtworkFragmentAcquisitionViewModel ViewModel, 
            Action OnViewClosed);

        [Inject] IArtworkFragmentAcquisitionViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        bool _isInitialized;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);

            if (_isInitialized) return;

            _isInitialized = true;
            EscapeResponderRegistry.Bind(this, ActualView);
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();

            ViewDelegate.OnViewDidUnload();
        }

        public void Setup(ArtworkFragmentAcquisitionViewModel viewModel)
        {
            ActualView.Setup(viewModel);
        }

        public void SetSkipButtonAction(Action action)
        {
            ActualView.SetSkipButtonAction(action);
        }

        public async UniTask PlayArtworkFragmentAnimation(IReadOnlyList<ArtworkFragmentPositionNum> positions, CancellationToken cancellationToken)
        {
            await ActualView.PlayArtworkFragmentAnimation(positions, cancellationToken);
        }

        public void SkipArtworkFragmentAnimation(IReadOnlyList<ArtworkFragmentPositionNum> positions)
        {
            ActualView.SkipArtworkFragmentAnimation(positions);
        }

        public async UniTask PlayArtworkCompleteAnimation(HP addHp, CancellationToken cancellationToken)
        {
            await ActualView.PlayArtworkCompleteAnimation(addHp, cancellationToken);
        }

        public void SkipArtworkCompleteAnimation()
        {
            ActualView.SkipArtworkCompleteAnimation();
        }

        public void EndAnimation()
        {
            ActualView.EndAnimation();
        }

        bool IEscapeResponder.OnEscape()
        {
            Debug.Log("OnEscape in ArtworkFragmentAcquisitionViewController");
            if(ActualView.Hidden) return false;

            ViewDelegate.OnBackButton();
            return true;
        }

        [UIAction]
        void OnCloseButtonTapped()
        {
            ViewDelegate.OnCloseSelected();
        }
    }
}
