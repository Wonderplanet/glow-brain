using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.InGame.Presentation.Constants;
using GLOW.Scenes.InGame.Presentation.Field;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components.Rush
{
    public class PvpOpponentRushLayer : MonoBehaviour
    {
        [Serializable]
        struct UnitObjectInfo
        {
            public UIObject ObjectRoot;
            public Transform UnitRoot;
        }

        [SerializeField] TimelineAnimation _timelineAnimation;
        [SerializeField] List<UnitObjectInfo> _unitObjectInfos;
        [SerializeField] EndRushResultLayer _endRushResultLayer;
        
        [SerializeField] UIObject _level1StartRushTitleComponent;
        [SerializeField] UIObject _level2StartRushTitleComponent;
        [SerializeField] UIObject _level3StartRushTitleComponent;

        List<UnitImage> _unitImages = new List<UnitImage>();

        public Action OnEndTimelineSignalAction { get; set; }
        public Action OnUnitAttackAnimationStartSignalAction { get; set; }
        public Action OnUnpauseSignalAction { get; set; }

        IUnitImageContainer _unitImageContainer;

        public void Initialize(IUnitImageContainer unitImageContainer)
        {
            _unitImageContainer = unitImageContainer;
        }

        public async UniTask PlayAsync(CancellationToken cancellationToken)
        {
            gameObject.SetActive(true);
            if (_timelineAnimation != null)
            {
                await _timelineAnimation.PlayAsync(cancellationToken);
            }
            gameObject.SetActive(false);
        }

        public void Pause(bool pause)
        {
            if (_timelineAnimation != null)
            {
                _timelineAnimation.Pause(pause);
            }
        }

        public void Skip()
        {
            if (_timelineAnimation != null)
            {
                _timelineAnimation.Skip();
            }
        }

        public void SetUpUnitImage(IReadOnlyList<UnitAssetKey> unitAssetKeys)
        {
            for (var i = 0; i < unitAssetKeys.Count; i++)
            {
                var unitAssetKey = unitAssetKeys[i];

                if (i >= _unitObjectInfos.Count) break;
                _unitObjectInfos[i].ObjectRoot.Hidden = false;
                var unitRoot = _unitObjectInfos[i].UnitRoot;

                var unitImage = InstantiateUnitImage(unitAssetKey, unitRoot);

                if (unitImage == null) continue;

                // 待機アニメーション開始
                // ライバルの場合は反転
                var anim = unitImage.GetMirrorAnimation(UnitAnimationType.Wait);
                unitImage.StartAnimation(anim, CharacterUnitAnimation.Empty);

                _unitImages.Add(unitImage);
            }
        }

        public void PlayUnitAttackAnimation()
        {
            foreach (var unitImage in _unitImages)
            {
                if (unitImage != null)
                {
                    // 攻撃アニメーション開始
                    // ライバルの場合は反転
                    var attackAnim = unitImage.GetMirrorAnimation(UnitAnimationType.Attack);
                    var waitAnim= unitImage.GetMirrorAnimation(UnitAnimationType.Wait);
                    unitImage.StartAnimation(attackAnim, waitAnim);
                }
            }
        }
        
        public void SetUpRushLevel(RushChargeCount chargeCount)
        {
            _level1StartRushTitleComponent.IsVisible = chargeCount.Value == 1;
            _level2StartRushTitleComponent.IsVisible = chargeCount.Value == 2;
            _level3StartRushTitleComponent.IsVisible = chargeCount.Value >= 3;
        }
        
        public void SetUpEndOpponentRushResultLayer(AttackPower calculatedRushAttackPower)
        {
            _endRushResultLayer.SetUpOpponentRushResult(calculatedRushAttackPower);
        }

        public void CleanUp()
        {
            foreach (var info in _unitObjectInfos)
            {
                info.ObjectRoot.Hidden = true;
                foreach (Transform child in info.UnitRoot)
                {
                    Destroy(child.gameObject);
                }
            }

            _unitImages.Clear();
        }

        public void PauseUnitImage(MultipleSwitchHandler handler)
        {
            foreach (var unitImage in _unitImages)
            {
                if (unitImage != null)
                {
                    unitImage.PauseAnimation(handler);
                }
            }
        }

        public void OnEndTimelineSignal()
        {
            OnEndTimelineSignalAction?.Invoke();
        }

        public void OnUnitAttackAnimationStartSignal()
        {
            OnUnitAttackAnimationStartSignalAction?.Invoke();
        }

        public void OnUnpauseSignal()
        {
            OnUnpauseSignalAction?.Invoke();
        }

        UnitImage InstantiateUnitImage(UnitAssetKey assetKey, Transform unitRoot)
        {
            if (unitRoot == null) return null;

            var prefab = _unitImageContainer.Get(UnitImageAssetPath.FromAssetKey(assetKey));

            var unitImage = Instantiate(prefab, unitRoot).GetComponent<UnitImage>();
            // ライバルの場合は反転
            unitImage.Flip = true;

            return unitImage;
        }
    }
}