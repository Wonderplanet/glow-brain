using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.ExchangeShop.Presentation.ViewModel;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.ExchangeShop.Presentation.View
{
    public class ExchangeShopTopAmountComponent : MonoBehaviour
    {
        [SerializeField] UIImage _itemIconImage;
        [SerializeField] UIText _amountText;

        ItemIconAssetPath _itemIconAssetPath;

        public void Setup(ExchangeShopTopAmountViewModel viewModel)
        {
            _itemIconAssetPath = viewModel.ItemIconAssetPath;
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_itemIconImage.Image, viewModel.ItemIconAssetPath.Value);
            _amountText.SetText(viewModel.Amount.ToStringSeparated());
        }

        public void UpdateAmount(ItemAmount amount)
        {
            _amountText.SetText(amount.ToStringSeparated());
        }

        public bool IsSameItem(ItemIconAssetPath itemIconAssetPath)
        {
            return _itemIconAssetPath == itemIconAssetPath;
        }
    }
}
