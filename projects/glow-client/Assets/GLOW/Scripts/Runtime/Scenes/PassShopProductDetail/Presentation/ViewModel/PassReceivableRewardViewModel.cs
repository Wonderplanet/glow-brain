using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.PassShopProductDetail.Presentation.ViewModel
{
    public record PassReceivableRewardViewModel(
        ProductName ProductName,
        PlayerResourceIconViewModel PlayerResourceIconViewModel,
        ObscuredPlayerResourceAmount DailyReceivableAmount)
    {
        public static PassReceivableRewardViewModel Empty { get; } = new(
            ProductName.Empty,
            PlayerResourceIconViewModel.Empty,
            ObscuredPlayerResourceAmount.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}