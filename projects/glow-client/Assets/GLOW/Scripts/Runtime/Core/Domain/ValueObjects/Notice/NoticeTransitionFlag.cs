namespace GLOW.Core.Domain.ValueObjects.Notice
{
    public record NoticeTransitionFlag(bool Value)
    {
        public static NoticeTransitionFlag True { get; } = new NoticeTransitionFlag(true);
        public static NoticeTransitionFlag False { get; } = new NoticeTransitionFlag(false);

        public static implicit operator bool(NoticeTransitionFlag flag) => flag.Value;
    }
}
