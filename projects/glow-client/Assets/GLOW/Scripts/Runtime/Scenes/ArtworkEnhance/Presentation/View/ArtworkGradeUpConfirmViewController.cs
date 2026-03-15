using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ArtworkEnhance.Presentation.ViewModels;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.ArtworkEnhance.Presentation.View
{
    public class ArtworkGradeUpConfirmViewController :
        UIViewController<ArtworkGradeUpConfirmView>,
        IEscapeResponder
    {
        public record Argument(MasterDataId MstArtworkId, Action OnConfirm);
        [Inject] IArtworkUpGradeConfirmDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        PlayerResourceIconViewModel _requiredEnhanceItemIconViewModel;
        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();

            EscapeResponderRegistry.Register(this);
        }

        public void SetUpView(ArtworkUpGradeConfirmViewModel viewModel)
        {
            ActualView.Setup(viewModel, ViewDelegate.OnItemIconTapped);
            SetButtonInteractable(viewModel.RequiredEnhanceItemViewModels);
        }

        public void OnClose()
        {
            EscapeResponderRegistry.Unregister(this);
            Dismiss();
        }

        void SetButtonInteractable(IReadOnlyList<RequiredEnhanceItemViewModel> requiredEnhanceItemViewModels)
        {
            for (int i = 0; i < requiredEnhanceItemViewModels.Count; i++)
            {
                if (requiredEnhanceItemViewModels[i].PossessionAmount < requiredEnhanceItemViewModels[i].ConsumeAmount)
                {
                    ActualView.GradeUpButton.interactable = false;
                    return;
                }
            }
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;

            OnClose();
            return true;
        }

        [UIAction]
        void OnInfoButtonTapped()
        {
            ViewDelegate.OnInfoButtonTapped();
        }

        [UIAction]
        void OnConfirmButtonTapped()
        {
            ViewDelegate.OnConfirmButtonTapped();
        }

        [UIAction]
        void OnBackButtonTapped()
        {
            ViewDelegate.OnBackButtonTapped();
        }
    }
}
