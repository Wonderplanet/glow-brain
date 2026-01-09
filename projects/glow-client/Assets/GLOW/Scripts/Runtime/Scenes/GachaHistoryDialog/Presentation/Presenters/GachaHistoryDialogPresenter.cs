using GLOW.Scenes.GachaHistoryDetailDialog.Presentation.ViewModels;
using GLOW.Scenes.GachaHistoryDialog.Presentation.ViewModels;
using GLOW.Scenes.GachaHistoryDialog.Presentation.Views;
using Zenject;

namespace GLOW.Scenes.GachaHistoryDialog.Presentation.Presenters
{
    public class GachaHistoryDialogPresenter : IGachaHistoryDialogViewDelegate
    {
        [Inject] GachaHistoryDialogViewController ViewController { get; }
        [Inject] GachaHistoryWireFrame GachaHistoryWireFrame { get; }
        [Inject] GachaHistoryDialogViewController.Argument Argument { get; }
        
        void IGachaHistoryDialogViewDelegate.OnViewDidLoad()
        {
            ViewController.Setup(Argument.ViewModel, Argument.CurrentPage);
        }
        
        void IGachaHistoryDialogViewDelegate.OnCellTapped(
            GachaHistoryCellViewModel cellViewModel,
            GachaHistoryDetailDialogViewModel detailViewModel,
            int currentPage,
            float scrollPos)
        {
            // スクロール・ページ情報をワイヤフレーム側に渡す
            GachaHistoryWireFrame.ShowGachaHistoryDetailDialogView(
                cellViewModel,
                detailViewModel,
                currentPage,
                scrollPos);
            ViewController.Dismiss();
        }
    }
}