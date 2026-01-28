using Zenject;
using UIKit.ZenjectBridge;
using GLOW.Scenes.DataRepair.Presentation;
using GLOW.Scenes.DataRepair.Domain;

namespace GLOW.Scenes.DataRepair.Application
{
    public class DataRepairViewInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<DataRepairViewController>();
            Container.BindInterfacesTo<DataRepairPresenter>().AsCached();
            Container.Bind<CacheDeleteUseCase>().AsCached();
        }
    }
}
