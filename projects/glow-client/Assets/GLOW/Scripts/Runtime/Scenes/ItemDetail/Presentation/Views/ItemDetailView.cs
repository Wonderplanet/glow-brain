using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.ItemDetail.Domain.Models;
using GLOW.Scenes.ItemDetail.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.ItemDetail.Presentation.Views
{
    public class ItemDetailView : UIView
    {
        static readonly int Disappear = Animator.StringToHash("disappear");
        static readonly int Appear = Animator.StringToHash("appear");

        [SerializeField] PlayerResourceIconComponent _playerResourceIconComponent;
        [SerializeField] UIText _amountText ;
        [SerializeField] UIObject _amountUI;
        [SerializeField] UIText _itemNameText;
        [SerializeField] UIText _itemDescriptionText;
        [SerializeField] ScrollRect _descriptionScrollRect;
        [SerializeField] LayoutElement _descriptionLayoutElement;
        [SerializeField] WhereGetMessageAreaComponent _whereGetMessageAreaComponent;
        [SerializeField] Animator _animator;
        [SerializeField] GameObject _whereGetMessageArea;

        public void InitializePlayerResourceDetail(ItemDetailWithTransitViewModel viewModel, Action<ItemDetailEarnLocationViewModel, bool> onTransitionButtonTapped, bool shouldShowTransitArea, bool popBeforeDetail)
        {
            _playerResourceIconComponent.Setup(viewModel.PlayerResourceDetailViewModel.iconViewModel);

            // whereGetMessageAreaComponentの初期設定
            _whereGetMessageAreaComponent.Hidden = !shouldShowTransitArea;
            if (shouldShowTransitArea)
            {
                _whereGetMessageAreaComponent.InitializeView();
                _whereGetMessageAreaComponent.EarnLocationSetActive(
                    viewModel.ItemDetailAvailableLocationViewModel.EarnLocationViewModel1, onTransitionButtonTapped, popBeforeDetail);
                _whereGetMessageAreaComponent.EarnLocationSetActive(
                    viewModel.ItemDetailAvailableLocationViewModel.EarnLocationViewModel2, onTransitionButtonTapped, popBeforeDetail);
            }

            if (viewModel.ItemDetailAvailableLocationViewModel.EarnLocationViewModel1.TransitionType == ItemTransitionType.None
                && viewModel.ItemDetailAvailableLocationViewModel.EarnLocationViewModel2.TransitionType == ItemTransitionType.None)
            {
                _whereGetMessageAreaComponent.Hidden = true;
            }

            if (viewModel.PlayerResourceDetailViewModel.Type is ResourceType.MissionBonusPoint or ResourceType.FreeDiamond or ResourceType.PaidDiamond)
            {
                _amountUI.Hidden = true;
                _whereGetMessageAreaComponent.Hidden = true;
            }
            else
            {
                _amountUI.Hidden = viewModel.PlayerResourceDetailViewModel.IsHideCurrentAmount;
                _amountText.SetText(viewModel.ItemDetailAmountViewModel.CurrentAmount.ToString());
            }

            // Coin/IdleIconは所持数を表示しない
            if (viewModel.PlayerResourceDetailViewModel.Type is ResourceType.Coin or ResourceType.IdleCoin)
            {
                _amountUI.Hidden = true;
            }

            _itemNameText.SetText(viewModel.PlayerResourceDetailViewModel.Name.Value);
            _itemDescriptionText.SetText(viewModel.PlayerResourceDetailViewModel.Description.Value);

            DisableDescriptionScrollIfNotNeeded();
        }

        public void PlayShowAnimation()
        {
            _animator.SetTrigger(Appear);
        }

        public void PlayCloseAnimation()
        {
            _animator.SetTrigger(Disappear);
        }

        void DisableDescriptionScrollIfNotNeeded()
        {
            LayoutRebuilder.ForceRebuildLayoutImmediate(_descriptionScrollRect.content);

            if (_descriptionScrollRect.content.sizeDelta.y <= _descriptionLayoutElement.minHeight)
            {
                _descriptionScrollRect.enabled = false;
                _descriptionScrollRect.verticalScrollbar.gameObject.SetActive(false);
            }
        }
    }
}
