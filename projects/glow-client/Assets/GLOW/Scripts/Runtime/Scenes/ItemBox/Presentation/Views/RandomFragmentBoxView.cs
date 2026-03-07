using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.ItemBox.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.ItemBox.Presentation.Views
{
    public class RandomFragmentBoxView : UIView
    {
        static readonly int Disappear = Animator.StringToHash("disappear");
        static readonly int Appear = Animator.StringToHash("appear");


        [Header(("各種要素"))]
        [SerializeField] UIText _itemDescriptionText;
        [SerializeField] ItemIconComponent _itemIconComponent;
        [SerializeField] UIText _itemNameText;
        [SerializeField] AmountSelectionComponent _amountSelectionComponent;
        [SerializeField] Animator _animator;

        public ItemAmount SelectedItemAmount => _amountSelectionComponent.Amount;

        public void Setup(RandomFragmentBoxViewModel viewModel)
        {
            var itemDetail = viewModel.ItemDetail;
            var limitAmount = ItemAmount.Min(itemDetail.Amount, viewModel.LimitUseAmount);

            _itemDescriptionText.SetText(itemDetail.Description.Value);
            _itemIconComponent.Setup(itemDetail.ItemIconAssetPath, itemDetail.Rarity, itemDetail.Amount);
            _itemNameText.SetText(itemDetail.Name.Value);
            _amountSelectionComponent.Setup(ItemAmount.One, limitAmount);
        }

        public void PlayShowAnimation()
        {
            _animator.SetTrigger(Appear);
        }

        public void PlayCloseAnimation()
        {
            _animator.SetTrigger(Disappear);
        }
    }
}
