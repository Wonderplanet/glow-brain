namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record AutoPlayerSequenceElementActivatedFlag(bool Value)
    {
        public static AutoPlayerSequenceElementActivatedFlag True { get; } = new(true);
        public static AutoPlayerSequenceElementActivatedFlag False { get; } = new(false);

        public static implicit operator bool(AutoPlayerSequenceElementActivatedFlag flag) => flag.Value;

        public static bool operator true(AutoPlayerSequenceElementActivatedFlag flag) => flag.Value;
        public static bool operator false(AutoPlayerSequenceElementActivatedFlag flag) => !flag.Value;

        public static AutoPlayerSequenceElementActivatedFlag operator |(AutoPlayerSequenceElementActivatedFlag left, AutoPlayerSequenceElementActivatedFlag right) => new(left.Value | right.Value);
    }
}
