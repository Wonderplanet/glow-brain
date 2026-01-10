using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.UserLevelUp.Presentation.Component;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.UserLevelUp.Presentation.View
{
    public class UserLevelUpResultView : UIView
    {
        [SerializeField] UserLevelUpResultAnimationController _animationController;
        [SerializeField] UserLevelUpLevelLabelComponent _userLevelComponent;
        [SerializeField] UserLevelUpTapLabelComponent _closeButtonTapLabelComponent;
        [SerializeField] RewardSectionLabelComponent rewardSectionLabelComponent;
        [SerializeField] RewardListComponent _rewardListComponent;
        [SerializeField] CanvasGroup _viewCanvasGroup;
        [SerializeField] GameObject _skipScreenButton;
        [SerializeField] GameObject _closeScreenButton;
        [SerializeField] RewardSectionLabelComponent _maxStaminaUpSectionLabel;
        [SerializeField] MaxStaminaDifferenceComponent _maxStaminaDifferenceComponent;

        public Action<PlayerResourceIconViewModel> OnPlayerResourceIconTapped
        {
            get => _rewardListComponent.OnPlayerResourceIconTapped;
            set => _rewardListComponent.OnPlayerResourceIconTapped = value;
        }

        public void SetupUserLevelNumber(UserLevel userLevel, bool isLevelMax)
        {
            _userLevelComponent.SetUserLevel(userLevel, isLevelMax);
        }

        public async UniTask PlayLevelUpEffectAnimation(CancellationToken cancellationToken)
        {
            await _animationController.PlayAnimation(cancellationToken);
        }

        public async UniTask PlayUserLevelLabel(CancellationToken cancellationToken)
        {
            await _userLevelComponent.PlayFadeIn(cancellationToken);
        }

        public async UniTask PlayMaxStaminaUpLabel(
            Stamina beforeMaxStamina,
            Stamina afterMaxStamina,
            CancellationToken cancellationToken)
        {
            if (beforeMaxStamina == afterMaxStamina)
            {
                _maxStaminaUpSectionLabel.Hidden = true;
                return;
            }

            await _maxStaminaUpSectionLabel.PlayFadeIn(cancellationToken);
        }

        public async UniTask PlayMaxStaminaDifference(
            Stamina beforeMaxStamina,
            Stamina afterMaxStamina,
            CancellationToken cancellationToken)
        {
            if (beforeMaxStamina == afterMaxStamina)
            {
                _maxStaminaDifferenceComponent.Hidden = true;
                return;
            }

            _maxStaminaDifferenceComponent.SetupMaxStamina(beforeMaxStamina, afterMaxStamina);
            await _maxStaminaDifferenceComponent.PlayFadeIn(cancellationToken);
        }

        public async UniTask PlayRewardLabelVisible(CancellationToken cancellationToken)
        {
            await rewardSectionLabelComponent.PlayFadeIn(cancellationToken);
        }

        public async UniTask PlayRewardItemAnimation(
            IReadOnlyList<PlayerResourceIconViewModel> iconViewModels,
            CancellationToken cancellationToken)
        {
            await _rewardListComponent.PlayRewardListAnimation(iconViewModels, 1, cancellationToken);
        }

        public async UniTask PlayCloseTextVisible(CancellationToken cancellationToken)
        {
            await _closeButtonTapLabelComponent.PlayFadeIn(cancellationToken);
        }

        public async UniTask PlayFadeOut(CancellationToken cancellationToken)
        {
            await _viewCanvasGroup.DOFade(0.0f, 0.2f).WithCancellation(cancellationToken);
        }

        public void SkipAnimation()
        {
            _animationController.Skip();
        }

        public void ShowUserLevel()
        {
            _userLevelComponent.ShowUserLevel();
        }

        public void ShowMaxStaminaComponent(
            Stamina beforeMaxStamina,
            Stamina afterMaxStamina)
        {
            if (beforeMaxStamina == afterMaxStamina)
            {
                _maxStaminaUpSectionLabel.Hidden = true;
                _maxStaminaDifferenceComponent.Hidden = true;
                return;
            }

            _maxStaminaUpSectionLabel.ShowRewardLabel();
            _maxStaminaDifferenceComponent.SetupMaxStamina(beforeMaxStamina, afterMaxStamina);
            _maxStaminaDifferenceComponent.SkipFadeIn();
        }

        public void ShowRewardLabel()
        {
            rewardSectionLabelComponent.ShowRewardLabel();
        }

        public void ShowRewardList(IReadOnlyList<PlayerResourceIconViewModel> iconViewModels)
        {
            _rewardListComponent.ShowRewardList(iconViewModels, 1);
        }

        public void ShowCloseLabel()
        {
            _closeButtonTapLabelComponent.ShowCloseLabel();
        }

        public void ShowSkipButton()
        {
            _skipScreenButton.SetActive(true);
        }

        public void HideSkipButton()
        {
            _skipScreenButton.SetActive(false);
        }

        public void ShowCloseButton()
        {
            _closeScreenButton.SetActive(true);
        }

        public void HideCloseButton()
        {
            _closeScreenButton.SetActive(false);
        }

    }
}
