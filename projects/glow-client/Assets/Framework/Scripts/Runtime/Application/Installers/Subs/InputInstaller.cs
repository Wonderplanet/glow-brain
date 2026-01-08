using WPFramework.Presentation.Modules;
using Zenject;

namespace WPFramework.Application.Installers
{
    internal sealed class InputInstaller : Installer
    {
        public override void InstallBindings()
        {
            // NOTE: Escapeキー処理をインストール
            Container.BindInterfacesTo<EscapeResponseDispatcher>()
                .FromNewComponentOnNewGameObject()
                .AsCached()
                .NonLazy();
            Container.Bind<UIEscapeResponder>().AsCached().NonLazy();
        }
    }
}
