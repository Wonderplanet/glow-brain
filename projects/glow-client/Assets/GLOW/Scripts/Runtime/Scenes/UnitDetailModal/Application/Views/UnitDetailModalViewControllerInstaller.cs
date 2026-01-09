using GLOW.Scenes.SpecialAttackInfo.Application.Installer;
using GLOW.Scenes.SpecialAttackInfo.Presentation.Views;
using GLOW.Scenes.UnitDetail.Domain.UseCases;
using GLOW.Scenes.UnitDetail.Presentation.Presenters;
using GLOW.Scenes.UnitDetail.Presentation.Views;
using GLOW.Scenes.UnitDetailModal.Presentation.Presenters;
using GLOW.Scenes.UnitDetailModal.Presentation.Views;
using GLOW.Scenes.UnitEnhance.Domain.ModelFactories;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.UnitDetailModal.Application.Views
{
    public class UnitDetailModalViewControllerInstaller : Installer
    {
        [Inject] UnitDetailModalViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindViewWithKernal<UnitDetailModalViewController>();

            // NOTE: あまり良い実装ではないが、UnitDetailModalViewを使うのにUnitDetailViewのArgumentを使いたくないのでここで調整する
            var argument = new UnitDetailViewController.Argument(Argument.MstUnitId, Argument.IsMaxStatus);
            Container.BindInstance(argument).AsCached();

            Container.BindInterfacesTo<UnitDetailPresenter>().AsCached();
            Container.BindInterfacesTo<UnitDetailModalPresenter>().AsCached();

            Container.Bind<GetUnitMaxStatusDetailUseCase>().AsCached();
            Container.Bind<GetUnitMinimumStatusDetailUseCase>().AsCached();

            Container.BindInterfacesTo<UnitEnhanceAbilityModelListFactory>().AsCached();

            Container.BindViewFactoryInfo<SpecialAttackInfoViewController, SpecialAttackInfoViewControllerInstaller>();
        }
    }
}
