using WPFramework.Debugs.Environment.Presentation.ViewModels;

namespace WPFramework.Debugs.Environment.Presentation.Views
{
    public interface IDebugEnvironmentSelectViewDelegate
    {
        void OnViewDidLoad();
        void OnSelectedEnvironment(DebugEnvironmentViewModel viewModel);
        void OnDeleteLocalData();
        void OnSpecifiedDomainSetting();
    }
}
