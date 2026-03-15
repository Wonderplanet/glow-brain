using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;

namespace GLOW.Scenes.PassShopProductDetail.Domain.Model
{
    public record PassReceivableRewardModel(
        ProductName ProductName,
        PlayerResourceModel PlayerResourceModel,
        ObscuredPlayerResourceAmount DailyReceivableAmount)
    {
        public static PassReceivableRewardModel Empty { get; } = new(
            ProductName.Empty,
            PlayerResourceModel.Empty,
            ObscuredPlayerResourceAmount.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}