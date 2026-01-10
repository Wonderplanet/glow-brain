using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects.Gacha;
using UnityEngine;
using WonderPlanet.ResourceManagement;
using Zenject;

namespace GLOW.Scenes.GachaList.Presentation.Views
{
    public interface IFestivalGachaBannerImageLoader
    {
        UniTask<FestivalGachaBannerImageComponent> LoadBannerImage(FestivalGachaBannerAssetPath assetPath, CancellationToken ct);
        void Clear();
    }

    public class FestivalGachaBannerImageLoader : IFestivalGachaBannerImageLoader, IDisposable
    {
        IAssetSource _assetSource;
        Dictionary<string, IAssetReference<GameObject>> _retainedBannerPrefabs = new();
        CancellationTokenSource _disposedCancellationTokenSource = new();

        [Inject]
        public void Inject(IAssetSource assetSource)
        {
            _assetSource = assetSource;
        }

        async UniTask<FestivalGachaBannerImageComponent> IFestivalGachaBannerImageLoader.LoadBannerImage(
            FestivalGachaBannerAssetPath assetPath,
            CancellationToken ct)
        {
            try
            {
                using var ctSource =
                    CancellationTokenSource.CreateLinkedTokenSource(_disposedCancellationTokenSource.Token, ct);

                if (_retainedBannerPrefabs.TryGetValue(assetPath.Value, out var cachedReference))
                {
                    var cachedComponent = cachedReference.Value.GetComponent<FestivalGachaBannerImageComponent>();
                    if (cachedComponent == null)
                    {
                        Debug.LogWarning("Try get FestivalGachaBannerImageComponent but component is null");
                    }
                    return cachedComponent;
                }

                var bannerPrefabReference =
                    await _assetSource.GetAsset<GameObject>(ctSource.Token, assetPath.Value);

                bannerPrefabReference.Retain();
                _retainedBannerPrefabs[assetPath.Value] = bannerPrefabReference;

                var result = bannerPrefabReference.Value.GetComponent<FestivalGachaBannerImageComponent>();
                if (result == null)
                {
                    Debug.LogWarning("Try get FestivalGachaBannerImageComponent but component is null");
                }
                return result;
            }
            catch (Exception e)
            {
                Debug.LogError("Called Exception FestivalGachaBannerImageLoader at: " + e);
                return null;
            }
        }

        void IFestivalGachaBannerImageLoader.Clear()
        {
            foreach (var prefab in _retainedBannerPrefabs.Values)
            {
                prefab?.Release();
            }
            _retainedBannerPrefabs.Clear();
        }

        void IDisposable.Dispose()
        {
            foreach (var prefab in _retainedBannerPrefabs.Values)
            {
                prefab?.Release();
            }
            _retainedBannerPrefabs.Clear();
            
            _disposedCancellationTokenSource?.Cancel();
            _disposedCancellationTokenSource?.Dispose();
            _disposedCancellationTokenSource = null;
        }
    }
}

