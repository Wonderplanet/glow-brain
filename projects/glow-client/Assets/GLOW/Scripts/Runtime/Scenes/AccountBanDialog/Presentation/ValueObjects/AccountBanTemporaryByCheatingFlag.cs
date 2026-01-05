namespace GLOW.Scenes.AccountBanDialog.Presentation.ValueObjects
{
    public record AccountBanTemporaryByCheatingFlag(bool Value)
    {
        public static AccountBanTemporaryByCheatingFlag True { get; } = new(true);
        public static AccountBanTemporaryByCheatingFlag False { get; } = new(false);

        public static implicit operator bool(AccountBanTemporaryByCheatingFlag flag) => flag.Value;

        public static bool operator true(AccountBanTemporaryByCheatingFlag flag) => flag.Value;
        public static bool operator false(AccountBanTemporaryByCheatingFlag flag) => !flag.Value;
    }
}
