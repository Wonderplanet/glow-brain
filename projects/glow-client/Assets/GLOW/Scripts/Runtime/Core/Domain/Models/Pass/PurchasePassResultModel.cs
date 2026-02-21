using System.Collections.Generic;
using GLOW.Core.Domain.Models.Shop;

namespace GLOW.Core.Domain.Models.Pass
{
    public record PurchasePassResultModel(
        UserStoreProductModel UserStoreProductModel,
        UserParameterModel UserParameterModel,
        UserShopPassModel UserShopPassModel,
        UserStoreInfoModel UserStoreInfoModel)
    {
        public static PurchasePassResultModel Empty { get; } = new(
            UserStoreProductModel.Empty,
            UserParameterModel.Empty,
            UserShopPassModel.Empty,
            UserStoreInfoModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
