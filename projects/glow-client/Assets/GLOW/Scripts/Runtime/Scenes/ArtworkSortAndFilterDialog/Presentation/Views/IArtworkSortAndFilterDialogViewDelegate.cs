using GLOW.Scenes.ArtworkSortAndFilterDialog.Presentation.ViewModels;

namespace GLOW.Scenes.ArtworkSortAndFilterDialog.Presentation.Views
{
    public interface IArtworkSortAndFilterDialogViewDelegate
    {
        void OnViewDidLoad(ArtworkSortAndFilterDialogViewModel viewModel);
        void OnConfirm();
        void OnCancel();
    }
}
