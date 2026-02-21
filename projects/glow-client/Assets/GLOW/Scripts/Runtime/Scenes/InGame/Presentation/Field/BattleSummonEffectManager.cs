using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Scenes.InGame.Presentation.Data;
using UnityEngine;
using UnityEngine.AddressableAssets;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    public class BattleSummonEffectManager : MonoBehaviour
    {
        [SerializeField] Transform _root;
        [SerializeField] AssetReference _battleSummonEffectInfoListReference;

        BattleSummonEffectInfoList _battleSummonEffectInfoList;
        List<BattleSummonEffectView> _effects = new ();

        public Transform EffectLayer => _root;

        void OnDestroy()
        {
            ReleaseBattleSummonEffectInfoList();
        }

        public async UniTask Initialize(CancellationToken cancellationToken)
        {
            await LoadBattleSummonEffectInfoList(cancellationToken);
        }

        public BattleSummonEffectView Generate(BattleEffectId id, Vector3 worldPos)
        {
            Debug.Log("Generate(BattleSummonEffectId id, Vector3 worldPos) /  "+id);
            var prefab = GetEffectPrefab(id);
            if (prefab == null) return null;

            var effect = Instantiate(prefab, _root);
            _effects.Add(effect);

            effect.OnCompleted = () => _effects.Remove(effect);
            effect.transform.position = worldPos;

            return effect;
        }

        public BattleSummonEffectView Generate(BattleEffectId id, Transform parent, Vector3 localPos)
        {
            Debug.Log("Generate(BattleSummonEffectId id, Transform parent, Vector3 localPos) /  "+id);
            var prefab = GetEffectPrefab(id);
            return Generate(prefab, parent, localPos);
        }

        public BattleSummonEffectView Generate(GameObject prefab, Transform parent, Vector3 localPos)
        {
            if (prefab == null) return null;

            var effect = prefab.GetComponent<BattleSummonEffectView>();
            return Generate(effect, parent, localPos);
        }

        public BattleSummonEffectView Generate(BattleSummonEffectView battleEffectView, Transform parent, Vector3 localPos)
        {
            if (battleEffectView == null) return null;

            var effect = Instantiate(battleEffectView, parent);
            _effects.Add(effect);

            effect.OnCompleted = () => _effects.Remove(effect);
            effect.transform.localPosition = localPos;

            return effect;
        }

        public BattleSummonEffectView Generate(BattleEffectId id, Transform parent)
        {
            return Generate(id, parent, Vector3.zero);
        }

        public bool Exists(BattleEffectId id)
        {
            return GetEffectPrefab(id) != null;
        }

        public MultipleSwitchHandler PauseAllEffects(MultipleSwitchHandler handler)
        {
            foreach (var effect in _effects )
            {
                effect.Pause(handler);
            }

            return handler;
        }

        BattleSummonEffectView GetEffectPrefab(BattleEffectId id)
        {
            var info = _battleSummonEffectInfoList.List.Find(info => info.Id == id);
            return info?.Prefab;
        }
        
        async UniTask LoadBattleSummonEffectInfoList(CancellationToken cancellationToken)
        {
            ReleaseBattleSummonEffectInfoList();
            
            await _battleSummonEffectInfoListReference
                .LoadAssetAsync<BattleSummonEffectInfoList>()
                .WithCancellation(cancellationToken);
            
            _battleSummonEffectInfoList = (BattleSummonEffectInfoList)_battleSummonEffectInfoListReference.Asset;
        }

        void ReleaseBattleSummonEffectInfoList()
        {
            if (_battleSummonEffectInfoListReference.IsValid()) _battleSummonEffectInfoListReference.ReleaseAsset();
            _battleSummonEffectInfoList = null;
        }
    }
}
