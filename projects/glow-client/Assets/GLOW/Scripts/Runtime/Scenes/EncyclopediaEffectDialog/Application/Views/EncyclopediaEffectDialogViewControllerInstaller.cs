using Zenject;
using UIKit.ZenjectBridge;
using GLOW.Scenes.EncyclopediaEffectDialog.Domain.UseCases;
using GLOW.Scenes.EncyclopediaEffectDialog.Presentation.Presenters;
using GLOW.Scenes.EncyclopediaEffectDialog.Presentation.Views;

namespace GLOW.Scenes.EncyclopediaEffectDialog.Application.Views
{
    public class EncyclopediaEffectDialogViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<EncyclopediaEffectDialogViewController>();
            Container.BindInterfacesTo<EncyclopediaEffectDialogPresenter>().AsCached();
            Container.Bind<GetEncyclopediaEffectUseCase>().AsCached();
        }
    }
}
