using GLOW.Scenes.UnitSortAndFilterDialog.Domain.Constants;

namespace GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Views
{
    public interface IUnitSortAndFilterDialogViewDelegate
    {
        void OnViewDidLoad(UnitSortAndFilterDialogViewModel viewModel);
        void OnConfirm();
        void OnCancel();
        void OnSortAndFilterTabClicked(UnitSortAndFilterTabType tabType);
    }
}
