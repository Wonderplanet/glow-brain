using GLOW.Core.Domain.Repositories;
using GLOW.Scenes.Title.Presentations.Modules.Systems;
using WPFramework.Modules.Log;
using GLOW.Scenes.Title.Presentations.Views;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.Title.Applications.Installers
{
    internal sealed class TitleSceneInstaller : MonoInstaller<TitleSceneInstaller>
    {
        [Inject] IUnitSortFilterCacheRepository UnitSortFilterCacheRepository { get; }

        public override void InstallBindings()
        {
            ApplicationLog.Log(nameof(TitleSceneInstaller), nameof(InstallBindings));

            Container.BindInterfacesTo<ViewFactory>().AsCached();
            Container.BindViewFactoryInfo<TitleViewController, TitleViewControllerInstaller>();

            Container.BindInterfacesTo<TitleContentMaintenanceHandler>().AsCached();

            UnitSortFilterCacheRepository.Clear();
        }
    }
}
