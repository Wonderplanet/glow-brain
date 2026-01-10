namespace GLOW.Core.Domain.ValueObjects
{
    public record UserDataCreatedFlag(bool Value)
    {
        public static UserDataCreatedFlag True { get; } = new UserDataCreatedFlag(true);
        public static UserDataCreatedFlag False { get; } = new UserDataCreatedFlag(false);

        public static implicit operator bool(UserDataCreatedFlag userDataCreatedFlag) => userDataCreatedFlag.Value;
    }
}
