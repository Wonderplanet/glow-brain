using System;
using GLOW.Scenes.GachaHistoryDetailDialog.Presentation.ViewModels;
using GLOW.Scenes.GachaHistoryDialog.Presentation.ViewModels;
using UIKit;
using Zenject;

namespace GLOW.Scenes.GachaHistoryDetailDialog.Presentation.Views
{
    public class GachaHistoryDetailDialogViewController : UIViewController<GachaHistoryDetailDialogView>
    {
        public record Argument(
            GachaHistoryCellViewModel CellViewModel,
            GachaHistoryDetailDialogViewModel DetailCellViewModel);
        
        [Inject] IGachaHistoryDetailDialogViewDelegate ViewDelegate { get; }
        
        public Action OnClose { get; set; }
        
        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }
        
        public void Setup(
            GachaHistoryCellViewModel cellViewModel,
            GachaHistoryDetailDialogViewModel detailCellViewModel)
        {
            ActualView.Setup(cellViewModel, detailCellViewModel.GachaHistoryDetailCellViewModels);
        }
        
        public override void ViewDidDisappear()
        {
            base.ViewDidDisappear();
            OnClose?.Invoke();
        }
        
        [UIAction]
        public void OnCloseButtonTapped()
        {
            Dismiss();
        }
    }
}