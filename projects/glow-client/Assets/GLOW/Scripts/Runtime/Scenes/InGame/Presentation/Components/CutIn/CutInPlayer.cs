using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.InGame.Domain.ScriptableObjects;
using GLOW.Scenes.InGame.Presentation.Field;
using GLOW.Scenes.InGame.Presentation.TimelineTracks;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using UIKit;
using UnityEngine;
using Zenject;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class CutInPlayer : MonoBehaviour
    {
        [SerializeField] UICanvas _backgroundCanvas;
        [SerializeField] UICanvas _frontCanvas;
        [SerializeField] Transform _backgroundRoot;
        [SerializeField] Transform _frontRoot;
        [SerializeField] Transform _unitLayerRoot;

        IUnitImageContainer _unitImageContainer;

        CancellationTokenSource _animationCancellationTokenSource;
        TimelineAnimation _backgroundTimelineAnimation;
        TimelineAnimation _frontTimelineAnimation;
        CutInUnitLayer _unitLayer;
        UnitImage _unitImage;
        readonly MultipleSwitchController _pauseController = new ();

        void Awake()
        {
            _pauseController.OnStateChanged = OnPause;
        }

        void Start()
        {
            _backgroundCanvas.RectTransform.anchoredPosition = Vector2.zero;
            _backgroundCanvas.RectTransform.sizeDelta = Vector2.zero;
            _backgroundCanvas.RectTransform.pivot = Vector2.zero;
            _backgroundCanvas.RectTransform.anchorMin = Vector2.zero;
            _backgroundCanvas.RectTransform.anchorMax = Vector2.one;

            _frontCanvas.RectTransform.anchoredPosition = Vector2.zero;
            _frontCanvas.RectTransform.sizeDelta = Vector2.zero;
            _frontCanvas.RectTransform.pivot = Vector2.zero;
            _frontCanvas.RectTransform.anchorMin = Vector2.zero;
            _frontCanvas.RectTransform.anchorMax = Vector2.one;
        }
        
        void OnDestroy()
        {
            _pauseController.Dispose();
        }
        
        public void Initialize(IUnitImageContainer unitImageContainer)
        {
            _unitImageContainer = unitImageContainer;
        }

        public async UniTask Play(
            CharacterColor unitColor,
            UnitAssetKey unitAssetKey,
            UnitAttackViewInfo attackViewInfo,
            CancellationToken cancellationToken)
        {
            var linkedCancellationToken = CancellationTokenSource.CreateLinkedTokenSource(
                this.GetCancellationTokenOnDestroy(), cancellationToken).Token;

            try
            {
                Cleanup();

                await UniTask.Yield(PlayerLoopTiming.Update, linkedCancellationToken);

                _backgroundTimelineAnimation = InstantiateBackgroundTimelineAnimation(attackViewInfo.CutInPrefab_background);
                _frontTimelineAnimation = InstantiateFrontTimelineAnimation(attackViewInfo.CutInPrefab_front);
                _unitLayer = InstantiateUnitLayer(attackViewInfo.CutInPrefab_unitEffect);

                _unitImage = InstantiateUnitImage(unitAssetKey, _unitLayer);

                if (_unitImage != null)
                {
                    _unitImage.SetUnitColor(CharacterColor.Colorless);
                    _unitImage.StartAnimation(CharacterUnitAnimation.SpecialAttackCutIn, CharacterUnitAnimation.Empty);
                }

                var backgroundPlayTask = PlayBackground(linkedCancellationToken);
                var frontPlayTask = PlayFront(linkedCancellationToken);
                var unitEffectPlayTask = PlayUnitEffect(linkedCancellationToken);
                
                SoundEffectPlayer.Play(SoundEffectId.SSE_051_063);
                SoundEffectPlayer.Play(SoundEffectId.SSE_072_012);
                SoundEffectPlayer.Play(SoundEffectId.SSE_051_045);
                SoundEffectPlayer.Play(SoundEffectId.SSE_072_022);

                await UniTask.WhenAll(backgroundPlayTask, frontPlayTask, unitEffectPlayTask);

                Cleanup();
            }
            catch (OperationCanceledException)
            {
                Cleanup();
                throw;
            }
        }

        public MultipleSwitchHandler Pause(MultipleSwitchHandler handler)
        {
            _pauseController.TurnOn(handler);

            if (_unitImage != null)
            {
                _unitImage.PauseAnimation(handler);
            }

            return handler;
        }

        void OnPause(bool isPause)
        {
            if (_backgroundTimelineAnimation != null)
            {
                _backgroundTimelineAnimation.Pause(isPause);
            }

            if (_frontTimelineAnimation != null)
            {
                _frontTimelineAnimation.Pause(isPause);
            }

            if (_unitLayer != null)
            {
                _unitLayer.Pause(isPause);
            }
        }

        TimelineAnimation InstantiateBackgroundTimelineAnimation(GameObject prefab)
        {
            if (prefab == null) return null;

            var background = Instantiate(prefab, _backgroundRoot.transform);
            return background.GetComponent<TimelineAnimation>();
        }

        TimelineAnimation InstantiateFrontTimelineAnimation(GameObject prefab)
        {
            if (prefab == null) return null;

            var front = Instantiate(prefab, _frontRoot.transform);
            return front.GetComponent<TimelineAnimation>();
        }

        CutInUnitLayer InstantiateUnitLayer(GameObject prefab)
        {
            if (prefab == null) return null;

            var unitLayer = Instantiate(prefab, _unitLayerRoot.transform);
            return unitLayer.GetComponent<CutInUnitLayer>();
        }

        UnitImage InstantiateUnitImage(UnitAssetKey assetKey, CutInUnitLayer unitLayer)
        {
            if (unitLayer.UnitRoot == null) return null;

            var prefab = _unitImageContainer.Get(UnitImageAssetPath.FromAssetKey(assetKey));

            return Instantiate(prefab, unitLayer.UnitRoot).GetComponent<UnitImage>();
        }

        async UniTask PlayBackground(CancellationToken cancellationToken)
        {
            if (_backgroundTimelineAnimation == null) return;

            await _backgroundTimelineAnimation.PlayAsync(cancellationToken);

            _backgroundTimelineAnimation.gameObject.SetActive(false);
        }

        async UniTask PlayFront(CancellationToken cancellationToken)
        {
            if (_frontTimelineAnimation == null) return;

            await _frontTimelineAnimation.PlayAsync(cancellationToken);

            _frontTimelineAnimation.gameObject.SetActive(false);
        }

        async UniTask PlayUnitEffect(CancellationToken cancellationToken)
        {
            if (_unitLayer == null) return;

            await _unitLayer.PlayAsync(cancellationToken);

            _unitLayer.gameObject.SetActive(false);
        }

        void Cleanup()
        {
            if (_backgroundTimelineAnimation != null)
            {
                Destroy(_backgroundTimelineAnimation.gameObject);
                _backgroundTimelineAnimation = null;
            }

            if (_frontTimelineAnimation != null)
            {
                Destroy(_frontTimelineAnimation.gameObject);
                _frontTimelineAnimation = null;
            }

            if (_unitLayer != null)
            {
                Destroy(_unitLayer.gameObject);
                _unitLayer = null;
            }
        }
    }
}
