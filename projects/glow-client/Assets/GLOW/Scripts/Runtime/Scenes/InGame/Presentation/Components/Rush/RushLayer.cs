using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Presentation.Constants;
using GLOW.Scenes.InGame.Presentation.Field;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.InGame.Presentation.Components.Rush
{
    public class RushLayer : MonoBehaviour
    {
        [Serializable]
        struct UnitObjectInfo
        {
            public UIObject ObjectRoot;
            public Transform UnitRoot;
        }

        [SerializeField] TimelineAnimation _timelineAnimation;
        [SerializeField] List<UnitObjectInfo> _unitObjectInfos;
        [SerializeField] UIObject _specialUnitCutIn;
        [SerializeField] List<UIImage> _specialUnitImages;
        [SerializeField] UIText _specialUnitBonusText;
        [SerializeField] UIText _specialUnitBonusShadowText;
        [SerializeField] EndRushResultLayer _endRushResultLayer;

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

        public void SetSpecialUnitCutInHidden(bool hidden)
        {
            _specialUnitCutIn.Hidden = hidden;
        }

        public void SetSpecialUnitBonusText(string text)
        {
            _specialUnitBonusText.SetText(text);
            _specialUnitBonusShadowText.SetText(text);
        }

        public void SetUpSpecialUnitAsset(IReadOnlyList<UnitAssetKey> specialUnitAssetKeys)
        {
            for (var i = 0; i < specialUnitAssetKeys.Count; i++)
            {
                if (i >= _specialUnitImages.Count) break;

                var unitAssetKey = specialUnitAssetKeys[i];
                var unitIcon = _specialUnitImages[i];
                SetUpRushUnitIcon(unitAssetKey, unitIcon);
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
                var anim = CharacterUnitAnimation.Wait;
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
                    var attackAnim = CharacterUnitAnimation.Attack;
                    var waitAnim = CharacterUnitAnimation.Wait;
                    unitImage.StartAnimation(attackAnim, waitAnim);
                }
            }
        }
        
        public void SetUpEndRushResultLayer(
            RushChargeCount chargeCount,
            AttackPower calculatedRushAttackPower,
            RushEvaluationType rushEvaluationType)
        {
            _endRushResultLayer.SetUpRushResult(
                chargeCount,
                calculatedRushAttackPower,
                rushEvaluationType);
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

        void SetUpRushUnitIcon(UnitAssetKey assetKey, UIImage image)
        {
            var rushImage = RushUnitImageAssetPath.FromAssetKey(assetKey);
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(image.Image, rushImage.Value);
            image.Hidden = false;
        }

        UnitImage InstantiateUnitImage(UnitAssetKey assetKey, Transform unitRoot)
        {
            if (unitRoot == null) return null;

            var prefab = _unitImageContainer.Get(UnitImageAssetPath.FromAssetKey(assetKey));

            var unitImage = Instantiate(prefab, unitRoot).GetComponent<UnitImage>();

            return unitImage;
        }
    }
}
