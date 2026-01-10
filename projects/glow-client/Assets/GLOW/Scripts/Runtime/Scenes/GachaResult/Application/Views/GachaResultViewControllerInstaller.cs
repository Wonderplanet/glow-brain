using GLOW.Modules.CommonReceiveView.Domain.UseCases;
using GLOW.Scenes.GachaResult.Domain.UseCases;
using GLOW.Scenes.GachaResult.Presentation.Presenters;
using GLOW.Scenes.GachaResult.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.GachaResult.Application.Views
{
    public class GachaResultViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.Bind<GachaResultUseCase>().AsCached();
            Container.Bind<GetCommonReceiveItemUseCase>().AsCached();
            Container.Bind<TutorialGachaResultConfirmUseCase>().AsCached();
            Container.Bind<TutorialGachaReDrawUseCase>().AsCached();
            Container.BindViewWithKernal<GachaResultViewController>();
            Container.BindInterfacesTo<GachaResultPresenter>().AsCached();
        }
    }
}
