using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.PackShop.Presentation.ViewModels;
using GLOW.Scenes.Shop.Domain.Model;
using GLOW.Scenes.ShopBuyConform.Presentation.ViewModel;

namespace GLOW.Scenes.Shop.Presentation.Translator
{
    public class ProductBuyWithCashConfirmationViewModelTranslator
    {
        public static ProductBuyWithCashConfirmationViewModel ToProductBuyWithCashConfirmationViewModel(PackShopProductViewModel model)
        {
            return new ProductBuyWithCashConfirmationViewModel(
                model.OprProductId,
                ProductType.Pack,
                model.DisplayCostType,
                model.ProductName,
                model.RawProductPriceText,
                model.Items.First(),
                model.DiscountRate,
                IsFirstTimeFreeDisplay.False);
        }

        public static ProductBuyWithCashConfirmationViewModel ToProductBuyWithCashConfirmationViewModel(
            ProductBuyWithCashConfirmationModel model)
        {
            var productModel = model.ProductModel;

            return new ProductBuyWithCashConfirmationViewModel(
                productModel.OprProductId,
                ProductType.Diamond,
                DisplayCostType.Cash,
                productModel.ProductName,
                productModel.RawProductPriceText,
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(
                    productModel.ProductContents.First()),
                DiscountRate.Empty,
                productModel.IsFirstTimeFreeDisplay);
        }
    }
}
