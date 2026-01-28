using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Shop;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public class UserStoreInfoModelTranslator
    {
        public static UserStoreInfoModel ToUserStoreInfoModel(UsrStoreInfoData data)
        {
            if (data == null) return UserStoreInfoModel.Empty;

            return new UserStoreInfoModel(
                data.Age != null ? new UserAge(data.Age.Value) : UserAge.Empty,
                data.CurrentMonthTotalBilling == -1 ?
                    CurrentMonthTotalBilling.Empty :
                    new CurrentMonthTotalBilling(data.CurrentMonthTotalBilling),
                data.RenotifyAt != null ? new StoreRenotifyAt(data.RenotifyAt.Value) : StoreRenotifyAt.Empty);
        }
    }
}
