using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Constants.Zenject;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using UnityEngine;
using WPFramework.Domain.Repositories;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.AssetLoaders
{
    public interface IDefenseTargetImageLoader
    {
        UniTask Load(DefenseTargetAssetKey assetKey, CancellationToken cancellationToken);
        void Unload();
    }

    public interface IDefenseTargetImageContainer
    {
        GameObject Get(DefenseTargetAssetKey assetKey);
    }

    public class DefenseTargetImageLoader : IDefenseTargetImageLoader, IDefenseTargetImageContainer
    {
        [Inject(Id = TemplateInjectId.AssetContainer.InGame)] IAssetReferenceContainerRepository AssetReferenceContainerRepository { get; }

        string ContainerKey => GlowAssetReferenceContainerId.DefenseTargetImage;

        async UniTask IDefenseTargetImageLoader.Load(DefenseTargetAssetKey assetKey, CancellationToken cancellationToken)
        {
            var assetPath = DefenseTargetAssetPath.FromAssetKey(assetKey);
            await AssetReferenceContainerRepository.Load<GameObject>(cancellationToken, ContainerKey, assetPath.Value);
        }
        
        void IDefenseTargetImageLoader.Unload()
        {
            var referenceContainer = AssetReferenceContainerRepository.Get<GameObject>(ContainerKey);
            referenceContainer?.Unload();
        }

        GameObject IDefenseTargetImageContainer.Get(DefenseTargetAssetKey assetKey)
        {
            var assetPath = DefenseTargetAssetPath.FromAssetKey(assetKey);
            return AssetReferenceContainerRepository.Get<GameObject>(ContainerKey).Get(assetPath.Value);
        }
    }
}
