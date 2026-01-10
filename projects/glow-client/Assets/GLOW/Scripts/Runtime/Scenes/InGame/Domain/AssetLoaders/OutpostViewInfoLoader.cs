using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Constants.Zenject;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.ScriptableObjects;
using WonderPlanet.ResourceManagement;
using WPFramework.Domain.Repositories;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.AssetLoaders
{
    public interface IOutpostViewInfoLoader
    {
        UniTask Load(OutpostAssetKey assetKey, CancellationToken cancellationToken);
        void Unload();
    }

    public interface IOutpostViewInfoContainer
    {
        OutpostViewInfo Get(OutpostAssetKey assetKey);
    }

    public class OutpostViewInfoLoader : IOutpostViewInfoLoader, IOutpostViewInfoContainer
    {
        [Inject(Id = TemplateInjectId.AssetContainer.InGame)] IAssetReferenceContainerRepository AssetReferenceContainerRepository { get; }
        [Inject] IAssetSource AssetSource { get; }

        string ContainerKey => GlowAssetReferenceContainerId.OutpostViewInfo;

        public async UniTask Load(OutpostAssetKey assetKey, CancellationToken cancellationToken)
        {
            var assetPath = OutpostViewInfoAssetPath.FromAssetKey(assetKey);
            await AssetReferenceContainerRepository.Load<OutpostViewInfo>(
                cancellationToken,
                ContainerKey,
                assetPath.Value);
        }
        
        void IOutpostViewInfoLoader.Unload()
        {
            var referenceContainer = AssetReferenceContainerRepository.Get<OutpostViewInfo>(ContainerKey);
            referenceContainer?.Unload();
        }

        public OutpostViewInfo Get(OutpostAssetKey assetKey)
        {
            var assetPath = OutpostViewInfoAssetPath.FromAssetKey(assetKey);
            return AssetReferenceContainerRepository.Get<OutpostViewInfo>(ContainerKey).Get(assetPath.Value);
        }
    }
}
