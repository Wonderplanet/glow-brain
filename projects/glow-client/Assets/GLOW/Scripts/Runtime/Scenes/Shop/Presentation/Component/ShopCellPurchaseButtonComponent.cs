using Cysharp.Text;
using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.PassShop.Presentation.ViewModel;
using GLOW.Scenes.Shop.Presentation.View;
using UnityEngine;

namespace GLOW.Scenes.Shop.Presentation.Component
{
    public class ShopCellPurchaseButtonComponent : UIObject
    {
        [SerializeField] UIImage _buttonImage;
        [SerializeField] ShopCostTextComponent _shopCostTextComponent;
        [SerializeField] UIObject _adTextObject;
        [SerializeField] UIObject _adSkipTextObject;
        [SerializeField] UIText _heldAdSkipPassNameText;
        [SerializeField] Sprite _adButtonSprite;
        [SerializeField] Sprite _normalButtonSprite;
        [SerializeField] Sprite _adSkipButtonSprite;
        
        public void Setup(
            PurchasableCount purchasableCount,
            CostAmount costAmount,
            RawProductPriceText price,
            DisplayCostType costType,
            IsFirstTimeFreeDisplay isFirstTimeFreeDisplay,
            HeldAdSkipPassInfoViewModel heldAdSkipPassInfoViewModel)
        {
            // 購入可能回数が0の場合は非表示
            if (purchasableCount.IsZero())
            {
                IsVisible = false;
                return;
            }
            

            IsVisible = true;
            var isAdvertisement = costType == DisplayCostType.Ad;
            if (isFirstTimeFreeDisplay.IsEnable())
            {
                _buttonImage.Sprite = _normalButtonSprite;
                _adTextObject.IsVisible = false;
                _shopCostTextComponent.IsVisible = true;
                _shopCostTextComponent.Setup(costType, costAmount, price, isFirstTimeFreeDisplay);
                _adSkipTextObject.IsVisible = false;
            }
            else if (isAdvertisement)
            {
                SetUpHeldAdSkipUi(heldAdSkipPassInfoViewModel);
            }
            else
            {
                _buttonImage.Sprite = _normalButtonSprite;
                _adTextObject.IsVisible = false;
                _shopCostTextComponent.IsVisible = true;
                _shopCostTextComponent.Setup(costType, costAmount, price, isFirstTimeFreeDisplay);
                _adSkipTextObject.IsVisible = false;
            }
        }

        void SetUpHeldAdSkipUi(HeldAdSkipPassInfoViewModel heldAdSkipPassInfoViewModel)
        {
            if (heldAdSkipPassInfoViewModel.IsEmpty())
            {
                _buttonImage.Sprite = _adButtonSprite;
                _adTextObject.IsVisible = true;
                _adSkipTextObject.IsVisible = false;
                _shopCostTextComponent.IsVisible = false;
            }
            else
            {
                _buttonImage.Sprite = _adSkipButtonSprite;
                _adSkipTextObject.IsVisible = true;
                _heldAdSkipPassNameText.SetText(ZString.Format(
                    "{0}適用中", 
                    heldAdSkipPassInfoViewModel.PassProductName.ToString()));
                _shopCostTextComponent.IsVisible = false;
            }
        }
    }
}