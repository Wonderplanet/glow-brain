using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Text;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Presentation.UI.UIEffect;
using GLOW.Scenes.InGame.Presentation.ViewModels;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Modules.Log;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class DeckComponent : UIObject
    {
        public enum DeckSwitchDirection
        {
            Left,
            Right,
        }

        const float NumberOfFrontDeckUnits = 5;
        const float DeckSwitchDuration = 0.12f;
        const float DeckSwitchSlideLength = 300;

        [Header("一段デッキレイアウト")]
        [Header("一段デッキレイアウト/手前")]
        [SerializeField] GameObject _switchModelDeckFront;
        [SerializeField] CanvasGroup _switchModelDeckFrontCanvasGroup;
        [SerializeField] RectMask2D _switchModelDeckFrontRectMask2D;
        [Header("一段デッキレイアウト/後ろ")]
        [SerializeField] GameObject _switchModeDeckBack;
        [SerializeField] CanvasGroup _switchModeDeckBackCanvasGroup;
        [SerializeField] RectMask2D _switchModeDeckBackRectMask2D;
        [Header("一段デッキレイアウト/各種要素")]
        [SerializeField] List<DeckCharacterComponent> _deckCharacterComponents;
        [SerializeField] float _switchModHeight;
        [SerializeField] GameObject _switchLeftButton;
        [SerializeField] GameObject _switchRightButton;
        [SerializeField] DeckSwipeDetector _deckSwipeDetector;
        [Header("二段デッキレイアウト")]
        [SerializeField] GameObject _twoRowModeDeck;
        [SerializeField] CanvasGroup _twoRowModeDeckCanvasGroup;
        [SerializeField] List<DeckCharacterComponent> _deckCharacterComponentsFromTwoRow;
        [SerializeField] float _twoRowModeHeight;

        bool _isTwoRowMode;
        List<Tween> _tweens = new ();
        CancellationTokenSource _deckSwitchCancellationTokenSource;

        Vector3 _defaultDeckFrontLocalPosition;
        Vector3 _defaultDeckBackLocalPosition;

        public Action<MasterDataId> OnSummonButtonTapped { get; set; }
        public Action<MasterDataId> OnUseSpecialAttackButtonTapped { get; set; }
        public Action<MasterDataId> OnSpecialUnitSummonButtonTapped { get; set; }
        public Action<UserDataId> OnButtonLongPressed { get; set; }


        public void Initialize(IReadOnlyList<DeckUnitViewModel> deckUnitViewModels, UIEffectManager uiEffectManager)
        {
            _defaultDeckFrontLocalPosition = _switchModelDeckFront.transform.localPosition;
            _defaultDeckBackLocalPosition = _switchModeDeckBack.transform.localPosition;

            _switchModelDeckFrontRectMask2D.enabled = false;
            _switchModeDeckBackRectMask2D.enabled = true;


            // 一段スワイプ対応オブジェクト
            for (var i = 0; i < _deckCharacterComponents.Count; i++)
            {
                var deckUnitModel = deckUnitViewModels[i];

                _deckCharacterComponents[i].Initialize(deckUnitModel, false, uiEffectManager);
                _deckCharacterComponents[i].IsSwitchDeckMode = true;
                _deckCharacterComponents[i].IsFront = i < NumberOfFrontDeckUnits;
                _deckCharacterComponents[i].OnTapped =
                    deckUnitModel.RoleType != CharacterUnitRoleType.Special
                        ? OnCharacterComponentTapped
                        : OnSpecialCharacterComponentTapped;
                _deckCharacterComponents[i].OnSwipedLeft += () => OnDeckSwiped(DeckSwitchDirection.Left);
                _deckCharacterComponents[i].OnSwipedRight += () => OnDeckSwiped(DeckSwitchDirection.Right);
                _deckCharacterComponents[i].OnLongPress.PointerDown.AddListener(() =>
                {
                    OnCharacterComponentLongTapped(deckUnitModel.UserUnitId);
                });
            }
            //二段表示オブジェクト
            for (var i = 0; i < _deckCharacterComponentsFromTwoRow.Count; i++)
            {
                var deckUnitViewModel = deckUnitViewModels[i];

                _deckCharacterComponentsFromTwoRow[i].Initialize(deckUnitViewModel, true, uiEffectManager);
                _deckCharacterComponentsFromTwoRow[i].IsSwitchDeckMode = false;
                _deckCharacterComponentsFromTwoRow[i].IsFront = true;
                _deckCharacterComponentsFromTwoRow[i].OnTapped =
                    deckUnitViewModel.RoleType != CharacterUnitRoleType.Special
                        ? OnCharacterComponentTapped
                        : OnSpecialCharacterComponentTapped;
                _deckCharacterComponentsFromTwoRow[i].OnLongPress.PointerDown.AddListener(() =>
                {
                    OnCharacterComponentLongTapped(deckUnitViewModel.UserUnitId);
                });
            }

            // 初期化の際は一段デッキを表示する処理にする
            UpdateModeDeckAndArrowButton(
                shouldShowFront: true,
                shouldShowBack: true,
                shouldShowTwoRow: false,
                shouldShowArrow: true);

            _deckSwipeDetector.OnSwipedLeft = () => OnDeckSwiped(DeckSwitchDirection.Left);
            _deckSwipeDetector.OnSwipedRight = () => OnDeckSwiped(DeckSwitchDirection.Right);
        }

        void UpdateModeDeckAndArrowButton(bool shouldShowFront, bool shouldShowBack, bool shouldShowTwoRow, bool shouldShowArrow)
        {
            _switchModelDeckFrontCanvasGroup.alpha = shouldShowFront ? 1 : 0;
            _switchModelDeckFrontCanvasGroup.blocksRaycasts = shouldShowFront;

            _switchModeDeckBackCanvasGroup.alpha = shouldShowBack ? 1 : 0;
            _switchModeDeckBackCanvasGroup.blocksRaycasts = shouldShowBack;

            _twoRowModeDeckCanvasGroup.alpha = shouldShowTwoRow ? 1 : 0;
            _twoRowModeDeckCanvasGroup.blocksRaycasts = shouldShowTwoRow;

            _switchLeftButton.SetActive(shouldShowArrow);
            _switchRightButton.SetActive(shouldShowArrow);
        }

        public void UpdateDeck(IReadOnlyList<DeckUnitViewModel> deckUnitViewModels)
        {
            foreach (var viewModel in deckUnitViewModels)
            {
                var component = _deckCharacterComponents.Find(c => c.CharacterId == viewModel.CharacterId);
                var componentTwoRow = _deckCharacterComponentsFromTwoRow.Find(c => c.CharacterId == viewModel.CharacterId);
                if (component == null)
                {
                    ApplicationLog.LogWarning(
                        nameof(DeckComponent), 
                        ZString.Format("DeckCharacterComponent is not found. {0}", viewModel.CharacterId));
                    continue;
                }

                component.UpdateDeckUnit(viewModel);
                componentTwoRow.UpdateDeckUnit(viewModel);
            }
        }

        public void ChangeDeckMode()
        {
            CancelDeckSwitch();

            _isTwoRowMode = !_isTwoRowMode;

            var height = _isTwoRowMode ? _twoRowModeHeight : _switchModHeight;
            RectTransform.sizeDelta = new Vector2(RectTransform.sizeDelta.x, height);
            
            //InGameView.ChangeDeckModeでコマのページ高さを変えるために必要
            LayoutRebuilder.ForceRebuildLayoutImmediate(transform.parent as RectTransform); 

            UpdateModeDeckAndArrowButton(
                shouldShowFront: !_isTwoRowMode,
                shouldShowBack: !_isTwoRowMode,
                shouldShowTwoRow: _isTwoRowMode,
                shouldShowArrow: !_isTwoRowMode);
        }

        void OnCharacterComponentTapped(MasterDataId characterId, DeckCharacterComponent.TapAction tapAction)
        {
            switch (tapAction)
            {
                case DeckCharacterComponent.TapAction.Summon:
                    OnSummonButtonTapped?.Invoke(characterId);
                    break;
                case DeckCharacterComponent.TapAction.SpecialAttack:
                    OnUseSpecialAttackButtonTapped?.Invoke(characterId);
                    break;
                case DeckCharacterComponent.TapAction.SpecialUnitSpecialAttack:
                    OnSpecialUnitSummonButtonTapped?.Invoke(characterId);
                    break;
            }
        }

        void OnSpecialCharacterComponentTapped(MasterDataId characterId, DeckCharacterComponent.TapAction tapAction)
        {
            // スペシャルキャラは召喚のみ実行可能とする
            switch (tapAction)
            {
                case DeckCharacterComponent.TapAction.SpecialUnitSpecialAttack:
                    OnSpecialUnitSummonButtonTapped?.Invoke(characterId);
                    break;
            }
        }

        void OnCharacterComponentLongTapped(UserDataId unitId)
        {
            if (unitId.IsEmpty()) return;
            OnButtonLongPressed?.Invoke(unitId);
        }

        void UpdateSwipeAnimationingStatus(bool value)
        {
            if (_isTwoRowMode) return;
            foreach (var t in _deckCharacterComponents)
            {
                t.IsSwipeAnimationing = value;
            }
        }

        void SwitchDeck(DeckSwitchDirection direction)
        {
            if (_isTwoRowMode) return;

            _switchModelDeckFrontRectMask2D.enabled = !_switchModelDeckFrontRectMask2D.enabled;
            _switchModeDeckBackRectMask2D.enabled = !_switchModeDeckBackRectMask2D.enabled;

            CancelDeckSwitch();
            UpdateSwipeAnimationingStatus(false);

            _deckSwitchCancellationTokenSource = new();
            var cancellationToken = CancellationTokenSource.CreateLinkedTokenSource(
                this.GetCancellationTokenOnDestroy(), _deckSwitchCancellationTokenSource.Token).Token;

            PlayDeckSwitchAnimation(direction, cancellationToken).Forget();
        }

        void CancelDeckSwitch()
        {
            _deckSwitchCancellationTokenSource?.Cancel();

            _tweens.ForEach(t => t.Kill(true));
            _tweens.Clear();
        }
        void SetDefaultPosition()
        {
            _switchModelDeckFront.transform.localPosition = _defaultDeckFrontLocalPosition;
            _switchModeDeckBack.transform.localPosition = _defaultDeckBackLocalPosition;

            var frontSiblingIndex = _switchModelDeckFront.transform.GetSiblingIndex();
            var backSiblingIndex = _switchModeDeckBack.transform.GetSiblingIndex();
            if (frontSiblingIndex < backSiblingIndex)
            {
                _switchModelDeckFront.transform.SetSiblingIndex(backSiblingIndex);
                _switchModeDeckBack.transform.SetSiblingIndex(frontSiblingIndex);
            }
        }

        async UniTask PlayDeckSwitchAnimation(DeckSwitchDirection direction, CancellationToken cancellationToken)
        {
            SetDefaultPosition();
            UpdateSwipeAnimationingStatus(true);

            var prevFrontDeck = _switchModelDeckFront;
            var prevBackDeck = _switchModeDeckBack;

            _switchModelDeckFront = prevBackDeck;
            _switchModeDeckBack = prevFrontDeck;

            var frontDeckTransform = prevFrontDeck.transform;
            var backDeckTransform = prevBackDeck.transform;
            var frontDeckLocalPos = frontDeckTransform.localPosition;
            var backDeckLocalPos = backDeckTransform.localPosition;

            foreach (var deckCharacterComponent in _deckCharacterComponents)
            {
                deckCharacterComponent.IsFront = !deckCharacterComponent.IsFront;
            }

            // 前面デッキと背面デッキをスライドさせる
            var slideLength = direction == DeckSwitchDirection.Left ? DeckSwitchSlideLength : -DeckSwitchSlideLength;

            var frontDeckTargetPos = frontDeckLocalPos;
            frontDeckTargetPos.x -= slideLength;

            var backDeckTargetPos = backDeckLocalPos;
            backDeckTargetPos.x += slideLength;

            //外側に移動
            _tweens.Add(frontDeckTransform
                .DOLocalMove(frontDeckTargetPos, DeckSwitchDuration * 0.5f).SetEase(Ease.InQuad));

            _tweens.Add(backDeckTransform
                .DOLocalMove(backDeckTargetPos, DeckSwitchDuration * 0.5f).SetEase(Ease.InQuad));

            await UniTask.Delay((int)(DeckSwitchDuration * 500), cancellationToken: cancellationToken);

            // 前後関係入れ替え
            var frontSiblingIndex = frontDeckTransform.GetSiblingIndex();
            var backSiblingIndex = backDeckTransform.GetSiblingIndex();

            prevFrontDeck.transform.SetSiblingIndex(backSiblingIndex);
            prevBackDeck.transform.SetSiblingIndex(frontSiblingIndex);

            // 内側に移動。前面デッキは背面デッキの元の位置に移動、背面デッキは前面デッキの元の位置に移動
            _tweens.Add(frontDeckTransform
                .DOLocalMove(backDeckLocalPos, DeckSwitchDuration * 0.5f).SetEase(Ease.OutQuad));

            _tweens.Add(backDeckTransform
                .DOLocalMove(frontDeckLocalPos, DeckSwitchDuration * 0.5f).SetEase(Ease.OutQuad));

            await UniTask.Delay((int)(DeckSwitchDuration * 10), cancellationToken: cancellationToken);
            // 高速に連続スワイプして、1~2Fくらいタップ離して・押し直すと、
            // スワイプ・召喚が同時に起きた(誤操作の)ようなUXになるのでUniTask待った後にステータス変える
            UpdateSwipeAnimationingStatus(false);
        }

        void OnDeckSwiped(DeckSwitchDirection direction)
        {
            SwitchDeck(direction);
        }

        public DeckCharacterComponent GetDeckCharacterComponent(MasterDataId characterId)
        {
            // 表示中のDeckCharacterComponentを返す
            var decks = _isTwoRowMode ? _deckCharacterComponentsFromTwoRow : _deckCharacterComponents;
            return decks.FirstOrDefault(c =>  c.CharacterId == characterId);
        }
    }
}
