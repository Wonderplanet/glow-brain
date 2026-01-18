using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using UnityEngine;
using WonderPlanet.ResourceManagement;
using Zenject;

namespace GLOW.Scenes.EventQuestSelect.Presentation
{
    public interface IEventQuestBackGroundLoader
    {
        UniTask<EventQuestBackgroundComponent> LoadBackGround(EventAssetKey assetKey, CancellationToken ct);
        void Clear();
    }

    public class EventQuestBackGroundLoader : IEventQuestBackGroundLoader, IDisposable
    {
        IAssetSource _assetSource;
        IAssetReference<GameObject> _retainedBgPrefab;
        CancellationTokenSource _clearedCancellationTokenSource = new();
        [Inject]
        public void Inject(IAssetSource assetSource)
        {
            _assetSource = assetSource;
        }

        async UniTask<EventQuestBackgroundComponent> IEventQuestBackGroundLoader.LoadBackGround(EventAssetKey assetKey, CancellationToken ct)
        {
            try
            {
                ClearCancellationToken();

                _clearedCancellationTokenSource = new CancellationTokenSource();
                //using...このメソッドのスコープになるので、メソッド外れたらDisposeされる
                using var ctSource = CancellationTokenSource.CreateLinkedTokenSource(_clearedCancellationTokenSource.Token, ct);

                var bgPrefabReference = await _assetSource.GetAsset<GameObject>(ctSource.Token, EventBackgroundAssetPath.ToBackgroundAssetPath(assetKey).Value);
                if (_retainedBgPrefab != null &&_retainedBgPrefab.Value == bgPrefabReference.Value)
                {
                    var bgComponent = _retainedBgPrefab.Value.GetComponent<EventQuestBackgroundComponent>();
                    if (bgComponent == null)
                    {
                        Debug.LogWarning("Try get EventBackgroundComponent but component is null");
                    }
                    return bgComponent;
                }
                bgPrefabReference.Retain();

                _retainedBgPrefab = bgPrefabReference;
                var result = bgPrefabReference.Value.GetComponent<EventQuestBackgroundComponent>();
                if (result == null)
                {
                    Debug.LogWarning("Try get EventBackgroundComponent but component is null");
                }
                return result;
            }
            catch (Exception e)
            {
                Debug.LogError("Called Exception BackGroundLoader at: " + e);
                return null;
            }
        }

        void IEventQuestBackGroundLoader.Clear()
        {
            _retainedBgPrefab?.Release();
            ClearCancellationToken();
        }
        void ClearCancellationToken()
        {
            _clearedCancellationTokenSource?.Cancel();
            _clearedCancellationTokenSource?.Dispose();
            _clearedCancellationTokenSource = null;
        }

        // このIDisposableはどのタイミングで呼ばれる？
        // Container.Bindをして、このコードが含まれたContainerが破棄されたとき呼ばれる
        void IDisposable.Dispose()
        {
            //finallyとかだと同じフレームにreleaseされるので、Disposeでやる
            _retainedBgPrefab?.Release();
            ClearCancellationToken();
        }
    }
}
