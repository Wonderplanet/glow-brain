using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.PassShop.Presentation.ViewModel;

namespace GLOW.Scenes.Shop.Presentation.ViewModel
{
    public record ShopViewModel(
        ShopCategoryProductCellViewModel DiamondCategory,
        ShopCategoryProductCellViewModel DailyCategory,
        ShopCategoryProductCellViewModel WeeklyCategory,
        ShopCategoryProductCellViewModel CoinCategory,
        HeldAdSkipPassInfoViewModel HeldAdSkipPassInfoViewModel)
    {
        public IReadOnlyList<ShopCategoryProductCellViewModel> ShopCategoryViewModelToList()
        {
            return new List<ShopCategoryProductCellViewModel>
            {
                DiamondCategory,
                DailyCategory,
                WeeklyCategory,
                CoinCategory
            };
        }
        public ShopCategoryProductCellViewModel GetTargetCellViewModel(MasterDataId targetProductId)
        {
            if (DiamondCategory.ShopProductCellViewModels.Any(x => x.ProductId == targetProductId))
            {
                return DiamondCategory;
            }

            if (DailyCategory.ShopProductCellViewModels.Any(x => x.ProductId == targetProductId))
            {
                return DailyCategory;
            }

            if (WeeklyCategory.ShopProductCellViewModels.Any(x => x.ProductId == targetProductId))
            {
                return WeeklyCategory;
            }

            if (CoinCategory.ShopProductCellViewModels.Any(x => x.ProductId == targetProductId))
            {
                return CoinCategory;
            }

            return ShopCategoryProductCellViewModel.Empty;
        }
    };
}
