using System;
using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.PackShop.Presentation.ViewModels;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.PackShop.Presentation.Views
{
    public class PackShopPriceButtonComponent : UIObject
    {
        [Header("価格欄")]
        [SerializeField] UIText _buyButtonText;
        [SerializeField] UIText _buyLimitButonText;
        [Header("購入可能回数")]
        [SerializeField] UIText _buyLimitPurchasableCountText;
        [SerializeField] UIText _buyFreePurchasableCountText;
        [SerializeField] UIText _buyAdPurchasableCountText;
        [SerializeField] UIText _buyAdSkipPurchasableCountText;
        [Header("広告スキップパス名")]
        [SerializeField] UIText _buyAdSkipPassNameText;
        [Header("ボタン")]
        [SerializeField] Button _buyButton;
        [SerializeField] Button _buyLimitButton;
        [SerializeField] Button _buyFreeButton;
        [SerializeField] Button _buyAdButton;
        [SerializeField] Button _buyAdSkipButton;

        public void SetButton(
            PackShopProductViewModel viewModel,
            Action<PackShopProductViewModel> buyEvent)
        {
            var price = viewModel.RawProductPriceText.ToString();
            var count = viewModel.PurchasableCount;
            var costType = viewModel.DisplayCostType;

            _buyButtonText.SetText(price);
            _buyLimitButonText.SetText(price);
            SetUpPurchasableCount(viewModel);

            var isNoLimit = count.IsEmpty() || count.IsInfinity();
            SetUpBuyButton(_buyButton, viewModel, buyEvent, costType == DisplayCostType.Cash && isNoLimit);
            SetUpBuyButton(_buyLimitButton, viewModel, buyEvent, costType == DisplayCostType.Cash && !isNoLimit);
            SetUpBuyButton(_buyFreeButton, viewModel, buyEvent, costType == DisplayCostType.Free);
            var emptyAdSkipPass = viewModel.HeldAdSkipPassInfo.IsEmpty();
            SetUpBuyButton(
                _buyAdButton,
                viewModel,
                buyEvent,
                costType == DisplayCostType.Ad && emptyAdSkipPass);
            SetUpBuyButton(
                _buyAdSkipButton,
                viewModel,
                buyEvent,
                costType == DisplayCostType.Ad && !emptyAdSkipPass);

            _buyAdSkipPassNameText.SetText("{0}適用中",
                viewModel.HeldAdSkipPassInfo.PassProductName.ToString());
        }

        void SetUpPurchasableCount(PackShopProductViewModel viewModel)
        {
            var text = viewModel.IsFirstTimeFreeDisplay.IsEnable()
                ? "1" : viewModel.PurchasableCount.ToString();
            _buyLimitPurchasableCountText.SetText(text);
            _buyFreePurchasableCountText.SetText(text);
            _buyAdPurchasableCountText.SetText(text);
            _buyAdSkipPurchasableCountText.SetText(text);
        }

        void SetUpBuyButton(
            Button button,
            PackShopProductViewModel viewModel,
            Action<PackShopProductViewModel> buyEvent,
            bool isActive)
        {
            button.onClick.RemoveAllListeners();
            button.onClick.AddListener(() => buyEvent?.Invoke(viewModel));
            button.gameObject.SetActive(isActive);
        }
    }
}
