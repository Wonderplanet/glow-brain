using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Extensions;
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
                .FirstOrDefault(model =>
                    CalculateTimeCalculator.IsValidTime(nowTime, model.MstStoreProduct.StartDate, model.MstStoreProduct.EndDate),
                    ValidatedStoreProductModel.Empty);

            // 期間外など、購入不可の場合
            if (validateStoreProduct.IsEmpty())
            {
                return ProductBuyWithCashConfirmationModel.Empty;
            }

            var productModel = ConfirmationShopProductModelFactory.Create(validateStoreProduct);

            return new ProductBuyWithCashConfirmationModel(productModel);
        }
    }
}
