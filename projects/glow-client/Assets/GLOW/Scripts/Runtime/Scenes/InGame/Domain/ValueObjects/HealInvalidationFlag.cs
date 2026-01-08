namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record HealInvalidationFlag(bool Value)
    {
        public static HealInvalidationFlag True { get; } = new(true);
        public static HealInvalidationFlag False { get; } = new(false);

        public static implicit operator bool(HealInvalidationFlag flag) => flag.Value;

        public static bool operator true(HealInvalidationFlag flag) => flag.Value;
        public static bool operator false(HealInvalidationFlag flag) => !flag.Value;
        public static HealInvalidationFlag operator |(HealInvalidationFlag left, HealInvalidationFlag right) => new (left.Value | right.Value);
    }
}
