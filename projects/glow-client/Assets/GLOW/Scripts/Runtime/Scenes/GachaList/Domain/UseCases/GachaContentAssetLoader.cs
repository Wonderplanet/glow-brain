using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Constants.Zenject;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using UnityEngine;
using WPFramework.Domain.Repositories;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.GachaList.Domain.UseCases
{
    public interface IGachaContentAssetLoader
    {
        UniTask Load(CancellationToken cancellationToken, GachaContentAssetPath assetPath);
        void Unload();
    }

    public interface IGachaContentAssetContainer
    {
        bool Exists(GachaContentAssetPath assetPath);
        GameObject Get(GachaContentAssetPath assetPath);
    }

    public class GachaContentAssetLoader : IGachaContentAssetLoader, IGachaContentAssetContainer
    {
        [Inject(Id = TemplateInjectId.AssetContainer.OutGame)] IAssetReferenceContainerRepository AssetReferenceContainerRepository { get; }

        string ContainerKey => GlowAssetReferenceContainerId.GachaContentAssetInfo;

        // 登録されたAsstPath List
        List<GachaContentAssetPath> registeredAssetPaths = new();

        async UniTask IGachaContentAssetLoader.Load(CancellationToken cancellationToken, GachaContentAssetPath assetPath)
        {
            await AssetReferenceContainerRepository.Load<GameObject>(cancellationToken, ContainerKey, assetPath.Value);
            registeredAssetPaths.Add(assetPath);
            ApplicationLog.Log(nameof(GachaContentAssetLoader), $"GachaAssetLoader Load Completed / {assetPath.Value}");

        }

        void IGachaContentAssetLoader.Unload()
        {
            var referenceContainer = AssetReferenceContainerRepository.Get<GameObject>(ContainerKey);
            referenceContainer?.Unload();
            registeredAssetPaths.Clear();
        }

        bool IGachaContentAssetContainer.Exists(GachaContentAssetPath assetPath)
        {
            return registeredAssetPaths.Contains(assetPath);
        }

        GameObject IGachaContentAssetContainer.Get(GachaContentAssetPath assetPath)
        {
            return AssetReferenceContainerRepository.Get<GameObject>(ContainerKey).Get(assetPath.Value);
        }

    }
}
