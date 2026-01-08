using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.ViewModels;
using GLOW.Scenes.Home.Presentation.Views;
using UIKit;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.Views
{
    /// <summary>
    /// 91_図鑑
    /// 　91-4_作品別原画表示
    /// 　　91-4-1_原画画面
    /// </summary>
    public class EncyclopediaArtworkDetailViewController : HomeBaseViewController<EncyclopediaArtworkDetailView>,
        IEscapeResponder,
        IEncyclopediaArtworkPageListDelegate
    {
        public record Argument(IReadOnlyList<MasterDataId> MstArtworkIds, MasterDataId SelectedMstArtworkId);

        [Inject] IEncyclopediaArtworkDetailViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] IViewFactory ViewFactory { get; }

        public Action OnClosed { get; set; }

        public override void ViewDidLoad()
        {
            EscapeResponderRegistry.Bind(this, ActualView);
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }

        public void InitializePageView(IReadOnlyList<MasterDataId> mstArtworkIds, MasterDataId selectedMstArtworkId)
        {
            ActualView.ArtworkPageComponent.Delegate = this;
            ActualView.ArtworkPageComponent.Setup(
                ViewFactory,
                this,
                mstArtworkIds,
                selectedMstArtworkId,
                ViewDelegate.OnSelectArtworkExpand);
        }

        public void Setup(EncyclopediaArtworkDetailViewModel viewModel, bool isHiddenArrowButton)
        {
            ActualView.Setup(viewModel, isHiddenArrowButton, ViewDelegate.OnSelectFragmentDropQuest);
        }

        bool IEscapeResponder.OnEscape()
        {
            if (View.Hidden) return false;

            SoundEffectPlayer.Play(SoundEffectId.SSE_000_003);
            ViewDelegate.OnBackButtonTapped();
            return true;
        }

        [UIAction]
        void OnSelectPrevArtworkButtonTapped()
        {
            ActualView.ArtworkPageComponent.ScrollToPrevPage(true);
        }

        [UIAction]
        void OnSelectNextArtworkButtonTapped()
        {
            ActualView.ArtworkPageComponent.ScrollToNextPage(true);
        }

        [UIAction]
        void OnSwitchOutpostArtworkButtonTapped()
        {
            ViewDelegate.OnSwitchOutpostArtworkButtonTapped();
        }

        [UIAction]
        void OnBackButtonTapped()
        {
            ViewDelegate.OnBackButtonTapped();
        }

        void IEncyclopediaArtworkPageListDelegate.SwitchArtwork(MasterDataId mstArtworkId)
        {
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
    }
}
