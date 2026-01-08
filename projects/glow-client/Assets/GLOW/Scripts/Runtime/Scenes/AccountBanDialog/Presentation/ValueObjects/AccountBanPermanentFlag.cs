namespace GLOW.Scenes.AccountBanDialog.Presentation.ValueObjects
{
    public record AccountBanPermanentFlag(bool Value)
    {
        public static AccountBanPermanentFlag True { get; } = new(true);
        public static AccountBanPermanentFlag False { get; } = new(false);

        public static implicit operator bool(AccountBanPermanentFlag flag) => flag.Value;

        public static bool operator true(AccountBanPermanentFlag flag) => flag.Value;
        public static bool operator false(AccountBanPermanentFlag flag) => !flag.Value;
    }
}
