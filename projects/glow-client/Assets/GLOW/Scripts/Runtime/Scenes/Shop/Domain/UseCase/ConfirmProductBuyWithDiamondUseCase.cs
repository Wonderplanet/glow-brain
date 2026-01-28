using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Shop.Domain.Factories;
using GLOW.Scenes.Shop.Domain.Model;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Scenes.Shop.Domain.UseCase
{
    public class ConfirmProductBuyWithDiamondUseCase
    {
        [Inject] IMstShopProductDataRepository MstShopProductDataRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] ISystemInfoProvider SystemInfoProvider { get; }
        [Inject] IConfirmationShopProductModelFactory ConfirmationShopProductModelFactory { get; }

        public ProductBuyWithDiamondConfirmationModel ConfirmProductBuyWithDiamond(MasterDataId masterDataId)
        {
            var nowTime = TimeProvider.Now;

            var mstShopItemModel = MstShopProductDataRepository.GetShopProducts()
                .Where(model => model.Id == masterDataId)
                .First(model => CalculateTimeCalculator.IsValidTime(nowTime, model.StartDate, model.EndDate));

            var productModel = ConfirmationShopProductModelFactory.Create(mstShopItemModel);

            var (beforePaidDiamond, beforeFreeDiamond) = GetCurrentDiamond();

            var (afterPaidDiamond, afterFreeDiamond) = DiamondCalculator.CalculateAfterDiamonds(
                beforePaidDiamond,
                beforeFreeDiamond,
                productModel.CostAmount.ToTotalDiamond());


            return new ProductBuyWithDiamondConfirmationModel(
                productModel,
                beforePaidDiamond,
                afterPaidDiamond,
                beforeFreeDiamond,
                afterFreeDiamond);
        }

        (PaidDiamond paid, FreeDiamond free) GetCurrentDiamond()
        {
            var gameFetchModel = GameRepository.GetGameFetch();
            var userParameterModel = gameFetchModel.UserParameterModel;

            var freeDiamond = userParameterModel.FreeDiamond;
            var paidDiamond = userParameterModel.GetPaidDiamondFromPlatform(SystemInfoProvider.GetApplicationSystemInfo().PlatformId);

            return (paidDiamond, freeDiamond);
        }
    }
}
