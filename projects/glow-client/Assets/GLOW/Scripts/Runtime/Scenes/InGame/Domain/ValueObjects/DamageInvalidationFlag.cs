namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record DamageInvalidationFlag(bool Value)
    {
        public static DamageInvalidationFlag True { get; } = new(true);
        public static DamageInvalidationFlag False { get; } = new(false);

        public static implicit operator bool(DamageInvalidationFlag flag) => flag.Value;

        public static bool operator true(DamageInvalidationFlag flag) => flag.Value;
        public static bool operator false(DamageInvalidationFlag flag) => !flag.Value;
        public static DamageInvalidationFlag operator |(DamageInvalidationFlag left, DamageInvalidationFlag right) => new (left.Value | right.Value);
    }
}
