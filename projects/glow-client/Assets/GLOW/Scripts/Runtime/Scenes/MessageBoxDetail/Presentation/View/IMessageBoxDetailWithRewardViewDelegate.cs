using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.MessageBoxDetail.Presentation.View
{
    public interface IMessageBoxDetailWithRewardViewDelegate
    {
        void OnViewDidLoad();
        void OnClose();
        void OnReceiveRewardSelected();
        void OnRewardSelected(PlayerResourceIconViewModel viewModel);
    }
}
