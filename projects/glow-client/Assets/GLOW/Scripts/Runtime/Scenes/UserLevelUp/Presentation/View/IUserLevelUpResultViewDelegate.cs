using GLOW.Core.Presentation.ViewModels;
namespace GLOW.Scenes.UserLevelUp.Presentation.View
{
    public interface IUserLevelUpResultViewDelegate
    {
        void OnViewDidLoad();
        void OnViewDidUnload();
        void OnSkipSelected();
        void OnCloseSelected();
        void OnBackButton();
        void OnIconSelected(PlayerResourceIconViewModel viewModel);
    }
}
