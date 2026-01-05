using GLOW.Scenes.TermsOfService.Domain.UseCases;
using GLOW.Scenes.TermsOfService.Presentation.Presenters;
using GLOW.Scenes.TermsOfService.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.TermsOfService.Application.Installers
{
    public class TermsOfServiceViewControllerInstaller : Installer
    {
        [Inject] TermsOfServiceViewController.Argument Argument { get; set; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.Bind<TermsOfServiceUseCases>().AsCached();
            Container.Bind<GetTermsOfServiceUrlUseCase>().AsCached();
            Container.BindInterfacesTo<TermsOfServicePresenter>().AsCached();
            Container.BindViewWithKernal<TermsOfServiceViewController>();
        }
    }
}
