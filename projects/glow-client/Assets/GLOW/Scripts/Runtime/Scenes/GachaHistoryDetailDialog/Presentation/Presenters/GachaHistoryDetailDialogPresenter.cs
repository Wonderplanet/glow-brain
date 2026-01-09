using GLOW.Scenes.GachaHistoryDetailDialog.Presentation.Views;
using Zenject;

namespace GLOW.Scenes.GachaHistoryDetailDialog.Presentation.Presenters
{
    public class GachaHistoryDetailDialogPresenter : IGachaHistoryDetailDialogViewDelegate
    {
        [Inject] GachaHistoryDetailDialogViewController ViewController { get; }
        [Inject] GachaHistoryDetailDialogViewController.Argument Argument { get; }
        
        void IGachaHistoryDetailDialogViewDelegate.OnViewDidLoad()
        {
            ViewController.Setup(Argument.CellViewModel, Argument.DetailCellViewModel);
        }
    }
}