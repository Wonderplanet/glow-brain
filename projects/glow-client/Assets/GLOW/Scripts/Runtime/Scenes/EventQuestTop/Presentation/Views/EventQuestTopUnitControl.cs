using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Modules.Spine.Presentation;
using GLOW.Scenes.InGame.Presentation.Field;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.EventQuestTop.Presentation.Views
{
    [Serializable]
    public class EventSelectUnitObject
    {
        public RectTransform DropShadowRectTransform;
        public Button UnitButton;
        public UISpineWithOutlineAvatar Unit;
        public RectTransform SpeechBalloonRectTransform;
        public UIText SpeechBalloonText;
    }

    public class EventQuestTopUnitControl : MonoBehaviour
    {
        [SerializeField] EventSelectUnitObject[] _units;

        bool _isUnit1ActionPlaying;
        bool _isUnit2ActionPlaying;
        bool _isUnit3ActionPlaying;

        public void Initialize(IReadOnlyList<UnitImage> unitModels)
        {
            foreach (var unit in _units)
            {
                unit.UnitButton.interactable = false;
                unit.Unit.gameObject.SetActive(false);
                unit.DropShadowRectTransform.gameObject.SetActive(false);
                unit.SpeechBalloonRectTransform.gameObject.SetActive(false);
            }
            for(var i = 0; i < _units.Length; i++)
            {
                if (unitModels.Count <= i) return;

                var unit = _units[i];
                var model = unitModels[i];
                unit.UnitButton.interactable = true;
                unit.Unit.gameObject.SetActive(true);
                unit.DropShadowRectTransform.gameObject.SetActive(true);

                var isFlip = i % 2 != 0;

                //順番依存1
                unit.Unit.SetSkeleton(model.SkeletonAnimation.skeletonDataAsset);
                var hasMirrorAnimation = unit.Unit.IsFindAnimation(CharacterUnitAnimation.MirrorWait.Name);

                if (isFlip && hasMirrorAnimation)
                {
                    unit.Unit.Animate(CharacterUnitAnimation.MirrorWait.ToString());
                }
                else
                {
                    unit.Unit.Animate(CharacterUnitAnimation.Wait.ToString());
                }

                //順番依存.2
                UpdateFlip(i, isFlip);
            }
        }

        //タップで「アニメーション+セリフ表示」の処理いるかも。作るならmst設定(data layer)から検討
        public void UpdateAnimation(int unitIndex)
        {
            if (_units.Length <= unitIndex) return;
            var unit = _units[unitIndex];

            //跳ねるアニメーション
            unit.Unit.gameObject.transform
                .DOLocalJump(unit.Unit.gameObject.transform.localPosition, 30, 1, 0.2f)
                .SetLink(this.gameObject)
                .Play();
        }

        void UpdateFlip(int unitIndex, bool isFlip)
        {
            if (_units.Length <= unitIndex) return;
            var unit = _units[unitIndex];
            unit.Unit.Flip = isFlip;
        }

        public async UniTask CreateSpeechBalloon(
            int unitIndex,
            EventDisplayUnitSpeechBalloonText text,
            float viewTime,
            CancellationToken ct)
        {
            if (_units.Length <= unitIndex) return;

            var unit = _units[unitIndex];
            unit.SpeechBalloonRectTransform.gameObject.SetActive(true);
            unit.SpeechBalloonText.SetText(text.Value);

            await UniTask.Delay(TimeSpan.FromSeconds(viewTime), cancellationToken: ct);
            unit.SpeechBalloonRectTransform.gameObject.SetActive(false);
        }

        public bool IsUnitActionPlaying(int unitIndex)
        {
            switch (unitIndex)
            {
                case 0:
                    return _isUnit1ActionPlaying;
                case 1:
                    return _isUnit2ActionPlaying;
                case 2:
                    return _isUnit3ActionPlaying;
                default:
                    return false;
            }
        }

        public void SetUnitActionPlaying(int unitIndex, bool isAction)
        {
            switch (unitIndex)
            {
                case 0:
                    _isUnit1ActionPlaying = isAction;
                    break;
                case 1:
                    _isUnit2ActionPlaying = isAction;
                    break;
                case 2:
                    _isUnit3ActionPlaying = isAction;
                    break;
            }
        }
    }
}
