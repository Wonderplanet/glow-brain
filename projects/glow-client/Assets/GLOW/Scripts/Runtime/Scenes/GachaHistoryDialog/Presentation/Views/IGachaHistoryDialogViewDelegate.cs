using GLOW.Scenes.GachaHistoryDetailDialog.Presentation.ViewModels;
using GLOW.Scenes.GachaHistoryDialog.Presentation.ViewModels;

namespace GLOW.Scenes.GachaHistoryDialog.Presentation.Views
{
    public interface IGachaHistoryDialogViewDelegate
    {
        void OnViewDidLoad();
        void OnCellTapped(
            GachaHistoryCellViewModel cellViewModel,
            GachaHistoryDetailDialogViewModel detailViewModel,
            int currentPage,
            float scrollPos);
    }
}