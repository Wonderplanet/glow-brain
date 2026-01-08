using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record UserItemModel(UserDataId UsrItemId, MasterDataId MstItemId, ItemAmount Amount): ILimitedAmountValueObject
    {
        public static UserItemModel Empty { get; } = new UserItemModel(UserDataId.Empty, MasterDataId.Empty, ItemAmount.Zero);
        public int HasAmount => Amount.Value;

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
