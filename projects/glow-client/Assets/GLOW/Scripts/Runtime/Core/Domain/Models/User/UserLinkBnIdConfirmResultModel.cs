using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record UserLinkBnIdConfirmResultModel(
        UserName UserName,
        UserLevel UserLevel,
        UserMyId MyId)
    {
        public static UserLinkBnIdConfirmResultModel Empty { get; } = new(
            UserName.Empty,
            UserLevel.Empty,
            UserMyId.Empty);

        public bool IsUserEmpty()
        {
            return UserName.IsEmpty() || UserLevel.IsEmpty();
        }
    }
}
