namespace GLOW.Modules.GameOption.Domain.ValueObjects
{
    public record PushOffFlag(bool Value)
    {
        public static PushOffFlag True { get; } = new(true);
        public static PushOffFlag False { get; } = new(false);

        public static implicit operator bool(PushOffFlag flag) => flag.Value;
        public static PushOffFlag operator !(PushOffFlag flag) => new(!flag.Value);
    }
}