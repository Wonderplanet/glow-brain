using GLOW.Core.Presentation.Modules;
using GLOW.Modules.MessageView.Presentation;
using Zenject;

namespace GLOW.Core.Application.Installers.Subs
{
    public sealed class GlowUIInstaller : Installer
    {
        public override void InstallBindings()
        {
            // NOTE: UIのスプライトロードをサポートをインストール
            Container.Bind<UIBannerLoaderEx>().AsCached().NonLazy();


            // NOTE: Alert表示サポートをインストール
            Container.BindInterfacesTo<MessageViewUtil>().AsCached();
        }
    }
}
