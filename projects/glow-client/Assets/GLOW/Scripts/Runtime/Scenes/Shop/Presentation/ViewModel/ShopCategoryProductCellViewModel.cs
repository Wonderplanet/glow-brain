using System.Collections.Generic;
using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.Shop.Presentation.ViewModel
{
    public record ShopCategoryProductCellViewModel(
        DisplayShopProductType DisplayShopProductType,
        RemainingTimeSpan UpdateTime,
        IReadOnlyList<ShopProductCellViewModel> ShopProductCellViewModels)
    {
        public static ShopCategoryProductCellViewModel Empty { get; } = new(
            DisplayShopProductType.Daily,
            RemainingTimeSpan.Empty,
            new List<ShopProductCellViewModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    };
}
