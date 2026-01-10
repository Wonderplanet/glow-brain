using GLOW.Scenes.EncyclopediaUnitSpecialAttack.Domain.UseCases;
using GLOW.Scenes.EncyclopediaUnitSpecialAttack.Presentation.AssetLoaders;
using GLOW.Scenes.EncyclopediaUnitSpecialAttack.Presentation.Presenters;
using GLOW.Scenes.EncyclopediaUnitSpecialAttack.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.EncyclopediaUnitSpecialAttack.Application.Views
{
    public class UnitSpecialAttackPreviewViewControllerInstaller : Installer
    {
        [Inject] UnitSpecialAttackPreviewViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<UnitSpecialAttackPreviewViewController>();
            Container.BindInterfacesTo<UnitSpecialAttackPreviewPresenter>().AsCached();
            Container.Bind<GetUnitSpecialAttackPreviewUseCase>().AsCached();
            Container.BindInterfacesTo<UnitSpecialAttackPreviewSoundEffectLoader>().AsCached();
            Container.BindInstance(Argument).AsCached();
        }
    }
}
