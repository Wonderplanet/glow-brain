using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Shop.Domain.Factories;
using GLOW.Scenes.Shop.Domain.Model;
using Zenject;

namespace GLOW.Scenes.Shop.Domain.UseCase
{
    public class ConfirmProductBuyWithCashUseCase
    {
        [Inject] IValidatedStoreProductRepository ValidatedStoreProductRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IConfirmationShopProductModelFactory ConfirmationShopProductModelFactory { get; }

        public ProductBuyWithCashConfirmationModel ConfirmProductBuyWithCash(MasterDataId oprProductId)
        {
            var nowTime = TimeProvider.Now;

            var validateStoreProduct = ValidatedStoreProductRepository.GetValidatedStoreProducts()
                .Where(model => model.MstStoreProduct.OprProductId == oprProductId)
                .Where(storeProduct => storeProduct.MstStoreProduct.ProductType == ProductType.Diamond)
                .First(model => CalculateTimeCalculator.IsValidTime(nowTime, model.MstStoreProduct.StartDate, model.MstStoreProduct.EndDate));

            var productModel = ConfirmationShopProductModelFactory.Create(validateStoreProduct);

            return new ProductBuyWithCashConfirmationModel(productModel);
        }
    }
}
