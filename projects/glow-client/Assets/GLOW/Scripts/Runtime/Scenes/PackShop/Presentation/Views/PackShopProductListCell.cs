using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.PackShop.Presentation.ViewModels;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.PackShop.Presentation.Views
{
    public class PackShopProductListCell : MonoBehaviour
    {
        [Serializable]
        public class Decorate
        {
            [SerializeField] string _enumKey;
            [SerializeField] GameObject _deco;

            public PackDecoration Decoration => Enum.Parse<PackDecoration>(_enumKey);
            public void ShowDeco() => _deco.SetActive(true);
            public void HideDeco() => _deco.SetActive(false);
        }

        [Header("情報")]
        [SerializeField] Button _infoButton;
        [SerializeField] UIText _productNameText;
        [SerializeField] PackShopEndDateTimeComponent _endTime;

        [Header("購入ボタン")]
        [SerializeField] PackShopPriceButtonComponent _priceButton;

        [Header("バナー")]
        [SerializeField] UIImage _banner;
        [SerializeField] List<Decorate> _decorates;
        [Header("バッジ")]
        [SerializeField] GameObject _newFlagObject;
        [SerializeField] ShopDiscountRateComponent _discountRate;
        [SerializeField] UIObject _noticeBadge;
        [Header("売り切れラベル")]
        [SerializeField] UIObject _soldOutLabel;

        public void Setup(
            PackShopProductViewModel viewModel,
            Action<PackShopProductViewModel> buyEvent,
            Action<MasterDataId> infoEvent)
        {
            SetUpButtonListener(viewModel, buyEvent, infoEvent);
            SetUpContent(viewModel);

            SetupDecoration(viewModel.Decoration);

            var isPurchasable = viewModel.PurchasableCount.IsPurchasable();
            _soldOutLabel.IsVisible = !isPurchasable;
            _priceButton.IsVisible = isPurchasable;
        }

        void SetUpButtonListener(
            PackShopProductViewModel viewModel,
            Action<PackShopProductViewModel> buyEvent,
            Action<MasterDataId> infoEvent)
        {
            _infoButton.onClick.RemoveAllListeners();
            _infoButton.onClick.AddListener(() => infoEvent?.Invoke(viewModel.OprProductId));
            _priceButton.SetButton(viewModel, buyEvent);
        }

        void SetUpContent(PackShopProductViewModel viewModel)
        {
            _productNameText.SetText(viewModel.ProductName.Value);
            _newFlagObject.SetActive(viewModel.NewFlag.Flg);
            _discountRate.SetDiscountRate(viewModel.DiscountRate);
            _discountRate.gameObject.SetActive(!viewModel.DiscountRate.IsZero());
            _noticeBadge.IsVisible = viewModel.PurchasableCount.IsPurchasable() &&
                viewModel.DisplayCostType is DisplayCostType.Free or DisplayCostType.Ad;

            _endTime.gameObject.SetActive(!viewModel.EndDateTime.IsEmpty());

            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_banner.Image, viewModel.BannerAssetPath.Value);
        }

        public void UpdateEndTime(TimeSpan endTime)
        {
            _endTime.UpdateEndTime(endTime);
        }

        public void SetEndTimeInfinity()
        {
            _endTime.SetEndTimeInfinity();
        }

        void SetupDecoration(PackDecoration? decoration)
        {
            foreach (var deco in _decorates)
            {
                deco.HideDeco();
            }

            if (!decoration.HasValue) return;

            var decoObj = _decorates.FirstOrDefault(d => d.Decoration == decoration.Value);
            decoObj?.ShowDeco();
        }
    }
}
