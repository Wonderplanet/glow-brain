using WonderPlanet.ResourceManagement;
using WonderPlanet.ResourceManagement.Spine;
using WPFramework.Data.DataStores;
using Zenject;

namespace WPFramework.Application.Installers
{
    internal sealed class AssetBundleInstaller : Installer
    {
        public override void InstallBindings()
        {
            // NOTE: アセットバンドルの管理システムをインストール
            //       現在は暗号化なしの状態で利用する
            Container.BindInterfacesTo<AddressableAssetManager>().AsCached();
            Container.BindInterfacesTo<BannerManager>().FromNewComponentOnNewGameObject().AsCached();

            // NOTE: アセットコンテナのデータストアをインストール
            Container.BindInterfacesTo<AssetReferenceContainerDataStore>().AsTransient();

            // NOTE: スプライトのロードサポートをインストール
            Container.BindInterfacesTo<SpriteLoadSupport>().AsCached();
            Container.BindInterfacesTo<BannerLoadSupport>().AsCached();

            // NOTE: Spineのロードサポートをインストール
            Container.BindInterfacesTo<SkeletonGraphicLoadSupport>().AsCached();
        }
    }
}
