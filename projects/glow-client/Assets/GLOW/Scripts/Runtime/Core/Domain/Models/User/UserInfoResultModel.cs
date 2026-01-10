using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record UserInfoResultModel(
        UserMyId UserMyId)
    {
        public static UserInfoResultModel Empty { get; } = new UserInfoResultModel(UserMyId.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
