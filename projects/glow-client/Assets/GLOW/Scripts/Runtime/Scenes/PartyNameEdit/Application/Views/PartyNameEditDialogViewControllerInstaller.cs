using GLOW.Scenes.PartyNameEdit.Domain.UseCases;
using GLOW.Scenes.PartyNameEdit.Presentation.Presenters;
using GLOW.Scenes.PartyNameEdit.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.PartyNameEdit.Application.Views
{
    public class PartyNameEditDialogViewControllerInstaller : Installer
    {
        [Inject] PartyNameEditDialogViewController.Argument Args { get; }
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<PartyNameEditDialogViewController>();
            Container.BindInstance(Args);

            Container.BindInterfacesTo<PartyNameEditDialogPresenter>().AsCached();
            Container.Bind<GetPartyNameUseCase>().AsCached();
            Container.Bind<UpdatePartyNameUseCase>().AsCached();
        }
    }
}
