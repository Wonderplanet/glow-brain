using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkEnhance.Presentation.ViewModels;
using GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.Views;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.ArtworkEnhance.Presentation.View
{
    public class ArtworkEnhanceViewController :
        UIViewController<ArtworkEnhanceView>,
        IEncyclopediaArtworkPageListDelegate,
        IEscapeResponder
    {
        public record Argument(
            MasterDataId MstFirstArtworkId,
            IReadOnlyList<MasterDataId> MstArtworkIds);
        [Inject] IArtworkEnhanceDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        public Action OnClosed { get; set; }

        MasterDataId _currentMstArtworkId;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();

            ActualView.PlayArtworkAnimation();
            EscapeResponderRegistry.Unregister(this);
        }

        public void SetUpView(ArtworkEnhanceViewModel viewModel)
        {
            ActualView.Setup(viewModel, ViewDelegate.OnItemIconTapped);
        }

        public void SetUpArtworkPageComponent(
            IViewFactory viewFactory,
            MasterDataId mstFirstArtworkId,
            IReadOnlyList<MasterDataId> mstArtworkIds)
        {
            ActualView.ArtworkPageComponent.Delegate = this;
            ActualView.ArtworkPageComponent.Setup(
                viewFactory,
                this,
                mstArtworkIds,
                mstFirstArtworkId);
            
            // 原画が複数ある場合のみ、左右の切り替えボタンを表示する
            ActualView.SetArrowButtonsVisible(mstArtworkIds.Count > 1);

            _currentMstArtworkId = mstFirstArtworkId;
        }

        public void UpdateCurrentPageView()
        {
            ActualView.ArtworkPageComponent.UpdateCurrentPageView();
        }

        public void OnClose()
        {
            OnClosed?.Invoke();
            EscapeResponderRegistry.Unregister(this);
        }

        void IEncyclopediaArtworkPageListDelegate.SwitchArtwork(MasterDataId mstArtworkId)
        {
            _currentMstArtworkId = mstArtworkId;
            ViewDelegate.OnSwitchArtwork(mstArtworkId);
        }

        void IEncyclopediaArtworkPageListDelegate.WillTransitionTo()
        {
            ActualView.Interactable = false;
        }

        void IEncyclopediaArtworkPageListDelegate.DidFinishAnimating(bool finished, bool transitionCompleted)
        {
            ActualView.Interactable = finished;
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;

            ViewDelegate.OnBackButtonTapped();
            return true;
        }

        [UIAction]
        void OnEnhanceButtonTapped()
        {
            ViewDelegate.OnEnhanceButtonTapped(_currentMstArtworkId);
        }

        [UIAction]
        void OnInfoButtonTapped()
        {
            ViewDelegate.OnInfoButtonTapped(_currentMstArtworkId);
        }

        [UIAction]
        void OnBackButtonTapped()
        {
            ViewDelegate.OnBackButtonTapped();
        }

        [UIAction]
        void OnArtworkEffectDescriptionTabTapped()
        {
            ActualView.SetArtworkDescriptionTab(ActualView.ArtworkEffectTabKey);
        }

        [UIAction]
        void OnArtworkDescriptionTabTapped()
        {
            ActualView.SetArtworkDescriptionTab(ActualView.ArtworkDescriptionTabKey);
        }

        [UIAction]
        void OnSelectNextArtworkButtonTapped()
        {
            ActualView.ArtworkPageComponent.ScrollToNextPage(true);
        }

        [UIAction]
        void OnSelectPrevArtworkButtonTapped()
        {
            ActualView.ArtworkPageComponent.ScrollToPrevPage(true);
        }
    }
}
