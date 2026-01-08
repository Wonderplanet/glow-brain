using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Constants.Zenject;
using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UnityEngine;
using WonderPlanet.ResourceManagement;
using WPFramework.Domain.Repositories;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.AssetLoaders
{
    public interface IKomaEffectPrefabLoader
    {
        UniTask Load(KomaEffectAssetKey assetKey, CancellationToken cancellationToken);
        void Unload();
    }

    public interface IKomaEffectPrefabContainer
    {
        bool IsFrontPrefabLoaded(KomaEffectAssetKey assetKey);
        bool IsBackPrefabLoaded(KomaEffectAssetKey assetKey);
        GameObject GetFrontPrefab(KomaEffectAssetKey assetKey);
        GameObject GetBackPrefab(KomaEffectAssetKey assetKey);
    }

    public class KomaEffectPrefabLoader : IKomaEffectPrefabLoader, IKomaEffectPrefabContainer
    {
        [Inject(Id = TemplateInjectId.AssetContainer.InGame)] IAssetReferenceContainerRepository AssetReferenceContainerRepository { get; }
        [Inject] IAssetSource AssetSource { get; }

        string ContainerKey => GlowAssetReferenceContainerId.KomaEffectPrefab;

        public UniTask Load(KomaEffectAssetKey assetKey, CancellationToken cancellationToken)
        {
            var frontEffectAssetPath = KomaEffectFrontPrefabAssetPath.FromAssetKey(assetKey);
            var backEffectAssetPath = KomaEffectBackPrefabAssetPath.FromAssetKey(assetKey);

            var taskList = new List<UniTask>();

            if (AssetSource.IsAddressExists(frontEffectAssetPath.Value))
            {
                var frontEffectLoadTask = AssetReferenceContainerRepository.Load<GameObject>(
                    cancellationToken,
                    ContainerKey,
                    frontEffectAssetPath.Value);

                taskList.Add(frontEffectLoadTask);
            }

            if (AssetSource.IsAddressExists(backEffectAssetPath.Value))
            {
                var backEffectLoadTask = AssetReferenceContainerRepository.Load<GameObject>(
                    cancellationToken,
                    ContainerKey,
                    backEffectAssetPath.Value);

                taskList.Add(backEffectLoadTask);
            }

            return UniTask.WhenAll(taskList);
        }
        
        void IKomaEffectPrefabLoader.Unload()
        {
            var referenceContainer = AssetReferenceContainerRepository.Get<GameObject>(ContainerKey);
            referenceContainer?.Unload();
        }

        bool IKomaEffectPrefabContainer.IsFrontPrefabLoaded(KomaEffectAssetKey assetKey)
        {
            var assetPath = KomaEffectFrontPrefabAssetPath.FromAssetKey(assetKey);
            return AssetSource.IsLoaded(assetPath.Value);
        }

        bool IKomaEffectPrefabContainer.IsBackPrefabLoaded(KomaEffectAssetKey assetKey)
        {
            var assetPath = KomaEffectBackPrefabAssetPath.FromAssetKey(assetKey);
            return AssetSource.IsLoaded(assetPath.Value);
        }

        public GameObject GetFrontPrefab(KomaEffectAssetKey assetKey)
        {
            var assetPath = KomaEffectFrontPrefabAssetPath.FromAssetKey(assetKey);
            return AssetReferenceContainerRepository.Get<GameObject>(ContainerKey).Get(assetPath.Value);
        }

        public GameObject GetBackPrefab(KomaEffectAssetKey assetKey)
        {
            var assetPath = KomaEffectBackPrefabAssetPath.FromAssetKey(assetKey);
            return AssetReferenceContainerRepository.Get<GameObject>(ContainerKey).Get(assetPath.Value);
        }
    }
}
