using Zenject;
using UIKit.ZenjectBridge;
using GLOW.Scenes.DiamondPurchaseHistory.Presentation;
using GLOW.Scenes.DiamondPurchaseHistory.Domain;

namespace GLOW.Scenes.DiamondPurchaseHistory.Application
{
    public class DiamondPurchaseHistoryViewInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<DiamondPurchaseHistoryViewController>();
            Container.BindInterfacesTo<DiamondPurchaseHistoryPresenter>().AsCached();
            Container.Bind<DiamondPurchaseHistoryUseCase>().AsCached();
        }
    }
}
