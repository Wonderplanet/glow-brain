using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Constants.Zenject;
using GLOW.Core.Domain.Constants;
using GLOW.Scenes.HomeHelpDialog.Domain.ScriptableObjects;
using GLOW.Scenes.HomeHelpDialog.Domain.ValueObjects;
using WPFramework.Domain.Repositories;
using Zenject;

namespace GLOW.Scenes.HomeHelpDialog.Domain.AssetLoaders
{
    public class HomeHelpInfoListAssetLoader : IHomeHelpInfoListAssetLoader
    {
        // TODO: AssetContainerをInGameとHomeに分けたい
        [Inject(Id = TemplateInjectId.AssetContainer.InGame)] IAssetReferenceContainerRepository AssetReferenceContainerRepository { get; }

        const string ContainerKey = GlowAssetReferenceContainerId.HomeHelpInfoList;

        async UniTask<HomeHelpInfoList> IHomeHelpInfoListAssetLoader.Load(CancellationToken cancellationToken, HomeHelpInfoAssetKey assetKey)
        {
            await AssetReferenceContainerRepository
                .Load<HomeHelpInfoList>(cancellationToken, ContainerKey, assetKey.Value);

            return AssetReferenceContainerRepository
                .Get<HomeHelpInfoList>(ContainerKey)
                .Get(assetKey.Value);
        }

        void IHomeHelpInfoListAssetLoader.Unload(HomeHelpInfoAssetKey assetKey)
        {
            AssetReferenceContainerRepository
                .Unload(ContainerKey, assetKey.Value);
        }
    }
}
