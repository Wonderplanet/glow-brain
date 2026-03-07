using GLOW.Scenes.GachaAnim.Domain.Evaluator;
using GLOW.Scenes.GachaAnim.Domain.UseCases;
using GLOW.Scenes.GachaAnim.Presentation.Presenters;
using GLOW.Scenes.GachaAnim.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.GachaAnim.Application.Views
{
    public class GachaAnimViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.Bind<GachaAnimUseCase>().AsCached();
            Container.BindViewWithKernal<GachaAnimViewController>();
            Container.BindInterfacesTo<GachaAnimPresenter>().AsCached();
            Container.BindInterfacesTo<GachaAnimStartRarityEvaluator>().AsCached();
        }
    }
}
