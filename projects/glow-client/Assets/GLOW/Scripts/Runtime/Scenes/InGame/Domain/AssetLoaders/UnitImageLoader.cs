using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Constants.Zenject;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using UnityEngine;
using WPFramework.Domain.Repositories;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.AssetLoaders
{
    public interface IUnitImageLoader
    {
        UniTask Load(CancellationToken cancellationToken, UnitImageAssetPath imageAssetPath);
        void Unload();
    }

    public interface IUnitImageContainer
    {
        GameObject Get(UnitImageAssetPath imageAssetPath);
    }

    public class UnitImageLoader : IUnitImageLoader, IUnitImageContainer
    {
        [Inject(Id = TemplateInjectId.AssetContainer.InGame)] IAssetReferenceContainerRepository AssetReferenceContainerRepository { get; }

        string ContainerKey => GlowAssetReferenceContainerId.UnitImage;

        async UniTask IUnitImageLoader.Load(CancellationToken cancellationToken, UnitImageAssetPath imageAssetPath)
        {
            await AssetReferenceContainerRepository.Load<GameObject>(cancellationToken, ContainerKey, imageAssetPath.Value);
        }
        
        void IUnitImageLoader.Unload()
        {
            var referenceContainer = AssetReferenceContainerRepository.Get<GameObject>(ContainerKey);
            referenceContainer?.Unload();
        }

        GameObject IUnitImageContainer.Get(UnitImageAssetPath imageAssetPath)
        {
            return AssetReferenceContainerRepository.Get<GameObject>(ContainerKey).Get(imageAssetPath.Value);
        }

    }
}
