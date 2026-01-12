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
    public interface IMangaAnimationLoader
    {
        UniTask Load(MangaAnimationAssetKey assetKey, CancellationToken cancellationToken);
        void Unload();
    }

    public interface IMangaAnimationContainer
    {
        GameObject Get(MangaAnimationAssetKey assetKey);
    }

    public class MangaAnimationLoader : IMangaAnimationLoader, IMangaAnimationContainer
    {
        [Inject(Id = TemplateInjectId.AssetContainer.InGame)] IAssetReferenceContainerRepository AssetReferenceContainerRepository { get; }

        string ContainerKey => GlowAssetReferenceContainerId.MangaAnimation;

        async UniTask IMangaAnimationLoader.Load(MangaAnimationAssetKey assetKey, CancellationToken cancellationToken)
        {
            if (assetKey.IsEmpty()) return;

            var assetPath = MangaAnimationAssetPath.FromAssetKey(assetKey);

            await AssetReferenceContainerRepository.Load<GameObject>(
                cancellationToken,
                ContainerKey,
                assetPath.Value);
        }
        
        void IMangaAnimationLoader.Unload()
        {
            var referenceContainer = AssetReferenceContainerRepository.Get<GameObject>(ContainerKey);
            referenceContainer?.Unload();
        }

        GameObject IMangaAnimationContainer.Get(MangaAnimationAssetKey assetKey)
        {
            if (assetKey.IsEmpty()) return null;

            var assetPath = MangaAnimationAssetPath.FromAssetKey(assetKey);

            return AssetReferenceContainerRepository.Get<GameObject>(ContainerKey).Get(assetPath.Value);
        }
    }
}
