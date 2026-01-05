namespace GLOW.Scenes.AccountBanDialog.Presentation.ValueObjects
{
    public record AccountBanTemporaryByDetectedAnomalyFlag(bool Value)
    {
        public static AccountBanTemporaryByDetectedAnomalyFlag True { get; } = new(true);
        public static AccountBanTemporaryByDetectedAnomalyFlag False { get; } = new(false);

        public static implicit operator bool(AccountBanTemporaryByDetectedAnomalyFlag flag) => flag.Value;

        public static bool operator true(AccountBanTemporaryByDetectedAnomalyFlag flag) => flag.Value;
        public static bool operator false(AccountBanTemporaryByDetectedAnomalyFlag flag) => !flag.Value;
    }
}
