using UIKit.ZenjectBridge;
using Zenject;
#if DEBUG
using WPFramework.Debugs.Environment.Domain.UseCases;
using WPFramework.Debugs.Environment.Presentation.Presenters;
using WPFramework.Debugs.Environment.Presentation.Translators;
using WPFramework.Debugs.Environment.Presentation.Views;
#endif // DEBUG

namespace GLOW.Debugs.Applications.Installers
{
    public class DebugEnvironmentSpecifiedDomainViewControllerInstaller : Installer
    {
#if DEBUG
        [Inject] DebugEnvironmentSpecifiedDomainViewController.Arguments Arguments { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Arguments).AsCached();
            Container.BindInterfacesTo<DebugEnvironmentViewModelTranslator>().AsCached();
            Container.Bind<DebugEnvironmentSpecifiedDomainUseCases>().AsCached();
            Container.BindInterfacesTo<DebugEnvironmentSpecifiedDomainPresenter>().AsCached();
            Container.BindViewWithKernal<DebugEnvironmentSpecifiedDomainViewController>();
        }
#else
        public override void InstallBindings()
        {
        }
#endif  // DEBUG
    }
}
