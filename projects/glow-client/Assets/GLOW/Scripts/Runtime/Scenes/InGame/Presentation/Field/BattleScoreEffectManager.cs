using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.InGame.Presentation.Constants;
using UnityEngine;
using UnityEngine.AddressableAssets;
using UnityEngine.Pool;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    public class BattleScoreEffectManager : MonoBehaviour
    {
        const int Capacity = 3;
        const int MaxSize = 30;

        [SerializeField] Transform _root;
        [SerializeField] AssetReferenceGameObject _scorePrefabReference;
        [SerializeField] AnimationClip _damageClip;
        [SerializeField] AnimationClip _defeatClip;

        BattleScoreEffectView _scorePrefab;
        ObjectPool<BattleScoreEffectView> _effectPool;
        List<BattleScoreEffectView> _activeEffects = new ();

        public void OnDestroy()
        {
            ReleaseLoadedPrefabs();
        }

        public async UniTask Initialize(CancellationToken cancellationToken)
        {
            SetGlobalZPosition(_root, FieldZPositionDefinitions.EffectRoot);

            await LoadPrefabs(cancellationToken);

            _effectPool = new ObjectPool<BattleScoreEffectView>(
                createFunc: () =>
                {
                    var effect = Instantiate(_scorePrefab, _root);
                    effect.gameObject.SetActive(true);
                    return effect;
                },
                actionOnGet: effect =>
                {
                    effect.AddCompletedAction(() => _effectPool.Release(effect));
                    effect.gameObject.SetActive(true);
                    _activeEffects.Add(effect);
                },
                actionOnRelease: effect =>
                {
                    effect.gameObject.SetActive(false);
                    _activeEffects.Remove(effect);
                },
                defaultCapacity: Capacity,
                maxSize: MaxSize);
        }

        public BattleScoreEffectView Generate(Vector3 worldPos, InGameScore score, InGameScoreType scoreType)
        {
            var effectView = _effectPool.Get();
            worldPos.z = FieldZPositionDefinitions.EffectRoot;
            effectView.transform.position = worldPos;
            effectView.SetAnimationClip(GetAnimationClip(score, scoreType));
            return effectView;
        }

        public MultipleSwitchHandler PauseAllEffects(MultipleSwitchHandler handler)
        {
            foreach (var effect in _activeEffects)
            {
                effect.Pause(handler);
            }

            return handler;
        }

        AnimationClip GetAnimationClip(InGameScore inGameScore, InGameScoreType scoreType)
        {
            // 現状スコア値によるダメージ画像の切り替えは一旦無しに
            switch (scoreType)
            {
                case InGameScoreType.Damage:
                    return _damageClip;
                case InGameScoreType.EnemyDefeat:
                case InGameScoreType.BossEnemyDefeat:
                    return _defeatClip;
                default:
                    return _damageClip;
            }
        }

        void SetGlobalZPosition(Transform gameObjectTransform, float z)
        {
            var pos = gameObjectTransform.position;
            pos.z = z;
            gameObjectTransform.position = pos;
        }

        async UniTask LoadPrefabs(CancellationToken cancellationToken)
        {
            ReleaseLoadedPrefabs();

            await _scorePrefabReference.LoadAssetAsync<GameObject>().WithCancellation(cancellationToken);
            _scorePrefab = ((GameObject)_scorePrefabReference.Asset).GetComponent<BattleScoreEffectView>();
        }

        void ReleaseLoadedPrefabs()
        {
            if (_scorePrefabReference.IsValid()) _scorePrefabReference.ReleaseAsset();
            _scorePrefab = null;
        }
    }
}
