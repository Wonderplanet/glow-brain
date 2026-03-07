using System;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Domain.Constants;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Presentation.ViewModels;
using UIKit;
using Zenject;

namespace GLOW.Scenes.ArtworkSortAndFilterDialog.Presentation.Views
{
    public class ArtworkSortAndFilterDialogViewController : UIViewController<ArtworkSortAndFilterDialogView>
    {
        public record Argument(ArtworkSortAndFilterDialogViewModel ViewModel, Action OnCancel, Action OnConfirm);

        [Inject] IArtworkSortAndFilterDialogViewDelegate ViewDelegate { get; }
        [Inject] Argument Args { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad(Args.ViewModel);
        }

        public void InitializeSort(ArtworkListSortType currentSortType, Action<ArtworkListSortType> onToggleChange)
        {
            ActualView.InitializeSort(currentSortType, onToggleChange);
        }

        public void InitializeFilter(ArtworkSortAndFilterDialogViewModel viewModel)
        {
            ActualView.InitializeFilter(viewModel);
        }

        public void SetSortToggle(ArtworkListSortType setSortType)
        {
            ActualView.SetSortToggle(setSortType);
        }

        [UIAction]
        void OnConfirm()
        {
            ViewDelegate.OnConfirm();
        }

        [UIAction]
        void OnCancel()
        {
            ViewDelegate.OnCancel();
        }
    }
}
