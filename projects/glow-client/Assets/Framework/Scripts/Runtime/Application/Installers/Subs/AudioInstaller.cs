using WPFramework.Constants.Zenject;
using WPFramework.Data.Repositories;
using WPFramework.Domain.Modules;
using WPFramework.Domain.Repositories;
using Zenject;

namespace WPFramework.Application.Installers
{
    internal sealed class AudioInstaller : Installer
    {
        public override void InstallBindings()
        {
            // NOTE: Audio関連をインストール
            Container.Bind<IAssetReferenceContainerRepository>()
                .WithId(FrameworkInjectId.AssetContainer.Audio)
                .To<AssetReferenceContainerRepository>()
                .AsCached();
            Container.BindInterfacesTo<UnityAudioProcessor>().AsCached();
        }
    }
}
