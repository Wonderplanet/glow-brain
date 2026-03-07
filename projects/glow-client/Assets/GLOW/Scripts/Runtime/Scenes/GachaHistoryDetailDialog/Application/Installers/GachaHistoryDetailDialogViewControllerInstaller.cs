using GLOW.Core.Data.Services;
using GLOW.Scenes.GachaHistoryDetailDialog.Presentation.Presenters;
using GLOW.Scenes.GachaHistoryDetailDialog.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.GachaHistoryDetailDialog.Application.Installers
{
    public class GachaHistoryDetailDialogViewControllerInstaller : Installer
    {
        [Inject] GachaHistoryDetailDialogViewController.Argument Argument { get; set; }
        
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<GachaHistoryDetailDialogViewController>();
            Container.BindInstance(Argument);
            Container.BindInterfacesTo<GachaHistoryDetailDialogPresenter>().AsCached();
        }
    }
}