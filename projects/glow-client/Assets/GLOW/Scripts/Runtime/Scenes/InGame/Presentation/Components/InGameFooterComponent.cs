using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Presentation.UI.UIEffect;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using GLOW.Scenes.InGame.Presentation.ViewModels;
using UnityEngine;
using UnityEngine.Serialization;
using UnityEngine.UI;
using WonderPlanet.UniTaskSupporter;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class InGameFooterComponent : UIObject
    {
        const float FadeTime = 0.2f;
        const float DefaultAlpha = 0.85f;
        readonly Color _defaultColor = new Color(1f, 1f, 1f, DefaultAlpha);

        [SerializeField] DeckComponent _deckComponent;
        [SerializeField] BattlePointComponent _battlePointComponent;
        [SerializeField] RushButton _rushButton;
        [SerializeField] OpponentRushGauge _opponentRushGauge;
        [SerializeField] UIObject _deckTapGuard;
        [SerializeField] UIObject _battlePointTapGuard;
        [SerializeField] UIObject _rushButtonTapGuard;
        [SerializeField] Image _deckTapGuardImage;
        [SerializeField] Image _battlePointTapGuardImage;
        [SerializeField] Image _rushButtonTapGuardImage;

        CancellationTokenSource _fadeCancellationTokenSource;

        public Action<MasterDataId> OnSummonButtonTapped { get; set; }
        public Action<MasterDataId> OnUseSpecialAttackButtonTapped { get; set; }
        public Action<MasterDataId> OnSpecialUnitSummonButtonTapped { get; set; }
        public Action<UserDataId> OnButtonLongPressed { get; set; }

        protected override void OnDestroy()
        {
            base.OnDestroy();

            _fadeCancellationTokenSource?.Dispose();
            _fadeCancellationTokenSource = null;
        }

        public void Initialize(InitializeViewModel initializeViewModel, UIEffectManager uiEffectManager)
        {
            _deckComponent.Initialize(initializeViewModel.DeckUnitViewModels, uiEffectManager);
            _deckComponent.OnSummonButtonTapped = characterId => OnSummonButtonTapped?.Invoke(characterId);
            _deckComponent.OnUseSpecialAttackButtonTapped = characterId => OnUseSpecialAttackButtonTapped?.Invoke(characterId);
            _deckComponent.OnSpecialUnitSummonButtonTapped = characterId => OnSpecialUnitSummonButtonTapped?.Invoke(characterId);
            _deckComponent.OnButtonLongPressed = userUnitId => OnButtonLongPressed?.Invoke(userUnitId);

            _rushButton.Initialize(initializeViewModel.RushModel);
            _opponentRushGauge.Initialize(initializeViewModel.PvpOpponentRushModel);

            UpdatedBattlePoint(initializeViewModel.BattlePointModel);
        }

        public void UpdateDeck(IReadOnlyList<DeckUnitViewModel> deckUnitViewModels)
        {
            _deckComponent.UpdateDeck(deckUnitViewModels);
        }

        public void ChangeDeckMode()
        {
            _deckComponent.ChangeDeckMode();
        }

        public void UpdatedBattlePoint(BattlePointModel battlePointModel)
        {
            _battlePointComponent.SetBattlePoint(battlePointModel.CurrentBattlePoint, battlePointModel.MaxBattlePoint);
        }

        public void UpdateRush(RushModel rushModel)
        {
            _rushButton.UpdateRushButton(rushModel);
        }

        public void UpdateOpponentRush(RushModel rushModel)
        {
            _opponentRushGauge.UpdateOpponentRushGauge(rushModel);
        }

        public void OnRushAttackPowerUp(
            Vector3 iconPos,
            PercentageM updatedPowerUp)
        {
            _rushButton.OnRushAttackPowerUp(iconPos, updatedPowerUp);
        }

        public void OnOpponentRushAttackPowerUp(
            Vector3 iconPos,
            PercentageM updatedPowerUp)
        {
            _opponentRushGauge.OnRushAttackPowerUp(iconPos, updatedPowerUp);
        }

        public void OnCoolTimeVariation(
            BattleSide targetDeckBattleSide,
            UIEffectId uiEffectId,
            MasterDataId targetCharacterId)
        {
            // 敵側のデッキは存在しないため、味方側のみ処理を行う
            if (targetDeckBattleSide == BattleSide.Enemy) return;

            var deckCharacterComponent = _deckComponent.GetDeckCharacterComponent(targetCharacterId);
            if (deckCharacterComponent != null)
            {
                deckCharacterComponent.PlayBattleEffect(uiEffectId);
            }
        }
        public void EnableTapGuardDuringTargetSelection()
        {
            EnableTapGuard();
            _deckTapGuardImage.DOFade(DefaultAlpha, 0.1f);
        }

        public void DisableTapGuardDuringTargetSelection()
        {
            DisableTapGuard();
            _deckTapGuardImage.DOFade(0.0f, 0.1f);
        }

        public void EnableTapGuard()
        {
            _deckTapGuard.Hidden = false;
            _battlePointTapGuard.Hidden = false;
            _rushButtonTapGuard.Hidden = false;
        }

        public void DisableTapGuard()
        {
            _deckTapGuard.Hidden = true;
            _battlePointTapGuard.Hidden = true;
            _rushButtonTapGuard.Hidden = true;
        }

        public void EnableFadeFooterTapGuard()
        {
            _fadeCancellationTokenSource?.Cancel();
            _fadeCancellationTokenSource?.Dispose();
            _fadeCancellationTokenSource = null;

            EnableTapGuard();

            _deckTapGuardImage.DOFade(DefaultAlpha, FadeTime);
            _battlePointTapGuardImage.DOFade(DefaultAlpha, FadeTime);
            _rushButtonTapGuardImage.DOFade(DefaultAlpha, FadeTime);
        }

        public void HideRushButton()
        {
            _rushButton.Hidden = true;
        }
        
        public void ShowRushButton()
        {
            _rushButton.IsVisible = true;
        }

        public void DisableFadeFooterTapGuard()
        {
            _fadeCancellationTokenSource?.Cancel();
            _fadeCancellationTokenSource?.Dispose();
            _fadeCancellationTokenSource = new();

            var linkedTokenSource = CancellationTokenSource.CreateLinkedTokenSource(
                this.GetCancellationTokenOnDestroy(),
                _fadeCancellationTokenSource.Token);

            DoAsync.Invoke(linkedTokenSource.Token, async token =>
            {
                _deckTapGuardImage.DOFade(0.0f, FadeTime);
                _battlePointTapGuardImage.DOFade(0.0f, FadeTime);
                _rushButtonTapGuardImage.DOFade(0.0f, FadeTime);
                await UniTask.Delay(TimeSpan.FromSeconds(FadeTime), cancellationToken:token);

                DisableTapGuard();
                ResetTapGuardColor();

                _fadeCancellationTokenSource?.Dispose();
                _fadeCancellationTokenSource = null;
            });
        }

        void ResetTapGuardColor()
        {
            _deckTapGuardImage.color = _defaultColor;
            _battlePointTapGuardImage.color = _defaultColor;
            _rushButtonTapGuardImage.color = _defaultColor;
        }
    }
}
