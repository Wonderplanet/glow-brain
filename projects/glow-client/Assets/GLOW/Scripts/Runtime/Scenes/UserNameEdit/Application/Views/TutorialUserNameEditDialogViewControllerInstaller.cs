using GLOW.Scenes.UserNameEdit.Domain.UseCases;
using GLOW.Scenes.UserNameEdit.Presentation.Presenters;
using GLOW.Scenes.UserNameEdit.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.UserNameEdit.Application.Views
{
    public class TutorialUserNameEditDialogViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<UserNameEditDialogViewController>();

            Container.BindInterfacesTo<TutorialUserNameEditDialogPresenter>().AsCached();
            Container.Bind<GetUserNameUseCase>().AsCached();
            Container.Bind<UpdateUserNameUseCase>().AsCached();
        }
    }
}
