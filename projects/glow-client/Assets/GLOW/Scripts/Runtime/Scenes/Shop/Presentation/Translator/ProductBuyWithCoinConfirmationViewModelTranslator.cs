using System.Linq;
using GLOW.Core.Presentation.Translators;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.Shop.Domain.Model;
using GLOW.Scenes.ShopBuyConform.Presentation.ViewModel;

namespace GLOW.Scenes.Shop.Presentation.Translator
{
    public class ProductBuyWithCoinConfirmationViewModelTranslator
    {
        public static ProductBuyWithCoinConfirmationViewModel ToProductBuyWithCoinConfirmationViewModel(
            ProductBuyWithCoinConfirmationModel model)
        {
            var productModel = model.ProductModel;

            var playerResourceModel = productModel.ProductContents.FirstOrDefault();
            var playerResourceIconViewModel = playerResourceModel == null ?
                PlayerResourceIconViewModel.Empty :
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(playerResourceModel);

            return new ProductBuyWithCoinConfirmationViewModel(
                productModel.ProductName,
                productModel.CostAmount,
                model.BeforeCoin,
                model.AfterCoin,
                playerResourceIconViewModel,
                productModel.IsFirstTimeFreeDisplay);
        }
    }
}
