using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Shop.Domain.Factories;
using GLOW.Scenes.Shop.Domain.Model;
using Zenject;

namespace GLOW.Scenes.Shop.Domain.UseCase
{
    public class ConfirmProductBuyWithCoinUseCase
    {
        [Inject] IMstShopProductDataRepository MstShopProductDataRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IConfirmationShopProductModelFactory ConfirmationShopProductModelFactory { get; }

        public ProductBuyWithCoinConfirmationModel ConfirmProductBuyWithCoin(MasterDataId masterDataId)
        {
            var nowTime = TimeProvider.Now;

            var mstShopItemModel = MstShopProductDataRepository.GetShopProducts()
                .Where(model => model.Id == masterDataId)
                .First(model => CalculateTimeCalculator.IsValidTime(nowTime, model.StartDate, model.EndDate));

            var productModel = ConfirmationShopProductModelFactory.Create(mstShopItemModel);

            var beforeCoin = GetCurrentCoin();
            var afterCoin = beforeCoin - productModel.CostAmount;

            return new ProductBuyWithCoinConfirmationModel(
                productModel,
                beforeCoin,
                afterCoin);
        }

        Coin GetCurrentCoin()
        {
            var gameFetchModel = GameRepository.GetGameFetch();
            return gameFetchModel.UserParameterModel.Coin;
        }
    }
}
