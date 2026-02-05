using WonderPlanet.SceneManagement;
using WPFramework.Presentation.Transitions;
using Zenject;

namespace WPFramework.Application.Installers
{
    internal sealed class SceneTransitionInstaller : Installer
    {
        public override void InstallBindings()
        {
            // NOTE: シーン管理システムをインストール
            Container.BindInterfacesTo<SceneNavigationController>().AsCached();

            // NOTE: シーン遷移時のトランジションを生成するファクトリをインストール
            Container.BindInterfacesTo<TransitionFactory>().AsCached();
        }
    }
}
