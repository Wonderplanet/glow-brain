using System.Collections.Generic;
using GLOW.Scenes.MessageBox.Presentation.ViewModel;
using UIKit;

namespace GLOW.Scenes.MessageBox.Presentation.View
{
    public interface IMessageBoxViewDelegate
    {
        void OnViewDidLoad();
        void OnViewDidUnload();
        void OnClose();
        void OnMessageSelected(IMessageBoxCellViewModel viewModel, UIIndexPath indexPath);
        void OnBulkOpen(IReadOnlyList<IMessageBoxCellViewModel> cellViewModels);
        void OnBulkReceive(IReadOnlyList<IMessageBoxCellViewModel> viewModels);
    }
}
