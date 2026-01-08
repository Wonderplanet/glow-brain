using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules;
using GLOW.Scenes.UnitEnhance.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.UnitEnhance.Presentation.Views.Components
{
    public class UnitEnhanceRequireItemIconComponent : UIObject
    {
        [SerializeField] ItemIconComponent _itemIconComponent;
        [SerializeField] UIText _amountText;
        [SerializeField] Button _button;
        [SerializeField] Color _enoughCostColor;
        [SerializeField] Color _notEnoughCostColor;

        public void Setup(
            UnitEnhanceRequireItemViewModel viewModel,
            Action<ResourceType, MasterDataId, PlayerResourceAmount> onItemTapped)
        {
            var itemIconViewModel = viewModel.ItemIcon;
            _itemIconComponent.Setup(
                itemIconViewModel.ItemIconAssetPath,
                itemIconViewModel.Rarity,
                itemIconViewModel.Amount);
            var color = viewModel.IsEnoughCost ? _enoughCostColor : _notEnoughCostColor;
            _amountText.SetColor(color);

            _button.onClick.RemoveAllListeners();
            _button.onClick.AddListener(() =>
            {
                onItemTapped?.Invoke(
                    ResourceType.Item,
                    itemIconViewModel.ItemId,
                    itemIconViewModel.Amount.ToPlayerResourceAmount());
            });
        }
    }
}
