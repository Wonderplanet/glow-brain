using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Modules.CommonReceiveView.Presentation.Views
{
    public interface ICommonReceiveViewDelegate
    {
        void OnViewDidLoad();
        void OnViewWillAppear();
        void OnCloseSelected();
        void OnIconSelected(PlayerResourceIconViewModel viewModel);
    }
}
