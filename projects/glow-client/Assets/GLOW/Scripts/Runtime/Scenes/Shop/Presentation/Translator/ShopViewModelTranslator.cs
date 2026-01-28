using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.Translators;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.PassShop.Domain.Model;
using GLOW.Scenes.PassShop.Presentation.Translator;
using GLOW.Scenes.Shop.Domain.Constants;
using GLOW.Scenes.Shop.Domain.Extension;
using GLOW.Scenes.Shop.Domain.Model;
using GLOW.Scenes.Shop.Presentation.ViewModel;

namespace GLOW.Scenes.Shop.Presentation.Translator
{
    public class ShopViewModelTranslator
    {
        public static ShopViewModel Translate(
            IReadOnlyList<StoreProductModel> storeProductModels,
            IReadOnlyList<ShopProductModel> shopProductModels,
            RemainingTimeSpan diamondNextUpdateTime,
            RemainingTimeSpan dailyNextUpdateTime,
            RemainingTimeSpan weeklyNextUpdateTime,
            RemainingTimeSpan coinNextUpdateTime,
            HeldAdSkipPassInfoModel heldAdSkipPassInfoModel)
        {
            var diamonds = ToStoreProductCellViewModel(storeProductModels, diamondNextUpdateTime);
            var daily =
                ToShopCategoryProductCellViewModel(
                    DisplayShopProductType.Daily,
                    shopProductModels.Where(s => s.DisplayShopProductType == DisplayShopProductType.Daily),
                    dailyNextUpdateTime);
            var weekly =
                ToShopCategoryProductCellViewModel(
                    DisplayShopProductType.Weekly,
                    shopProductModels.Where(s => s.DisplayShopProductType == DisplayShopProductType.Weekly),
                    weeklyNextUpdateTime);
            var coin =
                ToShopCategoryProductCellViewModel(
                    DisplayShopProductType.Coin,
                    shopProductModels.Where(s => s.DisplayShopProductType == DisplayShopProductType.Coin),
                    coinNextUpdateTime);
            var adSkipInfo = HeldAdSkipPassInfoViewModelTranslator.ToHeldAdSkipPassInfoViewModel(heldAdSkipPassInfoModel);
            return new ShopViewModel(diamonds, daily, weekly, coin, adSkipInfo);
        }

        public static IReadOnlyList<ShopProductCellViewModel> ToShopProductCellViewModels(
            IEnumerable<ShopProductModel> targetTypeShopProductModels)
        {
            return targetTypeShopProductModels
                .Select(ToShopProductCellViewModel)
                .ToList();
        }
        public static IReadOnlyList<ShopProductCellViewModel> ToPaidDiamondShopProductCellViewModels(
            IReadOnlyList<StoreProductModel> storeProductModels)
        {
            return storeProductModels
                .Select(ToPaidDiamondShopProductCellViewModel)
                .ToList();
        }

        static ShopCategoryProductCellViewModel ToStoreProductCellViewModel(
            IReadOnlyList<StoreProductModel> storeProductModels,
            RemainingTimeSpan diamondNextUpdateTime)
        {
            return new ShopCategoryProductCellViewModel(
                DisplayShopProductType.Diamond,
                diamondNextUpdateTime,
                ToPaidDiamondShopProductCellViewModels(storeProductModels).ToList()
                );
        }

        static ShopCategoryProductCellViewModel ToShopCategoryProductCellViewModel(
            DisplayShopProductType shopType,
            IEnumerable<ShopProductModel> shopProductModels,
            RemainingTimeSpan categoryNextUpdateTime)
        {
            return new ShopCategoryProductCellViewModel(
                shopType,
                categoryNextUpdateTime,
                ToShopProductCellViewModels(shopProductModels).ToList()
                );
        }

        static ShopProductCellViewModel ToShopProductCellViewModel(ShopProductModel product)
        {
            return new ShopProductCellViewModel(
                product.Id,
                product.ResourceId,
                product.DisplayShopProductType,
                product.ResourceType,
                product.ProductName,
                product.ProductResourceAmount,
                product.ResourceType == ResourceType.Item
                    ? ItemViewModelTranslator.ToItemIconViewModel(product.ItemModel)
                    : ItemIconViewModel.Empty,
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(product.PlayerResourceModel),
                product.IsFirstTimeFreeDisplay,
                product.PurchasableCount,
                RemainingTimeSpan.Empty, 
                product.NewFlag,
                product.DisplayCostType,
                product.CostAmount,

                product.ShopProductAssetPath,
                RawProductPriceText.Empty
            );
        }

        public static ShopProductCellViewModel ToPaidDiamondShopProductCellViewModel(StoreProductModel product)
        {
            return new ShopProductCellViewModel(
                product.OprProductId,
                ShopConst.DiamondId,
                product.ProductType.ToDisplayShopProductType(),
                ResourceType.PaidDiamond,
                new ProductName("プリズム"),
                product.PaidResourceAmount,
                ItemIconViewModel.Empty,
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(product.PlayerResourceModel),
                new IsFirstTimeFreeDisplay(false),
                product.PurchasableCount,
                product.PurchasableTime,
                product.NewFlag,
                product.DisplayCostType,
                product.Price.ToCostAmount(),
                product.ShopProductAssetPath,
                product.RawProductPriceText
            );
        }
    }
}
