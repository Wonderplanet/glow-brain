namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record AutoPlayerSequenceElementDeactivatedFlag(bool Value)
    {
        public static AutoPlayerSequenceElementDeactivatedFlag True { get; } = new(true);
        public static AutoPlayerSequenceElementDeactivatedFlag False { get; } = new(false);

        public static implicit operator bool(AutoPlayerSequenceElementDeactivatedFlag flag) => flag.Value;

        public static bool operator true(AutoPlayerSequenceElementDeactivatedFlag flag) => flag.Value;
        public static bool operator false(AutoPlayerSequenceElementDeactivatedFlag flag) => !flag.Value;

        public static AutoPlayerSequenceElementDeactivatedFlag operator |(AutoPlayerSequenceElementDeactivatedFlag left, AutoPlayerSequenceElementDeactivatedFlag right)
            => new(left.Value | right.Value);
    }
}
