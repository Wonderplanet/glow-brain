using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Constants.Zenject;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using UnityEngine;
using WPFramework.Domain.Repositories;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.AssetLoaders
{
    public interface IInGameGimmickObjectImageLoader
    {
        UniTask Load(InGameGimmickObjectAssetKey assetKey, CancellationToken cancellationToken);
        void Unload();
    }

    public interface IInGameGimmickObjectImageContainer
    {
        GameObject Get(InGameGimmickObjectAssetKey assetKey);
    }

    public class InGameGimmickObjectImageLoader : IInGameGimmickObjectImageLoader, IInGameGimmickObjectImageContainer
    {
        [Inject(Id = TemplateInjectId.AssetContainer.InGame)] IAssetReferenceContainerRepository AssetReferenceContainerRepository { get; }

        string ContainerKey => GlowAssetReferenceContainerId.InGameGimmickObjectImage;

        async UniTask IInGameGimmickObjectImageLoader.Load(InGameGimmickObjectAssetKey assetKey, CancellationToken cancellationToken)
        {
            var assetPath = InGameGimmickObjectAssetPath.FromAssetKey(assetKey);
            await AssetReferenceContainerRepository.Load<GameObject>(cancellationToken, ContainerKey, assetPath.Value);
        }
        
        void IInGameGimmickObjectImageLoader.Unload()
        {
            var referenceContainer = AssetReferenceContainerRepository.Get<GameObject>(ContainerKey);
            referenceContainer?.Unload();
        }

        GameObject IInGameGimmickObjectImageContainer.Get(InGameGimmickObjectAssetKey assetKey)
        {
            var assetPath = InGameGimmickObjectAssetPath.FromAssetKey(assetKey);
            return AssetReferenceContainerRepository.Get<GameObject>(ContainerKey).Get(assetPath.Value);
        }
    }
}
