using WPFramework.Debugs.Environment.Presentation.ViewModels;

namespace WPFramework.Debugs.Environment.Presentation.Views
{
    public interface IDebugEnvironmentSpecifiedDomainViewDelegate
    {
        void OnViewDidLoad();
        void OnConfirm(DebugEnvironmentSpecifiedDomainViewModel model);
        void OnReset();
    }
}
