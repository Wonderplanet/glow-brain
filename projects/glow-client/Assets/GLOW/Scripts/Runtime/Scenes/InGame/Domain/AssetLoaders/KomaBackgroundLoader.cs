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
    public interface IKomaBackgroundLoader
    {
        UniTask Load(KomaBackgroundAssetKey assetKey, CancellationToken cancellationToken);
        void Unload();
    }

    public interface IKomaBackgroundContainer
    {
        Sprite Get(KomaBackgroundAssetKey assetKey);
    }

    public class KomaBackgroundLoader : IKomaBackgroundLoader, IKomaBackgroundContainer
    {
        [Inject(Id = TemplateInjectId.AssetContainer.InGame)] IAssetReferenceContainerRepository AssetReferenceContainerRepository { get; }

        string ContainerKey => GlowAssetReferenceContainerId.KomaBackground;

        async UniTask IKomaBackgroundLoader.Load(KomaBackgroundAssetKey assetKey, CancellationToken cancellationToken)
        {
            var assetPath = KomaBackgroundAssetPath.FromAssetKey(assetKey);

            await AssetReferenceContainerRepository.Load<Sprite>(
                cancellationToken,
                ContainerKey,
                assetPath.Value);
        }
        
        void IKomaBackgroundLoader.Unload()
        {
            var referenceContainer = AssetReferenceContainerRepository.Get<Sprite>(ContainerKey);
            referenceContainer?.Unload();
        }

        Sprite IKomaBackgroundContainer.Get(KomaBackgroundAssetKey assetKey)
        {
            var assetPath = KomaBackgroundAssetPath.FromAssetKey(assetKey);

            return AssetReferenceContainerRepository.Get<Sprite>(ContainerKey).Get(assetPath.Value);
        }
    }
}
