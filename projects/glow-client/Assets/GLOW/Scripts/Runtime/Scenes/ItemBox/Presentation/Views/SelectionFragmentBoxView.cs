using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.ItemBox.Domain.UseCases;
using GLOW.Scenes.ItemBox.Presentation.Components;
using GLOW.Scenes.ItemBox.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using Zenject;

namespace GLOW.Scenes.ItemBox.Presentation.Views
{
    public class SelectionFragmentBoxView : UIView
    {
        [SerializeField] UIText _itemDescriptionText;
        [SerializeField] ItemIconComponent _itemIconComponent;
        [SerializeField] UIText _itemNameText;
        [SerializeField] SelectionFragmentLineupList _lineupList;
        [SerializeField] AmountSelectionComponent _amountSelectionComponent;
        [SerializeField] GameObject _infoButton;

        public MasterDataId SelectedItemId => _lineupList.SelectedFragment.ItemId;
        public ItemAmount SelectedItemAmount => _amountSelectionComponent.Amount;

        public void Setup(SelectionFragmentBoxViewModel viewModel)
        {
            _lineupList.Setup(viewModel.Lineup);

            var itemDetail = viewModel.ItemDetail;
            var limitAmount = ItemAmount.Min(itemDetail.Amount, viewModel.LimitUseAmount);

            _itemDescriptionText.SetText(itemDetail.Description.Value);
            _itemIconComponent.Setup(itemDetail.ItemIconAssetPath, itemDetail.Rarity, itemDetail.Amount);
            _itemNameText.SetText(itemDetail.Name.Value);
            _amountSelectionComponent.Setup(ItemAmount.One, limitAmount);

            _infoButton.SetActive(viewModel.IsAvailableLocation());
        }

        public void PlayCellAppearanceAnimation()
        {
            _lineupList.PlayCellAppearanceAnimation();
        }
    }
}
