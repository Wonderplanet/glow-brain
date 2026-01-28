using System;
using WPFramework.Application.Modules;
using WPFramework.Presentation.Modules;
using WPFramework.Presentation.Views;
using Zenject;

namespace WPFramework.Application.Installers
{
    internal sealed class UIInstaller : Installer
    {
        public override void InstallBindings()
        {
            // NOTE: UIのスプライトロードをサポートをインストール
            Container.Bind<UISpriteUtil>().AsCached().NonLazy();
            Container.Bind<UIBannerUtil>().AsCached().NonLazy();

            // NOTE: UIのSpineロードをサポートをインストール
            Container.Bind<UISkeletonGraphicUtil>().AsCached().NonLazy();

            // NOTE: UIKitのViewの音再生サポートをインストール
            Container.Bind<UISoundEffector>().AsCached().NonLazy();

            // NOTE: UIKitのViewFactoryのサポートをインストール
            Container.BindInterfacesTo<ViewFactory>().AsCached();

            var eventObserver = new ModalPresentationObserver();
            Container.BindInstance(eventObserver).AsCached();
            Container.Bind<IUIModalPresentationObserver>().FromInstance(eventObserver).AsCached();
            Container.Bind<IDisposable>().FromInstance(eventObserver).AsCached();
        }
    }
}
