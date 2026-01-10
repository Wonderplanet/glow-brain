using System.Linq;
using GLOW.Core.Presentation.Translators;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.Shop.Domain.Model;
using GLOW.Scenes.ShopBuyConform.Presentation.ViewModel;

namespace GLOW.Scenes.Shop.Presentation.Translator
{
    public class ProductBuyWithDiamondConfirmationViewModelTranslator
    {
        public static ProductBuyWithDiamondConfirmationViewModel ToProductBuyWithDiamondConfirmationViewModel(
            ProductBuyWithDiamondConfirmationModel model)
        {
            var productModel = model.ProductModel;

            var playerResourceModel = productModel.ProductContents.FirstOrDefault();
            var playerResourceIconViewModel = playerResourceModel == null ?
                PlayerResourceIconViewModel.Empty :
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(playerResourceModel);

            return new ProductBuyWithDiamondConfirmationViewModel(
                productModel.ProductName,
                productModel.CostAmount,
                model.BeforePaidDiamond,
                model.AfterPaidDiamond,
                model.BeforeFreeDiamond,
                model.AfterFreeDiamond,
                playerResourceIconViewModel,
                productModel.IsFirstTimeFreeDisplay);
        }
    }
}
