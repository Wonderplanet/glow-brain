using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Extensions;
using GLOW.Scenes.Shop.Domain.Calculator;
using GLOW.Scenes.Shop.Domain.Model;
using Zenject;

namespace GLOW.Scenes.Shop.Domain.Factories
{
    public class ConfirmationShopProductModelFactory : IConfirmationShopProductModelFactory
    {
        [Inject] IMstIdleIncentiveRepository MstIdleIncentiveRepository { get; }
        [Inject] IMstItemDataRepository MstItemDataRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IShopProductModelCalculator ShopProductModelCalculator { get; }

        public ConfirmationShopProductModel Create(MstShopItemModel mstShopItemModel)
        {
            var productName = GetProductName(mstShopItemModel);
            var isFirstTimeFreeDisplay = GetIsFirstTimeFreeDisplay(mstShopItemModel);

            var costAmount = isFirstTimeFreeDisplay.IsEnable()
                ? CostAmount.Zero
                : mstShopItemModel.CostAmount;

            var productContents = CreateProductContents(mstShopItemModel);

            return new ConfirmationShopProductModel(
                MasterDataId.Empty,
                productName,
                costAmount,
                RawProductPriceText.Empty,
                isFirstTimeFreeDisplay,
                productContents);
        }

        public ConfirmationShopProductModel Create(ValidatedStoreProductModel validateStoreProductModel)
        {
            var mstStoreProductModel = validateStoreProductModel.MstStoreProduct;
            var productName = GetProductName(mstStoreProductModel);

            var playerResourceModel = PlayerResourceModelFactory.Create(
                ResourceType.PaidDiamond,
                MasterDataId.Empty,
                mstStoreProductModel.PaidAmount.ToPlayerResourceAmount());
            var price = validateStoreProductModel.GetPrice();

            return new ConfirmationShopProductModel(
                mstStoreProductModel.OprProductId,
                productName,
                price.ToCostAmount(),
                validateStoreProductModel.RawProductPriceText,
                IsFirstTimeFreeDisplay.False,
                new List<PlayerResourceModel>{ playerResourceModel });
        }

        ProductName GetProductName(MstShopItemModel mstShopItemModel)
        {
            return GetProductName(mstShopItemModel.ResourceType, mstShopItemModel.ResourceId, mstShopItemModel.ProductResourceAmount);
        }

        ProductName GetProductName(MstStoreProductModel mstStoreProductModel)
        {
            return GetProductName(ResourceType.PaidDiamond, MasterDataId.Empty, mstStoreProductModel.PaidAmount);
        }

        ProductName GetProductName(ResourceType resourceType, MasterDataId resourceId, ProductResourceAmount amount)
        {
            MstItemModel mstItemModel = resourceType == ResourceType.Item
                ? MstItemDataRepository.GetItem(resourceId)
                : MstItemModel.Empty;
            MstCharacterModel mstCharacterModel = resourceType == ResourceType.Unit
                ? MstCharacterDataRepository.GetCharacter(resourceId)
                : MstCharacterModel.Empty;

            return ProductName.FromTypeAndName(resourceType, mstItemModel.Name,mstCharacterModel.Name, amount);
        }

        List<PlayerResourceModel> CreateProductContents(MstShopItemModel mstShopItemModel)
        {
            var idleIncentiveCoinSetting = ShopProductModelCalculator.GetBaseCoinAmountAndIntervalTime(
                MstIdleIncentiveRepository,
                GameRepository);

            var amount = ShopProductModelCalculator.CalculateProductResourceAmount(
                mstShopItemModel,
                idleIncentiveCoinSetting.baseCoinAmount,
                idleIncentiveCoinSetting.intervalMinutes);

            var playerResourceModel = PlayerResourceModelFactory.Create(
                mstShopItemModel.ResourceType,
                mstShopItemModel.ResourceId,
                amount);

            return new List<PlayerResourceModel>{ playerResourceModel };
        }

        IsFirstTimeFreeDisplay GetIsFirstTimeFreeDisplay(MstShopItemModel mstShopItemModel)
        {
            if (!mstShopItemModel.IsFirstTimeFree.IsEnable())
            {
                return IsFirstTimeFreeDisplay.False;
            }

            var gameFetchOtherModel = GameRepository.GetGameFetchOther();

            var userShopItemModel = gameFetchOtherModel.UserShopItemModels
                .FirstOrDefault(model => model.MstShopItemId == mstShopItemModel.Id, UserShopItemModel.Empty);

            return new IsFirstTimeFreeDisplay(userShopItemModel.TradeCount.IsZero());
        }
    }
}
