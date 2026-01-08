using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Scenes.InGame.Presentation.Data;
using UnityEngine;
using UnityEngine.AddressableAssets;

namespace GLOW.Scenes.InGame.Presentation.UI.UIEffect
{
    public class UIEffectManager : MonoBehaviour
    {
        [SerializeField] AssetReference _uiEffectInfoListReference;

        UIEffectInfoList _uiEffectInfoList;
        List<BaseUIEffectView> _effects = new ();

        void OnDestroy()
        {
            ReleaseUIEffectInfoList();
        }

        public async UniTask Initialize(CancellationToken cancellationToken)
        {
            await LoadUIEffectInfoList(cancellationToken);
        }

        public BaseUIEffectView Generate(UIEffectId id, Transform parent, Vector3 localPos)
        {
            var prefab = GetEffectPrefab(id);
            return Generate(prefab, parent, localPos);
        }

        public BaseUIEffectView Generate(GameObject prefab, Transform parent, Vector3 localPos)
        {
            if (prefab == null || parent == null) return null;

            var effect = prefab.GetComponent<BaseUIEffectView>();
            return Generate(effect, parent, localPos);
        }

        public BaseUIEffectView Generate(BaseUIEffectView uiEffectView, Transform parent, Vector3 localPos)
        {
            if (uiEffectView == null) return null;

            var effect = Instantiate(uiEffectView, parent);
            _effects.Add(effect);

            effect.AddCompletedAction(() => _effects.Remove(effect));
            effect.transform.localPosition = localPos;

            return effect;
        }

        public BaseUIEffectView Generate(UIEffectId id, Transform parent)
        {
            return Generate(id, parent, Vector3.zero);
        }

        public BaseUIEffectView Generate(GameObject prefab, Transform parent)
        {
            return Generate(prefab, parent, Vector3.zero);
        }

        public bool Exists(UIEffectId id)
        {
            return GetEffectPrefab(id) != null;
        }

        public MultipleSwitchHandler PauseAllEffects(MultipleSwitchHandler handler)
        {
            foreach (var effect in _effects )
            {
                if (effect == null) continue;
                effect.Pause(handler);
            }

            return handler;
        }

        BaseUIEffectView GetEffectPrefab(UIEffectId id)
        {
            if(_uiEffectInfoList == null) return null;

            var info = _uiEffectInfoList.List.Find(info => info.Id == id);
            return info?.Prefab;
        }

        async UniTask LoadUIEffectInfoList(CancellationToken cancellationToken)
        {
            ReleaseUIEffectInfoList();

            await _uiEffectInfoListReference
                .LoadAssetAsync<UIEffectInfoList>()
                .WithCancellation(cancellationToken);

            _uiEffectInfoList = (UIEffectInfoList)_uiEffectInfoListReference.Asset;
        }

        void ReleaseUIEffectInfoList()
        {
            if (_uiEffectInfoListReference.IsValid()) _uiEffectInfoListReference.ReleaseAsset();
            _uiEffectInfoList = null;
        }
    }
}

