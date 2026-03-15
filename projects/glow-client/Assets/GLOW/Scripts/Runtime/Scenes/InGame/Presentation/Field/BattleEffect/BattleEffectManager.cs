using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Scenes.InGame.Presentation.Constants;
using GLOW.Scenes.InGame.Presentation.Data;
using UnityEngine;
using UnityEngine.AddressableAssets;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    public class BattleEffectManager : MonoBehaviour
    {
        [SerializeField] Transform _root;
        [SerializeField] AssetReference _battleEffectInfoListReference; 

        BattleEffectInfoList _battleEffectInfoList;
        List<BaseBattleEffectView> _effects = new ();

        public Transform EffectLayer => _root;

        void Awake()
        {
            SetGlobalZPosition(_root, FieldZPositionDefinitions.EffectRoot);
        }

        void OnDestroy()
        {
            ReleaseBattleEffectInfoList();
        }

        public async UniTask Initialize(CancellationToken cancellationToken)
        {
            await LoadBattleEffectInfoList(cancellationToken);
        }

        public BaseBattleEffectView Generate(BattleEffectId id, Vector3 worldPos)
        {
            var prefab = GetEffectPrefab(id);
            if (prefab == null) return null;

            var effect = Instantiate(prefab, _root);
            _effects.Add(effect);

            effect.AddCompletedAction(() => _effects.Remove(effect));
            effect.transform.position = worldPos;

            return effect;
        }

        public BaseBattleEffectView Generate(BattleEffectId id, Transform parent, Vector3 localPos)
        {
            var prefab = GetEffectPrefab(id);
            return Generate(prefab, parent, localPos);
        }

        public BaseBattleEffectView Generate(GameObject prefab, Transform parent, Vector3 localPos)
        {
            if (prefab == null || parent == null) return null;

            var effect = prefab.GetComponent<BaseBattleEffectView>();
            return Generate(effect, parent, localPos);
        }

        public BaseBattleEffectView Generate(BaseBattleEffectView battleEffectView, Transform parent, Vector3 localPos)
        {
            if (battleEffectView == null) return null;

            var effect = Instantiate(battleEffectView, parent);
            _effects.Add(effect);

            effect.AddCompletedAction(() => _effects.Remove(effect));
            effect.transform.localPosition = localPos;

            return effect;
        }

        public BaseBattleEffectView Generate(BattleEffectId id, Transform parent)
        {
            return Generate(id, parent, Vector3.zero);
        }

        public BaseBattleEffectView Generate(GameObject prefab, Transform parent)
        {
            return Generate(prefab, parent, Vector3.zero);
        }

        public bool Exists(BattleEffectId id)
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

        BaseBattleEffectView GetEffectPrefab(BattleEffectId id)
        {
            if(_battleEffectInfoList == null) return null;
            
            var info = _battleEffectInfoList.List.Find(info => info.Id == id);
            return info?.Prefab;
        }
        
        void SetGlobalZPosition(Transform gameObjectTransform, float z)
        {
            var pos = gameObjectTransform.position;
            pos.z = z;
            gameObjectTransform.position = pos;
        }
        
        async UniTask LoadBattleEffectInfoList(CancellationToken cancellationToken)
        {
            ReleaseBattleEffectInfoList();

            await _battleEffectInfoListReference
                .LoadAssetAsync<BattleEffectInfoList>()
                .WithCancellation(cancellationToken);
                
            _battleEffectInfoList = (BattleEffectInfoList)_battleEffectInfoListReference.Asset;
        }

        void ReleaseBattleEffectInfoList()
        {
            if (_battleEffectInfoListReference.IsValid()) _battleEffectInfoListReference.ReleaseAsset();
            _battleEffectInfoList = null;
        }
    }
}
