using GLOW.Scenes.UnitEnhanceRankUpDetailDialog.Domain.UseCases;
using GLOW.Scenes.UnitEnhanceRankUpDetailDialog.Presentation.Presenters;
using GLOW.Scenes.UnitEnhanceRankUpDetailDialog.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.UnitEnhanceRankUpDetailDialog.Application.Views
{
    public class UnitEnhanceRankUpDetailDialogViewControllerInstaller : Installer
    {
        [Inject] UnitEnhanceRankUpDetailDialogViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<UnitEnhanceRankUpDetailDialogViewController>();
            Container.BindInterfacesTo<UnitEnhanceRankUpDetailDialogPresenter>().AsCached();
            Container.Bind<UnitEnhanceRankUpDetailDialogUseCase>().AsCached();
        }
    }
}
