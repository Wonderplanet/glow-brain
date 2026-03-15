using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models.Shop
{
    public record UserStoreInfoModel(
        UserAge UserAge,
        CurrentMonthTotalBilling CurrentMonthTotalBilling,
        StoreRenotifyAt StoreRenotifyAt)
    {
        public static UserStoreInfoModel Empty { get; } =  new (
            UserAge.Empty,
            CurrentMonthTotalBilling.Empty,
            StoreRenotifyAt.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
