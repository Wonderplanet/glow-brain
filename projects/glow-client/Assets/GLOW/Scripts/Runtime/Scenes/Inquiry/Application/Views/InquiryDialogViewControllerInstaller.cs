using GLOW.Scenes.Inquiry.Presentation.Presenter;
using GLOW.Scenes.Inquiry.Presentation.View;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.Inquiry.Application.Views
{
    public class InquiryDialogViewControllerInstaller : Installer
    {
        [Inject] InquiryDialogViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<InquiryDialogViewController>();
            Container.BindInterfacesTo<InquiryDialogPresenter>().AsCached();
        }
    }
}
