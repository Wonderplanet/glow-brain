using GLOW.Core.Data.Services;
using GLOW.Scenes.GachaHistoryDialog.Presentation.Presenters;
using GLOW.Scenes.GachaHistoryDialog.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.GachaHistoryDialog.Application.Installers
{
    public class GachaHistoryDialogViewControllerInstaller : Installer
    {
        [Inject] GachaHistoryDialogViewController.Argument Argument { get; set; }
        
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<GachaHistoryDialogViewController>();
            Container.BindInstance(Argument);
            Container.BindInterfacesTo<GachaHistoryDialogPresenter>().AsCached();
        }
    }
}