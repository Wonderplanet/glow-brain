using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Constants.Zenject;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.Home.Domain.ValueObjects;
using UnityEngine;
using UnityEngine.AddressableAssets;
using WPFramework.Domain.Repositories;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.Home.Domain.AssetLoader
{
    public interface IHomeMainKomaPatternLoader
    {
        UniTask Load(CancellationToken cancellationToken, HomeMainKomaPatternAssetPath assetPath);
        void Unload();
    }

    public interface IHomeMainKomaPatternContainer
    {
        bool Exists(HomeMainKomaPatternAssetPath path);
        GameObject Get(HomeMainKomaPatternAssetPath path);
    }

    public class HomeMainKomaPatternLoader:
        IHomeMainKomaPatternLoader,
        IHomeMainKomaPatternContainer
    {
        [Inject(Id = TemplateInjectId.AssetContainer.OutGame)]
        IAssetReferenceContainerRepository AssetReferenceContainerRepository { get; }
        string ContainerKey => GlowAssetReferenceContainerId.HomeMainKomaPattern;

        List<HomeMainKomaPatternAssetPath> registeredAssetPaths = new();

        async UniTask IHomeMainKomaPatternLoader.Load(CancellationToken cancellationToken, HomeMainKomaPatternAssetPath assetPath)
        {
            try
            {
                await AssetReferenceContainerRepository.Load<GameObject>(
                    cancellationToken,
                    ContainerKey,
                    assetPath.Value);
                registeredAssetPaths.Add(assetPath);
            }
            catch (OperationCanceledException)
            {
                throw;
            }
            catch (InvalidKeyException)
            {
                // この時点でエラーログが出てるので、ここでは何もしない
            }
            catch (Exception e)
            {
                ApplicationLog.LogError(nameof(HomeMainKomaPatternLoader), e.ToString());
            }
        }

        void IHomeMainKomaPatternLoader.Unload()
        {
            var referenceContainer = AssetReferenceContainerRepository.Get<GameObject>(ContainerKey);
            referenceContainer?.Unload();
            registeredAssetPaths.Clear();
        }

        bool IHomeMainKomaPatternContainer.Exists(HomeMainKomaPatternAssetPath path)
        {
            return registeredAssetPaths.Contains(path);
        }

        GameObject IHomeMainKomaPatternContainer.Get(HomeMainKomaPatternAssetPath path)
        {
            return AssetReferenceContainerRepository.Get<GameObject>(ContainerKey)?.Get(path.Value);
        }
    }
}
